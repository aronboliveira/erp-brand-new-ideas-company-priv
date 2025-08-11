import logging
import traceback
from django.http import JsonResponse
from django.core.exceptions import ValidationError, PermissionDenied
from django.http import Http404
from django.conf import settings

logger = logging.getLogger(__name__)

class ExceptionHandler:
  def __init__(self):
    self.dont_report = (ValidationError, Http404)

  def handle_request(self, request, view_func, *args, **kwargs):
    try:
      return view_func(request, *args, **kwargs)

    except ValidationError as e:
      self.report(e, request)
      return self.validation_error_response(e)

    except PermissionDenied as e:
      self.report(e, request)
      return self.permission_denied_response(e)

    except Http404 as e:
      self.report(e, request)
      return self.not_found_response(e)

    except Exception as e:
      self.report(e, request)
      return self.internal_server_error_response(e)

  def report(self, exception, request):
    if isinstance(exception, self.dont_report):
      return
    logger.error(f"Unhandled exception: {str(exception)}", exc_info=True)

  def validation_error_response(self, exception):
    return JsonResponse({
      'error': 'Validation error',
      'details': exception.message_dict if hasattr(exception, 'message_dict') else str(exception)
    }, status=400)

  def permission_denied_response(self, exception):
    return JsonResponse({
      'error': 'Permission denied'
    }, status=403)

  def not_found_response(self, exception):
    return JsonResponse({
      'error': 'Resource not found'
    }, status=404)

  def internal_server_error_response(self, exception):
    if settings.DEBUG:
      return JsonResponse({
        'error': str(exception),
        'traceback': traceback.format_exc()
      }, status=500)
    return JsonResponse({
      'error': 'An unexpected error occurred'
    }, status=500)
