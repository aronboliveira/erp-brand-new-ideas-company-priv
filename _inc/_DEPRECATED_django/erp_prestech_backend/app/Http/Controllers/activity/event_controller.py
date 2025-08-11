# event_controller.py
import json
import logging
import inspect
from datetime import date
from django.contrib.auth.decorators import login_required, permission_required
from django.db import transaction
from django.db.models import Q
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import redirect, render
from django.urls import reverse
from django.utils.decorators import method_decorator
from .._traits.controller import Controller
from ....Models.activity.event import Event
from ....Models.individuals.event_employee import EventEmployee
from ....Models.companies.branch import Branch
from ....Models.companies.department import Department
from ....Models.individuals.employee import Employee
from .._helpers.error_handlers import default_undefined_exception

class EventController(Controller):
    @method_decorator(login_required)
    @classmethod
    @permission_required('app.manage_event', raise_exception=True)
    def index(cls, request: HttpRequest) -> HttpResponse:
        try:
            user_id = request.user.id
            employees = Employee.objects.filter(created_by=request.user.creator_id)
            events = Event.objects.filter(created_by=user_id).order_by('-start_date')
            today = date.today()
            current_month_events = events.filter(
                start_date__year=today.year,
                start_date__month=today.month
            )
            arr_events = [
                {
                    'id': str(ev.id),
                    'title': ev.title,
                    'start': ev.start_date.isoformat(),
                    'end': ev.end_date.isoformat(),
                    'backgroundColor': ev.color,
                    'borderColor': "#fff",
                    'textColor': "white",
                }
                for ev in events
            ]
            context = {
                'employees': employees,
                'events': events,
                'trans_date': today.strftime('%Y-%m-%d'),
                'today_date': today.strftime('%Y-%m-%d'),
                'current_month_event': current_month_events,
                'arr_events': json.dumps(arr_events),
            }
            return render(request, 'event/index.html', context)
        except Exception as e:
            REF = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
            return default_undefined_exception(
                request,
                err=e,
                ref=REF,
                logger=logging.getLogger(__name__)
            )

    @method_decorator(login_required)
    @classmethod
    @permission_required('app.create_event', raise_exception=True)
    def create(cls, request: HttpRequest) -> HttpResponse:
        try:
            branches = Branch.objects.filter(created_by=request.user.creator_id)
            departments = Department.objects.none()
            employees = Employee.objects.none()
            return render(request, 'event/create.html', {
                'branches': branches,
                'departments': departments,
                'employees': employees,
                'settings': {},
            })
        except Exception as e:
            REF = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
            return default_undefined_exception(
                request,
                err=e,
                ref=REF,
                logger=logging.getLogger(__name__),
                json={'error': 'Failed to retrieve create form'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    @permission_required('app.create_event', raise_exception=True)
    @transaction.atomic
    def store(cls, request: HttpRequest) -> HttpResponse:
        if request.method != 'POST':
            return JsonResponse({'error': 'Invalid request method'}, status=405)
        try:
            data = request.POST
            branch_id = data.get('branch_id')
            dept_ids = data.getlist('department_id')
            emp_ids = data.getlist('employee_id')
            title = data.get('title')
            start = data.get('start_date')
            end = data.get('end_date')
            color = data.get('color')
            description = data.get('description', '')

            if not (branch_id and dept_ids and emp_ids and title and start and end and color):
                return JsonResponse({'error': 'Validation failed'}, status=422)

            ev = Event.objects.create(
                title=title,
                start_date=start,
                end_date=end,
                color=color,
                description=description,
                created_by=request.user.id,
                branch_id=branch_id,
                department_id=json.dumps(dept_ids),
                employee_id=json.dumps(emp_ids),
            )
            for eid in emp_ids:
                EventEmployee.objects.create(
                    event_id=ev.id,
                    employee_id=eid,
                )
            return redirect(reverse('event.index'))
        except Exception as e:
            REF = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
            return default_undefined_exception(
                request,
                err=e,
                ref=REF,
                logger=logging.getLogger(__name__),
                json={'error': 'Failed to create event'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    def show(cls, request: HttpRequest, event_id: int) -> HttpResponse:
        return redirect(reverse('event.index'))

    @method_decorator(login_required)
    @classmethod
    @permission_required('app.edit_event', raise_exception=True)
    def edit(cls, request: HttpRequest, event_id: int) -> HttpResponse:
        try:
            ev = Event.objects.filter(pk=event_id, created_by=request.user.id).first()
            if not ev:
                return JsonResponse({'error': 'Permission denied'}, status=401)
            branches = Branch.objects.filter(created_by=request.user.creator_id)
            departments = Department.objects.filter(branch_id=ev.branch_id)
            employees = Employee.objects.none()
            return render(request, 'event/edit.html', {
                'event': ev,
                'branches': branches,
                'departments': departments,
                'employees': employees,
            })
        except Exception as e:
            REF = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
            return default_undefined_exception(
                request,
                err=e,
                ref=REF,
                logger=logging.getLogger(__name__),
                json={'error': 'Failed retrieving event for edit'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    @permission_required('app.edit_event', raise_exception=True)
    @transaction.atomic
    def update(cls, request: HttpRequest, event_id: int) -> HttpResponse:
        if request.method != 'POST':
            return JsonResponse({'error': 'Invalid request method'}, status=405)
        try:
            ev = Event.objects.filter(pk=event_id, created_by=request.user.id).first()
            if not ev:
                return JsonResponse({'error': 'Permission denied'}, status=401)

            data = request.POST
            title = data.get('title')
            start = data.get('start_date')
            end = data.get('end_date')
            color = data.get('color')
            description = data.get('description', '')
            dept_ids = data.getlist('department_id')
            emp_ids = data.getlist('employee_id')

            if not (title and start and end and color):
                return JsonResponse({'error': 'Validation failed'}, status=422)

            ev.title = title
            ev.start_date = start
            ev.end_date = end
            ev.color = color
            ev.description = description
            ev.department_id = json.dumps(dept_ids)
            ev.employee_id = json.dumps(emp_ids)
            ev.save()

            EventEmployee.objects.filter(event_id=ev.id).delete()
            for eid in emp_ids:
                EventEmployee.objects.create(event_id=ev.id, employee_id=eid)

            return redirect(reverse('event.index'))
        except Exception as e:
            REF = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
            return default_undefined_exception(
                request,
                err=e,
                ref=REF,
                logger=logging.getLogger(__name__),
                json={'error': 'Failed to update event'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    @permission_required('app.delete_event', raise_exception=True)
    @transaction.atomic
    def destroy(cls, request: HttpRequest, event_id: int) -> HttpResponse:
        if request.method != 'POST':
            return JsonResponse({'error': 'Invalid request method'}, status=405)
        try:
            ev = Event.objects.filter(pk=event_id, created_by=request.user.id).first()
            if not ev:
                return JsonResponse({'error': 'Permission denied'}, status=401)
            EventEmployee.objects.filter(event_id=ev.id).delete()
            ev.delete()
            return redirect(reverse('event.index'))
        except Exception as e:
            REF = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
            return default_undefined_exception(
                request,
                err=e,
                ref=REF,
                logger=logging.getLogger(__name__),
                json={'error': 'Failed to delete event'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    def get_department(cls, request: HttpRequest) -> JsonResponse:
        try:
            branch_id = request.GET.get('branch_id')
            depts = Department.objects.filter(branch_id=branch_id)
            return JsonResponse(list(depts.values('id', 'name')), safe=False)
        except Exception as e:
            REF = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
            return default_undefined_exception(
                request,
                err=e,
                ref=REF,
                logger=logging.getLogger(__name__),
                json={'error': 'Failed to retrieve departments'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    def get_employee(cls, request: HttpRequest) -> JsonResponse:
        try:
            dept_ids = request.GET.getlist('department_id')
            emps = Employee.objects.filter(department_id__in=dept_ids)
            return JsonResponse(list(emps.values('id', 'name')), safe=False)
        except Exception as e:
            REF = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
            return default_undefined_exception(
                request,
                err=e,
                ref=REF,
                logger=logging.getLogger(__name__),
                json={'error': 'Failed to retrieve employees'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    def get_event_data(cls, request: HttpRequest) -> JsonResponse:
        try:
            events = Event.objects.filter(created_by=request.user.id)
            payload = [
                {
                    'id': str(ev.id),
                    'title': ev.title,
                    'start': ev.start_date.isoformat(),
                    'end': ev.end_date.isoformat(),
                    'backgroundColor': ev.color,
                    'borderColor': "#fff",
                    'textColor': "white",
                }
                for ev in events
            ]
            return JsonResponse(payload, safe=False)
        except Exception as e:
            REF = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
            return default_undefined_exception(
                request,
                err=e,
                ref=REF,
                logger=logging.getLogger(__name__),
                json={'error': 'Failed to retrieve event data'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    def get_dashboard_event_data(cls, request: HttpRequest) -> JsonResponse:
        """
        Returns JSON events for the HRM dashboard calendar.
        """
        try:
            emp = Employee.objects.filter(user_id=request.user.id).first()
            if not emp:
                return JsonResponse([], safe=False)

            qs = Event.objects.filter(
                Q(event_employees__employee_id=emp.id) |
                (Q(department_id=json.dumps(["0"])) &
                 Q(employee_id=json.dumps(["0"])))
            ).distinct()

            arr = [
                {
                    'id': str(ev.id),
                    'title': ev.title,
                    'start': ev.start_date.isoformat(),
                    'end': ev.end_date.isoformat(),
                    'backgroundColor': ev.color,
                    'borderColor': "#fff",
                    'textColor': "white",
                }
                for ev in qs
            ]
            return JsonResponse(arr, safe=False)
        except Exception as e:
            REF = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
            return default_undefined_exception(
                request,
                err=e,
                ref=REF,
                logger=logging.getLogger(__name__),
                json={'error': 'Failed to retrieve dashboard events'},
                status=400
            )
