from typing import Any, Union, Dict
from django.contrib import messages
from django.http import (HttpRequest, HttpResponseRedirect, 
                         HttpResponsePermanentRedirect, JsonResponse)
from django.shortcuts import redirect
from ....configs.messages_templates import get_exception_class_message
from .http import get_redirect_url
import logging
error_logger = logging.getLogger(__name__)
def default_permission_denial(request: HttpRequest,
                              err: Any,
                              ref: str = '#UNDEFINED_REFERENCE',
                              logger: logging.Logger = error_logger,
                              redirect_path: str = '/',
                              auto_redirect: bool = True,
                              json: Dict[str, Any] = None) -> Union[HttpResponseRedirect, 
                                                                   HttpResponsePermanentRedirect,
                                                                   JsonResponse,
                                                                   None]:
  from django.core.exceptions import PermissionDenied
  logger.error(f'{ref} denied permission: {err}')
  messages.error(request, get_exception_class_message(PermissionDenied, ref))
  if json:
    return JsonResponse({'error': 'Permission denied.'}, status=401)
  return redirect(get_redirect_url(request, redirect_path)) if auto_redirect else None

def default_undefined_exception(request: HttpRequest,
                                err: Any,
                                ref: str = '#UNDEFINED_REFERENCE',
                                logger: logging.Logger = error_logger,
                                redirect_path: str = '/',
                                auto_redirect: bool = True,
                                json: Dict[str, Any] = None,
                                status: int = 500) -> Union[HttpResponseRedirect,
                                                                     HttpResponsePermanentRedirect,
                                                                     JsonResponse,
                                                                     None]:
  logger.error(f'{ref} raised an undefined error: {err}')
  messages.error(request, f'An undefined error was raised: {err}')
  if json:
    return JsonResponse({'error': json}, status=status)
  return redirect(get_redirect_url(request, redirect_path)) if auto_redirect else None
