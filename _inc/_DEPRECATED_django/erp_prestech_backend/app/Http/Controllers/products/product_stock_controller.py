import inspect
import logging
from django.core.exceptions import PermissionDenied
from django.db import transaction
from django.http import HttpRequest, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.urls import reverse
from django.contrib import messages
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._helpers.http import get_redirect_url
from .._traits.controller import Controller
from ....Models.products.product_service import ProductService
from ....Models.products.product_stock import ProductStock
from ....Models.utils.utility import Utility

logger = logging.getLogger(__name__)

class ProductStockController(Controller):
  @classmethod
  def index(cls, request: HttpRequest) -> HttpResponse:
    ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      cls().authorize('manage product & service')
      services = ProductService.objects.filter(
        created_by=request.user.creator_id(),
        type='product'
      )
      return render(request,
                    'productstock/index.html',
                    {'product_services': services})
    except PermissionDenied as e:
      return default_permission_denial(request,
                                       err=e,
                                       ref=ref,
                                       logger=logger)
    except Exception as e:
      return default_undefined_exception(request,
                                         err=e,
                                         ref=ref,
                                         logger=logger)

  @classmethod
  def create(cls, request: HttpRequest) -> HttpResponse:
    ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      cls().authorize('create productstock')
      return render(request, 'productstock/create.html')
    except PermissionDenied as e:
      return default_permission_denial(request,
                                       err=e,
                                       ref=ref,
                                       logger=logger)
    except Exception as e:
      return default_undefined_exception(request,
                                         err=e,
                                         ref=ref,
                                         logger=logger)

  @classmethod
  def store(cls, request: HttpRequest) -> HttpResponse:
    ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      cls().authorize('create productstock')
      data = request.POST
      qty = int(data.get('quantity', '') or 0)
      pid = data.get('product_service_id', '').strip()
      if qty <= 0 or not pid:
        messages.error(request, 'Invalid input.')
        return redirect(get_redirect_url(request))
      service = get_object_or_404(
        ProductService,
        pk=pid,
        created_by=request.user.creator_id()
      )
      desc = data.get('description', '').strip()
      t = data.get('type', '').strip() or 'manually'
      tid = int(data.get('type_id', '') or 0)
      with transaction.atomic():
        service.quantity += qty
        service.created_by = request.user.creator_id()
        service.save()
        Utility.addProductStock(
          service.id,
          qty,
          t,
          desc,
          tid
        )
      messages.success(request, 'Product stock created successfully.')
      return redirect(reverse('productstock_index'))
    except PermissionDenied as e:
      return default_permission_denial(request, err=e, ref=ref, logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e, ref=ref, logger=logger)

  @classmethod
  def show(cls, request: HttpRequest, id) -> HttpResponse:
    ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      cls().authorize('view productstock')
      stock = get_object_or_404(ProductStock, pk=id)
      return render(request,
                    'productstock/show.html',
                    {'product_stock': stock})
    except PermissionDenied as e:
      return default_permission_denial(request,
                                       err=e,
                                       ref=ref,
                                       logger=logger)
    except Exception as e:
      return default_undefined_exception(request,
                                         err=e,
                                         ref=ref,
                                         logger=logger)

  @classmethod
  def edit(cls, request: HttpRequest, id) -> HttpResponse:
    ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      cls().authorize('edit product & service')
      service = get_object_or_404(ProductService, pk=id)
      if service.created_by != request.user.creator_id():
        raise PermissionDenied
      return render(request,
                    'productstock/edit.html',
                    {'productService': service})
    except PermissionDenied as e:
      return default_permission_denial(request,
                                       err=e,
                                       ref=ref,
                                       logger=logger)
    except Exception as e:
      return default_undefined_exception(request,
                                         err=e,
                                         ref=ref,
                                         logger=logger)

  @classmethod
  def update(cls, request: HttpRequest, id) -> HttpResponse:
    ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      cls().authorize('edit product & service')
      service = get_object_or_404(ProductService, pk=id)
      if service.created_by != request.user.creator_id():
        raise PermissionDenied
      qty = int(request.POST.get('quantity', 0) or 0)
      total = service.quantity + qty
      with transaction.atomic():
        service.quantity = total
        service.created_by = request.user.creator_id()
        service.save()
        desc = f'{qty} quantity added manually'
        Utility.addProductStock(service.id,
                                qty,
                                'manually',
                                desc,
                                0)
      messages.success(request,
                       'Product quantity updated manually.')
      return redirect(reverse('productstock_index'))
    except PermissionDenied as e:
      return default_permission_denial(request,
                                       err=e,
                                       ref=ref,
                                       logger=logger)
    except Exception as e:
      return default_undefined_exception(request,
                                         err=e,
                                         ref=ref,
                                         logger=logger)

  @classmethod
  def destroy(cls, request: HttpRequest, id) -> HttpResponse:
    ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      cls().authorize('delete productstock')
      stock = get_object_or_404(ProductStock, pk=id)
      if stock.created_by != request.user.creator_id():
        raise PermissionDenied
      with transaction.atomic():
        service = stock.product_service
        service.quantity -= stock.quantity
        service.save()
        stock.delete()
      messages.success(request, 'Stock record deleted.')
      return redirect(get_redirect_url(request))
    except PermissionDenied as e:
      return default_permission_denial(request, err=e, ref=ref, logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e, ref=ref, logger=logger)