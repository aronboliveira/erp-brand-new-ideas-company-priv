import time
import datetime
import logging
import inspect
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from ....configs.messages_templates import get_lacking_field_message
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.http import HttpRequest, HttpResponse
from django.shortcuts import redirect, render
from django.utils.decorators import method_decorator
from django.views.decorators.http import require_http_methods
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class EmployeeAttendanceController(Controller):

    @method_decorator(login_required)
    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            from ....Models.companies.branch import Branch
            from ....Models.companies.department import Department
            from ....Models.individuals.employee_attendance import EmployeeAttendance

            user = request.user
            if not user.has_perm('manage attendance'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)

            branch_qs = Branch.objects.filter(created_by=user.creator_id)
            branch = {'': 'Select Branch', **{b.id: b.name for b in branch_qs}}

            dept_qs = Department.objects.filter(created_by=user.creator_id)
            department = {'': 'Select Department', **{d.id: d.name for d in dept_qs}}

            if user.type not in ['client', 'company']:
                emp_id = getattr(user, 'employee', None).id if getattr(user, 'employee', None) else 0
                att_qs = EmployeeAttendance.objects.filter(employee_id=emp_id)
                if request.GET.get('type') == 'monthly' and request.GET.get('month'):
                    dt = datetime.datetime.strptime(request.GET.get('month'), '%Y-%m-%d')
                    start_date = dt.replace(day=1).date()
                    end_date = dt.replace(day=28).date()
                    att_qs = att_qs.filter(date__range=[start_date, end_date])
                elif request.GET.get('type') == 'daily' and request.GET.get('date'):
                    att_qs = att_qs.filter(date=request.GET.get('date'))
                else:
                    today = datetime.date.today()
                    start_date = today.replace(day=1)
                    end_date = today.replace(day=28)
                    att_qs = att_qs.filter(date__range=[start_date, end_date])
                EmployeeAttendance = att_qs.all()
            else:
                from ....Models.individuals.employee import Employee
                emp_qs = Employee.objects.filter(created_by=user.creator_id)
                if request.GET.get('branch'):
                    emp_qs = emp_qs.filter(branch_id=request.GET.get('branch'))
                if request.GET.get('department'):
                    emp_qs = emp_qs.filter(department_id=request.GET.get('department'))
                emp_ids = list(emp_qs.values_list('id', flat=True))
                from ....Models.individuals.employee_attendance import EmployeeAttendance
                att_qs = EmployeeAttendance.objects.filter(employee_id__in=emp_ids)
                if request.GET.get('type') == 'monthly' and request.GET.get('month'):
                    dt = datetime.datetime.strptime(request.GET.get('month'), '%Y-%m-%d')
                    start_date = dt.replace(day=1).date()
                    end_date = dt.replace(day=28).date()
                    att_qs = att_qs.filter(date__range=[start_date, end_date])
                elif request.GET.get('type') == 'daily' and request.GET.get('date'):
                    att_qs = att_qs.filter(date=request.GET.get('date'))
                else:
                    today = datetime.date.today()
                    start_date = today.replace(day=1)
                    end_date = today.replace(day=28)
                    att_qs = att_qs.filter(date__range=[start_date, end_date])
                EmployeeAttendance = att_qs.all()

            return render(request, 'attendance/index.html', {
                'EmployeeAttendance': EmployeeAttendance,
                'branch': branch,
                'department': department
            })

        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            from ....Models.individuals.user import User
            if not request.user.has_perm('create attendance'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)

            employees = User.objects.filter(
                created_by=request.user.creator_id,
                type="employee"
            )
            employees = {e.id: e.name for e in employees}
            return render(request, 'attendance/create.html', {'employees': employees})

        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def store(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            from ....Models.individuals.employee_attendance import EmployeeAttendance
            from ....Models.utils.utility import Utility

            user = request.user
            if not user.has_perm('create attendance'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)

            req = request.POST
            for field in ['employee_id', 'date', 'clock_in', 'clock_out']:
                if not req.get(field):
                    messages.error(request, get_lacking_field_message(field, CN))
                    return redirect(get_redirect_url(request))

            start_time = Utility.get_val_by_name('company_start_time')
            end_time   = Utility.get_val_by_name('company_end_time')
            exists = EmployeeAttendance.objects.filter(
                employee_id=req.get('employee_id'),
                date=req.get('date'),
                clock_out='00:00:00'
            ).exists()
            if exists:
                messages.error(request, "Employee Attendance Already Created.")
                return redirect('EmployeeAttendance.index')

            date_str   = datetime.date.today().strftime("%Y-%m-%d")
            totalLate  = int(time.mktime(time.strptime(req.get('clock_in'), "%H:%M:%S"))) - int(
                            time.mktime(time.strptime(date_str + " " + start_time, "%Y-%m-%d %H:%M:%S"))
                         )
            late       = time.strftime('%H:%M:%S', time.gmtime(totalLate))
            totalEarly = int(time.mktime(time.strptime(date_str + " " + end_time, "%Y-%m-%d %H:%M:%S"))) - int(
                            time.mktime(time.strptime(req.get('clock_out'), "%H:%M:%S"))
                         )
            earlyLeaving = time.strftime('%H:%M:%S', time.gmtime(totalEarly))
            overtime     = time.strftime(
                '%H:%M:%S',
                time.gmtime(
                    int(time.mktime(time.strptime(req.get('clock_out'), "%H:%M:%S"))) -
                    int(time.mktime(time.strptime(date_str + " " + end_time, "%Y-%m-%d %H:%M:%S")))
                )
            ) if int(time.mktime(time.strptime(req.get('clock_out'), "%H:%M:%S"))) > int(
                     time.mktime(time.strptime(date_str + " " + end_time, "%Y-%m-%d %H:%M:%S"))
                 ) else '00:00:00'

            att = EmployeeAttendance(
                employee_id=req.get('employee_id'),
                date=req.get('date'),
                status='Present',
                clock_in=req.get('clock_in') + ':00',
                clock_out=req.get('clock_out') + ':00',
                late=late,
                early_leaving=earlyLeaving,
                overtime=overtime,
                total_rest='00:00:00',
                created_by=user.creator_id
            )
            att.save()
            messages.success(request, "Employee attendance successfully created.")
            return redirect('EmployeeAttendance.index')

        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def show(cls, request: HttpRequest) -> HttpResponse:
        return redirect('EmployeeAttendance.index')

    @method_decorator(login_required)
    @classmethod
    def edit(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            from ....Models.individuals.employee import Employee
            from ....Models.individuals.employee_attendance import EmployeeAttendance

            if not request.user.has_perm('edit attendance'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)

            attendance = EmployeeAttendance.objects.filter(id=id).first()
            employees = {e.id: e.name for e in Employee.objects.filter(created_by=request.user.creator_id)}
            return render(request, 'attendance/edit.html', {
                'EmployeeAttendance': attendance,
                'employees': employees
            })

        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
 
    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def update(cls, request: HttpRequest, id: int) -> HttpResponse:
        import datetime, time, inspect
        cn = cls.__name__; mn = inspect.currentframe().f_code.co_name
        try:
            from ....Models.individuals.employee_attendance import EmployeeAttendance
            from ....Models.utils.utility import Utility

            user = request.user
            if user.type in ['company', 'HR']:
                attendance_record = EmployeeAttendance.objects.filter(
                    employee_id=request.POST.get('employee_id'),
                    date=request.POST.get('date')
                ).first()
                start_time = Utility.get_val_by_name('company_start_time')
                end_time = Utility.get_val_by_name('company_end_time')
                clock_in_val = request.POST.get('clock_in')
                clock_out_val = request.POST.get('clock_out')

                total_late = (
                    int(time.mktime(time.strptime(clock_in_val, "%H:%M:%S")))
                    - int(time.mktime(time.strptime(start_time, "%H:%M:%S")))
                )
                late_time = time.strftime('%H:%M:%S', time.gmtime(total_late))

                total_early = (
                    int(time.mktime(time.strptime(end_time, "%H:%M:%S")))
                    - int(time.mktime(time.strptime(clock_out_val, "%H:%M:%S")))
                )
                early_leaving_time = time.strftime('%H:%M:%S', time.gmtime(total_early))

                overtime_val = (
                    time.strftime('%H:%M:%S', time.gmtime(
                        int(time.mktime(time.strptime(clock_out_val, "%H:%M:%S")))
                        - int(time.mktime(time.strptime(end_time, "%H:%M:%S")))
                    )) if int(time.mktime(time.strptime(clock_out_val, "%H:%M:%S"))) >
                       int(time.mktime(time.strptime(end_time, "%H:%M:%S"))) else '00:00:00'
                )

                if attendance_record and str(attendance_record.date) == datetime.date.today().strftime("%Y-%m-%d"):
                    attendance_record.late = late_time
                    attendance_record.early_leaving = (
                        early_leaving_time if early_leaving_time > '00:00:00' else '00:00:00'
                    )
                    attendance_record.overtime = overtime_val
                    attendance_record.clock_in = clock_in_val
                    attendance_record.clock_out = clock_out_val
                    attendance_record.save()
                    messages.success(request, "Employee attendance successfully updated.")
                    return redirect('EmployeeAttendance.index')
                messages.error(request, "you can only update current day attendance.")
                return redirect('EmployeeAttendance.index')

            from ....Models.individuals.employee_attendance import EmployeeAttendance as ea_model
            # emp_id = getattr(request.user.employee, 'id', 0)
            today_str = datetime.date.today().strftime("%Y-%m-%d")
            ea_model.objects.filter(id=id).first()  # ensure exists
            start_time = Utility.get_val_by_name('company_start_time')
            end_time = Utility.get_val_by_name('company_end_time')
            if request.user.type == 'Employee':
                current_time = datetime.datetime.now().strftime("%H:%M:%S")
                total_early_diff = (
                    int(time.mktime(time.strptime(end_time, "%H:%M:%S")))
                    - int(time.time())
                )
                early_leaving_time = time.strftime('%H:%M:%S', time.gmtime(total_early_diff))
                overtime_val = (
                    time.strftime('%H:%M:%S', time.gmtime(
                        int(time.time())
                        - int(time.mktime(time.strptime(end_time, "%H:%M:%S")))
                    )) if time.time() > int(time.mktime(time.strptime(end_time, "%H:%M:%S"))) else '00:00:00'
                )
                ea_model.objects.filter(id=id).update(
                    clock_out=current_time,
                    early_leaving=early_leaving_time,
                    overtime=overtime_val
                )
                messages.success(request, "Employee successfully clock Out.")
                return redirect('hrm.dashboard')

            current_time = datetime.datetime.now().strftime("%H:%M:%S")
            total_late_diff = abs(
                int(time.mktime(time.strptime(current_time, "%H:%M:%S")))
                - int(time.mktime(time.strptime(today_str + " " + start_time, "%Y-%m-%d %H:%M:%S")))
            )
            late_time = time.strftime('%H:%M:%S', time.gmtime(total_late_diff))

            total_early = (
                int(time.mktime(time.strptime(today_str + " " + end_time, "%Y-%m-%d %H:%M:%S")))
                - int(time.mktime(time.strptime(current_time, "%H:%M:%S")))
            )
            early_leaving_time = time.strftime('%H:%M:%S', time.gmtime(total_early))

            overtime_val = (
                time.strftime('%H:%M:%S', time.gmtime(
                    int(time.mktime(time.strptime(current_time, "%H:%M:%S")))
                    - int(time.mktime(time.strptime(today_str + " " + end_time, "%Y-%m-%d %H:%M:%S")))
                )) if int(time.mktime(time.strptime(current_time, "%H:%M:%S"))) >
                   int(time.mktime(time.strptime(today_str + " " + end_time, "%Y-%m-%d %H:%M:%S"))) else '00:00:00'
            )

            attendance_obj = ea_model.objects.get(id=id)
            attendance_obj.clock_out = current_time
            attendance_obj.late = late_time
            attendance_obj.early_leaving = early_leaving_time
            attendance_obj.overtime = overtime_val
            attendance_obj.total_rest = '00:00:00'
            attendance_obj.save()
            messages.success(request, "Employee attendance successfully updated.")
            return redirect(get_redirect_url(request))

        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{cn}::{mn}', logger=logger)


    @method_decorator(login_required)
    @classmethod
    def destroy(cls, request: HttpRequest, id: int) -> HttpResponse:
        import inspect
        cn = cls.__name__; mn = inspect.currentframe().f_code.co_name
        try:
            from ....Models.individuals.employee_attendance import EmployeeAttendance
            if not request.user.has_perm('delete attendance'):
                return default_permission_denial(request, err=None, ref=f'{cn}::{mn}', logger=logger)
            record = EmployeeAttendance.objects.filter(id=id).first()
            if record:
                record.delete()
                messages.success(request, "Attendance successfully deleted.")
            else:
                messages.error(request, "Attendance record not found.")
            return redirect('EmployeeAttendance.index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{cn}::{mn}', logger=logger)


    @classmethod
    def attendance(cls, request: HttpRequest) -> HttpResponse:
        import datetime, time, inspect
        cn = cls.__name__; mn = inspect.currentframe().f_code.co_name
        try:
            from ....Models.individuals.employee_attendance import EmployeeAttendance
            from ....Models.utils.utility import Utility
            from ....Models.configs.ip_restrict import IpRestrict

            settings_obj = Utility.settings()
            if settings_obj.get('ip_restrict') == 'on':
                user_ip = request.META.get('REMOTE_ADDR')
                blocked = IpRestrict.objects.filter(
                    created_by=request.user.creator_id,
                    ip__in=[user_ip]
                ).first()
                if blocked:
                    return default_permission_denial(request, err=None, ref=f'{cn}::{mn}', logger=logger)

            emp_id = getattr(request.user.employee, 'id', 0)
            start_time = Utility.get_val_by_name('company_start_time')
            end_time = Utility.get_val_by_name('company_end_time')
            pending = EmployeeAttendance.objects.order_by('-id').filter(
                employee_id=emp_id, clock_out='00:00:00'
            ).first()
            if pending:
                pending.clock_out = end_time
                pending.save()

            today_str = datetime.date.today().strftime("%Y-%m-%d")
            current_time = datetime.datetime.now().strftime("%H:%M:%S")
            late_diff = abs(
                int(time.time())
                - int(time.mktime(time.strptime(today_str + " " + start_time, "%Y-%m-%d %H:%M:%S")))
            )
            late_time = time.strftime('%H:%M:%S', time.gmtime(late_diff))

            if not EmployeeAttendance.objects.filter(employee_id=request.user.id).exists():
                new_record = EmployeeAttendance(
                    employee_id=emp_id,
                    date=today_str,
                    status='Present',
                    clock_in=current_time,
                    clock_out='00:00:00',
                    late=late_time,
                    early_leaving='00:00:00',
                    overtime='00:00:00',
                    total_rest='00:00:00',
                    created_by=request.user.id
                )
            else:
                new_record = EmployeeAttendance(
                    employee_id=emp_id,
                    date=today_str,
                    status='Present',
                    clock_in=current_time,
                    clock_out='00:00:00',
                    late=late_time,
                    early_leaving='00:00:00',
                    overtime='00:00:00',
                    total_rest='00:00:00',
                    created_by=request.user.id
                )
            new_record.save()
            messages.success(request, "Employee Successfully Clock In.")
            return redirect(get_redirect_url(request))

        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{cn}::{mn}', logger=logger)


    @classmethod
    def bulk_attendance(cls, request: HttpRequest) -> HttpResponse:
        import inspect
        cn = cls.__name__; mn = inspect.currentframe().f_code.co_name
        try:
            from ....Models.companies.branch import Branch
            from ....Models.companies.department import Department
            from ....Models.individuals.employee import Employee

            user = request.user
            if not user.has_perm('create attendance'):
                return default_permission_denial(request, err=None, ref=f'{cn}::{mn}', logger=logger)

            branch_qs = Branch.objects.filter(created_by=user.creator_id)
            branch_map = {'': 'Select Branch', **{b.id: b.name for b in branch_qs}}
            dept_qs = Department.objects.filter(created_by=user.creator_id)
            department_map = {'': 'Select Department', **{d.id: d.name for d in dept_qs}}

            if request.GET.get('branch') and request.GET.get('department'):
                employee_qs = Employee.objects.filter(
                    created_by=user.creator_id,
                    branch_id=request.GET.get('branch'),
                    department_id=request.GET.get('department')
                )
            else:
                employee_qs = Employee.objects.filter(
                    created_by=user.creator_id,
                    branch_id=1, department_id=1
                )
            return render(request, 'attendance/bulk.html', {
                'employees': employee_qs,
                'branch': branch_map,
                'department': department_map
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{cn}::{mn}', logger=logger)


    @classmethod
    def bulk_attendance_data(cls, request: HttpRequest) -> HttpResponse:
        import time, inspect
        cn = cls.__name__; mn = inspect.currentframe().f_code.co_name
        try:
            from ....Models.individuals.employee_attendance import EmployeeAttendance
            from ....Models.utils.utility import Utility

            user = request.user
            if not user.has_perm('create attendance'):
                return default_permission_denial(request, err=None, ref=f'{cn}::{mn}', logger=logger)

            if request.POST.get('branch') and request.POST.get('department'):
                start_time = Utility.get_val_by_name('company_start_time')
                end_time = Utility.get_val_by_name('company_end_time')
                date_val = request.POST.get('date')
                employee_ids = request.POST.getlist('employee_id')
                if not employee_ids:
                    messages.error(request, "Employee not found.")
                    return redirect(get_redirect_url(request))

                for emp in employee_ids:
                    present_key = f"present-{emp}"
                    in_key = f"in-{emp}"
                    out_key = f"out-{emp}"
                    if getattr(request, present_key, None) == 'on':
                        clock_in_val = time.strftime(
                            "%H:%M:%S",
                            time.localtime(time.mktime(time.strptime(getattr(request, in_key), "%H:%M:%S")))
                        )
                        clock_out_val = time.strftime(
                            "%H:%M:%S",
                            time.localtime(time.mktime(time.strptime(getattr(request, out_key), "%H:%M:%S")))
                        )
                        total_late = (
                            int(time.mktime(time.strptime(clock_in_val, "%H:%M:%S")))
                            - int(time.mktime(time.strptime(start_time, "%H:%M:%S")))
                        )
                        late_time = time.strftime('%H:%M:%S', time.gmtime(total_late))

                        total_early = (
                            int(time.mktime(time.strptime(end_time, "%H:%M:%S")))
                            - int(time.mktime(time.strptime(clock_out_val, "%H:%M:%S")))
                        )
                        early_leaving_time = time.strftime('%H:%M:%S', time.gmtime(total_early))

                        overtime_val = (
                            time.strftime('%H:%M:%S', time.gmtime(
                                int(time.mktime(time.strptime(clock_out_val, "%H:%M:%S")))
                                - int(time.mktime(time.strptime(end_time, "%H:%M:%S")))
                            )) if int(time.mktime(time.strptime(clock_out_val, "%H:%M:%S"))) >
                               int(time.mktime(time.strptime(end_time, "%H:%M:%S"))) else '00:00:00'
                        )

                        record = EmployeeAttendance.objects.filter(
                            employee_id=emp, date=date_val
                        ).first()
                        rec = record if record else EmployeeAttendance(
                            employee_id=emp, created_by=user.creator_id
                        )
                        rec.date = date_val
                        rec.status = 'Present'
                        rec.clock_in = clock_in_val
                        rec.clock_out = clock_out_val
                        rec.late = late_time
                        rec.early_leaving = (
                            early_leaving_time if early_leaving_time > '00:00:00' else '00:00:00'
                        )
                        rec.overtime = overtime_val
                        rec.total_rest = '00:00:00'
                        rec.save()
                    else:
                        record = EmployeeAttendance.objects.filter(
                            employee_id=emp, date=date_val
                        ).first()
                        rec = record if record else EmployeeAttendance(
                            employee_id=emp, created_by=user.creator_id
                        )
                        rec.status = 'Leave'
                        rec.date = date_val
                        rec.clock_in = '00:00:00'
                        rec.clock_out = '00:00:00'
                        rec.late = '00:00:00'
                        rec.early_leaving = '00:00:00'
                        rec.overtime = '00:00:00'
                        rec.total_rest = '00:00:00'
                        rec.save()

                messages.success(request, "Employee attendance successfully created.")
                return redirect(get_redirect_url(request))

            messages.error(request, "Branch & department field required.")
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{cn}::{mn}', logger=logger)


    @classmethod
    def import_file(cls, request: HttpRequest) -> HttpResponse:
        # import inspect
        # cn = cls.__name__
        # mn = inspect.currentframe().f_code.co_name
        return render(request, 'attendance/import.html')


    @classmethod
    def import_data(cls, request: HttpRequest) -> HttpResponse:
        cn = cls.__name__
        mn = inspect.currentframe().f_code.co_name
        try:
            from ....Imports.attendance_import import AttendanceImport
            from ....Models.individuals.employee import Employee
            from ....Models.individuals.employee_attendance import EmployeeAttendance
            from ....Models.utils.utility import Utility

            file_obj = request.FILES.get('file')
            if not file_obj:
                messages.error(request, "File is required.")
                return redirect(get_redirect_url(request))

            attendance_rows = AttendanceImport().to_array(file_obj)[0]
            email_data = []
            total_attendance = len(attendance_rows) - 1
            error_array = []
            start_time = Utility.get_val_by_name('company_start_time')
            end_time = Utility.get_val_by_name('company_end_time')

            for idx, row in enumerate(attendance_rows):
                if idx == 0:
                    continue
                email = row[0]
                if row and Employee.objects.filter(email=email, created_by=request.user.creator_id).exists():
                    employee_obj = Employee.objects.get(email=email, created_by=request.user.creator_id)
                    clock_in_val, clock_out_val = row[2], row[3]
                    total_late = (
                        int(time.mktime(time.strptime(clock_in_val, "%H:%M:%S")))
                        - int(time.mktime(time.strptime(start_time, "%H:%M:%S")))
                    )
                    late_time = time.strftime('%H:%M:%S', time.gmtime(total_late))

                    total_early = (
                        int(time.mktime(time.strptime(end_time, "%H:%M:%S")))
                        - int(time.mktime(time.strptime(clock_out_val, "%H:%M:%S")))
                    )
                    early_leaving_time = time.strftime('%H:%M:%S', time.gmtime(total_early))

                    overtime_val = (
                        time.strftime('%H:%M:%S', time.gmtime(
                            int(time.mktime(time.strptime(clock_out_val, "%H:%M:%S")))
                            - int(time.mktime(time.strptime(end_time, "%H:%M:%S")))
                        )) if int(time.mktime(time.strptime(clock_out_val, "%H:%M:%S"))) >
                           int(time.mktime(time.strptime(end_time, "%H:%M:%S"))) else '00:00:00'
                    )

                    record = EmployeeAttendance.objects.filter(
                        employee_id=employee_obj.id, date=row[1]
                    ).first()
                    if record:
                        record.late = late_time
                        record.early_leaving = (
                            early_leaving_time if early_leaving_time > '00:00:00' else '00:00:00'
                        )
                        record.overtime = overtime_val
                        record.clock_in = clock_in_val
                        record.clock_out = clock_out_val
                        record.save()
                    else:
                        EmployeeAttendance.objects.create(
                            employee_id=employee_obj.id,
                            date=row[1],
                            status='Present' if clock_in_val else 'Leave',
                            late=late_time,
                            early_leaving=early_leaving_time if early_leaving_time > '00:00:00' else '00:00:00',
                            overtime=overtime_val,
                            clock_in=clock_in_val,
                            clock_out=clock_out_val,
                            created_by=request.user.id
                        )
                else:
                    email_data.append(email)

            if email_data:
                messages.info(request, "This record is not import. <br>" + " And ".join(email_data))
            elif error_array:
                messages.error(request, f"{len(error_array)} Record imported fail out of {total_attendance} record")
            else:
                messages.success(request, "Record successfully imported")

            return redirect(get_redirect_url(request))

        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{cn}::{mn}', logger=logger)
