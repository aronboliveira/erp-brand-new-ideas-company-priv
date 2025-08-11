import inspect
import json
import logging
import time
from json import JSONDecodeError
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse, HttpResponseNotFound
from django.shortcuts import redirect, render
from .....Http.Controllers._traits.controller import Controller
from ...Entities.landing_page_setting import LandingPageSetting
from .....Http.Controllers._helpers.error_handlers import default_permission_denial, default_undefined_exception

logger = logging.getLogger(__name__)


class FeaturesController(Controller):
  def _parse_list(self, settings_dict: dict, key: str) -> list:
    try:
      return json.loads(settings_dict.get(key, '[]'))
    except JSONDecodeError as e:
      logger.error(f"{self.__class__.__name__}::_parse_list failed for {key}: {e}")
      return []

  def _upload_file(self, request: HttpRequest, field: str,
                   prefix: str) -> str:
    file = request.FILES.get(field)
    if not file: return ''
    ext = file.name.rsplit('.', 1)[-1]
    name = f"{int(time.time())}-{prefix}.{ext}"
    path = 'uploads/landing_page_image'
    try:
      res = LandingPageSetting.upload_file(file, path, name)
      if res.get('flag') != 1:
        messages.error(request, res.get('msg', 'Upload failed'))
        return ''
      return name
    except Exception as e:
      logger.error(f"{self.__class__.__name__}::_upload_file failed: {e}")
      messages.error(request, 'An error occurred.')
      return ''

  def index(self, request: HttpRequest) -> HttpResponse:
    CN = self.__class__.__name__ 
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('view_features')
      sd = LandingPageSetting.settings()
      fof = self._parse_list(sd, 'feature_of_features')
      of = self._parse_list(sd, 'other_features')
      return render(request, 'landingpage/features/index.html', {
        'settings': sd,
        'feature_of_features': fof,
        'other_features': of
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
      self.authorize('create_features')
      return render(request, 'landingpage/create.html')
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
      self.authorize('create_features')
      data = {
        'feature_status': 'on'
          if request.POST.get('feature_status') else 'off',
        'feature_title': request.POST.get('feature_title', ''),
        'feature_heading': request.POST.get('feature_heading', ''),
        'feature_description':
          request.POST.get('feature_description', ''),
        'feature_buy_now_link':
          request.POST.get('feature_buy_now_link', '')
      }
      for k, v in data.items(): LandingPageSetting.set_value(k, v)
      messages.success(request, 'Setting updated successfully.')
      return redirect('features_index')
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
    return redirect('features_index')

  def destroy(self, request: HttpRequest, id: int) -> HttpResponse:
    return redirect('features_index')

  def feature_create(self, request: HttpRequest) -> HttpResponse:
    CN = self.__class__.__name__ 
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('create_feature_item')
      return render(request, 'landingpage/features/create.html', {
        'settings': LandingPageSetting.settings()
      })
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def feature_store(self, request: HttpRequest) -> HttpResponse:
    CN = self.__class__.__name__
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('create_feature_item')
      sd = LandingPageSetting.settings()
      items = self._parse_list(sd, 'feature_of_features')
      entry = {}
      logo = self._upload_file(request, 'feature_logo',
        'feature_logo')
      if logo: entry['feature_logo'] = logo
      entry['feature_heading'] = request.POST.get('feature_heading', '')
      entry['feature_description'] = request.POST.get(
        'feature_description', ''
      )
      items.append(entry)
      LandingPageSetting.set_value('feature_of_features',
        json.dumps(items))
      messages.success(request, 'Feature added successfully.')
      return redirect('features_index')
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def feature_edit(self, request: HttpRequest, key: str
                  ) -> HttpResponse:
    CN = self.__class__.__name__ 
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('update_feature_item')
      sd = LandingPageSetting.settings()
      items = self._parse_list(sd, 'feature_of_features')
      idx = int(key)
      feature = items[idx]
      return render(request, 'landingpage/features/edit.html', {
        'feature': feature, 'key': idx
      })
    except (ValueError, IndexError) as e:
      logger.error(f"{CN}::{MN} invalid key: {e}")
      return HttpResponseNotFound("Feature item not found.")
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def feature_update(self, request: HttpRequest, key: str
                    ) -> HttpResponse:
    CN = self.__class__.__name__ 
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('update_feature_item')
      sd = LandingPageSetting.settings()
      items = self._parse_list(sd, 'feature_of_features')
      idx = int(key)
      if idx < 0 or idx >= len(items):
        return HttpResponseNotFound("Feature item not found.")
      logo = self._upload_file(request, 'feature_logo',
        'feature_logo')
      if logo: items[idx]['feature_logo'] = logo
      items[idx].update({
        'feature_heading': request.POST.get(
          'feature_heading', ''
        ),
        'feature_description': request.POST.get(
          'feature_description', ''
        )
      })
      LandingPageSetting.set_value('feature_of_features',
        json.dumps(items))
      messages.success(request, 'Feature updated successfully.')
      return redirect('features_index')
    except (ValueError, IndexError) as e:
      logger.error(f"{CN}::{MN} invalid key: {e}")
      return HttpResponseNotFound("Feature item not found.")
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def feature_delete(self, request: HttpRequest, key: str
                    ) -> HttpResponse:
    CN = self.__class__.__name__ 
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('delete_feature_item')
      sd = LandingPageSetting.settings()
      items = self._parse_list(sd, 'feature_of_features')
      idx = int(key)
      items.pop(idx)
      LandingPageSetting.set_value('feature_of_features',
        json.dumps(items))
      messages.success(request, 'Feature deleted successfully.')
      return redirect('features_index')
    except (ValueError, IndexError) as e:
      logger.error(f"{CN}::{MN} invalid key: {e}")
      return HttpResponseNotFound("Feature item not found.")
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def feature_highlight_create(self, request: HttpRequest
                              ) -> HttpResponse:
    CN = self.__class__.__name__ 
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('update_features')
      logo = self._upload_file(request,
        'highlight_feature_image',
        'highlight_feature_image'
      )
      data = {
        'highlight_feature_heading':
          request.POST.get('highlight_feature_heading', ''),
        'highlight_feature_description':
          request.POST.get('highlight_feature_description', '')
      }
      if logo: data['highlight_feature_image'] = logo
      for k, v in data.items(): LandingPageSetting.set_value(k, v)
      messages.success(request, 'Setting updated successfully.')
      return redirect('features_index')
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def features_create(self, request: HttpRequest
                     ) -> HttpResponse:
    CN = self.__class__.__name__ 
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('create_other_feature')
      return render(request,
        'landingpage/features/features_create.html',
        {'settings': LandingPageSetting.settings()}
      )
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def features_store(self, request: HttpRequest
                     ) -> HttpResponse:
    CN = self.__class__.__name__ 
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('create_other_feature')
      sd = LandingPageSetting.settings()
      items = self._parse_list(sd, 'other_features')
      entry = {}
      img = self._upload_file(request,
        'other_features_image',
        'other_features_image'
      )
      if img: entry['other_features_image'] = img
      entry.update({
        'other_features_heading':
          request.POST.get('other_features_heading', ''),
        'other_featured_description':
          request.POST.get('other_featured_description', ''),
        'other_feature_buy_now_link':
          request.POST.get('other_feature_buy_now_link', '')
      })
      items.append(entry)
      LandingPageSetting.set_value('other_features',
        json.dumps(items))
      messages.success(request, 'Feature added successfully.')
      return redirect('features_index')
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def features_edit(self, request: HttpRequest, key: str
                    ) -> HttpResponse:
    CN = self.__class__.__name__ 
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('update_other_feature')
      sd = LandingPageSetting.settings()
      items = self._parse_list(sd, 'other_features')
      idx = int(key)
      feat = items[idx]
      return render(request,
        'landingpage/features/features_edit.html',
        {'other_features': feat, 'key': idx}
      )
    except (ValueError, IndexError) as e:
      logger.error(f"{CN}::{MN} invalid key: {e}")
      return HttpResponseNotFound("Other feature item not found.")
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def features_update(self, request: HttpRequest, key: str
                      ) -> HttpResponse:
    CN = self.__class__.__name__ 
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('update_other_feature')
      sd = LandingPageSetting.settings()
      items = self._parse_list(sd, 'other_features')
      idx = int(key)
      if idx < 0 or idx >= len(items):
        return HttpResponseNotFound("Other feature item not found.")
      img = self._upload_file(request,
        'other_features_image',
        'other_features_image'
      )
      if img: items[idx]['other_features_image'] = img
      items[idx].update({
        'other_features_heading':
          request.POST.get('other_features_heading', ''),
        'other_featured_description':
          request.POST.get('other_featured_description', ''),
        'other_feature_buy_now_link':
          request.POST.get('other_feature_buy_now_link', '')
      })
      LandingPageSetting.set_value('other_features',
        json.dumps(items))
      messages.success(request, 'Feature updated successfully.')
      return redirect('features_index')
    except (ValueError, IndexError) as e:
      logger.error(f"{CN}::{MN} invalid key: {e}")
      return HttpResponseNotFound("Other feature item not found.")
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)

  def features_delete(self, request: HttpRequest, key: str
                      ) -> HttpResponse:
    CN = self.__class__.__name__ 
    MN = inspect.currentframe().f_code.co_name
    try:
      self.authorize('delete_other_feature')
      sd = LandingPageSetting.settings()
      items = self._parse_list(sd, 'other_features')
      idx = int(key)
      items.pop(idx)
      LandingPageSetting.set_value('other_features',
        json.dumps(items))
      messages.success(request, 'Features deleted successfully.')
      return redirect('features_index')
    except (ValueError, IndexError) as e:
      logger.error(f"{CN}::{MN} invalid key: {e}")
      return HttpResponseNotFound("Other feature item not found.")
    except PermissionDenied as e:
      return default_permission_denial(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e,
        ref=f'{CN}::{MN}', logger=logger)
