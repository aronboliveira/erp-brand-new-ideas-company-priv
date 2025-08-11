import inspect
import logging
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.utils.decorators import method_decorator
from django.views.decorators.http import require_http_methods
from ....Models.individuals.employee import Employee
from ....Models.shapes.asset import Asset
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller
from ....configs.messages_templates import get_lacking_field_message

logger = logging.getLogger(__name__)

class AssetController(Controller):
    CN = 'AssetController'
    MFN = staticmethod(lambda: inspect.currentframe().f_back.f_code.co_name)

    @method_decorator(login_required)
    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        try:
            if not request.user.has_perm('crm.manage_assets'):
                return default_permission_denial(
                    request, err=PermissionDenied(),
                    ref=f"{cls.__name__}::{cls.MFN()}", logger=logger
                )
            assets = Asset.objects.filter(created_by=request.user.creator_id)
            return render(request, 'assets/index.html', {'assets': assets})
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f"{cls.__name__}::{cls.MFN()}", logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        try:
            if not request.user.has_perm('crm.create_assets'):
                return default_permission_denial(
                    request, err=PermissionDenied(),
                    ref=f"{cls.__name__}::{cls.MFN()}", logger=logger
                )
            employees = dict(Employee.objects.filter(
                created_by=request.user.creator_id
            ).values_list('user_id', 'name'))
            return render(request, 'assets/create.html', {'employee': employees})
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f"{cls.__name__}::{cls.MFN()}", logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def store(cls, request: HttpRequest) -> HttpResponse:
        try:
            if not request.user.has_perm('crm.create_assets'):
                return default_permission_denial(
                    request, err=PermissionDenied(),
                    ref=f"{cls.__name__}::{cls.MFN()}", logger=logger
                )
            data = {
                'employee_id': ','.join(request.POST.getlist('employee_id')) or '',
                **{k: request.POST.get(k) for k in ['name', 'purchase_date', 'supported_date', 'amount', 'description']},
                'created_by': request.user.creator_id
            }
            Asset.objects.create(**data)
            messages.success(request, 'Assets successfully created.')
            return redirect('account-assets.index')
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f"{cls.__name__}::{cls.MFN()}", logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def edit(cls, request: HttpRequest, id: int) -> HttpResponse:
        try:
            if not request.user.has_perm('crm.edit_assets'):
                return default_permission_denial(
                    request, err=PermissionDenied(),
                    ref=f"{cls.__name__}::{cls.MFN()}", logger=logger
                )
            asset = get_object_or_404(Asset, pk=id)
            employees = dict(Employee.objects.filter(
                created_by=request.user.creator_id
            ).values_list('id', 'name'))
            asset.employee_id = asset.employee_id.split(',') if asset.employee_id else []
            return render(request, 'assets/edit.html', {'asset': asset, 'employee': employees})
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f"{cls.__name__}::{cls.MFN()}", logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def update(cls, request: HttpRequest, id: int) -> HttpResponse:
        try:
            if not request.user.has_perm('crm.edit_assets'):
                return default_permission_denial(
                    request, err=PermissionDenied(),
                    ref=f"{cls.__name__}::{cls.MFN()}", logger=logger
                )
            asset = get_object_or_404(Asset, pk=id)
            if asset.created_by != request.user.creator_id:
                return default_permission_denial(
                    request, err=PermissionDenied(),
                    ref=f"{cls.__name__}::{cls.MFN()}", logger=logger
                )
            for field in ['name', 'purchase_date', 'supported_date', 'amount']:
                if not request.POST.get(field):
                    messages.error(request, get_lacking_field_message(field, cls.__name__))
                    return redirect(get_redirect_url(request))
            updates = {k: request.POST.get(k) for k in ['name', 'purchase_date', 'supported_date', 'amount', 'description']}
            updates['employee_id'] = ','.join(request.POST.getlist('employee_id')) or ''
            for k, v in updates.items():
                setattr(asset, k, v)
            asset.save()
            messages.success(request, 'Assets successfully updated.')
            return redirect('account-assets.index')
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f"{cls.__name__}::{cls.MFN()}", logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def destroy(cls, request: HttpRequest, id: int) -> HttpResponse:
        try:
            if not request.user.has_perm('crm.delete_assets'):
                return default_permission_denial(
                    request, err=PermissionDenied(),
                    ref=f"{cls.__name__}::{cls.MFN()}", logger=logger
                )
            asset = get_object_or_404(Asset, pk=id)
            if asset.created_by != request.user.creator_id:
                return default_permission_denial(
                    request, err=PermissionDenied(),
                    ref=f"{cls.__name__}::{cls.MFN()}", logger=logger
                )
            asset.delete()
            messages.success(request, 'Assets successfully deleted.')
            return redirect('account-assets.index')
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f"{cls.__name__}::{cls.MFN()}", logger=logger
            )

    @classmethod
    def show(cls, request: HttpRequest, asset_id: int) -> HttpResponse:
        return redirect('account-assets.index')
