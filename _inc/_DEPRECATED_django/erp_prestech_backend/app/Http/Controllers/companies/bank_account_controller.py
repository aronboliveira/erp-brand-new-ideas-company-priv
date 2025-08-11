import logging
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.db import transaction
from django.forms import ModelForm
from django.utils.decorators import method_decorator
from django.shortcuts import get_object_or_404, redirect, render
from django.views.decorators.http import require_http_methods
from django.http import HttpRequest, HttpResponse
from .._helpers.http import get_redirect_url
from django.core.exceptions import PermissionDenied
from ....configs.messages_templates import get_exception_class_message

logger = logging.getLogger(__name__)

class BankAccountController(ModelForm):
  class Meta:
    model = None  # placeholder, set dynamically in __init__
  def __init__(self, *args: any, **kwargs: any) -> None:
    from ....Models.companies.bank_account import BankAccount
    self._meta.model = BankAccount
    super().__init__(*args, **kwargs)

@method_decorator(login_required)
def index(request: HttpRequest) -> HttpResponse:
  try:
    if not request.user.has_perm('create_bank_account'):
      messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))
      return redirect(get_redirect_url(request))
    from ....Models.companies.bank_account import BankAccount
    accounts = BankAccount.objects.filter(created_by=request.user.creator_id)
    return render(request, 'bank_account/index.html', {'accounts': accounts})
  except Exception as e:
    logger.error("Failed in index: %s", e)
    messages.error(request, "An error occurred.")
    return redirect(get_redirect_url(request))

@method_decorator(login_required)
def create(request: HttpRequest) -> HttpResponse:
  try:
    if not request.user.has_perm('create_bank_account'):
      messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))
      return redirect(get_redirect_url(request))
    from ....Models.charts.chart_of_account import ChartOfAccount
    from ....Models.shapes.custom_field import CustomField
    chart_accounts = ChartOfAccount.objects.filter(created_by=request.user.creator_id)
    chart_account_choices = [('', 'Select Account')] + [(acc.id, f"{acc.code} - {acc.name}") for acc in chart_accounts]
    custom_fields = CustomField.get_data('account', request.user.creator_id)
    return render(request, 'bank_account/create.html', {
      'chart_accounts': chart_account_choices,
      'custom_fields': custom_fields
    })
  except Exception as e:
    logger.error("Failed in create: %s", e)
    messages.error(request, "An error occurred.")
    return redirect(get_redirect_url(request))

@method_decorator(login_required)
@require_http_methods(["POST"])
def store(request: HttpRequest) -> HttpResponse:
  try:
    if not request.user.has_perm('create_bank_account'):
      messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))
      return redirect(get_redirect_url(request))
    from ....Models.companies.bank_account import BankAccount
    from ....Models.shapes.custom_field import CustomField
    class _DynamicBankAccountForm(BankAccountController):
      class Meta(BankAccountController.Meta):
        model = BankAccount
    with transaction.atomic():
      form = _DynamicBankAccountForm(request.POST)
      if not form.is_valid():
        messages.error(request, next(iter(form.errors.values()))[0])
        return redirect('bank_account_index')
      account = form.save(commit=False)
      account.created_by = request.user.creator_id
      account.save()
      CustomField.save_data(account, request.POST.get('customField'))
    messages.success(request, "Account successfully created.")
    return redirect('bank_account_index')
  except Exception as e:
    logger.error("Failed in store: %s", e)
    messages.error(request, "An error occurred.")
    return redirect(get_redirect_url(request))

@method_decorator(login_required)
def edit(request: HttpRequest, id: int) -> HttpResponse:
  try:
    from ....Models.companies.bank_account import BankAccount
    from ....Models.charts.chart_of_account import ChartOfAccount
    from ....Models.shapes.custom_field import CustomField
    account = get_object_or_404(BankAccount, id=id)
    if not request.user.has_perm('edit_bank_account') or account.created_by != request.user.creator_id:
      messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))
      return redirect(get_redirect_url(request))
    chart_accounts = ChartOfAccount.objects.filter(created_by=request.user.creator_id)
    chart_account_choices = [('', 'Select Account')] + [(acc.id, f"{acc.code} - {acc.name}") for acc in chart_accounts]
    custom_fields = CustomField.get_fields('account', request.user.creator_id)
    account.custom_fields = CustomField.get_data(account, 'account')
    return render(request, 'bank_account/edit.html', {
      'bank_account': account,
      'chart_accounts': chart_account_choices,
      'custom_fields': custom_fields
    })
  except Exception as e:
    logger.error("Failed in edit: %s", e)
    messages.error(request, "An error occurred.")
    return redirect(get_redirect_url(request))

@method_decorator(login_required)
@require_http_methods(["POST"])
def update(request: HttpRequest, id: int) -> HttpResponse:
  try:
    from ....Models.companies.bank_account import BankAccount
    from ....Models.shapes.custom_field import CustomField
    account = get_object_or_404(BankAccount, id=id)
    if not request.user.has_perm('edit_bank_account') or account.created_by != request.user.creator_id:
      messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))
      return redirect(get_redirect_url(request))
    class _DynamicBankAccountForm(BankAccountController):
      class Meta(BankAccountController.Meta):
        model = BankAccount
    with transaction.atomic():
      form = _DynamicBankAccountForm(request.POST, instance=account)
      if not form.is_valid():
        messages.error(request, next(iter(form.errors.values()))[0])
        return redirect('bank_account_index')
      account = form.save(commit=False)
      account.created_by = request.user.creator_id
      account.save()
      CustomField.save_data(account, request.POST.get('customField'))
    messages.success(request, "Account successfully updated.")
    return redirect('bank_account_index')
  except Exception as e:
    logger.error("Failed in update: %s", e)
    messages.error(request, "An error occurred.")
    return redirect(get_redirect_url(request))

@method_decorator(login_required)
def destroy(request: HttpRequest, id: int) -> HttpResponse:
  try:
    from ....Models.companies.bank_account import BankAccount
    from ....Models.bills.revenue import Revenue
    from ....Models.bills.invoice_payment import InvoicePayment
    from ....Models.bills.payment import Payment
    from ....Models.bills.bill_payment import BillPayment
    from ....Models.bills.transaction import Transaction
    account = get_object_or_404(BankAccount, id=id)
    if not request.user.has_perm('delete_bank_account') or account.created_by != request.user.creator_id:
      messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))
      return redirect(get_redirect_url(request))
    has_related = any([
      Revenue.objects.filter(account_id=account.id).exists(),
      InvoicePayment.objects.filter(account_id=account.id).exists(),
      Transaction.objects.filter(account_id=account.id).exists(),
      Payment.objects.filter(account_id=account.id).exists(),
      BillPayment.objects.exists()
    ])
    if has_related:
      messages.error(request, "Please delete related record of this account.")
      return redirect('bank_account_index')
    account.delete()
    messages.success(request, "Account successfully deleted.")
    return redirect('bank_account_index')
  except Exception as e:
    logger.error("Failed in destroy: %s", e)
    messages.error(request, "An error occurred.")
    return redirect(get_redirect_url(request))
