import inspect
import logging
from django.contrib import messages
from django.core.files.storage import default_storage
from django.core.signing import BadSignature, Signer
from django.forms.models import model_to_dict
from django.http import HttpRequest, JsonResponse
from django.shortcuts import redirect, render
from django.utils import timezone
from .._helpers.error_handlers import (
  default_permission_denial,
  default_undefined_exception,
)
from .._helpers.http import get_redirect_url
from .._helpers.security import permission_required_custom
from .._traits.controller import Controller
from ....Models.activity.pos import Pos
from ....Models.bills.pos_payment import PosPayment
from ....Models.bills.stock_report import StockReport
from ....Models.configs.settings import Setting
from ....Models.companies.warehouse import Warehouse
from ....Models.individuals.customer import Customer
from ....Models.products.pos_product import PosProduct
from ....Models.products.product_service import ProductService
from ....Models.utils.utility import Utility
logger = logging.getLogger(__name__)
class PosController(Controller):

  def __init__(self, **kwargs):
    super().__init__(**kwargs)
    self.logger = logger

  @classmethod
  def _check_permission(cls, request: HttpRequest, perm: str):
    self = cls()
    self.request = request
    try:
      self.authorize(perm)
      return True
    except Exception as e:
      return default_permission_denial(
        request,
        e,
        ref=f'{cls.__name__}::authorize',
        logger=logger,
      )

  def _ref(self):
    caller = inspect.currentframe().f_back
    return f'{self.__class__.__name__}::{caller.f_code.co_name}'

  def _get_user_details(self, user):
    return model_to_dict(user)

  def _get_session_sales(self, request):
    return request.session.get('pos', {})

  def _build_sales_data(self, session_sales, user, discount=0):
    mainsub = 0
    rows = []
    for item in session_sales.values():
      sub = item['price'] * item['quantity']
      tax_amt = sub * item['tax'] / 100
      rows.append({
        'name': item['name'],
        'quantity': item['quantity'],
        'price': user.price_format(item['price']),
        'tax': f"{item['tax']}%",
        'product_tax': item.get('product_tax', 0),
        'tax_amount': user.price_format(tax_amt),
        'subtotal': user.price_format(item['subtotal']),
      })
      mainsub += item['subtotal']
    total = mainsub - discount
    return {
      'data': rows,
      'discount': user.price_format(discount),
      'sub_total': user.price_format(mainsub),
      'total': user.price_format(total),
    }

  def index(self, request: HttpRequest):
    perm = self._check_permission(request, 'manage pos')
    if perm is not True:
      return perm
    try:
      u = request.user
      custs = list(
        Customer.objects
        .filter(created_by=u.creator_id())
        .values_list('name', 'name')
      )
      customers = ['Walk-in-customer', ''] + custs
      warehouses = list(
        Warehouse.objects
        .filter(created_by=u.creator_id())
        .values_list('id', 'name')
      )
      ctx = {
        'customers': customers,
        'warehouses': warehouses,
        'details': {
          'pos_id': u.pos_number_format(self.invoice_pos_number(request)),
          'user': self._get_user_details(u),
          'date': timezone.now().date().isoformat(),
          'pay': 'show',
        }
      }
      return render(request, 'pos/index.html', ctx)
    except Exception as e:
      return default_undefined_exception(
        request, e, ref=self._ref(), logger=self.logger
      )

  def create(self, request: HttpRequest):
    perm = self._check_permission(request, 'manage pos')
    if perm is not True:
      return perm
    try:
      sess = self._get_session_sales(request)
      if not sess:
        return JsonResponse({'error': 'Add some products to cart!'}, status=404)
      u = request.user
      disc = int(request.POST.get('discount', 0))
      cust = Customer.objects.filter(
        name=request.POST.get('vc_name'),
        created_by=u.creator_id()
      ).first()
      wh = Warehouse.objects.filter(
        id=request.POST.get('warehouse_name'),
        created_by=u.creator_id()
      ).first()
      ctx = {
        'sales': self._build_sales_data(sess, u, disc),
        'details': {
          'pos_id': u.pos_number_format(self.invoice_pos_number(request)),
          'customer': model_to_dict(cust) if cust else {},
          'warehouse': model_to_dict(wh) if wh else {},
          'user': self._get_user_details(u),
          'date': timezone.now().date().isoformat(),
          'pay': 'show',
        }
      }
      return render(request, 'pos/show.html', ctx)
    except Exception as e:
      return default_undefined_exception(
        request, e, ref=self._ref(), logger=self.logger
      )

  def store(self, request: HttpRequest):
    perm = self._check_permission(request, 'manage pos')
    if perm is not True:
      return perm
    try:
      u = request.user
      sess = self._get_session_sales(request)
      if not sess:
        return JsonResponse({'error': 'Items not found!'}, status=404)
      disc = int(request.POST.get('discount', 0))
      pid = self.invoice_pos_number(request)
      if Pos.objects.filter(pos_id=pid, created_by=u.creator_id()).exists():
        return JsonResponse(
          {'success': 'Payment is already completed!'},
          status=200
        )
      pos = Pos(
        pos_id=pid,
        customer_id=Customer.customer_id(request.POST.get('vc_name')),
        warehouse_id=request.POST.get('warehouse_name'),
        pos_date=timezone.now().date(),
        created_by=u.creator_id()
      )
      pos.save()
      for itm in sess.values():
        svc = ProductService.objects.filter(
          pk=itm['id'], created_by=u.creator_id()
        ).first()
        if svc:
          svc.quantity -= itm['quantity']
          svc.save()
        pp = PosProduct(
          pos=pos,
          product_id=itm['id'],
          price=itm['price'],
          quantity=itm['quantity'],
          tax=ProductService.tax_id(itm['id']),
          discount=disc
        )
        pp.save()
        StockReport.objects.filter(type='pos', type_id=pos.id).delete()
        StockReport.add_product_stock(
          pp.product_id, pp.quantity, 'pos',
          f"{pp.quantity} quantity sold in pos {u.pos_number_format(pid)}",
          pos.id
        )
      mainsub = sum(v['subtotal'] for v in sess.values())
      pay = PosPayment(
        pos=pos,
        date=request.POST.get('date'),
        amount=mainsub,
        discount=disc,
        discount_amount=mainsub - disc,
        created_by=u.creator_id()
      )
      pay.save()
      request.session.pop('pos', None)
      return JsonResponse(
        {'success': 'Payment completed successfully!'},
        status=200
      )
    except Exception as e:
      return default_undefined_exception(
        request, e, ref=self._ref(), logger=self.logger
      )

  def show(self, request: HttpRequest, enc_id):
    perm = self._check_permission(request, 'manage pos')
    if perm is not True:
      return perm
    try:
      real = self._decrypt_id(enc_id)
      pos = Pos.objects.get(pk=real)
      if pos.created_by != request.user.creator_id():
        raise PermissionError('Not owner')
      pay = PosPayment.objects.filter(pos=pos).first()
      return render(request, 'pos/view.html', {
        'pos': pos,
        'customer': pos.customer,
        'items': pos.items(),
        'pos_payment': pay
      })
    except PermissionError as e:
      return default_permission_denial(
        request, e, ref=self._ref(), logger=self.logger
      )
    except Exception as e:
      return default_undefined_exception(
        request, e, ref=self._ref(), logger=self.logger
      )

  def invoice_pos_number(self, request: HttpRequest) -> int:
    latest = Pos.objects.filter(
      created_by=request.user.creator_id()
    ).order_by('-created_at').first()
    return (latest.pos_id + 1) if latest else 1

  def report(self, request: HttpRequest):
    perm = self._check_permission(request, 'manage pos')
    if perm is not True:
      return perm
    try:
      pp = Pos.objects.filter(
        created_by=request.user.creator_id()
      ).select_related('customer', 'warehouse')
      return render(request, 'pos/report.html', {'pos_payments': pp})
    except Exception as e:
      return default_undefined_exception(
        request, e, ref=self._ref(), logger=self.logger
      )

  def barcode(self, request: HttpRequest):
    perm = self._check_permission(request, 'manage pos')
    if perm is not True:
      return perm
    try:
      svcs = ProductService.objects.filter(
        created_by=request.user.creator_id()
      )
      bc = {
        'barcode_type': request.user.barcode_type(),
        'barcode_format': request.user.barcode_format(),
      }
      return render(request, 'pos/barcode.html', {
        'product_services': svcs, 'barcode': bc
      })
    except Exception as e:
      return default_undefined_exception(
        request, e, ref=self._ref(), logger=self.logger
      )

  def get_settings(self, request: HttpRequest):
    perm = self._check_permission(request, 'manage pos')
    if perm is not True:
      return perm
    try:
      ss = Setting.objects.filter(created_by=request.user.creator_id())
      return render(request, 'pos/setting.html', {
        'settings': {s.name: s.value for s in ss}
      })
    except Exception as e:
      return default_undefined_exception(
        request, e, ref=self._ref(), logger=self.logger
      )

  @permission_required_custom('manage pos', logger)
  def print_barcode(self, request: HttpRequest):
    ws = Warehouse.objects.filter(created_by=request.user.creator_id())
    return render(request, 'pos/print.html', {'warehouses': ws})

  def barcode_settings_store(self, request: HttpRequest):
    perm = self._check_permission(request, 'manage pos')
    if perm is not True:
      return perm
    try:
      barcode_type = request.POST.get('barcode_type')
      barcode_format = request.POST.get('barcode_format')
      if not barcode_type or not barcode_format:
        messages.error(request, 'Both barcode_type and barcode_format are required')
        return redirect(get_redirect_url(request))
      for name, val in (('barcode_type', barcode_type), ('barcode_format', barcode_format)):
        Setting.objects.update_or_create(
          name=name,
          created_by=request.user.creator_id(),
          defaults={'value': val}
        )
      messages.success(request, 'Barcode setting successfully updated')
      return redirect(get_redirect_url(request))
    except Exception as e:
      return default_undefined_exception(
        request, e,
        ref=self._ref(),
        logger=self.logger
      )

  @permission_required_custom('manage pos', logger)
  def get_product(self, request: HttpRequest):
    wid = request.POST.get('warehouse_id')
    if not wid or int(wid) == 0:
      svcs = ProductService.objects.none()
    else:
      pids = Warehouse.objects.filter(
        created_by=request.user.creator_id(), pk=wid
      ).values_list('product_id', flat=True)
      svcs = ProductService.objects.filter(pk__in=pids)
    return JsonResponse({s.id: s.name for s in svcs})

  def receipt(self, request: HttpRequest):
    prods = request.POST.getlist('product_id')
    if not prods:
      return redirect(get_redirect_url(request)).with_error('Product is required.')
    svcs = ProductService.objects.filter(pk__in=prods)
    qty = request.POST.getlist('quantity')
    bc = {
      'barcode_type': request.user.barcode_type(),
      'barcode_format': request.user.barcode_format(),
    }
    return render(request, 'pos/receipt.html', {
      'product_services': svcs, 'barcode': bc, 'quantity': qty
    })

  def cart_discount(self, request: HttpRequest):
    sess = self._get_session_sales(request)
    subtotal = sum(i['subtotal'] for i in sess.values()) if sess else 0
    disc = int(request.POST.get('discount', 0))
    total = request.user.price_formats(subtotal - disc)
    return JsonResponse({'total': total})

  def pos(self, request: HttpRequest, uid):
    return self.show(request, uid)

  def preview_pos(self, request: HttpRequest, template: str, color: str):
    u = request.user
    # build 3 demo items
    items = []
    for i in range(1, 4):
      tax_rate = 5
      qty = 1
      price = 100
      discount = 50
      # TODO tax_price = price * qty * tax_rate / 100
      item_taxes = [
        {'name': f'Tax {k}', 'rate': '10 %', 'price': '$10'}
        for k in (1, 2)
      ]
      items.append({
        'name': f'Item {i}',
        'quantity': qty,
        'tax': tax_rate,
        'discount': discount,
        'price': price,
        'itemTax': item_taxes,
      })
    # aggregate totals
    total_tax_price = sum(
      float(t['price'].strip('$'))
      for it in items for t in it['itemTax']
    )
    total_quantity = sum(it['quantity'] for it in items)
    total_rate = sum(it['price'] for it in items)
    total_discount = sum(it['discount'] for it in items)
    # taxes_data map
    taxes_data = {}
    for it in items:
      for t in it['itemTax']:
        name = t['name']
        amt = float(t['price'].strip('$'))
        taxes_data[name] = taxes_data.get(name, 0) + amt
    # settings & logos
    s = Utility.settings()
    logo_dir = default_storage.url('uploads/logo/')
    company_logo = Utility.get_val_by_name('company_logo_dark')
    pos_logo = Utility.get_val_by_name('pos_logo')
    img = (
      default_storage.url(f'pos_logo/{pos_logo}')
      if pos_logo else logo_dir + (company_logo or 'logo-dark.png')
    )
    color_hex = f'#{color}'
    font_color = Utility.get_font_color(color_hex)
    # dummy customer
    customer = {
      'email': '<Email>',
      'shipping_name': '<Name>',
      'billing_name': '<Name>',
      # ...
    }
    pos = Pos()
    pos.pos_id = 1
    pos.issue_date = timezone.now()
    pos.itemData = items
    pos.totalTaxPrice = total_tax_price
    pos.totalQuantity = total_quantity
    pos.totalRate = total_rate
    pos.totalDiscount = total_discount
    pos.taxesData = taxes_data
    pos.created_by = u.creator_id()
    pos_payment = PosPayment()
    pos_payment.amount = 360
    pos_payment.discount = 100

    return render(
      request,
      f'pos/templates/{template}.html',
      {
        'pos': pos,
        'preview': True,
        'color': color_hex,
        'img': img,
        'settings': s,
        'customer': customer,
        'font_color': font_color,
        'pos_payment': pos_payment,
      }
    )

  def save_pos_template_settings(self, request: HttpRequest):
    perm = self._check_permission(request, 'manage pos')
    if perm is not True:
      return perm
    try:
      data = request.POST.dict()
      data.pop('_token', None)
      if 'pos_template' in data and not data.get('pos_color'):
        data['pos_color'] = 'ffffff'
      if logo := request.FILES.get('pos_logo'):
        if logo.content_type != 'image/png' or logo.size > 20480 * 1024:
          messages.error(request, 'Invalid pos logo upload')
          return redirect(get_redirect_url(request))
        fn = f"{request.user.id}_pos_logo.png"
        default_storage.save(f'pos_logo/{fn}', logo)
        data['pos_logo'] = fn
      for name, val in data.items():
        Setting.objects.update_or_create(
          name=name,
          created_by=request.user.creator_id(),
          defaults={'value': val}
        )
      messages.success(request, 'POS Setting updated successfully')
      return redirect(get_redirect_url(request))
    except Exception as e:
      return default_undefined_exception(
        request, e, ref=self._ref(), logger=self.logger
      )

  def print_view(self, request: HttpRequest):
    if not self._check_permission(request, 'manage pos'):
      return None
    try:
      sess = self._get_session_sales(request)
      user = request.user
      discount = int(request.POST.get('discount', 0))
      settings_data = Utility.settings()
      customer = Customer.objects.filter(
        name=request.POST.get('vc_name'),
        created_by=user.creator_id()
      ).first()
      warehouse = Warehouse.objects.filter(
        id=request.POST.get('warehouse_name'),
        created_by=user.creator_id()
      ).first()
      details = {
        'pos_id': user.pos_number_format(self.invoice_pos_number(request)),
        'date': timezone.now().date().isoformat(),
        'pay': 'show',
        'customer': model_to_dict(customer) if customer else {},
        'warehouse': model_to_dict(warehouse) if warehouse else {},
        'user': self._get_user_details(user),
      }
      if customer:
        bs = f", {customer.billing_state}" if customer.billing_state else ''
        ss = f", {customer.shipping_state}" if customer.shipping_state else ''
        details['customer']['details'] = (
          f'<h6 class="text-dark">{customer.name.capitalize()}'
          f'<p class="m-0 h6 font-weight-normal">'
          f'{customer.billing_phone}</p>'
          f'<p class="m-0 h6 font-weight-normal">'
          f'{customer.billing_address}</p>'
          f'<p class="m-0 h6 font-weight-normal">'
          f'{customer.billing_city}{bs}</p>'
          f'<p class="m-0 h6 font-weight-normal">'
          f'{customer.billing_country}</p>'
          f'<p class="m-0 h6 font-weight-normal">'
          f'{customer.billing_zip}</p></h6>'
        )
        details['warehouse']['details'] = (
          f'<h7 class="text-dark">'
          f'{warehouse.name.capitalize()}</p></h7>'
        )
        details['customer']['shippdetails'] = (
          f'<h6 class="text-dark"><b>'
          f'{customer.name.capitalize()}</b>'
          f'<p class="m-0 h6 font-weight-normal">'
          f'{customer.shipping_phone}</p>'
          f'<p class="m-0 h6 font-weight-normal">'
          f'{customer.shipping_address}</p>'
          f'<p class="m-0 h6 font-weight-normal">'
          f'{customer.shipping_city}{ss}</p>'
          f'<p class="m-0 h6 font-weight-normal">'
          f'{customer.shipping_country}</p>'
          f'<p class="m-0 h6 font-weight-normal">'
          f'{customer.shipping_zip}</p></h6>'
        )
      else:
        details['customer']['details'] = (
          '<h2 class="h6"><b>Walk-in Customer</b><h2>'
        )
        details['warehouse']['details'] = (
          f'<h7 class="text-dark">'
          f'{warehouse.name.capitalize()}</p></h7>'
        )
        details['customer']['shippdetails'] = '-'
      settings_data['company_telephone'] = (
        f", {settings_data['company_telephone']}"
        if settings_data.get('company_telephone') else ''
      )
      settings_data['company_state'] = (
        f", {settings_data['company_state']}"
        if settings_data.get('company_state') else ''
      )
      userdetails = (
        f'<h6 class="text-dark"><b>'
        f'{user.name.capitalize()}</b><h2 class="font-weight-normal">'
        f'<p class="m-0 font-weight-normal">'
        f'{settings_data["company_name"]}'
        f'{settings_data["company_telephone"]}'
        f'</p>'
        f'<p class="m-0 font-weight-normal">'
        f'{settings_data["company_address"]}'
        f'</p>'
        f'<p class="m-0 h6 font-weight-normal">'
        f'{settings_data["company_city"]}'
        f'{settings_data["company_state"]}'
        f'</p>'
        f'<p class="m-0 font-weight-normal">'
        f'{settings_data["company_country"]}'
        f'</p>'
        f'<p class="m-0 font-weight-normal">'
        f'{settings_data["company_zipcode"]}'
        f'</p></h2>'
      )
      details['user']['details'] = userdetails
      sales = self._build_sales_data(sess, user, discount)
      product_services = ProductService.objects.filter(
        created_by=user.creator_id()
      )
      barcode_conf = {
        'barcode_type': user.barcode_type(),
        'barcode_format': user.barcode_format(),
      }
      return render(request, 'pos/printview.html', {
        'details': details,
        'sales': sales,
        'customer': customer,
        'productServices': product_services,
        'barcode': barcode_conf
      })
    except Exception as e:
      return default_undefined_exception(
        request,
        e,
        ref=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=self.logger
      )

  @staticmethod
  def _decrypt_id(enc_id: str) -> int:
    signer = Signer()
    try:
      return int(signer.unsign(enc_id))
    except BadSignature as e:
      logger.error(f'Failed to decrypt POS id: {e}')
      raise PermissionError('Invalid POS identifier')
