import inspect
import logging
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller
from ....Models.configs.permission import Permission
from ....Models.individuals.role import Role

logger = logging.getLogger(__name__)

class PermissionController(Controller):
    CLN = 'PermissionController'

    @classmethod
    def MFN(cls):
        return inspect.currentframe().f_back.f_code.co_name

    @classmethod
    def _set_controller(cls, request: HttpRequest) -> 'PermissionController':
        inst = cls()
        inst.request = request
        return inst

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        cls._set_controller(request)
        REF = f'{cls.CLN}::{cls.MFN()}'
        try:
            perms = Permission.objects.all()
            return render(request, 'permission/index.html', {'permissions': perms})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        cls._set_controller(request)
        REF = f'{cls.CLN}::{cls.MFN()}'
        try:
            roles = Role.objects.all()
            return render(request, 'permission/create.html', {'roles': roles})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def store(cls, request: HttpRequest) -> HttpResponse:
        cls._set_controller(request)
        REF = f'{cls.CLN}::{cls.MFN()}'
        try:
            name = request.POST.get('name') or ''
            if not name:
                return default_undefined_exception(request, err=ValueError('name is required'), ref=REF, logger=logger)
            perm = Permission(name=name, created_by=request.user)
            perm.save()
            for role_id in request.POST.getlist('roles'):
                r = get_object_or_404(Role, pk=role_id)
                perm.permissions.add(r)
            from django.contrib import messages
            messages.success(request, f'Permission {perm.name} added!')
            return redirect('permission_index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def edit(cls, request: HttpRequest, permission_id: str) -> HttpResponse:
        cls._set_controller(request)
        REF = f'{cls.CLN}::{cls.MFN()}'
        try:
            perm = get_object_or_404(Permission, pk=permission_id)
            roles = Role.objects.filter(created_by=request.user).all()
            return render(request, 'permission/edit.html', {'permission': perm, 'roles': roles})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def update(cls, request: HttpRequest, permission_id: str) -> HttpResponse:
        cls._set_controller(request)
        REF = f'{cls.CLN}::{cls.MFN()}'
        try:
            perm = get_object_or_404(Permission, pk=permission_id)
            name = request.POST.get('name') or ''
            if not name:
                return default_undefined_exception(request, err=ValueError('name is required'), ref=REF, logger=logger)
            perm.name = name
            perm.save()
            from django.contrib import messages
            messages.success(request, f'Permission {perm.name} updated!')
            return redirect('permission_index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def destroy(cls, request: HttpRequest, permission_id: str) -> HttpResponse:
        cls._set_controller(request)
        REF = f'{cls.CLN}::{cls.MFN()}'
        try:
            perm = get_object_or_404(Permission, pk=permission_id)
            perm.delete()
            from django.contrib import messages
            messages.success(request, 'Permission successfully deleted.')
            return redirect('permission_index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)
