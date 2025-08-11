import logging
import inspect
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.utils.decorators import method_decorator
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller
from ....Models.companies.department import Department
from ....Models.individuals.designation import Designation

logger = logging.getLogger(__name__)

class DesignationController(Controller):

    @method_decorator(login_required)
    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('manage designation'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks permission: manage designation'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            designations = Designation.objects.filter(created_by=request.user.creator_id())
            return render(request, 'designation/index.html', {'designations': designations})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('create designation'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks permission: create designation'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            departments_qs = Department.objects.filter(created_by=request.user.creator_id())
            departments    = {dept.id: dept.name for dept in departments_qs}
            return render(request, 'designation/create.html', {'departments': departments})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger, json={})

    @method_decorator(login_required)
    @classmethod
    def store(cls, request: HttpRequest) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('create designation'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks permission: create designation'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            dept_id = request.POST.get('department_id')
            name    = request.POST.get('name')
            if not dept_id or not name:
                messages.error(request, 'Department and name are required.')
                return redirect(get_redirect_url(request))
            if len(name) > 20:
                messages.error(request, 'Name must be at most 20 characters.')
                return redirect(get_redirect_url(request))
            designation = Designation(
                department_id=dept_id,
                name=name,
                created_by=request.user.creator_id()
            )
            designation.save()
            messages.success(request, 'Designation successfully created.')
            return redirect('designation.index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def show(cls, request: HttpRequest, designation_id: int) -> HttpResponse:
        # CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        # TODO No detail view; redirect back to list
        return redirect('designation.index')

    @method_decorator(login_required)
    @classmethod
    def edit(cls, request: HttpRequest, designation_id: int) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            designation = get_object_or_404(Designation, pk=designation_id)
            if not request.user.has_perm('edit designation'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks permission: edit designation'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            if designation.created_by != request.user.creator_id():
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            dept = Department.objects.filter(id=designation.department_id).first()
            departments = {dept.id: dept.name} if dept else {}
            return render(request, 'designation/edit.html', {
                'designation': designation,
                'departments': departments
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger, json={})

    @method_decorator(login_required)
    @classmethod
    def update(cls, request: HttpRequest, designation_id: int) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit designation'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks permission: edit designation'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            designation = get_object_or_404(Designation, pk=designation_id)
            if designation.created_by != request.user.creator_id():
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            dept_id = request.POST.get('department_id')
            name    = request.POST.get('name')
            if not dept_id or not name:
                messages.error(request, 'Department and name are required.')
                return redirect(get_redirect_url(request))
            if len(name) > 20:
                messages.error(request, 'Name must be at most 20 characters.')
                return redirect(get_redirect_url(request))
            designation.department_id = dept_id
            designation.name          = name
            designation.save()
            messages.success(request, 'Designation successfully updated.')
            return redirect('designation.index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def destroy(cls, request: HttpRequest, designation_id: int) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('delete designation'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks permission: delete designation'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            designation = get_object_or_404(Designation, pk=designation_id)
            if designation.created_by != request.user.creator_id():
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            designation.delete()
            messages.success(request, 'Designation successfully deleted.')
            return redirect('designation.index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
