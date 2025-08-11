from typing import Any, List, Union, Dict
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.db.models import BaseManager
from django.http import HttpRequest
from ....configs.messages_templates import get_exception_class_message
from ....Models.individuals.user import User
import logging
from logging import Logger
security_logger = logging.getlogger(__name__)
def permission_required_custom(perm: str, logger: Logger):
    """
    Custom decorator to check if the current user holds a particular permission.
    If not, an error message is flashed, the issue is logged, and PermissionDenied is raised.
    """
    def decorator(view_func):
        def _wrapped_view(request: HttpRequest, *args, **kwargs):
            if not request.user.has_perm(perm):
                messages.error(request, get_exception_class_message(PermissionDenied))
                logger.warning("User %s lacks permission: %s", request.user, perm)
                raise PermissionDenied("Permission Denied by Permission Requirement")
            return view_func(request, *args, **kwargs)
        return _wrapped_view
    return decorator

def email_validation(email: str, manager: BaseManager[Any], excludes: Union[Dict[str, Any], None]) -> List[str]:
    from django.core.exceptions import ValidationError
    from django.core.validators import validate_email
    errors = []
    try:
        validate_email(email)
    except ValidationError:
        security_logger.warn(f'Email validation for {email} failed')
        errors.append('Invalid email address.')
    clamped = manager.filter(email=email).exclude(**excludes) if excludes and excludes.items() else manager.filter(email=email)
    if email and clamped.exists():
        errors.append('A lead with this email already exists.')
    return errors

def user_id_validation(data: dict, manager: Union[BaseManager[Any], str], filters: Dict[Any]) -> List[str]:
    errors = []
    uid = data.get('user_id')
    if not uid:
        errors.append('Assignee is required.')
    else:
        try:
            manager = User.objects if manager == 'users' else manager
            if isinstance(manager, User):
                if not manager.filter(pk=uid, **filters).exists():
                    errors.append('Selected user is invalid.')
            else:
                if not manager.filter(user_id=uid, **filters).exists():
                    errors.append('Selected user is invalid')
        except ValueError as e:
            errors.append(f'Assignee was invalidated: {e}')
        except Exception as e:
            errors.append(f'As unkown error occurred while attempting to validate the user id: {e}')
    if errors:
        security_logger.warn(f'Attemp to validate user with id {uid or '## UNDEFINED ID'} failed')
    return errors