# erp_prestech_backend/app/Http/Controllers/ProductServiceUnitController.py

import inspect
import logging
from uuid import UUID
from django.shortcuts import render, redirect, get_object_or_404
from django.http import HttpRequest, HttpResponse
from django.core.exceptions import PermissionDenied
from django.urls import reverse
from .._traits.controller import Controller
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import (
    default_permission_denial,
    default_undefined_exception,
)
from ....Models.products.product_service_unit import ProductServiceUnit
from ....Models.products.product_service import ProductService

logger = logging.getLogger(__name__)

class ProductServiceUnitController(Controller):

  @classmethod
  def index(cls, request: HttpRequest) -> HttpResponse:
    REF=f'{cls.__name}::{inspect.currentframe().f_code.co_name}'
    try:
      ctrl = ProductServiceUnitController()
      ctrl.request = request
      ctrl.authorize('manage constant unit')
      units = ProductServiceUnit.objects.filter(
        created_by=request.user.creator_id()
      ).all()
      return render(request,
                    'productServiceUnit/index.html',
                    {'units': units})
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def create(cls, request: HttpRequest) -> HttpResponse:
    REF=f'{cls.__name}::{inspect.currentframe().f_code.co_name}'
    try:
      if not request.user.has_perm('create constant unit'):
        raise PermissionDenied('User does not have permission to create constant units.')
      return render(request, 'productServiceUnit/create.html')
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=REF,
        logger=logger
      )
      
  @classmethod
  def store(cls, request: HttpRequest) -> HttpResponse:
    REF=f'{cls.__name}::{inspect.currentframe().f_code.co_name}'
    try:
      ctrl = ProductServiceUnitController() 
      ctrl.request = request
      ctrl.authorize('create constant unit')
      name = request.POST.get('name', '').strip()
      if not name:
        return redirect(get_redirect_url(request))
      if len(name) > 20:
        return redirect(get_redirect_url(request))
      unit = ProductServiceUnit(
        name=name,
        created_by=request.user.creator_id()
      )
      unit.save()
      return redirect(reverse('product-unit.index'))
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def edit(cls, request: HttpRequest, id: UUID) -> HttpResponse:
    REF=f'{cls.__name}::{inspect.currentframe().f_code.co_name}'
    try:
      if not request.user.has_perm('edit constant unit'): 
        raise PermissionDenied('User does not have permission to edit constant units')
      unit = get_object_or_404(ProductServiceUnit, pk=id)
      return render(request,
                  'productServiceUnit/edit.html',
                  {'unit': unit})
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=REF,
        logger=logger
      )    

  @classmethod
  def update(cls, request: HttpRequest, id: UUID) -> HttpResponse:
    REF=f'{cls.__name}::{inspect.currentframe().f_code.co_name}'
    try:
      ctrl = ProductServiceUnitController() 
      ctrl.request = request
      ctrl.authorize('edit constant unit')
      unit = get_object_or_404(ProductServiceUnit, pk=id)
      if unit.created_by != request.user.creator_id():
        raise PermissionDenied
      name = request.POST.get('name', '').strip()
      if not name or len(name) > 20:
        return redirect(get_redirect_url(request))
      unit.name = name 
      unit.save()
      return redirect(reverse('product-unit.index'))
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def destroy(cls, request: HttpRequest, id: UUID) -> HttpResponse:
    REF=f'{cls.__name}::{inspect.currentframe().f_code.co_name}'
    try:
      ctrl = ProductServiceUnitController() 
      ctrl.request = request
      ctrl.authorize('delete constant unit')
      unit = get_object_or_404(ProductServiceUnit, pk=id)
      if unit.created_by != request.user.creator_id():
        raise PermissionDenied
      if ProductService.objects.filter(unit_id=unit.id).exists():
        return redirect(get_redirect_url(request))
      unit.delete()
      return redirect(reverse('product-unit.index'))
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e,
        ref=REF,
        logger=logger
      )
