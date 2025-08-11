import inspect
from django.contrib import messages
from django.core.exceptions import BadSignature
from django.core.signing import loads as decrypt
from django.http import HttpRequest
from django.shortcuts import redirect, render
from typing import Any
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller
from ....Models.activity.order import Order
from ....Models.individuals.user import User
from ....Models.planning.plan import Plan
from ....Models.planning.plan_request import PlanRequest
from ....Models.utils.utility import Utility
from .._helpers.http import get_redirect_url

class PlanRequestController(Controller):

  @classmethod
  def _authorize_super_admin(cls, request: HttpRequest) -> Any:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_code}'
    try:
      if getattr(request.user, 'type', None) != 'super admin':
        return default_permission_denial(
          request,
          err='Not super admin',
          ref=REF
        )
      return True
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF
      )

  @classmethod
  def _authorize_not_super_admin(cls, request: HttpRequest) -> Any:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_code}'
    try:
      if getattr(request.user, 'type', None) == 'super admin':
        return default_permission_denial(
          request,
          err='Super admin not allowed',
          ref=REF
        )
      return True
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF
      )

  def index(self, request: HttpRequest) -> Any:
    REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_code}'
    auth = self._authorize_super_admin(request)
    if auth is not True:
      return auth
    try:
      plan_requests = PlanRequest.objects.all()
      return render(
        request,
        'plan_request/index.html',
        {'plan_requests': plan_requests}
      )
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF
      )

  def request_view(self, request: HttpRequest, plan_id: str) -> Any:
    REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_code}'
    auth = self._authorize_not_super_admin(request)
    if auth is not True:
      return auth
    try:
      try:
        pid = decrypt(plan_id)
      except BadSignature as e:
        return default_undefined_exception(
          request,
          err=e,
          ref=REF
        )
      plan = Plan.objects.filter(id=pid).first()
      if not plan:
        messages.error(request, 'Something went wrong.')
        return redirect(get_redirect_url(request))
      return render(request, 'plan_request/show.html', {'plan': plan})
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF
      )

  def user_request(self, request: HttpRequest, plan_id: str) -> Any:
    REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_code}'
    auth = self._authorize_not_super_admin(request)
    if auth is not True:
      return auth
    try:
      if getattr(request.user, 'requested_plan', None) != 0:
        messages.error(request, 'You already sent request to another plan.')
        return redirect(get_redirect_url(request))
      try:
        pid = decrypt(plan_id)
      except BadSignature as e:
        return default_undefined_exception(
          request,
          err=e,
          ref=REF
        )
      plan = Plan.objects.filter(id=pid).first()
      if not plan:
        messages.error(request, 'Something went wrong.')
        return redirect(get_redirect_url(request))
      PlanRequest.objects.create(
        user=request.user,
        plan=plan,
        duration=plan.duration
      )
      request.user.requested_plan = pid
      request.user.save()
      messages.success(request, 'Request Send Successfully.')
      return redirect(get_redirect_url(request))
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF
      )

  def accept_request(self, request: HttpRequest, id: str, response: str) -> Any:
    REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_code}'
    auth = self._authorize_super_admin(request)
    if auth is not True:
      return auth
    try:
      pr = PlanRequest.objects.filter(id=id).first()
      if not pr:
        messages.error(request, 'Something went wrong.')
        return redirect(get_redirect_url(request))
      user = User.objects.filter(id=pr.user_id).first()
      if not user:
        messages.error(request, 'Something went wrong.')
        return redirect(get_redirect_url(request))
      if response == '1':
        user.requested_plan = 0
        user.plan = pr.plan_id
        user.save()
        plan = pr.plan
        result = user.assign_plan(plan.id, user.id)
        price = plan.price
        setting = Utility.get_admin_payment_setting()
        if result.get('is_success') and plan:
          if getattr(user, 'payment_subscription_id', None):
            try:
              user.cancel_subscription(user.id)
            except Exception:
              pass
          import uuid
          oid = uuid.uuid4().hex.upper()
          Order.objects.create(
            order_id=oid,
            name=None,
            email=None,
            card_number=None,
            card_exp_month=None,
            card_exp_year=None,
            plan_name=plan.name,
            plan=plan,
            price=price,
            price_currency=setting.get('currency', 'USD'),
            txn_id='',
            payment_type='Manually Upgrade By Super Admin',
            payment_status='success',
            receipt=None,
            user=user
          )
          pr.delete()
          messages.success(request, 'Plan successfully upgraded.')
          return redirect(get_redirect_url(request))
        messages.error(request, 'Plan fail to upgrade.')
        return redirect(get_redirect_url(request))
      else:
        user.requested_plan = 0
        user.save()
        pr.delete()
        messages.success(request, 'Request Rejected Successfully.')
        return redirect(get_redirect_url(request))
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF
      )

  def cancel_request(self, request: HttpRequest, id: str) -> Any:
    REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_code}'
    auth = self._authorize_not_super_admin(request)
    if auth is not True:
      return auth
    try:
      user = User.objects.filter(id=id).first()
      if user:
        user.requested_plan = 0
        user.save()
      PlanRequest.objects.filter(user_id=id).delete()
      messages.success(request, 'Request Canceled Successfully.')
      return redirect(get_redirect_url(request))
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF
      )
