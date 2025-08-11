import logging
import inspect
from typing import Any, Dict
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core.exceptions import ObjectDoesNotExist, PermissionDenied
from django.db import transaction, IntegrityError
from django.shortcuts import redirect, render
from django.utils.decorators import method_decorator
from ....Models.bills.bill import Bill
from ....Models.bills.debit_note import DebitNote
from ....Models.utils.utility import Utility
from .._helpers.http import get_redirect_url
from .._helpers.security import permission_required_custom
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._strategies.debit_note_strategy import DebitNoteStrategy
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class DebitNoteController(Controller):
    """
    Controller for handling Debit Note operations. Inherits from Django's View.
    All methods feature robust error handling, permission enforcement, and transactional integrity.
    """

    @classmethod
    def __init__(cls, **kwargs: Any) -> None:
        super().__init__(**kwargs)
        cls.middleware: list = []

    @method_decorator(login_required)
    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        if not request.user.has_perm('manage debit note'):
            return default_permission_denial(
                request,
                err=PermissionDenied('User lacks permission: manage debit note'),
                ref=f'{CN}::{MN}',
                logger=logger
            )
        try:
            bills = Bill.objects.filter(created_by=request.user.creator_id())
            return render(request, 'debitNote/index.html', {'bills': bills})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @permission_required_custom('create debit note', logger)
    @classmethod
    def create(cls, request: HttpRequest, bill_id: int) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            bill_due = Bill.objects.filter(id=bill_id).first()
            if not bill_due:
                messages.error(request, "Bill not found.")
                return redirect(get_redirect_url(request))
            return render(request, 'debitNote/create.html', {
                'bill_due': bill_due,
                'bill_id': bill_id
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @permission_required_custom('create debit note', logger)
    @classmethod
    def store(cls, request: HttpRequest, bill_id: int) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            if not request.POST.get('amount') or not request.POST.get('date'):
                messages.error(request, 'Amount and date are required.')
                return redirect(get_redirect_url(request))
            bill_due = Bill.objects.filter(id=bill_id).first()
            if not bill_due:
                messages.error(request, "Bill not found.")
                return redirect(get_redirect_url(request))
            try:
                amount: float = float(request.POST.get('amount'))
            except ValueError:
                messages.error(request, "Amount must be numeric.")
                return redirect(get_redirect_url(request))
            if amount > bill_due.get_due():
                formatted = request.user.price_format(bill_due.get_due())
                messages.error(request, f"Maximum {formatted} debit limit of this bill.")
                return redirect(get_redirect_url(request))
            with transaction.atomic():
                DebitNoteStrategy.create_debit_note(
                    bill_due, amount, request.POST.get('description', ''), request.POST.get('date'), request.user
                )
            messages.success(request, 'Debit Note successfully created!')
            return redirect(get_redirect_url(request))
        except (ValueError, ObjectDoesNotExist) as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except IntegrityError as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @permission_required_custom('edit debit note', logger)
    @classmethod
    def edit(cls, request: HttpRequest, bill_id: int, debit_note_id: int) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            debit_note = DebitNote.objects.get(pk=debit_note_id)
            return render(request, 'debitNote/edit.html', {'debit_note': debit_note})
        except ObjectDoesNotExist:
            messages.error(request, "Debit Note not found.")
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @permission_required_custom('edit debit note', logger)
    @classmethod
    def update(cls, request: HttpRequest, bill_id: int, debit_note_id: int) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            if not request.POST.get('amount') or not request.POST.get('date'):
                messages.error(request, 'Amount and date are required.')
                return redirect(get_redirect_url(request))
            bill_due = Bill.objects.filter(id=bill_id).first()
            if not bill_due:
                messages.error(request, "Bill not found.")
                return redirect(get_redirect_url(request))
            try:
                amount: float = float(request.POST.get('amount'))
            except ValueError:
                messages.error(request, "Amount must be numeric.")
                return redirect(get_redirect_url(request))
            if amount > bill_due.get_due():
                formatted = request.user.price_format(bill_due.get_due())
                messages.error(request, f"Maximum {formatted} debit limit of this bill.")
                return redirect(get_redirect_url(request))
            debit_note = DebitNote.objects.get(pk=debit_note_id)
            with transaction.atomic():
                DebitNoteStrategy.update_debit_note(
                    bill_due, debit_note, amount, request.POST.get('description', ''), request.POST.get('date'), request.user
                )
            messages.success(request, 'Debit Note successfully updated!')
            return redirect(get_redirect_url(request))
        except (ValueError, ObjectDoesNotExist) as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except IntegrityError as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @permission_required_custom('delete debit note', logger)
    @classmethod
    def destroy(cls, request: HttpRequest, bill_id: int, debit_note_id: int) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            debit_note = DebitNote.objects.get(pk=debit_note_id)
            with transaction.atomic():
                # TODO: confirm Utility.update_user_balance signature
                Utility.update_user_balance('vendor', debit_note.vendor, debit_note.amount, 'debit')
                debit_note.delete()
            messages.success(request, 'Debit Note successfully deleted!')
            return redirect(get_redirect_url(request))
        except ObjectDoesNotExist:
            messages.error(request, "Debit Note not found.")
            return redirect(get_redirect_url(request))
        except IntegrityError as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @permission_required_custom('create debit note', logger)
    @classmethod
    def custom_create(cls, request: HttpRequest) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            bills = Bill.objects.filter(created_by=request.user.creator_id(), type='Bill')
            bills_dict: Dict[int, Any] = {b.id: b.bill_id for b in bills}
            return render(request, 'debitNote/custom_create.html', {'bills': bills_dict})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @permission_required_custom('create debit note', logger)
    @classmethod
    def custom_store(cls, request: HttpRequest) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            for field in ['bill', 'amount', 'date']:
                if not request.POST.get(field):
                    messages.error(request, f"{field.capitalize()} is required.")
                    return redirect(get_redirect_url(request))
            try:
                bill_id = int(request.POST.get('bill'))
                amount = float(request.POST.get('amount'))
            except ValueError:
                messages.error(request, "Invalid bill id or amount must be numeric.")
                return redirect(get_redirect_url(request))
            bill_due = Bill.objects.filter(id=bill_id).first()
            if not bill_due:
                messages.error(request, "Bill not found.")
                return redirect(get_redirect_url(request))
            if amount > bill_due.get_due():
                formatted = request.user.price_format(bill_due.get_due())
                messages.error(request, f"Maximum {formatted} debit limit of this bill.")
                return redirect(get_redirect_url(request))
            with transaction.atomic():
                DebitNoteStrategy.create_debit_note(
                    bill_due, amount, request.POST.get('description', ''), request.POST.get('date'), request.user
                )
            messages.success(request, 'Debit Note successfully created!')
            return redirect(get_redirect_url(request))
        except (ValueError, ObjectDoesNotExist) as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except IntegrityError as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def get_bill(cls, request: HttpRequest) -> JsonResponse:
        # CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        bill_id = request.POST.get('bill_id')
        if not bill_id:
            return JsonResponse({'error': "Bill id is required."}, status=400)
        try:
            bill = Bill.objects.filter(id=bill_id).first()
            if not bill:
                return JsonResponse({'error': "Bill not found."}, status=404)
            return JsonResponse({'due': bill.get_due()}, status=200)
        except Exception:
            return JsonResponse({'error': "An error occurred while retrieving the bill."}, status=500)
