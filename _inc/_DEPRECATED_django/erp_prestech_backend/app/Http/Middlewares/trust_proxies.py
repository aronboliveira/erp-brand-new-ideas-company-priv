import logging;
import re;
from django.http import HttpRequest, HttpResponse;
from django.utils.deprecation import MiddlewareMixin;
  
logger = logging.getLogger(__name__);
  
class TrustProxies(MiddlewareMixin):
  def __init__(self, get_response) -> None:
    super().__init__(get_response);
    self.trusted_proxies: list[str] = [];
  
  def process_request(self, request: HttpRequest) -> HttpResponse | None:
    try:
      remote_addr: str = request.META.get('REMOTE_ADDR', '');
      if self.is_trusted_proxy(remote_addr):
        x_forwarded_for: str = request.META.get('HTTP_X_FORWARDED_FOR', '');
        if x_forwarded_for:
          ips = [ip.strip() for ip in x_forwarded_for.split(',')];
          request.META['REMOTE_ADDR'] = ips[0] if ips else remote_addr;
        x_forwarded_proto: str = request.META.get('HTTP_X_FORWARDED_PROTO', '');
        if x_forwarded_proto:
          request.is_secure = lambda: x_forwarded_proto == 'https';
      return None;
    except Exception as e:
      logger.error("Failed in TrustProxies.process_request: %s", e);
      return None;
  
  def is_trusted_proxy(self, ip: str) -> bool:
    return True;
