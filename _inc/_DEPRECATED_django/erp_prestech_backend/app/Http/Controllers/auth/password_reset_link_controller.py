import datetime
import json
import os
from django.conf import settings
from django.contrib import messages
from django.contrib.auth.forms import PasswordResetForm
from django.http import HttpRequest, HttpResponse
from django.shortcuts import redirect
from ....Models.utils.utility import Utility
from .._traits.controller import Controller

class PasswordResetLinkController(Controller):
  def get(self, request: HttpRequest, *args, **kwargs) -> HttpResponse:
    # TODO: Add additional logic if needed
    return HttpResponse(status=200)
  def post(self, request: HttpRequest, *args, **kwargs) -> HttpResponse:
    if getattr(settings, 'RECAPTCHA_MODULE', 'off') == 'on':
      recaptcha_response = request.POST.get('g-recaptcha-response')
      if not recaptcha_response:
        messages.error(request, "Please complete the reCAPTCHA.")
        return redirect('password_reset_link')
    email = request.POST.get('email')
    if not email:
      messages.error(request, "Email is required.")
      return redirect('password_reset_link')
    try:
      Utility.smtpDetail(1)
      form = PasswordResetForm({'email': email})
      messages.success(request, "Reset link sent.") if form.is_valid() else messages.error(request, "Error: " + str(form.errors))
      if form.is_valid():
        form.save(request=request, use_https=request.is_secure(), from_email=settings.EMAIL_HOST_USER)
      return redirect('password_reset_link')
    except Exception as e:
      # TODO: Add logging: Failed to POST for password reset: {e}
      messages.error(request, "E-Mail has not been sent due to SMTP configuration")
      return redirect('password_reset_link')
