import logging
import inspect
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.db import transaction
from django.http import HttpRequest, HttpResponse
from django.shortcuts import render, redirect, get_object_or_404
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller
from ....Models.utils.utility import Utility
from ....Models.companies.bank_account import BankAccount
from ....Models.charts.chart_of_account import ChartOfAccount
from ....Models.shapes.journal_entry import JournalEntry
from ....Models.planning.journal_item import JournalItem

logger = logging.getLogger(__name__)


class JournalEntryController(Controller):

    @classmethod
    def _set_entry(cls, entry: JournalEntry, data: dict, user, journal_id=None) -> JournalEntry:
        if journal_id is not None:
            entry.journal_id = journal_id
        for field in ('date', 'reference', 'description'):
            setattr(entry, field, data.get(field))
        entry.created_by = user.creator_id()
        return entry

    @classmethod
    def _process_item(cls, item_data: dict, entry: JournalEntry) -> JournalItem:
        ji = JournalItem.objects.filter(id=item_data.get('id')).first() or JournalItem()
        ji.journal = entry.id
        for field in ('account', 'description', 'debit', 'credit'):
            setattr(ji, field, item_data.get(field, getattr(ji, field, 0)))
        ji.save()
        for ba in BankAccount.objects.filter(chart_account_id=ji.account):
            balance = ba.opening_balance - ji.debit if ji.debit else ba.opening_balance + ji.credit
            ba.opening_balance = balance
            ba.save()
        return ji

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            ctrl = cls(); ctrl.request = request; ctrl.authorize('manage journal entry')
            entries = JournalEntry.objects.filter(created_by=request.user.creator_id()).all()
            return render(request, 'journalEntry/index.html', {'journal_entries': entries})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        from django.db.models import Value
        from django.db.models.functions import Concat
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            ctrl = cls(); ctrl.request = request; ctrl.authorize('create journal entry')
            accounts = (
                ChartOfAccount.objects.filter(created_by=request.user.creator_id())
                .annotate(code_name=Concat('code', Value(' - '), 'name'))
                .values_list('code_name', 'id')
            )
            journal_id = cls.journal_number(request)
            return render(request, 'journalEntry/create.html', {'accounts': accounts, 'journal_id': journal_id})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def store(cls, request: HttpRequest) -> HttpResponse:
        import json
        from json import JSONDecodeError
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            ctrl = cls(); ctrl.request = request; ctrl.authorize('create journal entry')
            data = request.POST
            raw = data.get('accounts')
            try:
                accounts = json.loads(raw) if raw else []
            except JSONDecodeError as e:
                return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
            total_debit = sum(item.get('debit', 0) for item in accounts)
            total_credit = sum(item.get('credit', 0) for item in accounts)
            if total_debit != total_credit:
                messages.error(request, 'Debit and Credit must be Equal.')
                return redirect(get_redirect_url(request))
            with transaction.atomic():
                journal = cls._set_entry(JournalEntry(), data, request.user, cls.journal_number(request))
                journal.save()
                for item in accounts:
                    cls._process_item(item, journal)
            messages.success(request, 'Journal entry successfully created.')
            return redirect('journal_entry_index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def show(cls, request: HttpRequest, journal_entry_id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            ctrl = cls(); ctrl.request = request; ctrl.authorize('show journal entry')
            entry = get_object_or_404(JournalEntry, pk=journal_entry_id)
            entry.created_by == request.user.creator_id() or (_ for _ in ()).throw(PermissionDenied())
            accounts = JournalItem.objects.filter(journal=entry.id).all()
            settings = Utility.settings()
            return render(request, 'journalEntry/view.html', {
                'journal_entry': entry,
                'accounts': accounts,
                'settings': settings
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def edit(cls, request: HttpRequest, journal_entry_id: int) -> HttpResponse:
        from django.db.models import Value
        from django.db.models.functions import Concat
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            ctrl = cls(); ctrl.request = request; ctrl.authorize('edit journal entry')
            entry = get_object_or_404(JournalEntry, pk=journal_entry_id)
            accounts = (
                ChartOfAccount.objects.filter(created_by=request.user.creator_id())
                .annotate(code_name=Concat('code', Value(' - '), 'name'))
                .values_list('id', 'id')
            )
            return render(request, 'journalEntry/edit.html', {
                'journal_entry': entry,
                'accounts': accounts
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def update(cls, request: HttpRequest, journal_entry_id: int) -> HttpResponse:
        import json
        from json import JSONDecodeError
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            ctrl = cls(); ctrl.request = request; ctrl.authorize('edit journal entry')
            entry = get_object_or_404(JournalEntry, pk=journal_entry_id)
            data = request.POST
            raw = data.get('accounts')
            try:
                accounts = json.loads(raw) if raw else []
            except JSONDecodeError as e:
                return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
            total_debit = sum(item.get('debit', 0) for item in accounts)
            total_credit = sum(item.get('credit', 0) for item in accounts)
            if total_debit != total_credit:
                messages.error(request, 'Debit and Credit must be Equal.')
                return redirect(get_redirect_url(request))
            with transaction.atomic():
                cls._set_entry(entry, data, request.user).save()
                for item in accounts:
                    cls._process_item(item, entry)
            messages.success(request, 'Journal entry successfully updated.')
            return redirect('journal_entry_index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def destroy(cls, request: HttpRequest, journal_entry_id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            ctrl = cls(); ctrl.request = request; ctrl.authorize('delete journal entry')
            entry = get_object_or_404(JournalEntry, pk=journal_entry_id)
            with transaction.atomic():
                JournalItem.objects.filter(journal=entry.id).delete()
                entry.delete()
            messages.success(request, 'Journal entry successfully deleted.')
            return redirect('journal_entry_index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def journal_number(cls, request: HttpRequest) -> int:
        latest = JournalEntry.objects.filter(created_by=request.user.creator_id()).order_by('-id').first()
        return 1 if not latest else latest.journal_id + 1

    @classmethod
    def account_destroy(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            ctrl = cls(); ctrl.request = request; ctrl.authorize('delete journal entry')
            JournalItem.objects.filter(id=request.POST.get('id')).delete()
            messages.success(request, 'Journal entry account successfully deleted.')
            return redirect(get_redirect_url(request))
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def journal_destroy(cls, request: HttpRequest, item_id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            ctrl = cls(); ctrl.request = request; ctrl.authorize('delete journal entry')
            JournalItem.objects.filter(id=item_id).delete()
            messages.success(request, 'Journal account successfully deleted.')
            return redirect(get_redirect_url(request))
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
