import inspect
from typing import Union
from django.core.exceptions import PermissionDenied
from django.contrib import messages
from django.http import HttpRequest, HttpResponse
from django.shortcuts import render, get_object_or_404, redirect
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from . import Controller
from ....Models.info.notification_templates import NotificationTemplates
from ....Models.info.notification_template_lang import NotificationTemplateLangs
from ....Models.utils.utility import Utility
from ....Models.shapes.language import Language

class NotificationTemplatesController(Controller):
  
  @classmethod
  def _set_auth(cls, request: HttpRequest, perm:str) -> Union[bool, Exception]:
      self = cls()
      self.request = request 
      self.authorize(f'{perm} notification templates')

  @classmethod
  def index(cls, request: HttpRequest, id=None, lang='en') -> HttpResponse:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      NotificationTemplatesController._set_auth(request, 'view')
      template = NotificationTemplates.objects.filter(pk=id).first() if id else NotificationTemplates.objects.first()
      if not template:
        messages.error(request,'Template does not exist.') 
        return redirect(get_redirect_url(request))
      langs = Utility.languages()
      lang_obj = Language.objects.filter(code=lang).first()
      noti_lang = NotificationTemplateLangs.objects.filter(parent_id=template.id,lang=lang,created_by=request.user.creator_id()).first() \
                 or NotificationTemplateLangs.objects.filter(parent_id=template.id,lang=lang).first() \
                 or NotificationTemplateLangs.objects.filter(parent_id=template.id,lang='en').first()
      all_templates = NotificationTemplates.objects.all()
      return render(request,'notification_templates/index.html',{
        'notification_template': template,
        'notification_templates': all_templates,
        'curr_noti_tempLang': noti_lang,
        'languages': langs,
        'LangName': lang_obj
      })
    except PermissionDenied as err:
      return default_permission_denial(request,err,ref=REF)
    except Exception as err:
      return default_undefined_exception(request,err,ref=REF)

  @classmethod
  def create(cls, request: HttpRequest) -> HttpResponse:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      NotificationTemplatesController._set_auth(request, 'create')
      langs = Utility.languages()
      return render(request,'notification_templates/create.html',{
        'languages': langs
      })
    except PermissionDenied as err:
      return default_permission_denial(request,err,ref=REF)
    except Exception as err:
      return default_undefined_exception(request,err,ref=REF)

  @classmethod
  def store(cls, request: HttpRequest) -> HttpResponse:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      NotificationTemplatesController._set_auth(request, 'create')
      data = request.POST
      name = data.get('name')
      variables = data.get('variables')
      if not name or not variables:
        messages.error(request,'Name and variables are required.') 
        return redirect(get_redirect_url(request))
      NotificationTemplates.objects.create(
        name=name,variables=variables,created_by=request.user.creator_id()
      )
      messages.success(request,'Template successfully created.')
      return redirect(get_redirect_url(request))
    except PermissionDenied as err:
      return default_permission_denial(request,err,ref=REF)
    except Exception as err:
      return default_undefined_exception(request,err,ref=REF)

  @classmethod
  def show(cls, request: HttpRequest, id) -> HttpResponse:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      NotificationTemplatesController._set_auth(request, 'view')
      tpl = get_object_or_404(NotificationTemplates,pk=id)
      langs = Utility.languages()
      return render(request,'notification_templates/show.html',{
        'notification_template': tpl,
        'languages': langs
      })
    except PermissionDenied as err:
      return default_permission_denial(request,err,ref=REF)
    except Exception as err:
      return default_undefined_exception(request,err,ref=REF)

  @classmethod
  def edit(cls, request: HttpRequest, id) -> HttpResponse:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      NotificationTemplatesController._set_auth(request, 'update')
      tpl = get_object_or_404(NotificationTemplates,pk=id)
      langs = Utility.languages()
      return render(request,'notification_templates/edit.html',{
        'notification_template': tpl,
        'languages': langs
      })
    except PermissionDenied as err:
      return default_permission_denial(request,err,ref=REF)
    except Exception as err:
      return default_undefined_exception(request,err,ref=REF)

  @classmethod
  def destroy(cls, request: HttpRequest, id) -> HttpResponse:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      NotificationTemplatesController._set_auth(request, 'delete')
      tpl = get_object_or_404(NotificationTemplates,pk=id)
      tpl.delete()
      messages.success(request,'Template successfully deleted.')
      return redirect(get_redirect_url(request))
    except PermissionDenied as err:
      return default_permission_denial(request,err,ref=REF)
    except Exception as err:
      return default_undefined_exception(request,err,ref=REF)
