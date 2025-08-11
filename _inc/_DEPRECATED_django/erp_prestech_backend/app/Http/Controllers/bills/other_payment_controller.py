import logging
import inspect
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.db import transaction
from django.http import HttpRequest, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from ....Models.individuals.employee import Employee
from ....Models.bills.other_payment import OtherPayment
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class OtherPaymentController(Controller):

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            inst = cls()
            inst.request = request
            inst.authorize('edit other payment')
            logger.info(OtherPayment.objects.all())
            return get_redirect_url(request)
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=ref, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)

    @classmethod
    def otherpayment_create(cls, request: HttpRequest, employee_id: str) -> HttpResponse:
        ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            inst = cls()
            inst.request = request
            inst.authorize('create other payment')
            employee = get_object_or_404(Employee, pk=employee_id)
            otherpaytypes = OtherPayment.other_payment_type
            return render(request, 'otherpayment/create.html', {
                'employee': employee,
                'otherpaytype': otherpaytypes
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=ref, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)

    @classmethod
    def store(cls, request: HttpRequest) -> HttpResponse:
        ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            inst = cls()
            inst.request = request
            inst.authorize('create other payment')
            eid = request.POST.get('employee_id')
            title = request.POST.get('title')
            amt = request.POST.get('amount')
            if not (eid and title and amt):
                messages.error(request, 'Employee, Title and Amount are required.')
                return redirect(get_redirect_url(request))
            with transaction.atomic():
                op_props = {}
                for k, v in {
                    "employee_id": eid,
                    "title": title,
                    "type": request.POST.get('type'),
                    "amount": amt,
                    "created_by": request.user.creator_id()
                }.items():
                    op_props[k] = v
                op = OtherPayment(**op_props)
                op.save()
            messages.success(request, 'OtherPayment successfully created.')
            return redirect(get_redirect_url(request))
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=ref, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)

    @classmethod
    def show(cls, request: HttpRequest, otherpayment_id: str) -> HttpResponse:
        # TODO: probably should render something rather than redirect
        return redirect('commission_index')

    @classmethod
    def edit(cls, request: HttpRequest, otherpayment_id: str) -> HttpResponse:
        ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            inst = cls()
            inst.request = request
            inst.authorize('edit other payment')
            op = get_object_or_404(OtherPayment, pk=otherpayment_id)
            if op.created_by != request.user.creator_id():
                raise PermissionDenied('User lacks permission.')
            otherpaytypes = OtherPayment.other_payment_type
            return render(request, 'otherpayment/edit.html', {
                'otherpayment': op,
                'otherpaytypes': otherpaytypes
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=ref, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)

    @classmethod
    def update(cls, request: HttpRequest, otherpayment_id: str) -> HttpResponse:
        ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            inst = cls()
            inst.request = request
            inst.authorize('edit other payment')
            op = get_object_or_404(OtherPayment, pk=otherpayment_id)
            if op.created_by != request.user.creator_id():
                raise PermissionDenied('User lacks permission.')
            title = request.POST.get('title')
            amt = request.POST.get('amount')
            if not (title and amt):
                messages.error(request, 'Title and Amount are required.')
                return redirect(get_redirect_url(request))
            for k, v in {
                "title": title,
                "type": request.POST.get('type'),
                "amount": amt
            }.items():
                op[k] = v
            op.save()
            messages.success(request, 'OtherPayment successfully updated.')
            return redirect(get_redirect_url(request))
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=ref, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)

    @classmethod
    def destroy(cls, request: HttpRequest, otherpayment_id: str) -> HttpResponse:
        ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            inst = cls()
            inst.request = request
            inst.authorize('delete other payment')
            op = get_object_or_404(OtherPayment, pk=otherpayment_id)
            if op.created_by != request.user.creator_id():
                raise PermissionDenied('User lacks permission.')
            op.delete()
            messages.success(request, 'OtherPayment successfully deleted.')
            return redirect(get_redirect_url(request))
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=ref, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)
