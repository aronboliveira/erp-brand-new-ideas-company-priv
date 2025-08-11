import logging
import inspect
import json
import requests
from django.shortcuts import redirect
from django.contrib import messages
from django.http import HttpRequest, HttpResponse
from django.urls import reverse

from ....Models.activity.order import Order
from ....Models.bills.coupon import Coupon
from ....Models.bills.invoice import Invoice
from ....Models.bills.invoice_payment import InvoicePayment
from ....Models.bills.user_coupon import UserCoupon
from ....Models.individuals.customer import Customer
from ....Models.individuals.user import User
from ....Models.planning.plan import Plan
from ....Models.utils.utility import Utility
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_undefined_exception
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class BenefitPaymentController(Controller):

    @classmethod
    def initiate_payment(cls, request: HttpRequest) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            # settings_dict = Utility.settings()
            admin_settings = Utility.get_admin_payment_setting()
            secret_key = admin_settings.get('benefit_secret_key')
            user = request.user
            plan_id = Utility.decrypt(request.POST.get('plan_id'))
            plan = Plan.objects.filter(id=plan_id).first()
            if not plan:
                return redirect('plans.index')
            price = plan.price
            coupon_code = request.POST.get('coupon', '').strip()
            # coupon_id = None
            if coupon_code:
                coupon = Coupon.objects.filter(
                    code=coupon_code.upper(),
                    is_active=True
                ).first()
                if not coupon:
                    messages.error(request, "This coupon code is invalid or has expired.")
                    return redirect(get_redirect_url(request))
                if coupon.used_coupon() >= coupon.limit:
                    messages.error(request, "This coupon code has expired.")
                    return redirect(get_redirect_url(request))
                discount = (plan.price / 100) * coupon.discount
                price -= discount
                # coupon_id = coupon.id
                if price <= 0:
                    user.plan_id = plan.id
                    user.save()
                    result = user.assign_plan(plan.id)
                    if result.get('is_success'):
                        Order.objects.create(
                            order_id=Utility.generate_order_id(),
                            plan_name=plan.name,
                            plan_id=plan.id,
                            price=0,
                            price_currency=admin_settings.get('currency', 'BHD'),
                            payment_type='Benefit',
                            payment_status='success',
                            user_id=user.id
                        )
                        if user.payment_subscription_id:
                            try:
                                user.cancel_subscription(user.id)
                            except Exception as e:
                                logger.exception(f"Failed to cancel subscription: {e}")
                        UserCoupon.objects.create(
                            user=user.id,
                            coupon=coupon.id,
                            order=Utility.generate_order_id()
                        )
                        return redirect('plans.index')
            payload = {
                'amount': price,
                'currency': admin_settings.get('currency', 'BHD'),
                'customer_initiated': True,
                'threeDSecure': True,
                'save_card': False,
                'description': f'Plan - {plan.name}',
                'metadata': {'udf1': 'Metadata 1'},
                'reference': {'transaction': 'txn_01', 'order': 'ord_01'},
                'receipt': {'email': True, 'sms': True},
                'customer': {
                    'first_name': user.name,
                    'middle_name': '',
                    'last_name': '',
                    'email': user.email,
                    'phone': {'country_code': 965, 'number': '51234567'}
                },
                'source': {'id': 'src_bh.benefit'},
                'post': {'url': 'https://webhook.site/fd8b0712-d70a-4280-8d6f-9f14407b3bbd'},
                'redirect': {
                    'url': reverse('BenefitPaymentController.call_back', kwargs={
                        'plan_id': plan.id,
                        'amount': price,
                        'coupon': coupon_code if coupon_code else '0'
                    })
                }
            }
            res = requests.post(
                'https://api.tap.company/v2/charges',
                headers={
                    'Authorization': f'Bearer {secret_key}',
                    'accept': 'application/json',
                    'content-type': 'application/json',
                },
                data=json.dumps(payload)
            )
            return redirect(res.json()['transaction']['url'])
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def call_back(cls, request: HttpRequest, plan_id: int,
                  amount: float, coupon: str) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            settings_dict = Utility.get_admin_payment_setting()
            secret_key = settings_dict.get('benefit_secret_key')
            user = request.user
            plan = Plan.objects.get(id=plan_id)
            order_id = Utility.generate_order_id()
            coupon_id = None
            if coupon != '0':
                coupon_obj = Coupon.objects.filter(
                    code=coupon.upper(),
                    is_active=True
                ).first()
                coupon_id = coupon_obj.id if coupon_obj else None
            tap_id = request.GET.get('tap_id')
            res = requests.get(
                f'https://api.tap.company/v2/charges/{tap_id}',
                headers={
                    'Authorization': f'Bearer {secret_key}',
                    'accept': 'application/json'
                }
            )
            data = res.json()
            status_code = data['gateway']['response']['code']
            if status_code == '00':
                Order.objects.create(
                    order_id=order_id,
                    name=user.name,
                    plan_name=plan.name,
                    plan_id=plan.id,
                    price=amount,
                    price_currency=settings_dict.get('currency', 'BHD'),
                    payment_type='Benefit',
                    payment_status='success',
                    user_id=user.id
                )
                if coupon_id:
                    user_coupon = UserCoupon(user=user.id, coupon=coupon_id, order=order_id)
                    user_coupon.save()
                    c_obj = Coupon.objects.get(id=coupon_id)
                    if c_obj.used_coupon() >= c_obj.limit:
                        Coupon.objects.filter(id=coupon_id).update(is_active=False)
                result = user.assign_plan(plan.id)
                messages.success(request, 'Plan activated Successfully.') if result.get('is_success') \
                    else messages.error(request, result.get('error'))
            else:
                messages.error(request, 'Your Transaction has failed.')
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)
        return redirect('plans.index')

    @classmethod
    def invoice_pay_with_benefit(cls, request: HttpRequest) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            invoice_id = Utility.decrypt(request.POST.get('invoice_id'))
            invoice = Invoice.objects.get(id=invoice_id)
            user = User.objects.get(id=invoice.created_by)
            settings_dict = Utility.settings_by_id(user.id)
            company_settings = Utility.get_company_payment_setting(user.id)
            secret_key = company_settings.get('benefit_secret_key')
            amount = float(request.POST.get('amount'))
            if amount > invoice.get_due():
                messages.error(request, 'Invalid amount.')
                return redirect(get_redirect_url(request))
            payload = {
                'amount': amount,
                'currency': settings_dict.get('site_currency', 'BHD'),
                'customer_initiated': True,
                'threeDSecure': True,
                'save_card': False,
                'description': invoice.invoice_id,
                'metadata': {'udf1': 'Metadata 1'},
                'reference': {'transaction': 'txn_01', 'order': 'ord_01'},
                'receipt': {'email': True, 'sms': True},
                'customer': {
                    'first_name': user.name,
                    'middle_name': '',
                    'last_name': '',
                    'email': user.email,
                    'phone': {'country_code': 965, 'number': '51234567'}
                },
                'source': {'id': 'src_bh.benefit'},
                'post': {'url': 'https://webhook.site/fd8b0712-d70a-4280-8d6f-9f14407b3bbd'},
                'redirect': {
                    'url': reverse('BenefitPaymentController.get_invoice_payment_status', kwargs={
                        'invoice_id': invoice_id,
                        'amount': amount
                    })
                }
            }
            res = requests.post(
                'https://api.tap.company/v2/charges',
                headers={
                    'Authorization': f'Bearer {secret_key}',
                    'accept': 'application/json',
                    'content-type': 'application/json',
                },
                data=json.dumps(payload)
            )
            return redirect(res.json()['transaction']['url'])
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def get_invoice_payment_status(cls, request: HttpRequest,
                                   invoice_id: int, amount: float) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            invoice = Invoice.objects.get(id=invoice_id)
            order_id = Utility.generate_order_id()
            user = User.objects.get(id=invoice.created_by)
            company_settings = Utility.get_company_payment_setting(user.id)
            secret_key = company_settings.get('benefit_secret_key')
            settings_dict = Utility.settings_by_id(user.id)
            tap_id = request.GET.get('tap_id')
            res = requests.get(
                f'https://api.tap.company/v2/charges/{tap_id}',
                headers={
                    'Authorization': f'Bearer {secret_key}',
                    'accept': 'application/json'
                }
            )
            data = res.json()
            status_code = data['gateway']['response']['code']
            if status_code == '00':
                InvoicePayment.objects.create(
                    invoice_id=invoice_id,
                    date=Utility.today_str(),
                    amount=amount,
                    account_id=0,
                    payment_method=0,
                    order_id=order_id,
                    payment_type='Benefit',
                    receipt='',
                    reference='',
                    description=f'Invoice {Utility.invoice_number_format(settings_dict, invoice.invoice_id)}'
                )
                due = invoice.get_due() - amount
                invoice.status = 3 if due == 0 else 2
                invoice.save()
                Utility.update_user_balance('customer', invoice.customer_id, amount, 'debit')
                customer = Customer.objects.get(id=invoice.customer_id)
                notification = {
                    'payment_price': amount,
                    'invoice_payment_type': 'Benefit',
                    'customer_name': customer.name
                }
                if settings_dict.get('payment_notification'):
                    Utility.send_slack_msg('new_invoice_payment', notification, invoice.created_by)
                if settings_dict.get('telegram_payment_notification'):
                    Utility.send_telegram_msg('new_invoice_payment', notification, invoice.created_by)
                if settings_dict.get('twilio_payment_notification'):
                    Utility.send_twilio_msg(
                        customer.contact,
                        'new_invoice_payment',
                        notification,
                        invoice.created_by
                    )
                webhook = Utility.webhook_setting('New Invoice Payment', invoice.created_by)
                if webhook:
                    status = Utility.webhook_call(
                        webhook['url'],
                        json.dumps(notification),
                        webhook['method']
                    )
                    if status:
                        messages.error(request, 'Transaction has been failed.')
                        return redirect('InvoiceController.copy', invoice_id)
                    else:
                        messages.error(request, 'Webhook call failed.')
                        return redirect(get_redirect_url(request))
                messages.success(request, 'Invoice paid Successfully!')
                return redirect('InvoiceController.copy', invoice_id)
            messages.error(request, 'Transaction fail!')
            return redirect('InvoiceController.copy', invoice_id)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)
