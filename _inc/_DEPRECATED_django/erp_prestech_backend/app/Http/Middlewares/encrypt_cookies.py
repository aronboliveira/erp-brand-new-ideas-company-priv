import logging;
from typing import Optional;
from django.core import signing;
from django.http import HttpRequest, HttpResponse;
from django.utils.deprecation import MiddlewareMixin;
logger = logging.getLogger(__name__);
class EncryptCookies(MiddlewareMixin):
  except_cookies = [];  # Uncomment to add cookie names to be exempt;
  def process_request(self, request: HttpRequest) -> Optional[HttpResponse]:
    try:
      decrypted_cookies = {};
      for key, value in request.COOKIES.items():
        if key not in self.except_cookies:
          try:
            decrypted_cookies[key] = signing.loads(value);
          except signing.BadSignature as bs:
            logger.warning("Failed to unsign cookie '%s': BadSignature", key);
            decrypted_cookies[key] = value;
          except Exception as e:
            logger.exception("Failed to unsign cookie '%s': %s", key, e);
            decrypted_cookies[key] = value;
        else:
          decrypted_cookies[key] = value;
      request.COOKIES = decrypted_cookies;
    except Exception as e:
      logger.exception("Error processing request cookies: %s", e);
    return None;
  def process_response(self, request: HttpRequest, response: HttpResponse) -> HttpResponse:
    try:
      for key, cookie in response.cookies.items():
        if key not in self.except_cookies:
          original_value = cookie.value;
          try:
            cookie.value = signing.dumps(original_value);
          except Exception as e:
            logger.exception("Failed to sign cookie '%s': %s", key, e);
            cookie.value = original_value;
      return response;
    except Exception as e:
      logger.exception("Error processing response cookies: %s", e);
      return response;
