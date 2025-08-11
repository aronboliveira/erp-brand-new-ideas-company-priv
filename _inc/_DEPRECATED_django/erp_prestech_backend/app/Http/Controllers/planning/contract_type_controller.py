import inspect
import logging
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from ....Models.planning.contract import Contract
from ....Models.planning.contract_type import ContractType
from .._traits.controller import Controller
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception

logger = logging.getLogger(__name__)

class ContractTypeController(Controller):

    @staticmethod
    def _set_contract_type(instance: ContractType, name: str, creator_id: int) -> ContractType:
        instance.name = name
        instance.created_by = creator_id
        instance.save()
        return instance

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if not (request.user.has_perm('manage contract type') and request.user.type == 'company'):
                raise PermissionDenied('manage contract type')
            types = ContractType.objects.filter(created_by=request.user.creator_id())
            return render(request, 'contract_type/index.html', {'types': types})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            return render(request, 'contract_type/create.html', {})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def store(cls, request: HttpRequest) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if not request.user.has_perm('create contract type'):
                raise PermissionDenied('create contract type')
            name = request.POST.get('name')
            if not name:
                messages.error(request, "Name is required.")
                return redirect(get_redirect_url(request))
            creator_id = request.user.creator_id()
            cls._set_contract_type(ContractType(), name, creator_id)
            messages.success(request, "Contract Type successfully created.")
            return redirect('contract_type_i_tdex')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def show(cls, request: HttpRequest, id: int) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            return redirect('contract_type_i_tdex')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def edit(cls, request: HttpRequest, id: int) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            ct = get_object_or_404(ContractType, pk=id)
            return render(request, 'contract_type/edit.html', {'contract_type': ct})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def update(cls, request: HttpRequest, id: int) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if not request.user.has_perm('edit contract type'):
                raise PermissionDenied('edit contract type')
            name = request.POST.get('name')
            if not name:
                messages.error(request, "Name is required.")
                return redirect(get_redirect_url(request))
            creator_id = request.user.creator_id()
            ct = get_object_or_404(ContractType, pk=id)
            cls._set_contract_type(ct, name, creator_id)
            messages.success(request, "Contract Type successfully updated.")
            return redirect('contract_type_i_tdex')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def destroy(cls, request: HttpRequest, id: int) -> HttpResponse:
        C, M = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f'{C}::{M}'
        try:
            if not request.user.has_perm('delete contract type'):
                raise PermissionDenied('delete contract type')
            ct = get_object_or_404(ContractType, pk=id)
            if Contract.objects.filter(type=ct.id).exists():
                messages.error(request, "this type is already use so please transfer or delete this type related data.")
                return redirect(get_redirect_url(request))
            ct.delete()
            messages.success(request, "Contract Type successfully deleted.")
            return redirect('contract_type_i_tdex')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)
