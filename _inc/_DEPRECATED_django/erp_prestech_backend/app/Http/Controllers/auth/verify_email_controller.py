from django.contrib.auth.mixins import LoginRequiredMixin
from django.dispatch import Signal
from django.http import HttpRequest, HttpResponse
from django.shortcuts import redirect
from django.urls import reverse
from typing import Any
from .._traits.controller import Controller

email_verified = Signal(providing_args=["user"])

class VerifyEmailController(LoginRequiredMixin, Controller):

  def get(self, request: HttpRequest, *args: Any, **kwargs: Any) -> HttpResponse:
    user = request.user
    if getattr(user, "is_email_verified", False):
      return redirect(reverse("home") + "?verified=1")
    try:
      if user.mark_email_as_verified():
        email_verified.send(sender=user.__class__, user=user)
    except Exception as e:
      print(f"Failed to mark email as verified: {e.__class__.__name__}: {e}")
    return redirect(reverse("home") + "?verified=1")
