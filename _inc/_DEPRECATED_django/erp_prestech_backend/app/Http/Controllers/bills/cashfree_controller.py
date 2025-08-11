import datetime
import requests
import uuid
from django.conf import settings
from django.contrib import messages
from django.core.signing import BadSignature, Signer
from django.shortcuts import redirect
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
from .._traits.controller import Controller
import logging
from django.http import HttpRequest, HttpResponse
from django.db import transaction

logger = logging.getLogger(__name__)

class CashfreeController(Controller):
  def decrypt_id(self, encrypted_id: str) -> int:
    signer = Signer()
    try:
      return int(signer.unsign(encrypted_id))
    except BadSignature as e:
      logger.error("Failed in decrypt_id: %s", e)
      raise

  def encrypt_id(self, plain_id: int) -> str:
    signer = Signer()
    return signer.sign(plain_id)

  def payment_config(self, request: HttpRequest) -> None:
    if request.user.is_authenticated:
      payment_setting = Utility.getAdminPaymentSetting()
      settings.SERVICES = getattr(settings, 'SERVICES', {})
      settings.SERVICES['cashfree'] = {
        'currency': payment_setting.get('currency', 'USD'),
        'key': payment_setting.get('cashfree_api_key', ''),
        'secret': payment_setting.get('cashfree_secret_key', ''),
        'url': payment_setting.get('cashfree_url', '')
      }

  def cashfree_payment_store(self, request: HttpRequest) -> HttpResponse:
    if request.method != 'POST':
      messages.error(request, "Invalid request method.")
      return redirect(reverse('plans_index'))
    try:
      planID = self.decrypt_id(request.POST.get('plan_id'))
    except Exception as e:
      logger.error("Failed in cashfreePaymentStore (decrypt_id): %s", e)
      messages.error(request, "Invalid plan id.")
      return redirect(reverse('plans_index'))
    plan = Plan.objects.filter(pk=planID).first()
    user = request.user
    self.paymentConfig(request)
    url = settings.SERVICES['cashfree'].get('url', '')
    if plan:
      get_amount = plan.price
      try:
        coupon_input = request.POST.get('coupon')
        if coupon_input:
          coupons = Coupon.objects.filter(code=coupon_input.upper(), is_active=1).first()
          if coupons:
            usedCoupon = coupons.used_coupon()
            discount_value = (plan.price / 100) * coupons.discount
            get_amount = plan.price - discount_value
            if coupons.limit == usedCoupon:
              messages.error(request, "This coupon code has expired.")
              return redirect(get_redirect_url(request))
            if get_amount <= 0:
              with transaction.atomic():
                user.plan = plan.id
                user.save()
                assignPlan = user.assign_plan(plan.id)
                if assignPlan.get('is_success') and plan:
                  if getattr(user, 'payment_subscription_id', ''):
                    try:
                      user.cancel_subscription(user.id)
                    except Exception as exception:
                      logger.error("Failed to cancel subscription in cashfreePaymentStore: %s", exception)
                  orderID = uuid.uuid4().hex.upper()
                  userCoupon = UserCoupon(user=user.id, coupon=coupons.id, order=orderID)
                  userCoupon.save()
                  Order.objects.create(
                    order_id=orderID,
                    name=None,
                    email=None,
                    card_number=None,
                    card_exp_month=None,
                    card_exp_year=None,
                    plan_name=plan.name,
                    plan_id=plan.id,
                    price=get_amount if get_amount is not None else 0,
                    price_currency=settings.SERVICES['cashfree'].get('currency', 'USD'),
                    txn_id='',
                    payment_type='Cashfree',
                    payment_status='success',
                    receipt=None,
                    user_id=user.id
                  )
                  user.assign_plan(plan.id)
                  messages.success(request, "Plan Successfully Activated")
                  return redirect(reverse('plans_index'))
          else:
            messages.error(request, "This coupon code is invalid or has expired.")
            return redirect(get_redirect_url(request))
        coupon = coupon_input if coupon_input else "0"
        orderID = uuid.uuid4().hex.upper()
        headers = {
          "Content-Type": "application/json",
          "x-api-version": "2022-01-01",
          "x-client-id": settings.SERVICES['cashfree'].get('key', ''),
          "x-client-secret": settings.SERVICES['cashfree'].get('secret', '')
        }
        data = {
          'order_id': orderID,
          'order_amount': get_amount,
          "order_currency": settings.SERVICES['cashfree'].get('currency', 'USD'),
          "order_name": plan.name,
          "customer_details": {
            "customer_email": user.email,
            "customer_id": f"customer_{user.id}",
            "customer_name": user.name,
            "customer_phone": "1234567890"
          },
          "order_meta": {
            "return_url": (request.build_absolute_uri(reverse('cashfreePayment_success')) +
                           f'?order_id={{order_id}}&order_token={{order_token}}&plan_id={plan.id}&amount={get_amount}&coupon={coupon}')
          }
        }
        try:
          resp = requests.post(url, json=data, headers=headers)
          resp_json = resp.json()
          return redirect(resp_json.get('payment_link'))
        except Exception as th:
          logger.error("Failed POST to Cashfree API in cashfreePaymentStore: %s", th)
          messages.error(request, "Currency Not Supported.Contact To Your Site Admin")
          return redirect(get_redirect_url(request))
      except Exception as e:
        logger.error("Error in processing coupon in cashfreePaymentStore: %s", e)
        messages.error(request, str(e))
        return redirect(get_redirect_url(request))
    else:
      messages.error(request, "Plan is deleted.")
      return redirect(reverse('plans_index'))

  def cashfree_payment_success(self, request: HttpRequest) -> HttpResponse:
    self.paymentConfig(request)
    user = request.user
    plan = Plan.objects.filter(pk=request.GET.get('plan_id')).first()
    couponCode = request.GET.get('coupon')
    getAmount = request.GET.get('amount')
    orderID = uuid.uuid4().hex.upper()
    if couponCode != "0":
      coupons = Coupon.objects.filter(code=couponCode.upper(), is_active=1).first()
      request.GET._mutable = True
      request.GET['coupon_id'] = coupons.id if coupons else None
    else:
      coupons = None
    try:
      headers = {
        'accept': 'application/json',
        'x-api-version': '2022-09-01',
        "x-client-id": settings.SERVICES['cashfree'].get('key', ''),
        "x-client-secret": settings.SERVICES['cashfree'].get('secret', '')
      }
      settlement_url = f"{settings.SERVICES['cashfree'].get('url', '')}/{request.GET.get('order_id')}/settlements"
      resp = requests.get(settlement_url, headers=headers)
      respons = resp.json()
      if respons.get('order_id') and respons.get('cf_payment_id'):
        payment_url = f"{settings.SERVICES['cashfree'].get('url', '')}/{respons.get('order_id')}/payments/{respons.get('cf_payment_id')}"
        resp_payment = requests.get(payment_url, headers=headers)
        info = resp_payment.json()
        if info.get('payment_status') == "SUCCESS":
          with transaction.atomic():
            order = Order()
            order.order_id = orderID
            order.name = user.name
            order.card_number = ''
            order.card_exp_month = ''
            order.card_exp_year = ''
            order.plan_name = plan.name
            order.plan_id = plan.id
            order.price = getAmount
            order.price_currency = settings.SERVICES['cashfree'].get('currency', 'USD')
            order.payment_type = "Cashfree"
            order.payment_status = 'success'
            order.txn_id = ''
            order.receipt = ''
            order.user_id = user.id
            order.save()
            if request.GET.get('coupon_id'):
              coupons = Coupon.objects.filter(pk=request.GET.get('coupon_id')).first()
              if coupons:
                userCoupon = UserCoupon(user=user.id, coupon=coupons.id, order=orderID)
                userCoupon.save()
                usedCoupon = coupons.used_coupon()
                if coupons.limit <= usedCoupon:
                  coupons.is_active = 0
                  coupons.save()
            assignPlan = user.assign_plan(plan.id)
          messages.success(request, "Plan activated Successfully.") if assignPlan.get('is_success') else messages.error(request, assignPlan.get('error', ''))
          return redirect(reverse('plans_index'))
        else:
          messages.error(request, "Your Transaction is fail please try again")
          return redirect(reverse('plans_index'))
      else:
        messages.error(request, "Payment Failed.")
        return redirect(reverse('plans_index'))
    except Exception as e:
      logger.error("Error in cashfreePaymentSuccess: %s", e)
      messages.error(request, str(e))
      return redirect(reverse('plans_index'))

  def invoice_pay_with_cashfree(self, request: HttpRequest) -> HttpResponse:
    try:
      invoice_id = self.decrypt_id(request.POST.get('invoice_id'))
      invoice = Invoice.objects.filter(pk=invoice_id).first()
      self.invoiceData = invoice
      try:
        user_obj = User.objects.filter(pk=invoice.created_by).first()
        settings_by_id = Utility.settingsById(invoice.created_by)
        companyPaymentSettings = Utility.getCompanyPaymentSetting(user_obj.id)
        settings.SERVICES = getattr(settings, 'SERVICES', {})
        settings.SERVICES['cashfree'] = {
          'key': companyPaymentSettings.get('cashfree_api_key', ''),
          'secret': companyPaymentSettings.get('cashfree_secret_key', ''),
          'url': companyPaymentSettings.get('cashfree_url', '')
        }
        url = settings.SERVICES['cashfree'].get('url', '')
        user_obj = request.user if request.user.is_authenticated else User.objects.filter(pk=invoice.created_by).first()
        get_amount = float(request.POST.get('amount', 0))
        orderID = uuid.uuid4().hex.upper()
        if invoice and get_amount != 0:
          if get_amount > invoice.getDue():
            messages.error(request, "Invalid amount.")
            return redirect(get_redirect_url(request))
          headers = {
            "Content-Type": "application/json",
            "x-api-version": "2022-01-01",
            "x-client-id": settings.SERVICES['cashfree'].get('key', ''),
            "x-client-secret": settings.SERVICES['cashfree'].get('secret', '')
          }
          data = {
            'order_id': orderID,
            'order_amount': get_amount,
            "order_currency": "INR",
            "order_name": invoice.name,
            "customer_details": {
              "customer_email": user_obj.email,
              "customer_id": f"customer_{user_obj.id}",
              "customer_name": user_obj.name,
              "customer_phone": "1234567890"
            },
            "order_meta": {
              "return_url": (request.build_absolute_uri(reverse('invoice_cashfreePayment_success')) +
                             f'?order_id={{order_id}}&invoice_id={invoice_id}&amount={get_amount}')
            }
          }
          try:
            resp = requests.post(url, json=data, headers=headers)
            resp_json = resp.json()
            return redirect(resp_json.get('payment_link'))
          except Exception as th:
            logger.error("Failed POST to Cashfree API in invoicepaywithcashfree: %s", th)
            messages.error(request, "Currency Not Supported.Contact To Your Site Admin")
            return redirect(get_redirect_url(request))
      except Exception as e:
        logger.error("Error in invoicepaywithcashfree (inner): %s", e)
        messages.error(request, str(e))
        return redirect(get_redirect_url(request))
    except Exception as e:
      logger.error("Error in invoicepaywithcashfree (outer): %s", e)
      messages.error(request, str(e))
      return redirect(get_redirect_url(request))

  def get_invoice_payment_status(self, request: HttpRequest) -> HttpResponse:
    invoice = Invoice.objects.filter(pk=request.GET.get('invoice_id')).first()
    orderID = uuid.uuid4().hex.upper()
    user_obj = User.objects.filter(pk=invoice.created_by).first()
    settings_by_id = Utility.settingsById(invoice.created_by)
    companyPaymentSettings = Utility.getCompanyPaymentSetting(user_obj.id)
    settings.SERVICES = getattr(settings, 'SERVICES', {})
    settings.SERVICES['cashfree'] = {
      'key': companyPaymentSettings.get('cashfree_api_key', ''),
      'secret': companyPaymentSettings.get('cashfree_secret_key', ''),
      'url': companyPaymentSettings.get('cashfree_url', '')
    }
    try:
      headers = {
        'accept': 'application/json',
        'x-api-version': '2022-09-01',
        "x-client-id": settings.SERVICES['cashfree'].get('key', ''),
        "x-client-secret": settings.SERVICES['cashfree'].get('secret', '')
      }
      settlement_url = f"{settings.SERVICES['cashfree'].get('url', '')}/{request.GET.get('order_id')}/settlements"
      resp = requests.get(settlement_url, headers=headers)
      respons = resp.json()
      if respons.get('order_id') and respons.get('cf_payment_id'):
        payment_url = f"{settings.SERVICES['cashfree'].get('url', '')}/{respons.get('order_id')}/payments/{respons.get('cf_payment_id')}"
        resp_payment = requests.get(payment_url, headers=headers)
        info = resp_payment.json()
        try:
          if info.get('payment_status') == "SUCCESS":
            with transaction.atomic():
              invoice_payment = InvoicePayment()
              invoice_payment.invoice_id = request.GET.get('invoice_id')
              invoice_payment.date = request.GET.get('date') or datetime.date.today().strftime('%Y-%m-%d')
              invoice_payment.amount = float(request.GET.get('amount', 0))
              invoice_payment.account_id = 0
              invoice_payment.payment_method = 0
              invoice_payment.order_id = orderID
              invoice_payment.payment_type = 'Cashfree'
              invoice_payment.receipt = ''
              invoice_payment.reference = ''
              invoice_payment.description = 'Invoice ' + Utility.invoiceNumberFormat(settings_by_id, invoice.invoice_id)
              invoice_payment.save()
              due = invoice.getDue() - invoice_payment.amount
              Invoice.change_status(invoice.id, 3) if due == 0 else Invoice.change_status(invoice.id, 2)
              invoice.save()
              setting = Utility.settingsById(invoice.created_by)
              customer = Customer.objects.filter(pk=invoice.customer_id).first()
              notificationArr = {
                'customer_name': customer.name,
                'invoice_payment_type': 'Cashfree',
                'payment_price': request.GET.get('amount')
              }
              if setting.get('payment_notification') == 1:
                Utility.send_slack_msg('new_invoice_payment', notificationArr, invoice.created_by)
              if setting.get('telegram_payment_notification') == 1:
                Utility.send_telegram_msg('new_invoice_payment', notificationArr, invoice.created_by)
              if setting.get('twilio_payment_notification') == 1:
                Utility.send_twilio_msg(customer.contact, 'new_invoice_payment', notificationArr, invoice.created_by)
              Utility.updateUserBalance('customer', invoice.customer_id, float(request.GET.get('amount', 0)), 'debit')
              if 'invoice_data' in request.session:
                del request.session['invoice_data']
            messages.success(request, "Invoice paid Successfully!")
            return redirect(reverse('invoice_link_copy', kwargs={'id': self.encrypt_id(invoice.id)}))
          else:
            messages.error(request, "Transaction fail")
            return redirect(reverse('invoice_link_copy', kwargs={'id': self.encrypt_id(invoice.id)}))
        except Exception as e:
          logger.error("Error while processing payment status in getInvoicePaymentStatus: %s", e)
          messages.error(request, str(e))
          return redirect(reverse('invoice_link_copy', kwargs={'id': self.encrypt_id(invoice.id)}))
      else:
        messages.error(request, "Payment Failed.")
        return redirect(reverse('invoice_link_copy', kwargs={'id': self.encrypt_id(invoice.id)}))
    except Exception as e:
      logger.error("Error in getInvoicePaymentStatus: %s", e)
      messages.error(request, str(e))
      return redirect(reverse('invoice_link_copy', kwargs={'id': self.encrypt_id(invoice.id)}))
