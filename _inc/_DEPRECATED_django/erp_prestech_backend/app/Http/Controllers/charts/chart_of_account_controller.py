import calendar  
import datetime  
from django.contrib import messages  
from django.http import HttpRequest, HttpResponse, JsonResponse  
from django.shortcuts import get_object_or_404, redirect, render  
from ....Models.charts.chart_of_account import ChartOfAccount
from ....Models.charts.chart_of_account_sub_type import ChartOfAccountSubType
from ....Models.charts.chart_of_account_type import ChartOfAccountType
from .._traits.controller import Controller
from .._helpers.http import get_redirect_url
from django.core.exceptions import PermissionDenied
from ....configs.messages_templates import get_exception_class_message
import logging  
logger = logging.getLogger(__name__)  

class ChartOfAccountController(Controller):  
  def index(self, request: HttpRequest) -> HttpResponse:  
    try:  
      if request.user.has_perm('manage chart of account'):  
        start = request.GET.get('start_date') if request.GET.get('start_date') and request.GET.get('end_date') else datetime.date.today().replace(month=1, day=1).strftime('%Y-%m-%d')  
        end = request.GET.get('end_date') if request.GET.get('start_date') and request.GET.get('end_date') else (datetime.date.today() + datetime.timedelta(days=1)).strftime('%Y-%m-%d')  
        filter = {'startDateRange': start, 'endDateRange': end}  
        types = ChartOfAccountType.objects.filter(created_by=request.user.creator_id())  
        chartAccounts = {}  
        for t in types:  
          accounts = ChartOfAccount.objects.filter(type=t.id, created_by=request.user.creator_id()).select_related('subType')  
          chartAccounts[t.name] = accounts  
        logger.info("ChartOfAccountController.index: Loaded successfully")  
        return render(request, 'chartOfAccount/index.html', {'chartAccounts': chartAccounts, 'types': types, 'filter': filter})  
      else:  
        messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))  
        return redirect(get_redirect_url(request))  
    except Exception as e:  
      logger.exception(f"ChartOfAccountController.index failed: {e}")  
      messages.error(request, "An error occurred.")  
      return redirect(get_redirect_url(request))  
  
  def create(self, request: HttpRequest) -> HttpResponse:  
    try:  
      types_qs = ChartOfAccountType.objects.filter(created_by=request.user.creator_id()).values('id', 'name')  
      types_dict = {t['id']: t['name'] for t in types_qs}  
      types_dict = {0: 'Select Account Type', **types_dict}  
      logger.info("ChartOfAccountController.create: Rendered create view")  
      return render(request, 'chartOfAccount/create.html', {'types': types_dict})  
    except Exception as e:  
      logger.exception(f"ChartOfAccountController.create failed: {e}")  
      messages.error(request, "An error occurred.")  
      return redirect(get_redirect_url(request))  
  
  def store(self, request: HttpRequest) -> HttpResponse:  
    try:  
      if request.user.has_perm('create chart of account'):  
        name = request.POST.get('name')  
        type_val = request.POST.get('type')  
        if not name or not type_val:  
          messages.error(request, "Name and Account Type are required.")  
          return redirect(get_redirect_url(request))  
        account = ChartOfAccount()  
        account.name = name  
        account.code = request.POST.get('code')  
        account.type = type_val  
        account.sub_type = request.POST.get('sub_type')  
        account.description = request.POST.get('description')  
        account.is_enabled = 1 if request.POST.get('is_enabled') else 0  
        account.created_by = request.user.creator_id()  
        account.save()  
        messages.success(request, "Account successfully created.")  
        logger.info("ChartOfAccountController.store: Account created")  
        return redirect('chart-of-account_index')  
      else:  
        messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))  
        return redirect(get_redirect_url(request))  
    except Exception as e:  
      logger.exception(f"ChartOfAccountController.store failed: {e}")  
      messages.error(request, f"ChartOfAccountController.store: {e}")  
      return redirect(get_redirect_url(request))  
  
  def show(self, request: HttpRequest, id: int) -> HttpResponse:  
    try:  
      if request.user.has_perm('ledger report'):  
        start = request.GET.get('start_date') if request.GET.get('start_date') and request.GET.get('end_date') else datetime.date.today().replace(day=1).strftime('%Y-%m-%d')  
        last_day = calendar.monthrange(datetime.date.today().year, datetime.date.today().month)[1]  
        end = request.GET.get('end_date') if request.GET.get('start_date') and request.GET.get('end_date') else datetime.date.today().replace(day=last_day).strftime('%Y-%m-%d')  
        accounts_qs = ChartOfAccount.objects.filter(created_by=request.user.creator_id())  
        accounts_qs = accounts_qs.filter(created_at__gte=start, created_at__lte=end) if request.GET.get('start_date') and request.GET.get('end_date') else accounts_qs  
        accounts = {}  
        for acc in accounts_qs:  
          accounts[acc.id] = f"{acc.code} - {acc.name}"  
        accounts = {'': 'Select Account', **accounts}  
        account_id = request.GET.get('account')  
        account = ChartOfAccount.objects.filter(pk=account_id).first() if account_id else ChartOfAccount.objects.filter(pk=id).first()  
        filter = {'startDateRange': start, 'endDateRange': end}  
        logger.info("ChartOfAccountController.show: Rendered show view")  
        return render(request, 'chartOfAccount/show.html', {'filter': filter, 'account': account, 'accounts': accounts})  
      else:  
        messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))  
        return redirect(get_redirect_url(request))  
    except Exception as e:  
      logger.exception(f"ChartOfAccountController.show failed: {e}")  
      messages.error(request, "An error occurred.")  
      return redirect(get_redirect_url(request))  
  
  def edit(self, request: HttpRequest, id: int) -> HttpResponse:  
    try:  
      chartOfAccount = get_object_or_404(ChartOfAccount, pk=id)  
      types_qs = ChartOfAccountType.objects.all().values('id', 'name')  
      types_dict = {t['id']: t['name'] for t in types_qs}  
      types_dict = {0: 'Select Account Type', **types_dict}  
      logger.info("ChartOfAccountController.edit: Rendered edit view for ID %s", id)  
      return render(request, 'chartOfAccount/edit.html', {'chartOfAccount': chartOfAccount, 'types': types_dict})  
    except Exception as e:  
      logger.exception(f"ChartOfAccountController.edit failed: {e}")  
      messages.error(request, "An error occurred.")  
      return redirect(get_redirect_url(request))  
  
  def update(self, request: HttpRequest, id: int) -> HttpResponse:  
    try:  
      if request.user.has_perm('edit chart of account'):  
        chartOfAccount = get_object_or_404(ChartOfAccount, pk=id)  
        name = request.POST.get('name')  
        if not name:  
          messages.error(request, "Name is required.")  
          return redirect(get_redirect_url(request))  
        chartOfAccount.name = name  
        chartOfAccount.code = request.POST.get('code')  
        chartOfAccount.description = request.POST.get('description')  
        chartOfAccount.is_enabled = 1 if request.POST.get('is_enabled') else 0  
        chartOfAccount.save()  
        messages.success(request, "Account successfully updated.")  
        logger.info("ChartOfAccountController.update: Account ID %s updated", id)  
        return redirect('chart-of-account_index')  
      else:  
        return JsonResponse({'error': "Permission denied."}, status=401)  
    except Exception as e:  
      logger.exception(f"ChartOfAccountController.update failed: {e}")  
      messages.error(request, f"ChartOfAccountController.update: {e}")  
      return redirect(get_redirect_url(request))  
  
  def destroy(self, request: HttpRequest, id: int) -> HttpResponse:  
    try:  
      chartOfAccount = get_object_or_404(ChartOfAccount, pk=id)  
      if request.user.has_perm('delete chart of account'):  
        chartOfAccount.delete()  
        messages.success(request, "Account successfully deleted.")  
        logger.info("ChartOfAccountController.destroy: Account ID %s deleted", id)  
        return redirect('chart-of-account_index')  
      else:  
        messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))  
        return redirect(get_redirect_url(request))  
    except Exception as e:  
      logger.exception(f"ChartOfAccountController.destroy failed: {e}")  
      messages.error(request, "An error occurred.")  
      return redirect(get_redirect_url(request))  
  
  def get_subtype(self, request: HttpRequest) -> HttpResponse:  
    try:  
      type_val = request.POST.get('type')  
      types_qs = ChartOfAccountSubType.objects.filter(type=type_val).values('id', 'name')  
      types_dict = {t['id']: t['name'] for t in types_qs}  
      logger.info("ChartOfAccountController.get_subtype: Returned subtypes for type %s", type_val)  
      return JsonResponse(types_dict)  
    except Exception as e:  
      logger.exception(f"ChartOfAccountController.get_subtype failed: {e}")  
      return JsonResponse({'error': "An error occurred."}, status=500)  
