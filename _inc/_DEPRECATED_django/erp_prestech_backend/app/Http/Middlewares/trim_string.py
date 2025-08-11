import re;
from django.http import HttpRequest, HttpResponse;
from django.utils.deprecation import MiddlewareMixin;

class TrimStringsMiddleware(MiddlewareMixin):
  except_fields: list = ['current_password', 'password', 'password_confirmation'];
  def process_request(self, request: HttpRequest) -> HttpResponse | None:
    if request.method == 'POST':
      post_copy = request.POST.copy();
      for field in post_copy:
        if field not in self.except_fields:
          if isinstance(post_copy[field], str):
            post_copy[field] = post_copy[field].strip();
      request.POST = post_copy;
    return None;
