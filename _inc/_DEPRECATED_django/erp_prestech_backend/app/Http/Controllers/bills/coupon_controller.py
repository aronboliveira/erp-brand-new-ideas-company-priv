import logging
import inspect
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core import signing
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.urls import reverse
from django.utils.decorators import method_decorator
from ....Models.bills.coupon import Coupon
from ....Models.bills.user_coupon import UserCoupon
from ....Models.planning.plan import Plan
from ....Models.utils.utility import Utility
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class CouponController(Controller):

    @method_decorator(login_required)
    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('manage coupon'):
                raise PermissionDenied('User lacks permission: manage coupon')
            coupons = Coupon.objects.all()
            return render(request, 'coupon/index.html', {'coupons': coupons})
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('create coupon'):
                raise PermissionDenied('User lacks permission: create coupon')
            return render(request, 'coupon/create.html', {})
        except PermissionDenied as err:
            return default_permission_denial(
                request,
                err=err,
                ref=REF,
                logger=logger,
                json={'error': 'Permission denied.'}
            )
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def store(cls, request: HttpRequest) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('create coupon'):
                raise PermissionDenied('User lacks permission: create coupon')
            name = request.POST.get('name')
            discount = request.POST.get('discount')
            limit = request.POST.get('limit')
            if not name or not discount or not limit:
                messages.error(request, "Name, discount and limit are required.")
                return redirect(get_redirect_url(request))
            try:
                discount = float(discount)
                limit = int(limit)
            except Exception:
                messages.error(request, "Discount and limit must be numeric.")
                return redirect(get_redirect_url(request))
            if not request.POST.get('manualCode') and not request.POST.get('autoCode'):
                messages.error(request, "Coupon code is required.")
                return redirect(get_redirect_url(request))
            coupon = Coupon(
                name=name,
                discount=discount,
                limit=limit,
                code=(request.POST.get('manualCode').upper()
                      if request.POST.get('manualCode')
                      else request.POST.get('autoCode'))
            )
            coupon.save()
            messages.success(request, "Coupon successfully created.")
            return redirect(reverse('coupons_index'))
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def show(cls, request: HttpRequest, coupon_id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            coupon = get_object_or_404(Coupon, pk=coupon_id)
            user_coupons = UserCoupon.objects.filter(coupon=coupon.id).select_related('user_detail')
            return render(request, 'coupon/view.html', {'userCoupons': user_coupons})
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def edit(cls, request: HttpRequest, coupon_id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('edit coupon'):
                raise PermissionDenied('User lacks permission: edit coupon')
            coupon = get_object_or_404(Coupon, pk=coupon_id)
            return render(request, 'coupon/edit.html', {'coupon': coupon})
        except PermissionDenied as err:
            return default_permission_denial(
                request,
                err=err,
                ref=REF,
                logger=logger,
                json={'error': 'Permission denied.'}
            )
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def update(cls, request: HttpRequest, coupon_id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('edit coupon'):
                raise PermissionDenied('User lacks permission: edit coupon')
            name = request.POST.get('name')
            discount = request.POST.get('discount')
            limit = request.POST.get('limit')
            code = request.POST.get('code')
            if not name or not discount or not limit or not code:
                messages.error(request, "Name, discount, limit and code are required.")
                return redirect(get_redirect_url(request))
            try:
                discount = float(discount)
                limit = int(limit)
            except Exception:
                messages.error(request, "Discount and limit must be numeric.")
                return redirect(get_redirect_url(request))
            coupon = get_object_or_404(Coupon, pk=coupon_id)
            coupon.name = name
            coupon.discount = discount
            coupon.limit = limit
            coupon.code = code
            coupon.save()
            messages.success(request, "Coupon successfully updated.")
            return redirect(reverse('coupons_index'))
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def destroy(cls, request: HttpRequest, coupon_id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('delete coupon'):
                raise PermissionDenied('User lacks permission: delete coupon')
            coupon = get_object_or_404(Coupon, pk=coupon_id)
            coupon.delete()
            messages.success(request, "Coupon successfully deleted.")
            return redirect(reverse('coupons_index'))
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    def apply_coupon(cls, request: HttpRequest) -> JsonResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            plan = Plan.objects.filter(pk=signing.loads(request.POST.get('plan_id'))).first()
            if not request.user.has_perm('apply coupon'):
                raise PermissionDenied('User lacks permission: apply coupon')
            if plan and request.POST.get('coupon', '') != '':
                original_price = cls.format_price(plan.price)
                coupons = Coupon.objects.filter(code=request.POST.get('coupon').upper(), is_active=1).first()
                if coupons and coupons.limit == coupons.used_coupon():
                    return JsonResponse({
                        'is_success': False,
                        'final_price': original_price,
                        'price': format(plan.price, f".{Utility.get_val_by_name('decimal_number')}f"),
                        'message': "This coupon code has expired."
                    })
                if coupons:
                    discount_amount = (plan.price / 100) * coupons.discount
                    return JsonResponse({
                        'is_success': True,
                        'discount_price': '-' + cls.format_price(discount_amount),
                        'final_price': cls.format_price(plan.price - discount_amount),
                        'price': format(plan.price - discount_amount, f".{Utility.get_val_by_name('decimal_number')}f"),
                        'message': "Coupon code has applied successfully."
                    })
            # fallback
            return JsonResponse({
                'is_success': False,
                'final_price': cls.format_price(plan.price if plan else 0),
                'price': format(plan.price if plan else 0, f".{Utility.get_val_by_name('decimal_number')}f"),
                'message': "This coupon code is invalid or has expired."
            })
        except PermissionDenied as err:
            return default_permission_denial(
                request,
                err=err,
                ref=REF,
                logger=logger,
                json={'error': 'Permission denied.'}
            )
        except Exception as err:
            return default_undefined_exception(
                request,
                err=err,
                ref=REF,
                logger=logger,
                json={'error': f"{CNAME}.{MNAME}: Error"},
                status=500
            )

    @classmethod
    def format_price(cls, price: float) -> str:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            admin_payment_setting = Utility.get_admin_payment_setting()
            currency = admin_payment_setting.get('currency', '$')
            decimal_number = Utility.get_val_by_name('decimal_number')
            return f"{currency}{format(price, f'.{decimal_number}f')}"
        except Exception as err:
            logger.error(f"{REF} Failed to format price: {err.__class__.__name__}: {err}")
            # TODO: consider raising or defaulting differently
            return f"${price}"
