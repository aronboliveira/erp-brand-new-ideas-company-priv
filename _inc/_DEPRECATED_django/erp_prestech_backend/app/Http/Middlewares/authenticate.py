from django.http import HttpRequest, HttpResponse;
from django.shortcuts import redirect;
from django.urls import reverse;
from typing import Callable;

class Authenticate:
  def __init__(self, get_response: Callable[[HttpRequest], HttpResponse]) -> None:
    self.get_response = get_response;
  def __call__(self, request: HttpRequest) -> HttpResponse:
    try:
      if not request.user.is_authenticated:
        return redirect(reverse('login')) if not self.request_expects_json(request) else self.get_response(request);
      return self.get_response(request);
    except Exception as e:
      print(f"[Authenticate.__call__] Failed to process request: {e.__class__.__name__}: {e}");
      return redirect(reverse('login'));
  def request_expects_json(self, request: HttpRequest) -> bool:
    try:
      accept_header = request.META.get('HTTP_ACCEPT', '');
      if 'application/json' in accept_header:
        return True;
      if request.headers.get('X-Requested-With', '') == 'XMLHttpRequest':
        return True;
      return False;
    except Exception as e:
      print(f"[Authenticate.request_expects_json] Failed to determine response type: {e.__class__.__name__}: {e}");
      return False;
