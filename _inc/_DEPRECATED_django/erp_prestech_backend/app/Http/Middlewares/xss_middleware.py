import re
from django.http import HttpRequest, HttpResponse
from django.shortcuts import redirect
from django.urls import reverse
from django.utils import translation
from django.utils.deprecation import MiddlewareMixin
from typing import Callable, Optional

class XSSMiddleware(MiddlewareMixin):
  def __init__(self, get_response: Callable[[HttpRequest], HttpResponse]) -> None:
    self.get_response = get_response
  def process_request(self, request: HttpRequest) -> Optional[HttpResponse]:
    try:
      if request.user.is_authenticated:
        user_lang = getattr(request.user, 'lang', None)
        if user_lang:
          translation.activate(user_lang)
          request.LANGUAGE_CODE = user_lang
        if getattr(request.user, 'type', None) == 'super admin':
          migrations_ok = self.check_migrations()
          if not migrations_ok:
            return redirect(reverse('django_updater_welcome'))
      self.strip_tags_from_request(request)
    except Exception as e:
      print(f"[XSSMiddleware.process_request] Error: {e.__class__.__name__}: {e}")
    return None
  def check_migrations(self) -> bool:
    try:
      return True
    except Exception as e:
      print(f"[XSSMiddleware.check_migrations] Error: {e.__class__.__name__}: {e}")
      return True
  def strip_tags_from_request(self, request: HttpRequest) -> None:
    try:
      if request.method == 'POST':
        cleaned_data = {key: re.sub(r'<[^>]*>', '', value) for key, value in request.POST.items()}
        request.POST = request.POST.copy()
        for k, v in cleaned_data.items():
          request.POST[k] = v
    except Exception as e:
      print(f"[XSSMiddleware.strip_tags_from_request] Error: {e.__class__.__name__}: {e}")
