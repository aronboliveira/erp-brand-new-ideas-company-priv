import json
import inspect
import logging
from django.http import HttpResponseNotFound, HttpRequest, HttpResponse
from django.shortcuts import render, redirect
from django.contrib import messages
from ...Entities.landing_page_setting import LandingPageSetting
from .....Http.Controllers._traits.controller import Controller
from .....Http.Controllers._helpers.error_handlers import default_permission_denial, default_undefined_exception

logger = logging.getLogger(__name__)

class FaqController(Controller):

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.is_superuser:
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            settings_dict = LandingPageSetting.settings()
            try:
                faqs = json.loads(settings_dict.get('faqs', '[]'))
            except json.JSONDecodeError:
                faqs = []
            return render(request, 'landingpage/faq/index.html', {
                'settings': settings_dict,
                'faqs': faqs
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            return render(request, 'landingpage/faq/create.html')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def store(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if request.method != 'POST':
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            data = {
                'faq_status': 'on' if request.POST.get('faq_status') else 'off',
                'faq_title': request.POST.get('faq_title', ''),
                'faq_heading': request.POST.get('faq_heading', ''),
                'faq_description': request.POST.get('faq_description', '')
            }
            for key, value in data.items():
                LandingPageSetting.set_value(key, value)
            messages.success(request, 'Setting updated successfully.')
            return redirect('faq_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def show(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            return render(request, 'landingpage/show.html')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def edit(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            return render(request, 'landingpage/edit.html')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def update(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            return redirect('faq_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def destroy(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            return redirect('faq_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def faq_create(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            settings_dict = LandingPageSetting.settings()
            return render(request, 'landingpage/faq/create.html', {
                'settings': settings_dict
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def faq_store(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if request.method != 'POST':
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            settings_dict = LandingPageSetting.settings()
            try:
                faq_list = json.loads(settings_dict.get('faqs', '[]'))
            except json.JSONDecodeError:
                faq_list = []
            entry = {
                'faq_questions': request.POST.get('faq_questions', ''),
                'faq_answer': request.POST.get('faq_answer', '')
            }
            faq_list.append(entry)
            LandingPageSetting.set_value('faqs', json.dumps(faq_list))
            messages.success(request, 'Faq added successfully.')
            return redirect('faq_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def faq_edit(cls, request: HttpRequest, key: str) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            settings_dict = LandingPageSetting.settings()
            try:
                faq_list = json.loads(settings_dict.get('faqs', '[]'))
            except json.JSONDecodeError:
                faq_list = []
            idx = int(key)
            faq = faq_list[idx]
            return render(request, 'landingpage/faq/edit.html', {
                'faq': faq,
                'key': idx
            })
        except (ValueError, IndexError):
            return HttpResponseNotFound("Faq item not found.")
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def faq_update(cls, request: HttpRequest, key: str) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if request.method != 'POST':
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            settings_dict = LandingPageSetting.settings()
            try:
                faq_list = json.loads(settings_dict.get('faqs', '[]'))
            except json.JSONDecodeError:
                faq_list = []
            idx = int(key)
            if not (0 <= idx < len(faq_list)):
                return HttpResponseNotFound("Faq item not found.")
            faq_list[idx].update({
                'faq_questions': request.POST.get('faq_questions', ''),
                'faq_answer': request.POST.get('faq_answer', '')
            })
            LandingPageSetting.set_value('faqs', json.dumps(faq_list))
            messages.success(request, 'Faq updated successfully.')
            return redirect('faq_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def faq_delete(cls, request: HttpRequest, key: str) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if request.method != 'POST':
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            settings_dict = LandingPageSetting.settings()
            try:
                faq_list = json.loads(settings_dict.get('faqs', '[]'))
            except json.JSONDecodeError:
                faq_list = []
            idx = int(key)
            if not (0 <= idx < len(faq_list)):
                return HttpResponseNotFound("Faq item not found.")
            faq_list.pop(idx)
            LandingPageSetting.set_value('faqs', json.dumps(faq_list))
            messages.success(request, 'Faq deleted successfully.')
            return redirect('faq_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
