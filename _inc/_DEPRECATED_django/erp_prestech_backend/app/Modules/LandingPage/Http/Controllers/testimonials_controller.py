import inspect
import json
import logging
import time

from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse, HttpResponseNotFound
from django.shortcuts import redirect, render

from ...Entities.landing_page_setting import LandingPageSetting
from .....Http.Controllers._traits.controller import Controller
from .....Http.Controllers._helpers.error_handlers import default_permission_denial, default_undefined_exception

logger = logging.getLogger(__name__)


class TestimonialsController(Controller):
  def index(self, request: HttpRequest) -> HttpResponse:
    CN = self.__class__.__name__
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('view_testimonials')
      if not request.user.is_superuser:
        raise PermissionDenied('Permission denied.')
      settings_dict = LandingPageSetting.landing_page_setting()
      testimonials = json.loads(settings_dict.get('testimonials', '[]'))
      return render(request, 'landingpage/testimonials/index.html', {
        'settings': settings_dict,
        'testimonials': testimonials
      })
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def create(self, request: HttpRequest) -> HttpResponse:
    CN = self.__class__.__name__
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('create_testimonials')
      if not request.user.is_superuser:
        raise PermissionDenied('Permission denied.')
      return render(request, 'landingpage/testimonials/create.html')
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def store(self, request: HttpRequest) -> HttpResponse:
    CN = self.__class__.__name__
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('update_testimonials')
      if request.method != 'POST':
        return redirect('testimonials_index')
      referer = request.META.get('HTTP_REFERER', 'testimonials_index')
      data = {
        'testimonials_status': 'on',
        'testimonials_heading': request.POST.get(
          'testimonials_heading', ''),
        'testimonials_description': request.POST.get(
          'testimonials_description', ''),
        'testimonials_long_description': request.POST.get(
          'testimonials_long_description', '')
      }
      for k, v in data.items():
        LandingPageSetting.set_value(k, v)
      messages.success(request, 'Setting updated successfully.')
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
    return redirect('testimonials_index')

  def destroy(self, request: HttpRequest, id: int) -> HttpResponse:
    return redirect('testimonials_index')

  def testimonials_create(self, request: HttpRequest) -> HttpResponse:
    CN = self.__class__.__name__
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('create_testimonials')
      settings_dict = LandingPageSetting.settings()
      return render(request,
        'landingpage/testimonials/create.html',
        {'settings': settings_dict})
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def testimonials_store(self, request: HttpRequest) -> HttpResponse:
    CN = self.__class__.__name__
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('create_testimonials')
      if request.method != 'POST':
        return redirect('testimonials_index')
      referer = request.META.get('HTTP_REFERER', 'testimonials_index')
      settings_dict = LandingPageSetting.settings()
      items = json.loads(settings_dict.get('testimonials', '[]'))
      entry = {
        'testimonials_title': request.POST.get(
          'testimonials_title', ''),
        'testimonials_description': request.POST.get(
          'testimonials_description', ''),
        'testimonials_user': request.POST.get(
          'testimonials_user', ''),
        'testimonials_designation': request.POST.get(
          'testimonials_designation', ''),
        'testimonials_star': request.POST.get(
          'testimonials_star', '')
      }
      if 'testimonials_user_avtar' in request.FILES:
        ext = request.FILES['testimonials_user_avtar'].name.split('.')[-1]
        fn = f"{int(time.time())}-testimonials_user_avtar.{ext}"
        res = LandingPageSetting.upload_file(
          request, 'testimonials_user_avtar', fn,
          'uploads/landing_page_image', [])
        if res.get('flag') == 0:
          messages.error(request, res.get('msg'))
          return redirect(referer)
        entry['testimonials_user_avtar'] = fn
      items.append(entry)
      LandingPageSetting.set_value('testimonials',
        json.dumps(items))
      messages.success(request, 'Testimonial added successfully.')
      return redirect(referer)
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def testimonials_edit(self, request: HttpRequest,
                        key: str) -> HttpResponse:
    CN = self.__class__.__name__
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('view_testimonials')
      settings_dict = LandingPageSetting.settings()
      items = json.loads(settings_dict.get('testimonials', '[]'))
      idx = int(key)
      entry = items[idx]
      return render(request,
        'landingpage/testimonials/edit.html', {
        'testimonial': entry,
        'key': key
      })
    except ValueError | IndexError:
      return HttpResponseNotFound("Testimonial not found.")
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def testimonials_update(self, request: HttpRequest,
                          key: str) -> HttpResponse:
    CN = self.__class__.__name__
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('update_testimonials')
      if request.method != 'POST':
        return redirect('testimonials_index')
      referer = request.META.get('HTTP_REFERER', 'testimonials_index')
      settings_dict = LandingPageSetting.settings()
      items = json.loads(settings_dict.get('testimonials', '[]'))
      idx = int(key)
      if 'testimonials_user_avtar' in request.FILES:
        ext = request.FILES['testimonials_user_avtar'].name.split('.')[-1]
        fn = f"{int(time.time())}-testimonials_user_avtar.{ext}"
        res = LandingPageSetting.upload_file(
          request, 'testimonials_user_avtar', fn,
          'uploads/landing_page_image', [])
        if res.get('flag') == 0:
          messages.error(request, res.get('msg'))
          return redirect(referer)
        items[idx]['testimonials_user_avtar'] = fn
      for fld in [
        'testimonials_title', 'testimonials_description',
        'testimonials_user', 'testimonials_designation',
        'testimonials_star'
      ]:
        items[idx][fld] = request.POST.get(fld, '')
      LandingPageSetting.set_value('testimonials',
        json.dumps(items))
      messages.success(request, 'Testimonial updated successfully.')
      return redirect(referer)
    except ValueError | IndexError:
      return HttpResponseNotFound("Testimonial not found.")
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def testimonials_delete(self, request: HttpRequest,
                          key: str) -> HttpResponse:
    CN = self.__class__.__name__
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('delete_testimonials')
      if request.method != 'POST':
        return redirect('testimonials_index')
      referer = request.META.get('HTTP_REFERER', 'testimonials_index')
      settings_dict = LandingPageSetting.settings()
      items = json.loads(settings_dict.get('testimonials', '[]'))
      idx = int(key)
      items.pop(idx)
      LandingPageSetting.set_value('testimonials',
        json.dumps(items))
      messages.success(request, 'Testimonial deleted successfully.')
      return redirect(referer)
    except ValueError | IndexError:
      return HttpResponseNotFound("Testimonial not found.")
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
