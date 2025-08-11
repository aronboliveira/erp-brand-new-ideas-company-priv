import datetime  
import logging  
import inspect
from django.contrib import messages  
from django.core.exceptions import PermissionDenied  
from django.db import transaction  
from django.http import HttpRequest, HttpResponse, JsonResponse  
from django.shortcuts import get_object_or_404, redirect, render  
from .._helpers.http import get_redirect_url  
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception  
from ....Models.individuals.employee import Employee  
from ....Models.planning.leave import Leave  
from ....Models.planning.leave_type import LeaveType  
from ....Models.utils.utility import Utility  
from .._traits.controller import Controller  

logger = logging.getLogger(__name__)  

class LeaveController(Controller):
    def __init__(self, **kwargs):
        super().__init__(**kwargs)
        self.middleware(['auth'])

    @classmethod
    def _set_leave(cls, leave: Leave, data: dict, request_user) -> Leave:
        field_map = {
            'leave_type_id': 'leave_type_id',
            'start_date': 'start_date',
            'end_date': 'end_date',
            'leave_reason': 'leave_reason',
            'remark': 'remark',
        }
        for src, dest in field_map.items():
            setattr(leave, dest, data.get(src))
        sd = data.get('start_date')
        ed = data.get('end_date')
        sd_dt = datetime.datetime.fromisoformat(sd)
        ed_dt = datetime.datetime.fromisoformat(ed) + datetime.timedelta(days=1)
        leave.total_leave_days = (ed_dt - sd_dt).days
        if request_user.type == 'Employee':
            leave.employee_id = (
                Employee.objects
                .filter(user_id=request_user.id)
                .values_list('id', flat=True)
                .first()
            )
        else:
            leave.employee_id = data.get('employee_id')
        if not getattr(leave, 'id', None):
            for attr, val in {
                'applied_on': datetime.date.today().isoformat(),
                'status': 'Pending',
                'created_by': request_user.creator_id(),
            }.items():
                setattr(leave, attr, val)

        return leave


    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('manage leave') or (_ for _ in ()).throw(
                PermissionDenied('User lacks permission: manage leave')
            )
            leaves = (
                Leave.objects
                .filter(employee_id=Employee.objects.filter(user_id=request.user.id)
                        .values_list('id', flat=True).first())
                .select_related('leave_type','employees')
                if request.user.type == 'Employee'
                else Leave.objects
                     .filter(created_by=request.user.creator_id())
                     .select_related('leave_type','employees')
            )
            return render(request, 'leave/index.html', {'leaves': leaves})
        except PermissionDenied as e:
            return default_permission_denial(
                request, err=e,
                ref=f'{CN}::{MN}', logger=logger
            )
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{CN}::{MN}', logger=logger
            )

    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('create leave') or (_ for _ in ()).throw(
                PermissionDenied('User lacks permission: create leave')
            )
            employees = (
                Employee.objects.filter(user_id=request.user.id)
                .values_list('name','id')
                if request.user.type == 'Employee'
                else Employee.objects
                     .filter(created_by=request.user.creator_id())
                     .values_list('name','id')
            )
            leavetypes = LeaveType.objects \
                .filter(created_by=request.user.creator_id()) \
                .values_list('title','id')
            return render(request, 'leave/create.html', {
                'employees': employees,
                'leavetypes': leavetypes
            })
        except PermissionDenied as e:
            return default_permission_denial(
                request, err=e,
                ref=f'{CN}::{MN}', logger=logger,
                json={'error': 'Permission denied.'}
            )
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{CN}::{MN}', logger=logger
            )

    @classmethod
    @transaction.atomic
    def store(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('create leave') or (_ for _ in ()).throw(
                PermissionDenied('User lacks permission: create leave')
            )
            data = request.POST
            required = ['leave_type_id', 'start_date', 'end_date', 'leave_reason', 'remark']
            if request.user.type != 'Employee':
                required.append('employee_id')
            for field in required:
                if not data.get(field):
                    messages.error(request, f"{field.replace('_', ' ').capitalize()} is required.")
                    return redirect(get_redirect_url(request))
            lt = LeaveType.objects.filter(id=data['leave_type_id']).first()
            if not lt:
                messages.error(request, "Selected leave type is invalid.")
                return redirect(get_redirect_url(request))
            try:
                sd = datetime.date.fromisoformat(data['start_date'])
                ed = datetime.date.fromisoformat(data['end_date'])
            except ValueError:
                messages.error(request, "Dates must be in YYYY-MM-DD format.")
                return redirect(get_redirect_url(request))
            if ed < sd:
                messages.error(request, "End date must be on or after start date.")
                return redirect(get_redirect_url(request))

            days = (ed - sd).days + 1
            if days > lt.days:
                messages.error(request, f"Leave type {lt.title} allows max {lt.days} days.")
                return redirect(get_redirect_url(request))
            leave = cls._set_leave(Leave(), data, request.user)
            leave.save()
            messages.success(request, 'Leave successfully created.')
            return redirect('leave_index')

        except PermissionDenied as e:
            return default_permission_denial(
                request, err=e, ref=f'{CN}::{MN}', logger=logger
            )
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=f'{CN}::{MN}', logger=logger
            )

    @classmethod
    def show(cls, request: HttpRequest, leave_id: int) -> HttpResponse:
        return redirect('leave_index')

    @classmethod
    def edit(cls, request: HttpRequest, leave_id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('edit leave') or (_ for _ in ()).throw(
                PermissionDenied('User lacks permission: edit leave')
            )
            leave = get_object_or_404(Leave, pk=leave_id)
            leave.created_by == request.user.creator_id() or (_ for _ in ()).throw(
                PermissionDenied('Permission denied.')
            )
            employees = Employee.objects.filter(
                created_by=request.user.creator_id()
            ).values_list('name','id')
            leavetypes = LeaveType.objects.filter(
                created_by=request.user.creator_id()
            ).values_list('title','id')
            return render(request, 'leave/edit.html', {
                'leave': leave,
                'employees': employees,
                'leavetypes': leavetypes
            })
        except PermissionDenied as e:
            return default_permission_denial(
                request, err=e,
                ref=f'{CN}::{MN}', logger=logger,
                json={'error': 'Permission denied.'}
            )
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{CN}::{MN}', logger=logger
            )

    @classmethod
    def update(cls, request: HttpRequest, leave_id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('edit leave') or (_ for _ in ()).throw(
                PermissionDenied('User lacks permission: edit leave')
            )
            leave = get_object_or_404(Leave, pk=leave_id)
            leave.created_by == request.user.creator_id() or (_ for _ in ()).throw(
                PermissionDenied('Permission denied.')
            )
            data = request.POST
            for field in ('leave_type_id', 'start_date', 'end_date', 'leave_reason', 'remark'):
                if not data.get(field):
                    messages.error(request, f"{field.replace('_', ' ').capitalize()} is required.")
                    return redirect(get_redirect_url(request))
            lt = LeaveType.objects.filter(id=data['leave_type_id']).first()
            if not lt:
                messages.error(request, "Selected leave type is invalid.")
                return redirect(get_redirect_url(request))
            try:
                sd = datetime.date.fromisoformat(data['start_date'])
                ed = datetime.date.fromisoformat(data['end_date'])
            except ValueError:
                messages.error(request, "Dates must be in YYYY-MM-DD format.")
                return redirect(get_redirect_url(request))
            if ed < sd:
                messages.error(request, "End date must be on or after start date.")
                return redirect(get_redirect_url(request))

            days = (ed - sd).days + 1
            if days > lt.days:
                messages.error(request, f"Leave type {lt.title} allows max {lt.days} days.")
                return redirect(get_redirect_url(request))
            cls._set_leave(leave, data, request.user).save()
            messages.success(request, 'Leave successfully updated.')
            return redirect('leave_index')

        except PermissionDenied as e:
            return default_permission_denial(
                request, err=e, ref=f'{CN}::{MN}', logger=logger
            )
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=f'{CN}::{MN}', logger=logger
            )

    @classmethod
    def destroy(cls, request: HttpRequest, leave_id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('delete leave') or (_ for _ in ()).throw(
                PermissionDenied('User lacks permission: delete leave')
            )
            leave = get_object_or_404(Leave, pk=leave_id)
            leave.created_by == request.user.creator_id() or (_ for _ in ()).throw(
                PermissionDenied('Permission denied.')
            )
            leave.delete()
            messages.success(request, 'Leave successfully deleted.')
            return redirect('leave_index')
        except PermissionDenied as e:
            return default_permission_denial(
                request, err=e,
                ref=f'{CN}::{MN}', logger=logger
            )
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{CN}::{MN}', logger=logger
            )

    @classmethod
    def action(cls, request: HttpRequest, leave_id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('manage leave') or (_ for _ in ()).throw(
                PermissionDenied('User lacks permission: manage leave')
            )
            leave = get_object_or_404(Leave, pk=leave_id)
            emp = get_object_or_404(Employee, pk=leave.employee_id)
            lt = get_object_or_404(LeaveType, pk=leave.leave_type_id)
            return render(request, 'leave/action.html', {
                'employee': emp,
                'leavetype': lt,
                'leave': leave
            })
        except PermissionDenied as e:
            return default_permission_denial(
                request, err=e,
                ref=f'{CN}::{MN}', logger=logger
            )
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{CN}::{MN}', logger=logger
            )

    @classmethod
    def changeaction(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('manage leave') or (_ for _ in ()).throw(
                PermissionDenied('User lacks permission: manage leave')
            )
            lid = request.POST.get('leave_id')
            leave = get_object_or_404(Leave, pk=lid)
            leave.status = request.POST.get('status')
            if leave.status == 'Approval':
                sd = datetime.datetime.fromisoformat(leave.start_date)
                ed = datetime.datetime.fromisoformat(leave.end_date)
                leave.total_leave_days = (ed - sd).days
                leave.status = 'Approved'
            leave.save()
            emp = get_object_or_404(Employee, pk=leave.employee_id)
            if Utility.settings().get('leave_status', '') == '1':
                arr = {
                    'leave_name': emp.name or '',
                    'leave_status': leave.status,
                    'leave_reason': leave.leave_reason,
                    'leave_start_date': leave.start_date,
                    'leave_end_date': leave.end_date,
                    'total_leave_days': leave.total_leave_days
                }
                resp = Utility.send_email_template('leave_action_sent', {emp.id: emp.email}, arr)
                msg = 'Leave status successfully updated.' + (
                    f"<br><span class='text-danger'>{resp['error']}</span>"
                    if resp.get('is_success') is False and resp.get('error') else ''
                )
            else:
                msg = 'Leave status successfully updated.'
            messages.success(request, msg)
            return redirect('leave_index')
        except PermissionDenied as e:
            return default_permission_denial(
                request, err=e,
                ref=f'{CN}::{MN}', logger=logger
            )
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{CN}::{MN}', logger=logger
            )

    @classmethod
    def jsoncount(cls, request: HttpRequest) -> JsonResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            lts = LeaveType.objects.filter(created_by=request.user.creator_id())
            result = []
            for lt in lts:
                cnt = Leave.objects.filter(
                    leave_type_id=lt.id,
                    employee_id=request.GET.get('employee_id')
                ).aggregate(
                    total_days=Utility.coalesce_sum('total_leave_days')
                )['total_days'] or 0
                result.append({
                    'total_leave': cnt,
                    'title': lt.title,
                    'days': lt.days,
                    'id': lt.id
                })
            return JsonResponse(result, safe=False)
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{CN}::{MN}', logger=logger
            )
