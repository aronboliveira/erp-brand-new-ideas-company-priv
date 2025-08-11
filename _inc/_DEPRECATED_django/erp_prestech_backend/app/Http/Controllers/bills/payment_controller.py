import inspect
import logging
import time
import uuid
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse
from django.shortcuts import redirect, render
from erp_prestech_backend.app.Models.bills.bill_account import BillAccount
from erp_prestech_backend.app.Models.bills.payment import Payment
from erp_prestech_backend.app.Models.bills.transaction import Transaction
from erp_prestech_backend.app.Models.charts.chart_of_account import ChartOfAccount
from erp_prestech_backend.app.Models.companies.bank_account import BankAccount
from erp_prestech_backend.app.Models.companies.vendor import Vendor
from erp_prestech_backend.app.Models.products.product_service_category import ProductServiceCategory
from erp_prestech_backend.app.Models.utils.utility import Utility
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class PaymentController(Controller):
  def __init__(self, **kwargs):
    super().__init__(**kwargs)
    self.middleware(['auth','XSS','revalidate'])

  def index(self, request: HttpRequest) -> HttpResponse:
    REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      self.authorize('manage payment')
      vendors = list(Vendor.objects.filter(created_by=request.user.creator_id()).values_list('id','name'))
      vendors.insert(0,('','Select Vendor'))
      accounts = list(BankAccount.objects.filter(created_by=request.user.creator_id()).values_list('id','holder_name'))
      accounts.insert(0,('','Select Account'))
      categories = list(ProductServiceCategory.objects.filter(created_by=request.user.creator_id()).values_list('id','name'))
      categories.insert(0,('','Select Category'))
      query = Payment.objects.filter(created_by=request.user.creator_id())
      date_param = request.GET.get('date','')
      if ' to ' in date_param:
        start,end = date_param.split(' to ')
        query = query.filter(date__gte=start,date__lte=end)
      elif date_param:
        query = query.filter(date__range=[date_param,date_param])
      if request.GET.get('vendor'):
        query = query.filter(vendor_id=request.GET.get('vendor'))
      if request.GET.get('account'):
        query = query.filter(account_id=request.GET.get('account'))
      if request.GET.get('category'):
        query = query.filter(category_id=request.GET.get('category'))
      payments = list(query)
      return render(request,'payment/index.html',{
        'payments':payments,'vendors':vendors,
        'accounts':accounts,'categories':categories
      })
    except PermissionDenied as e:
      return default_permission_denial(
        request,err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request,err=e,
        ref=REF,
        logger=logger
      )

  def create(self, request: HttpRequest) -> HttpResponse:
    REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      self.authorize('create payment')
      vendors = list(Vendor.objects.filter(created_by=request.user.creator_id()).values_list('id','name'))
      vendors.insert(0,(None,'--'))
      categories = list(ProductServiceCategory.objects.filter(
        created_by=request.user.creator_id()
      ).exclude(type__in=['product & service','income']).values_list('id','name'))
      categories.insert(0,('','Select Category'))
      accounts = list(BankAccount.objects.filter(created_by=request.user.creator_id()).annotate(
        name=BankAccount.holder_name
      ).values_list('id','name'))
      chart_accounts = list(ChartOfAccount.objects.filter(
        created_by=request.user.creator_id()
      ).annotate(code_name=ChartOfAccount.code).values_list('id','code_name'))
      chart_accounts.insert(0,('','Select Account'))
      return render(request,'payment/create.html',{
        'vendors':vendors,'categories':categories,
        'accounts':accounts,'chart_accounts':chart_accounts
      })
    except PermissionDenied as e:
      return default_permission_denial(
        request,err=e,
        ref=REF,
        logger=logger,
        json={'error':'Permission denied.'}
      )
    except Exception as e:
      return default_undefined_exception(
        request,err=e,
        ref=REF,
        logger=logger
      )

  def store(self, request: HttpRequest) -> HttpResponse:
    REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      self.authorize('create payment')
      # TODO: validate request.POST fields more strictly
      payment_props = {}
      payment_props.payment_method = 0
      for k in ('date', 'amount', 'account_id', 'vendor_id', 'category_id',
                'reference'):
        payment_props[k] = request.POST.get(k)
      payment = Payment(**payment_props)
      if request.FILES.get('add_receipt'):
        size = request.FILES['add_receipt'].size
        res = Utility.update_storage_limit(request.user.creator_id(),size)
        if res != 1:
          messages.error(request,res)
          return redirect('payment_create')
        fname = f"{int(time.time())}_{request.FILES['add_receipt'].name}"
        payment.add_receipt = fname
        Utility.upload_file(request,'add_receipt',fname,'uploads/payment',[])
      payment.description = request.POST.get('description')
      payment.created_by = request.user.creator_id()
      payment.save()
      ba_props = {}
      for k, v in {'chart_account': ChartOfAccount.objects.get(id=request.POST.get('account_id')),
                   'price': payment.amount, 'description': payment.description,
                   'type': 'payment', 'ref_id': payment.id}.items():
        setattr(ba_props, k, v)
      acct = BillAccount(**ba_props)
      acct.save()
      cat = ProductServiceCategory.objects.get(id=request.POST.get('category_id'))
      payment.payment_id = payment.id
      payment.type = 'Payment'
      payment.category = cat.name
      payment.user_id = payment.vendor_id
      payment.user_type = 'Vendor'
      payment.account = request.POST.get('account_id')
      Transaction.add_transaction(payment)
      if payment.vendor_id:
        Utility.user_balance('vendor',payment.vendor_id,payment.amount,'debit')
      Utility.bank_account_balance(payment.account_id,payment.amount,'debit')
      return redirect('payment_index')
    except PermissionDenied as e:
      return default_permission_denial(
        request,err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request,err=e,
        ref=REF,
        logger=logger
      )

  def edit(self, request: HttpRequest, payment_id: uuid.UUID) -> HttpResponse:
    REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      self.authorize('edit payment')
      payment = Payment.objects.get(pk=payment_id)
      if payment.created_by != request.user.creator_id():
        raise PermissionDenied('Permission denied')
      vendors = list(Vendor.objects.filter(created_by=request.user.creator_id()).values_list('id','name'))
      vendors.insert(0,(None,'--'))
      categories = list(ProductServiceCategory.objects.filter(
        created_by=request.user.creator_id()
      ).exclude(type__in=['product & service','income']).values_list('id','name'))
      categories.insert(0,('','Select Category'))
      accounts = list(BankAccount.objects.filter(created_by=request.user.creator_id()).annotate(
        name=BankAccount.holder_name
      ).values_list('id','name'))
      chart_accounts = list(ChartOfAccount.objects.filter(
        created_by=request.user.creator_id()
      ).annotate(code_name=ChartOfAccount.code).values_list('id','code_name'))
      chart_accounts.insert(0,('','Select Account'))
      return render(request,'payment/edit.html',{
        'vendors':vendors,'categories':categories,
        'accounts':accounts,'payment':payment,
        'chart_accounts':chart_accounts
      })
    except PermissionDenied as e:
      return default_permission_denial(
        request,err=e,
        ref=REF,
        logger=logger,
        json={'error':'Permission denied.'}
      )
    except Exception as e:
      return default_undefined_exception(
        request,err=e,
        ref=REF,
        logger=logger
      )

  def update(self, request: HttpRequest, payment_id: uuid.UUID) -> HttpResponse:
    REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      self.authorize('edit payment')
      payment = Payment.objects.get(pk=payment_id)
      if payment.created_by != request.user.creator_id():
        raise PermissionDenied('Permission denied')
      # TODO: validate request.POST fields
      old_amt = payment.amount
      payment.date = request.POST.get('date')
      payment.amount = request.POST.get('amount')
      payment.account_id = request.POST.get('account_id')
      payment.vendor_id = request.POST.get('vendor_id')
      payment.category_id = request.POST.get('category_id')
      payment.payment_method = 0
      payment.reference = request.POST.get('reference')
      if request.FILES.get('add_receipt'):
        size = request.FILES['add_receipt'].size
        res = Utility.update_storage_limit(request.user.creator_id(),size)
        if res != 1:
          messages.error(request,res)
          return redirect('payment_edit',payment_id)
        fname = f"{int(time.time())}_{request.FILES['add_receipt'].name}"
        payment.add_receipt = fname
        Utility.upload_file(request,'add_receipt',fname,'uploads/payment',[])
      payment.description = request.POST.get('description')
      payment.save()
      cat = ProductServiceCategory.objects.get(id=request.POST.get('category_id'))
      payment.category = cat.name
      payment.payment_id = payment.id
      payment.type = 'Payment'
      payment.account = request.POST.get('account_id')
      Transaction.edit_transaction(payment)
      if payment.vendor_id:
        Utility.user_balance('vendor',payment.vendor_id,old_amt,'credit')
        Utility.user_balance('vendor',payment.vendor_id,payment.amount,'debit')
      Utility.bank_account_balance(payment.account_id,old_amt,'credit')
      Utility.bank_account_balance(payment.account_id,payment.amount,'debit')
      return redirect('payment_index')
    except PermissionDenied as e:
      return default_permission_denial(
        request,err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request,err=e,
        ref=REF,
        logger=logger
      )

  def destroy(self, request: HttpRequest, payment_id: uuid.UUID) -> HttpResponse:
    REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      self.authorize('delete payment')
      payment = Payment.objects.get(pk=payment_id)
      if payment.created_by != request.user.creator_id():
        raise PermissionDenied('Permission denied')
      if payment.add_receipt:
        Utility.change_storage_limit(request.user.creator_id(),
                                     f"/uploads/payment/{payment.add_receipt}")
      payment.delete()
      Transaction.destroy_transaction(payment_id,'Payment','Vendor')
      if payment.vendor_id:
        Utility.user_balance('vendor',payment.vendor_id,payment.amount,'credit')
      Utility.bank_account_balance(payment.account_id,payment.amount,'credit')
      return redirect('payment_index')
    except PermissionDenied as e:
      return default_permission_denial(
        request,err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request,err=e,
        ref=REF,
        logger=logger
      )
