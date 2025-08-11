import json
import logging
import inspect
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core.exceptions import PermissionDenied
from django.db.models import Q
from django.http import JsonResponse, HttpRequest, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.utils.decorators import method_decorator
from django.views.decorators.http import require_http_methods
from ....Models.companies.branch import Branch
from ....Models.companies.department import Department
from ....Models.info.announcement import Announcement
from ....Models.individuals.employee_announcement import employeeannouncement
from ....Models.individuals.employee import Employee
from ....Models.utils.utility import Utility
from .._traits.controller import Controller
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception

logger = logging.getLogger(__name__)

class AnnouncementController(Controller):

    @method_decorator(login_required)
    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('crm.manage_announcement'):
                raise PermissionDenied('crm.manage_announcement')
            try:
                current_employee = Employee.objects.filter(user=request.user).first()
            except Exception as e:
                logger.exception(f"{cls.__name__}.index: error fetching current employee: {e}")
                current_employee = None
            announcements = (
                Announcement.objects.order_by('-id')
                    .filter(
                        Q(employeeannouncement__employee_id=current_employee.id) |
                        Q(department_id='[\"0\"]', employee_id='[\"0\"]')
                    ).distinct()
                if getattr(request.user, 'type', None) == 'Employee'
                else Announcement.objects.filter(created_by=request.user.creator_id)
            )
            return render(request, 'announcement/index.html', {
                'announcements': announcements,
                'current_employee': current_employee
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('crm.create_announcement'):
                raise PermissionDenied('crm.create_announcement')
            creator_id = request.user.creator_id
            employees = Employee.objects.filter(created_by=creator_id).values('id', 'name')
            branches = Branch.objects.filter(created_by=creator_id)
            departments = Department.objects.filter(created_by=creator_id)
            return render(request, 'announcement/create.html', {
                'employees': employees,
                'branches': branches,
                'departments': departments
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger, auto_redirect=False, json={})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger, auto_redirect=False, json={})

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def store(cls, request: HttpRequest) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('crm.create_announcement'):
                raise PermissionDenied('crm.create_announcement')
            required_fields = ['title', 'start_date', 'end_date', 'branch_id', 'department_id', 'employee_id']
            for field in required_fields:
                if not request.POST.get(field):
                    messages.error(request, f"{field} is required.")
                    return redirect(get_redirect_url(request))
            creator_id = request.user.creator_id
            announcement = Announcement(
                title=request.POST.get('title'),
                start_date=request.POST.get('start_date'),
                end_date=request.POST.get('end_date'),
                branch_id=request.POST.get('branch_id') or 0,
                department_id=json.dumps(request.POST.getlist('department_id')),
                employee_id=json.dumps(request.POST.getlist('employee_id')),
                description=request.POST.get('description', ''),
                created_by=creator_id
            )
            announcement.save()
            employee_ids = request.POST.getlist('employee_id')
            department_employees = (
                list(Employee.objects.filter(department_id__in=request.POST.getlist('department_id'))
                     .values_list('id', flat=True))
                if '0' in employee_ids else employee_ids
            )
            for emp_id in department_employees:
                ann_emp = employeeannouncement(
                    announcement_id=announcement.id,
                    employee_id=emp_id,
                    created_by=creator_id
                )
                try:
                    ann_emp.save()
                except Exception as e:
                    logger.exception(f"{cls.__name__}.store: error saving employeeannouncement for emp_id {emp_id}: {e}")
            setting = Utility.settings(creator_id)
            branch_id = request.POST.get('branch_id')
            branch_names = (
                list(Branch.objects.all().values_list('name', flat=True))
                if branch_id == '0'
                else (
                    Branch.objects.filter(pk=branch_id).first().name.split(',')
                    if Branch.objects.filter(pk=branch_id).first() and Branch.objects.filter(pk=branch_id).first().name
                    else []
                )
            )
            announceNotificationArr = {
                'announcement_title': request.POST.get('title'),
                'branch_name': ','.join(branch_names),
                'start_date': request.POST.get('start_date'),
                'end_date': request.POST.get('end_date')
            }
            if setting.get('announcement_notification') == 1:
                try:
                    Utility.send_slack_msg('new_announcement', announceNotificationArr)
                except Exception as e:
                    logger.exception(f"{cls.__name__}.store: error sending Slack notification: {e}")
            if setting.get('telegram_announcement_notification') == 1:
                try:
                    Utility.send_telegram_msg('new_announcement', announceNotificationArr)
                except Exception as e:
                    logger.exception(f"{cls.__name__}.store: error sending Telegram notification: {e}")
            webhook = Utility.webhookSetting('New Announcement')
            if webhook:
                parameter = json.dumps({'id': announcement.id, 'title': announcement.title})
                try:
                    status = Utility.WebhookCall(webhook['url'], parameter, webhook['method'])
                    if not status:
                        messages.error(request, "Webhook call failed.")
                        return redirect(get_redirect_url(request))
                except Exception as e:
                    logger.exception(f"{cls.__name__}.store: error during webhook call: {e}")
                    messages.error(request, "Webhook call failed due to exception.")
                    return redirect(get_redirect_url(request))
            messages.success(request, "Announcement successfully created.")
            return redirect('announcement_index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def show(cls, request: HttpRequest, announcement_id: int) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            return redirect('announcement_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def edit(cls, request: HttpRequest, announcement_id: int) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('crm.edit_announcement'):
                raise PermissionDenied('crm.edit_announcement')
            announcement = get_object_or_404(Announcement, pk=announcement_id)
            if announcement.created_by != request.user.creator_id:
                raise PermissionDenied('crm.edit_announcement')
            creator_id = request.user.creator_id
            branches = Branch.objects.filter(created_by=creator_id).values('id', 'name')
            departments = Department.objects.filter(created_by=creator_id).values('id', 'name')
            return render(request, 'announcement/edit.html', {
                'announcement': announcement,
                'branches': branches,
                'departments': departments
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def update(cls, request: HttpRequest, announcement_id: int) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('crm.edit_announcement'):
                raise PermissionDenied('crm.edit_announcement')
            announcement = get_object_or_404(Announcement, pk=announcement_id)
            if announcement.created_by != request.user.creator_id:
                raise PermissionDenied('crm.edit_announcement')
            required_fields = ['title', 'start_date', 'end_date', 'branch_id', 'department_id']
            for field in required_fields:
                if not request.POST.get(field):
                    messages.error(request, f"{field} is required.")
                    return redirect(get_redirect_url(request))
            for k in ('title', 'start_date', 'end_date', 'branch_id'):
              setattr(announcement, k, request.POST.get(k))
            announcement.description = request.POST.get('description', '')
            announcement.department_id = json.dumps(request.POST.getlist('department_id'))
            announcement.save()
            messages.success(request, "Announcement successfully updated.")
            return redirect('announcement_index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def destroy(cls, request: HttpRequest, announcement_id: int) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('crm.delete_announcement'):
                raise PermissionDenied('crm.delete_announcement')
            announcement = get_object_or_404(Announcement, pk=announcement_id)
            if announcement.created_by != request.user.creator_id:
                raise PermissionDenied('crm.delete_announcement')
            announcement.delete()
            messages.success(request, "Announcement successfully deleted.")
            return redirect('announcement_index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def get_department(cls, request: HttpRequest) -> JsonResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            creator_id = request.user.creator_id
            branch_id = request.GET.get('branch_id')
            departments = (
                Department.objects.filter(created_by=creator_id).values('id', 'name')
                if branch_id in (None, '0')
                else Department.objects.filter(created_by=creator_id, branch_id=branch_id).values('id', 'name')
            )
            department_dict = {dept['id']: dept['name'] for dept in departments}
            return JsonResponse(department_dict)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger, auto_redirect=False, json={})

    @method_decorator(login_required)
    @classmethod
    def get_employee(cls, request: HttpRequest) -> JsonResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            creator_id = request.user.creator_id
            department_id = request.GET.get('department_id')
            employees = (
                Employee.objects.filter(created_by=creator_id).values('id', 'name')
                if not department_id
                else Employee.objects.filter(created_by=creator_id, department_id=department_id).values('id', 'name')
            )
            employee_dict = {emp['id']: emp['name'] for emp in employees}
            return JsonResponse(employee_dict)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger, auto_redirect=False, json={})
