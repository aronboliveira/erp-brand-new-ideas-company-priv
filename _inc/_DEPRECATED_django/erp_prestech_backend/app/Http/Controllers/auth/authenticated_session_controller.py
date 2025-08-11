import re
import json
import random
import string
import logging
from typing import Any, Dict, Optional
from django.shortcuts import render, redirect
from django.http import HttpRequest, HttpResponse
from django.contrib import messages
from django.contrib.auth import authenticate, login, logout
from django.contrib.auth.hashers import make_password
from django.utils import timezone
from django.conf import settings
from django.core.mail import send_mail
from django.template.loader import render_to_string
from .._helpers.http import get_redirect_url
from ....Models.companies.vendor import Vendor
from ....Models.individuals.customer import Customer
from ....Models.individuals.login_detail import LoginDetail
from ....Models.individuals.password_reset import PasswordReset
from ....Models.planning.plan import Plan
from ....Models.utils.utility import Utility
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class AuthenticatedSessionController(Controller):
    """
    This class mimics the functionality of your Laravel AuthenticatedSessionController.
    It defines authentication, password reset, and form-rendering methods.
    """

    # --- Helper Methods ---
    @staticmethod
    def get_device_type(user_agent: str) -> str:
        mobile_regex = re.compile(
            r'(?:phone|windows\s+phone|ipod|blackberry|(?:android|bb\d+|meego|silk|googlebot).+?mobile|palm|windows\s+ce|opera mini|avantgo|mobilesafari|docomo)',
            re.I,
        )
        tablet_regex = re.compile(
            r'(?:ipad|playbook|(?:android|bb\d+|meego|silk)(?!.+?mobile))',
            re.I,
        )
        if mobile_regex.search(user_agent):
            logger.debug("Device type determined: mobile")
            return 'mobile'
        elif tablet_regex.search(user_agent):
            logger.debug("Device type determined: tablet")
            return 'tablet'
        else:
            logger.debug("Device type determined: desktop")
            return 'desktop'

    @staticmethod
    def generate_token(length: int = 60) -> str:
        token = ''.join(random.choices(string.ascii_letters + string.digits, k=length))
        logger.debug("Generated token: %s", token)
        return token

    # --- Authentication Methods ---
    def create(self, request: HttpRequest) -> HttpResponse:
        """
        Display the login view.
        """
        logger.info("Displaying login view")
        try:
            return render(request, 'auth/login.html')
        except Exception as e:
            logger.exception("Error displaying login view: %s", e)
            messages.error(request, "An error occurred while loading the login page.")
            return redirect('/')

    def store(self, request: HttpRequest) -> HttpResponse:
        """
        Handle an incoming authentication request.
        Performs reCAPTCHA validation (if enabled), authenticates the user,
        checks custom flags and plan expiration (for companies), and logs extra details.
        """
        logger.info("Processing authentication request")
        try:
            if request.method == 'POST':
                # ReCaptcha validation if enabled
                if getattr(settings, 'RECAPTCHA_MODULE', 'off') == 'on':
                    recaptcha_response: Optional[str] = request.POST.get('g-recaptcha-response')
                    if not recaptcha_response:
                        logger.warning("ReCAPTCHA validation failed - response missing")
                        messages.error(request, "Please complete the reCAPTCHA.")
                        return redirect('login')
                    # Optional: Verify recaptcha with external API

                username: Optional[str] = request.POST.get('username')
                password: Optional[str] = request.POST.get('password')
                logger.debug("Authenticating username: %s", username)
                user = authenticate(request, username=username, password=password)
                if user is not None:
                    # Check custom flags: delete_status and is_active
                    if hasattr(user, 'delete_status') and not user.delete_status:
                        logger.warning("User account deleted logging out user [%s]", username)
                        logout(request)
                        messages.error(request, "Your account has been deleted.")
                        return redirect('login')
                    if not user.is_active:
                        logger.warning("User account inactive logging out user [%s]", username)
                        logout(request)
                        messages.error(request, "Your account is inactive.")
                        return redirect('login')
                    # For company users, check plan expiration
                    if getattr(user, 'type', None) == 'company':
                        try:
                            plan: Plan = Plan.objects.get(pk=user.plan)
                            if plan.duration != 'lifetime' and user.plan_expire_date:
                                if user.plan_expire_date.date() <= timezone.now().date():
                                    user.assign_plan(1)
                                    logger.info("User plan expired assigned default plan for user [%s]", username)
                                    messages.error(request, "Your Plan is expired.")
                                    return redirect('home')
                        except Plan.DoesNotExist:
                            logger.error("Plan not found for user [%s]", username)
                    # Log the user in
                    login(request, user)
                    user.last_login_at = timezone.now()
                    user.save()
                    logger.info("User [%s] successfully authenticated", username)

                    # Log additional details for non-company, non-super admin users.
                    if getattr(user, 'type', None) not in ['company', 'super admin']:
                        ip: str = request.META.get('REMOTE_ADDR', '')
                        user_agent: str = request.META.get('HTTP_USER_AGENT', '')
                        device_type: str = self.get_device_type(user_agent)
                        referrer: str = get_redirect_url(request)
                        details: Dict[str, Any] = {
                            'browser_name': '',  # Optionally, use a dedicated library
                            'os_name': '',
                            'browser_language': request.META.get('HTTP_ACCEPT_LANGUAGE', '')[:2],
                            'device_type': device_type,
                            'referrer': referrer,
                        }
                        json_details: str = json.dumps(details)
                        LoginDetail.objects.create(
                            user_id=user.id,
                            ip=ip,
                            date=timezone.now(),
                            details=json_details,
                            created_by=user.creator_id() if hasattr(user, 'creator_id') else None,
                        )
                        logger.debug("Login details logged for user [%s]: %s", username, json_details)

                    if getattr(user, 'type', None) in ['company', 'super admin', 'client']:
                        logger.info("Redirecting user [%s] to home", username)
                        return redirect('home')
                    else:
                        logger.info("Redirecting user [%s] to employee_home", username)
                        return redirect('employee_home')
                else:
                    logger.warning("Authentication failed for username: %s", username)
                    messages.error(request, "Invalid credentials.")
                    return redirect('login')
            else:
                logger.debug("Non-POST request received in store(), redirecting to login form")
                return self.create(request)
        except Exception as e:
            logger.exception("Error in authentication store(): %s", e)
            messages.error(request, "An error occurred during login.")
            return redirect('login')

    def destroy(self, request: HttpRequest) -> HttpResponse:
        """
        Destroy an authenticated session.
        """
        logger.info("Destroying user session")
        try:
            logout(request)
            request.session.flush()
            logger.debug("Session flushed successfully")
            return redirect('/')
        except Exception as e:
            logger.exception("Error during logout: %s", e)
            messages.error(request, "An error occurred while logging out.")
            return redirect('/')

    # --- Customer Authentication ---
    def show_customer_login_form(self, request: HttpRequest, lang: str = '') -> HttpResponse:
        if not lang:
            lang = Utility.get_value_by_name('default_language')
            logger.debug("Default language used for customer login: %s", lang)
        try:
            return render(request, 'auth/customer_login.html', {'lang': lang})
        except Exception as e:
            logger.exception("Error displaying customer login form: %s", e)
            messages.error(request, "An error occurred.")
            return redirect('/')

    def customer_login(self, request: HttpRequest) -> HttpResponse:
        logger.info("Processing customer login")
        try:
            if request.method == 'POST':
                email: Optional[str] = request.POST.get('email')
                password: Optional[str] = request.POST.get('password')
                if not email or not password or len(password) < 6:
                    logger.warning("Invalid credentials provided for customer login")
                    messages.error(request, "Invalid email or password.")
                    return redirect('customer_login')
                user = authenticate(request, username=email, password=password)
                if user is not None:
                    if not user.is_active:
                        logger.warning("Inactive customer account for email: %s", email)
                        logout(request)
                        messages.error(request, "Your account is inactive.")
                        return redirect('customer_login')
                    login(request, user)
                    user.last_login_at = timezone.now()
                    user.save()
                    logger.info("Customer [%s] logged in successfully", email)
                    return redirect('customer_dashboard')
                else:
                    logger.warning("Authentication failed for customer email: %s", email)
                    messages.error(request, "Invalid login credentials.")
                    return redirect('customer_login')
            else:
                return render(request, 'auth/customer_login.html')
        except Exception as e:
            logger.exception("Error in customer_login: %s", e)
            messages.error(request, "An error occurred during customer login.")
            return redirect('customer_login')

    # --- Vendor Authentication ---
    def show_vendor_login_form(self, request: HttpRequest, lang: str = '') -> HttpResponse:
        if not lang:
            lang = Utility.get_value_by_name('default_language')
            logger.debug("Default language used for vendor login: %s", lang)
        try:
            return render(request, 'auth/vendor_login.html', {'lang': lang})
        except Exception as e:
            logger.exception("Error displaying vendor login form: %s", e)
            messages.error(request, "An error occurred.")
            return redirect('/')

    def vendor_login(self, request: HttpRequest) -> HttpResponse:
        logger.info("Processing vendor login")
        try:
            if request.method == 'POST':
                email: Optional[str] = request.POST.get('email')
                password: Optional[str] = request.POST.get('password')
                if not email or not password or len(password) < 6:
                    logger.warning("Invalid credentials for vendor login")
                    messages.error(request, "Invalid email or password.")
                    return redirect('vendor_login')
                user = authenticate(request, username=email, password=password)
                if user is not None:
                    if not user.is_active:
                        logger.warning("Inactive vendor account for email: %s", email)
                        logout(request)
                        messages.error(request, "Your account is inactive.")
                        return redirect('vendor_login')
                    login(request, user)
                    user.last_login_at = timezone.now()
                    user.save()
                    logger.info("Vendor [%s] logged in successfully", email)
                    return redirect('vendor_dashboard')
                else:
                    logger.warning("Authentication failed for vendor email: %s", email)
                    messages.error(request, "Invalid login credentials.")
                    return redirect('vendor_login')
            else:
                return render(request, 'auth/vendor_login.html')
        except Exception as e:
            logger.exception("Error in vendor_login: %s", e)
            messages.error(request, "An error occurred during vendor login.")
            return redirect('vendor_login')

    # --- General Login and Forgot Password Forms ---
    def show_login_form(self, request: HttpRequest, lang: str = '') -> HttpResponse:
        if not lang:
            lang = Utility.get_value_by_name('default_language')
            logger.debug("Default language used for login form: %s", lang)
        try:
            settings_data: Dict[str, Any] = Utility.settings()
            return render(request, 'auth/login.html', {'lang': lang, 'settings': settings_data})
        except Exception as e:
            logger.exception("Error displaying general login form: %s", e)
            messages.error(request, "An error occurred.")
            return redirect('/')

    def show_link_request_form(self, request: HttpRequest, lang: str = '') -> HttpResponse:
        if not lang:
            lang = Utility.get_value_by_name('default_language')
            logger.debug("Default language used for forgot password form: %s", lang)
        try:
            return render(request, 'auth/forgot-password.html', {'lang': lang})
        except Exception as e:
            logger.exception("Error displaying forgot password form: %s", e)
            messages.error(request, "An error occurred.")
            return redirect('/')

    def show_customer_login_lang(self, request: HttpRequest, lang: str = '') -> HttpResponse:
        if not lang:
            lang = Utility.get_value_by_name('default_language')
            logger.debug("Default language used for customer login lang: %s", lang)
        try:
            return render(request, 'auth/customer_login.html', {'lang': lang})
        except Exception as e:
            logger.exception("Error in show_customer_login_lang: %s", e)
            messages.error(request, "An error occurred.")
            return redirect('/')

    def show_vendor_login_lang(self, request: HttpRequest, lang: str = '') -> HttpResponse:
        if not lang:
            lang = Utility.get_value_by_name('default_language')
            logger.debug("Default language used for vendor login lang: %s", lang)
        try:
            return render(request, 'auth/vendor_login.html', {'lang': lang})
        except Exception as e:
            logger.exception("Error in show_vendor_login_lang: %s", e)
            messages.error(request, "An error occurred.")
            return redirect('/')

    # --- Customer Password Reset ---
    def show_customer_link_request_form(self, request: HttpRequest, lang: str = '') -> HttpResponse:
        if not lang:
            lang = Utility.get_value_by_name('default_language')
            logger.debug("Default language used for customer password reset: %s", lang)
        try:
            return render(request, 'auth/passwords/customerEmail.html', {'lang': lang})
        except Exception as e:
            logger.exception("Error displaying customer password reset email form: %s", e)
            messages.error(request, "An error occurred.")
            return redirect('/')

    def post_customer_email(self, request: HttpRequest) -> HttpResponse:
        logger.info("Processing customer password reset email")
        try:
            if request.method == 'POST':
                email: Optional[str] = request.POST.get('email')
                if not email:
                    logger.error("Email not provided for password reset")
                    messages.error(request, "Email is required.")
                    return redirect('customer_password_reset')
                try:
                    Customer.objects.get(email=email)
                except Customer.DoesNotExist:
                    logger.warning("Customer email not found: %s", email)
                    messages.error(request, "Email does not exist.")
                    return redirect('customer_password_reset')
                token: str = self.generate_token(60)
                PasswordReset.objects.create(
                    email=email,
                    token=token,
                    created_at=timezone.now()
                )
                subject: str = "Reset Password Notification"
                message: str = render_to_string('auth/customerVerify.html', {'token': token})
                from_email: str = settings.EMAIL_HOST_USER
                send_mail(subject, message, from_email, [email])
                logger.info("Password reset email sent to: %s", email)
                messages.success(request, "We have emailed your password reset link!")
                return redirect('customer_password_reset')
            else:
                return HttpResponse("Method not allowed", status=405)
        except Exception as e:
            logger.exception("Error in post_customer_email: %s", e)
            messages.error(request, "An error occurred.")
            return redirect('customer_password_reset')

    def show_reset_form(self, request: HttpRequest, token: Optional[str] = None) -> HttpResponse:
        try:
            default_language_obj = Utility.objects.filter(name='default_language').first()
            lang: str = default_language_obj.value if default_language_obj else 'en'
            context: Dict[str, Any] = {
                'token': token,
                'email': request.GET.get('email', ''),
                'lang': lang,
            }
            logger.info("Rendering password reset form for token: %s", token)
            return render(request, 'auth/passwords/reset.html', context)
        except Exception as e:
            logger.exception("Error in show_reset_form: %s", e)
            messages.error(request, "An error occurred.")
            return redirect('/')

    def get_customer_password(self, request: HttpRequest, token: str) -> HttpResponse:
        logger.info("Rendering customer password reset form for token: %s", token)
        try:
            return render(request, 'auth/passwords/customerReset.html', {'token': token})
        except Exception as e:
            logger.exception("Error in get_customer_password: %s", e)
            messages.error(request, "An error occurred.")
            return redirect('/')

    def update_customer_password(self, request: HttpRequest) -> HttpResponse:
        logger.info("Updating customer password")
        try:
            if request.method == 'POST':
                email: Optional[str] = request.POST.get('email')
                password: Optional[str] = request.POST.get('password')
                password_confirmation: Optional[str] = request.POST.get('password_confirmation')
                if (not email or not password or len(password) < 6 or password != password_confirmation):
                    logger.error("Invalid input for customer password update")
                    messages.error(request, "Invalid input.")
                    return redirect(request.path)
                try:
                    reset_entry = PasswordReset.objects.get(email=email, token=request.POST.get('token'))
                except PasswordReset.DoesNotExist:
                    logger.warning("Invalid token for customer password reset for email: %s", email)
                    messages.error(request, "Invalid token!")
                    return redirect(request.path)
                # Update customer password securely
                Customer.objects.filter(email=email).update(password=make_password(password))
                reset_entry.delete()
                logger.info("Customer password updated for email: %s", email)
                messages.success(request, "Your password has been changed.")
                return redirect('login')
            else:
                return HttpResponse("Method not allowed", status=405)
        except Exception as e:
            logger.exception("Error in update_customer_password: %s", e)
            messages.error(request, "An error occurred.")
            return redirect(request.path)

    # --- Vendor Password Reset ---
    def show_vendor_link_request_form(self, request: HttpRequest, lang: str = '') -> HttpResponse:
        if not lang:
            lang = Utility.get_value_by_name('default_language')
            logger.debug("Default language used for vendor password reset: %s", lang)
        try:
            return render(request, 'auth/passwords/vendorEmail.html', {'lang': lang})
        except Exception as e:
            logger.exception("Error displaying vendor password reset email form: %s", e)
            messages.error(request, "An error occurred.")
            return redirect('/')

    def post_vendor_email(self, request: HttpRequest) -> HttpResponse:
        logger.info("Processing vendor password reset email")
        try:
            if request.method == 'POST':
                email: Optional[str] = request.POST.get('email')
                if not email:
                    logger.error("Email not provided for vendor password reset")
                    messages.error(request, "Email is required.")
                    return redirect('vendor_password_reset')
                try:
                    Vendor.objects.get(email=email)
                except Vendor.DoesNotExist:
                    logger.warning("Vendor email not found: %s", email)
                    messages.error(request, "Email does not exist.")
                    return redirect('vendor_password_reset')
                token: str = self.generate_token(60)
                PasswordReset.objects.create(
                    email=email,
                    token=token,
                    created_at=timezone.now()
                )
                subject: str = "Reset Password Notification"
                message: str = render_to_string('auth/vendorVerify.html', {'token': token})
                from_email: str = settings.EMAIL_HOST_USER
                send_mail(subject, message, from_email, [email])
                logger.info("Password reset email sent to vendor: %s", email)
                messages.success(request, "We have emailed your password reset link!")
                return redirect('vendor_password_reset')
            else:
                return HttpResponse("Method not allowed", status=405)
        except Exception as e:
            logger.exception("Error in post_vendor_email: %s", e)
            messages.error(request, "An error occurred.")
            return redirect('vendor_password_reset')

    def get_vendor_password(self, request: HttpRequest, token: str) -> HttpResponse:
        logger.info("Rendering vendor password reset form for token: %s", token)
        try:
            return render(request, 'auth/passwords/vendorReset.html', {'token': token})
        except Exception as e:
            logger.exception("Error in get_vendor_password: %s", e)
            messages.error(request, "An error occurred.")
            return redirect('/')

    def update_vendor_password(self, request: HttpRequest) -> HttpResponse:
        logger.info("Updating vendor password")
        try:
            if request.method == 'POST':
                email: Optional[str] = request.POST.get('email')
                password: Optional[str] = request.POST.get('password')
                password_confirmation: Optional[str] = request.POST.get('password_confirmation')
                if (not email or not password or len(password) < 6 or password != password_confirmation):
                    logger.error("Invalid input for vendor password update")
                    messages.error(request, "Invalid input.")
                    return redirect(request.path)
                try:
                    reset_entry = PasswordReset.objects.get(email=email, token=request.POST.get('token'))
                except PasswordReset.DoesNotExist:
                    logger.warning("Invalid token for vendor password reset for email: %s", email)
                    messages.error(request, "Invalid token!")
                    return redirect(request.path)
                Vendor.objects.filter(email=email).update(password=make_password(password))
                reset_entry.delete()
                logger.info("Vendor password updated for email: %s", email)
                messages.success(request, "Your password has been changed.")
                return redirect('login')
            else:
                return HttpResponse("Method not allowed", status=405)
        except Exception as e:
            logger.exception("Error in update_vendor_password: %s", e)
            messages.error(request, "An error occurred.")
            return redirect(request.path)
