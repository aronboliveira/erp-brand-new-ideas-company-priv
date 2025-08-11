from typing import Any
from django.http import HttpRequest
import logging
logger = logging.getLogger(__name__)
def get_redirect_url(request: HttpRequest, path: str = '/', add_data: Any = '') -> Any:
  logger.info(f'Redirecting...\n\nMetadata: {str(add_data)}')
  if isinstance(request, HttpRequest) and request.META.get('HTTP_REFERER'):
    return request.META.get('HTTP_REFERER', path)
  else:
    return '/'