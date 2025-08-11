import logging
import inspect
from django.contrib import messages
from django.contrib.auth.decorators import login_required, permission_required
from django.core.exceptions import PermissionDenied, BadSignature
from django.core.signing import Signer, loads
from django.db import transaction
from django.db.models import F
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import render, redirect, get_object_or_404
from django.utils.decorators import method_decorator
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller
from ....Models.bills.bill import Bill
from ....Models.bills.bill_account import BillAccount
from ....Models.bills.bill_payment import BillPayment
from ....Models.bills.bill_product import BillProduct
from ....Models.charts.chart_of_account import ChartOfAccount
from ....Models.companies.bank_account import BankAccount
from ....Models.companies.vendor import Vendor
from ....Models.individuals.customer import Customer
from ....Models.individuals.employee import Employee
from ....Models.products.product_service import ProductService
from ....Models.products.product_service_category import ProductServiceCategory
from ....Models.shapes.custom_field import CustomField
from ....Models.utils.utility import Utility

logger = logging.getLogger(__name__)

class ExpenseController(Controller):

    @classmethod
    def bill_number(cls, request: HttpRequest) -> int:
        try:
            latest = Bill.objects.filter(created_by=request.user.id, type='Bill')\
                         .order_by('-id').first()
            return 1 if not latest else latest.bill_id + 1
        except Exception as e:
            logger.error(f"{cls.__name__}::{inspect.currentframe().f_code.co_name} failed: {e}")
            return 1

    @classmethod
    def expense_number(cls, request: HttpRequest) -> int:
        try:
            latest = Bill.objects.filter(created_by=request.user.id, type='Expense')\
                         .order_by('-id').first()
            return 1 if not latest else latest.bill_id + 1
        except Exception as e:
            logger.error(f"{cls.__name__}::{inspect.currentframe().f_code.co_name} failed: {e}")
            return 1

    @classmethod
    def employee(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            emp = Employee.objects.filter(id=request.GET.get('id', '')).first()
            return render(request, 'expense/employee_detail.html', {'employee': emp})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def vendor(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            ven = Vendor.objects.filter(id=request.GET.get('id', '')).first()
            return render(request, 'expense/vendor_detail.html', {'vendor': ven})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def customer(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            cust = Customer.objects.filter(id=request.GET.get('id', '')).first()
            return render(request, 'expense/customer_detail.html', {'customer': cust})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def product(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            prod = ProductService.objects.filter(id=request.GET.get('product_id', '')).first()
            data = {}
            if prod:
                data = {
                    'product': prod,
                    'unit': prod.unit.name if prod.unit else '',
                    'taxRate': prod.taxRate(prod.tax_id) if prod.tax_id else 0,
                    'taxes': prod.tax(prod.tax_id) if prod.tax_id else 0,
                    'totalAmount': prod.purchase_price
                }
            return Utility.json_response(data)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('manage_bill'):
                raise PermissionDenied('User lacks permission: manage_bill')
            qs = Bill.objects.filter(type='Expense', created_by=request.user.id)
            ven = request.GET.get('vendor', '').strip()
            dr = request.GET.get('bill_date', '').strip()
            cat = request.GET.get('category', '').strip()
            if ven:
                qs = qs.filter(vendor_id=ven)
            if ' to ' in dr:
                start, end = dr.split(' to ',1)
                qs = qs.filter(bill_date__range=(start, end))
            elif dr:
                qs = qs.filter(bill_date__range=(dr, dr))
            if cat:
                qs = qs.filter(category_id=cat)
            return render(request, 'expense/index.html', {
                'expenses': qs.all(),
                'vendor': Vendor.objects.filter(created_by=request.user.id).values_list('id','name'),
                'status': Bill.statues(),
                'category': ProductServiceCategory.objects.filter(
                    created_by=request.user.id
                ).exclude(type__in=['product & service','income']).values_list('id','name')
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def create(cls, request: HttpRequest, id: int) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('create_bill'):
                raise PermissionDenied('User lacks permission: create_bill')
            return render(request, 'expense/create.html', {
                'customFields': CustomField.objects.filter(created_by=request.user.id, module='bill'),
                'category': ProductServiceCategory.objects.filter(
                    created_by=request.user.id
                ).exclude(type__in=['product & service','income']).values_list('id','name'),
                'expense_number': Utility.expense_number_format(cls.expense_number(request)),
                'employees': Employee.objects.filter(created_by=request.user.id).values_list('id','name'),
                'customers': Customer.objects.filter(created_by=request.user.id).values_list('id','name'),
                'vendors': Vendor.objects.filter(created_by=request.user.id).values_list('id','name'),
                'product_services': ProductService.objects.filter(created_by=request.user.id).values_list('id','name'),
                'chartAccounts': ChartOfAccount.objects.filter(created_by=request.user.id).values_list('id','code','name'),
                'accounts': BankAccount.objects.filter(created_by=request.user.id).values_list('id','bank_name','holder_name'),
                'Id': id
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def store(cls, request: HttpRequest) -> HttpResponse:
        if request.method != 'POST':
            return redirect(get_redirect_url(request))
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('create_bill'):
                raise PermissionDenied('User lacks permission: create_bill')
            pay_date = request.POST.get('payment_date','').strip()
            if not pay_date:
                messages.error(request, 'Payment date is required.')
                return redirect(get_redirect_url(request))
            expense = Bill(
                bill_id=cls.expense_number(request),
                vendor_id=request.POST.get(f"{request.POST.get('type','')}_id",'0'),
                bill_date=pay_date,
                status=4,
                type='Expense',
                user_type=request.POST.get('type',''),
                due_date=pay_date,
                category_id=request.POST.get('category_id','0') or 0,
                order_number=0,
                created_by=request.user.id
            )
            expense.save()
            BillPayment.objects.create(
                bill_id=expense.id,
                date=pay_date,
                amount=request.POST.get('totalAmount','0'),
                account_id=request.POST.get('account_id','0'),
                payment_method=0,
                reference='NULL',
                description='NULL',
                add_receipt='NULL'
            )
            messages.success(request, 'Expense successfully created.')
            return redirect('expense_index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def show(cls, request: HttpRequest, ids: str) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('show_bill'):
                raise PermissionDenied('User lacks permission: show_bill')
            pk = Signer().unsign(ids)
            expense = get_object_or_404(Bill, id=pk, created_by=request.user.id)
            exp_pay = BillPayment.objects.filter(bill_id=expense.id).first()
            if expense.user_type == 'employee':
                user = Employee.objects.filter(id=expense.vendor_id).first()
            elif expense.user_type == 'customer':
                user = Customer.objects.filter(id=expense.vendor_id).first()
            else:
                user = Vendor.objects.filter(id=expense.vendor_id).first()
            item_qs = expense.items.all()
            acc_qs = expense.accounts.all()
            items = []
            if item_qs:
                for i, val in enumerate(item_qs):
                    c_id = acc_qs[i].chart_account_id if i < len(acc_qs) else None
                    a_id = acc_qs[i].id if i < len(acc_qs) else None
                    pr   = acc_qs[i].price if i < len(acc_qs) else 0
                    items.append({
                        'chart_account_id': c_id,
                        'account_id':       a_id,
                        'amount':           pr,
                        'product_id':       val.product_id,
                        'quantity':         val.quantity,
                        'tax':              val.tax,
                        'discount':         val.discount,
                        'price':            val.price,
                        'description':      val.description,
                    })
            else:
                for val in acc_qs:
                    items.append({
                        'chart_account_id': val.chart_account_id,
                        'account_id':       val.id,
                        'amount':           val.price,
                    })
            return render(request, 'expense/view.html', {
                'expense':         expense,
                'user':            user,
                'items':           items,
                'expensePayment':  exp_pay,
            })
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except BadSignature as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def items(cls, request: HttpRequest) -> JsonResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            bp = BillProduct.objects.filter(
                bill_id=request.GET.get('bill_id',''),
                product_id=request.GET.get('product_id','')
            ).first()
            data = {
                'id':          bp.id,
                'quantity':    bp.quantity,
                'tax':         bp.tax,
                'discount':    bp.discount,
                'price':       bp.price,
                'description': bp.description
            } if bp else {}
            return Utility.json_response(data)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def edit(cls, request: HttpRequest, ids: str) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('edit_bill'):
                raise PermissionDenied('User lacks permission: edit_bill')
            pk = Signer().unsign(ids)
            expense = get_object_or_404(Bill, id=pk)
            cat_qs    = ProductServiceCategory.objects.filter(
                            created_by=request.user.id
                        ).exclude(type__in=['product & service','income'])\
                         .values_list('id','name')
            expense_num=Utility.expense_number_format(expense.bill_id)
            vendors   = Vendor.objects.filter(created_by=request.user.id).values_list('id','name')
            employees = Employee.objects.filter(created_by=request.user.id).values_list('id','name')
            customers = Customer.objects.filter(created_by=request.user.id).values_list('id','name')
            product_svcs   = ProductService.objects.filter(created_by=request.user.id).values_list('id','name')
            chart_accs     = ChartOfAccount.objects.filter(created_by=request.user.id).values_list('id','code','name')
            bank_accs      = BankAccount.objects.filter(created_by=request.user.id).values_list('id','bank_name','holder_name')
            item_qs = expense.items.all()
            acc_qs  = expense.accounts.all()
            items   = []
            if item_qs:
                for i, val in enumerate(item_qs):
                    c_id = acc_qs[i].chart_account_id if i < len(acc_qs) else None
                    a_id = acc_qs[i].id               if i < len(acc_qs) else None
                    pr   = acc_qs[i].price            if i < len(acc_qs) else 0
                    items.append({
                        'id':               val.id,
                        'chart_account_id': c_id,
                        'account_id':       a_id,
                        'amount':           pr,
                        'product_id':       val.product_id,
                        'quantity':         val.quantity,
                        'tax':              val.tax,
                        'discount':         val.discount,
                        'price':            val.price,
                        'description':      val.description,
                    })
            else:
                for val in acc_qs:
                    items.append({
                        'id':               val.id,
                        'chart_account_id': val.chart_account_id,
                        'account_id':       val.id,
                        'amount':           val.price,
                    })
            return render(request, 'expense/edit.html', {
                'expense':          expense,
                'expense_number':   expense_num,
                'vendors':          vendors,
                'employees':        employees,
                'customers':        customers,
                'product_services': product_svcs,
                'category':         cat_qs,
                'bank_Account':     bank_accs,
                'chartAccounts':    chart_accs,
                'items':            items,
            })
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except BadSignature as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def update(cls, request: HttpRequest, id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        if request.method != 'POST':
            return redirect(get_redirect_url(request))
        try:
            if not request.user.has_perm('edit_bill'):
                raise PermissionDenied('User lacks permission: edit_bill')
            expense = get_object_or_404(Bill, id=id, created_by=request.user.id)
            bill_date = request.POST.get('bill_date','').strip()
            if not bill_date:
                messages.error(request, 'Bill date is required.')
                return redirect('expense_index')
            expense.vendor_id  = request.POST.get(f"{request.POST.get('type','')}_id",'0')
            expense.bill_date  = bill_date
            expense.due_date   = bill_date
            expense.order_number = 0
            expense.category_id  = request.POST.get('category_id','0')
            expense.save()
            BillPayment.objects.create(
                bill_id=expense.id,
                date=bill_date,
                amount=request.POST.get('totalAmount','0'),
                account_id=request.POST.get('account_id','0'),
                payment_method=0,
                reference='NULL',
                description='NULL',
                add_receipt='NULL'
            )
            messages.success(request, 'Expense successfully updated.')
            return redirect('expense_index')
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    @permission_required('app.show_expense', raise_exception=True)
    def view(cls, request: HttpRequest, expense_id: str) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        # Step 1: Decrypt and fetch
        try:
            if not request.user.has_perm('show_bill'):
                raise PermissionDenied('User lacks permission: show_bill')
            pk = loads(expense_id)
            expense = Bill.objects.get(id=pk, created_by=request.user.id)
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except BadSignature as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Bill.DoesNotExist:
            return default_permission_denial(request, err=PermissionDenied(), ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

        try:
            settings = Utility.settings(expense.created_by) or {}
            for row in Utility.raw_settings(expense.created_by):
                settings[row.name] = row.value
        except Exception:
            settings = {}
            messages.warning(request, f"Could not load settings for {REF}")

        try:
            vendor = getattr(expense, 'vendor', None)
        except Exception:
            vendor = None

        total_tax = total_qty = total_rate = total_discount = 0
        taxes_data = {}
        items = []
        for prod in getattr(expense, 'items', []):
            try:
                name    = prod.product.name if prod.product else ''
                qty     = prod.quantity or 0
                tax_pct = prod.tax or 0
                disc    = prod.discount or 0
                price   = prod.price or 0
                desc    = prod.description or ''
            except Exception:
                continue

            total_qty      += qty
            total_rate     += price
            total_discount += disc

            item_taxes = []
            if tax_pct:
                for tax in Utility.tax(tax_pct) or []:
                    try:
                        tax_price = Utility.taxRate(tax.rate, price, qty, disc)
                        total_tax += tax_price
                        item_taxes.append({
                            'name':      tax.name,
                            'rate':      f"{tax.rate}%",
                            'price':     Utility.priceFormat(settings, tax_price),
                            'tax_price': tax_price,
                        })
                        taxes_data[tax.name] = taxes_data.get(tax.name, 0) + tax_price
                    except Exception:
                        continue

            item = type('X', (), {})()
            item.name        = name
            item.quantity    = qty
            item.tax         = tax_pct
            item.discount    = disc
            item.price       = price
            item.description = desc
            item.itemTax     = item_taxes
            items.append(item)

        expense.itemData      = items
        expense.totalTaxPrice = total_tax
        expense.totalQuantity = total_qty
        expense.totalRate     = total_rate
        expense.totalDiscount = total_discount
        expense.taxesData     = taxes_data

        try:
            expense.customField = CustomField.getData(expense, 'bill')
        except Exception:
            expense.customField = []

        try:
            logo_base   = Utility.get_file_url('bill_logo/')
            custom_logo = settings.get('bill_logo') or ''
            if custom_logo:
                img = f"{logo_base}{custom_logo}"
            else:
                default_logo = Utility.getValByName('company_logo_dark') or 'logo-dark.png'
                img = f"{logo_base}{default_logo}"
        except Exception:
            img = ''

        color      = f"#{settings.get('bill_color','000000')}"
        font_color = Utility.getFontColor(color) if hasattr(Utility, 'getFontColor') else '#000'
        template   = f"bill/templates/{settings.get('bill_template','default')}.html"

        return render(request, template, {
            'expense':    expense,
            'vendor':     vendor,
            'settings':   settings,
            'color':      color,
            'font_color': font_color,
            'img':        img,
        })
    
    @classmethod
    def product_destroy(cls, request: HttpRequest) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('delete_bill_product'):
                raise PermissionDenied('User lacks permission: delete_bill_product')
            with transaction.atomic():
                pid = request.POST.get('id','')
                amt = request.POST.get('amount','0')
                bp  = BillProduct.objects.filter(id=pid).first()
                if bp:
                    ex = Bill.objects.filter(id=bp.bill_id).first()
                    if ex:
                        Utility.update_user_balance('vendor', ex.vendor_id, amt, 'credit')
                    bp.delete()
            messages.success(request, 'Expense product successfully deleted.')
            return redirect(get_redirect_url(request))
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def destroy(cls, request: HttpRequest, id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('delete_bill'):
                raise PermissionDenied('User lacks permission: delete_bill')
            with transaction.atomic():
                expense = get_object_or_404(Bill, id=id, created_by=request.user.id)
                for pay in expense.payments.all():
                    Utility.bank_account_balance(pay.account_id, pay.amount, 'credit')
                    pay.delete()
                if expense.vendor_id != 0 and expense.status != 0:
                    Utility.update_user_balance('vendor', expense.vendor_id, expense.get_due(), 'credit')
                BillProduct.objects.filter(bill_id=expense.id).delete()
                BillAccount.objects.filter(ref_id=expense.id).delete()
                expense.delete()
            messages.success(request, 'Expense successfully deleted.')
            return redirect('expense_index')
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def payment(cls, request: HttpRequest) -> JsonResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            bill_id = request.GET.get('bill_id','').strip()
            if not bill_id:
                return JsonResponse({'error': 'bill_id is required'}, status=400)
            payments = BillPayment.objects.filter(bill_id=bill_id)\
                           .select_related('bank_account')\
                           .order_by('-date')
            payload = [{
                'id':           str(p.id),
                'date':         p.date.isoformat(),
                'amount':       float(p.amount),
                'account_id':   str(p.account_id),
                'account_name': p.bank_account.holder_name if hasattr(p, 'bank_account') and p.bank_account else '',
                'method':       p.payment_method,
                'reference':    p.reference or '',
            } for p in payments]
            return JsonResponse(payload, safe=False)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger, json={'error':'Could not load payments'}, status=500)

    @method_decorator(login_required)
    @classmethod
    @permission_required('app.manage_bill', raise_exception=True)
    def expense_list(cls, request: HttpRequest) -> JsonResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            qs = Bill.objects.filter(type='Expense', created_by=request.user.id)
            ven = request.GET.get('vendor','').strip()
            dr  = request.GET.get('bill_date','').strip()
            cat = request.GET.get('category','').strip()
            if ven:
                qs = qs.filter(vendor_id=ven)
            if ' to ' in dr:
                start, end = dr.split(' to ',1)
                qs = qs.filter(bill_date__range=(start, end))
            elif dr:
                qs = qs.filter(bill_date=dr)
            if cat:
                qs = qs.filter(category_id=cat)
            qs = qs.select_related('vendor').order_by('-bill_date')
            result = [{
                'id':        str(exp.id),
                'bill_id':   exp.bill_id,
                'vendor':    exp.vendor.name if exp.vendor else '',
                'bill_date': exp.bill_date.isoformat(),
                'due_date':  exp.due_date.isoformat(),
                'subtotal':  float(exp.get_sub_total()),
                'discount':  float(exp.get_total_discount()),
                'tax':       float(exp.get_total_tax()),
                'total':     float(exp.get_total()),
                'paid':      float(exp.payments.aggregate(total=F('amount'))['total'] or 0),
                'due':       float(exp.get_due()),
                'status':    exp.status,
            } for exp in qs]
            return JsonResponse(result, safe=False)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger, json={'error':'Could not load expenses'}, status=500)