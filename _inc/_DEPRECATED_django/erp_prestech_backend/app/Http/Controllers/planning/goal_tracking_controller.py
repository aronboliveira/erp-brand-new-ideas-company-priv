import logging
from django.db import transaction
from django.shortcuts import render, redirect, get_object_or_404
from django.contrib import messages
from django.http import HttpRequest, HttpResponse
from ....Models.companies.branch import Branch
from ....Models.individuals.employee import Employee
from ....Models.planning.goal_tracking import GoalTracking
from ....Models.planning.goal_type import GoalType
from .._traits.controller import Controller
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
import inspect

logger = logging.getLogger(__name__)

class GoalTrackingController(Controller):

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            return cls.__index_impl(request)
        except Exception as ex:
            return default_undefined_exception(request, err=ex, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def __index_impl(cls, request: HttpRequest) -> HttpResponse:
        if not request.user.has_perm('manage_goal_tracking'):
            return default_permission_denial(request, err=None, ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}', logger=logger)
        user = request.user
        if user.type == 'Employee':
            emp = Employee.objects.filter(user_id=user.id).first()
            qs = GoalTracking.objects.filter(created_by=user.id, branch=emp.branch_id)
        else:
            qs = GoalTracking.objects.filter(created_by=user.id)
        goal_trackings = qs.select_related('goal_type', 'branches')
        return render(request, 'goaltracking/index.html', {'goal_trackings': goal_trackings})

    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            return cls.__create_impl(request)
        except Exception as ex:
            return default_undefined_exception(request, err=ex, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def __create_impl(cls, request: HttpRequest) -> HttpResponse:
        if not request.user.has_perm('create_goal_tracking'):
            return default_permission_denial(request, err=None, ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}', logger=logger)
        branches = Branch.objects.filter(created_by=request.user.id).values_list('id', 'name')
        goal_types = GoalType.objects.filter(created_by=request.user.id).values_list('id', 'name')
        status = GoalTracking.status
        return render(request, 'goaltracking/create.html', {
            'branches': branches,
            'goal_types': goal_types,
            'status': status,
        })

    @classmethod
    def store(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            with transaction.atomic():
                return cls.__store_impl(request)
        except Exception as ex:
            return default_undefined_exception(request, err=ex, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def __store_impl(cls, request: HttpRequest) -> HttpResponse:
        if not request.user.has_perm('create_goal_tracking'):
            return default_permission_denial(request, err=None, ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}', logger=logger)
        data = request.POST
        required = ('branch', 'goal_type', 'start_date', 'end_date', 'subject')
        if any(not data.get(f) for f in required):
            messages.error(request, 'Missing required fields.')
            return redirect('/')
        gt = GoalTracking()
        cls._set_goal_tracking(gt, data, request.user.id)
        gt.save()
        messages.success(request, 'Goal tracking successfully created.')
        return redirect('/')

    @classmethod
    def show(cls, request: HttpRequest, pk: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            gt = get_object_or_404(GoalTracking, pk=pk)
            return render(request, 'goaltracking/show.html', {'goal_tracking': gt})
        except Exception as ex:
            return default_undefined_exception(request, err=ex, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def edit(cls, request: HttpRequest, pk: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit_goal_tracking'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            gt = get_object_or_404(GoalTracking, pk=pk)
            branches = Branch.objects.filter(created_by=request.user.id).values_list('id', 'name')
            goal_types = GoalType.objects.filter(created_by=request.user.id).values_list('id', 'name')
            status = GoalTracking.status
            ratings = gt.rating
            return render(request, 'goaltracking/edit.html', {
                'branches': branches,
                'goal_types': goal_types,
                'goal_tracking': gt,
                'ratings': ratings,
                'status': status,
            })
        except Exception as ex:
            return default_undefined_exception(request, err=ex, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def update(cls, request: HttpRequest, pk: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            with transaction.atomic():
                return cls.__update_impl(request, pk)
        except Exception as ex:
            return default_undefined_exception(request, err=ex, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def __update_impl(cls, request: HttpRequest, pk: int) -> HttpResponse:
        if not request.user.has_perm('edit_goal_tracking'):
            return default_permission_denial(request, err=None, ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}', logger=logger)
        gt = get_object_or_404(GoalTracking, pk=pk)
        data = request.POST
        required = ('branch', 'goal_type', 'start_date', 'end_date', 'subject')
        if any(not data.get(f) for f in required):
            messages.error(request, 'Missing required fields.')
            return redirect('/')
        cls._set_goal_tracking(gt, data, request.user.id)
        gt.status = data.get('status')
        gt.progress = data.get('progress')
        gt.rating = data.get('rating')
        gt.save()
        messages.success(request, 'Goal tracking successfully updated.')
        return redirect('/')

    @classmethod
    def destroy(cls, request: HttpRequest, pk: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            with transaction.atomic():
                return cls.__destroy_impl(request, pk)
        except Exception as ex:
            return default_undefined_exception(request, err=ex, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def __destroy_impl(cls, request: HttpRequest, pk: int) -> HttpResponse:
        if not request.user.has_perm('delete_goal_tracking'):
            return default_permission_denial(request, err=None, ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}', logger=logger)
        gt = get_object_or_404(GoalTracking, pk=pk)
        if gt.created_by == request.user.id:
            gt.delete()
            messages.success(request, 'Goal tracking successfully deleted.')
        else:
            messages.error(request, 'Permission denied.')
        return redirect('/')

    @classmethod
    def _set_goal_tracking(cls, obj: GoalTracking, data, user_id: int):
        obj.branch = data.get('branch')
        obj.goal_type = data.get('goal_type')
        obj.start_date = data.get('start_date')
        obj.end_date = data.get('end_date')
        obj.subject = data.get('subject')
        obj.target_achievement = data.get('target_achievement')
        obj.description = data.get('description')
        obj.created_by = user_id
