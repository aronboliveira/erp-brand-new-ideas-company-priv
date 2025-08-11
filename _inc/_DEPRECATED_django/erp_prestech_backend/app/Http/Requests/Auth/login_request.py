import math
from django import forms
from django.contrib.auth import authenticate
from django.core.cache import cache
from django.core.exceptions import ValidationError
from django.utils.translation import gettext as _
from typing import Any, Dict
class LoginRequest(forms.Form):
  email: forms.EmailField = forms.EmailField(required=True)
  password: forms.CharField = forms.CharField(widget=forms.PasswordInput, required=True)
  remember: forms.BooleanField = forms.BooleanField(required=False)
  RATE_LIMIT_MAX_ATTEMPTS: int = 5
  RATE_LIMIT_TIMEOUT: int = 60  # seconds
  def __init__(self, *args: Any, **kwargs: Any) -> None:
    self.request = kwargs.pop('request', None)
    super().__init__(*args, **kwargs)
    self.user: Any = None
  def get_throttle_key(self) -> str:
    email: str = (self.cleaned_data.get('email', '').lower() if 'email' in self.cleaned_data else '')
    ip: str = (self.request.META.get('REMOTE_ADDR', '') if self.request else '')
    return f"login_attempts_{email}|{ip}"
  def ensure_is_not_rate_limited(self) -> None:
    key: str = self.get_throttle_key()
    attempts: int = cache.get(key, 0)
    if attempts >= self.RATE_LIMIT_MAX_ATTEMPTS:
      seconds_left: int = (cache.ttl(key) if hasattr(cache, 'ttl') else self.RATE_LIMIT_TIMEOUT)
      minutes_left: int = math.ceil(seconds_left / 60)
      raise ValidationError({'email': _(f"Too many login attempts. Please try again in {seconds_left} seconds ({minutes_left} minutes).")})
  def increment_attempts(self) -> None:
    key: str = self.get_throttle_key()
    attempts: int = cache.get(key, 0)
    cache.set(key, 1, self.RATE_LIMIT_TIMEOUT) if attempts == 0 else cache.incr(key)
  def clear_attempts(self) -> None:
    key: str = self.get_throttle_key()
    cache.delete(key)
  def clean(self) -> Dict[str, Any]:
    self.ensure_is_not_rate_limited()
    cleaned_data: Dict[str, Any] = super().clean()
    email: Any = cleaned_data.get('email')
    password: Any = cleaned_data.get('password')
    if email and password:
      user = authenticate(username=email, password=password)
      if user is None:
        self.increment_attempts()
        raise ValidationError({'email': _("These credentials do not match our records.")})
      else:
        self.user = user
        self.clear_attempts()
    return cleaned_data
