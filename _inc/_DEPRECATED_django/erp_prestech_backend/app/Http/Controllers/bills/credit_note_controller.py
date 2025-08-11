import logging
import inspect
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core.exceptions import ObjectDoesNotExist, PermissionDenied
from django.db import transaction, IntegrityError
from django.http import JsonResponse, HttpRequest, HttpResponse
from django.shortcuts import redirect, render
from django.utils.decorators import method_decorator
from django.views.decorators.http import require_http_methods
from ....Models.bills.credit_note import CreditNote
from ....Models.bills.invoice import Invoice
from ....Models.utils.utility import Utility
from .._helpers.http import get_redirect_url
from .._helpers.security import permission_required_custom
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._strategies import CreditNoteStrategy
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class CreditNoteController(Controller):
    """
    Controller for managing Credit Note-related operations.
    All methods include improved error handling, transaction management,
    and separation of financial logic where applicable.
    """

    @method_decorator(login_required)
    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        """Display a list of invoices for users who manage credit notes."""
        if not request.user.has_perm('manage credit note'):
            return default_permission_denial(
                request,
                err=PermissionDenied('User lacks permission: manage credit note'),
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger
            )
        try:
            invoices = Invoice.objects.filter(created_by=request.user.creator_id)
            return render(request, 'creditNote/index.html', {'invoices': invoices})
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger
            )

    @permission_required_custom('create credit note', logger)
    @method_decorator(login_required)
    @classmethod
    def create(cls, request: HttpRequest, invoice_id: int) -> HttpResponse:
        """Render form for creating a new credit note."""
        try:
            invoice_due = Invoice.objects.filter(id=invoice_id).first()
            if not invoice_due:
                messages.error(request, "Invoice not found.")
                return redirect(get_redirect_url(request))
            return render(request, 'creditNote/create.html', {
                'invoice_due': invoice_due,
                'invoice_id': invoice_id
            })
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger
            )

    @permission_required_custom('create credit note', logger)
    @require_http_methods(["POST"])
    @method_decorator(login_required)
    @classmethod
    def store(cls, request: HttpRequest, invoice_id: int) -> HttpResponse:
        """Store a new credit note atomically via CreditNoteStrategy."""
        if not request.POST.get('amount') or not request.POST.get('date'):
            messages.error(request, "Amount and date are required.")
            return redirect(get_redirect_url(request))
        try:
            amount = float(request.POST.get('amount'))
        except ValueError:
            messages.error(request, "Amount must be numeric.")
            return redirect(get_redirect_url(request))

        try:
            invoice = Invoice.objects.get(id=invoice_id)
        except ObjectDoesNotExist as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger
            )

        description = request.POST.get('description', '')

        try:
            with transaction.atomic():
                CreditNoteStrategy.create_credit_note(
                    invoice, amount, description, request.POST.get('date'), request.user
                )
            messages.success(request, "Credit Note successfully created.")
            return redirect(get_redirect_url(request))
        except (ValueError, ObjectDoesNotExist) as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger
            )
        except IntegrityError as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger
            )
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger
            )

    @permission_required_custom('edit credit note', logger)
    @method_decorator(login_required)
    @classmethod
    def edit(cls, request: HttpRequest, invoice_id: int, credit_note_id: int) -> HttpResponse:
        """Render edit form for an existing credit note."""
        try:
            credit_note = CreditNote.objects.filter(id=credit_note_id).first()
            if not credit_note:
                messages.error(request, "Credit Note not found.")
                return redirect(get_redirect_url(request))
            return render(request, 'creditNote/edit.html', {'credit_note': credit_note})
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger
            )

    @permission_required_custom('edit credit note', logger)
    @require_http_methods(["POST"])
    @method_decorator(login_required)
    @classmethod
    def update(cls, request: HttpRequest, invoice_id: int, credit_note_id: int) -> HttpResponse:
        """Update an existing credit note under a transaction."""
        if not request.POST.get('amount') or not request.POST.get('date'):
            messages.error(request, "Amount and date are required.")
            return redirect(get_redirect_url(request))
        try:
            amount = float(request.POST.get('amount'))
        except ValueError:
            messages.error(request, "Amount must be numeric.")
            return redirect(get_redirect_url(request))

        try:
            invoice = Invoice.objects.get(id=invoice_id)
            credit_note = CreditNote.objects.get(id=credit_note_id)
        except ObjectDoesNotExist as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger
            )

        description = request.POST.get('description', '')

        try:
            with transaction.atomic():
                CreditNoteStrategy.update_credit_note(
                    invoice, credit_note, amount, description, request.POST.get('date'), request.user
                )
            messages.success(request, "Credit Note successfully updated.")
            return redirect(get_redirect_url(request))
        except IntegrityError as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger
            )
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger
            )

    @permission_required_custom('delete credit note', logger)
    @method_decorator(login_required)
    @classmethod
    def destroy(cls, request: HttpRequest, invoice_id: int, credit_note_id: int) -> HttpResponse:
        """Delete a credit note and reverse its balance effect."""
        try:
            credit_note = CreditNote.objects.get(id=credit_note_id)
            with transaction.atomic():
                Utility.update_user_balance(
                    'customer', credit_note.customer, credit_note.amount, 'credit'
                )
                credit_note.delete()
            messages.success(request, "Credit Note successfully deleted.")
            return redirect(get_redirect_url(request))
        except ObjectDoesNotExist:
            messages.error(request, "Credit Note not found.")
            return redirect(get_redirect_url(request))
        except IntegrityError as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger
            )
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger
            )

    @permission_required_custom('create credit note', logger)
    @method_decorator(login_required)
    @classmethod
    def custom_create(cls, request: HttpRequest) -> HttpResponse:
        """Render custom creation form for credit notes."""
        try:
            invoices = {
                item['id']: item['invoice_id']
                for item in Invoice.objects.filter(created_by=request.user.creator_id)
                                             .values('id', 'invoice_id')
            }
            return render(request, 'creditNote/custom_create.html', {'invoices': invoices})
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger
            )

    @permission_required_custom('create credit note', logger)
    @method_decorator(login_required)
    @classmethod
    def custom_store(cls, request: HttpRequest) -> HttpResponse:
        """Store a credit note via custom form, ensuring atomicity."""
        if not request.POST.get('invoice') or not request.POST.get('amount') or not request.POST.get('date'):
            messages.error(request, "Invoice, amount and date are required.")
            return redirect(get_redirect_url(request))
        try:
            invoice_id = int(request.POST.get('invoice'))
            amount = float(request.POST.get('amount'))
        except ValueError:
            messages.error(request, "Invalid invoice id or amount must be numeric.")
            return redirect(get_redirect_url(request))

        try:
            invoice = Invoice.objects.get(id=invoice_id)
        except ObjectDoesNotExist as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger
            )

        description = request.POST.get('description', '')

        try:
            with transaction.atomic():
                CreditNoteStrategy.create_credit_note(
                    invoice, amount, description, request.POST.get('date'), request.user
                )
            messages.success(request, "Credit Note successfully created.")
            return redirect(get_redirect_url(request))
        except IntegrityError as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger
            )
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger
            )

    @classmethod
    def getinvoice(cls, request: HttpRequest) -> JsonResponse:
        """Retrieve invoice due amount via JSON (AJAX)."""
        invoice_id = request.POST.get('id')
        if not invoice_id:
            return JsonResponse({'error': "Invoice id is required."}, status=400)
        try:
            invoice = Invoice.objects.get(id=invoice_id)
            return JsonResponse(invoice.get_due(), safe=False)
        except ObjectDoesNotExist:
            return JsonResponse({'error': "Invoice not found."}, status=404)
        except Exception as e:
            return JsonResponse({'error': f"Error retrieving invoice: {e}"}, status=500)
