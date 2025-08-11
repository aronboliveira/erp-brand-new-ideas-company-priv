import datetime
import time
import inspect
import logging
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core import signing
from django.db import connection
from django.http import HttpRequest, HttpResponse
from django.shortcuts import redirect, render
from django.utils.decorators import method_decorator
from ....Models.activity.order import Order
from ....Models.bills.coupon import Coupon
from ....Models.bills.invoice import Invoice
from ....Models.bills.invoice_bank_transfer import InvoiceBankTransfer
from ....Models.bills.invoice_payment import InvoicePayment
from ....Models.bills.user_coupon import UserCoupon
from ....Models.planning.plan import Plan
from ....Models.individuals.user import User
from ....Models.utils.utility import Utility
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_undefined_exception
from .._traits.controller import Controller
logger = logging.getLogger(__name__)
class BankTransferPaymentController(Controller):

    @method_decorator(login_required)
    @classmethod
    def plan_pay_with_bank(cls, request: HttpRequest) -> HttpResponse:
        try:
            if not request.POST.get('payment_receipt'):
                messages.error(request, "Payment receipt is required.")
                return redirect(get_redirect_url(request))
            try:
                plan_id = signing.loads(request.POST.get('plan_id'))
            except Exception:
                messages.error(request, "Invalid plan id.")
                return redirect(get_redirect_url(request))
            plan = Plan.objects.filter(id=plan_id).first()
            authuser = request.user
            # coupon_id = ""
            if plan:
                price = plan.price
                if request.POST.get('coupon'):
                    coupons = Coupon.objects.filter(
                        code=request.POST.get('coupon').upper(), is_active='1'
                    ).first()
                    if coupons:
                        used_coupon = coupons.used_coupon()
                        discount_value = (plan.price / 100) * coupons.discount
                        price = plan.price - discount_value
                        if coupons.limit == used_coupon:
                            messages.error(request, "This coupon code has expired.")
                            return redirect(get_redirect_url(request))
                        # coupon_id = coupons.id
                    else:
                        # TODO: fix double request parameter in messages.error
                        messages.error(request, "This coupon code is invalid or has expired.")
                        return redirect(get_redirect_url(request))
                order_id = str(time.time()).replace(".", "").upper()
                fileName = ""
                if request.FILES.get('payment_receipt'):
                    file_obj = request.FILES['payment_receipt']
                    fileName = f"{int(time.time())}_{file_obj.name}"
                    dir_path = "uploads/order"
                    Utility.upload_file(request, 'payment_receipt', fileName, dir_path, [])
                Order.objects.create(
                    order_id=order_id,
                    name=None,
                    email=None,
                    card_number=None,
                    card_exp_month=None,
                    card_exp_year=None,
                    plan_name=plan.name,
                    plan_id=plan.id,
                    price=price,
                    price_currency="USD",
                    txn_id="",
                    payment_type="Bank Transfer",
                    payment_status="Pending",
                    receipt=fileName,
                    user_id=authuser.id,
                )
                if request.POST.get('coupon'):
                    user_coupon = UserCoupon()
                    user_coupon.user = authuser.id
                    user_coupon.coupon = coupons.id
                    user_coupon.order = order_id
                    user_coupon.save()
                    used_coupon = coupons.used_coupon()
                    if coupons.limit <= used_coupon:
                        coupons.is_active = 0
                        coupons.save()
                messages.success(request, "Plan payment request send successfully")
                return redirect("plans.index")
            else:
                messages.error(request, "Plan is deleted.")
                return redirect("plans.index")
        except Exception as e:
            ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def order_destroy(cls, request: HttpRequest, id: int) -> HttpResponse:
        try:
            Order.objects.filter(id=id).delete()
            messages.success(request, "Order successfully deleted.")
            return redirect(get_redirect_url(request))
        except Exception as e:
            ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def action(cls, request: HttpRequest, id: int) -> HttpResponse:
        try:
            order = Order.objects.filter(id=id).first()
            admin_payment_setting = Utility.getAdminPaymentSetting()
            return render(
                request,
                'order/action.html',
                {'order': order, 'admin_payment_setting': admin_payment_setting}
            )
        except Exception as e:
            ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def change_status(cls, request: HttpRequest, order_id: str) -> HttpResponse:
        try:
            order = Order.objects.filter(id=request.POST.get('order_id')).first()
            if request.POST.get('status') == 'Approval':
                plan = Plan.objects.filter(id=order.plan_id).first()
                authuser = User.objects.filter(id=order.user_id).first()
                authuser.plan = plan.id
                authuser.assignPlan(plan.id, authuser.id)
                order.payment_status = 'Approved'
            else:
                order.payment_status = 'Rejected'
            order.save()
            messages.success(request, "Plan payment status updated successfully.")
            return redirect("order.index")
        except Exception as e:
            ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def customer_pay_with_bank(cls, request: HttpRequest) -> HttpResponse:
        try:
            if not request.POST.get('payment_receipt'):
                messages.error(request, "Payment receipt is required.")
                return redirect(get_redirect_url(request))
            try:
                invoice_id = signing.loads(request.POST.get('invoice_id'))
            except Exception:
                messages.error(request, "Invalid invoice id.")
                return redirect(get_redirect_url(request))
            invoice = Invoice.objects.filter(id=invoice_id).first()
            user_obj = User.objects.filter(id=invoice.created_by).first()
            if invoice:
                fileName = ""
                if request.FILES.get('payment_receipt'):
                    file_obj = request.FILES['payment_receipt']
                    fileName = f"{int(time.time())}_{file_obj.name}"
                    dir_path = "uploads/order"
                    Utility.upload_file(request, 'payment_receipt', fileName, dir_path, [])
                order_id = str(time.time()).replace(".", "").upper()
                InvoiceBankTransfer.objects.create(
                    invoice_id=invoice.id,
                    order_id=order_id,
                    amount=request.POST.get('amount'),
                    status="Pending",
                    date=datetime.date.today(),
                    receipt=fileName,
                    created_by=user_obj.id,
                )
                messages.success(request, "Invoice payment request send successfully.")
                return redirect(get_redirect_url(request))
            else:
                messages.success(request, "Invoice payment request send successfully.")
                return redirect(get_redirect_url(request))
        except Exception as e:
            ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def invoice_action(cls, request: HttpRequest, id: int) -> HttpResponse:
        try:
            ibt = InvoiceBankTransfer.objects.filter(id=id).first()
            invoice = Invoice.objects.filter(id=ibt.invoice_id).first()
            user_id = ibt.created_by
            company_payment_setting = Utility.getCompanyPaymentSetting(user_id)
            return render(
                request,
                'invoice/action.html',
                {
                    'invoiceBankTransfer': ibt,
                    'company_payment_setting': company_payment_setting,
                    'invoice': invoice
                }
            )
        except Exception as e:
            ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def invoice_change_status(cls, request: HttpRequest, invoice_id: int) -> HttpResponse:
        try:
            ibt = InvoiceBankTransfer.objects.filter(id=request.POST.get('order_id')).first()
            invoice = Invoice.objects.filter(id=ibt.invoice_id).first()
            with connection.cursor() as cursor:
                cursor.execute(
                    "SELECT value, name FROM settings WHERE created_by = %s",
                    [ibt.created_by]
                )
                settings_dict = dict(cursor.fetchall())
            if request.POST.get('status') == 'Approval':
                ibt.status = 'Approved'
                InvoicePayment.objects.create(
                    invoice_id=ibt.invoice_id,
                    date=datetime.date.today(),
                    amount=ibt.amount,
                    payment_method=1,
                    order_id=ibt.order_id,
                    payment_type="Bank Transfer",
                    receipt=ibt.receipt,
                    description="Invoice " + Utility.invoiceNumberFormat(settings_dict, invoice.invoice_id),
                )
                ibt.delete()
            else:
                ibt.status = 'Rejected'
                ibt.save()
            messages.success(request, "Invoice payment request status updated successfully.")
            return redirect(get_redirect_url(request))
        except Exception as e:
            ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)
