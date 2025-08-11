# app/Http/Controllers/activity/overtime_controller.py

import inspect
import logging
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.db import transaction
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import get_object_or_404, redirect, render
from ....Models.activity.overtime import Overtime
from ....Models.individuals.employee import Employee
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._helpers.http import get_redirect_url
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class OvertimeController(Controller):
  
  def index(self, request: HttpRequest) -> JsonResponse:
    try:
      try:
        self.authorize('update',['Overtime'])
        can_edit_all = True
      except PermissionDenied:
        can_edit_all = False
      # fetch and serialize
      ots = Overtime.objects.filter(created_by=request.user.creatorId())
      payload = [{
        'id': str(ot.id),
        'employee_id': str(ot.employee_id),
        'title': ot.title,
        'number_of_days': ot.number_of_days,
        'hours': ot.hours,
        'rate': ot.rate,
        'can_edit': can_edit_all
      } for ot in ots]
      return JsonResponse(payload, safe=False)
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e,
        ref=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger,
        json={'error': 'Permission denied.'}
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger,
        json={'error': str(e)}
      )  
  
  def overtime_create(self, request: HttpRequest, eid: str) -> HttpResponse:
    try:
      self.authorize('create',['Overtime'])
      emp = get_object_or_404(Employee, pk=eid)
      return render(request,'overtime/create.html',{'employee':emp})
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e,
        ref=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )

  def store(self, request: HttpRequest) -> HttpResponse:
    try:
      self.authorize('create',['Overtime'])
      data = request.POST
      missing = [f for f in ('employee_id','title','number_of_days','hours','rate') if not data.get(f)]
      if missing:
        messages.error(
          request,
          f"{missing[0].replace('_',' ').capitalize()} is required."
        )
        return redirect(get_redirect_url(request))
      with transaction.atomic():
        ot = Overtime(
          employee_id=data['employee_id'],
          title=data['title'],
          number_of_days=data['number_of_days'],
          hours=data['hours'],
          rate=data['rate'],
          created_by=request.user.creatorId()
        )
        ot.save()
      messages.success(request,'Overtime successfully created.')
      return redirect(get_redirect_url(request))
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e,
        ref=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )

  def show(self, request: HttpRequest, overtime_id: str) -> HttpResponse:
    try:
      # redirects to commission index
      return redirect('commission_index')
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )

  def edit(self, request: HttpRequest, overtime_id: str) -> HttpResponse:
    try:
      self.authorize('update',['Overtime',overtime_id])
      ot = get_object_or_404(Overtime, pk=overtime_id)
      if ot.created_by != request.user.creatorId():
        messages.error(request,'Permission denied.')
        return redirect(get_redirect_url(request))
      return render(request,'overtime/edit.html',{'overtime':ot})
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e,
        ref=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )

  def update(self, request: HttpRequest, overtime_id: str) -> HttpResponse:
    try:
      self.authorize('update',['Overtime',overtime_id])
      ot = get_object_or_404(Overtime, pk=overtime_id)
      if ot.created_by != request.user.creatorId():
        messages.error(request,'Permission denied.')
        return redirect(get_redirect_url(request))
      data = request.POST
      missing = [f for f in ('title','number_of_days','hours','rate') if not data.get(f)]
      if missing:
        messages.error(
          request,
          f"{missing[0].replace('_',' ').capitalize()} is required."
        )
        return redirect(get_redirect_url(request))
      ot.title = data['title']
      ot.number_of_days = data['number_of_days']
      ot.hours = data['hours']
      ot.rate = data['rate']
      ot.save()
      messages.success(request,'Overtime successfully updated.')
      return redirect(get_redirect_url(request))
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e,
        ref=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )

  def destroy(self, request: HttpRequest, overtime_id: str) -> HttpResponse:
    try:
      self.authorize('delete',['Overtime',overtime_id])
      ot = get_object_or_404(Overtime, pk=overtime_id)
      if ot.created_by != request.user.creatorId():
        messages.error(request,'Permission denied.')
        return redirect(get_redirect_url(request))
      ot.delete()
      messages.success(request,'Overtime successfully deleted.')
      return redirect(get_redirect_url(request))
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e,
        ref=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )
