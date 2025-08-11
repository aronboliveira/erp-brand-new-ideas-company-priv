import logging
import inspect
from typing import Optional
from django.http import HttpRequest, HttpResponse
from django.shortcuts import render, redirect, get_object_or_404
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller
from ....Models.contact.email_template import EmailTemplate
from ....Models.contact.email_template_lang import EmailTemplateLang
from ....Models.contact.user_email_template import UserEmailTemplate
from ....Models.shapes.language import Language
from ....Models.utils.utility import Utility

logger = logging.getLogger(__name__)

class EmailTemplateController(Controller):

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            usr = request.user
            if usr.type not in ['super admin', 'company']:
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            templates = EmailTemplate.objects.all()
            return render(request, 'settings/company.html', {'EmailTemplates': templates})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            return render(request, 'email_templates/create.html')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def store(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if request.method != 'POST':
                return redirect(get_redirect_url(request, add_data='Invalid request.'))
            name = request.POST.get('name')
            if not name:
                return redirect(get_redirect_url(request, add_data='Name field is required.'))
            tpl = EmailTemplate()
            cls._set_template(tpl, name, request.user.id)
            return redirect('email_template.index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def show(cls, request: HttpRequest) -> HttpResponse:
        return redirect(get_redirect_url(request, add_data='Permission denied.'))

    @classmethod
    def edit(cls, request: HttpRequest, email_template_id: int) -> HttpResponse:
        return redirect(get_redirect_url(request, add_data='Permission denied.'))

    @classmethod
    def update(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if request.method != 'POST':
                return redirect(get_redirect_url(request, add_data='Invalid request.'))
            from_email = request.POST.get('from')
            subject = request.POST.get('subject')
            content = request.POST.get('content')
            lang = request.POST.get('lang')
            if not from_email or not subject or not content:
                return redirect(get_redirect_url(request, add_data='All fields are required.'))
            tpl = get_object_or_404(EmailTemplate, pk=id)
            tpl.from_email = from_email
            tpl.save()
            etl, _ = EmailTemplateLang.objects.get_or_create(parent_id=id, lang=lang)
            cls._set_lang(etl, subject, content)
            return redirect('manage.email.language', id=id, lang=lang)
        except EmailTemplate.DoesNotExist:
            return redirect(get_redirect_url(request, add_data='Email Template not found.'))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def destroy(cls, request: HttpRequest, email_template_id: int) -> HttpResponse:
        return redirect(get_redirect_url(request, add_data='Permission denied.'))

    @classmethod
    def manage_email_lang(cls, request: HttpRequest, id: int, lang: Optional[str] = 'en') -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            usr = request.user
            if usr.type != 'super admin':
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            langs = Utility.languages()
            lang_name = Language.objects.filter(code=lang).first()
            etl = EmailTemplateLang.objects.filter(parent_id=id, lang=lang).first()
            if not etl:
                return redirect(get_redirect_url(request, add_data='Data not found.'))
            tpl = EmailTemplate.objects.filter(id=id).first()
            all_tpls = EmailTemplate.objects.all()
            return render(request, 'email_templates/show.html', {
                'emailTemplate': tpl,
                'languages': langs,
                'currEmailTempLang': etl,
                'EmailTemplates': all_tpls,
                'LangName': lang_name
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def store_email_lang(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if request.method != 'POST':
                return redirect(get_redirect_url(request, add_data='Invalid request.'))
            subject = request.POST.get('subject')
            content = request.POST.get('content')
            lang = request.POST.get('lang')
            if not subject or not content:
                return redirect(get_redirect_url(request, add_data='Subject and Content are required.'))
            etl, _ = EmailTemplateLang.objects.get_or_create(parent_id=id, lang=lang)
            cls._set_lang(etl, subject, content)
            return redirect('manage.email.language', id=id, lang=lang)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def update_status(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if request.method != 'POST':
                return redirect(get_redirect_url(request, add_data='Invalid request.'))
            usr = request.user
            if usr.type not in ['super admin', 'company']:
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            UserEmailTemplate.objects.filter(user_id=usr.id).update(is_active=0)
            for key, val in request.POST.items():
                if key != '_token':
                    uet = UserEmailTemplate.objects.filter(user_id=usr.id, template_id=key).first()
                    if uet:
                        uet.is_active = val
                        uet.save()
            return redirect(get_redirect_url(request, success='Status successfully updated!'))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def _set_template(cls, obj: EmailTemplate, name: str, user_id: int):
        obj.name = name
        obj.created_by = user_id
        obj.save()

    @classmethod
    def _set_lang(cls, obj: EmailTemplateLang, subject: str, content: str):
        obj.subject = subject
        obj.content = content
        obj.save()
