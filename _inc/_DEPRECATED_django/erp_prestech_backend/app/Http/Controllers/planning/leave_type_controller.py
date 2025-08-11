import logging
import inspect
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.db import transaction
from django.http import HttpRequest, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from ....Models.planning.leave_type import LeaveType
from .._traits.controller import Controller

logger = logging.getLogger(__name__)


class LeaveTypeController(Controller):
    def __init__(self, **kwargs):
        super().__init__(**kwargs)
        self.middleware(['auth'])

    @classmethod
    def _set_leavetype(cls, lt: LeaveType, data: dict, request_user) -> LeaveType:
        for field in ('title', 'days'):
            setattr(lt, field, data.get(field))
        if not getattr(lt, 'id', None):
            lt.created_by = request_user.creator_id()
        return lt

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('manage leave type') or (_ for _ in ()).throw(
                PermissionDenied('User lacks permission: manage leave type')
            )
            lts = LeaveType.objects.filter(created_by=request.user.creator_id())
            return render(request, 'leavetype/index.html', {'leavetypes': lts})
        except PermissionDenied as e:
            return default_permission_denial(
                request, err=e, ref=f'{CN}::{MN}', logger=logger
            )
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=f'{CN}::{MN}', logger=logger
            )

    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('create leave type') or (_ for _ in ()).throw(
                PermissionDenied('User lacks permission: create leave type')
            )
            return render(request, 'leavetype/create.html')
        except PermissionDenied as e:
            return default_permission_denial(
                request, err=e, ref=f'{CN}::{MN}', logger=logger,
                json={'error': 'Permission denied.'}
            )
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=f'{CN}::{MN}', logger=logger
            )

    @classmethod
    @transaction.atomic
    def store(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('create leave type') or (_ for _ in ()).throw(
                PermissionDenied('User lacks permission: create leave type')
            )
            data = request.POST
            for field in ('title', 'days'):
                if not data.get(field):
                    messages.error(request, f"{field.capitalize()} is required.")
                    return redirect(get_redirect_url(request))
            lt = cls._set_leavetype(LeaveType(), data, request.user)
            lt.save()
            messages.success(request, 'LeaveType successfully created.')
            return redirect('leavetype_index')
        except PermissionDenied as e:
            return default_permission_denial(
                request, err=e, ref=f'{CN}::{MN}', logger=logger
            )
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=f'{CN}::{MN}', logger=logger
            )

    @classmethod
    def show(cls, request: HttpRequest, leavetype_id: int) -> HttpResponse:
        return redirect('leavetype_index')

    @classmethod
    def edit(cls, request: HttpRequest, leavetype_id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('edit leave type') or (_ for _ in ()).throw(
                PermissionDenied('User lacks permission: edit leave type')
            )
            lt = get_object_or_404(LeaveType, pk=leavetype_id)
            lt.created_by == request.user.creator_id() or (_ for _ in ()).throw(
                PermissionDenied('Permission denied.')
            )
            return render(request, 'leavetype/edit.html', {'leavetype': lt})
        except PermissionDenied as e:
            return default_permission_denial(
                request, err=e, ref=f'{CN}::{MN}', logger=logger,
                json={'error': 'Permission denied.'}
            )
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=f'{CN}::{MN}', logger=logger
            )

    @classmethod
    @transaction.atomic
    def update(cls, request: HttpRequest, leavetype_id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('edit leave type') or (_ for _ in ()).throw(
                PermissionDenied('User lacks permission: edit leave type')
            )
            lt = get_object_or_404(LeaveType, pk=leavetype_id)
            lt.created_by == request.user.creator_id() or (_ for _ in ()).throw(
                PermissionDenied('Permission denied.')
            )
            data = request.POST
            for field in ('title', 'days'):
                if not data.get(field):
                    messages.error(request, f"{field.capitalize()} is required.")
                    return redirect(get_redirect_url(request))
            cls._set_leavetype(lt, data, request.user).save()
            messages.success(request, 'LeaveType successfully updated.')
            return redirect('leavetype_index')
        except PermissionDenied as e:
            return default_permission_denial(
                request, err=e, ref=f'{CN}::{MN}', logger=logger
            )
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=f'{CN}::{MN}', logger=logger
            )

    @classmethod
    def destroy(cls, request: HttpRequest, leavetype_id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('delete leave type') or (_ for _ in ()).throw(
                PermissionDenied('User lacks permission: delete leave type')
            )
            lt = get_object_or_404(LeaveType, pk=leavetype_id)
            lt.created_by == request.user.creator_id() or (_ for _ in ()).throw(
                PermissionDenied('Permission denied.')
            )
            lt.delete()
            messages.success(request, 'LeaveType successfully deleted.')
            return redirect('leavetype_index')
        except PermissionDenied as e:
            return default_permission_denial(
                request, err=e, ref=f'{CN}::{MN}', logger=logger
            )
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=f'{CN}::{MN}', logger=logger
            )
