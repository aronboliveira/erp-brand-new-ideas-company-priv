import logging
import inspect
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse
from django.shortcuts import render, redirect, get_object_or_404
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from ....Models.bills.deduction_option import DeductionOption
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class DeductionOptionController(Controller):

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('manage deduction option'):
                raise PermissionDenied('User lacks permission: manage deduction option')
            deductionoptions = DeductionOption.objects.filter(
                created_by=request.user.creator_id()
            )
            return render(request, 'deductionoption/index.html', {
                'deductionoptions': deductionoptions
            })
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('create deduction option'):
                raise PermissionDenied('User lacks permission: create deduction option')
            return render(request, 'deductionoption/create.html')
        except PermissionDenied as err:
            return default_permission_denial(
                request,
                err=err,
                ref=REF,
                logger=logger,
                json={'error': 'Permission denied.'}
            )
        except Exception as err:
            return default_undefined_exception(
                request,
                err=err,
                ref=REF,
                logger=logger,
                json={'error': 'An error occurred.'},
                status=500
            )

    @classmethod
    def store(cls, request: HttpRequest) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('create deduction option'):
                raise PermissionDenied('User lacks permission: create deduction option')
            name = request.POST.get('name')
            if not name:
                messages.error(request, 'Name is required.')
                return redirect(get_redirect_url(request))
            deductionoption = DeductionOption(
                name=name,
                created_by=request.user.creator_id()
            )
            deductionoption.save()
            messages.success(request, 'DeductionOption successfully created.')
            return redirect('deductionoption.index')
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def show(cls, request: HttpRequest, deduction_option_id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            return redirect('deductionoption.index')
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def edit(cls, request: HttpRequest, deduction_option_id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('edit deduction option'):
                raise PermissionDenied('User lacks permission: edit deduction option')
            deductionoption = get_object_or_404(DeductionOption, pk=deduction_option_id)
            if deductionoption.created_by != request.user.creator_id():
                raise PermissionDenied('User lacks permission: edit deduction option')
            return render(request, 'deductionoption/edit.html', {
                'deductionoption': deductionoption
            })
        except PermissionDenied as err:
            return default_permission_denial(
                request,
                err=err,
                ref=REF,
                logger=logger,
                json={'error': 'Permission denied.'}
            )
        except Exception as err:
            return default_undefined_exception(
                request,
                err=err,
                ref=REF,
                logger=logger,
                json={'error': 'An error occurred.'},
                status=500
            )

    @classmethod
    def update(cls, request: HttpRequest, deduction_option_id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('edit deduction option'):
                raise PermissionDenied('User lacks permission: edit deduction option')
            deductionoption = get_object_or_404(DeductionOption, pk=deduction_option_id)
            if deductionoption.created_by != request.user.creator_id():
                raise PermissionDenied('User lacks permission: edit deduction option')
            name = request.POST.get('name')
            if not name:
                messages.error(request, 'Name is required.')
                return redirect(get_redirect_url(request))
            deductionoption.name = name
            deductionoption.save()
            messages.success(request, 'DeductionOption successfully updated.')
            return redirect('deductionoption.index')
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def destroy(cls, request: HttpRequest, deduction_option_id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('delete deduction option'):
                raise PermissionDenied('User lacks permission: delete deduction option')
            deductionoption = get_object_or_404(DeductionOption, pk=deduction_option_id)
            if deductionoption.created_by != request.user.creator_id():
                raise PermissionDenied('User lacks permission: delete deduction option')
            deductionoption.delete()
            messages.success(request, 'DeductionOption successfully deleted.')
            return redirect('deductionoption.index')
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)
