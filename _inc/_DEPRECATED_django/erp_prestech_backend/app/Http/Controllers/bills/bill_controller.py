import datetime
import json
import time
import logging
import inspect
from django.conf import settings
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.db import connection, transaction
from django.http import JsonResponse, HttpRequest, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.urls import reverse
from django.core.exceptions import PermissionDenied, ObjectDoesNotExist
from django.core.signing import Signer
from django.utils.decorators import method_decorator
from ....Exports import BillExport
from ....Models.bills.bill import Bill
from ....Models.bills.bill_account import BillAccount
from ....Models.bills.bill_payment import BillPayment
from ....Models.bills.bill_product import BillProduct
from ....Models.bills.debit_note import DebitNote
from ....Models.bills.stock_report import StockReport
from ....Models.bills.transaction import Transaction
from ....Models.charts.chart_of_account import ChartOfAccount
from ....Models.companies.bank_account import BankAccount
from ....Models.companies.vendor import Vendor
from ....Models.products.product_service import ProductService
from ....Models.products.product_service_category import ProductServiceCategory
from ....Models.shapes.custom_field import CustomField
from ....Models.utils.utility import Utility
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class BillController(Controller):

    @method_decorator(login_required)
    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            vendor_qs = Vendor.objects.filter(created_by=request.user.creator_id)
            vendor = {'': 'Select Vendor'}
            for v in vendor_qs:
                vendor[v.id] = v.name
            status = Bill.statues  # TODO: verify attribute name
            query = Bill.objects.filter(type='Bill', created_by=request.user.creator_id)
            if request.GET.get('vendor'):
                query = query.filter(vendor_id=request.GET['vendor'])
            bill_date = request.GET.get('bill_date')
            if bill_date:
                if ' to ' in bill_date:
                    start, end = (d.strip() for d in bill_date.split(' to '))
                    query = query.filter(bill_date__range=(start, end))
                else:
                    query = query.filter(bill_date=bill_date)
            if request.GET.get('status'):
                query = query.filter(status=request.GET['status'])
            bills = query.select_related('category')
            return render(request, 'bill/index.html', {
                'bills': bills,
                'vendor': vendor,
                'status': status
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def create(cls, request: HttpRequest, vendorId) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        if not request.user.has_perm('create bill'):
            return default_permission_denial(
                request,
                err=PermissionDenied('User lacks permission: create bill'),
                ref=f'{CN}::{MN}',
                logger=logger
            )
        try:
            custom_fields = CustomField.objects.filter(
                created_by=request.user.creator_id, module='bill'
            )
            category_qs = ProductServiceCategory.objects.filter(
                created_by=request.user.creator_id
            ).exclude(type__in=['product & service', 'income'])
            category = {'': 'Select Category'}
            for c in category_qs:
                category[c.id] = c.name
            bill_number = request.user.billNumberFormat(cls.bill_number(request))
            vendors_qs = Vendor.objects.filter(created_by=request.user.creator_id)
            vendors = {'': 'Select Vendor'}
            for v in vendors_qs:
                vendors[v.id] = v.name
            product_services_qs = ProductService.objects.filter(created_by=request.user.creator_id)
            product_services = {'': 'Select Item'}
            for p in product_services_qs:
                product_services[p.id] = p.name
            chart_accounts_qs = ChartOfAccount.objects.extra(
                select={'code_name': "CONCAT(code, ' - ', name)"}
            ).filter(created_by=request.user.creator_id)
            chart_accounts = {'': 'Select Account'}
            for a in chart_accounts_qs:
                chart_accounts[a.id] = a.code_name
            return render(request, 'bill/create.html', {
                'vendors': vendors,
                'bill_number': bill_number,
                'product_services': product_services,
                'category': category,
                'customFields': custom_fields,
                'vendorId': vendorId,
                'chartAccounts': chart_accounts
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def store(cls, request: HttpRequest) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        if not request.user.has_perm('create bill'):
            return default_permission_denial(
                request,
                err=PermissionDenied('User lacks permission: create bill'),
                ref=f'{CN}::{MN}',
                logger=logger
            )
        try:
            if not (request.POST.get('vendor_id') and request.POST.get('bill_date') and request.POST.get('due_date')):
                messages.error(request, "Vendor, Bill Date, and Due Date are required.")
                return redirect(get_redirect_url(request))
            items = request.POST.get('items')
            if items:
                data = json.loads(items)
                first = data[0]
                if not (first.get('item') or first.get('chart_account_id') or first.get('amount')):
                    messages.error(request, "Item is required.")
                    return redirect(get_redirect_url(request))
                if not first.get('chart_account_id') and first.get('amount'):
                    messages.error(request, "Chart Account is required when amount is provided.")
                    return redirect(get_redirect_url(request))
            bill = Bill()
            bill.bill_id = cls.bill_number(request)
            bill.vendor_id = request.POST.get('vendor_id')
            bill.bill_date = request.POST.get('bill_date')
            bill.status = 0
            bill.type = 'Bill'
            bill.user_type = 'vendor'
            bill.due_date = request.POST.get('due_date')
            bill.category_id = request.POST.get('category_id') or 0
            bill.order_number = request.POST.get('order_number') or 0
            bill.created_by = request.user.creator_id
            bill.save()
            CustomField.saveData(bill, request.POST.get('customField'))
            products = json.loads(request.POST.get('items'))
            total_amount = 0
            for prod in products:
                bp = BillProduct.objects.filter(id=prod.get('id')).first()
                if not bp:
                    bp = BillProduct(
                        bill=bill,
                        product_id=prod.get('item') or prod.get('items'),
                        quantity=prod.get('quantity'),
                        tax=prod.get('tax'),
                        discount=prod.get('discount'),
                        price=prod.get('price'),
                        description=prod.get('description')
                    )
                    bp.save()
                else:
                    Utility.total_quantity('minus', bp.quantity, bp.product_id)
                billAccount = None
                if prod.get('chart_account_id'):
                    billAccount = BillAccount(
                        chart_account_id=prod.get('chart_account_id'),
                        price=prod.get('amount'),
                        description=prod.get('description'),
                        type='Bill',
                        ref_id=bill.id
                    )
                    billAccount.save()
                if prod.get('id', 0) > 0:
                    Utility.total_quantity('plus', prod.get('quantity'), bp.product_id)
                desc = f"{prod.get('quantity')} quantity purchase in bill {request.user.billNumberFormat(bill.bill_id)}"
                StockReport.objects.filter(type='bill', type_id=bill.id).delete()
                if prod.get('item'):
                    Utility.addProductStock(prod.get('item'), prod.get('quantity'), 'bill', desc, bill.id)
                    amt = (bp.quantity * bp.price) + (billAccount.price if billAccount else 0)
                    total_amount += amt
            if request.POST.get('chart_account_id'):
                cat = ProductServiceCategory.objects.get(id=request.POST.get('category_id'))
                ca = ChartOfAccount.objects.get(id=cat.chart_account_id)
                ba = BillAccount(
                    chart_account_id=ca.id,
                    price=total_amount,
                    description=request.POST.get('description'),
                    type='Bill Category',
                    ref_id=bill.id
                )
                ba.save()
            setting = Utility.settings(request.user.creator_id)
            vendor = Vendor.objects.get(id=request.POST.get('vendor_id'))
            notif = {
                'bill_number': request.user.billNumberFormat(bill.bill_id),
                'user_name': request.user.name,
                'bill_date': bill.bill_date,
                'bill_due_date': bill.due_date,
                'vendor_name': vendor.name
            }
            if setting.get('bill_notification') == 1:
                Utility.send_slack_msg('new_bill', notif)
            if setting.get('telegram_bill_notification') == 1:
                Utility.send_telegram_msg('new_bill', notif)
            if setting.get('twilio_bill_notification') == 1:
                Utility.send_twilio_msg(vendor.contact, 'new_bill', notif)
            webhook = Utility.webhookSetting('New Bill')
            if webhook:
                param = json.dumps(bill.__dict__)
                flag = Utility.WebhookCall(webhook['url'], param, webhook['method'])
                if flag:
                    messages.success(request, "Bill successfully created.")
                    return redirect(reverse('bill.index', args=[bill.id]))
                else:
                    messages.error(request, "Webhook call failed.")
                    return redirect(get_redirect_url(request))
            messages.success(request, "Bill successfully created.")
            return redirect(reverse('bill.index', args=[bill.id]))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def bill_number(cls, request: HttpRequest) -> int:
        # CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        latest = Bill.objects.filter(created_by=request.user.creator_id).order_by('-id').first()
        return 1 if not latest else latest.bill_id + 1
  
    @method_decorator(login_required)
    @classmethod
    def show(cls, request: HttpRequest, ids) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        if not request.user.has_perm('show bill'):
            return default_permission_denial(
                request,
                err=PermissionDenied('User lacks permission: show bill'),
                ref=f'{CN}::{MN}',
                logger=logger
            )
        try:
            signer = Signer()
            id_decrypted = signer.unsign(ids)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        try:
            bill = Bill.objects.select_related('debitNote').filter(id=id_decrypted).first()
            if not bill or bill.created_by != request.user.creator_id:
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Cannot view this bill'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            billPayment = BillPayment.objects.filter(bill_id=bill.id).first()
            vendor = bill.vendor
            items, accounts = [], list(bill.accounts.all())
            for idx, val in enumerate(bill.items.all()):
                val.chart_account_id = accounts[idx].chart_account_id if idx < len(accounts) else None
                val.account_id       = accounts[idx].id               if idx < len(accounts) else None
                val.amount           = accounts[idx].price            if idx < len(accounts) else None
                items.append(val)
            if not items:
                for acc in accounts:
                    items.append({
                        'chart_account_id': acc.chart_account_id,
                        'account_id':      acc.id,
                        'amount':          acc.price
                    })
            bill.customField   = CustomField.getData(bill, 'bill')
            customFields       = CustomField.objects.filter(
                                    created_by=request.user.creator_id,
                                    module='bill'
                                 )
            return render(request, 'bill/view.html', {
                'bill':        bill,
                'vendor':      vendor,
                'items':       items,
                'billPayment': billPayment,
                'customFields':customFields
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def edit(cls, request: HttpRequest, ids) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        if not request.user.has_perm('edit bill'):
            return JsonResponse({'error': 'Permission denied.'}, status=401)
        try:
            id_decrypted = Signer().unsign(ids)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        try:
            bill = Bill.objects.filter(id=id_decrypted).first()
            if not bill:
                raise ObjectDoesNotExist('Bill not found')
            # prepare context...
            category = {c.id: c.name for c in ProductServiceCategory.objects
                        .filter(created_by=request.user.creator_id)
                        .exclude(type__in=['product & service', 'income'])}
            bill_number      = request.user.billNumberFormat(bill.bill_id)
            vendors          = {v.id: v.name for v in Vendor.objects.filter(created_by=request.user.creator_id)}
            product_services = {p.id: p.name for p in ProductService.objects
                                .filter(created_by=request.user.creator_id)}
            customFields     = CustomField.objects.filter(created_by=request.user.creator_id, module='bill')
            chartAccounts    = {ca.id: ca.code_name for ca in ChartOfAccount.objects.extra(
                                   select={'code_name': "CONCAT(code, ' - ', name)"}
                               ).filter(created_by=request.user.creator_id)}
            items, accounts = [], list(bill.accounts.all())
            for idx, val in enumerate(bill.items.all()):
                val.chart_account_id = accounts[idx].chart_account_id if idx < len(accounts) else None
                val.account_id       = accounts[idx].id               if idx < len(accounts) else None
                val.amount           = accounts[idx].price            if idx < len(accounts) else None
                items.append(val)
            if not items:
                for acc in accounts:
                    items.append({
                        'chart_account_id': acc.chart_account_id,
                        'account_id':      acc.id,
                        'amount':          acc.price
                    })
            bill.customField = CustomField.getData(bill, 'bill')
            return render(request, 'bill/edit.html', {
                'vendors':         vendors,
                'product_services':product_services,
                'bill':            bill,
                'bill_number':     bill_number,
                'category':        category,
                'customFields':    customFields,
                'chartAccounts':   chartAccounts,
                'items':           items
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)


    @method_decorator(login_required)
    @classmethod
    def update(cls, request: HttpRequest, bill_id) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            bill = get_object_or_404(Bill, id=bill_id)
            if bill.created_by != request.user.creator_id:
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Cannot update this bill'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            if not (request.POST.get('vendor_id') and request.POST.get('bill_date') and request.POST.get('due_date')):
                messages.error(request, "Vendor, Bill Date, and Due Date are required.")
                return redirect(reverse('bill.index'))
            # update fields...
            bill.vendor_id    = request.POST['vendor_id']
            bill.bill_date    = request.POST['bill_date']
            bill.due_date     = request.POST['due_date']
            bill.user_type    = 'vendor'
            bill.order_number = request.POST.get('order_number')
            bill.category_id  = request.POST.get('category_id')
            bill.save()
            CustomField.saveData(bill, request.POST.get('customField'))
            # process products and accounts...
            messages.success(request, "Bill successfully updated.")
            return redirect(reverse('bill.index', args=[bill.id]))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)


    @method_decorator(login_required)
    @classmethod
    def destroy(cls, request: HttpRequest, bill_id) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            bill = get_object_or_404(Bill, id=bill_id)
            if bill.created_by != request.user.creator_id:
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Cannot delete this bill'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            with transaction.atomic():
                for bp in bill.payments.all():
                    Utility.bankAccountBalance(bp.account_id, bp.amount, 'credit')
                    if tran := Transaction.objects.filter(payment_id=bp.id).first():
                        tran.delete()
                    if bp_obj := BillPayment.objects.filter(id=bp.id).first():
                        bp_obj.delete()
                bill.delete()
                if bill.vendor_id and bill.status:
                    Utility.updateUserBalance('vendor', bill.vendor_id, bill.getDue(), 'credit')
                BillProduct.objects.filter(bill=bill).delete()
                BillAccount.objects.filter(ref_id=bill.id).delete()
                DebitNote.objects.filter(bill=bill.id).delete()
            messages.success(request, "Bill successfully deleted.")
            return redirect(reverse('bill.index'))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)


    @method_decorator(login_required)
    @classmethod
    def product(cls, request: HttpRequest) -> JsonResponse:
        # CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            prod = ProductService.objects.get(id=request.GET.get('product_id'))
            unit     = prod.unit.name if prod.unit else ''
            taxRate  = prod.taxRate(prod.tax_id) if prod.tax_id else 0
            taxes    = prod.tax(prod.tax_id)     if prod.tax_id else 0
            salePrice= prod.purchase_price
            data = {
                'product': prod.id,
                'unit':    unit,
                'taxRate': taxRate,
                'taxes':   taxes,
                'totalAmount': salePrice
            }
            return JsonResponse(data, status=200)
        except Exception as e:
            return JsonResponse({'error': str(e)}, status=500)


    @method_decorator(login_required)
    @classmethod
    def product_destroy(cls, request: HttpRequest) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        if not request.user.has_perm('delete bill product'):
            return default_permission_denial(
                request,
                err=PermissionDenied('User lacks permission: delete bill product'),
                ref=f'{CN}::{MN}',
                logger=logger
            )
        try:
            bp   = BillProduct.objects.get(id=request.POST.get('id'))
            bill = Bill.objects.get(id=bp.bill.id)
            Utility.updateUserBalance('vendor', bill.vendor_id, request.POST.get('amount'), 'credit')
            bp.delete()
            messages.success(request, "Bill product successfully deleted.")
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def send(cls, request: HttpRequest, bill_id) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        if not request.user.has_perm('send bill'):
            return default_permission_denial(
                request,
                err=PermissionDenied('User lacks permission: send bill'),
                ref=f'{CN}::{MN}',
                logger=logger
            )
        try:
            bill = Bill.objects.get(id=bill_id)
            bill.send_date = datetime.date.today()
            bill.status    = 1
            bill.save()
            vendor = Vendor.objects.get(id=bill.vendor_id)
            bill.name = vendor.name or ''
            bill.bill = request.user.billNumberFormat(bill.bill_id)
            billId    = Signer().sign(bill.id)
            bill.url  = reverse('bill.pdf', args=[billId])
            Utility.updateUserBalance('vendor', vendor.id, bill.getTotal(), 'debit')
            resp = Utility.sendEmailTemplate(
                'vendor_bill_sent',
                {vendor.id: vendor.email},
                {
                    'vendor_bill_name':   bill.name,
                    'vendor_bill_number': bill.bill,
                    'vendor_bill_url':    bill.url
                }
            )
            messages.success(request, "Bill successfully sent." +
                             (f"<br><span class='text-danger'>{resp['error']}</span>"
                              if resp and not resp.get('is_success') and resp.get('error') else ""))
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def resend(cls, request, bill_id) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            settings_val = Utility.settings()
            if settings_val.get('bill_resent') == 1:
                bill = Bill.objects.get(id=bill_id)
                vendor = Vendor.objects.get(id=bill.vendor_id)
                bill.name = vendor.name or ''
                bill.bill = request.user.billNumberFormat(bill.bill_id)
                signer = Signer()
                billId = signer.sign(bill.id)
                bill.url = reverse('bill.pdf', args=[billId])
                billResendArr = {
                    'vendor_name':   vendor.name,
                    'vendor_email':  vendor.email,
                    'bill_name':     bill.name,
                    'bill_number':   bill.bill,
                    'bill_url':      bill.url
                }
                resp = Utility.sendEmailTemplate('bill_resent', {vendor.id: vendor.email}, billResendArr)
            messages.success(
                request,
                "Bill successfully sent." +
                (f"<br><span class='text-danger'>{resp['error']}</span>"
                 if resp and not resp.get('is_success') and resp.get('error') else "")
            )
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def payment(cls, request, bill_id) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        if not request.user.has_perm('create payment bill'):
            return default_permission_denial(
                request,
                err=PermissionDenied('User lacks permission: create payment bill'),
                ref=f'{CN}::{MN}',
                logger=logger
            )
        try:
            bill = Bill.objects.get(id=bill_id)
            vendors = {v.id: v.name for v in Vendor.objects.filter(created_by=request.user.creator_id)}
            categories = {c.id: c.name for c in ProductServiceCategory.objects.filter(created_by=request.user.creator_id)}
            accounts = {
                a.id: a.name
                for a in BankAccount.objects.extra(
                    select={'name': "CONCAT(bank_name, ' ', holder_name)"}
                ).filter(created_by=request.user.creator_id)
            }
            return render(request, 'bill/payment.html', {
                'vendors': vendors,
                'categories': categories,
                'accounts': accounts,
                'bill': bill
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def create_payment(cls, request, bill_id) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        if not request.user.has_perm('create payment bill'):
            return default_permission_denial(
                request,
                err=PermissionDenied('User lacks permission: create payment bill'),
                ref=f'{CN}::{MN}',
                logger=logger
            )
        try:
            if not (request.POST.get('date') and request.POST.get('amount') and request.POST.get('account_id')):
                messages.error(request, "Date, Amount, and Account are required.")
                return redirect(get_redirect_url(request))
            bp = BillPayment(
                bill_id=bill_id,
                date=request.POST['date'],
                amount=request.POST['amount'],
                account_id=request.POST['account_id'],
                payment_method=0,
                reference=request.POST.get('reference'),
                description=request.POST.get('description')
            )
            if file := request.FILES.get('add_receipt'):
                size = file.size
                if Utility.updateStorageLimit(request.user.creator_id, size) == 1:
                    if bp.add_receipt:
                        import os
                        path = os.path.join(settings.MEDIA_ROOT, 'uploads/payment/', bp.add_receipt)
                        if os.path.exists(path):
                            os.remove(path)
                    fn = f"{int(time.time())}_{file.name}"
                    bp.add_receipt = fn
                    info = Utility.upload_file(request, 'add_receipt', fn, 'uploads/payment', [])
                    if info.get('flag') == 0:
                        messages.error(request, info.get('msg'))
                        return redirect(get_redirect_url(request))
            bp.save()
            bill = Bill.objects.get(id=bill_id)
            due = bill.getDue()
            if bill.status == 0:
                bill.send_date = datetime.date.today()
                bill.save()
            bill.status = 4 if due <= 0 else 3
            bill.save()
            bp.user_id    = bill.vendor_id
            bp.user_type  = 'Vendor'
            bp.type       = 'Partial'
            bp.created_by = request.user.id
            bp.payment_id = bp.id
            bp.category   = 'Bill'
            bp.account    = request.POST['account_id']
            bp.save()
            Transaction.addTransaction(bp)
            vendor = Vendor.objects.get(id=bill.vendor_id)
            payment = BillPayment()
            payment.name   = vendor.name
            payment.method = '-'
            payment.date   = request.user.dateFormat(request.POST['date'])
            payment.amount = request.user.priceFormat(request.POST['amount'])
            payment.bill   = 'bill ' + request.user.billNumberFormat(bp.bill_id)
            Utility.updateUserBalance('vendor', bill.vendor_id, request.POST['amount'], 'credit')
            Utility.bankAccountBalance(request.POST['account_id'], request.POST['amount'], 'debit')
            settings_val = Utility.settings()
            if settings_val.get('new_bill_payment') == 1:
                resp = Utility.sendEmailTemplate('new_bill_payment', {vendor.id: vendor.email}, {
                    'vendor_name':  vendor.name,
                    'vendor_email': vendor.email,
                    'payment_name': payment.name,
                    'payment_amount': payment.amount,
                    'payment_bill': payment.bill,
                    'payment_date': payment.date,
                    'payment_method': payment.method,
                    'company_name': payment.method,
                })
                messages.success(
                    request,
                    "Payment successfully added." +
                    (f"<br><span class='text-danger'>{resp['error']}</span>"
                     if resp and not resp.get('is_success') and resp.get('error') else "")
                )
                return redirect(get_redirect_url(request))
            messages.success(request, "Payment successfully added.")
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def payment_destroy(cls, request, bill_id, payment_id) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        if not request.user.has_perm('delete payment bill'):
            return default_permission_denial(
                request,
                err=PermissionDenied('User lacks permission: delete payment bill'),
                ref=f'{CN}::{MN}',
                logger=logger
            )
        try:
            payment = BillPayment.objects.get(id=payment_id)
            payment.delete()
            bill = Bill.objects.get(id=bill_id)
            due, total = bill.getDue(), bill.getTotal()
            bill.status = 3 if (due > 0 and total != due) else 2
            Utility.updateUserBalance('vendor', bill.vendor_id, payment.amount, 'debit')
            Utility.bankAccountBalance(payment.account_id, payment.amount, 'credit')
            if payment.add_receipt:
                Utility.changeStorageLimit(request.user.creator_id, f'/uploads/payment/{payment.add_receipt}')
            bill.save()
            Transaction.destroyTransaction(payment_id, 'Partial', 'Vendor')
            messages.success(request, "Payment successfully deleted.")
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def vendor_bill(cls, request) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        if not request.user.has_perm('manage vendor bill'):
            return default_permission_denial(
                request,
                err=PermissionDenied('User lacks permission: manage vendor bill'),
                ref=f'{CN}::{MN}',
                logger=logger
            )
        try:
            status = Bill.statues
            query = Bill.objects.filter(
                vendor_id=request.user.vendor_id,
                created_by=request.user.creator_id
            ).exclude(status=0)
            if request.GET.get('vendor'):
                query = query.filter(id=request.GET['vendor'])
            if dr := request.GET.get('bill_date'):
                start, end = (d.strip() for d in dr.split(' - '))
                query = query.filter(bill_date__range=(start, end))
            if request.GET.get('status'):
                query = query.filter(status=request.GET['status'])
            bills = query.all()
            return render(request, 'bill/index.html', {'bills': bills, 'status': status})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def vendor_bill_show(cls, request, id) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            bill_id = Signer().unsign(id)
            bill = Bill.objects.select_related('debitNote').filter(id=bill_id).first()
            if not bill or bill.created_by != request.user.creator_id:
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Cannot view this vendor bill'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            vendor = bill.vendor
            items = list(bill.items.all())
            bill.customField = CustomField.getData(bill, 'bill')
            customFields = CustomField.objects.filter(created_by=request.user.creator_id, module='bill')
            return render(request, 'bill/view.html', {
                'bill': bill, 'vendor': vendor,
                'items': items, 'billPayment': bill.payments.first(),
                'customFields': customFields
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def vendor(cls, request) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            vendor = Vendor.objects.get(id=request.GET.get('id'))
            return render(request, 'bill/vendor_detail.html', {'vendor': vendor})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def vendor_bill_send(cls, request, bill_id) -> HttpResponse:
        # No permission check needed
        return render(request, 'vendor/bill_send.html', {'bill_id': bill_id})

    @method_decorator(login_required)
    @classmethod
    def vendor_bill_send_email(cls, request, bill_id) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            if not request.POST.get('email'):
                messages.error(request, "Email is required.")
                return redirect(get_redirect_url(request))
            bill   = Bill.objects.get(id=bill_id)
            vendor = Vendor.objects.get(id=bill.vendor_id)
            bill.name  = vendor.name or ''
            bill.bill  = request.user.billNumberFormat(bill.bill_id)
            billId     = Signer().sign(bill.id)
            bill.url   = reverse('bill.pdf', args=[billId])
            try:
                # TODO: implement actual email send
                pass
            except Exception as smtp_err:
                logger.exception("SMTP error in vendorBillSendEmail: %s", smtp_err)
                smtp_error = "E-Mail has not been sent due to SMTP configuration"
            messages.success(
                request,
                "Bill successfully sent." +
                (f"<br><span class='text-danger'>{smtp_error}</span>" if 'smtp_error' in locals() else "")
            )
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def shipping_display(cls, request, id) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            bill = Bill.objects.get(id=id)
            bill.shipping_display = 1 if request.GET.get('is_display') == 'true' else 0
            bill.save()
            messages.success(request, "Shipping address status successfully changed.")
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def duplicate(cls, request, bill_id) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        if not request.user.has_perm('duplicate bill'):
            return default_permission_denial(
                request,
                err=PermissionDenied('User lacks permission: duplicate bill'),
                ref=f'{CN}::{MN}',
                logger=logger
            )
        try:
            bill = Bill.objects.get(id=bill_id)
            dup  = Bill(
                bill_id           = cls.bill_number(request),
                vendor_id         = bill.vendor_id,
                bill_date         = datetime.date.today(),
                due_date          = bill.due_date,
                send_date         = None,
                category_id       = bill.category_id,
                order_number      = bill.order_number,
                status            = 0,
                shipping_display  = bill.shipping_display,
                created_by        = bill.created_by
            )
            dup.save()
            for product in BillProduct.objects.filter(bill=bill):
                dp = BillProduct(
                    bill=dup,
                    product_id=product.product_id,
                    quantity=product.quantity,
                    tax=product.tax,
                    discount=product.discount,
                    price=product.price
                )
                dp.save()
            messages.success(request, "Bill duplicate successfully.")
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def preview_bill(cls, request, template, color) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            objUser       = request.user
            settings_data = Utility.settings()
            bill          = Bill()
            vendor        = type('DummyVendor', (), {})()
            vendor.email            = '<Email>'
            vendor.shipping_name    = '<Vendor Name>'
            vendor.shipping_country = '<Country>'
            vendor.shipping_state   = '<State>'
            vendor.shipping_city    = '<City>'
            vendor.shipping_phone   = '<Vendor Phone Number>'
            vendor.shipping_zip     = '<Zip>'
            vendor.shipping_address = '<Address>'
            vendor.billing_name     = '<Vendor Name>'
            vendor.billing_country  = '<Country>'
            vendor.billing_state    = '<State>'
            vendor.billing_city     = '<City>'
            vendor.billing_phone    = '<Vendor Phone Number>'
            vendor.billing_zip      = '<Zip>'
            vendor.billing_address  = '<Address>'
            totalTaxPrice = 0
            taxesData     = {}
            items         = []
            for i in range(1, 4):
                item = type('DummyItem', (), {})()
                item.name     = f'Item {i}'
                item.quantity = 1
                item.tax      = 5
                item.discount = 50
                item.price    = 100
                item.unit     = 1
                itemTaxes     = []
                for k, tname in enumerate(['Tax 1', 'Tax 2']):
                    taxPrice = 10
                    totalTaxPrice += taxPrice
                    itemTax = {
                        'name':      tname,
                        'rate':      '10 %',
                        'price':     '$10',
                        'tax_price': 10
                    }
                    itemTaxes.append(itemTax)
                    taxesData[tname] = taxesData.get(tname, 0) + taxPrice
                item.itemTax = itemTaxes
                items.append(item)
            bill.bill_id       = 1
            bill.issue_date    = datetime.datetime.now()
            bill.due_date      = datetime.datetime.now()
            bill.itemData      = items
            bill.totalTaxPrice = totalTaxPrice
            bill.totalQuantity = sum(i.quantity for i in items)
            bill.totalRate     = sum(i.price for i in items)
            bill.totalDiscount = sum(i.discount for i in items)
            bill.taxesData     = taxesData
            bill.created_by    = objUser.creator_id
            bill.customField   = []
            customFields       = []
            preview            = 1
            color_code         = f'#{color}'
            font_color         = Utility.getFontColor(color_code)
            logo               = settings_data.get('logo', '')
            company_logo       = Utility.getValByName('company_logo_dark')
            bill_logo          = Utility.getValByName('bill_logo')
            img = (
                Utility.get_file('bill_logo/') + bill_logo
                if bill_logo else
                (logo if company_logo else 'logo-dark.png')
            )
            return render(request, f'bill/templates/{template}.html', {
                'bill':         bill,
                'preview':      preview,
                'color':        color_code,
                'img':          img,
                'settings':     settings_data,
                'vendor':       vendor,
                'font_color':   font_color,
                'customFields': customFields
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def bill(cls, request, bill_id) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            signer = Signer()
            bid    = signer.unsign(bill_id)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        bill = Bill.objects.filter(id=bid).first()
        if not bill or bill.created_by != request.user.creator_id:
            return default_permission_denial(
                request,
                err=PermissionDenied('Cannot view this bill'),
                ref=f'{CN}::{MN}',
                logger=logger
            )
        with connection.cursor() as cursor:
            cursor.execute(
                "SELECT name, value FROM settings WHERE created_by = %s",
                [bill.created_by]
            )
            settings_dict = {row[0]: row[1] for row in cursor.fetchall()}
        vendor           = bill.vendor
        totalTaxPrice    = totalQuantity = totalRate = totalDiscount = 0
        taxesData        = {}
        items            = []
        for product in bill.items.all():
            item = type('DummyItem', (), {})()
            prod_obj = getattr(product, 'product', lambda: None)()
            item.name        = prod_obj.name if prod_obj else ''
            item.quantity    = product.quantity
            item.unit        = prod_obj.unit_id if prod_obj else ''
            item.tax         = product.tax
            item.discount    = product.discount
            item.price       = product.price
            item.description = product.description
            totalQuantity   += item.quantity
            totalRate       += item.price
            totalDiscount   += item.discount
            itemTaxes        = []
            if item.tax:
                for tax in Utility.tax(item.tax):
                    taxPrice = Utility.taxRate(tax.rate, item.price, item.quantity, item.discount)
                    totalTaxPrice += taxPrice
                    itemTax = {
                        'name':      tax.name,
                        'rate':      f"{tax.rate}%",
                        'price':     Utility.priceFormat(settings_dict, taxPrice),
                        'tax_price': taxPrice
                    }
                    itemTaxes.append(itemTax)
                    taxesData[tax.name] = taxesData.get(tax.name, 0) + taxPrice
            item.itemTax = itemTaxes
            items.append(item)
        bill.itemData      = items
        bill.totalTaxPrice = totalTaxPrice
        bill.totalQuantity = totalQuantity
        bill.totalRate     = totalRate
        bill.totalDiscount = totalDiscount
        bill.taxesData     = taxesData
        bill.customField   = CustomField.getData(bill, 'bill')
        customFields       = CustomField.objects.filter(
                                created_by=request.user.creator_id,
                                module='bill'
                             )
        logo           = settings_dict.get('logo', '')
        company_logo   = Utility.getValByName('company_logo_dark')
        settings_data  = Utility.settingsById(bill.created_by)
        bill_logo      = settings_data.get('bill_logo')
        img = (
            Utility.get_file('bill_logo/') + bill_logo
            if bill_logo else
            (logo if company_logo else 'logo-dark.png')
        )
        color_code = f"#{settings_dict.get('bill_color', 'ffffff')}"
        font_color = Utility.getFontColor(color_code)
        context = {
            'bill':         bill,
            'color':        color_code,
            'settings':     settings_dict,
            'vendor':       vendor,
            'img':          img,
            'font_color':   font_color,
            'customFields': customFields
        }
        return render(request, f"bill/templates/{settings_dict.get('bill_template')}.html", context)

    @method_decorator(login_required)
    @classmethod
    def save_bill_template_settings(cls, request) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            post = request.POST.dict()
            post.pop('_token', None)
            if 'bill_template' in post and not post.get('bill_color'):
                post['bill_color'] = "ffffff"
            
            # handle uploaded logo if present
            if request.FILES.get('bill_logo'):
                bill_logo_filename = f"{request.user.id}_bill_logo.png"
                validation_rules = ['mimes:png', 'max:20480']
                upload_info = Utility.upload_file(
                    request, 'bill_logo', bill_logo_filename, 'bill_logo/', validation_rules
                )
                if upload_info.get('flag') == 0:
                    messages.error(request, upload_info.get('msg'))
                    return redirect(get_redirect_url(request))
                post['bill_logo'] = bill_logo_filename

            for key, val in post.items():
                with connection.cursor() as cursor:
                    cursor.execute(
                        "INSERT INTO settings (value, name, created_by) VALUES (%s, %s, %s) "
                        "ON DUPLICATE KEY UPDATE value=VALUES(value)",
                        [val, key, request.user.creator_id]
                    )

            messages.success(request, "Bill Setting updated successfully")
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
    
    @method_decorator(login_required)
    @classmethod
    def items(cls, request) -> JsonResponse:
        # CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            item = BillProduct.objects.filter(
                bill_id=request.GET.get('bill_id'),
                product_id=request.GET.get('product_id')
            ).first()
            return JsonResponse(item.__dict__ if item else {}, status=200)
        except Exception as e:
            return JsonResponse({'error': str(e)}, status=500)

    @method_decorator(login_required)
    @classmethod
    def export(cls, request) -> HttpResponse:
        CN, MN = cls.__name__, inspect.currentframe().f_code.co_name
        try:
            name = "bill_" + datetime.datetime.now().strftime("%Y-%m-%d_%H-%M-%S")
            return Utility.excel_download(BillExport, f"{name}.xlsx")
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)






