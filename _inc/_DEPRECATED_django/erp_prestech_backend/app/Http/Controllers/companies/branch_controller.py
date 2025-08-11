import inspect
import logging
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.utils.decorators import method_decorator
from django.views.decorators.http import require_http_methods
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller
from ....Models.companies.branch import Branch
from ....Models.companies.department import Department
from ....Models.individuals.employee import Employee
logger = logging.getLogger(__name__)

class BranchController(Controller):

    @method_decorator(login_required)
    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('crm.manage_branch'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks permission: crm.manage_branch'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            creator_id = request.user.creator_id
            branches   = Branch.objects.filter(created_by=creator_id)
            return render(request, 'branch/index.html', {'branches': branches})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('crm.create_branch'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks permission: crm.create_branch'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            return render(request, 'branch/create.html')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @require_http_methods(["POST"])
    @method_decorator(login_required)
    @classmethod
    def store(cls, request: HttpRequest) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('crm.create_branch'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks permission: crm.create_branch'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            name = request.POST.get('name')
            if not name:
                messages.error(request, "Name is required.")
                return redirect(get_redirect_url(request))
            branch = Branch(name=name, created_by=request.user.creator_id)
            branch.save()
            messages.success(request, "Branch successfully created.")
            return redirect('branch_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def show(cls, request: HttpRequest, branch_id: int) -> HttpResponse:
        # CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        # TODO no actual show logic; redirect to index
        return redirect('branch_index')

    @method_decorator(login_required)
    @classmethod
    def edit(cls, request: HttpRequest, branch_id: int) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('crm.edit_branch'):
                return JsonResponse({'error': "Permission denied."}, status=401)
            branch = get_object_or_404(Branch, pk=branch_id)
            if branch.created_by != request.user.creator_id:
                return JsonResponse({'error': "Permission denied."}, status=401)
            return render(request, 'branch/edit.html', {'branch': branch})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @require_http_methods(["POST"])
    @method_decorator(login_required)
    @classmethod
    def update(cls, request: HttpRequest, branch_id: int) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('crm.edit_branch'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks permission: crm.edit_branch'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            branch = get_object_or_404(Branch, pk=branch_id)
            if branch.created_by != request.user.creator_id:
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            name = request.POST.get('name')
            if not name:
                messages.error(request, "Name is required.")
                return redirect(get_redirect_url(request))
            branch.name = name
            branch.save()
            messages.success(request, "Branch successfully updated.")
            return redirect('branch_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @require_http_methods(["POST"])
    @method_decorator(login_required)
    @classmethod
    def destroy(cls, request: HttpRequest, branch_id: int) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('crm.delete_branch'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks permission: crm.delete_branch'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            branch = get_object_or_404(Branch, pk=branch_id)
            if branch.created_by != request.user.creator_id:
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            branch.delete()
            messages.success(request, "Branch successfully deleted.")
            return redirect('branch_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def get_department(cls, request: HttpRequest) -> JsonResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            branch_id = request.GET.get('branch_id')
            qs = Department.objects.all().values('id', 'name') \
                 if branch_id in (None, '0') \
                 else Department.objects.filter(branch_id=branch_id).values('id', 'name')
            department_dict = {d['id']: d['name'] for d in qs}
            return JsonResponse(department_dict)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def get_employee(cls, request: HttpRequest) -> JsonResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            dept_ids = request.GET.getlist('department_id')
            qs = Employee.objects.all().values('id', 'name') \
                 if '0' in dept_ids \
                 else Employee.objects.filter(department_id__in=dept_ids).values('id', 'name')
            employee_dict = {e['id']: e['name'] for e in qs}
            return JsonResponse(employee_dict)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
