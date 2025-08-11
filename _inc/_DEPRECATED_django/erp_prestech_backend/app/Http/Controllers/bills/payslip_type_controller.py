import inspect
from typing import Union
import logging
from django.core.exceptions import PermissionDenied
from django.shortcuts import get_object_or_404, redirect, render
from django.http import HttpRequest, HttpResponse
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._helpers.http import get_redirect_url
from ....Models.bills.payslip_type import PayslipType
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class PayslipTypeController(Controller):
  
  @classmethod
  def _set_auth(cls, request: HttpRequest, perm:str) -> Union[Exception, bool]:
      self = cls()
      self.request = request
      self.authorize(f'{perm} payslip type')
      return True
  
  @classmethod
  def index(cls, request: HttpRequest) -> HttpResponse:
    REF =f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PayslipTypeController._set_auth(request, 'manage')
      pts = PayslipType.objects.filter(
        created_by=request.user.creator_id()
      ).all()
      return render(
        request,
        'paysliptype/index.html',
        {'paysliptypes': pts}
      )
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def create(cls, request: HttpRequest) -> HttpResponse:
    REF =f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PayslipTypeController._set_auth(request, 'create')
      return render(request,'paysliptype/create.html',{})
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e,
        ref=REF,
        logger=logger, json={}
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def store(cls, request: HttpRequest) -> HttpResponse:
    REF =f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PayslipTypeController._set_auth(request, 'create')
      name = request.POST.get('name')
      if not name or len(name) > 20:
        from django.contrib import messages
        messages.error(request,'Name is required and max 20 characters.')
        return redirect(get_redirect_url(request))  # TODO: refine validation logic
      pt = PayslipType(
        name=name,
        created_by=request.user.creator_id()
      )
      pt.save()
      from django.contrib import messages
      messages.success(request,'PayslipType successfully created.')
      return redirect('paysliptype_index')
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def show(cls, request: HttpRequest, paysliptype_id) -> HttpResponse:
    REF =f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      return redirect('paysliptype_index')
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def edit(cls, request: HttpRequest, paysliptype_id) -> HttpResponse:
    REF =f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PayslipTypeController._set_auth(request, 'edit')
      pt = get_object_or_404(PayslipType, pk=paysliptype_id)
      if pt.created_by != request.user.creator_id():
        raise PermissionDenied()
      return render(
        request,
        'paysliptype/edit.html',
        {'paysliptype': pt}
      )
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e,
        ref=REF,
        logger=logger, json={}
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def update(cls, request: HttpRequest, paysliptype_id) -> HttpResponse:
    REF =f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PayslipTypeController._set_auth(request, 'edit')
      pt = get_object_or_404(PayslipType, pk=paysliptype_id)
      if pt.created_by != request.user.creator_id():
        raise PermissionDenied()
      name = request.POST.get('name')
      if not name or len(name) > 20:
        from django.contrib import messages
        messages.error(request,'Name is required and max 20 characters.')
        return redirect(get_redirect_url(request))
      pt.name = name
      pt.save()
      from django.contrib import messages
      messages.success(request,'PayslipType successfully updated.')
      return redirect('paysliptype_index')
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e,
        ref=REF,
        logger=logger, json={}
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def destroy(cls, request: HttpRequest, paysliptype_id) -> HttpResponse:
    REF =f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PayslipTypeController._set_auth(request, 'delete')
      pt = get_object_or_404(PayslipType, pk=paysliptype_id)
      if pt.created_by != request.user.creator_id():
        raise PermissionDenied()
      pt.delete()
      from django.contrib import messages
      messages.success(request,'PayslipType successfully deleted.')
      return redirect('paysliptype_index')
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e,
        ref=REF,
        logger=logger, json={}
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=REF,
        logger=logger
      )
