import logging
from typing import Any
from django.shortcuts import redirect
from django.contrib import messages
from django.contrib.auth.mixins import LoginRequiredMixin
from django.utils.decorators import method_decorator
from django.views.decorators.csrf import csrf_exempt
from django.http import HttpRequest, HttpResponse
from .._helpers.http import get_redirect_url
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class EmailVerificationNotificationController(LoginRequiredMixin, Controller):
    """
    Django equivalent of Laravel's EmailVerificationNotificationController.
    Sends a new verification email to the user unless they are already verified.
    """

    @method_decorator(csrf_exempt)
    def post(self, request: HttpRequest, *args: Any, **kwargs: Any) -> HttpResponse:
        logger.info("EmailVerificationNotificationController.post() called for user: %s", request.user)
        try:
            user = request.user

            if getattr(user, 'is_email_verified', False):
                logger.info("User [%s] already verified. Redirecting to home.", user)
                return redirect('home')

            # If user has a dedicated verification method, use it.
            if hasattr(user, 'send_email_verification'):
                logger.info("Using user's send_email_verification() method for user: %s", user)
                user.send_email_verification()
            else:
                # Fallback: manually send the email
                logger.info("User [%s] does not have send_email_verification sending fallback email.", user)
                from django.core.mail import send_mail
                from django.template.loader import render_to_string

                verification_link = f"https://yourdomain.com/verify-email/{user.pk}/token"
                subject = "Verify your email"
                message = render_to_string('emails/verify_email.html', {
                    'user': user,
                    'verification_link': verification_link,
                })
                send_mail(subject, message, 'no-reply@yourdomain.com', [user.email])
                logger.debug("Fallback verification email sent to: %s", user.email)

            messages.success(request, 'Verification link sent.')
            logger.info("Verification process completed successfully for user: %s", user)
            return redirect(get_redirect_url(request, 'home'))
        except Exception as e:
            logger.exception("Error in EmailVerificationNotificationController.post(): %s", e)
            messages.error(request, 'An error occurred while sending the verification link.')
            return redirect(get_redirect_url(request, 'home'))
