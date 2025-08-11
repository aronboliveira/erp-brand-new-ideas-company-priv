from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.shortcuts import get_object_or_404, redirect, render
from .._traits.controller import Controller
from ....Models.planning.goal import Goal
import inspect
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
import logging
logger = logging.getLogger(__name__)
class GoalController(Controller):
  
    @staticmethod
    def _set_goal(request):
        data = request.POST
        fields = ('name','type','from','to','amount')
        missing = [f for f in fields if not data.get(f)]
        if missing:
          messages.error(request,f"{missing[0]} is required")
          return redirect(get_redirect_url(request))
        g_attrs = {}
        for k in ('name', 'type', 'amount'):
          setattr(g_attrs, k, data.get(k))
        for k in ('from', 'to'):
          setattr(g_attrs, f'{k}_date', data.get(k))
        g_attrs.is_display = 1 if data.get('is_display') else 0
        g_attrs.created_by = request.user.creator_id()
        goal = Goal(**g_attrs)
        goal.save()

    @classmethod
    def index(cls, request):
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            inst = cls()
            inst.request = request
            inst.authorize('manage goal')
            goals = Goal.objects.filter(created_by=request.user.creator_id())
            return render(request, 'goal/index.html', {'goals': goals})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def create(cls, request):
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            inst = cls()
            inst.request = request
            inst.authorize('create goal')
            types = Goal.type
            return render(request, 'goal/create.html', {'types': types})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def store(cls, request):
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            inst = cls()
            inst.request = request
            inst.authorize('create goal')
            GoalController._set_goal(request)
            messages.success(request, 'Goal successfully created.')
            return redirect('goal_index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def show(cls, request, goal_id):
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            inst = cls()
            inst.request = request
            inst.authorize('view goal')
            goal = get_object_or_404(Goal, pk=goal_id)
            return render(request, 'goal/show.html', {'goal': goal})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def edit(cls, request, goal_id):
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            inst = cls()
            inst.request = request
            inst.authorize('create goal')
            goal = get_object_or_404(Goal, pk=goal_id)
            types = Goal.type  # TODO verify goal_type attribute
            return render(request, 'goal/edit.html', {'types': types, 'goal': goal})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def update(cls, request, goal_id):
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            inst = cls()
            inst.request = request
            inst.authorize('edit goal')
            goal = get_object_or_404(Goal, pk=goal_id)
            if goal.created_by != request.user.creator_id():
                messages.error(request, 'Permission denied.')
                return redirect(request.META.get('HTTP_REFERER', '/'))
            GoalController._set_goal(request)
            messages.success(request, 'Goal successfully updated.')
            return redirect('goal_index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def destroy(cls, request, goal_id):
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            inst = cls()
            inst.request = request
            inst.authorize('delete goal')
            goal = get_object_or_404(Goal, pk=goal_id)
            if goal.created_by != request.user.creator_id():
                messages.error(request, 'Permission denied.')
                return redirect(request.META.get('HTTP_REFERER', '/'))
            goal.delete()
            messages.success(request, 'Goal successfully deleted.')
            return redirect('goal_index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)