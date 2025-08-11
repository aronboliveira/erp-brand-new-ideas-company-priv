import re;
from django.conf import settings;
from django.http import HttpRequest, HttpResponse;
from typing import Callable;

class PreventRequestsDuringMaintenance:
  def __init__(self, get_response: Callable[[HttpRequest], HttpResponse]) -> None:
    self.get_response = get_response;
    self.except_urls = getattr(settings, 'MAINTENANCE_EXCEPT_URLS', []);
  def __call__(self, request: HttpRequest) -> HttpResponse:
    try:
      if getattr(settings, 'MAINTENANCE_MODE', False):
        path = request.path_info.lstrip('/');
        if not any(re.fullmatch(pattern, path) for pattern in self.except_urls):
          return HttpResponse("Site under maintenance", status=503);
    except Exception as e:
      print(f"[PreventRequestsDuringMaintenance.__call__] Error: {e.__class__.__name__}: {e}");
    return self.get_response(request);
