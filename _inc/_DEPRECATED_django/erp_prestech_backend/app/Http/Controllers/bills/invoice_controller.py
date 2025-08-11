from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.core.signing import loads, BadSignature
from django.db import transaction
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import redirect, render, get_object_or_404
from django.utils.dateparse import parse_date
from twilio.rest import Client
import logging
import json
import requests
from ....configs.messages_templates import get_exception_class_message, get_lacking_field_message
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial
from .._traits.controller import Controller
from ....Models.bills.credit_note import CreditNote
from ....Models.bills.invoice import Invoice
from ....Models.bills.invoice_payment import InvoicePayment
from ....Models.bills.invoice_product import InvoiceProduct
from ....Models.bills.invoice_bank_transfer import InvoiceBankTransfer
from ....Models.bills.stock_report import StockReport
from ....Models.bills.transaction import Transaction
from ....Models.companies.bank_account import BankAccount
from ....Models.individuals.customer import Customer
from ....Models.individuals.user import User
from ....Models.planning.plan import Plan
from ....Models.products.product_service import ProductService
from ....Models.products.product_service_category import ProductServiceCategory
from ....Models.shapes.custom_field import CustomField
from ....Models.utils.utility import Utility
import inspect

logger = logging.getLogger(__name__)

class InvoiceController(Controller):

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('manage_invoice'):
                raise PermissionDenied('User lacks permission: manage_invoice')
            customers = [('', "Select Customer")]
            customers += list(Customer.objects.filter(created_by=request.user.creator_id()
                         ).values_list('name','id'))
            status = Invoice.statuses
            qs = Invoice.objects.filter(created_by=request.user.creator_id())
            invoices = qs.all()
            cust = request.GET.get('customer')
            if cust:
                qs = qs.filter(customer_id=cust)
            start = request.GET.get('start_date')
            if start:
                qs = qs.filter(issue_date__gte=start)
            end = request.GET.get('end_date')
            if end:
                qs = qs.filter(issue_date__LTE=end)
            st = request.GET.get('status')
            if st:
                qs = qs.filter(status=st)
            invoices = qs.order_by('-issue_date')
            return render(request, 'invoice/index.html',
                          {'invoices': invoices, 'customers': customers, 'status': status})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    def create(cls, request: HttpRequest, customer_id: int) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('create_invoice'):
                raise PermissionDenied('User lacks permission: create_invoice')
            custom_fields = CustomField.objects.filter(created_by=request.user.creator_id(),
                                                       module='invoice')
            invoice_number = request.user.invoice_number_format(cls.invoice_number())
            customers = [('', 'Select Customer')]
            customers += list(Customer.objects.filter(created_by=request.user.creator_id()
                        ).values_list('name','id'))
            categories = [('', 'Select Categories')]
            categories += list(ProductServiceCategory.objects.filter(created_by=request.user.creator_id(),
                                                                      type='income').values_list('name', 'id'))
            services = [('', 'Select Services')]
            services += list(ProductService.objects.filter(created_by=request.user.creator_id()
                       ).values_list('name','id'))
            return render(request, 'invoice/create.html', {
                'customers':        customers,
                'invoice_number':   invoice_number,
                'product_services': services,
                'category':         categories,
                'custom_fields':    custom_fields,
                'customer_id':      customer_id,
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    def customer_detail(cls, request: HttpRequest) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            customer = Customer.objects.filter(id=request.GET.get('id')).first()
            return render(request, 'invoice/customer_detail.html',
                          {'customer': customer})
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    def product(cls, request: HttpRequest) -> JsonResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            product = ProductService.objects.get(pk=request.POST.get('product_id'))
            unit = product.unit.name if hasattr(product,'unit') else ''
            tax_rate = product.tax_rate(product.tax_id) if product.tax_id else 0
            taxes = product.tax(product.tax_id) if product.tax_id else 0
            sale_price = product.sale_price
            quantity = 1
            tax_price = (tax_rate/100)*(sale_price*quantity)
            total_amount = (sale_price*quantity)
            data = {
                'product': product, 'unit': unit,
                'taxRate': tax_rate, 'taxes': taxes,
                'totalAmount': total_amount,
                'taxPrice': tax_price
            }
            return JsonResponse(data, safe=False)
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    @transaction.atomic
    def store(cls, request: HttpRequest) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('create_invoice'):
                logger.error('Attempt to create invoice without permission halted')
                messages.error(request, get_exception_class_message(PermissionDenied, cls.__name__))
                raise PermissionDenied('User lacks permission: create_invoice')
            for field in ('customer_id', 'issue_date', 'due_date', 'category_id', 'items'):
                if not request.POST.get(field):
                    messages.error(request, get_lacking_field_message(field, cls.__name__))
                    return redirect(get_redirect_url(request))
            invoice = Invoice.objects.create(
                invoice_id   = cls.invoice_number(),
                customer_id  = request.POST['customer_id'],
                status       = 0,
                issue_date   = request.POST['issue_date'],
                due_date     = request.POST['due_date'],
                category_id  = request.POST['category_id'],
                ref_number   = request.POST.get('ref_number', ''),
                created_by   = request.user.creator_id()
            )
            invoice.save()
            CustomField.save_data(invoice, request.POST.get('customField', {}))
            try:
                items_data = json.loads(request.POST['items'])
            except ValueError:
                # TODO: fix quoting in f-string below
                messages.error(request, f"Invalid items JSON: {items_data or request.POST['items'] or '# ERROR_LOADING_JSON'}")
                return get_redirect_url(request)
            for item in items_data:
                prod = InvoiceProduct.objects.create(
                    invoice_id = invoice.id,
                    product_id = item['product_id'],
                    quantity   = item['quantity'],
                    rate       = item.get('rate', 0),
                    tax        = item.get('tax', '')
                )
                Utility.total_quantity('minus', prod.quantity, prod.product_id)
                text = f"New invoice *{invoice.id}* created."
                slack_url = Utility.get_val_by_name('slack_webhook')
                if slack_url:
                    try:
                        requests.post(slack_url, json={'text': text})
                    except Exception as e:
                        logger.error("Slack notify failed: %s", e)
                tg_token = Utility.get_val_by_name('telegram_accestoken')
                tg_chat  = Utility.get_val_by_name('telegram_chatid')
                if tg_token and tg_chat:
                    try:
                        requests.get(
                            f"https://api.telegram.org/bot{tg_token}/sendMessage",
                            params={'chat_id': tg_chat, 'text': text}
                        )
                    except Exception as e:
                        logger.error("Telegram notify failed: %s", e)
                tw_sid   = Utility.get_val_by_name('twilio_sid')
                tw_token = Utility.get_val_by_name('twilio_token')
                tw_from  = Utility.get_val_by_name('twilio_from')
                if tw_sid and tw_token and tw_from:
                    try:
                        cust = Customer.objects.get(pk=invoice.customer_id)
                        client = Client(tw_sid, tw_token)
                        client.messages.create(
                            body = f"Your invoice {invoice.id} is ready.",
                            from_=tw_from,
                            to   = cust.phone
                        )
                    except Exception as e:
                        logger.error("Twilio SMS failed: %s", e)
            StockReport.objects.filter(type='invoice', type_id=invoice.id).delete()
            for items in items_data:
                StockReport.objects.create(
                    type='invoice', type_id=invoice.id, product_id=items['product_id'],
                    quantity=items['quantity'], created_by=request.user.creator_id()
                )
            webhook_url = Utility.get_val_by_name('invoice_webhook_url')
            if webhook_url:
                try:
                    requests.post(webhook_url, json={
                        'invoice_id': invoice.id,
                        'created_by': request.user.creator_id()
                    })
                except Exception as e:
                    logger.error('Invoice webhook failed: %s', e)
            messages.success(request, 'Invoice successfully created.')
            return redirect('invoice.index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    def edit(cls, request: HttpRequest, encrypted_id: str) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('edit_invoice'):
                raise PermissionDenied('User lacks permission: edit_invoice')
            try:
                decrypted_id = loads(encrypted_id)
            except BadSignature:
                messages.error(request, "Encrypted id invalid or malformed")
                return redirect(get_redirect_url(request))
            except Exception as e:
                msg = f'Undefined error occurred while trying to load encrypted id: {e}'
                logger.warning(msg)
                messages.error(request, msg)
                return redirect(get_redirect_url(request))
            invoice = get_object_or_404(Invoice, pk=decrypted_id)
            invoice_number = request.user.invoice_number_format(invoice.id)
            customers = Customer.objects.filter(created_by=request.user.creator_id()
                        ).values_list('name','id')
            categories = ProductServiceCategory.objects.filter(
                         created_by=request.user.creator_id(), type='income'
                       ).values_list('name','id')
            services = ProductService.objects.filter(created_by=request.user.creator_id()
                       ).values_list('name','id')
            custom_fields = CustomField.objects.filter(created_by=request.user.creator_id(),
                               module='invoice')
            invoice.custom_fields = CustomField.get_data(invoice,'invoice')
            return render(request, 'invoice/edit.html', {
                'customers': customers, 'product_services': services,
                'invoice': invoice, 'invoice_number': invoice_number,
                'category': categories, 'custom_fields': custom_fields
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger, json={})
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    def update(cls, request: HttpRequest, invoice: Invoice) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('edit_invoice'):
                raise PermissionDenied('User lacks permission: edit_invoice')
            if invoice.created_by != request.user.creator_id():
                messages.error(request, get_exception_class_message(PermissionDenied, cls.__name__))
                return redirect('invoice.index')
            for f in ('customer_id','issue_date','due_date','category_id','items'):
                if not request.POST.get(f):
                    messages.error(request, get_lacking_field_message(f, cls.__name__))
                    return redirect('invoice.index')
            invoice.customer_id = request.POST.get('customer_id')
            invoice.issue_date = request.POST.get('issue_date')
            invoice.due_date = request.POST.get('due_date')
            invoice.ref_number = request.POST.get('ref_number')
            invoice.category_id = request.POST.get('category_id')
            invoice.save()
            Utility.starting_number(invoice.id+1,'invoice')
            CustomField.save_data(invoice, request.POST.get('customField', {}))
            old_items = InvoiceProduct.objects.filter(invoice_id=invoice.id)
            for old in old_items:
                Utility.total_quantity('plus', old.quantity, old.product_id)
            old_items.delete()
            try:
                items_data = json.loads(request.POST['items'])
            except ValueError:
                messages.error(request, "Invalid items data.")
                return redirect('invoice.index')
            for item in items_data:
                prod = InvoiceProduct.objects.create(
                    invoice_id = invoice.id,
                    product_id = item['product_id'],
                    quantity   = item['quantity'],
                    rate       = item.get('rate', 0),
                    tax        = item.get('tax', '')
                )
                Utility.total_quantity('minus', prod.quantity, prod.product_id)
            StockReport.objects.filter(type='invoice', type_id=invoice.id).delete()
            for item in items_data:
                StockReport.objects.create(
                    type       = 'invoice',
                    type_id    = invoice.id,
                    product_id = item['product_id'],
                    quantity   = item['quantity'],
                    created_by = request.user.creator_id()
                )
            messages.success(request, 'Invoice successfully updated')
            return redirect('invoice.index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    def invoice_number(cls) -> int:
        # TODO: Controller.request likely incorrect; no request context available
        latest = Invoice.objects.filter(created_by=Controller.request.user.creator_id()
                 ).order_by('-id').first()
        return (latest.invoice_id+1) if latest else 1

    @classmethod
    def _success(cls, request: HttpRequest, msg: str,
                 redirect_to: str = 'invoice.index') -> HttpResponse:
        messages.success(request, msg)
        return redirect(redirect_to)

    @classmethod
    def _error(cls, request: HttpRequest, msg: str,
               ref: str = None) -> HttpResponse:
        messages.error(request, msg)
        return redirect(ref or get_redirect_url(request))

    @classmethod
    def show(cls, request: HttpRequest, encrypted_id: str) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('show_invoice'):
                raise PermissionDenied('show_invoice')
            try:
                invoice_id = Utility.decrypt(encrypted_id)
            except Exception:
                return cls._error(request, 'Invoice Not Found.')
            invoice = get_object_or_404(
                Invoice.objects.select_related('customer'),
                pk=invoice_id, created_by=request.user.creator_id())
            payments = InvoicePayment.objects.filter(invoice_id=invoice.id).first()
            custom_fields = CustomField.objects.filter(
                created_by=request.user.creator_id(), module='invoice')
            invoice.custom_fields = CustomField.get_data(invoice,'invoice')
            return render(request, 'invoice/view.html', {
                'invoice': invoice, 'customer': invoice.customer,
                'items': invoice.items, 'invoice_payment': payments,
                'custom_fields': custom_fields, 'user': request.user
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    @transaction.atomic
    def destroy(cls, request: HttpRequest, invoice_id: int) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('delete_invoice'):
                raise PermissionDenied('delete_invoice')
            invoice = get_object_or_404(Invoice, pk=invoice_id)
            if invoice.created_by != request.user.creator_id():
                return cls._error(request, get_exception_class_message(PermissionDenied, cls.__name__))
            for pay in list(invoice.payments.all()):
                Utility.bank_account_balance(pay.account_id, pay.amount, 'debit')
                pay.delete()
            if invoice.customer_id and invoice.status != 0:
                Utility.update_user_balance('customer', invoice.customer_id,
                                            invoice.get_due(), 'debit')
            CreditNote.objects.filter(invoice=invoice.id).delete()
            InvoiceProduct.objects.filter(invoice_id=invoice.id).delete()
            invoice.delete()
            return cls._success(request, 'Invoice deleted')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    @transaction.atomic
    def product_destroy(cls, request: HttpRequest) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('delete_invoice_product'):
                raise PermissionDenied('delete_invoice_product')
            prod_id = request.POST.get('id')
            amount = float(request.POST.get('amount',0))
            inv_prod = get_object_or_404(InvoiceProduct, pk=prod_id)
            invoice = inv_prod.invoice
            Utility.update_user_balance('customer', invoice.customer_id,
                                        amount, 'debit')
            inv_prod.delete()
            return cls._success(request, 'Product removed')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    def customer_invoice(cls, request: HttpRequest) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('manage_customer_invoice'):
                raise PermissionDenied('manage_customer_invoice')
            status = Invoice.statuses
            query = Invoice.objects.filter(customer_id=request.user.id,
                     status__gt=0, created_by=request.user.creator_id())
            if 'issue_date' in request.GET:
                dr = request.GET['issue_date'].split(' - ')
                query = query.filter(issue_date__range=dr)
            if 'status' in request.GET and request.GET['status']:
                query = query.filter(status=request.GET['status'])
            invoices = query.all()
            return render(request, 'invoice/index.html',
                          {'invoices': invoices, 'status': status})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    def customer_invoice_show(cls, request: HttpRequest, invoice_id: int) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            invoice = get_object_or_404(
                Invoice.objects.select_related('customer'),
                pk=invoice_id)
            creator = User.objects.get(pk=invoice.created_by)
            if creator.creator_id() != invoice.created_by:
                return cls._error(request, get_exception_class_message(PermissionDenied, cls.__name__))
            tpl = 'invoice.view.html' if creator.type=='super admin' \
                  else 'invoice/customer_invoice.html'
            return render(request, tpl, {
                'invoice': invoice, 'customer': invoice.customer,
                'items': invoice.items, 'user': creator
            })
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise 
          
    @classmethod
    @transaction.atomic
    def send(cls, request: HttpRequest, invoice_id: int) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('send_invoice'):
                raise PermissionDenied('send_invoice')
            settings = Utility.settings(request.user.creator_id())
            invoice = get_object_or_404(Invoice, pk=invoice_id)
            invoice.send_date = Utility.today()
            invoice.status = 1
            invoice.save()
            customer = Customer.objects.get(pk=invoice.customer_id)
            Utility.update_user_balance('customer', customer.id,
                                        invoice.get_total(), 'credit')
            if settings.get('customer_invoice_sent'):
                Utility.notify_email('customer_invoice_sent', customer.id, {
                    'invoice_number': request.user.invoice_number_format(invoice.id),
                    'invoice_url': Utility.route('invoice.pdf', Utility.encrypt(invoice.id))
                })
            return cls._success(request, 'Invoice sent')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    def resend(cls, request: HttpRequest, invoice_id: int) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('send_invoice'):
                raise PermissionDenied('send_invoice')
            invoice = get_object_or_404(Invoice, pk=invoice_id)
            customer = Customer.objects.get(pk=invoice.customer_id)
            Utility.notify_email('customer_invoice_sent', customer.id, {
                'invoice_number': request.user.invoice_number_format(invoice.id),
                'invoice_url': Utility.route('invoice.pdf', Utility.encrypt(invoice.id))
            })
            return cls._success(request, 'Invoice resent')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    def payment(cls, request: HttpRequest, invoice_id: int) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('create_payment_invoice'):
                raise PermissionDenied('create_payment_invoice')
            invoice = get_object_or_404(Invoice, pk=invoice_id)
            customers = Customer.objects.filter(
                created_by=request.user.creator_id()
            ).values_list('name', 'id')
            categories = ProductServiceCategory.objects.filter(
                created_by=request.user.creator_id()
            ).values_list('name', 'id')
            accounts = BankAccount.objects.filter(
                created_by=request.user.creator_id()
            ).annotate(name_concat=BankAccount.concat_name()
            ).values_list('name_concat', 'id')
            return render(request, 'invoice/payment.html', {
                'customers': customers, 'categories': categories,
                'accounts': accounts, 'invoice': invoice
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    @transaction.atomic
    def create_payment(cls, request: HttpRequest, invoice_id: int) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('create_payment_invoice'):
                raise PermissionDenied('create_payment_invoice')
            invoice = get_object_or_404(Invoice, pk=invoice_id)
            try:
                amount = float(request.POST.get('amount', 0))
            except ValueError:
                return cls._error(request, 'Invalid amount')
            except Exception as e:
                msg = f'Unknown error raised while trying to evaluate amount for creation of payment: {e}'
                logger.warn(msg)
                messages.error(request, msg)
                raise
            if amount > invoice.get_sub_total():
                return cls._error(request, 'Amount must not exceed subtotal')
            parsed_date = parse_date(request.POST.get('date'))
            if not parsed_date:
                return cls._error(request, f'Invalid payment date: {parsed_date}')
            account_id = request.POST.get('account_id')
            if not account_id or not BankAccount.objects.filter(pk=account_id).exists():
                return cls._error(request, f'Invalid account ({account_id}) selected')
            pay = InvoicePayment(
                invoice_id     = invoice_id,
                date           = parsed_date,
                amount         = amount,
                account_id     = account_id,
                payment_method = request.POST.get('payment_method', 0),
                reference      = request.POST.get('reference', '').strip(),
                description    = request.POST.get('description', '').strip(),
                created_by     = request.user.id,
                user_id        = invoice.customer_id,
                user_type      = 'Customer',
                type           = 'Partial',
                category       = 'Invoice'
            )
            if request.FILES.get('add_receipt'):
                file = request.FILES['add_receipt']
                ok, fname = Utility.store_payment_receipt(
                    file, request.user.creator_id()
                )
                if not ok:
                    return cls._error(request, fname)
                pay.add_receipt = fname
            pay.save()
            invoice.status = 4 if invoice.get_due() <= 0 else 3
            if invoice.status == 0:
                invoice.send_date = Utility.today()
            invoice.save()
            pay.user_id   = invoice.customer_id
            pay.user_type = 'Customer'
            pay.type      = 'Partial'
            pay.created_by= request.user.id
            pay.category  = 'Invoice'
            Transaction.add_transaction(pay)
            Utility.update_user_balance('customer', invoice.customer_id, amount, 'debit')
            Utility.bank_account_balance(pay.account_id, amount, 'credit')
            Utility.notify_email('new_invoice_payment', invoice.customer_id, {
                'amount': amount,
                'invoice_number': request.user.invoice_number_format(invoice.id)
            })
            Utility.fire_webhook('New Invoice Payment', invoice)
            return cls._success(request, 'Payment added')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise
    
    @classmethod
    @transaction.atomic
    def payment_destroy(cls, request: HttpRequest, invoice_id: int, payment_id: int) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('delete_payment_invoice'):
                raise PermissionDenied('delete_payment_invoice')
            payment = get_object_or_404(InvoicePayment, pk=payment_id)
            invoice = get_object_or_404(Invoice, pk=invoice_id)
            payment.delete()
            InvoiceBankTransfer.objects.filter(pk=payment_id).delete()
            invoice.status = 3 if invoice.get_due() > 0 and invoice.get_total() != invoice.get_due() else 2
            invoice.save()
            if payment.add_receipt:
                Utility.delete_file('uploads/payment/', payment.add_receipt, invoice.created_by)
            Transaction.destroy_transaction(payment_id, 'Partial', 'Customer')
            Utility.update_user_balance('customer', invoice.customer_id, payment.amount, 'credit')
            Utility.bank_account_balance(payment.account_id, payment.amount, 'debit')
            return cls._success(request, 'Payment deleted')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    def payment_reminder(cls, request: HttpRequest, invoice_id: int) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            invoice  = get_object_or_404(Invoice, pk=invoice_id)
            customer = Customer.objects.get(pk=invoice.customer_id)
            Utility.notify_sms('invoice_payment_reminder', customer.contact, {
                'invoice_number': request.user.invoice_number_format(invoice.id)
            })
            Utility.notify_email('new_payment_reminder', customer.id, {
                'invoice_number': request.user.invoice_number_format(invoice.id),
                'due_amount': invoice.get_due()
            })
            return cls._success(request, 'Reminder sent')
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    def customer_invoice_send(cls, request: HttpRequest, invoice_id: int) -> HttpResponse:
        return render(request, 'customer/invoice_send.html', {'invoice_id': invoice_id})

    @classmethod
    def customer_invoice_send_mail(cls, request: HttpRequest, invoice_id: int) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            email = request.POST.get('email')
            if not email:
                return cls._error(request, 'Valid email required')
            invoice  = get_object_or_404(Invoice, pk=invoice_id)
            customer = Customer.objects.get(pk=invoice.customer_id)
            Utility.send_raw_mail(email, 'Invoice',
                Utility.render_invoice_email(invoice, customer))
            return cls._success(request, 'Invoice sent')
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    def shipping_display(cls, request: HttpRequest, invoice_id: int) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            invoice = get_object_or_404(Invoice, pk=invoice_id)
            invoice.shipping_display = 1 if request.GET.get('is_display') == 'true' else 0
            invoice.save()
            return cls._success(request, 'Shipping address visibility updated',
                                ref=request.META.get('HTTP_REFERER', '/'))
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    @transaction.atomic
    def duplicate(cls, request: HttpRequest, invoice_id: int) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('duplicate_invoice'):
                raise PermissionDenied('duplicate_invoice')
            invoice = get_object_or_404(Invoice, pk=invoice_id)
            dup = Invoice.objects.create(
                invoice_id       = cls.invoice_number(),
                customer_id      = invoice.customer_id,
                issue_date       = Utility.today(),
                due_date         = invoice.due_date,
                category_id      = invoice.category_id,
                ref_number       = invoice.ref_number,
                status           = 0,
                shipping_display = invoice.shipping_display,
                created_by       = invoice.created_by
            )
            for prod in invoice.items.all():
                InvoiceProduct.objects.create(
                    invoice_id = dup.id,
                    product_id = prod.product_id,
                    quantity   = prod.quantity,
                    tax        = prod.tax,
                    discount   = prod.discount,
                    price      = prod.price
                )
            return cls._success(request, 'Invoice duplicated')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    def preview_invoice(cls, request: HttpRequest, template: str, color: str) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            settings       = Utility.settings(request.user.creator_id())
            dummy_invoice  = Utility.build_preview_invoice(request.user, color)
            img            = Utility.invoice_logo(dummy_invoice.created_by)
            font_color     = Utility.get_font_color('#'+color)
            return render(request, f'invoice/templates/{template}.html', {
                'invoice': dummy_invoice, 'preview': True,
                'color': f'#{color}', 'img': img,
                'settings': settings, 'customer': dummy_invoice.customer,
                'font_color': font_color, 'custom_fields': []
            })
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    def invoice(cls, request: HttpRequest, encrypted_id: str) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            invoice_id = Utility.decrypt(encrypted_id)
            invoice    = get_object_or_404(Invoice, pk=invoice_id)
            settings   = Utility.settings_by_id(invoice.created_by)
            Utility.prepare_invoice_totals(invoice, settings)
            img        = Utility.invoice_logo(invoice.created_by)
            font_color = Utility.get_font_color('#'+settings['invoice_color'])
            return render(request,
                f'invoice/templates/{settings["invoice_template"]}.html', {
                  'invoice': invoice, 'color': '#'+settings['invoice_color'],
                  'settings': settings, 'customer': invoice.customer,
                  'img': img, 'font_color': font_color,
                  'custom_fields': CustomField.objects.filter(
                    created_by=invoice.created_by, module='invoice')
                })
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    def save_template_settings(cls, request: HttpRequest) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            post = request.POST.dict()
            post.pop('_token', None)
            if 'invoice_template' in post and not post.get('invoice_color'):
                post['invoice_color'] = 'ffffff'
            if 'invoice_logo' in request.FILES:
                ok, fname = Utility.upload_invoice_logo(request.FILES['invoice_logo'],
                                                        request.user.id)
                if not ok:
                    return cls._error(request, fname)
                post['invoice_logo'] = fname
            for k, v in post.items():
                Utility.update_setting(k, v, request.user.creator_id())
            return cls._success(request, 'Invoice settings updated',
                                ref=get_redirect_url(request))
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    def items(cls, request: HttpRequest) -> JsonResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            invoice_id = request.GET.get('invoice_id')
            product_id = request.GET.get('product_id')
            item = InvoiceProduct.objects.filter(
                invoice_id=invoice_id, product_id=product_id
            ).first()
            return JsonResponse(item.to_dict() if item else {}, safe=False)
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    def invoice_link(cls, request: HttpRequest, encrypted_id: str) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            invoice_id         = Utility.decrypt(encrypted_id)
            invoice            = get_object_or_404(Invoice, pk=invoice_id)
            settings           = Utility.settings_by_id(invoice.created_by)
            company_payment    = Utility.company_payment_settings(invoice.created_by)
            plan               = Plan.objects.get(pk=User.objects.get(
                                  pk=invoice.created_by).plan)
            return render(request, 'invoice/customer_invoice.html', {
                'settings': settings, 'invoice': invoice,
                'customer': invoice.customer, 'items': invoice.items,
                'invoice_payment': invoice.payments,
                'custom_fields': CustomField.objects.filter(module='invoice'),
                'user': User.objects.get(pk=invoice.created_by),
                'company_payment_setting': company_payment,
                'invoice_user': User.objects.get(pk=invoice.created_by),
                'user_plan': plan
            })
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise

    @classmethod
    def export(cls, request: HttpRequest) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.user.has_perm('manage_invoice'):
                raise PermissionDenied('manage_invoice')
            name = f"invoice_{Utility.today('%Y-%m-%d_%H%M%S')}.xlsx"
            xlsx = Utility.export_invoices_xlsx(name)
            return xlsx
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            logger.error(f"{REF} failed: {e}")
            raise
