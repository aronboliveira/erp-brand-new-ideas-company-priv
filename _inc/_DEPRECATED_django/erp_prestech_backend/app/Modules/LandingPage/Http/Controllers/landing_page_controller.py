import inspect
import logging
from django.core.exceptions import PermissionDenied
from django.contrib import messages
from django.http import HttpRequest, HttpResponse
from django.shortcuts import redirect, render
from .....Http.Controllers._traits.controller import Controller
from ...Entities.landing_page_setting import LandingPageSetting
from .....Http.Controllers._helpers.error_handlers import default_permission_denial, default_undefined_exception

logger = logging.getLogger(__name__)


class LandingPageController(Controller):
  def index(self, request: HttpRequest) -> HttpResponse:
    CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('view_landpage')
      if not request.user.is_superuser:
        raise PermissionDenied('Permission denied.')
      return render(request, 'landingpage/landingpage/topbar.html')
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def create(self, request: HttpRequest) -> HttpResponse:
    CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('create_landpage')
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
      self.authorize('create_landpage')
      if request.method != 'POST':
        return redirect('landingpage_index')
      referer = request.META.get('HTTP_REFERER', 'landingpage_index')
      data = {
        'topbar_status': request.POST.get('topbar_status', 'off'),
        'topbar_notification_msg':
          request.POST.get('topbar_notification_msg', '')
      }
      for k, v in data.items():
        LandingPageSetting.set_value(k, v)
      messages.success(request, 'Topbar setting updated successfully.')
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
    return redirect('landingpage_index')

  def destroy(self, request: HttpRequest, id: int) -> HttpResponse:
    return redirect('landingpage_index')
