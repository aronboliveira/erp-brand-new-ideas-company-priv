import inspect
import json
import logging
from django.core.exceptions import PermissionDenied
from django.db.models import Q
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import render, redirect
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception 
from .._helpers.http import get_redirect_url
from .._traits.controller import Controller
from ....Models.activity.meeting import Meeting
from ....Models.activity.meeting_employee import MeetingEmployee
from ....Models.companies.branch import Branch
from ....Models.companies.department import Department
from ....Models.individuals.employee import Employee
from ....Models.utils.utility import Utility

logger = logging.getLogger(__name__)

class MeetingController(Controller):
  @classmethod
  def _set_meeting(cls, request: HttpRequest) -> Meeting:
    meeting = Meeting()
    data = {
      'branch_id': request.POST.get('branch_id'),
      'department_id': json.dumps(request.POST.getlist('department_id')),
      'employee_id': json.dumps(request.POST.getlist('employee_id')),
      'title': request.POST.get('title'),
      'date': request.POST.get('date'),
      'time': request.POST.get('time'),
      'note': request.POST.get('note'),
      'created_by': request.user.creator_id(),
    }
    for attr, val in data.items(): setattr(meeting, attr, val)
    return meeting

  @classmethod
  def index(cls, request: HttpRequest) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'  
    self = cls()
    self.request = request
    try:
      self.authorize('manage_meeting')
      employees = Employee.objects.all()
      meetings = (Meeting.objects.order_by('-id')
        .filter(
          Q(meetingemployee__employee_id=Employee.objects.filter(user_id=request.user.id).first().id) |
          (Q(department_id__contains=['0']) & Q(employee_id__contains=['0']))
        ).distinct() if request.user.type == 'Employee'
        else Meeting.objects.filter(created_by=request.user.creator_id())
      )
      return render(request, 'meeting/index.html', {'meetings': meetings, 'employees': employees})
    except PermissionDenied as e:
      return default_permission_denial(
        request,
        err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def create(cls, request: HttpRequest) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls()
    self.request = request
    try:
      self.authorize('create_meeting')
      qs_employees = Employee.objects.filter(created_by=request.user.creator_id())
      employees = (qs_employees.exclude(user_id=request.user.id)
        if request.user.type == 'Employee' else qs_employees
      )
      departments = Department.objects.filter(created_by=request.user.creator_id())
      branch = Branch.objects.filter(created_by=request.user.creator_id())
      settings = Utility.settings(request.user.creator_id())
      return render(
        request,
        'meeting/create.html',
        {'employees': employees, 'departments': departments, 'branch': branch, 'settings': settings}
      )
    except PermissionDenied as e:
      return default_permission_denial(request, err=e, ref=REF, logger=logger, json={'error': 'Permission denied.'})
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def store(cls, request: HttpRequest) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls()
    self.request = request
    try:
      self.authorize('create_meeting')
      meeting = cls._set_meeting(request) 
      meeting.save()
      emp_ids = json.loads(meeting.employee_id)
      if '0' in emp_ids:
        emp_ids = list(
          Employee.objects
            .filter(department_id__in=json.loads(meeting.department_id))
            .values_list('id', flat=True)
        )
      for emp in emp_ids:
        me = MeetingEmployee() 
        setattr(me, 'meeting_id', meeting.id) 
        setattr(me, 'employee_id', emp)
        setattr(me, 'created_by', request.user.creator_id()) 
        me.save()
      setting = Utility.settings(request.user.creator_id())
      branch_obj = Branch.objects.get(id=meeting.branch_id)
      note = {
        'meeting_title': meeting.title,
        'branch_name': branch_obj.name,
        'meeting_date': meeting.date,
        'meeting_time': meeting.time,
      }
      if setting.get('support_notification') == 1: Utility.send_slack_msg('new_meeting', note)
      if setting.get('telegram_meeting_notification') == 1: Utility.send_telegram_msg('new_meeting', note)
      if request.GET.get('synchronize_type') == 'google_calendar':
        cal = Meeting() 
        setattr(cal, 'title', meeting.title)
        setattr(cal, 'start_date', meeting.date) 
        setattr(cal, 'end_date', meeting.date)
        Utility.addCalendarData(cal, 'meeting')
      webhook = Utility.webhookSetting('New Meeting')
      if webhook:
        payload = json.dumps([{'meeting_id': meeting.id, 'employee_id': emp} for emp in emp_ids])
        status = Utility.WebhookCall(webhook['url'], payload, webhook['method'])
        return redirect('meeting.index') if status else redirect(get_redirect_url(request))
      return redirect('meeting.index')
    except PermissionDenied as e:
      return default_permission_denial(
        request,
        err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def show(cls, request: HttpRequest, meeting_id: int) -> HttpResponse:
    return redirect('meeting.index')

  @classmethod
  def edit(cls, request: HttpRequest, meeting_id: int) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls()
    self.request = request
    try:
      self.authorize('edit_meeting')
      meeting = Meeting.objects.get(pk=meeting_id)
      if meeting.created_by != request.user.creator_id(): raise PermissionDenied
      qs = Employee.objects.filter(created_by=request.user.creator_id())
      employees = (qs.exclude(user_id=request.user.id)
        if request.user.type == 'Employee' else qs
      )
      return render(request, 'meeting/edit.html', {'meeting': meeting, 'employees': employees})
    except PermissionDenied as e:
      return default_permission_denial(request, err=e, ref=REF, logger=logger, json={'error': 'Permission denied.'})
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def update(cls, request: HttpRequest, meeting_id: int) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls() 
    self.request = request
    try:
      self.authorize('edit_meeting')
      meeting = Meeting.objects.get(pk=meeting_id)
      if meeting.created_by != request.user.creator_id(): raise PermissionDenied
      for k in ('title', 'date', 'time', 'note'):
        setattr(meeting, k, request.POST.get(k))
      meeting.note = request.POST.get('note')
      meeting.save()
      return redirect('meeting.index')
    except PermissionDenied as e:
      return default_permission_denial(request, err=e, ref=REF, logger=logger)
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def destroy(cls, request: HttpRequest, meeting_id: int) -> HttpResponse:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls() 
    self.request = request
    try:
      self.authorize('delete_meeting')
      meeting = Meeting.objects.get(pk=meeting_id)
      if meeting.created_by != request.user.creator_id(): raise PermissionDenied
      meeting.delete()
      return redirect('meeting.index')
    except PermissionDenied as e:
      return default_permission_denial(request, err=e, ref=REF, logger=logger)
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def get_department(cls, request: HttpRequest) -> JsonResponse:
    self = cls() 
    self.request = request
    try:
      bid = int(request.POST.get('branch_id', 0))
      qs = Department.objects.filter(created_by=request.user.creator_id())
      if bid != 0: qs = qs.filter(branch_id=bid)
      data = dict(qs.values_list('id', 'name'))
      return JsonResponse(data)
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )

  @classmethod
  def get_employee(cls, request: HttpRequest) -> JsonResponse:
    self = cls() 
    self.request = request
    try:
      dept_ids = request.POST.getlist('department_id')
      if '0' in dept_ids:
        qs = Employee.objects.filter(created_by=request.user.creator_id())
      else:
        qs = Employee.objects.filter(created_by=request.user.creator_id(), department_id__in=dept_ids)
      data = dict(qs.values_list('id', 'name'))
      return JsonResponse(data)
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )

  @classmethod
  def calendar(cls, request: HttpRequest) -> HttpResponse:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls() 
    self.request = request
    try:
      self.authorize('manage_meeting')
      transdate = request.GET.get('transdate', None)
      qs = Meeting.objects.filter(created_by=request.user.creator_id())
      start = request.GET.get('start_date') 
      end = request.GET.get('end_date')
      qs = qs.filter(date__gte=start) if start else qs
      qs = qs.filter(date__lte=end) if end else qs
      arr = []
      for m in qs:
        arr.append({
          'id': m.id,
          'title': m.title,
          'start': str(m.date),
          'time': str(m.time),
          'className': 'event-primary',
          'url': f'/meeting/{m.id}/edit',
        })
      return render(request, 'meeting/calendar.html', {'arrMeetings': json.dumps(arr), 'transdate': transdate, 'meetings': qs})
    except PermissionDenied as e:
      return default_permission_denial(request, err=e, ref=REF, logger=logger)
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def get_meeting_data(cls, request: HttpRequest) -> JsonResponse:
    self = cls() 
    self.request = request
    try:
      if request.GET.get('calendar_type') == 'google_calendar':
        data = Utility.getCalendarData('meeting')
      else:
        qs = Meeting.objects.filter(created_by=request.user.creator_id())
        data = [{
          'id': m.id,
          'title': m.title,
          'start': f"{m.date} {m.time}",
          'className': 'event-primary',
          'textColor': '#51459d',
          'url': f'/meeting/{m.id}/edit',
          'allDay': False
        } for m in qs]
      return JsonResponse(data, safe=False)
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
      )
