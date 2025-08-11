import inspect
import logging
from datetime import datetime
from io import BytesIO

import pandas as pd
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.db.models import F, Value
from django.db.models.functions import Concat
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import get_object_or_404, redirect, render

from .._helpers.error_handlers import (
  default_permission_denial,
  default_undefined_exception
)
from .._helpers.http import get_redirect_url
from .._traits.controller import Controller
from ....Models.bills.tax import Tax
from ....Models.charts.chart_of_account import ChartOfAccount
from ....Models.products.product_service import ProductService
from ....Models.products.product_service_category import ProductServiceCategory
from ....Models.products.product_service_unit import ProductServiceUnit
from ....Models.products.warehouse_product import WarehouseProduct
from ....Models.shapes.custom_field import CustomField

logger = logging.getLogger(__name__)


class ProductServiceController(Controller):

  @classmethod
  def _set_product_service(cls,
                           request: HttpRequest,
                           instance: ProductService = None
                          ) -> ProductService:
    data = {
      'name': request.POST.get('name'),
      'description': request.POST.get('description', ''),
      'sku': request.POST.get('sku'),
      'sale_price': request.POST.get('sale_price'),
      'purchase_price': request.POST.get('purchase_price'),
      'tax_id': ','.join(request.POST.getlist('tax_id')),
      'unit_id': request.POST.get('unit_id'),
      'type': request.POST.get('type'),
      'sale_chartaccount_id':
        request.POST.get('sale_chartaccount_id'),
      'expense_chartaccount_id':
        request.POST.get('expense_chartaccount_id'),
      'category_id': request.POST.get('category_id'),
      'created_by': request.user.creator_id(),
    }
    svc = instance or ProductService()
    for attr, val in data.items():
      setattr(svc, attr, val)
    return svc

  @classmethod
  def index(cls, request: HttpRequest) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls()
    self.request = request
    try:
      self.authorize('manage_product_service')
      cats = ProductServiceCategory.objects.filter(
        created_by=request.user.creator_id(),
        type='product & service'
      ).values_list('id', 'name')
      category = {'': 'Select Category'}
      category.update({str(pk): nm for pk, nm in cats})
      qs = ProductService.objects.filter(
        created_by=request.user.creator_id()
      ).select_related('category', 'unit')
      if request.GET.get('category'):
        qs = qs.filter(category_id=request.GET['category'])
      return render(request,
                    'productservice/index.html',
                    {'productServices': qs, 'category': category})
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e, ref=REF, logger=logger,
        json={'error': 'Permission denied.'}
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e, ref=REF, logger=logger
      )

  @classmethod
  def create(cls, request: HttpRequest) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls()
    self.request = request
    try:
      self.authorize('create_product_service')
      custom_fields = CustomField.objects.filter(
        created_by=request.user.creator_id(), module='product'
      )
      cats = ProductServiceCategory.objects.filter(
        created_by=request.user.creator_id(),
        type='product & service'
      ).values_list('id', 'name')
      category = {'': 'Select Category'}
      category.update({str(pk): nm for pk, nm in cats})
      units = ProductServiceUnit.objects.filter(
        created_by=request.user.creator_id()
      ).values_list('id', 'name')
      unit = {str(pk): nm for pk, nm in units}
      taxes = Tax.objects.filter(
        created_by=request.user.creator_id()
      ).values_list('id', 'name')
      tax = {str(pk): nm for pk, nm in taxes}
      income_qs = ChartOfAccount.objects.filter(
        type__name='income',
        created_by=request.user.creator_id()
      ).annotate(
        code_name=Concat(F('code'), Value(' - '), F('name'))
      ).values_list('id', 'code_name')
      income_accounts = {'': 'Select Account'}
      income_accounts.update({str(pk): cn for pk, cn in income_qs})
      expense_qs = ChartOfAccount.objects.filter(
        type__name__in=['Expenses', 'Costs of Goods Sold'],
        created_by=request.user.creator_id()
      ).annotate(
        code_name=Concat(F('code'), Value(' - '), F('name'))
      ).values_list('id', 'code_name')
      expense_accounts = {'': 'Select Account'}
      expense_accounts.update({str(pk): cn for pk, cn in expense_qs})
      return render(request,
                    'productservice/create.html',
                    {
                      'customFields': custom_fields,
                      'category': category,
                      'unit': unit,
                      'tax': tax,
                      'incomeChartAccounts': income_accounts,
                      'expenseChartAccounts': expense_accounts
                    })
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e, ref=REF, logger=logger,
        json={'error': 'Permission denied.'}
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e, ref=REF, logger=logger
      )

  @classmethod
  def store(cls, request: HttpRequest) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls() 
    self.request = request
    try:
      self.authorize('create_product_service')
      svc = cls._set_product_service(request)
      svc.save()
      CustomField.save_data(svc, request.POST.get('customField', {}))
      messages.success(request, 'Product successfully created.')
      return redirect('productservice.index')
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e, ref=REF, logger=logger,
        json={'error': 'Permission denied.'}
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e, ref=REF, logger=logger
      )

  @classmethod
  def show(cls, request: HttpRequest, product_id: str) -> HttpResponse:
    return redirect('productservice.index')

  @classmethod
  def edit(cls, request: HttpRequest, product_id: str) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls() 
    self.request = request
    try:
      self.authorize('edit_product_service')
      svc = get_object_or_404(ProductService, pk=product_id)
      if svc.created_by != request.user.creator_id():
        return default_permission_denial(
          request, err=PermissionDenied(), ref=REF, logger=logger,
          json={'error': 'Permission denied.'}
        )
      svc.customField = CustomField.get_data(svc, 'product')
      custom_fields = CustomField.objects.filter(
        created_by=request.user.creator_id(), module='product'
      )
      cats = ProductServiceCategory.objects.filter(
        created_by=request.user.creator_id(),
        type='product & service'
      ).values_list('id', 'name')
      category = {'': 'Select Category'}
      category.update({str(pk): nm for pk, nm in cats})
      units = ProductServiceUnit.objects.filter(
        created_by=request.user.creator_id()
      ).values_list('id', 'name')
      unit = {str(pk): nm for pk, nm in units}
      taxes = Tax.objects.filter(
        created_by=request.user.creator_id()
      ).values_list('id', 'name')
      tax = {str(pk): nm for pk, nm in taxes}
      income_qs = ChartOfAccount.objects.filter(
        type__name='income',
        created_by=request.user.creator_id()
      ).annotate(
        code_name=Concat(F('code'), Value(' - '), F('name'))
      ).values_list('id', 'code_name')
      income_accounts = {'': 'Select Account'}
      income_accounts.update({str(pk): cn for pk, cn in income_qs})
      expense_qs = ChartOfAccount.objects.filter(
        type__name__in=['Expenses', 'Costs of Goods Sold'],
        created_by=request.user.creator_id()
      ).annotate(
        code_name=Concat(F('code'), Value(' - '), F('name'))
      ).values_list('id', 'code_name')
      expense_accounts = {'': 'Select Account'}
      expense_accounts.update({str(pk): cn for pk, cn in expense_qs})
      return render(request,
                    'productservice/edit.html',
                    {
                      'productService': svc,
                      'customFields': custom_fields,
                      'category': category,
                      'unit': unit,
                      'tax': tax,
                      'incomeChartAccounts': income_accounts,
                      'expenseChartAccounts': expense_accounts
                    })
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e, ref=REF, logger=logger,
        json={'error': 'Permission denied.'}
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e, ref=REF, logger=logger
      )

  @classmethod
  def update(cls, request: HttpRequest, product_id: str) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls() 
    self.request = request
    try:
      self.authorize('edit_product_service')
      svc = get_object_or_404(ProductService, pk=product_id)
      if svc.created_by != request.user.creator_id():
        return default_permission_denial(
          request, err=PermissionDenied(), ref=REF, logger=logger,
          json={'error': 'Permission denied.'}
        )
      svc = cls._set_product_service(request, instance=svc)
      svc.save()
      CustomField.save_data(svc, request.POST.get('customField', {}))
      messages.success(request, 'Product successfully updated.')
      return redirect('productservice.index')
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e, ref=REF, logger=logger,
        json={'error': 'Permission denied.'}
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e, ref=REF, logger=logger
      )

  @classmethod
  def destroy(cls, request: HttpRequest, product_id: str) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls() 
    self.request = request
    try:
      self.authorize('delete_product_service')
      svc = get_object_or_404(ProductService, pk=product_id)
      if svc.created_by != request.user.creator_id():
        return default_permission_denial(
          request, err=PermissionDenied(), ref=REF, logger=logger,
          json={'error': 'Permission denied.'}
        )
      if getattr(svc, 'pro_image', None):
        from ....Models.utils.utility import Utility
        Utility.change_storage_limit(
          request.user.creator_id(),
          f'/uploads/pro_image/{svc.pro_image}'
        )
      svc.delete()
      messages.success(request, 'Product successfully deleted.')
      return redirect('productservice.index')
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e, ref=REF, logger=logger,
        json={'error': 'Permission denied.'}
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e, ref=REF, logger=logger
      )

  @classmethod
  def export(cls, request: HttpRequest) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls() 
    self.request = request
    try:
      self.authorize('manage_product_service')
      qs = ProductService.objects.filter(
        created_by=request.user.creator_id()
      ).values(
        'id', 'name', 'sku', 'sale_price', 'purchase_price',
        'tax_id', 'unit_id', 'type',
        'sale_chartaccount_id', 'expense_chartaccount_id'
      )
      df = pd.DataFrame.from_records(qs)
      buf = BytesIO()
      with pd.ExcelWriter(buf, engine='openpyxl') as w:
        df.to_excel(w, index=False, sheet_name='ProductService')
      buf.seek(0)
      fn = f'productservice_{datetime.now():%Y%m%d_%H%M%S}.xlsx'
      resp = HttpResponse(
        buf.read(),
        content_type=(
          'application/vnd.openxmlformats-officedocument.'
          'spreadsheetml.sheet'
        )
      )
      resp['Content-Disposition'] = f'attachment filename={fn}'
      return resp
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e, ref=REF, logger=logger,
        json={'error': 'Permission denied.'}
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e, ref=REF, logger=logger
      )

  @classmethod
  def import_data(cls, request: HttpRequest) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls() 
    self.request = request
    try:
      self.authorize('create_product_service')
      file = request.FILES.get('file')
      if not file:
        messages.error(request, 'No file uploaded.')
        return redirect(get_redirect_url(request))
      df = pd.read_excel(file)
      errors = []
      for idx, row in df.iterrows():
        try:
          svc, _ = ProductService.objects.update_or_create(
            sku=row['sku'],
            created_by=request.user.creator_id(),
            defaults={
              'name': row['name'],
              'sale_price': row['sale_price'],
              'purchase_price': row['purchase_price'],
              'tax_id': row.get('tax_id'),
              'unit_id': row.get('unit_id'),
              'type': row.get('type'),
              'sale_chartaccount_id':
                row.get('sale_chartaccount_id'),
              'expense_chartaccount_id':
                row.get('expense_chartaccount_id'),
            }
          )
          CustomField.save_data(svc, row.get('customField', {}))
        except Exception as ex:
          errors.append(f'Row {idx+2}: {ex}')
      if errors:
        messages.error(
          request,
          f'{len(errors)} records failed to import: ' + ' '.join(errors)
        )
      else:
        messages.success(request, 'All records imported successfully.')
      return redirect('productservice.index')
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e, ref=REF, logger=logger,
        json={'error': 'Permission denied.'}
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e, ref=REF, logger=logger
      )

  @classmethod
  def import_file(cls, request: HttpRequest) -> HttpResponse:
    return render(request, 'productservice/import.html')

  @classmethod
  def warehouse_detail(cls,
                       request: HttpRequest,
                       product_id: str
                      ) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls() 
    self.request = request
    try:
      self.authorize('manage_product_service')
      prods = WarehouseProduct.objects.filter(
        product_id=product_id,
        created_by=request.user.creator_id()
      )
      return render(request,
                    'productservice/detail.html',
                    {'products': prods})
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e, ref=REF, logger=logger,
        json={'error': 'Permission denied.'}
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e, ref=REF, logger=logger
      )

  @classmethod
  def search_products(cls, request: HttpRequest) -> JsonResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls() 
    self.request = request
    try:
      self.authorize('manage_pos')
      if not request.is_ajax():
        return JsonResponse({'error': 'Invalid request'}, status=400)
      sess = request.GET.get('session_key', '')
      war = request.GET.get('war_id', '0')
      cat = request.GET.get('cat_id', '')
      term = request.GET.get('search', '')
      ids = list(
        WarehouseProduct.objects.filter(
          warehouse_id=war or 1,
          created_by=request.user.creator_id()
        ).values_list('product_id', flat=True)
      )
      prods = ProductService.get_all_products(request.user)
      prods = prods.filter(id__in=ids)
      if cat and cat != '0':
        prods = prods.filter(category_id=cat)
      if term:
        prods = prods.filter(name__icontains=term)
      output = ''
      for p in prods.select_related('unit'):
        qty = p.get_total_product_quantity(request.user)
        price = (
          p.purchase_price
          if sess == 'purchases' and p.purchase_price else
          p.sale_price
        )
        taxes = [p.tax] if getattr(p, 'tax', None) else []
        total_rate = sum(t.rate for t in taxes)
        html_tax = ''.join(
          f"<span class='badge badge-primary'>{t.name}"
          f" ({t.rate}%)</span><br>" for t in taxes
        ) or '-'
        tax_val = (price * total_rate) / 100
        sub = price + tax_val
        img = getattr(p, 'pro_image', '') or 'default.png'
        url = f"/add-to-cart/{p.id}/{sess}"
        output += (
          f"<div class='col-lg-2'>"
          f"<div data-url='{url}' class='toacart'>"
          f"<img src='/media/{img}'/><h6>{p.name}</h6>"
          f"<small>{price:.2f}</small>"
          f"<small>{qty} {getattr(p.unit, 'name','')}</small>"
          f"<small>{sub}</small>"
          f"</div></div>"
        )
      if not output:
        output = ("<div class='card'><h5>No Product Available</h5>"
                  "</div>")
      return JsonResponse({'html': html_tax + output})
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e, ref=REF, logger=logger,
        json={'error': 'Permission denied.'}
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e, ref=REF, logger=logger
      )

  @classmethod
  def add_to_cart(cls,
                  request: HttpRequest,
                  product_id: str,
                  session_key: str
                 ) -> JsonResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls() 
    self.request = request
    try:
      self.authorize('manage_product_service')
      if not request.is_ajax():
        return JsonResponse({'error': 'Invalid'}, status=400)
      p = get_object_or_404(ProductService, pk=product_id,
                            created_by=request.user.creator_id())
      avail = p.get_total_product_quantity(request.user)
      if session_key == 'pos' and avail == 0:
        return JsonResponse(
          {'error': 'Out of stock'}, status=404
        )
      price = p.purchase_price if session_key == 'purchases' else p.sale_price
      taxes = [p.tax] if getattr(p, 'tax', None) else []
      rate = sum(t.rate for t in taxes)
      tax_val = (price * rate) / 100
      sub = price + tax_val
      cart = request.session.get(session_key, {})
      item = cart.get(str(p.id), {})
      if not item:
        item = {
          'name': p.name, 'quantity': 1, 'price': price,
          'tax': rate, 'subtotal': sub, 'id': str(p.id),
          'original_quantity': avail,
          'tax_html': ''.join(
            f"<span>{t.name}({t.rate}%)</span><br>"
            for t in taxes
          ) or '-'
        }
      else:
        item['quantity'] += 1
        item['subtotal'] = (
          item['price'] * item['quantity']
          + (item['price'] * item['quantity'] * item['tax'] / 100)
        )
      if session_key == 'pos' and item['quantity'] > avail:
        return JsonResponse({'error': 'Out of stock'}, status=404)
      cart[str(p.id)] = item
      request.session[session_key] = cart
      return JsonResponse({'code': 200,
                           'status': 'Success',
                           'product': item,
                           'carthtml': '<!-- html for cart row -->'})
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e, ref=REF, logger=logger,
        json={'error': 'Permission denied.'}
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e, ref=REF, logger=logger
      )

  @classmethod
  def update_cart(cls, request: HttpRequest) -> JsonResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls() 
    self.request = request
    try:
      self.authorize('manage_product_service')
      if not request.is_ajax():
        return JsonResponse({'error': 'Invalid'}, status=400)
      pid = request.POST.get('id') 
      qty = int(request.POST.get('quantity', 0))
      disc = float(request.POST.get('discount', 0))
      sess = request.POST.get('session_key', '')
      cart = request.session.get(sess, {})
      item = cart.get(pid)
      if not item:
        return JsonResponse({'error': 'Not found'}, status=404)
      if qty == 0:
        cart.pop(pid, None)
      else:
        item['quantity'] = qty
        tax = item['tax'] 
        price = item['price']
        item['subtotal'] = price*qty + price*qty*tax/100
      total = sum(i['subtotal'] for i in cart.values()) - disc
      request.session[sess] = cart
      return JsonResponse({
        'code': 200,
        'success': 'Cart updated successfully!',
        'cart': cart,
        'discount': f'{total:.2f}'
      })
    except PermissionDenied as e:
      return default_permission_denial(
        request, err=e, ref=REF, logger=logger,
        json={'error': 'Permission denied.'}
      )
    except Exception as e:
      return default_undefined_exception(
        request, err=e, ref=REF, logger=logger
      )

  @classmethod
  def empty_cart(cls, request: HttpRequest) -> HttpResponse:
    key = request.POST.get('session_key')
    if key:
      request.session.pop(key, None)
      messages.warning(request, 'Cart is empty!')
    return redirect(get_redirect_url(request))

  @classmethod
  def warehouse_empty_cart(cls, request: HttpRequest) -> JsonResponse:
    key = request.POST.get('session_key')
    if key:
      request.session.pop(key, None)
    return JsonResponse({})

  @classmethod
  def remove_from_cart(cls, request: HttpRequest) -> HttpResponse:
    key = request.POST.get('session_key')
    pid = request.POST.get('id')
    cart = request.session.get(key, {})
    cart.pop(pid, None)
    request.session[key] = cart
    messages.info(request, 'Product removed from cart!')
    return redirect(get_redirect_url(request))
