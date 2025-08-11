import inspect
import logging
from django.conf import settings
from django.contrib import messages
from django.contrib.auth import login
from django.contrib.auth.hashers import make_password
from django.http import HttpRequest, HttpResponse
from django.shortcuts import render, redirect
from django.utils import timezone
from django.utils.translation import activate
from ....Models.individuals.role import Role
from ....Models.individuals.user import User
from ....Models.ssr.experience_certificate import ExperienceCertificate
from ....Models.ssr.generated_offer_letter import GeneratedOfferLetter
from ....Models.ssr.joining_letter import JoiningLetter
from ....Models.ssr.noc import Noc
from ....Models.utils.utility import Utility
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class RegisteredUserController(Controller):
    CLN = 'RegisteredUserController'
    @classmethod
    def MFN(cls):
        return inspect.currentframe().f_back.f_code.co_name

    def __init__(self, **kwargs):
        super().__init__(**kwargs)
        self.middleware('guest')

    def show_registration_form(self, request: HttpRequest, lang: str = '') -> HttpResponse:
        if request.user.is_authenticated:
            return redirect('home')
        settings_dict = Utility.settings()
        if settings_dict.get('enable_signup', 'off') != 'on':
            return redirect('login')
        lang = lang or Utility.get_value_by_name('default_language')
        activate(lang)
        return render(request, 'auth/register.html', {'lang': lang})

    def store(self, request: HttpRequest, *args, **kwargs) -> HttpResponse:
        if request.user.is_authenticated:
            return redirect('home')
        try:
            if getattr(settings, 'RECAPTCHA_MODULE', 'off') == 'on':
                resp = request.POST.get('g-recaptcha-response')
                if not resp:
                    messages.error(request, "Please complete the reCAPTCHA.")
                    return redirect('register')
            name = request.POST.get('name')
            email = request.POST.get('email')
            pwd = request.POST.get('password')
            pwd_conf = request.POST.get('password_confirmation')
            if not name or not email or not pwd or pwd != pwd_conf:
                messages.error(request, "Invalid input.")
                return redirect('register')
            if User.objects.filter(email=email).exists():
                messages.error(request, "Email already exists.")
                return redirect('register')
            user = User.objects.create(
                name=name,
                email=email,
                password=make_password(pwd),
                type='company',
                default_pipeline=1,
                plan=1,
                lang=Utility.get_value_by_name('default_language'),
                avatar='',
                created_by=1,
            )
            login(request, user)
            s = Utility.settings()
            if s.get('email_verification', 'off') == 'on':
                try:
                    Utility.smtpDetail(1)
                    r = Role.find_by_name('company')
                    user.assign_role(r)
                    user.user_default_data_register(user.id)
                    user.user_warehouse_register(user.id)
                    user.user_default_bank_account(user.id)
                    Utility.chart_of_account_type_data(user.id)
                    Utility.chart_of_account_data1(user.id)
                    Utility.pipeline_lead_deal_stage(user.id)
                    Utility.project_task_stages(user.id)
                    Utility.labels(user.id)
                    Utility.sources(user.id)
                    Utility.job_stage(user.id)
                    GeneratedOfferLetter.default_offer_letter_register(user.id)
                    ExperienceCertificate.default_exp_certificate_register(user.id)
                    JoiningLetter.default_joining_letter_register(user.id)
                    Noc.default_noc_certificate_register(user.id)
                except Exception as e:
                    logger.exception(f"{self.CLN}::{self.MFN()} email-verif failed: %s", e)
                    user.delete()
                    messages.error(request, "SMTP settings mis-configured, contact admin.")
                    return redirect('register')
                return redirect('email_verify_prompt')
            user.email_verified_at = timezone.now()
            user.save()
            r = Role.find_by_name('company')
            user.assign_role(r)
            user.user_default_data(user.id)
            user.user_default_data_register(user.id)
            user.user_default_bank_account(user.id)
            Utility.chart_of_account_type_data(user.id)
            Utility.chart_of_account_data1(user.id)
            GeneratedOfferLetter.default_offer_letter_register(user.id)
            ExperienceCertificate.default_exp_certificate_register(user.id)
            JoiningLetter.default_joining_letter_register(user.id)
            Noc.default_noc_certificate_register(user.id)
            Utility.send_user_email_template(
                'new_user',
                {user.id: user.email},
                {'email': user.email, 'password': user.password}
            )
            return redirect('home')
        except Exception as e:
            logger.exception(f"{self.CLN}::{self.MFN()} failed: %s", e)
            messages.error(request, "Registration error.")
            return redirect('register')
