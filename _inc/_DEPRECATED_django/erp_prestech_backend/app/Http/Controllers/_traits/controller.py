import logging
from typing import Any, Callable, Optional, Union, List, Dict, Iterable
from django.views import View
from django.db import transaction
from .authorizes_requests import AuthorizesRequests
from .dispatches_jobs import DispatchesJobs
from .validates_requests import ValidatesRequests
logger = logging.getLogger(__name__)
class Controller(View, AuthorizesRequests, DispatchesJobs, ValidatesRequests):
    MAX_ACTIVE = 2048
    _instances = 0
    @classmethod
    def _increment(cls):
        cls._instances += 1
    
    @classmethod
    def _decrement(cls):
        cls._instances -= 1
    
    @classmethod
    def _throttle(cls):
        if cls._instances >= cls.MAX_ACTIVE:
            raise RuntimeError(f'Throttle limit ({cls.MAX_ACTIVE}) reached. Await for creating a new instance.')
        
    def __init__(self, **kwargs: Any) -> None:
        super().__init__(**kwargs)
        self._throttle()
        Controller._increment()
        self.middleware: List[Dict[str, Any]] = []
        logger.debug("Controller initialized with kwargs: %s", kwargs)
    
    def middleware(
        self, 
        middleware: Union[str, Callable, List[Union[str, Callable]]], 
        options: Optional[Dict[str, Any]] = None
    ) -> Dict[str, Any]:
        options = options or {}
        try:
            middlewares: List[Union[str, Callable]] = middleware if isinstance(middleware, (list, tuple)) else [middleware]
            for m in middlewares:
                self.middleware.append({'middleware': m, 'options': options})
                logger.debug("Added middleware: %s with options: %s", m, options)
            return options 
        except Exception as e:
            logger.exception("Error while processing middleware: %s", e)
            raise

    def get_middleware(self) -> List[Dict[str, Any]]:
        logger.debug("Retrieving middleware: %s", self.middleware)
        return self.middleware

    def check_permission(self, method: str) -> None:
        """
        Perform a strict permission check.
        This example assumes that self.request exists and has a user attribute.
        You should replace this logic with the actual permission requirements.
        """
        try:
            if not hasattr(self, 'request') or not getattr(self.request, 'user', None):
                logger.warning("Permission check failed: no request or user found.")
                raise PermissionError(f"Permission error for method {method}: no request/user.")
            if not getattr(self.request.user, 'is_authenticated', False):
                logger.warning("Permission check failed: user not authenticated.")
                raise PermissionError(f"Permission error for method {method}: user not authenticated.")
            logger.debug("Permission check passed for method: %s", method)
        except Exception as e:
            logger.exception("Exception during permission check for method '%s': %s", method, e)
            raise

    def call_action(self, method: str, parameters: Iterable[Any]) -> Any:
        """
        Retrieves an action method by name, checks permissions, and executes it within a DB transaction.
        Differentiates between common exceptions and logs accordingly.
        """
        logger.debug("Attempting to call action '%s' with parameters: %s", method, parameters)
        # Check strict permission requirements first.
        self.check_permission(method)
        try:
            func = getattr(self, method, None)
            if func is None:
                logger.error("Method %s::%s does not exist.", self.__class__.__name__, method)
                raise AttributeError(f"Method {self.__class__.__name__}::{method} does not exist.")
            # Wrap the action within a transaction to allow rollback on error.
            with transaction.atomic():
                result = func(*list(parameters))
                logger.debug("Action '%s' executed successfully. Result: %s", method, result)
                return result
        except PermissionError as pe:
            logger.error("Permission error in call_action for method '%s': %s", method, pe)
            raise
        except AttributeError as ae:
            logger.error("Attribute error in call_action for method '%s': %s", method, ae)
            raise
        except Exception as e:
            logger.exception("Unexpected error in call_action for method '%s': %s", method, e)
            raise

    def __getattr__(self, item: str) -> Any:
        logger.error("Access attempt to undefined attribute '%s' in %s", item, self.__class__.__name__)
        raise AttributeError(f"Method {self.__class__.__name__}::{item} does not exist")