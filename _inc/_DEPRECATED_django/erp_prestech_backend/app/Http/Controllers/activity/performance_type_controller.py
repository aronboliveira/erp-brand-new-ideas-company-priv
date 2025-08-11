import inspect
import logging
from typing import Union
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.shortcuts import render, redirect, get_object_or_404
from django.http import HttpRequest, HttpResponse
from .._traits.controller import Controller
from ....Models.activity.performance_type import PerformanceType
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception

logger = logging.getLogger(__name__)

class PerformanceTypeController(Controller):
  
  @classmethod
  def _set_auth(cls, request: HttpRequest, perm:str) -> Union[bool, Exception]:
    self = cls()
    self.request = request
    self.authorize(f'{perm} performance type')
    return True

  @classmethod
  def index(cls, request: HttpRequest) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PerformanceTypeController._set_auth(request, 'manage')
      if request.user.type != 'company':
        messages.error(request,'Permission denied.') 
        return redirect(get_redirect_url(request))
      types = PerformanceType.objects.filter(created_by=request.user.creator_id())
      return render(request,'performanceType/index.html',{'types':types})
    except PermissionDenied as err:
      return default_permission_denial(request,err,ref=REF)
    except Exception as err:
      return default_undefined_exception(request,err,ref=REF)

  @classmethod
  def create(cls, request: HttpRequest) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PerformanceTypeController._set_auth(request, 'create')
      return render(request,'performanceType/create.html')
    except PermissionDenied as err:
      return default_permission_denial(request,err,ref=REF)
    except Exception as err:
      return default_undefined_exception(request,err,ref=REF)

  @classmethod
  def store(cls, request: HttpRequest) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PerformanceTypeController._set_auth(request, 'create')
      if request.user.type != 'company':
        messages.error(request,'Permission denied.') 
        return redirect(get_redirect_url(request))
      name = request.POST.get('name')
      if not name:
        messages.error(request,'Name is required.') 
        return redirect(get_redirect_url(request))
      PerformanceType.objects.create(
        name=name,created_by=request.user.creator_id()
      )
      messages.success(request,'Performance Type successfully created.')
      return redirect('performance_type_index')
    except PermissionDenied as err:
      return default_permission_denial(request,err,ref=REF)
    except Exception as err:
      return default_undefined_exception(request,err,ref=REF)

  @classmethod
  def show(cls, request: HttpRequest, performance_type_id) -> HttpResponse:
    # no explicit show view redirect back
    return redirect(get_redirect_url(request))

  @classmethod
  def edit(cls, request: HttpRequest, performance_type_id) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PerformanceTypeController._set_auth(request, 'edit')
      performance_type = get_object_or_404(PerformanceType,pk=performance_type_id)
      return render(request,'performanceType/edit.html',{'performanceType':performance_type})
    except PermissionDenied as err:
      return default_permission_denial(request,err,ref=REF)
    except Exception as err:
      return default_undefined_exception(request,err,ref=REF)

  @classmethod
  def update(cls, request: HttpRequest, performance_type_id) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PerformanceTypeController._set_auth(request, 'edit')
      performance_type = get_object_or_404(PerformanceType,pk=performance_type_id)
      if request.user.type != 'company':
        messages.error(request,'Permission denied.') 
        return redirect(get_redirect_url(request))
      name = request.POST.get('name')
      if not name:
        messages.error(request,'Name is required.') 
        return redirect(get_redirect_url(request))
      performance_type.name = name
      performance_type.created_by = request.user.creator_id()
      performance_type.save()
      messages.success(request,'Performance Type successfully updated.')
      return redirect('performance_type_index')
    except PermissionDenied as err:
      return default_permission_denial(request,err,ref=REF)
    except Exception as err:
      return default_undefined_exception(request,err,ref=REF)

  @classmethod
  def destroy(cls, request: HttpRequest, performance_type_id) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PerformanceTypeController._set_auth(request, 'delete')
      performance_type = get_object_or_404(PerformanceType,pk=performance_type_id)
      if request.user.type != 'company':
        messages.error(request,'Permission denied.') 
        return redirect(get_redirect_url(request))
      performance_type.delete()
      messages.success(request,'Performance Type successfully deleted.')
      return redirect('performance_type_index')
    except PermissionDenied as err:
      return default_permission_denial(request,err,ref=REF)
    except Exception as err:
      return default_undefined_exception(request,err,ref=REF)
