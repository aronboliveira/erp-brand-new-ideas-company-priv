import logging;
from django.conf import settings;
from django.shortcuts import redirect;
from django.http import HttpRequest, HttpResponse;
logger = logging.getLogger(__name__);
class RedirectIfAuthenticated:
  def __init__(self, get_response: callable) -> None:
    self.get_response = get_response;
    self.home_url: str = getattr(settings, 'HOME_URL', '/');
  def __call__(self, request: HttpRequest) -> HttpResponse:
    try:
      return redirect(self.home_url) if request.user.is_authenticated else self.get_response(request);
    except Exception as e:
      logger.exception("RedirectIfAuthenticated __call__ failed: %s", e);
      return self.get_response(request);
