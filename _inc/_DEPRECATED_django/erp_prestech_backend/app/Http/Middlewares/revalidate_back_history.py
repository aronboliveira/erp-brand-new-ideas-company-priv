from django.http import HttpRequest, HttpResponse;
from django.utils.deprecation import MiddlewareMixin;
from typing import Callable;

class RevalidateBackHistory(MiddlewareMixin):
  def __init__(self, get_response: Callable[[HttpRequest], HttpResponse]) -> None:
    self.get_response = get_response;
  def process_response(self, request: HttpRequest, response: HttpResponse) -> HttpResponse:
    try:
      response['Access-Control-Allow-Origin'] = '*';
      response['Access-Control-Allow-Methods'] = 'POST, GET, OPTIONS, PUT, DELETE';
      response['Access-Control-Allow-Headers'] = 'Content-Type, Accept, Authorization, X-Requested-With, Application';
    except Exception as e:
      print(f"[RevalidateBackHistory.process_response] Failed: {e.__class__.__name__}: {e}");
    return response;
