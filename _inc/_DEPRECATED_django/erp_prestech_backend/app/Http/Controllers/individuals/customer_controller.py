import os
import time
import inspect
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core import signing
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.urls import reverse
from django.utils.decorators import method_decorator
from django.views.decorators.http import require_http_methods
from ....Imports.customer_import import CustomerImport
from ....Models.bills.transaction import Transaction
from ....Models.individuals.customer import Customer
from ....Models.individuals.user import User
from ....Models.planning.plan import Plan
from ....Models.shapes.custom_field import CustomField
from ....Models.utils.utility import Utility
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller

import logging
logger = logging.getLogger(__name__)

class CustomerController(Controller):

    @method_decorator(login_required)
    @classmethod
    def dashboard(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            data = {'invoice_chart_data': request.user.invoice_chart_data()}
            return render(request, 'customer/dashboard.html', data)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('manage customer'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=REF,
                    logger=logger
                )
            customers = Customer.objects.filter(created_by=request.user.creator_id)
            return render(request, 'customer/index.html', {'customers': customers})
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('create customer'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=REF,
                    logger=logger
                )
            cf = CustomField.objects.filter(
                created_by=request.user.creator_id, module='customer'
            )
            return render(request, 'customer/create.html', {'customFields': cf})
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def store(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('create customer'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=REF,
                    logger=logger
                )
            name, contact, email = (
                request.POST.get('name'),
                request.POST.get('contact'),
                request.POST.get('email'),
            )
            if not (name and contact and email):
                messages.error(request, "Name, contact and email are required.")
                return redirect(get_redirect_url(request))
            if Customer.objects.filter(
                email=email, created_by=request.user.creator_id
            ).exists():
                messages.error(request, "Email already exists.")
                return redirect(get_redirect_url(request))

            creator = User.objects.get(pk=request.user.creator_id)
            total_cust = request.user.count_customers()
            plan = Plan.objects.get(pk=creator.plan)
            default_lang = Utility.get_default_language(request.user.creator_id)

            if total_cust < plan.max_customers or plan.max_customers == -1:
                customer = Customer(
                    customer_id=cls.customer_number(request),
                    name=name,
                    contact=contact,
                    email=email,
                    tax_number=request.POST.get('tax_number'),
                    created_by=request.user.creator_id,
                    billing_name=request.POST.get('billing_name'),
                    billing_country=request.POST.get('billing_country'),
                    billing_state=request.POST.get('billing_state'),
                    billing_city=request.POST.get('billing_city'),
                    billing_phone=request.POST.get('billing_phone'),
                    billing_zip=request.POST.get('billing_zip'),
                    billing_address=request.POST.get('billing_address'),
                    shipping_name=request.POST.get('shipping_name'),
                    shipping_country=request.POST.get('shipping_country'),
                    shipping_state=request.POST.get('shipping_state'),
                    shipping_city=request.POST.get('shipping_city'),
                    shipping_phone=request.POST.get('shipping_phone'),
                    shipping_zip=request.POST.get('shipping_zip'),
                    shipping_address=request.POST.get('shipping_address'),
                    lang=default_lang
                )
                customer.save()
                CustomField.save_data(customer, request.POST.get('customField'))
            else:
                messages.error(request, "Your user limit is over, Please upgrade plan.")
                return redirect(get_redirect_url(request))

            setting = Utility.settings(request.user.creator_id)
            notif = {
                'user_name': request.user.name,
                'customer_name': customer.name,
                'customer_email': customer.email
            }
            if setting.get('twilio_customer_notification') == 1:
                Utility.send_twilio_msg(contact, 'new_customer', notif)

            messages.success(request, "Customer successfully created.")
            return redirect(reverse('customer_index'))
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def show(cls, request: HttpRequest, ids: str) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            id_val = signing.loads(ids)
            customer = Customer.objects.filter(pk=id_val).first()
            return render(request, 'customer/show.html', {'customer': customer})
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def edit(cls, request: HttpRequest, id: int) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('edit customer'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=REF,
                    logger=logger
                )
            cust = get_object_or_404(Customer, pk=id)
            cust.custom_field = CustomField.get_data(cust, 'customer')
            cf = CustomField.objects.filter(
                created_by=request.user.creator_id, module='customer'
            )
            return render(request, 'customer/edit.html', {
                'customer': cust, 'customFields': cf
            })
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def update(cls, request: HttpRequest, customer_id: int) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('edit customer'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=REF,
                    logger=logger
                )
            for f in ['name','contact']:
                if not request.POST.get(f):
                    messages.error(request, f"{f} is required.")
                    return redirect(get_redirect_url(request))
            cust = get_object_or_404(Customer, pk=customer_id)
            for attr in [
                'name','contact','email','tax_number',
                'billing_name','billing_country','billing_state',
                'billing_city','billing_phone','billing_zip',
                'billing_address','shipping_name','shipping_country',
                'shipping_state','shipping_city','shipping_phone',
                'shipping_zip','shipping_address'
            ]:
                setattr(cust, attr, request.POST.get(attr))
            cust.save()
            CustomField.save_data(cust, request.POST.get('customField'))
            messages.success(request, "Customer successfully updated.")
            return redirect(reverse('customer_index'))
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def destroy(cls, request: HttpRequest, customer_id: int) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('delete customer'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=REF,
                    logger=logger
                )
            cust = get_object_or_404(Customer, pk=customer_id)
            if cust.created_by != request.user.creator_id:
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=REF,
                    logger=logger
                )
            cust.delete()
            messages.success(request, "Customer successfully deleted.")
            return redirect(reverse('customer_index'))
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def customer_logout(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            from django.contrib.auth import logout
            logout(request)
            request.session.flush()
            return redirect(reverse('customer_login'))
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def payment(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('manage customer payment'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=REF,
                    logger=logger
                )
            cat = {'Invoice':'Invoice','Deposit':'Deposit','Sales':'Sales'}
            qry = Transaction.objects.filter(
                user_id=request.user.id, user_type="Customer", type="Payment"
            )
            if d := request.GET.get('date'):
                dr = [x.strip() for x in d.split(' - ')]
                qry = qry.filter(date__range=(dr[0], dr[1]))
            if c := request.GET.get('category'):
                qry = qry.filter(category=c)
            pays = qry.all()
            return render(request, 'customer/payment.html', {
                'payments': pays, 'category': cat
            })
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def transaction(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('manage customer payment'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=REF,
                    logger=logger
                )
            cat = {'Invoice':'Invoice','Deposit':'Deposit','Sales':'Sales'}
            qry = Transaction.objects.filter(
                user_id=request.user.id, user_type="Customer"
            )
            if d := request.GET.get('date'):
                dr = [x.strip() for x in d.split(' - ')]
                qry = qry.filter(date__range=(dr[0], dr[1]))
            if c := request.GET.get('category'):
                qry = qry.filter(category=c)
            txs = qry.all()
            return render(request, 'customer/transaction.html', {
                'transactions': txs, 'category': cat
            })
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def profile(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            ud = request.user
            ud.custom_field = CustomField.get_data(ud, 'customer')
            cf = CustomField.objects.filter(created_by=request.user.creator_id, module='customer')
            return render(request, 'customer/profile.html', {
                'userDetail': ud, 'customFields': cf
            })
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def edit_profile(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            from django.core.files.storage import default_storage
            ud = request.user
            user = get_object_or_404(Customer, pk=ud.id)
            # validation
            for f in ['name','email','contact']:
                if not request.POST.get(f):
                    messages.error(request, f"{f} is required.")
                    return redirect(get_redirect_url(request))
            # handle upload
            if fobj := request.FILES.get('profile'):
                base, ext = os.path.splitext(fobj.name)
                fname = f"{base}_{int(time.time())}{ext}"
                default_storage.save(f'uploads/avatar/{fname}', fobj)
                if ud.avatar:
                    old = os.path.join(os.getcwd(), 'uploads/avatar', str(ud.avatar))
                    if os.path.exists(old): os.remove(old)
                user.avatar = fname
            # update fields
            for fld in ['name','email','contact']:
                setattr(user, fld, request.POST.get(fld))
            user.save()
            CustomField.save_data(user, request.POST.get('customField'))
            messages.success(request, "Profile successfully updated.")
            return redirect(get_redirect_url(request))
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def edit_billing(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            ud = request.user
            user = get_object_or_404(Customer, pk=ud.id)
            for f in [
                'billing_name','billing_country','billing_state','billing_city',
                'billing_phone','billing_zip','billing_address'
            ]:
                if not request.POST.get(f):
                    messages.error(request, f"{f} is required.")
                    return redirect(get_redirect_url(request))
            data = request.POST.dict()
            user.__dict__.update(data)
            user.save()
            messages.success(request, "Profile successfully updated.")
            return redirect(get_redirect_url(request))
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def edit_shipping(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            ud = request.user
            user = get_object_or_404(Customer, pk=ud.id)
            for f in [
                'shipping_name','shipping_country','shipping_state','shipping_city',
                'shipping_phone','shipping_zip','shipping_address'
            ]:
                if not request.POST.get(f):
                    messages.error(request, f"{f} is required.")
                    return redirect(get_redirect_url(request))
            data = request.POST.dict()
            user.__dict__.update(data)
            user.save()
            messages.success(request, "Profile successfully updated.")
            return redirect(get_redirect_url(request))
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def change_language(cls, request: HttpRequest, lang: str) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            ud = request.user
            ud.lang = lang
            ud.save()
            messages.success(request, "Language Change Successfully!")
            return redirect(get_redirect_url(request))
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def export(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            from openpyxl import Workbook
            from datetime import datetime
            name = f"customer_{datetime.now():%Y-%m-%d_%H-%M-%S}.xlsx"
            wb = Workbook(); ws = wb.active; ws.title = 'Customers'
            headers = ['Customer ID','Name','Email','Contact','Billing Country','Billing City']
            ws.append(headers)
            for c in Customer.objects.all().values(
                'customer_id','name','email','contact','billing_country','billing_city'
            ):
                ws.append([
                    c.get('customer_id',''),
                    c.get('name',''),
                    c.get('email',''),
                    c.get('contact',''),
                    c.get('billing_country',''),
                    c.get('billing_city',''),
                ])
            response = HttpResponse(
                content_type='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            )
            response['Content-Disposition'] = f'attachment; filename={name}'
            wb.save(response)
            return response
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def import_file(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            return render(request, 'customer/import.html', {})
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)
          
    @method_decorator(login_required)
    @classmethod
    def import_customers(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if 'file' not in request.FILES:
                messages.error(request, "File is required and must be CSV or TXT.")
                return redirect(get_redirect_url(request))
            rows = CustomerImport().to_array(request.FILES['file'])[0]
            total = len(rows) - 1
            errors = []
            for row in rows[1:]:
                cust = Customer.objects.filter(email=row[2]).first() or Customer()
                if not cust.pk:
                    cust.customer_id = cls.customer_number(request)
                cust.customer_id = row[0]
                cust.name = row[1]
                cust.email = row[2]
                cust.contact = row[3]
                cust.is_active = 1
                cust.billing_name = row[4]
                cust.billing_country = row[5]
                cust.billing_state = row[6]
                cust.billing_city = row[7]
                cust.billing_phone = row[8]
                cust.billing_zip = row[9]
                cust.billing_address = row[10]
                cust.shipping_name = row[11]
                cust.shipping_country = row[12]
                cust.shipping_state = row[13]
                cust.shipping_city = row[14]
                cust.shipping_phone = row[15]
                cust.shipping_zip = row[16]
                cust.shipping_address = row[17]
                cust.balance = 0
                cust.created_by = request.user.creator_id
                try:
                    cust.save()
                except Exception:
                    errors.append(row)
            if not errors:
                status = {'status': 'success', 'msg': "Record successfully imported"}
            else:
                status = {
                    'status': 'error',
                    'msg': f"{len(errors)} Record imported fail out of {total} record"
                }
                request.session['errorArray'] = [','.join(map(str, r)) for r in errors]
            return redirect(get_redirect_url(request)).with_(**status)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def search_customers(cls, request: HttpRequest) -> HttpResponse:
        CNAME, MNAME = cls.__name__, inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('manage customer'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=REF,
                    logger=logger,
                    json={}
                )
            term = request.GET.get('search', '').strip()
            if request.is_ajax() and term:
                by_name = Customer.objects.filter(
                    is_active=1, created_by=request.user.creator_id
                ).filter(name__icontains=term)
                by_email = Customer.objects.filter(
                    is_active=1, created_by=request.user.creator_id
                ).filter(email__icontains=term)
                qs = by_name.union(by_email).values('id', 'name', 'email')
                return JsonResponse(list(qs), safe=False)
            return JsonResponse([], safe=False)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)