import logging
import inspect
from typing import Any
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core.exceptions import PermissionDenied
from django.db import transaction
from django.forms import ModelForm
from django.http import HttpRequest, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.utils.decorators import method_decorator
from django.views.decorators.http import require_http_methods
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller
from ....Models.bills.bank_transfer import BankTransfer
from ....Models.companies.bank_account import BankAccount
from ....Models.utils.utility import Utility

logger = logging.getLogger(__name__)

class BankTransferForm(ModelForm):
    class Meta:
        model = BankTransfer
        fields = [
            'from_account',
            'to_account',
            'amount',
            'date',
            'reference',
            'description'
        ]

class BankTransferController(ModelForm, Controller):
  
    @method_decorator(login_required)
    @classmethod
    def index(cls, request: HttpRequest, *args: Any, **kwargs: Any) -> HttpResponse:
        try:
            if not request.user.has_perm('manage_bank_transfer'):
                ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks permission: manage_bank_transfer'),
                    ref=ref,
                    logger=logger
                )
            account_qs = BankAccount.objects.filter(created_by=request.user.creator_id)
            account = dict([('', 'Select Account')] + [(a.id, a.holder_name) for a in account_qs])
            query = BankTransfer.objects.filter(created_by=request.user.creator_id)
            if request.GET.get('date'):
                dates = request.GET['date'].split(' to ')
                query = query.filter(date__range=[dates[0], dates[-1]])
            if request.GET.get('f_account'):
                query = query.filter(from_account=request.GET['f_account'])
            if request.GET.get('t_account'):
                query = query.filter(to_account=request.GET['t_account'])
            transfers = query.all()
            return render(request, 'bank_transfer/index.html', {
                'transfers': transfers,
                'account': account
            })
        except Exception as e:
            ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def create(cls, request: HttpRequest, *args: Any, **kwargs: Any) -> HttpResponse:
        try:
            if not request.user.has_perm('create_bank_transfer'):
                ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks permission: create_bank_transfer'),
                    ref=ref,
                    logger=logger
                )
            account_qs = BankAccount.objects.filter(created_by=request.user.creator_id)
            bank_account = {
                b.id: f"{b.bank_name} {b.holder_name}"
                for b in account_qs
            }
            return render(request, 'bank_transfer/create.html', {
                'bankAccount': bank_account
            })
        except Exception as e:
            ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)

    @method_decorator(login_required)
    @require_http_methods(["POST"])
    @classmethod
    def store(cls, request: HttpRequest, *args: Any, **kwargs: Any) -> HttpResponse:
        try:
            if not request.user.has_perm('create_bank_transfer'):
                ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks permission: create_bank_transfer'),
                    ref=ref,
                    logger=logger
                )
            with transaction.atomic():
                form = BankTransferForm(request.POST)
                if not form.is_valid():
                    messages.error(request, next(iter(form.errors.values()))[0])
                    return redirect('bank_transfer_index')
                transfer = form.save(commit=False)
                transfer.payment_method = 0
                transfer.created_by = request.user.creator_id
                transfer.save()
                Utility.bankAccountBalance(request.POST['from_account'], request.POST['amount'], 'debit')
                Utility.bankAccountBalance(request.POST['to_account'], request.POST['amount'], 'credit')
            messages.success(request, "Amount successfully transferred.")
            return redirect('bank_transfer_index')
        except Exception as e:
            ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def edit(cls, request: HttpRequest, id: int, *args: Any, **kwargs: Any) -> HttpResponse:
        try:
            if not request.user.has_perm('edit_bank_transfer'):
                ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks permission: edit_bank_transfer'),
                    ref=ref,
                    logger=logger
                )
            transfer = get_object_or_404(BankTransfer, id=id)
            account_qs = BankAccount.objects.filter(created_by=request.user.creator_id)
            bank_account = {
                b.id: f"{b.bank_name} {b.holder_name}"
                for b in account_qs
            }
            return render(request, 'bank_transfer/edit.html', {
                'bankAccount': bank_account,
                'transfer': transfer
            })
        except Exception as e:
            ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def update(cls, request: HttpRequest, id: int, *args: Any, **kwargs: Any) -> HttpResponse:
        try:
            if not request.user.has_perm('edit_bank_transfer'):
                ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks permission: edit_bank_transfer'),
                    ref=ref,
                    logger=logger
                )
            transfer = get_object_or_404(BankTransfer, id=id)
            with transaction.atomic():
                Utility.bankAccountBalance(transfer.from_account, transfer.amount, 'credit')
                Utility.bankAccountBalance(transfer.to_account, transfer.amount, 'debit')
                form = BankTransferForm(request.POST, instance=transfer)
                if not form.is_valid():
                    messages.error(request, next(iter(form.errors.values()))[0])
                    return redirect('bank_transfer_index')
                updated = form.save(commit=False)
                updated.payment_method = 0
                updated.save()
                Utility.bankAccountBalance(request.POST['from_account'], request.POST['amount'], 'debit')
                Utility.bankAccountBalance(request.POST['to_account'], request.POST['amount'], 'credit')
            messages.success(request, "Amount transfer successfully updated.")
            return redirect('bank_transfer_index')
        except Exception as e:
            ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def destroy(cls, request: HttpRequest, id: int, *args: Any, **kwargs: Any) -> HttpResponse:
        try:
            if not request.user.has_perm('delete_bank_transfer'):
                ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User lacks permission: delete_bank_transfer'),
                    ref=ref,
                    logger=logger
                )
            transfer = get_object_or_404(BankTransfer, id=id)
            if transfer.created_by != request.user.creator_id:
                ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
                return default_permission_denial(
                    request,
                    err=PermissionDenied('User is not the creator of this transfer'),
                    ref=ref,
                    logger=logger
                )
            with transaction.atomic():
                transfer.delete()
                Utility.bankAccountBalance(transfer.from_account, transfer.amount, 'credit')
                Utility.bankAccountBalance(transfer.to_account, transfer.amount, 'debit')
            messages.success(request, "Amount transfer successfully deleted.")
            return redirect('bank_transfer_index')
        except Exception as e:
            ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)
