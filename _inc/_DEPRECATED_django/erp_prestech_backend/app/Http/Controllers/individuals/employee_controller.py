import os
import time
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core.files.storage import default_storage
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.utils.decorators import method_decorator
from django.views.decorators.http import require_http_methods
from ....Imports.employees_import import EmployeesImport
from ....Models.companies.branch import Branch
from ....Models.companies.department import Department
from ....Models.individuals.employee import Employee
from ....Models.individuals.designation import Designation
from ....Models.individuals.user import User
from ....Models.planning.plan import Plan
from ....Models.planning.termination import Termination
from ....Models.shapes.document import Document
from ....Models.ssr.joining_letter import JoiningLetter
from ....Models.ssr.experience_certificate import ExperienceCertificate
from ....Models.ssr.noc import Noc
from ....Models.utils.utility import Utility
from .._traits.controller import Controller
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
import logging

logger = logging.getLogger(__name__)

class EmployeeController(Controller):

    @method_decorator(login_required)
    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        import inspect
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('crm.manage_employee'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            employees = (
                Employee.objects.filter(user=request.user)
                if getattr(request.user, 'type', None) == 'Employee'
                else Employee.objects.filter(created_by=request.user.creator_id)
            )
            return render(request, 'employee/index.html', {'employees': employees})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        import inspect
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('crm.create_employee'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            creator_id = request.user.creator_id
            company_settings = Utility.settings(creator_id)
            documents = Document.objects.filter(created_by=creator_id)
            branches = {b['id']: b['name'] for b in Branch.objects.filter(created_by=creator_id).values('id','name')}
            departments = {d['id']: d['name'] for d in Department.objects.filter(created_by=creator_id).values('id','name')}
            designations = {d['id']: d['name'] for d in Designation.objects.filter(created_by=creator_id).values('id','name')}
            employees = User.objects.filter(created_by=creator_id)
            employees_id = cls.employee_number(request)
            context = {
                'employees': employees,
                'employeesId': employees_id,
                'departments': departments,
                'designations': designations,
                'documents': documents,
                'branches': branches,
                'company_settings': company_settings,
            }
            return render(request, 'employee/create.html', context)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def employee_number(cls, request: HttpRequest) -> int:
        import inspect
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            creator_id = request.user.creator_id
            latest = Employee.objects.filter(created_by=creator_id).order_by('-id').first()
            return (latest.employee_id + 1) if latest else 1
        except Exception as e:
            print(f"[{CN}.{MN}] Failed to generate employee number: {e.__class__.__name__}: {e}")
            return 1

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def store(cls, request: HttpRequest) -> HttpResponse:
        import inspect
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('crm.create_employee'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            required_fields = ['name','dob','phone','address','email','password','department_id','designation_id']
            for field in required_fields:
                if not request.POST.get(field):
                    messages.error(request, f"{field} is required.")
                    return redirect(get_redirect_url(request))
            creator_id = request.user.creator_id
            obj_user = User.objects.get(pk=creator_id)
            total_employee = obj_user.countEmployees()
            plan = Plan.objects.get(pk=obj_user.plan)
            if total_employee >= plan.max_users and plan.max_users != -1:
                messages.error(request, "Your employee limit is over, Please upgrade plan.")
                return redirect(get_redirect_url(request))
            from django.contrib.auth.hashers import make_password
            user = User.objects.create(
                name=request.POST['name'],
                email=request.POST['email'],
                password=make_password(request.POST['password']),
                type='employee',
                lang='en',
                created_by=creator_id
            )
            user.assign_role('Employee')
            document_keys = request.POST.getlist('document')
            document_implode = ','.join(document_keys) if document_keys else None
            emp_number = cls.employee_number(request)
            employee = Employee.objects.create(
                user_id=user.id,
                name=request.POST['name'],
                dob=request.POST['dob'],
                gender=request.POST.get('gender'),
                phone=request.POST['phone'],
                address=request.POST['address'],
                email=request.POST['email'],
                password=make_password(request.POST['password']),
                employee_id=emp_number,
                branch_id=request.POST.get('branch_id'),
                department_id=request.POST['department_id'],
                designation_id=request.POST['designation_id'],
                company_doj=request.POST.get('company_doj'),
                documents=document_implode,
                account_holder_name=request.POST.get('account_holder_name'),
                account_number=request.POST.get('account_number'),
                bank_name=request.POST.get('bank_name'),
                bank_identifier_code=request.POST.get('bank_identifier_code'),
                branch_location=request.POST.get('branch_location'),
                tax_payer_id=request.POST.get('tax_payer_id'),
                created_by=creator_id
            )
            if request.FILES.getlist('document'):
                from ....Models.shapes.employee_document import EmployeeDocument
                for key, file in enumerate(request.FILES.getlist('document')):
                    try:
                        orig = file.name
                        base, ext = os.path.splitext(orig)
                        fname = f"{base}_{int(time.time())}{ext}"
                        dir_path = 'uploads/document/'
                        full = os.path.join(dir_path, orig)
                        if default_storage.exists(full):
                            default_storage.delete(full)
                        os.makedirs(dir_path, mode=0o777, exist_ok=True)
                        default_storage.save(os.path.join(dir_path, fname), file)
                        EmployeeDocument.objects.create(
                            employee_id=employee.employee_id,
                            document_id=key,
                            document_value=fname,
                            created_by=creator_id
                        )
                    except Exception as fe:
                        print(f"[{CN}.{MN}] Failed saving document {key}: {fe.__class__.__name__}: {fe}")
            settings_obj = Utility.settings(creator_id)
            if settings_obj.get('new_user') == 1:
                resp = Utility.send_email_template('new_user', {user.id: user.email}, {'email': user.email, 'password': user.password})
                msg = "Employee successfully created." + (
                    f"<br><span class='text-danger'>{resp.get('error')}</span>"
                    if resp and not resp.get('is_success') and resp.get('error') else ""
                )
                messages.success(request, msg)
                return redirect('employee_index')
            messages.success(request, "Employee successfully created.")
            return redirect('employee_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def edit(cls, request: HttpRequest, encrypted_id: str) -> HttpResponse:
        import inspect
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            from django.core import signing
            try:
                emp_id = signing.loads(encrypted_id)
            except Exception:
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            if not request.user.has_perm('crm.edit_employee'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            creator_id = request.user.creator_id
            documents = Document.objects.filter(created_by=creator_id)
            branches = {'': 'Select Branch', **{b['id']: b['name'] for b in Branch.objects.filter(created_by=creator_id).values('id','name')}}
            departments = {d['id']: d['name'] for d in Department.objects.filter(created_by=creator_id).values('id','name')}
            designations = {d['id']: d['name'] for d in Designation.objects.filter(created_by=creator_id).values('id','name')}
            employee = get_object_or_404(Employee, pk=emp_id)
            employees_id = request.user.employeeIdFormat(employee.employee_id)
            dept_data = {d['id']: d['name'] for d in Department.objects.filter(created_by=creator_id, branch_id=employee.branch_id).values('id','name')}
            context = {
                'employee': employee,
                'employeesId': employees_id,
                'branches': branches,
                'departments': departments,
                'designations': designations,
                'documents': documents,
                'departmentData': dept_data,
            }
            return render(request, 'employee/edit.html', context)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def update(cls, request: HttpRequest, emp_id: int) -> HttpResponse:
        import inspect
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('crm.edit_employee'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            employee = get_object_or_404(Employee, pk=emp_id)
            for field in ['name','dob','gender','phone','address']:
                if not request.POST.get(field):
                    messages.error(request, f"{field} is required.")
                    return redirect(get_redirect_url(request))
            if request.FILES.getlist('document'):
                from ....Models.shapes.employee_document import EmployeeDocument
                for key, file in enumerate(request.FILES.getlist('document')):
                    try:
                        orig = file.name
                        base, ext = os.path.splitext(orig)
                        fname = f"{base}_{int(time.time())}{ext}"
                        dir_path = 'uploads/document/'
                        os.makedirs(dir_path, mode=0o777, exist_ok=True)
                        default_storage.save(os.path.join(dir_path, fname), file)
                        emp_doc = EmployeeDocument.objects.filter(employee_id=employee.employee_id, document_id=key).first()
                        if emp_doc:
                            emp_doc.document_value = fname
                            emp_doc.save()
                        else:
                            EmployeeDocument.objects.create(
                                employee_id=employee.employee_id,
                                document_id=key,
                                document_value=fname,
                                created_by=request.user.creator_id
                            )
                    except Exception as fe:
                        print(f"[{CN}.{MN}] Failed saving document {key}: {fe.__class__.__name__}: {fe}")
            data = request.POST.dict()
            for k, v in data.items():
                setattr(employee, k, v)
            employee.save()
            usr = User.objects.filter(id=employee.user_id).first()
            if usr:
                usr.name = employee.name
                usr.email = employee.email
                usr.save()
            if request.POST.get('salary'):
                return redirect('setsalary_index')
            if getattr(request.user, 'type','').lower() == 'employee':
                from django.core.signing import dumps
                return redirect('employee_show', encrypted_id=dumps(employee.id))
            return redirect('employee_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def destroy(cls, request: HttpRequest, emp_id: int) -> HttpResponse:
        import inspect
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('crm.delete_employee'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            employee = get_object_or_404(Employee, pk=emp_id)
            user_obj = User.objects.filter(id=employee.user_id).first()
            from ....Models.shapes.employee_document import EmployeeDocument
            emp_docs = EmployeeDocument.objects.filter(employee_id=employee.employee_id)
            employee.delete()
            if user_obj:
                user_obj.delete()
            dir_path = os.path.join(os.getcwd(), 'uploads/document/')
            for doc in emp_docs:
                doc.delete()
                if doc.document_value:
                    fp = os.path.join(dir_path, doc.document_value)
                    if os.path.exists(fp):
                        os.unlink(fp)
            messages.success(request, "Employee successfully deleted.")
            return redirect('employee_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def show(cls, request: HttpRequest, encrypted_id: str) -> HttpResponse:
        import inspect
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            from django.core import signing
            try:
                emp_id = signing.loads(encrypted_id)
            except Exception:
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            if not request.user.has_perm('crm.view_employee'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            creator_id = request.user.creator_id
            documents = Document.objects.filter(created_by=creator_id)
            branches = {b['id']: b['name'] for b in Branch.objects.filter(created_by=creator_id).values('id','name')}
            departments = {d['id']: d['name'] for d in Department.objects.filter(created_by=creator_id).values('id','name')}
            designations = {d['id']: d['name'] for d in Designation.objects.filter(created_by=creator_id).values('id','name')}
            employee = get_object_or_404(Employee, pk=emp_id)
            employees_id = request.user.employeeIdFormat(employee.employee_id)
            context = {
                'employee': employee,
                'employeesId': employees_id,
                'branches': branches,
                'departments': departments,
                'designations': designations,
                'documents': documents,
            }
            return render(request, 'employee/show.html', context)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def json(cls, request: HttpRequest) -> JsonResponse:
        import inspect
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            dept_id = request.GET.get('department_id')
            designations_qs = Designation.objects.filter(department_id=dept_id).values('id','name')
            return JsonResponse({d['id']: d['name'] for d in designations_qs})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger,
                                               json=f"{CN}.{MN}: Error", status=500)

    @method_decorator(login_required)
    @classmethod
    def profile(cls, request: HttpRequest) -> HttpResponse:
        import inspect
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            creator_id = request.user.creator_id
            qs = Employee.objects.filter(created_by=creator_id)
            branch = request.GET.get('branch')
            department = request.GET.get('department')
            designation = request.GET.get('designation')
            if branch:
                qs = qs.filter(branch_id=branch)
            if department:
                qs = qs.filter(department_id=department)
            if designation:
                qs = qs.filter(designation_id=designation)
            employees = qs.all()
            branches = {'': 'All', **{b['id']: b['name'] for b in Branch.objects.filter(created_by=creator_id).values('id','name')}}
            departments = {'': 'All', **{d['id']: d['name'] for d in Department.objects.filter(created_by=creator_id).values('id','name')}}
            designations = {'': 'All', **{d['id']: d['name'] for d in Designation.objects.filter(created_by=creator_id).values('id','name')}}
            context = {
                'employees': employees,
                'branches': branches,
                'departments': departments,
                'designations': designations,
            }
            return render(request, 'employee/profile.html', context)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def profile_show(cls, request: HttpRequest, encrypted_id: str) -> HttpResponse:
        import inspect
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            from django.core import signing
            try:
                emp_id = signing.loads(encrypted_id)
            except Exception:
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            creator_id = request.user.creator_id
            documents = Document.objects.filter(created_by=creator_id)
            branches = {b['id']: b['name'] for b in Branch.objects.filter(created_by=creator_id).values('id','name')}
            departments = {d['id']: d['name'] for d in Department.objects.filter(created_by=creator_id).values('id','name')}
            designations = {d['id']: d['name'] for d in Designation.objects.filter(created_by=creator_id).values('id','name')}
            employee = get_object_or_404(Employee, pk=emp_id)
            employees_id = request.user.employeeIdFormat(employee.employee_id)
            context = {
                'employee': employee,
                'employeesId': employees_id,
                'branches': branches,
                'departments': departments,
                'designations': designations,
                'documents': documents,
            }
            return render(request, 'employee/show.html', context)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
          
    @method_decorator(login_required)
    @classmethod
    def last_login(cls, request: HttpRequest) -> HttpResponse:
        import inspect
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            creator_id = request.user.creator_id
            users = User.objects.filter(created_by=creator_id)
            return render(request, 'employee/last_login.html', {'users': users})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def employee_json(cls, request: HttpRequest) -> JsonResponse:
        import inspect
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            branch = request.GET.get('branch')
            qs = Employee.objects.filter(branch_id=branch).values('id', 'name')
            return JsonResponse({e['id']: e['name'] for e in qs})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger, status=500)

    @method_decorator(login_required)
    @classmethod
    def get_department(cls, request: HttpRequest) -> JsonResponse:
        import inspect
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            creator_id = request.user.creator_id
            branch_id = request.GET.get('branch_id')
            qs = Department.objects.filter(created_by=creator_id) if branch_id == '0' else Department.objects.filter(created_by=creator_id, branch_id=branch_id)
            qs = qs.values('id', 'name')
            return JsonResponse({d['id']: d['name'] for d in qs})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger, status=500)

    @method_decorator(login_required)
    @classmethod
    def joining_letter_pdf(cls, request: HttpRequest, emp_id: int) -> HttpResponse:
        import inspect, os, time
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            user = request.user
            lang = user.currentLanguage()
            jl = JoiningLetter.objects.filter(lang=lang, created_by=user.creator_id).first()
            date_str = time.strftime('%Y-%m-%d')
            emp = get_object_or_404(Employee, pk=emp_id)
            settings = Utility.settings(user.creator_id)
            secs = time.mktime(time.strptime(settings.get('company_start_time', '00:00'), "%H:%M")) - time.mktime(time.strptime("00:00", "%H:%M"))
            total = time.strftime("%H:%M", time.localtime(time.mktime(time.strptime(settings.get('company_end_time', '00:00'), "%H:%M")) - secs))
            ctx = {
                'date': user.dateFormat(date_str),
                'app_name': os.getenv('APP_NAME', 'MyApp'),
                'employee_name': emp.name,
                'address': emp.address or '',
                'designation': emp.designation.name if emp.designation else '',
                'start_date': emp.company_doj or '',
                'branch': emp.Branch.name if hasattr(emp, 'Branch') and emp.Branch else '',
                'start_time': settings.get('company_start_time', ''),
                'end_time': settings.get('company_end_time', ''),
                'total_hours': total,
            }
            jl.content = JoiningLetter.replaceVariable(jl.content, ctx)
            return render(request, 'employee/template/joining_letter_pdf.html', {'joiningletter': jl, 'employees': emp})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def joining_letter_doc(cls, request: HttpRequest, emp_id: int) -> HttpResponse:
        import inspect, os, time
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            user = request.user
            lang = user.currentLanguage()
            jl = JoiningLetter.objects.filter(lang=lang, created_by=user.creator_id).first()
            date_str = time.strftime('%Y-%m-%d')
            emp = get_object_or_404(Employee, pk=emp_id)
            settings = Utility.settings(user.creator_id)
            secs = time.mktime(time.strptime(settings.get('company_start_time', '00:00'), "%H:%M")) - time.mktime(time.strptime("00:00", "%H:%M"))
            total = time.strftime("%H:%M", time.localtime(time.mktime(time.strptime(settings.get('company_end_time', '00:00'), "%H:%M")) - secs))
            ctx = {
                'date': user.dateFormat(date_str),
                'app_name': os.getenv('APP_NAME', 'MyApp'),
                'employee_name': emp.name,
                'address': emp.address or '',
                'designation': emp.designation.name if emp.designation else '',
                'start_date': emp.company_doj or '',
                'branch': emp.Branch.name if hasattr(emp, 'Branch') and emp.Branch else '',
                'start_time': settings.get('company_start_time', ''),
                'end_time': settings.get('company_end_time', ''),
                'total_hours': total,
            }
            jl.content = JoiningLetter.replaceVariable(jl.content, ctx)
            return render(request, 'employee/template/joining_letter_docx.html', {'joiningletter': jl, 'employees': emp})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def exp_certificate_pdf(cls, request: HttpRequest, emp_id: int) -> HttpResponse:
        import inspect, time
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            lang = request.COOKIES.get('LANGUAGE', 'en')
            term = Termination.objects.filter(employee_id=emp_id).first()
            ec = ExperienceCertificate.objects.filter(lang=lang, created_by=request.user.creator_id).first()
            date_str = time.strftime('%Y-%m-%d')
            emp = get_object_or_404(Employee, pk=emp_id)
            settings = Utility.settings(request.user.creator_id)
            time.mktime(time.strptime(settings.get('company_start_time', '00:00'), "%H:%M")) - time.mktime(time.strptime("00:00", "%H:%M"))
            if term and term.termination_date:
                from datetime import datetime
                d1 = datetime.strptime(emp.company_doj, "%Y-%m-%d")
                d2 = datetime.strptime(term.termination_date, "%Y-%m-%d")
                days = (d2 - d1).days
                ctx = {
                    'date': request.user.dateFormat(date_str),
                    'app_name': os.getenv('APP_NAME', 'MyApp'),
                    'employee_name': emp.name,
                    'payroll': emp.salaryType.name if hasattr(emp, 'salaryType') and emp.salaryType else '',
                    'duration': f"{days} days",
                    'designation': emp.designation.name if emp.designation else '',
                }
            else:
                return default_undefined_exception(request, err=Exception("termination_date missing"), ref=f'{CN}::{MN}', logger=logger)
            ec.content = ExperienceCertificate.replaceVariable(ec.content, ctx)
            return render(request, 'employee/template/exp_certificate_pdf.html', {'experience_certificate': ec, 'employees': emp})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def exp_certificate_doc(cls, request: HttpRequest, emp_id: int) -> HttpResponse:
        import inspect, time
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            lang = request.COOKIES.get('LANGUAGE', 'en')
            term = Termination.objects.filter(employee_id=emp_id).first()
            ec = ExperienceCertificate.objects.filter(lang=lang, created_by=request.user.creator_id).first()
            date_str = time.strftime('%Y-%m-%d')
            emp = get_object_or_404(Employee, pk=emp_id)
            settings = Utility.settings(request.user.creator_id)
            time.mktime(time.strptime(settings.get('company_start_time', '00:00'), "%H:%M")) - time.mktime(time.strptime("00:00", "%H:%M"))
            if term and term.termination_date:
                from datetime import datetime
                d1 = datetime.strptime(emp.company_doj, "%Y-%m-%d")
                d2 = datetime.strptime(term.termination_date, "%Y-%m-%d")
                days = (d2 - d1).days
                ctx = {
                    'date': request.user.dateFormat(date_str),
                    'app_name': os.getenv('APP_NAME', 'MyApp'),
                    'employee_name': emp.name,
                    'payroll': emp.salaryType.name if hasattr(emp, 'salaryType') and emp.salaryType else '',
                    'duration': f"{days} days",
                    'designation': emp.designation.name if emp.designation else '',
                }
            else:
                return default_undefined_exception(request, err=Exception("termination_date missing"), ref=f'{CN}::{MN}', logger=logger)
            ec.content = ExperienceCertificate.replaceVariable(ec.content, ctx)
            return render(request, 'employee/template/exp_certificate_docx.html', {'experience_certificate': ec, 'employees': emp})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def noc_pdf(cls, request: HttpRequest, emp_id: int) -> HttpResponse:
        import inspect, time, os
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            user = request.user
            lang = user.currentLanguage()
            noc = Noc.objects.filter(lang=lang, created_by=user.creator_id).first()
            date_str = time.strftime('%Y-%m-%d')
            emp = get_object_or_404(Employee, pk=emp_id)
            settings = Utility.settings(user.creator_id)
            # TODO SECS?
            time.mktime(time.strptime(settings.get('company_start_time', '00:00'), "%H:%M")) - time.mktime(time.strptime("00:00", "%H:%M"))
            ctx = {
                'date': user.dateFormat(date_str),
                'employee_name': emp.name,
                'designation': emp.designation.name if emp.designation else '',
                'app_name': os.getenv('APP_NAME', 'MyApp'),
            }
            noc.content = Noc.replaceVariable(noc.content, ctx)
            return render(request, 'employee/template/noc_pdf.html', {'noc_certificate': noc, 'employees': emp})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def noc_doc(cls, request: HttpRequest, emp_id: int) -> HttpResponse:
        import inspect, time, os
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            user = request.user
            lang = user.currentLanguage()
            noc = Noc.objects.filter(lang=lang, created_by=user.creator_id).first()
            date_str = time.strftime('%Y-%m-%d')
            emp = get_object_or_404(Employee, pk=emp_id)
            settings = Utility.settings(user.creator_id)
            time.mktime(time.strptime(settings.get('company_start_time', '00:00'), "%H:%M")) - time.mktime(time.strptime("00:00", "%H:%M"))
            ctx = {
                'date': user.dateFormat(date_str),
                'employee_name': emp.name,
                'designation': emp.designation.name if emp.designation else '',
                'app_name': os.getenv('APP_NAME', 'MyApp'),
            }
            noc.content = Noc.replaceVariable(noc.content, ctx)
            return render(request, 'employee/template/noc_docx.html', {'noc_certificate': noc, 'employees': emp})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def export(cls, request: HttpRequest) -> HttpResponse:
        import inspect, time
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            filename = f"employee_{time.strftime('%Y-%m-%d_%H-%M-%S')}.xlsx"
            from ....Exports.employee_export import generate_employee_export
            return generate_employee_export(filename)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    def import_file(cls, request: HttpRequest) -> HttpResponse:
        import inspect
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            return render(request, 'employee/import.html', {})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def import_employees(cls, request: HttpRequest) -> HttpResponse:
        import inspect
        from django.contrib.auth.hashers import make_password
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            file = request.FILES.get('file')
            if not file or not file.name.lower().endswith(('.csv','.txt')):
                messages.error(request, "File is required and must be CSV or TXT.")
                return redirect(get_redirect_url(request))
            importer = EmployeesImport()
            data = importer.to_array(file)[0]
            total = len(data) - 1
            errors = []
            for row in data[1:]:
                if not row[5]:
                    messages.error(request, "Email field is required.")
                    return redirect(get_redirect_url(request))
                emp = Employee.objects.filter(email=row[5]).first()
                usr = User.objects.filter(email=row[5]).first()
                if emp and usr:
                    ed = emp
                else:
                    usr = User.objects.create(
                        name=row[0], email=row[5],
                        password=make_password(row[6]),
                        type='employee', lang='en', created_by=request.user.creator_id
                    )
                    usr.assign_role('Employee')
                    ed = Employee()
                    ed.employee_id = cls.employee_number(request)
                    ed.user_id = usr.id
                ed.name, ed.dob, ed.gender, ed.phone, ed.address, ed.email = row[0], row[1], row[2], row[3], row[4], row[5]
                ed.password = make_password(row[6])
                ed.branch_id, ed.department_id, ed.designation_id = row[8], row[9], row[10]
                ed.company_doj, ed.account_holder_name, ed.account_number = row[11], row[12], row[13]
                ed.bank_name, ed.bank_identifier_code, ed.branch_location = row[14], row[15], row[16]
                ed.tax_payer_id, ed.created_by = row[17], request.user.creator_id
                try:
                    ed.save()
                except Exception:
                    errors.append(row)
            if errors:
                messages.error(request, f"{len(errors)} records failed out of {total}")
                request.session['errorArray'] = [','.join(map(str, r)) for r in errors]
            else:
                messages.success(request, "Record successfully imported")
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)






