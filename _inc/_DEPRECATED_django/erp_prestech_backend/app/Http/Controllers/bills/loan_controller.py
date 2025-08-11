import logging
import inspect
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core.exceptions import PermissionDenied
from django.db import transaction
from django.http import HttpRequest, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.utils.decorators import method_decorator
from .._helpers.http import get_redirect_url
from .._traits.controller import Controller
from ....Models.individuals.employee import Employee
from ....Models.bills.loan import Loan
from ....Models.bills.loan_option import LoanOption
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception

logger = logging.getLogger(__name__)

class LoanController(Controller):
    def __init__(self, **kwargs):
        super().__init__(**kwargs)
        self.middleware(['auth'])

    @method_decorator(login_required)
    @classmethod
    def loan_create(cls, request: HttpRequest, employee_id: int) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        # Permission check
        if not request.user.has_perm('create loan'):
            return default_permission_denial(
                request,
                err=PermissionDenied('User lacks permission: create loan'),
                ref=f'{CN}::{MN}',
                logger=logger
            )
        try:
            emp   = get_object_or_404(Employee, pk=employee_id)
            opts  = LoanOption.objects.filter(
                        created_by=request.user.creator_id()
                    ).values_list('name', 'id')
            types = getattr(Loan, 'LOAN_TYPES', [])
            return render(request, 'loan/create.html', {
                'employee':     emp,
                'loan_options': opts,
                'loan_types':   types
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    @transaction.atomic
    def store(cls, request: HttpRequest) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        if not request.user.has_perm('create loan'):
            return default_permission_denial(
                request,
                err=PermissionDenied('User lacks permission: create loan'),
                ref=f'{CN}::{MN}',
                logger=logger
            )
        try:
            data    = request.POST
            emp_id  = data.get('employee_id')
            opt     = data.get('loan_option')
            title   = data.get('title')
            amount  = data.get('amount')
            reason  = data.get('reason')
            if not (emp_id and opt and title and amount and reason):
                messages.error(request, 'All fields are required.')
                return redirect(get_redirect_url(request))

            ln = Loan(
                employee_id=emp_id,
                loan_option=opt,
                title=title,
                type=data.get('type'),
                amount=amount,
                reason=reason,
                created_by=request.user.creator_id()
            )
            ln.save()
            messages.success(request, 'Loan successfully created.')
            return redirect(get_redirect_url(request))

        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def show(cls, request: HttpRequest, loan_id: int) -> HttpResponse:
        # TODO CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        # No operation defined; simply redirect
        return redirect(get_redirect_url(request))

    @method_decorator(login_required)
    @classmethod
    def edit(cls, request: HttpRequest, loan_id: int) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        if not request.user.has_perm('edit loan'):
            return default_permission_denial(
                request,
                err=PermissionDenied('User lacks permission: edit loan'),
                ref=f'{CN}::{MN}',
                logger=logger
            )
        try:
            ln = get_object_or_404(Loan, pk=loan_id)
            if ln.created_by != request.user.creator_id():
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            opts  = LoanOption.objects.filter(
                        created_by=request.user.creator_id()
                    ).values_list('name', 'id')
            types = getattr(Loan, 'LOAN_TYPES', [])
            return render(request, 'loan/edit.html', {
                'loan':         ln,
                'loan_options': opts,
                'loan_types':   types
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    @transaction.atomic
    def update(cls, request: HttpRequest, loan_id: int) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        if not request.user.has_perm('edit loan'):
            return default_permission_denial(
                request,
                err=PermissionDenied('User lacks permission: edit loan'),
                ref=f'{CN}::{MN}',
                logger=logger
            )
        try:
            ln = get_object_or_404(Loan, pk=loan_id)
            if ln.created_by != request.user.creator_id():
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            d      = request.POST
            opt    = d.get('loan_option')
            title  = d.get('title')
            amount = d.get('amount')
            reason = d.get('reason')
            if not (opt and title and amount and reason):
                messages.error(request, 'All fields are required.')
                return redirect(get_redirect_url(request))

            ln.loan_option = opt
            ln.title       = title
            ln.type        = d.get('type')
            ln.amount      = amount
            ln.reason      = reason
            ln.save()
            messages.success(request, 'Loan successfully updated.')
            return redirect(get_redirect_url(request))

        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def destroy(cls, request: HttpRequest, loan_id: int) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        if not request.user.has_perm('delete loan'):
            return default_permission_denial(
                request,
                err=PermissionDenied('User lacks permission: delete loan'),
                ref=f'{CN}::{MN}',
                logger=logger
            )
        try:
            ln = get_object_or_404(Loan, pk=loan_id)
            if ln.created_by != request.user.creator_id():
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            ln.delete()
            messages.success(request, 'Loan successfully deleted.')
            return redirect(get_redirect_url(request))

        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
