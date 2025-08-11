import inspect
import traceback
import logging
from typing import Tuple, Dict, Any, Optional, List, Union
from django.core.exceptions import PermissionDenied
from django.db import models, transaction
from ....Models.individuals.user import User

logger = logging.getLogger(__name__)


class AuthorizesRequests:
    def authorize(self, ability: str, arguments: Optional[List[Any]] = None) -> Union[bool, Exception]:
        C = self.__class__.__name__
        M = inspect.currentframe().f_code.co_name
        arguments = arguments or []
        logger.debug("%s::%s called with ability: '%s' and arguments: %s", C, M, ability, arguments)
        ability, arguments = self.parse_ability_and_arguments(ability, arguments)
        if not self.request.user.has_perm(ability):
            logger.error("%s::%s: User lacks permission '%s'", C, M, ability)
            raise PermissionDenied(f"User lacks permission: {ability}")
        logger.info("%s::%s: Authorization successful for ability '%s'", C, M, ability)
        return True

    def authorize_for_user(self, user: User, ability: str, arguments: Optional[List[Any]] = None) -> bool:
        C = self.__class__.__name__
        M = inspect.currentframe().f_code.co_name
        arguments = arguments or []
        logger.debug("%s::%s called for user '%s' with ability: '%s' and arguments: %s", C, M, user, ability, arguments)
        ability, arguments = self.parse_ability_and_arguments(ability, arguments)
        if not user.has_perm(ability):
            logger.error("%s::%s: User '%s' lacks permission '%s'", C, M, user, ability)
            raise PermissionDenied(f"User {user} lacks permission: {ability}")
        logger.info("%s::%s: Authorization successful for user '%s' with ability '%s'", C, M, user, ability)
        return True

    def parse_ability_and_arguments(self, ability: str, arguments: Any) -> Tuple[str, Any]:
        C = self.__class__.__name__
        M = inspect.currentframe().f_code.co_name
        logger.debug("%s::%s called with ability: '%s', arguments: %s", C, M, ability, arguments)
        try:
            if isinstance(ability, str) and '\\' not in ability:
                return ability, arguments
            stack = traceback.extract_stack()
            method = stack[-3].name if len(stack) >= 3 else M
            normalized_ability = self.normalize_guessed_ability_name(method)
            logger.debug("%s::%s: Ability '%s' normalized to '%s' based on calling method '%s'", C, M, ability, normalized_ability, method)
            return normalized_ability, ability
        except Exception as e:
            logger.exception("%s::%s: Error in parse_ability_and_arguments(): %s", C, M, e)
            raise

    def normalize_guessed_ability_name(self, ability: str) -> str:
        C = self.__class__.__name__
        M = inspect.currentframe().f_code.co_name
        logger.debug("%s::%s called with ability: '%s'", C, M, ability)
        try:
            mapping: Dict[str, str] = self.resource_ability_map()
            normalized = mapping.get(ability, ability)
            logger.debug("%s::%s: Normalized ability: '%s' to '%s'", C, M, ability, normalized)
            return normalized
        except Exception as e:
            logger.exception("%s::%s: Error in normalize_guessed_ability_name(): %s", C, M, e)
            raise

    def resource_ability_map(self) -> Dict[str, str]:
        C = self.__class__.__name__
        M = inspect.currentframe().f_code.co_name
        logger.debug("%s::%s called", C, M)
        try:
            return {
                'index': 'viewAny',
                'show': 'view',
                'create': 'create',
                'store': 'create',
                'edit': 'update',
                'update': 'update',
                'destroy': 'delete',
            }
        except Exception as e:
            logger.exception("%s::%s: Error in resource_ability_map(): %s", C, M, e)
            raise

    def resource_methods_without_models(self) -> Tuple[str, ...]:
        C = self.__class__.__name__
        M = inspect.currentframe().f_code.co_name
        logger.debug("%s::%s called", C, M)
        try:
            return ('index', 'create', 'store')
        except Exception as e:
            logger.exception("%s::%s: Error in resource_methods_without_models(): %s", C, M, e)
            raise

    def authorize_resource(
        self,
        model: Union[models.Model, List[str], Tuple[str, ...]],
        parameter: Optional[Union[str, List[str]]] = None,
        options: Optional[Dict[str, Any]] = None,
        request_obj: Optional[Any] = None
    ) -> None:
        C = self.__class__.__name__
        M = inspect.currentframe().f_code.co_name
        options = options or {}
        request_obj = request_obj or getattr(self, 'request', None)
        logger.debug("%s::%s called with model: %s, parameter: %s, options: %s, request_obj: %s", C, M, model, parameter, options, request_obj)
        try:
            with transaction.atomic():
                model_str = ','.join(model) if isinstance(model, (list, tuple)) else model
                parameter_str = ','.join(parameter) if isinstance(parameter, (list, tuple)) else (parameter or model_str.lower())
                middleware: Dict[str, List[str]] = {}
                for method, ability in self.resource_ability_map().items():
                    model_name = model_str if method not in self.resource_methods_without_models() else parameter_str
                    key = f"can:{ability},{model_name}"
                    middleware.setdefault(key, []).append(method)
                    logger.debug("%s::%s: Generated middleware key '%s' for method '%s'", C, M, key, method)
                for middleware_name, methods in middleware.items():
                    try:
                        self.middleware(middleware_name, options)
                        logger.info("%s::%s: Middleware '%s' applied to methods: %s", C, M, middleware_name, methods)
                    except Exception as e:
                        logger.exception("%s::%s: Error applying middleware '%s' to methods %s: %s", C, M, middleware_name, methods, e)
                        raise
                logger.info("%s::%s: Resource authorization completed for model '%s'", C, M, model_str)
        except PermissionDenied as pd:
            logger.error("%s::%s: Permission denied", C, M, exc_info=pd)
            raise
        except Exception as e:
            logger.exception("%s::%s: Critical undefined error: %s", C, M, e)
            raise
