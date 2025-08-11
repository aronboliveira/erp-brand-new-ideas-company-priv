import logging
import inspect
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.db import transaction
from django.http import HttpRequest, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller
from ....Models.bills.loan_option import LoanOption

logger = logging.getLogger(__name__)

class LoanOptionController(Controller):
    def __init__(self, **kwargs):
        super().__init__(**kwargs)
        self.middleware(['auth'])

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('manage loan option'):
                raise PermissionDenied('User lacks permission: manage loan option')
            opts = LoanOption.objects.filter(created_by=request.user.creator_id())
            return render(request, 'loanoption/index.html', {'loanoptions': opts})
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
            if not request.user.has_perm('create loan option'):
                raise PermissionDenied('User lacks permission: create loan option')
            return render(request, 'loanoption/create.html')
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF,
                                             logger=logger,
                                             json={'error': 'Permission denied.'})
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    @transaction.atomic
    def store(cls, request: HttpRequest) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('create loan option'):
                raise PermissionDenied('User lacks permission: create loan option')
            name = request.POST.get('name')
            if not name:
                messages.error(request, 'Name is required.')
                return redirect(get_redirect_url(request))
            lo = LoanOption(name=name, created_by=request.user.creator_id())
            lo.save()
            messages.success(request, 'LoanOption successfully created.')
            return redirect('loanoption_index')
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def show(cls, request: HttpRequest, loanoption_id: int) -> HttpResponse:
        return redirect('loanoption_index')

    @classmethod
    def edit(cls, request: HttpRequest, loanoption_id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('edit loan option'):
                raise PermissionDenied('User lacks permission: edit loan option')
            lo = get_object_or_404(LoanOption, pk=loanoption_id)
            if lo.created_by != request.user.creator_id():
                raise PermissionDenied('Permission denied.')
            return render(request, 'loanoption/edit.html', {'loanoption': lo})
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF,
                                             logger=logger,
                                             json={'error': 'Permission denied.'})
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    @transaction.atomic
    def update(cls, request: HttpRequest, loanoption_id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('edit loan option'):
                raise PermissionDenied('User lacks permission: edit loan option')
            lo = get_object_or_404(LoanOption, pk=loanoption_id)
            if lo.created_by != request.user.creator_id():
                raise PermissionDenied('Permission denied.')
            name = request.POST.get('name')
            if not name:
                messages.error(request, 'Name is required.')
                return redirect(get_redirect_url(request))
            lo.name = name
            lo.save()
            messages.success(request, 'LoanOption successfully updated.')
            return redirect('loanoption_index')
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def destroy(cls, request: HttpRequest, loanoption_id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('delete loan option'):
                raise PermissionDenied('User lacks permission: delete loan option')
            lo = get_object_or_404(LoanOption, pk=loanoption_id)
            if lo.created_by != request.user.creator_id():
                raise PermissionDenied('Permission denied.')
            lo.delete()
            messages.success(request, 'LoanOption successfully deleted.')
            return redirect('loanoption_index')
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)
