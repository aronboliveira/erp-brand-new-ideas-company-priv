import os
import time
from datetime import datetime
import logging
import inspect
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.contrib.auth.hashers import check_password, make_password
from django.core import signing
from django.core.exceptions import PermissionDenied
from django.http import JsonResponse, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.urls import reverse
from django.utils.decorators import method_decorator
from django.views.decorators.http import require_http_methods
from ....Models.activity.order import Order
from ....Models.individuals.employee import Employee
from ....Models.individuals.login_detail import LoginDetail
from ....Models.individuals.role import Role
from ....Models.individuals.user import User
from ....Models.planning.plan import Plan
from ....Models.planning.user_to_do import UserToDo
from ....Models.shapes.custom_field import CustomField
from ....Models.ssr.experience_certificate import ExperienceCertificate
from ....Models.ssr.generated_offer_letter import GeneratedOfferLetter
from ....Models.ssr.joining_letter import JoiningLetter
from ....Models.ssr.noc import Noc
from ....Models.utils.utility import Utility
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class UserController(Controller):
    MFN = staticmethod(lambda: inspect.currentframe().f_back.f_code.co_name)
    
    @method_decorator(login_required)
    @classmethod
    def index(cls, request: HttpResponse) -> HttpResponse:
        ref = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
        try:
            user = request.user
            if not user.has_perm('crm.manage_user'):
                return default_permission_denial(request, err=PermissionDenied(), ref=ref, logger=logger)
            if user.type.lower() == 'super admin':
                users = User.objects.filter(created_by=user.creator_id, type='company') \
                                     .select_related('currentPlan')
            else:
                users = User.objects.filter(created_by=user.creator_id) \
                                     .exclude(type__iexact='client') \
                                     .select_related('currentPlan')
            return render(request, 'user/index.html', {'users': users})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def create(cls, request: HttpResponse) -> HttpResponse:
        ref = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
        try:
            user = request.user
            if not user.has_perm('crm.create_user'):
                return default_permission_denial(request, err=PermissionDenied(), ref=ref, logger=logger)
            customFields = CustomField.getData(user, 'user')
            roles_qs = Role.objects.filter(created_by=user.creator_id).exclude(name__iexact='client')
            roles = {role.id: role.name for role in roles_qs}
            return render(request, 'user/create.html', {'roles': roles, 'customFields': customFields})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=ref, logger=logger)

    @classmethod
    def employee_number(cls, request: HttpResponse) -> int:
        try:
            creator_id = request.user.creator_id
            latest = Employee.objects.filter(created_by=creator_id).order_by('-id').first()
            return 1 if not latest else latest.employee_id + 1
        except Exception:
            return 1  # fallback if something goes wrong

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def store(cls, request: HttpResponse) -> HttpResponse:
        try:
            user = request.user
            default_language = Utility.settings(user.creator_id).get('default_language', 'en')
            if user.type.lower() == 'super admin':
                name = request.POST.get('name')
                email = request.POST.get('email')
                password = request.POST.get('password')
                if not name or not email or not password:
                    messages.error(request, "Name, Email, and Password are required.")
                    return redirect(get_redirect_url(request))
                if User.objects.filter(email=email).exists():
                    messages.error(request, "Email already exists.")
                    return redirect(get_redirect_url(request))
            fields = {
                'name': name,
                'email': email,
                'password': make_password(password),
                'type': 'company',
                'default_pipeline': 1,
                'plan': Plan.objects.first().id,
                'lang': default_language,
                'created_by': user.creator_id,
                'email_verified_at': datetime.now()
            }
            new_user = User(**fields)
            new_user.save()
            post_methods = [
                (new_user.assign_role,          (Role.objects.get(name__iexact='company'),)),
                (new_user.userDefaultDataRegister, (new_user.id,)),
                (new_user.userWarehouseRegister,   (new_user.id,)),
                (new_user.userDefaultBankAccount,  (new_user.id,)),
            ]
            for fn, args in post_methods:
                fn(*args)
            utility_calls = [
                Utility.chartOfAccountTypeData,
                Utility.chartOfAccountData1,
                Utility.pipeline_lead_deal_Stage,
                Utility.project_task_stages,
                Utility.labels,
                Utility.sources,
                Utility.jobStage,
            ]
            for fn in utility_calls:
                fn(new_user.id)
            cert_calls = [
                GeneratedOfferLetter.defaultOfferLetterRegister,
                ExperienceCertificate.defaultExpCertificatRegister,
                JoiningLetter.defaultJoiningLetterRegister,
                Noc.defaultNocCertificateRegister,
            ]
            for fn in cert_calls:
                fn(new_user.id)
            else:
                required_fields = ('name', 'email', 'password', 'role')
                data = {field: request.POST.get(field, '').strip() for field in required_fields}
                missing = [field for field, value in data.items() if not value]
                if missing:
                    labels = ", ".join(f.title() for f in missing)
                    messages.error(request, f"{labels} {'is' if len(missing)==1 else 'are'} required.")
                    return redirect(get_redirect_url(request))
                if User.objects.filter(email=data['email']).exists():
                    messages.error(request, "Email already exists.")
                    return redirect(get_redirect_url(request))
                name, email, password, role_id = (data[field] for field in required_fields)
                creator_user = User.objects.get(pk=user.creator_id)
                total_user = creator_user.countUsers()
                plan = Plan.objects.get(pk=creator_user.plan)
                if total_user >= plan.max_users and plan.max_users != -1:
                    messages.error(request, "Your user limit is over, Please upgrade plan.")
                    return redirect(get_redirect_url(request))
                role_obj = Role.objects.get(pk=role_id)
                new_user = User.objects.create(
                    name=name,
                    email=email,
                    password=make_password(password),
                    type=role_obj.name,
                    lang=default_language,
                    created_by=user.creator_id,
                    email_verified_at=datetime.now()
                )
                new_user.assign_role(role_obj)
                if role_obj.name.lower() != 'client':
                    Utility.employeeDetails(new_user.id, user.creator_id)
            settings_obj = Utility.settings(user.creator_id)
            if settings_obj.get('new_user') == 1:
                new_user.password = password
                new_user.type = (role_obj.name if user.type.lower() != 'super admin' else 'company')
                new_user.userDefaultDataRegister(new_user.id)
                userArr = {'email': new_user.email, 'password': new_user.password}
                resp = Utility.sendEmailTemplate('new_user', {new_user.id: new_user.email}, userArr)
                msg = "User successfully created."
                if resp and (not resp.get('is_success')) and resp.get('error'):
                    msg += f"<br> <span class='text-danger'>{resp['error']}</span>"
                messages.success(request, msg)
                return redirect('users_index')
            messages.success(request, "User successfully created.")
            return redirect('users_index')
        except PermissionDenied as e:
            return default_permission_denial(
                request, err=e, ref=f'{cls.__name__}::{cls.MFN()}', logger=logger
            )
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=f'{cls.__name__}::{cls.MFN()}', logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def show(cls, request: HttpResponse) -> HttpResponse:
        try:
            return redirect('users_index')
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=f'{cls.__name__}::{cls.MFN()}', logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def edit(cls, request: HttpResponse, user_id: int) -> HttpResponse:
        try:
            user = request.user
            roles_qs = Role.objects.filter(created_by=user.creator_id).exclude(name__iexact='client')
            roles = {role.id: role.name for role in roles_qs}
            if not user.has_perm('crm.edit_user'):
                return redirect(get_redirect_url(request))
            edit_user = get_object_or_404(User, pk=user_id)
            edit_user.customField = CustomField.getData(edit_user, 'user')
            customFields = CustomField.objects.filter(created_by=user.creator_id, module='user')
            return render(request, 'user/edit.html', {
                'user': edit_user, 'roles': roles, 'customFields': customFields
            })
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=f'{cls.__name__}::{cls.MFN()}', logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def update(cls, request: HttpResponse, user_id: int) -> HttpResponse:
        try:
            if not request.user.has_perm('crm.edit_user'):
                return redirect(get_redirect_url(request))
            if request.user.type.lower() == 'super admin':
                edit_user = get_object_or_404(User, pk=user_id)
                name = request.POST.get('name')
                email = request.POST.get('email')
                if not name or not email:
                    messages.error(request, "Name and Email are required.")
                    return redirect(get_redirect_url(request))
                if User.objects.filter(email=email).exclude(pk=user_id).exists():
                    messages.error(request, "Email already exists.")
                    return redirect(get_redirect_url(request))
                role_obj = Role.objects.get(name__iexact='company')
                input_data = request.POST.dict()
                input_data['type'] = role_obj.name
                for key, value in input_data.items():
                    setattr(edit_user, key, value)
                edit_user.save()
                CustomField.saveData(edit_user, request.POST.get('customField', {}))
                edit_user.roles.set([role_obj.id])
                messages.success(request, "User successfully updated.")
                return redirect('users_index')
            else:
                edit_user = get_object_or_404(User, pk=user_id)
                name = request.POST.get('name')
                email = request.POST.get('email')
                role_id = request.POST.get('role')
                if not name or not email or not role_id:
                    messages.error(request, "Name, Email, and Role are required.")
                    return redirect(get_redirect_url(request))
                if User.objects.filter(email=email).exclude(pk=user_id).exists():
                    messages.error(request, "Email already exists.")
                    return redirect(get_redirect_url(request))
                role_obj = Role.objects.get(pk=role_id)
                input_data = request.POST.dict()
                input_data['type'] = role_obj.name
                for key, value in input_data.items():
                    setattr(edit_user, key, value)
                edit_user.save()
                Utility.employeeDetailsUpdate(edit_user.id, request.user.creator_id)
                CustomField.saveData(edit_user, request.POST.get('customField', {}))
                edit_user.roles.set([role_id])
                messages.success(request, "User successfully updated.")
                return redirect('users_index')
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=f'{cls.__name__}::{cls.MFN()}', logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def destroy(cls, request: HttpResponse, user_id: int) -> HttpResponse:
        try:
            if not request.user.has_perm('crm.delete_user'):
                return redirect(get_redirect_url(request))
            edit_user = User.objects.filter(pk=user_id).first()
            if edit_user:
                if request.user.type.lower() == 'super admin':
                    edit_user.delete_status = 1 if edit_user.delete_status == 0 else 0
                    edit_user.save()
                elif request.user.type.lower() == 'company':
                    Employee.objects.filter(user_id=edit_user.id).delete()
                    deleted = User.objects.filter(id=edit_user.id).delete()
                    if not deleted:
                        messages.error(request, "Something is wrong.")
                        return redirect(get_redirect_url(request))
                messages.success(request, "User successfully deleted.")
                return redirect('users_index')
            else:
                messages.error(request, "Something is wrong.")
                return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=f'{cls.__name__}::{cls.MFN()}', logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def profile(cls, request: HttpResponse) -> HttpResponse:
        try:
            userDetail = request.user
            userDetail.customField = CustomField.getData(userDetail, 'user')
            customFields = CustomField.objects.filter(created_by=userDetail.creator_id, module='user')
            return render(request, 'user/profile.html', {
                'userDetail': userDetail, 'customFields': customFields
            })
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=f'{cls.__name__}::{cls.MFN()}', logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def edit_profile(cls, request: HttpResponse) -> HttpResponse:
        try:
            userDetail = request.user
            edit_user = get_object_or_404(User, pk=userDetail.id)
            name = request.POST.get('name')
            email = request.POST.get('email')
            if not name or not email:
                messages.error(request, "Name and Email are required.")
                return redirect(get_redirect_url(request))
            if request.FILES.get('profile'):
                file = request.FILES['profile']
                original_name = file.name
                filename, extension = os.path.splitext(original_name)
                fileNameToStore = f"{filename}_{int(time.time())}.{extension.lstrip('.')}"
                settings_storage = Utility.getStorageSetting()
                dir_path = ('uploads/avatar/' if settings_storage.get('storage_setting') == 'local'
                            else 'uploads/avatar')
                image_path = os.path.join(dir_path, str(userDetail.avatar)) if userDetail.avatar else ''
                if image_path and os.path.exists(image_path):
                    os.remove(image_path)
                path_result = Utility.upload_file(request, 'profile', fileNameToStore, dir_path, [], {})
                if path_result.get('flag') == 1:
                    edit_user.avatar = fileNameToStore
                else:
                    messages.error(request, path_result.get('msg'))
                    return redirect('profile')
            edit_user.name = name
            edit_user.email = email
            edit_user.save()
            CustomField.saveData(edit_user, request.POST.get('customField', {}))
            messages.success(request, "Profile successfully updated.")
            return redirect('dashboard')
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=f'{cls.__name__}::{cls.MFN()}', logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def update_password(cls, request: HttpResponse) -> HttpResponse:
        try:
            if not request.user.is_authenticated:
                messages.error(request, "Something is wrong.")
                return redirect('profile', request.user.id)
            old_password = request.POST.get('old_password')
            new_password = request.POST.get('password')
            password_confirmation = request.POST.get('password_confirmation')
            if not old_password or not new_password or not password_confirmation:
                messages.error(request, "All password fields are required.")
                return redirect('profile', request.user.id)
            if new_password != password_confirmation:
                messages.error(request, "New password and confirmation must match.")
                return redirect('profile', request.user.id)
            if not check_password(old_password, request.user.password):
                messages.error(request, "Please enter correct current password.")
                return redirect('profile', request.user.id)
            edit_user = get_object_or_404(User, pk=request.user.id)
            edit_user.password = make_password(new_password)
            edit_user.save()
            messages.success(request, "Password successfully updated.")
            return redirect('profile', request.user.id)
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=f'{cls.__name__}::{cls.MFN()}', logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def todo_store(cls, request: HttpResponse) -> JsonResponse:
        try:
            title = request.POST.get('title')
            if not title or len(title) > 120:
                messages.error(request, "Title is required and must be 120 characters or less.")
                return redirect(get_redirect_url(request))
            post_data = request.POST.dict()
            post_data['user_id'] = request.user.id
            todo = UserToDo.objects.create(**post_data)
            todo.updateUrl = reverse('todo_update', args=[todo.id])
            todo.deleteUrl = reverse('todo_destroy', args=[todo.id])
            todo.save()
            return JsonResponse(todo.to_dict())
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=f'{cls.__name__}::{cls.MFN()}', logger=logger, json={'error': f"{cls.__name__}::{cls.MFN()}: {e}"}
            )

    @method_decorator(login_required)
    @classmethod
    def todo_update(cls, request: HttpResponse, todo_id: int) -> JsonResponse:
        try:
            todo = get_object_or_404(UserToDo, pk=todo_id)
            todo.is_complete = 0 if todo.is_complete == 1 else 1
            todo.save()
            return JsonResponse(todo.to_dict())
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=f'{cls.__name__}::{cls.MFN()}', logger=logger, json={'error': f"{cls.__name__}::{cls.MFN()}: {e}"}
            )

    @method_decorator(login_required)
    @classmethod
    def todo_destroy(cls, request: HttpResponse, todo_id: int) -> JsonResponse:
        try:
            todo = get_object_or_404(UserToDo, pk=todo_id)
            todo.delete()
            return JsonResponse({'success': True})
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=f'{cls.__name__}::{cls.MFN()}', logger=logger, json={'error': f"{cls.__name__}::{cls.MFN()}: {e}"}
            )
            
    @method_decorator(login_required)
    @classmethod
    def change_mode(cls, request: HttpResponse) -> HttpResponse:
        try:
            usr = request.user
            usr.mode, usr.dark_mode = (('dark', 1) if usr.mode == 'light' else ('light', 0))
            usr.save()
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{cls.MFN()}',
                logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def upgrade_plan(cls, request: HttpResponse, user_id: int) -> HttpResponse:
        try:
            user_obj = get_object_or_404(User, pk=user_id)
            plans = Plan.objects.all()
            admin_payment_setting = Utility.getAdminPaymentSetting()
            return render(request, 'user/plan.html', {
                'user': user_obj,
                'plans': plans,
                'admin_payment_setting': admin_payment_setting
            })
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{cls.MFN()}',
                logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def active_plan(cls, request: HttpResponse, user_id: int, plan_id: int) -> HttpResponse:
        try:
            user_obj = get_object_or_404(User, pk=user_id)
            assignPlan = user_obj.assignPlan(plan_id)
            plan = get_object_or_404(Plan, pk=plan_id)
            if assignPlan.get('is_success') and plan:
                import uuid
                order_id = uuid.uuid4().hex.upper()
                Order.objects.create(
                    order_id=order_id,
                    name=None,
                    card_number=None,
                    card_exp_month=None,
                    card_exp_year=None,
                    plan_name=plan.name,
                    plan_id=plan.id,
                    price=plan.price,
                    price_currency=user_obj.planPrice().get('currency', ''),
                    txn_id='',
                    payment_status='success',
                    receipt=None,
                    user_id=user_obj.id,
                )
                messages.success(request, "Plan successfully upgraded.")
            else:
                messages.error(request, "Plan fail to upgrade.")
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{cls.MFN()}',
                logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def user_password_view(cls, request: HttpResponse, encrypted_id: str) -> HttpResponse:
        try:
            try:
                emp_id = signing.loads(encrypted_id)
            except Exception as de:
                messages.error(request, f"Employee Not Found: {de}")
                return redirect(get_redirect_url(request))
            user_obj = get_object_or_404(User, pk=emp_id)
            return render(request, 'user/reset.html', {'user': user_obj})
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{cls.MFN()}',
                logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def user_password_recreate(cls, request: HttpResponse, user_id: int) -> HttpResponse:
        try:
            new_password = request.POST.get('password')
            confirmation = request.POST.get('password_confirmation')
            if new_password != confirmation:
                messages.error(request, "Passwords do not match.")
                return redirect(get_redirect_url(request))
            user_obj = get_object_or_404(User, pk=user_id)
            user_obj.password = make_password(new_password)
            user_obj.save()
            messages.success(request, "User Password successfully updated.")
            return redirect('users_index')
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{cls.MFN()}',
                logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def user_logs(cls, request: HttpResponse) -> HttpResponse:
        try:
            creator_id = request.user.creator_id
            users_qs = User.objects.filter(created_by=creator_id).values('id', 'name')
            filteruser = {'': 'Select User', **{u['id']: u['name'] for u in users_qs}}
            query = LoginDetail.objects.filter(created_by=request.user.id).select_related('user')
            month = request.GET.get('month')
            if month:
                dt = datetime.strptime(month, "%Y-%m-%d")
                query = query.filter(date__month=dt.month, date__year=dt.year)
            else:
                today = datetime.today()
                query = query.filter(date__month=today.month, date__year=today.year)
            if users := request.GET.get('users'):
                query = query.filter(user_id=users)
            context = {
                'userdetails': list(query),
                'last_login_details': LoginDetail.objects.filter(created_by=creator_id),
                'filteruser': filteruser,
            }
            return render(request, 'user/userlog.html', context)
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{cls.MFN()}',
                logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def user_logs_view(cls, request: HttpResponse, log_id: int) -> HttpResponse:
        try:
            log = get_object_or_404(LoginDetail, pk=log_id)
            return render(request, 'user/userlogview.html', {'users': log})
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{cls.MFN()}',
                logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def user_logs_destroy(cls, request: HttpResponse, user_id: int) -> HttpResponse:
        try:
            LoginDetail.objects.filter(user_id=user_id).delete()
            messages.success(request, "User successfully deleted.")
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.__name__}::{cls.MFN()}',
                logger=logger
            )
  
    @method_decorator(login_required)
    @classmethod
    def notifications_seen(cls, request: HttpResponse, uid: int) -> JsonResponse:
        try:
            return JsonResponse({'success': True})
        except Exception as e:
            logger.error(f"Failed in {cls.__name__}.{cls.MFN()}: {e}")
            return JsonResponse({'error': f"{cls.MFN()}: {e}"}, status=500)

    @method_decorator(login_required)
    @classmethod
    def filter_user_view(cls, request: HttpResponse) -> JsonResponse:
        try:
            users = User.objects.filter(created_by=request.user.creator_id).values()
            return JsonResponse(list(users), safe=False)
        except Exception as e:
            logger.error(f"Failed in {cls.__name__}.{cls.MFN()}: {e}")
            return JsonResponse({'error': f"{cls.MFN()}: {e}"}, status=500)

    @method_decorator(login_required)
    @classmethod
    def check_user_exists(cls, request: HttpResponse) -> JsonResponse:
        try:
            email = request.GET.get('email')
            exists = User.objects.filter(email=email).exists()
            return JsonResponse({'exists': exists})
        except Exception as e:
            logger.error(f"Failed in {cls.__name__}.{cls.MFN()}: {e}")
            return JsonResponse({'error': f"{cls.MFN()}: {e}"}, status=500)

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def update_profile(cls, request: HttpResponse) -> JsonResponse:
        try:
            user = request.user
            name = request.POST.get('name')
            email = request.POST.get('email')
            if not name or not email:
                return JsonResponse({'error': "Name and Email are required."}, status=400)
            user.name = name
            user.email = email
            user.save()
            return JsonResponse({'success': True})
        except Exception as e:
            logger.error(f"Failed in {cls.__name__}.{cls.MFN()}: {e}")
            return JsonResponse({'error': f"{cls.MFN()}: {e}"}, status=500)

    @method_decorator(login_required)
    @classmethod
    def user_info(cls, request: HttpResponse, id: int) -> JsonResponse:
        try:
            user_obj = get_object_or_404(User, pk=id)
            data = {
                'id': user_obj.id,
                'name': user_obj.name,
                'email': user_obj.email,
                'type': user_obj.type
            }
            return JsonResponse(data)
        except Exception as e:
            logger.error(f"Failed in {cls.__name__}.{cls.MFN()}: {e}")
            return JsonResponse({'error': f"{cls.MFN()}: {e}"}, status=500)

    @method_decorator(login_required)
    @classmethod
    def get_project_task(cls, request: HttpResponse, id: int, type: str) -> JsonResponse:
        try:
            data = {'projects': []} if type == 'project' else ({'tasks': []} if type == 'task' else {})
            return JsonResponse(data)
        except Exception as e:
            logger.error(f"Failed to process request for {id} in {cls.__name__}.{cls.MFN()}: {e}")
            logger.error(request)
            return JsonResponse({'error': f"{cls.MFN()}: {e}"}, status=500)

    @classmethod
    def search(cls, request: HttpResponse) -> JsonResponse:
        try:
            q = request.GET.get('q', '')
            results = User.objects.filter(name__icontains=q).values('id', 'name')
            return JsonResponse(list(results), safe=False)
        except Exception as e:
            logger.error(f"Failed in {cls.__name__}.{cls.MFN()}: {e}")
            return JsonResponse({'error': f"{cls.MFN()}: {e}"}, status=500)