import logging
import inspect
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.utils.decorators import method_decorator
from django.views.decorators.http import require_http_methods
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from ....Models.bills.allowance_option import AllowanceOption
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class AllowanceOptionController(Controller):

    @method_decorator(login_required)
    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('crm.manage_allowance_option'):
                raise PermissionDenied('User lacks permission: crm.manage_allowance_option')
            creator_id = request.user.creator_id
            allowanceoptions = AllowanceOption.objects.filter(created_by=creator_id)
            return render(request, 'allowanceoption/index.html', {'allowanceoptions': allowanceoptions})
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('crm.create_allowance_option'):
                # JSON response on permission error
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks permission: crm.create_allowance_option'),
                    ref=REF,
                    logger=logger,
                    json={'error': 'Permission denied.'}
                )
            return render(request, 'allowanceoption/create.html')
        except Exception as err:
            return default_undefined_exception(
                request,
                err=err,
                ref=REF,
                logger=logger,
                json={'error': f"{CNAME}.{MNAME}: {err}"},
                status=500
            )

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def store(cls, request: HttpRequest) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('crm.create_allowance_option'):
                raise PermissionDenied('User lacks permission: crm.create_allowance_option')
            name = request.POST.get('name')
            if not name:
                messages.error(request, "Name is required.")
                return redirect(get_redirect_url(request))
            if len(name) > 20:
                messages.error(request, "Name must be 20 characters or less.")
                return redirect(get_redirect_url(request))
            creator_id = request.user.creator_id
            allowanceoption = AllowanceOption(name=name, created_by=creator_id)
            allowanceoption.save()
            messages.success(request, "AllowanceOption successfully created.")
            return redirect('allowanceoption_index')
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def show(cls, request: HttpRequest, allowanceoption_id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            return redirect('allowanceoption_index')
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def edit(cls, request: HttpRequest, allowanceoption_id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('crm.edit_allowance_option'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks permission: crm.edit_allowance_option'),
                    ref=REF,
                    logger=logger,
                    json={'error': 'Permission denied.'}
                )
            allowanceoption = get_object_or_404(AllowanceOption, pk=allowanceoption_id)
            if allowanceoption.created_by != request.user.creator_id:
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks ownership'),
                    ref=REF,
                    logger=logger,
                    json={'error': 'Permission denied.'}
                )
            return render(request, 'allowanceoption/edit.html', {'allowanceoption': allowanceoption})
        except Exception as err:
            return default_undefined_exception(
                request,
                err=err,
                ref=REF,
                logger=logger,
                json={'error': f"{CNAME}.{MNAME}: {err}"},
                status=500
            )

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def update(cls, request: HttpRequest, allowanceoption_id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('crm.edit_allowance_option'):
                raise PermissionDenied('User lacks permission: crm.edit_allowance_option')
            allowanceoption = get_object_or_404(AllowanceOption, pk=allowanceoption_id)
            if allowanceoption.created_by != request.user.creator_id:
                raise PermissionDenied('User lacks permission: crm.edit_allowance_option')
            name = request.POST.get('name')
            if not name:
                messages.error(request, "Name is required.")
                return redirect(get_redirect_url(request))
            if len(name) > 20:
                messages.error(request, "Name must be 20 characters or less.")
                return redirect(get_redirect_url(request))
            allowanceoption.name = name
            allowanceoption.save()
            messages.success(request, "AllowanceOption successfully updated.")
            return redirect('allowanceoption_index')
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def destroy(cls, request: HttpRequest, allowanceoption_id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('crm.delete_allowance_option'):
                raise PermissionDenied('User lacks permission: crm.delete_allowance_option')
            allowanceoption = get_object_or_404(AllowanceOption, pk=allowanceoption_id)
            if allowanceoption.created_by != request.user.creator_id:
                raise PermissionDenied('User lacks permission: crm.delete_allowance_option')
            allowanceoption.delete()
            messages.success(request, "AllowanceOption successfully deleted.")
            return redirect('allowanceoption_index')
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)
