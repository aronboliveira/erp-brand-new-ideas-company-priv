import json
from datetime import timedelta
from datetime import datetime
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core.exceptions import PermissionDenied
from django.db import transaction
from django.http import JsonResponse, HttpRequest, HttpResponse
from django.shortcuts import redirect, render
from django.urls import reverse
from calendar import HTMLCalendar
from ....Models.planning.holiday import Holiday
from ....Models.utils.utility import Utility
from ....Models.google import GoogleCalendar
from ....Models.notifications import NotificationHandler
from ....configs.messages_templates import get_exception_class_message
import logging

logger = logging.getLogger(__name__)
from .._traits.controller import Controller
class HolidayController(Controller):
  @method_decorator(login_required)
  def get(self, request: HttpRequest, *args, **kwargs) -> HttpResponse:
    try:
      if not request.user.has_perm('holiday.manage'): raise PermissionDenied
      start_date = request.GET.get('start_date') 
      end_date = request.GET.get('end_date') 
      qs = Holiday.objects.filter(created_by=request.user.creator_id)
      qs = qs.filter(date__gte=start_date) if start_date else qs
      qs = qs.filter(date__lte=end_date) if end_date else qs
      return render(request, 'holiday/index.html', {'holidays': qs.order_by('date')})
    except Exception as e:
      logger.exception(f"HolidayController.index: {str(e)}") 
      messages.error(request, "Error loading holidays") 
      return redirect('home')

  @method_decorator(login_required)
  def create(self, request: HttpRequest) -> HttpResponse:
    try:
      return render(request, 'holiday/create.html', {'settings': Utility.settings()}) if request.user.has_perm('holiday.create') else self._permission_denied()
    except Exception as e:
      logger.exception(f"HolidayController.create: {str(e)}") 
      messages.error(request, "Error loading form") 
      return redirect('holiday.index')

  @transaction.atomic
  @method_decorator(login_required)
  def store(self, request: HttpRequest) -> HttpResponse:
    try:
      if not request.user.has_perm('holiday.create'): return self._permission_denied()
      date = request.POST.get('date') 
      end_date = request.POST.get('end_date') 
      occasion = request.POST.get('occasion')
      if not all([date, occasion]): return self._validation_error('Missing required fields')
      
      holiday = Holiday.objects.create(
        date=date, end_date=end_date, occasion=occasion, 
        created_by=request.user.creator_id()
      )
      self._handle_notifications(request, holiday) 
      self._handle_google_sync(request, holiday)
      return redirect('holiday.index')
    except Exception as e:
      logger.exception(f"HolidayController.store: {str(e)}") 
      messages.error(request, "Error creating holiday") 
      return redirect('holiday.create')

  @method_decorator(login_required)
  def edit(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      holiday = Holiday.objects.get(pk=id) 
      return render(request, 'holiday/edit.html', {'holiday': holiday}) if request.user.has_perm('holiday.edit') else self._permission_denied()
    except Holiday.DoesNotExist: return self._not_found()
    except Exception as e:
      logger.exception(f"HolidayController.edit: {str(e)}") 
      messages.error(request, "Error loading holiday") 
      return redirect('holiday.index')

  @transaction.atomic
  @method_decorator(login_required)
  def update(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      holiday = Holiday.objects.get(pk=id)
      if not request.user.has_perm('holiday.edit'): return self._permission_denied()
      holiday.date = request.POST.get('date', holiday.date) 
      holiday.end_date = request.POST.get('end_date', holiday.end_date) 
      holiday.occasion = request.POST.get('occasion', holiday.occasion) 
      holiday.save()
      return redirect('holiday.index')
    except Holiday.DoesNotExist: return self._not_found()
    except Exception as e:
      logger.exception(f"HolidayController.update: {str(e)}") 
      messages.error(request, "Error updating holiday") 
      return redirect('holiday.edit', id=id)

  @transaction.atomic
  @method_decorator(login_required)
  def destroy(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      holiday = Holiday.objects.get(pk=id) 
      holiday.delete() if request.user.has_perm('holiday.delete') else self._permission_denied()
      return redirect('holiday.index')
    except Holiday.DoesNotExist: return self._not_found()
    except Exception as e:
      logger.exception(f"HolidayController.destroy: {str(e)}") 
      messages.error(request, "Error deleting holiday") 
      return redirect('holiday.index')

  @method_decorator(login_required)
  def calendar(self, request: HttpRequest) -> HttpResponse:
    try:
      if not request.user.has_perm('holiday.manage'): return self._permission_denied()
      holidays = Holiday.objects.filter(created_by=request.user.creator_id()) 
      arr_holidays = []
      for h in holidays: arr_holidays.append({
        'id': h.id, 'title': h.occasion, 'start': h.date.isoformat(), 'end': h.end_date.isoformat(),
        'className': 'event-primary', 'url': reverse('holiday.edit', args=[h.id])
      })
      return render(request, 'holiday/calendar.html', {'arr_holidays': json.dumps(arr_holidays), 'transdate': datetime.today()})
    except Exception as e:
      logger.exception(f"HolidayController.calendar: {str(e)}") 
      messages.error(request, "Error loading calendar") 
      return redirect('holiday.index')

  def get_holiday_data(self, request: HttpRequest) -> JsonResponse:
    try:
      cal_type = request.GET.get('calendar_type') 
      data = []
      if cal_type == 'google_calendar': data = GoogleCalendar.fetch_events('holiday')
      else: data = [self._serialize_holiday(h) for h in Holiday.objects.filter(created_by=request.user.creator_id())]
      return JsonResponse(data, safe=False)
    except Exception as e:
      logger.exception(f"HolidayController.get_holiday_data: {str(e)}") 
      return JsonResponse([], safe=False)

  def _serialize_holiday(self, holiday: Holiday) -> dict:
    return {
      'id': holiday.id, 'title': holiday.occasion, 'start': holiday.date.isoformat(),
      'end': (holiday.end_date + timedelta(days=1)).isoformat(), 'className': 'event-primary',
      'textColor': '#51459d', 'url': reverse('holiday.edit', args=[holiday.id]), 'allDay': True
    }

  def _handle_notifications(self, request: HttpRequest, holiday: Holiday) -> None:
    settings = Utility.settings(request.user.creator_id())
    NotificationHandler.send('holiday', {
      'title': holiday.occasion, 'date': holiday.date.isoformat()
    }, settings)

  def _handle_google_sync(self, request: HttpRequest, holiday: Holiday) -> None:
    if request.POST.get('synchronize_type') == 'google_calendar':
      GoogleCalendar.sync_event(holiday, 'holiday')

  def _permission_denied(self) -> HttpResponse:
    messages.error(self.request,get_exception_class_message(PermissionDenied, __class__.__name__)) 
    return redirect('holiday.index')

  def _validation_error(self, msg: str) -> HttpResponse:
    messages.error(self.request,self.request, msg) 
    return redirect('holiday.create')

  def _not_found(self) -> HttpResponse:
    messages.error(self.request, 'Holiday not found') 
    return redirect('holiday.index')