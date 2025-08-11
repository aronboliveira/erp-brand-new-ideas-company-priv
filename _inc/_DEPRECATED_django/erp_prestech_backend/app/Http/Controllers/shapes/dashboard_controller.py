import inspect
import logging
from django.contrib.auth.decorators import login_required
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import redirect, render
from django.utils.decorators import method_decorator
from ....Models.utils.utility import Utility
from ....Models.bills.revenue import Revenue
from ....Models.bills.payment import Payment
from ....Models.bills.bill import Bill
from ....Models.bills.tax import Tax
from ....Models.bills.invoice import Invoice
from ....Models.products.product_service_category import ProductServiceCategory
from ....Models.products.product_service_unit import ProductServiceUnit
from ....Models.companies.bank_account import BankAccount
from ....Models.planning.plan import Plan
from ....Models.planning.goal import Goal
from ....Models.planning.project_task import ProjectTask
from ....Models.planning.project import Project
from ....Models.bills.expense import Expense
from ....Models.shapes.timesheet import Timesheet
from ....Models.info.announcement import Announcement
from ....Models.activity.meeting import Meeting
from ....Models.activity.event import Event
from ....Models.activity.pos import Pos
from ....Models.activity.purchase import Purchase
from ....Models.activity.lead import Lead
from ....Models.activity.lead_stage import LeadStage
from ....Models.activity.stage import Stage
from ....Models.activity.deal import Deal
from ....Models.planning.contract import Contract
from ....Models.individuals.user import User
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_undefined_exception
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class DashboardController(Controller):
    CN = 'DashboardController'
    MFN = staticmethod(lambda: inspect.currentframe().f_back.f_code.co_name)

    @method_decorator(login_required)
    @classmethod
    def account_dashboard_index(cls, request: HttpRequest) -> HttpResponse:
        try:
            if not request.user.is_authenticated:
                if not Utility.storage_installed():
                    return redirect('install')
                admin_settings = Utility.settings()
                if admin_settings.get('display_landing_page') == 'on':
                    return render(request, 'landingpage/layouts/landingpage.html', {'adminSettings': admin_settings})
                return redirect('login')

            if request.user.type.lower() in ['super admin', 'client']:
                return redirect('client_dashboard_view')

            if not request.user.has_perm('crm.show_account_dashboard'):
                return cls.project_dashboard_index(request)

            creator_id = request.user.creator_id
            now = Utility.now()
            data = {
                'latestIncome': Revenue.recent(creator_id),
                'latestExpense': Payment.recent(creator_id),
                'currentYear': now.year,
                **cls._category_data(creator_id, 'income'),
                **cls._category_data(creator_id, 'expense'),
                'incExpBarChartData': request.user.getincExpBarChartData(),
                'incExpLineChartData': request.user.getIncExpLineChartDate(),
                'currentMonth': now.strftime("%b"),
                'constant': {
                    k: cls._model_count(m, creator_id)
                    for k, m in [
                        ('taxes', Tax),
                        ('category', ProductServiceCategory),
                        ('units', ProductServiceUnit),
                        ('bankAccount', BankAccount),
                    ]
                },
                'bankAccountDetail': BankAccount.filter_by(creator_id),
                'recentInvoice': Invoice.recent(creator_id),
                'weeklyInvoice': request.user.weeklyInvoice(),
                'monthlyInvoice': request.user.monthlyInvoice(),
                'recentBill': Bill.recent(creator_id),
                'weeklyBill': request.user.weeklyBill(),
                'monthlyBill': request.user.monthlyBill(),
                'goals': Goal.objects.filter(created_by=creator_id, is_display=1),
                'users': User.objects.get(pk=creator_id),
                'plan': Plan.objects.get(pk=request.user.show_dashboard()),
            }
            data['storage_limit'] = cls._compute_storage(data['users'], data['plan'])
            return render(request, 'dashboard/account-dashboard.html', data)
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f"{cls.CN}::{cls.MFN()}", logger=logger
            )

    @classmethod
    def _category_data(cls, creator_id, cat_type):
        qs = ProductServiceCategory.objects.filter(created_by=creator_id, type=cat_type)
        colors = ['#' + c.color for c in qs]
        names = [c.name for c in qs]
        amounts = [
            c.incomeCategoryRevenueAmount() if cat_type == 'income'
            else c.expenseCategoryAmount()
            for c in qs
        ]
        prefix = 'income' if cat_type == 'income' else 'expense'
        return {
            f"{prefix}CategoryColor": colors,
            f"{prefix}Category": names,
            f"{prefix}CatAmount": amounts
        }

    @classmethod
    def _model_count(cls, model, creator_id):
        return model.objects.filter(created_by=creator_id).count()

    @classmethod
    def _compute_storage(cls, user, plan):
        return (user.storage_limit / plan.storage_limit * 100) if plan.storage_limit > 0 else 0

    @method_decorator(login_required)
    @classmethod
    def project_dashboard_index(cls, request: HttpRequest) -> HttpResponse:
        try:
            if not request.user.has_perm('crm.show_project_dashboard'):
                return cls.account_dashboard_index(request)

            user = request.user
            if user.type.lower() == 'admin':
                return render(request, 'admin/dashboard.html')

            project_ids = list(user.projects().values_list('project_id', flat=True))
            tasks = ProjectTask.filter_by(project_ids)
            expenses = Expense.filter_by(project_ids)
            seven = Utility.getLastSevenDays()

            home = {
                'total_project': cls._pct_dict(len(project_ids), len(project_ids)),
                'total_task': cls._pct_dict(tasks.count(), tasks.complete_for(user.id)),
                'total_expense': cls._pct_dict(
                    expenses.count(),
                    Utility.sum_expense(expenses, project_ids)
                ),
                'total_user': user.contacts.count(),
                **cls._seven_day_data(seven, project_ids)
            }
            home['project_status']  = cls._status_overview(Project, user.projects(), 'project_status')
            home['due_project']     = user.projects().due(5)
            home['due_tasks']       = tasks.due(5)
            home['last_tasks']      = ProjectTask.last(5, project_ids)

            return render(request, 'dashboard/project-dashboard.html', {'home_data': home})
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f"{cls.CN}::{cls.MFN()}", logger=logger
            ) 
    
    @classmethod
    def _pct_dict(cls, total, complete):
        pct = Utility.getPercentage(complete, total) if total else 0
        return {'total': total, 'percentage': pct}

    @classmethod
    def _seven_day_data(cls, days, projects):
        to = {}
        ts = {}
        for date_str, label in days.items():
            to[label] = ProjectTask.count_complete_on(date_str, projects)
            ts[label] = Utility.calculateTimesheetHours(
                Timesheet.filter_by_date(date_str, projects)
            ).replace(':', '.')
        return {'task_overview': to, 'timesheet_logged': ts}

    @classmethod
    def _status_overview(cls, model, qs, status_field):
        total = qs.count()
        return {
            k: {'total': c, 'percentage': Utility.getPercentage(c, total)}
            for k, v in getattr(model, status_field).items()
            for c in [qs.filter(status__icontains=k).count()]
        }

    @method_decorator(login_required)
    @classmethod
    def hrm_dashboard_index(cls, request: HttpRequest) -> HttpResponse:
        from ....Models.individuals.employee import Employee
        try:
            if not request.user.has_perm('crm.show_hrm_dashboard'):
                return cls.account_dashboard_index(request)

            user = request.user
            t = user.type.lower()
            if t not in ['client', 'company']:
                emp = Employee.for_user(user.id)
                arr_events = Event.calendar_for(emp.id)
                context = {
                    'arrEvents': arr_events,
                    'announcements': Announcement.for_employee(emp.id),
                    'employees': Employee.all(),
                    'meetings': Meeting.for_employee(emp.id),
                    'employeeAttendance': emp.today_attendance(),
                    'officeTime': Utility.office_hours(),
                }
                return render(request, 'dashboard/dashboard.html', context)

            if t == 'super admin':
                data = user.super_admin_stats()
                data['chartData'] = cls.get_order_chart({'duration': 'week'})
                return render(request, 'dashboard/super_admin.html', data)

            # company/client HRM
            arr_events = Event.for_creator(request.user.creator_id)
            context = user.company_hrm_context(arr_events)
            return render(request, 'dashboard/dashboard.html', context)
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f"{cls.CN}::{cls.MFN()}", logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def crm_dashboard_index(cls, request: HttpRequest) -> HttpResponse:
        try:
            if not request.user.has_perm('crm.show_crm_dashboard'):
                return cls.account_dashboard_index(request)

            user = request.user
            if user.type.lower() == 'admin':
                return render(request, 'admin/dashboard.html')

            creator_id = user.creator_id
            crm_data = {
                'total_leads': Lead.count(creator_id),
                'total_deals': Deal.count(creator_id),
                'total_contracts': Contract.count(creator_id),
                'lead_status': LeadStage.status_overview(creator_id),
                'deal_status': Stage.status_overview(creator_id),
                'latestContract': Contract.recent(creator_id),
            }
            return render(request, 'dashboard/crm-dashboard.html', {'crm_data': crm_data})
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f"{cls.CN}::{cls.MFN()}", logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def pos_dashboard_index(cls, request: HttpRequest) -> HttpResponse:
        try:
            if not request.user.has_perm('crm.show_pos_dashboard'):
                return cls.account_dashboard_index(request)

            user = request.user
            if user.type.lower() == 'admin':
                return render(request, 'admin/dashboard.html')

            data = {
                'monthlyPosAmount': Pos.totalPosAmount(True),
                'totalPosAmount': Pos.totalPosAmount(),
                'monthlyPurchaseAmount': Purchase.totalPurchaseAmount(True),
                'totalPurchaseAmount': Purchase.totalPurchaseAmount(),
                'purchasesArray': Purchase.getPurchaseReportChart(),
                'posesArray': Pos.getPosReportChart(),
            }
            return render(request, 'dashboard/pos-dashboard.html', data)
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f"{cls.CN}::{cls.MFN()}", logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def filter_view(cls, request: HttpRequest) -> JsonResponse:
        try:
            users = User.objects.exclude(id=request.user.id)
            if request.is_ajax():
                kw = request.GET.get('keyword', '')
                if kw:
                    users = users.filter(name__istartswith=kw)
                html = render(request, 'dashboard/view.html', {'users': users}).content.decode('utf-8')
                return JsonResponse({'success': True, 'html': html})
            return JsonResponse({'success': False, 'html': ''})
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.CN}::{cls.MFN()}', logger=logger,
                json={'error': f"{cls.CN}.{cls.MFN()}: {e}"}, status=500
            )

    @method_decorator(login_required)
    @classmethod
    def client_view(cls, request: HttpRequest) -> HttpResponse:
        import datetime
        try:
            user = request.user
            t = user.type.lower()
            if t == 'super admin':
                data = {
                    'user': user._super_admin_stats(),
                    'chartData': cls.get_order_chart({'duration': 'week'})
                }
                return render(request, 'dashboard/super_admin.html', data)

            if t == 'client':
                now = datetime.now()
                arr_temp = {'date': [], 'invoice': [], 'payment': []}
                for i in range(7):
                    d = now - datetime.timedelta(days=i)
                    arr_temp['date'].append(d.strftime('%a'))
                    arr_temp['invoice'].append(10)
                    arr_temp['payment'].append(20)
                chart_data = arr_temp.copy()

                calendar_tasks = user._build_calendar_tasks()
                arr_count = user._build_arr_count()
                project_ctx = user._build_project_context()
                users_data = {
                    'staff': User.objects.filter(created_by=user.creator_id).count(),
                    'user': User.objects.filter(created_by=user.creator_id).exclude(type__iexact='client').count(),
                    'client': User.objects.filter(created_by=user.creator_id, type__iexact='client').count(),
                }
                context = {
                    'calendarTasks': calendar_tasks,
                    'arrCount': arr_count,
                    'chartData': chart_data,
                    'project': project_ctx,
                    'invoice': {'total_invoice': 5},
                    'top_tasks': user.created_top_due_task(),
                    'users': users_data,
                    'transdate': now.strftime('%Y-%m-%d'),
                    'currentYear': now.year,
                }
                return render(request, 'dashboard/clientView.html', context)

            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.CN}::{cls.MFN()}', logger=logger,
                json={'error': f"{cls.CN}.{cls.MFN()}: {e}"}, status=500
            )

    @staticmethod
    def get_order_chart(params: dict) -> dict:
        try:
            import time
            from ....Models.activity.order import Order
            from datetime import datetime

            arr = {}
            if params.get('duration') == 'week':
                base = time.time() - 14 * 86400
                for i in range(14):
                    ts = base + i * 86400
                    dt = datetime.fromtimestamp(ts)
                    arr[dt.strftime('%Y-%m-%d')] = dt.strftime('%d-%b')

            labels, data = [], []
            for d, lbl in arr.items():
                labels.append(lbl)
                data.append(Order.objects.filter(created_at__date=d).count())
            return {'label': labels, 'data': data}
        except Exception:
            return {}

    @method_decorator(login_required)
    @classmethod
    def stop_tracker(cls, request: HttpRequest) -> JsonResponse:
        from ....Models.planning.time_tracker import TimeTracker
        try:
            if request.user.is_client():
                return JsonResponse({'error': "Permission denied."}, status=403)

            name = request.POST.get('name', '')
            pid = request.POST.get('project_id', '')
            if not name or len(name) > 120:
                return JsonResponse({'error': "Name is required and must be 120 characters or less."}, status=400)
            if not pid.isdigit():
                return JsonResponse({'error': "Project ID is required and must be an integer."}, status=400)

            tracker = TimeTracker.objects.filter(created_by=request.user.id, is_active=1).first()
            if not tracker:
                return JsonResponse({'error': "Tracker not found."}, status=404)

            from datetime import datetime as _dt
            end = request.POST.get('end_time') or _dt.now().strftime('%Y-%m-%d %H:%M:%S')
            tracker.end_time = end
            tracker.is_active = 0
            tracker.total_time = Utility.diffance_to_time(tracker.start_time, tracker.end_time)
            tracker.save()
            return JsonResponse({'success': "Add Time successfully."})
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.CN}::{cls.MFN()}', logger=logger,
                json={'error': f"{cls.CN}.{cls.MFN()}: {e}"}, status=500
            )