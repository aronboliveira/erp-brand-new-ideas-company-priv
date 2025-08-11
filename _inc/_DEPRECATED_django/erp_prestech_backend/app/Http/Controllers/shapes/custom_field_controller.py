import inspect
import logging
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse
from django.shortcuts import render, redirect, get_object_or_404
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller
from ....Models.shapes.custom_field import CustomField

logger = logging.getLogger(__name__)

class CustomFieldController(Controller):

    @classmethod
    def _set_custom_field(cls, instance: CustomField, **attrs) -> CustomField:
        for field, value in attrs.items():
            setattr(instance, field, value)
        instance.save()
        return instance

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if not request.user.has_perm('manage constant custom field'):
                raise PermissionDenied('Permission Denied.')
            custom_fields = CustomField.objects.filter(created_by=request.user.creator_id())
            return render(request, 'customFields/index.html', {'custom_fields': custom_fields})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if not request.user.has_perm('create constant custom field'):
                raise PermissionDenied('Permission Denied.')
            return render(request, 'customFields/create.html', {
                'types': CustomField.field_types,
                'modules': CustomField.modules
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger,
                                            auto_redirect=False, json={'error': None})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger,
                                               auto_redirect=False, json='An error occurred.')

    @classmethod
    def store(cls, request: HttpRequest) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if not request.user.has_perm('create constant custom field'):
                raise PermissionDenied('Permission Denied.')
            data = {k: request.POST.get(k) for k in ('name', 'type', 'module')}
            if not all(data.values()) or len(data['name']) > 40:
                messages.error(request, 'Validation Error: Check required fields.')
                return redirect('custom_field_index')
            data['created_by'] = request.user.creator_id()
            cls._set_custom_field(CustomField(), **data)
            messages.success(request, 'Custom Field successfully created!')
            return redirect('custom_field_index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def show(cls, request: HttpRequest, custom_field_id: int) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            return redirect('custom_field_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def edit(cls, request: HttpRequest, custom_field_id: int) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if not request.user.has_perm('edit constant custom field'):
                raise PermissionDenied('Permission Denied.')
            cf = get_object_or_404(CustomField, pk=custom_field_id)
            if cf.created_by != request.user.creator_id():
                raise PermissionDenied('Permission Denied.')
            return render(request, 'customFields/edit.html', {
                'custom_field': cf,
                'types': CustomField.field_types,
                'modules': CustomField.modules
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger,
                                            auto_redirect=False, json={'error': None})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger,
                                               auto_redirect=False, json='An error occurred.')

    @classmethod
    def update(cls, request: HttpRequest, custom_field_id: int) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if not request.user.has_perm('edit constant custom field'):
                raise PermissionDenied('Permission Denied.')
            cf = get_object_or_404(CustomField, pk=custom_field_id)
            if cf.created_by != request.user.creator_id():
                raise PermissionDenied('Permission Denied.')
            name = request.POST.get('name')
            if not name or len(name) > 40:
                messages.error(request, 'Validation Error: Name is required and should be at most 40 characters.')
                return redirect('custom_field_index')
            cls._set_custom_field(cf, name=name)
            messages.success(request, 'Custom Field successfully updated!')
            return redirect('custom_field_index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def destroy(cls, request: HttpRequest, custom_field_id: int) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if not request.user.has_perm('delete constant custom field'):
                raise PermissionDenied('Permission Denied.')
            cf = get_object_or_404(CustomField, pk=custom_field_id)
            if cf.created_by != request.user.creator_id():
                raise PermissionDenied('Permission Denied.')
            cf.delete()
            messages.success(request, 'Custom Field successfully deleted!')
            return redirect('custom_field_index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)
