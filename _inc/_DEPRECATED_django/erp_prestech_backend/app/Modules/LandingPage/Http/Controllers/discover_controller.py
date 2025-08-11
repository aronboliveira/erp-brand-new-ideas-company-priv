import json
import inspect
import logging
from django.http import HttpRequest, HttpResponse, HttpResponseNotFound
from django.shortcuts import render, redirect
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from ...Entities.landing_page_setting import LandingPageSetting
from .....Http.Controllers._traits.controller import Controller
from .....Http.Controllers._helpers.error_handlers import default_permission_denial, default_undefined_exception

logger = logging.getLogger(__name__)

class DiscoverController(Controller):

    @classmethod
    def discover_index(cls, request: HttpRequest) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if not request.user.is_superuser:
                raise PermissionDenied('Permission denied.')
            settings_dict = LandingPageSetting.settings()
            features_json = settings_dict.get('discover_of_features', '[]')
            try:
                discover_of_features = json.loads(features_json)
            except json.JSONDecodeError:
                discover_of_features = []
            return render(request, 'landingpage/discover/index.html', {
                'settings': settings_dict,
                'discover_of_features': discover_of_features
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def discover_create_view(cls, request: HttpRequest) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if request.method != 'GET':
                return redirect('discover_index')
            return render(request, 'landingpage/discover/create.html')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def discover_store_view(cls, request: HttpRequest) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if request.method == 'POST':
                data = {
                    'discover_status': 'on',
                    'discover_heading': request.POST.get('discover_heading', ''),
                    'discover_description': request.POST.get('discover_description', ''),
                    'discover_live_demo_link': request.POST.get('discover_live_demo_link', ''),
                    'discover_buy_now_link': request.POST.get('discover_buy_now_link', '')
                }
                for key, value in data.items():
                    LandingPageSetting.set_value(key, value)
                messages.success(request, 'Setting updated successfully.')
            return redirect('discover_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def discover_create(cls, request: HttpRequest) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if request.method != 'GET':
                return redirect('discover_index')
            settings_dict = LandingPageSetting.settings()
            return render(request, 'landingpage/discover/create.html', {
                'settings': settings_dict
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def discover_store(cls, request: HttpRequest) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if request.method == 'POST':
                settings_dict = LandingPageSetting.settings()
                features_json = settings_dict.get('discover_of_features', '[]')
                try:
                    features_list = json.loads(features_json)
                except json.JSONDecodeError:
                    features_list = []
                item = {}
                if 'discover_logo' in request.FILES:
                    ext = request.FILES['discover_logo'].name.rsplit('.', 1)[-1]
                    fname = f"{int(round(__import__('time').time()))}-discover_logo.{ext}"
                    result = LandingPageSetting.upload_file(
                        request, 'discover_logo', fname, 'uploads/landing_page_image', {}
                    )
                    if result.get('flag') == 0:
                        messages.error(request, result.get('msg', ''))
                        return redirect('discover_index')
                    item['discover_logo'] = fname
                for field in ('discover_heading', 'discover_description'):
                    item[field] = request.POST.get(field, '')
                features_list.append(item)
                LandingPageSetting.set_value(
                    'discover_of_features', json.dumps(features_list)
                )
                messages.success(request, 'Discover added successfully.')
            return redirect('discover_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def discover_edit(cls, request: HttpRequest, key) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            settings_dict = LandingPageSetting.settings()
            features_json = settings_dict.get('discover_of_features', '[]')
            try:
                features_list = json.loads(features_json)
            except json.JSONDecodeError:
                features_list = []
            try:
                idx = int(key)
                item = features_list[idx]
            except (IndexError, ValueError):
                return HttpResponseNotFound("Discover item not found.")
            if request.method == 'GET':
                return render(request, 'landingpage/discover/edit.html', {
                    'discover': item,
                    'key': idx
                })
            return redirect('discover_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def discover_update(cls, request: HttpRequest, key) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if request.method == 'POST':
                settings_dict = LandingPageSetting.settings()
                features_json = settings_dict.get('discover_of_features', '[]')
                try:
                    features_list = json.loads(features_json)
                except json.JSONDecodeError:
                    features_list = []
                try:
                    idx = int(key)
                except ValueError:
                    return HttpResponseNotFound("Discover item not found.")
                if not (0 <= idx < len(features_list)):
                    return HttpResponseNotFound("Discover item not found.")
                item = features_list[idx]
                if 'discover_logo' in request.FILES:
                    ext = request.FILES['discover_logo'].name.rsplit('.', 1)[-1]
                    fname = f"{int(round(__import__('time').time()))}-discover_logo.{ext}"
                    result = LandingPageSetting.upload_file(
                        request, 'discover_logo', fname, 'uploads/landing_page_image', {}
                    )
                    if result.get('flag') == 0:
                        messages.error(request, result.get('msg', ''))
                        return redirect('discover_index')
                    item['discover_logo'] = fname
                item['discover_heading'] = request.POST.get('discover_heading', '')
                item['discover_description'] = request.POST.get('discover_description', '')
                features_list[idx] = item
                LandingPageSetting.set_value(
                    'discover_of_features', json.dumps(features_list)
                )
                messages.success(request, 'Discover updated successfully.')
            return redirect('discover_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def discover_delete(cls, request: HttpRequest, key) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if request.method == 'POST':
                settings_dict = LandingPageSetting.settings()
                pages_json = settings_dict.get('discover_of_features', '[]')
                try:
                    pages = json.loads(pages_json)
                except json.JSONDecodeError:
                    pages = []
                try:
                    idx = int(key)
                    pages.pop(idx)
                except (IndexError, ValueError):
                    return HttpResponseNotFound("Discover item not found.")
                LandingPageSetting.set_value(
                    'discover_of_features', json.dumps(pages)
                )
                messages.success(request, 'Discover deleted successfully.')
            return redirect('discover_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)
