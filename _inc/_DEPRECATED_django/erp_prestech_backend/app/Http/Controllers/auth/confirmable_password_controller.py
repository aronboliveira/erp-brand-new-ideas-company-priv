from .._traits.controller import Controller
from django.contrib import messages
from django.contrib.auth.mixins import LoginRequiredMixin
from django.http import HttpRequest, HttpResponse
from django.shortcuts import render, redirect
from django.utils import timezone
import logging
from typing import Any

logger = logging.getLogger(__name__)

class ConfirmablePasswordController(LoginRequiredMixin, Controller):
  """
  A Django view equivalent to the Laravel ConfirmablePasswordController.
  It shows the confirm password form and validates the user's password.
  """
  
  def get(self, request: HttpRequest, *args: Any,
          **kwargs: Any) -> HttpResponse:
    try:
      response = render(request, 'auth/confirm-password.html')
      logger.info("GET confirm password page rendered successfully")
      return response
    except Exception as e:
      logger.error("Failed GET for confirm password: %s", e)
      messages.error(request, "An error occurred, please try again")
      return render(request, 'auth/confirm-password.html', status=500)
  
  def post(self, request: HttpRequest, *args: Any,
           **kwargs: Any) -> HttpResponse:
    try:
      user = request.user
      password = request.POST.get('password')
      if not password:
        messages.error(request, "Password is required")
        logger.error("POST confirm password: Password not provided")
        return render(request, 'auth/confirm-password.html', status=400)
      if not user.check_password(password):
        messages.error(request,
                       "These credentials do not match our records")
        logger.error("Failed POST for confirm password: "
                     "Invalid password for user %s", user.username)
        return render(request, 'auth/confirm-password.html', status=422)
      request.session['auth.password_confirmed_at'] = int(
        timezone.now().timestamp())
      logger.info("POST confirm password: Password confirmed for user %s",
                  user.username)
      return redirect('home')
    except Exception as e:
      logger.error("Failed POST for confirm password: %s", e)
      messages.error(request, "An error occurred, please try again")
      return render(request, 'auth/confirm-password.html', status=500)
