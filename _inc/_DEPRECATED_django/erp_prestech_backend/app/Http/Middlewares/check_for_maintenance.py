import logging;
from typing import Optional;
from django.conf import settings;
from django.http import HttpResponse;
from django.utils.deprecation import MiddlewareMixin;

logger = logging.getLogger(__name__);

class CheckForMaintenanceMode(MiddlewareMixin):
  exempt_urls = getattr(settings, 'MAINTENANCE_EXEMPT_URLS', []);
  def process_request(self, request: HttpResponse) -> Optional[HttpResponse]:
    try:
      if getattr(settings, 'MAINTENANCE_MODE', False):
        if not any(request.path.startswith(url) for url in self.exempt_urls):
          logger.info("Maintenance mode active; denying access to %s", request.path);
          return HttpResponse("Site is under maintenance", status=503);
      return None;
    except Exception as e:
      logger.exception("Failed to process request in maintenance middleware: %s", e);
      return HttpResponse("An error occurred", status=500);
