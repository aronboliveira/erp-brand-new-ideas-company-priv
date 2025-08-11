import logging;
import re;
from typing import Optional;
from django.conf import settings;
from django.http import HttpRequest, HttpResponse;
from django.middleware.csrf import CsrfViewMiddleware;
logger = logging.getLogger(__name__);
class VerifyCsrfToken(CsrfViewMiddleware):
  EXEMPT_PATHS = [
    r'^plan/paytm/',
    r'^customer/paytm/',
    r'^plan-pay-with-paymentwall/',
    r'^invoice-pay-with-paymentwall/',
    r'^iyzipay/callback/',
    r'^paytab-success/',
    r'^aamarpay',
  ];
  def process_view(
    self,
    request: HttpRequest,
    callback: callable,
    callback_args: list,
    callback_kwargs: dict
  ) -> Optional[HttpResponse]:
    try:
      path: str = request.path_info.lstrip('/');
      for pattern in self.EXEMPT_PATHS:
        if re.match(pattern, path):
          return None;
      return super().process_view(request, callback, callback_args, callback_kwargs);
    except Exception as e:
      logger.exception(f"VerifyCsrfToken.process_view failed: {e}");
      return super().process_view(request, callback, callback_args, callback_kwargs);
