import datetime
import json
from django.contrib import messages
from django.core.signing import BadSignature, Signer
from django.db.models import Sum
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import get_object_or_404, redirect, render
from ....Models.bills.bill import Bill
from ....Models.bills.invoice import Invoice
from ....Models.bills.payment import Payment
from ....Models.bills.revenue import Revenue
from ....Models.bills.budget import Budget
from ....Models.products.product_service_category import ProductServiceCategory
from ....Models.utils.utility import Utility
from .._traits.controller import Controller
from .._helpers.http import get_redirect_url
from django.core.exceptions import PermissionDenied
from ....configs.messages_templates import get_exception_class_message

class BudgetController(Controller):

  def decrypt_id(self, signed_id: str) -> int:
    signer = Signer()
    try:
      return int(signer.unsign(signed_id))
    except BadSignature as e:
      print(f"Decryption failed: {e}")  # NOTE: add proper logging here if needed
      raise

  def index(self, request: HttpRequest) -> HttpResponse:
    if request.user.has_perm('manage budget plan'):
      budgets = Budget.objects.filter(created_by=request.user.creator_id())
      periods = Budget.PERIOD
      return render(request, 'budget/index.html', {'budgets': budgets, 'periods': periods})
    else:
      messages.error(request, get_exception_class_message(PermissionDenied, __class__.__name__))
      return redirect(get_redirect_url(request))

  def create(self, request: HttpRequest) -> HttpResponse:
    if request.user.has_perm('create budget plan'):
      periods = Budget.PERIOD
      monthList = self.yearMonth()
      quarterly_monthlist = ['Jan-Mar', 'Apr-Jun', 'Jul-Sep', 'Oct-Dec']
      half_yearly_monthlist = ['Jan-Jun', 'Jul-Dec']
      yearly_monthlist = ['Jan-Dec']
      yearList = self.yearList()
      incomeproduct = ProductServiceCategory.objects.filter(
        created_by=request.user.creator_id(), type='income'
      )
      expenseproduct = ProductServiceCategory.objects.filter(
        created_by=request.user.creator_id(), type='expense'
      )
      context = {
        'periods': periods,
        'monthList': monthList,
        'quarterly_monthlist': quarterly_monthlist,
        'half_yearly_monthlist': half_yearly_monthlist,
        'yearly_monthlist': yearly_monthlist,
        'yearList': yearList,
        'incomeproduct': incomeproduct,
        'expenseproduct': expenseproduct
      }
      return render(request, 'budget/create.html', context)
    else:
      return JsonResponse({'error': "Permission denied."}, status=401)

  def store(self, request: HttpRequest) -> HttpResponse:
    if request.user.has_perm('create budget plan'):
      if request.method == 'POST':
        name = request.POST.get('name')
        period = request.POST.get('period')
        if not name or not period:
          messages.error(request, "Validation error: Name and period are required.")
          return redirect(get_redirect_url(request))
        budget = Budget()
        budget.name = name
        budget.from_date = request.POST.get('year')
        budget.period = period
        budget.income_data = json.dumps(request.POST.get('income'))
        budget.expense_data = json.dumps(request.POST.get('expense'))
        budget.created_by = request.user.creator_id()
        budget.save()
        setting = Utility.settings(request.user.creator_id())
        budgetNotificationArr = {
          'budget_period': Budget.PERIOD.get(period, period),
          'budget_year': request.POST.get('year'),
          'budget_name': name
        }
        if setting.get('budget_notification') == 1:
          Utility.send_slack_msg('new_budget', budgetNotificationArr)
        if setting.get('telegram_budget_notification') == 1:
          Utility.send_telegram_msg('new_budget', budgetNotificationArr)
        module = 'New Budget'
        webhook = Utility.webhookSetting(module)
        if webhook:
          parameter = json.dumps({'id': budget.id, 'name': budget.name})
          status = Utility.WebhookCall(webhook['url'], parameter, webhook['method'])
          if status:
            messages.success(request, "Budget Plan successfully created.")
            return redirect('budget_index')
          else:
            messages.error(request, "Webhook call failed.")
            return redirect(get_redirect_url(request))
        messages.success(request, "Budget Plan successfully created.")
        return redirect('budget_index')
      else:
        messages.error(request, "Invalid request method.")
        return redirect(get_redirect_url(request))
    else:
      messages.error(request, get_exception_class_message(PermissionDenied, __class__.__name__))
      return redirect(get_redirect_url(request))

  def show(self, request: HttpRequest, ids: str) -> HttpResponse:
    if request.user.has_perm('view budget plan'):
      try:
        id_ = self.decrypt_id(ids)
      except Exception as e:
        messages.error(request, "Budget Not Found.")
        return redirect(get_redirect_url(request))
      budget = get_object_or_404(Budget, pk=id_)
      budget.income_data = json.loads(budget.income_data) if budget.income_data else {}
      budget_expense_data = json.loads(budget.expense_data) if budget.expense_data else {}
      budgetTotal = {}
      for arr in budget.income_data.values():
        for k, value in arr.items():
          budgetTotal[k] = budgetTotal.get(k, 0) + value
      budgetExpenseTotal = {}
      for arr in budget_expense_data.values():
        for k, value in arr.items():
          budgetExpenseTotal[k] = budgetExpenseTotal.get(k, 0) + value
      monthList = self.yearMonth()
      quarterly_monthlist = {'1-3': 'Jan-Mar', '4-6': 'Apr-Jun', '7-9': 'Jul-Sep', '10-12': 'Oct-Dec'}
      half_yearly_monthlist = {'1-6': 'Jan-Jun', '7-12': 'Jul-Dec'}
      yearly_monthlist = {'1-12': 'Jan-Dec'}
      yearList = self.yearList()
      year = budget.from_date if budget.from_date else datetime.datetime.now().year
      incomeproduct = ProductServiceCategory.objects.filter(
        created_by=request.user.creator_id(), type='income'
      )
      incomeArr = {}
      incomeTotalArr = {}
      for cat in incomeproduct:
        if budget.period == 'monthly':
          monthIncomeArr = {}
          for i in range(1, 13):
            revenuAmount = Revenue.objects.filter(
              created_by=request.user.creator_id(),
              category_id=cat.id,
              date__year=year,
              date__month=i
            ).aggregate(total=Sum('amount'))['total'] or 0
            revenuTotalAmount = Revenue.objects.filter(
              created_by=request.user.creator_id(),
              date__year=year,
              date__month=i
            ).aggregate(total=Sum('amount'))['total'] or 0
            invoices = Invoice.objects.filter(
              created_by=request.user.creator_id(),
              category_id=cat.id,
              send_date__year=year,
              send_date__month=i
            )
            invoiceAmount = sum([inv.get_total() for inv in invoices])
            invoicesTotal = Invoice.objects.filter(
              created_by=request.user.creator_id(),
              send_date__year=year,
              send_date__month=i
            )
            invoiceTotalAmount = sum([inv.get_total() for inv in invoicesTotal])
            monthName = datetime.date(year, i, 1).strftime('%B')
            monthIncomeArr[monthName] = invoiceAmount + revenuAmount
            incomeTotalArr[monthName] = invoiceTotalAmount + revenuTotalAmount
          incomeArr[cat.id] = monthIncomeArr
        elif budget.period in ['quarterly', 'half-yearly', 'yearly']:
          durations = quarterly_monthlist if budget.period == 'quarterly' else (
            yearly_monthlist if budget.period == 'yearly' else half_yearly_monthlist
          )
          monthIncomeArr = {}
          for monthnumber, monthName in durations.items():
            start, end = map(int, monthnumber.split('-'))
            revenuAmount = Revenue.objects.filter(
              created_by=request.user.creator_id(),
              category_id=cat.id,
              date__year=year,
              date__month__gte=start,
              date__month__lte=end
            ).aggregate(total=Sum('amount'))['total'] or 0
            revenuTotalAmount = Revenue.objects.filter(
              created_by=request.user.creator_id(),
              date__year=year,
              date__month__gte=start,
              date__month__lte=end
            ).aggregate(total=Sum('amount'))['total'] or 0
            invoices = Invoice.objects.filter(
              created_by=request.user.creator_id(),
              category_id=cat.id,
              send_date__year=year,
              send_date__month__gte=start,
              send_date__month__lte=end
            )
            invoiceAmount = sum([inv.get_total() for inv in invoices])
            invoicesTotal = Invoice.objects.filter(
              created_by=request.user.creator_id(),
              send_date__year=year,
              send_date__month__gte=start,
              send_date__month__lte=end
            )
            invoiceTotalAmount = sum([inv.get_total() for inv in invoicesTotal])
            monthIncomeArr[monthName] = invoiceAmount + revenuAmount
            incomeTotalArr[monthName] = invoiceTotalAmount + revenuTotalAmount
          incomeArr[cat.id] = monthIncomeArr
      expenseproduct = ProductServiceCategory.objects.filter(
        created_by=request.user.creator_id(), type='expense'
      )
      expenseArr = {}
      expenseTotalArr = {}
      for exp in expenseproduct:
        if budget.period == 'monthly':
          monthExpenseArr = {}
          for i in range(1, 13):
            paymentAmount = Payment.objects.filter(
              created_by=request.user.creator_id(),
              category_id=exp.id,
              date__year=year,
              date__month=i
            ).aggregate(total=Sum('amount'))['total'] or 0
            paymentTotalAmount = Payment.objects.filter(
              created_by=request.user.creator_id(),
              date__year=year,
              date__month=i
            ).aggregate(total=Sum('amount'))['total'] or 0
            bills = Bill.objects.filter(
              created_by=request.user.creator_id(),
              category_id=exp.id,
              send_date__year=year,
              send_date__month=i
            )
            billAmount = sum([bill.get_total() for bill in bills])
            billsTotal = Bill.objects.filter(
              created_by=request.user.creator_id(),
              send_date__year=year,
              send_date__month=i
            )
            billTotalAmount = sum([bill.get_total() for bill in billsTotal])
            monthName = datetime.date(year, i, 1).strftime('%B')
            monthExpenseArr[monthName] = billAmount + paymentAmount
            expenseTotalArr[monthName] = billTotalAmount + paymentTotalAmount
          expenseArr[exp.id] = monthExpenseArr
        elif budget.period in ['quarterly', 'half-yearly', 'yearly']:
          durations = quarterly_monthlist if budget.period == 'quarterly' else (
            yearly_monthlist if budget.period == 'yearly' else half_yearly_monthlist
          )
          monthExpenseArr = {}
          for monthnumber, monthName in durations.items():
            start, end = map(int, monthnumber.split('-'))
            paymentAmount = Payment.objects.filter(
              created_by=request.user.creator_id(),
              category_id=exp.id,
              date__year=year,
              date__month__gte=start,
              date__month__lte=end
            ).aggregate(total=Sum('amount'))['total'] or 0
            paymentTotalAmount = Payment.objects.filter(
              created_by=request.user.creator_id(),
              date__year=year,
              date__month__gte=start,
              date__month__lte=end
            ).aggregate(total=Sum('amount'))['total'] or 0
            bills = Bill.objects.filter(
              created_by=request.user.creator_id(),
              category_id=exp.id,
              send_date__year=year,
              send_date__month__gte=start,
              send_date__month__lte=end
            )
            billAmount = sum([bill.get_total() for bill in bills])
            billsTotal = Bill.objects.filter(
              created_by=request.user.creator_id(),
              send_date__year=year,
              send_date__month__gte=start,
              send_date__month__lte=end
            )
            billTotalAmount = sum([bill.get_total() for bill in billsTotal])
            monthExpenseArr[monthName] = billAmount + paymentAmount
            expenseTotalArr[monthName] = billTotalAmount + paymentTotalAmount
          expenseArr[exp.id] = monthExpenseArr
      budgetprofit = {}
      for key in set(list(budgetTotal.keys()) + list(budgetExpenseTotal.keys())):
        budgetprofit[key] = budgetTotal.get(key, 0) - budgetExpenseTotal.get(key, 0)
      actualprofit = {}
      for key in set(list(incomeTotalArr.keys()) + list(expenseTotalArr.keys())):
        actualprofit[key] = incomeTotalArr.get(key, 0) - expenseTotalArr.get(key, 0)
      data = {
        'monthList': monthList,
        'quarterly_monthlist': quarterly_monthlist,
        'half_yearly_monthlist': half_yearly_monthlist,
        'yearly_monthlist': yearly_monthlist,
        'yearList': yearList,
        'currentYear': year,
        'budgetprofit': budgetprofit,
        'actualprofit': actualprofit
      }
      context = {
        'id': id_,
        'budget': budget,
        'incomeproduct': incomeproduct,
        'expenseproduct': expenseproduct,
        'incomeArr': incomeArr,
        'expenseArr': expenseArr,
        'incomeTotalArr': incomeTotalArr,
        'expenseTotalArr': expenseTotalArr,
        'budgetTotal': budgetTotal,
        'budgetExpenseTotal': budgetExpenseTotal
      }
      context.update(data)
      return render(request, 'budget/show.html', context)
    else:
      return JsonResponse({'error': "Permission denied."}, status=401)

  def edit(self, request: HttpRequest, ids: str) -> HttpResponse:
    if request.user.has_perm('edit budget plan'):
      try:
        id_ = self.decrypt_id(ids)
      except Exception as e:
        messages.error(request, "Budget Not Found.")
        return redirect(get_redirect_url(request))
      budget = get_object_or_404(Budget, pk=id_)
      budget.income_data = json.loads(budget.income_data) if budget.income_data else {}
      budget.expense_data = json.loads(budget.expense_data) if budget.expense_data else {}
      periods = Budget.PERIOD
      monthList = self.yearMonth()
      quarterly_monthlist = ['Jan-Mar', 'Apr-Jun', 'Jul-Sep', 'Oct-Dec']
      half_yearly_monthlist = ['Jan-Jun', 'Jul-Dec']
      yearly_monthlist = ['Jan-Dec']
      yearList = self.yearList()
      incomeproduct = ProductServiceCategory.objects.filter(
        created_by=request.user.creator_id(), type='income'
      )
      expenseproduct = ProductServiceCategory.objects.filter(
        created_by=request.user.creator_id(), type='expense'
      )
      context = {
        'periods': periods,
        'budget': budget,
        'monthList': monthList,
        'quarterly_monthlist': quarterly_monthlist,
        'half_yearly_monthlist': half_yearly_monthlist,
        'yearly_monthlist': yearly_monthlist,
        'yearList': yearList,
        'incomeproduct': incomeproduct,
        'expenseproduct': expenseproduct
      }
      return render(request, 'budget/edit.html', context)
    else:
      messages.error(request, get_exception_class_message(PermissionDenied, __class__.__name__))
      return redirect(get_redirect_url(request))

  def update(self, request: HttpRequest, ids: str) -> HttpResponse:
    if request.user.has_perm('edit budget plan'):
      try:
        id_ = self.decrypt_id(ids)
      except Exception as e:
        messages.error(request, "Budget Not Found.")
        return redirect(get_redirect_url(request))
      budget = get_object_or_404(Budget, pk=id_)
      if budget.created_by == request.user.creator_id():
        if request.method == 'POST':
          name = request.POST.get('name')
          period = request.POST.get('period')
          if not name or not period:
            messages.error(request, "Validation error: Name and period are required.")
            return redirect(get_redirect_url(request))
          budget.name = name
          budget.from_date = request.POST.get('year')
          budget.period = period
          budget.income_data = json.dumps(request.POST.get('income'))
          budget.expense_data = json.dumps(request.POST.get('expense'))
          budget.save()
          messages.success(request, "Budget Plan successfully updated.")
          return redirect('budget_index')
        else:
          messages.error(request, "Invalid request method.")
          return redirect(get_redirect_url(request))
      else:
        messages.error(request, get_exception_class_message(PermissionDenied, __class__.__name__))
        return redirect(get_redirect_url(request))
    else:
      return JsonResponse({'error': "Permission denied."}, status=401)

  def destroy(self, request: HttpRequest, ids: str) -> HttpResponse:
    if request.user.has_perm('delete budget plan'):
      try:
        id_ = self.decrypt_id(ids)
      except Exception as e:
        messages.error(request, "Budget Not Found.")
        return redirect(get_redirect_url(request))
      budget = get_object_or_404(Budget, pk=id_)
      if budget.created_by == request.user.creator_id():
        budget.delete()
        messages.success(request, "Budget Plan successfully deleted.")
        return redirect('budget_index')
      else:
        messages.error(request, get_exception_class_message(PermissionDenied, __class__.__name__))
        return redirect(get_redirect_url(request))
    else:
      messages.error(request, get_exception_class_message(PermissionDenied, __class__.__name__))
      return redirect(get_redirect_url(request))

  def yearMonth(self) -> list[str]:
    return [
      'January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'
    ]

  def yearList(self) -> dict[int, int]:
    ending_year = datetime.datetime.now().year
    starting_year = ending_year - 5
    years: dict[int, int] = {}
    for year in range(ending_year, starting_year - 1, -1):
      years[year] = year
    return years
