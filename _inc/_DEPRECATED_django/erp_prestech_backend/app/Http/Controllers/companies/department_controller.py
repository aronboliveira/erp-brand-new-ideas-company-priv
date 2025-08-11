from typing import Union, Dict
from django.shortcuts import render, redirect, get_object_or_404
from django.http import JsonResponse, HttpRequest, HttpResponse
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.utils.decorators import method_decorator
from django.views.decorators.http import require_http_methods
from ....Models.companies.department import Department
from ....Models.companies.branch import Branch
from .._traits.controller import Controller
from .._helpers.http import get_redirect_url
from django.core.exceptions import PermissionDenied
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
import inspect
import logging

logger = logging.getLogger(__name__)

class DepartmentController(Controller):

    @method_decorator(login_required)
    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('crm.manage_department'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('crm.manage_department'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            creator_id = request.user.creator_id
            departments = Department.objects.filter(created_by=creator_id)
            return render(request, 'department/index.html', {'departments': departments})
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def create(cls, request: HttpRequest) -> Union[HttpResponse, JsonResponse]:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('crm.create_department'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('crm.create_department'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            creator_id = request.user.creator_id
            branches_qs = Branch.objects.filter(created_by=creator_id).values('id', 'name')
            branches: Dict[int, str] = {b['id']: b['name'] for b in branches_qs}
            return render(request, 'department/create.html', {'branch': branches})
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger,
                json={}
            )

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def store(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('crm.create_department'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('crm.create_department'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            branch_id: str = request.POST.get('branch_id', '')
            name: str = request.POST.get('name', '')
            if not branch_id or not name:
                messages.error(request, "Branch and Name are required.")
                return redirect(get_redirect_url(request))
            if len(name) > 20:
                messages.error(request, "Name must be 20 characters or less.")
                return redirect(get_redirect_url(request))
            creator_id = request.user.creator_id
            dept = Department(branch_id=branch_id, name=name, created_by=creator_id)
            dept.save()
            messages.success(request, "Department successfully created.")
            return redirect('department_index')
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def show(cls, request: HttpRequest, department_id: int) -> HttpResponse:
        # simply redirect to index
        return redirect('department_index')

    @method_decorator(login_required)
    @classmethod
    def edit(cls, request: HttpRequest, department_id: int) -> Union[HttpResponse, JsonResponse]:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('crm.edit_department'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('crm.edit_department'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            dept = get_object_or_404(Department, pk=department_id)
            if dept.created_by != request.user.creator_id:
                return default_permission_denial(
                    request,
                    err=PermissionDenied('crm.edit_department'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            creator_id = request.user.creator_id
            branches_qs = Branch.objects.filter(created_by=creator_id).values('id', 'name')
            branches: Dict[int, str] = {b['id']: b['name'] for b in branches_qs}
            return render(request, 'department/edit.html', {'department': dept, 'branch': branches})
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger,
                json={}
            )

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def update(cls, request: HttpRequest, department_id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('crm.edit_department'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('crm.edit_department'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            dept = get_object_or_404(Department, pk=department_id)
            if dept.created_by != request.user.creator_id:
                return default_permission_denial(
                    request,
                    err=PermissionDenied('crm.edit_department'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            branch_id: str = request.POST.get('branch_id', '')
            name: str = request.POST.get('name', '')
            if not branch_id or not name:
                messages.error(request, "Branch and Name are required.")
                return redirect(get_redirect_url(request))
            if len(name) > 20:
                messages.error(request, "Name must be 20 characters or less.")
                return redirect(get_redirect_url(request))
            dept.branch_id = branch_id
            dept.name = name
            dept.save()
            messages.success(request, "Department successfully updated.")
            return redirect('department_index')
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def destroy(cls, request: HttpRequest, department_id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('crm.delete_department'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('crm.delete_department'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            dept = get_object_or_404(Department, pk=department_id)
            if dept.created_by != request.user.creator_id:
                return default_permission_denial(
                    request,
                    err=PermissionDenied('crm.delete_department'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            dept.delete()
            messages.success(request, "Department successfully deleted.")
            return redirect('department_index')
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )
