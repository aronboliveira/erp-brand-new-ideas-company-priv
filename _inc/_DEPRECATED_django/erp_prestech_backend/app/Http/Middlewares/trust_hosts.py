import logging;
import re;
from typing import Optional;
from django.conf import settings;
from django.http import HttpRequest, HttpResponse;
from django.utils.deprecation import MiddlewareMixin;
logger = logging.getLogger(__name__);
class TrustHosts(MiddlewareMixin):
  def process_request(self, request: HttpRequest) -> Optional[HttpResponse]:
    try:
      base_domain: str = getattr(settings, 'APPLICATION_DOMAIN', 'example.com');
      host: str = request.get_host().split(':')[0].lower();
      if host == base_domain or host.endswith(f".{base_domain}"):
        pass;
      else:
        logger.warning("TrustHosts: Untrusted host: %s", host);
      return None;
    except Exception as e:
      logger.exception("TrustHosts.process_request failed: %s", e);
      return None;
