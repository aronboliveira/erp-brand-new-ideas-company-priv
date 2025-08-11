import inspect
import logging
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse
from django.shortcuts import render, redirect, get_object_or_404
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._helpers.http import get_redirect_url
from ....Models.planning.goal_type import GoalType
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class GoalTypeController(Controller):

    @classmethod
    def _set_goal_type(cls, instance: GoalType, **attrs) -> GoalType:
        for field, value in attrs.items():
            setattr(instance, field, value)
        instance.save()
        return instance

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if not request.user.has_perm('manage_goal_type'):
                raise PermissionDenied('manage_goal_type')
            types = GoalType.objects.filter(created_by=request.user.creator_id())
            return render(request, 'goaltype/index.html', {'goaltypes': types})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if not request.user.has_perm('create_goal_type'):
                raise PermissionDenied('create_goal_type')
            return render(request, 'goaltype/create.html')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def store(cls, request: HttpRequest) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if not request.user.has_perm('create_goal_type'):
                raise PermissionDenied('create_goal_type')
            name = request.POST.get('name')
            if not name:
                messages.error(request, 'Name is required')
                return redirect(get_redirect_url(request))
            creator_id = request.user.creator_id()
            cls._set_goal_type(GoalType(), name=name, created_by=creator_id)
            messages.success(request, 'Goal type created successfully')
            return redirect('goaltype.index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def show(cls, request: HttpRequest, id: int) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if not request.user.has_perm('view_goal_type'):
                raise PermissionDenied('view_goal_type')
            gt = get_object_or_404(GoalType, pk=id)
            return render(request, 'goaltype/show.html', {'goal_type': gt})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except GoalType.DoesNotExist as e:
            logger.error(f"{REF} not found: {e}")
            messages.error(request, 'Goal type not found')
            return redirect('goaltype.index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def edit(cls, request: HttpRequest, id: int) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if not request.user.has_perm('edit_goal_type'):
                raise PermissionDenied('edit_goal_type')
            gt = get_object_or_404(GoalType, pk=id)
            return render(request, 'goaltype/edit.html', {'goal_type': gt})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except GoalType.DoesNotExist as e:
            logger.error(f"{REF} not found: {e}")
            messages.error(request, 'Goal type not found')
            return redirect('goaltype.index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def update(cls, request: HttpRequest, id: int) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if not request.user.has_perm('edit_goal_type'):
                raise PermissionDenied('edit_goal_type')
            name = request.POST.get('name')
            if not name:
                messages.error(request, 'Name is required')
                return redirect(get_redirect_url(request))
            creator_id = request.user.creator_id()
            gt = get_object_or_404(GoalType, pk=id)
            cls._set_goal_type(gt, name=name, created_by=creator_id)
            messages.success(request, 'Goal type updated successfully')
            return redirect('goaltype.index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except GoalType.DoesNotExist as e:
            logger.error(f"{REF} not found: {e}")
            messages.error(request, 'Goal type not found')
            return redirect('goaltype.index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def destroy(cls, request: HttpRequest, id: int) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if not request.user.has_perm('delete_goal_type'):
                raise PermissionDenied('delete_goal_type')
            gt = get_object_or_404(GoalType, pk=id)
            if gt.created_by != request.user.creator_id():
                raise PermissionDenied('delete_goal_type')
            gt.delete()
            messages.success(request, 'Goal type deleted successfully')
            return redirect('goaltype.index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except GoalType.DoesNotExist as e:
            logger.error(f"{REF} not found: {e}")
            messages.error(request, 'Goal type not found')
            return redirect('goaltype.index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)
