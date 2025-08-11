import json
from datetime import datetime
from django.http import HttpRequest, HttpResponse
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core.exceptions import PermissionDenied
from django.db import transaction
from django.db.models import Q
from django.http import JsonResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.urls import reverse
from django.utils.decorators import method_decorator
from ....Models.individuals.user import User
from ....Models.individuals.job_stage import JobStage
from ....Models.individuals.job_application import JobApplication
from ....Models.utils.utility import Utility
from ....Models.planning.interview_schedule import InterviewSchedule
from ....Models.google import GoogleCalendar
from ....configs.messages_templates import get_exception_class_message
import logging

logger = logging.getLogger(__name__)
from .._traits.controller import Controller
class InterviewScheduleController(Controller):
  @method_decorator(login_required)
  def get(self, request: HttpRequest, *args, **kwargs) -> HttpResponse:
    try:
      if not request.user.has_perm('interview.manage'): raise PermissionDenied
      schedules = InterviewSchedule.objects.filter(created_by=request.user.creator_id())
      arr_schedule = [{
        'id': s.id, 'title': s.application.job.title if s.application.job else '',
        'start': s.date.isoformat(), 'className': 'event-primary',
        'url': reverse('interview-schedule-show', args=[s.id])
      } for s in schedules]
      return render(request, 'interviewSchedule/index.html', {
        'arrSchedule': json.dumps(arr_schedule), 
        'transdate': datetime.today()
      })
    except Exception as e:
      logger.exception(f"InterviewSchedule.index: {str(e)}") 
      messages.error(request, "Error loading schedules") 
      return redirect('home')

  @method_decorator(login_required)
  def create(self, request: HttpRequest, candidate_id: int = 0) -> HttpResponse:
    try:
      if not request.user.has_perm('interview.create'): raise PermissionDenied
      employees = User.objects.filter(Q(type='employee') | Q(id=request.user.creator_id()), created_by=request.user.creator_id()).values_list('id', 'name')
      candidates = JobApplication.objects.filter(created_by=request.user.creator_id()).values_list('id', 'name')
      return render(request, 'interviewSchedule/create.html', {
        'employees': dict(employees), 'candidates': dict(candidates),
        'candidate': candidate_id, 'settings': Utility.settings()
      })
    except Exception as e:
      logger.exception(f"InterviewSchedule.create: {str(e)}") 
      messages.error(request, "Error loading form") 
      return redirect('interview-schedule-index')

  @transaction.atomic
  @method_decorator(login_required)
  def store(self, request: HttpRequest) -> HttpResponse:
    try:
      if not request.user.has_perm('interview.create'): return self._permission_denied()
      candidate = request.POST.get('candidate') 
      employee = request.POST.get('employee')
      date = request.POST.get('date') 
      time = request.POST.get('time')
      if not all([candidate, employee, date, time]): return self._validation_error('Missing required fields')
      
      schedule = InterviewSchedule.objects.create(
        candidate_id=candidate, employee_id=employee, date=date, time=time,
        comment=request.POST.get('comment', ''), created_by=request.user.creator_id()
      )
      self._handle_google_sync(request, schedule) 
      return redirect('interview-schedule-index')
    except Exception as e:
      logger.exception(f"InterviewSchedule.store: {str(e)}") 
      messages.error(request, "Error creating schedule") 
      return redirect('interview-schedule-create')

  @method_decorator(login_required)
  def show(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      schedule = get_object_or_404(InterviewSchedule, pk=id)
      stages = JobStage.objects.filter(created_by=request.user.creator_id())
      return render(request, 'interviewSchedule/show.html', {'interviewSchedule': schedule, 'stages': stages})
    except Exception as e:
      logger.exception(f"InterviewSchedule.show: {str(e)}") 
      messages.error(request, "Error loading schedule") 
      return redirect('interview-schedule-index')

  @method_decorator(login_required)
  def edit(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      schedule = get_object_or_404(InterviewSchedule, pk=id)
      employees = User.objects.filter(Q(type='employee') | Q(id=request.user.creator_id()), created_by=request.user.creator_id()).values_list('id', 'name')
      candidates = JobApplication.objects.filter(created_by=request.user.creator_id()).values_list('id', 'name')
      return render(request, 'interviewSchedule/edit.html', {
        'employees': dict(employees), 'candidates': dict(candidates),
        'interviewSchedule': schedule
      })
    except Exception as e:
      logger.exception(f"InterviewSchedule.edit: {str(e)}") 
      messages.error(request, "Error loading schedule") 
      return redirect('interview-schedule-index')

  @transaction.atomic
  @method_decorator(login_required)
  def update(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      schedule = get_object_or_404(InterviewSchedule, pk=id)
      if not request.user.has_perm('interview.edit'): return self._permission_denied()
      schedule.candidate_id = request.POST.get('candidate', schedule.candidate_id)
      schedule.employee_id = request.POST.get('employee', schedule.employee_id)
      schedule.date = request.POST.get('date', schedule.date)
      schedule.time = request.POST.get('time', schedule.time)
      schedule.comment = request.POST.get('comment', schedule.comment)
      schedule.save() 
      return redirect('interview-schedule-index')
    except Exception as e:
      logger.exception(f"InterviewSchedule.update: {str(e)}") 
      messages.error(request, "Error updating schedule") 
      return redirect('interview-schedule-edit', id=id)

  @transaction.atomic
  @method_decorator(login_required)
  def destroy(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      schedule = get_object_or_404(InterviewSchedule, pk=id) 
      schedule.delete()
      return redirect('interview-schedule-index')
    except Exception as e:
      logger.exception(f"InterviewSchedule.destroy: {str(e)}") 
      messages.error(request, "Error deleting schedule") 
      return redirect('interview-schedule-index')

  def get_interview_data(self, request: HttpRequest) -> JsonResponse:
    try:
      cal_type = request.GET.get('calendar_type') 
      data = []
      if cal_type == 'google_calendar': data = GoogleCalendar.fetch_events('interview_schedule')
      else: data = [self._serialize_schedule(s) for s in InterviewSchedule.objects.filter(created_by=request.user.creator_id())]
      return JsonResponse(data, safe=False)
    except Exception as e:
      logger.exception(f"InterviewSchedule.get_data: {str(e)}") 
      return JsonResponse([], safe=False)

  def _serialize_schedule(self, schedule: InterviewSchedule) -> dict:
    return {
      'id': schedule.id, 'title': schedule.comment, 'start': f"{schedule.date}T{schedule.time}",
      'className': 'event-primary', 'textColor': '#51459d', 'url': reverse('interview-schedule-show', args=[schedule.id]), 'allDay': False
    }

  def _handle_google_sync(self, request: HttpRequest, schedule: InterviewSchedule) -> None:
    if request.POST.get('synchronize_type') == 'google_calendar':
      GoogleCalendar.sync_event(schedule, 'interview_schedule')

  def _permission_denied(self) -> HttpResponse:
    messages.error(self.request,get_exception_class_message(PermissionDenied, __class__.__name__)) 
    return redirect('interview-schedule-index')

  def _validation_error(self, msg: str) -> HttpResponse:
    messages.error(self.request, msg) 
    return redirect('interview-schedule-create')