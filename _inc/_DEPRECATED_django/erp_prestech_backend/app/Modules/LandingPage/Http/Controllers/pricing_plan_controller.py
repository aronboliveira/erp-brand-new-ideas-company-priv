import inspect
import logging
from django.core.exceptions import PermissionDenied
from django.contrib import messages
from django.http import HttpRequest, HttpResponse
from django.shortcuts import render, redirect
from .....Http.Controllers._traits.controller import Controller
from ...Entities.landing_page_setting import LandingPageSetting
from .....Http.Controllers._helpers.error_handlers import default_permission_denial, default_undefined_exception

logger = logging.getLogger(__name__)


class PricingPlanController(Controller):
  def index(self, request: HttpRequest) -> HttpResponse:
    CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('view_pricingplan')
      if not request.user.is_superuser:
        raise PermissionDenied('Permission denied.')
      settings_dict = LandingPageSetting.settings()
      return render(request, 'landingpage/pricing_plan.html',
                    {'settings': settings_dict})
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def create(self, request: HttpRequest) -> HttpResponse:
    CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('create_pricingplan')
      return render(request, 'landingpage/create.html')
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def store(self, request: HttpRequest) -> HttpResponse:
    CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('create_pricingplan')
      if request.method != 'POST':
        return redirect('pricing_plan_index')
      referer = request.META.get('HTTP_REFERER', 'pricing_plan_index')
      data = {
        'plan_status': 'on',
        'plan_title': request.POST.get('plan_title', ''),
        'plan_heading': request.POST.get('plan_heading', ''),
        'plan_description': request.POST.get('plan_description', '')
      }
      for k, v in data.items():
        LandingPageSetting.set_value(k, v)
      messages.success(request, 'Plan updated successfully.')
      return redirect(referer)
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def show(self, request: HttpRequest, id: int) -> HttpResponse:
    return render(request, 'landingpage/show.html')

  def edit(self, request: HttpRequest, id: int) -> HttpResponse:
    return render(request, 'landingpage/edit.html')

  def update(self, request: HttpRequest, id: int) -> HttpResponse:
    return redirect('pricing_plan_index')

  def destroy(self, request: HttpRequest, id: int) -> HttpResponse:
    return redirect('pricing_plan_index')
