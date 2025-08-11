from django.contrib.auth.mixins import LoginRequiredMixin
from django.http import HttpRequest, HttpResponse
from django.shortcuts import redirect, render
from django.utils import translation
from typing import Any
from ....Models.utils.utility import Utility
from .._traits.controller import Controller

class EmailVerificationPromptController(LoginRequiredMixin, Controller):

  def get(self, request: HttpRequest, *args: Any, **kwargs: Any) -> HttpResponse:
    return redirect('home') if getattr(request.user, 'is_email_verified', False) else render(request, 'auth/verify.html')

  def show_verify_form(self, request: HttpRequest, lang: str = '') -> HttpResponse:
    if not lang:
      try:
        lang = Utility.get_value_by_name('default_language') or 'en'
      except Exception as e:
        print(f'Failed to GET default language: {e.__class__.__name__}: {e}')
        lang = 'en'
    try:
      translation.activate(lang)
    except Exception as e:
      print(f'Failed to ACTIVATE translation for lang {lang}: {e.__class__.__name__}: {e}')
    return render(request, 'auth/verify.html', {'lang': lang})
