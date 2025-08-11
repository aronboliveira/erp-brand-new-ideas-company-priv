import inspect
import logging
import time
from django.core.exceptions import PermissionDenied
from django.core.files.storage import default_storage
from django.core.signing import BadSignature, Signer
from django.shortcuts import redirect, render
from django.http import HttpRequest, HttpResponse
from django.utils.translation import gettext_lazy as _
from django.contrib import messages
from ....Models.planning.plan import Plan
from ....Models.utils.utility import Utility
from .._helpers.error_handlers import (
  default_permission_denial,
  default_undefined_exception,
)
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class PlanController(Controller):
  def index(self, request: HttpRequest) -> HttpResponse:
    REF = f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      self.request = request
      self.authorize('manage plan')
      plans = Plan.objects.all()
      admin_payment_setting = Utility.get_admin_payment_setting()
      return render(request, 'plans/index.html', {
        'plans': plans,
        'admin_payment_setting': admin_payment_setting,
      })
    except PermissionDenied as err:
      return default_permission_denial(
        request,
        err=err,
        ref=REF,
        logger=logger,
      )
    except Exception as err:
      return default_undefined_exception(
        request,
        err=err,
        ref=REF,
        logger=logger,
      )

  def create(self, request: HttpRequest) -> HttpResponse:
    REF = f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      self.request = request
      self.authorize('create plan')
      arr_duration = {
        'lifetime': _('Lifetime'),
        'month': _('Per Month'),
        'year': _('Per Year'),
      }
      return render(request, 'plans/create.html', {
        'arr_duration': arr_duration,
      })
    except PermissionDenied as err:
      return default_permission_denial(
        request,
        err=err,
        ref=REF,
        logger=logger,
      )
    except Exception as err:
      return default_undefined_exception(
        request,
        err=err,
        ref=REF,
        logger=logger,
      )

  def store(self, request: HttpRequest) -> HttpResponse:
    REF = f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      self.request = request
      self.authorize('create plan')
      data = request.POST
      plan_props = {}
      for k in ('name', 'duration', 'max_users',
                'max_customers', 'max_vendors', 'max_clients',
                'storage_limit'):
        setattr(plan_props, k, data.get(k))
      for k in ('crm', 'hrm', 'account', 'project',
                'pos', 'chatgpt'):
        setattr(plan_props, bool(data.get(f'enable_{k}')))
      plan = Plan(**plan_props, created_by=request.user)
      if request.FILES.get('image'):
        image = request.FILES['image']
        filename = f'plan_{int(time.time())}.{image.name.rsplit(".",1)[-1]}'
        default_storage.save(f'plan_images/{filename}', image)
        plan.image = filename
      plan.save()
      messages.success(request, _('Plan successfully created.'))
      return redirect(request.META.get('HTTP_REFERER', '/'))
    except PermissionDenied as err:
      return default_permission_denial(
        request,
        err=err,
        ref=REF,
        logger=logger,
      )
    except Exception as err:
      return default_undefined_exception(
        request,
        err=err,
        ref=REF,
        logger=logger,
      )

  def edit(self, request: HttpRequest, plan_id: str) -> HttpResponse:
    REF = f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      self.request = request
      self.authorize('edit plan')
      arr_duration = {
        'lifetime': _('Lifetime'),
        'month': _('Per Month'),
        'year': _('Per Year'),
      }
      plan = Plan.objects.get(pk=plan_id)
      return render(request, 'plans/edit.html', {
        'plan': plan,
        'arr_duration': arr_duration,
      })
    except PermissionDenied as err:
      return default_permission_denial(
        request,
        err=err,
        ref=REF,
        logger=logger,
      )
    except Plan.DoesNotExist as err:
      return default_undefined_exception(
        request,
        err=err,
        ref=REF,
        logger=logger,
      )
    except Exception as err:
      return default_undefined_exception(
        request,
        err=err,
        ref=REF,
        logger=logger,
      )

  def update(self, request: HttpRequest, plan_id: str) -> HttpResponse:
    REF = f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      self.request = request
      self.authorize('edit plan')
      plan = Plan.objects.get(pk=plan_id)
      data = request.POST
      for k in ('name', 'duration', 'max_users',
                'max_customers', 'max_vendors', 'max_clients',
                'storage_limit'):
        setattr(plan, k, data.get(k))
      for k in ('crm', 'hrm', 'account', 'project',
                'pos', 'chatgpt'):
        setattr(plan, bool(data.get(f'enable_{k}')))
      if request.FILES.get('image'):
        old = plan.image
        image = request.FILES['image']
        filename = f'plan_{int(time.time())}.{image.name.rsplit(".",1)[-1]}'
        if old:
          default_storage.delete(f'plan_images/{old}')
        default_storage.save(f'plan_images/{filename}', image)
        plan.image = filename
      plan.save()
      messages.success(request, _('Plan successfully updated.'))
      return redirect(request.META.get('HTTP_REFERER', '/'))
    except PermissionDenied as err:
      return default_permission_denial(
        request,
        err=err,
        ref=REF,
        logger=logger,
      )
    except Plan.DoesNotExist as err:
      return default_undefined_exception(
        request,
        err=err,
        ref=REF,
        logger=logger,
      )
    except Exception as err:
      return default_undefined_exception(
        request,
        err=err,
        ref=REF,
        logger=logger,
      )

  def user_plan(self, request: HttpRequest) -> HttpResponse:
    REF = f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      self.request = request
      signer = Signer()
      code = request.GET.get('code')
      plan_id = signer.unsign(code) if code else None
      plan = Plan.objects.get(pk=plan_id)
      if plan.price <= 0:
        request.user.assign_plan(plan.id)
        messages.success(request, _('Plan successfully activated.'))
      else:
        messages.error(request, _('Something is wrong.'))
      return redirect('plans:index')
    except BadSignature as err:
      return default_permission_denial(
        request,
        err=err,
        ref=REF,
        logger=logger,
      )
    except Plan.DoesNotExist as err:
      return default_undefined_exception(
        request,
        err=err,
        ref=REF,
        logger=logger,
      )
    except Exception as err:
      return default_undefined_exception(
        request,
        err=err,
        ref=REF,
        logger=logger,
      )
