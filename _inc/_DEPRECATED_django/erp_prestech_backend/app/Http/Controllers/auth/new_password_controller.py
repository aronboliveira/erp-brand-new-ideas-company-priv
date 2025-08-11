import logging
from django.shortcuts import render, redirect
from django.contrib import messages
from django.contrib.auth import get_user_model
from django.contrib.auth.tokens import default_token_generator
from django.core.exceptions import ValidationError
from django.core.validators import validate_email
from django.db import transaction
from django.http import HttpRequest, HttpResponse

User = get_user_model()

from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class NewPasswordController(Controller):
    @staticmethod
    def create(request: HttpRequest) -> HttpResponse:
        """
        Display the password reset view.
        """
        logger.info("NewPasswordController.create() called")
        try:
            return render(request, 'auth/passwords/reset.html', {'request': request})
        except Exception as e:
            logger.exception("Error in NewPasswordController.create: %s", e)
            messages.error(request, "An error occurred while loading the password reset page.")
            return redirect('password_reset_create')

    @staticmethod
    def store(request: HttpRequest) -> HttpResponse:
        """
        Handle an incoming new password request.
        Validates input, checks the reset token, updates the password,
        and redirects with a status message.
        """
        logger.info("NewPasswordController.store() called, request method: %s", request.method)
        try:
            if request.method == 'POST':
                token: str = request.POST.get('token', '')
                email: str = request.POST.get('email', '')
                password: str = request.POST.get('password', '')
                password_confirmation: str = request.POST.get('password_confirmation', '')
                
                # Input validations.
                if not token:
                    messages.error(request, "Token is required.")
                    logger.warning("Token missing in password reset request.")
                    return redirect(request.path)
                if not email:
                    messages.error(request, "Email is required.")
                    logger.warning("Email missing in password reset request.")
                    return redirect(request.path)
                try:
                    validate_email(email)
                except ValidationError:
                    messages.error(request, "Invalid email address.")
                    logger.warning("Email validation failed for: %s", email)
                    return redirect(request.path)
                if not password:
                    messages.error(request, "Password is required.")
                    logger.warning("Password missing in password reset request.")
                    return redirect(request.path)
                if password != password_confirmation:
                    messages.error(request, "Passwords do not match.")
                    logger.warning("Password confirmation does not match for email: %s", email)
                    return redirect(request.path)
                
                # Retrieve the user by email.
                try:
                    user = User.objects.get(email=email)
                except User.DoesNotExist:
                    messages.error(request, "User does not exist.")
                    logger.warning("User lookup failed for email: %s", email)
                    return redirect(request.path)
                
                # Verify token validity.
                if not default_token_generator.check_token(user, token):
                    messages.error(request, "Invalid or expired token.")
                    logger.warning("Token verification failed for email: %s", email)
                    return redirect(request.path)
                
                # Update password within a transaction for atomicity.
                with transaction.atomic():
                    user.set_password(password)
                    user.save()
                    logger.info("Password updated successfully for user: %s", email)
                
                messages.success(request, "Password reset successfully. Please login.")
                return redirect('login')
            else:
                logger.warning("NewPasswordController.store() received a non-POST request.")
                return redirect('password_reset_create')
        except Exception as e:
            logger.exception("Error in NewPasswordController.store: %s", e)
            messages.error(request, "An error occurred while resetting your password.")
            return redirect(request.path)
