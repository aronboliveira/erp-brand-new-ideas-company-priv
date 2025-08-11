import inspect
import logging
from typing import Union, Dict, Any
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.db.models import QuerySet
from django.http import (HttpRequest, HttpResponse, HttpResponseRedirect, 
                         HttpResponsePermanentRedirect, JsonResponse, Http404)
from django.shortcuts import redirect, render, get_object_or_404
from .._helpers.error_handlers import (
    default_permission_denial,
    default_undefined_exception,
)
from .._helpers.http import get_redirect_url
from .._traits.controller import Controller
from ....Models.individuals.designation import Designation
from ....Models.individuals.employee import Employee
from ....Models.planning.promotion import Promotion
from ....Models.utils.utility import Utility

logger = logging.getLogger(__name__)


class PromotionController(Controller):

    def __init__(self, **kwargs):
        super().__init__(**kwargs)
        self.logger = logger

    @classmethod
    def _check_permission(cls, request: HttpRequest, perm: str, json: bool = False) -> Union[Exception, bool]:
        self = cls()
        self.request = request
        try:
            self.authorize(perm)
            return True
        except Exception as e:
            return default_permission_denial(
                request,
                e,
                ref=f"{cls.__name__}::{inspect.currentframe().f_code.co_name}",
                logger=cls().logger,
                json={"error": "Permission denied."} if json else None,
            )

    def _get_user_promotions(self, request: HttpRequest) -> Union[HttpResponse, JsonResponse, None]:
        user = request.user
        qs = Promotion.objects.filter(created_by=user.creator_id())
        if user.type == "Employee":
            emp = Employee.objects.filter(user_id=user.id).first()
            if emp:
                qs = qs.filter(employee_id=emp.id)
        return qs

    def _pluck(self, qs: QuerySet, key: str, val: Any) -> Dict[Any, Any]:
        return dict(qs.values_list(key, val))

    def index(self, request: HttpRequest) -> Union[HttpResponse, JsonResponse, None]:
        REF=f"{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}"
        try:
            self._check_permission(request, "manage promotion")
            promotions = (
                self._get_user_promotions(request)
                .select_related("designation", "employee")
            )
            return render(request, "promotion/index.html", {"promotions": promotions})
        except PermissionDenied as e:
            return default_permission_denial(
                request,
                e,
                ref=REF,
                logger=self.logger,
                json={"error": "Permission denied."},
            )
        except Exception as e:
            return default_undefined_exception(
                request,
                e,
                ref=REF,
                logger=self.logger,
            )

    def create(self, request: HttpRequest) -> Union[HttpResponse, JsonResponse, None]:
        REF=f"{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}"
        try:
            self._check_permission(request, "create promotion", json=True)
            user = request.user
            designations = self._pluck(
                Designation.objects.filter(created_by=user.creator_id()), "id", "name"
            )
            employees = self._pluck(
                Employee.objects.filter(created_by=user.creator_id()), "id", "name"
            )
            return render(
                request,
                "promotion/create.html",
                {"designations": designations, "employees": employees},
            )
        except PermissionDenied as e:
            return default_permission_denial(
                request,
                e,
                ref=REF,
                logger=self.logger,
                json={"error": "Permission denied."},
            )
        except Exception as e:
            return default_undefined_exception(
                request,
                e,
                ref=REF,
                logger=self.logger,
            )

    def store(self, request: HttpRequest) -> Union[HttpResponse, JsonResponse, None]:
        REF=f"{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}"
        try:
            self._check_permission(request, "create promotion")
            data = request.POST
            required = ("employee_id", "designation_id", "promotion_title", "promotion_date")
            missing = [f for f in required if not data.get(f)]
            if missing:
                messages.error(
                    request,
                    f"{missing[0].replace('_',' ').capitalize()} is required",
                )
                return redirect(get_redirect_url(request))
            pmt_props = {}
            for k in (*required, 'description'):
              if k == 'description': setattr(pmt_props, k, data.get(k, ''))
              else: setattr(pmt_props, k, data.get(k))
            promotion = Promotion(
              **pmt_props,
              created_by=request.user.creator_id(),
            )
            promotion.save()
            settings = Utility.settings()
            if settings.get("promotion_sent") == 1:
                emp = Employee.objects.filter(id=promotion.employee_id).first()
                des = Designation.objects.filter(id=promotion.designation_id).first()
                promotion.designation = des.name if des else ""
                arr = {
                    "employee_name": emp.name if emp else "",
                    "promotion_designation": promotion.designation,
                    "promotion_title": promotion.promotion_title,
                    "promotion_date": promotion.promotion_date,
                }
                resp = Utility.send_email_template(
                    "promotion_sent", [emp.email] if emp else [], arr
                )
                msg = "Promotion successfully created."
                if resp and not resp.get("is_success") and resp.get("error"):
                    msg += f"<br><span class='text-danger'>{resp['error']}</span>"
                messages.success(request, msg)
            else:
                messages.success(request, "Promotion successfully created.")
            return redirect("promotion_index")
        except PermissionDenied as e:
            return default_permission_denial(
                request,
                e,
                ref=REF,
                logger=self.logger,
                json={"error": "Permission denied."},
            )
        except Exception as e:
            return default_undefined_exception(
                request,
                e,
                ref=REF,
                logger=self.logger,
            )

    def show(self, request: HttpRequest, promotion_id:str) -> Union[Http404, HttpResponseRedirect, HttpResponsePermanentRedirect]:
        try:
          get_object_or_404(Promotion, pk=promotion_id)
          return redirect(get_redirect_url(request) if request.META.get('HTTP_REFERER') else "promotion_index")
        except Exception as e:
            return default_undefined_exception(
                request,
                e,
                ref=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=self.logger,
            )

    def edit(self, request: HttpRequest, promotion_id:str) -> Union[HttpResponse, JsonResponse, None]:
        REF = f"{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}"
        try:
            self._check_permission(request, "edit promotion", json=True)
            promotion = Promotion.objects.filter(
                id=promotion_id, created_by=request.user.creator_id()
            ).first()
            if not promotion:
                raise PermissionDenied
            user = request.user
            designations = self._pluck(
                Designation.objects.filter(created_by=user.creator_id()), "id", "name"
            )
            employees = self._pluck(
                Employee.objects.filter(created_by=user.creator_id()), "id", "name"
            )
            return render(
                request,
                "promotion/edit.html",
                {
                    "promotion": promotion,
                    "designations": designations,
                    "employees": employees,
                },
            )
        except PermissionDenied as e:
            return default_permission_denial(
                request,
                e,
                ref=REF,
                logger=self.logger,
                json={"error": "Permission denied."},
            )
        except Exception as e:
            return default_undefined_exception(
                request,
                e,
                ref=REF,
                logger=self.logger,
            )

    def update(self, request: HttpRequest, promotion_id:str) -> Union[HttpResponse, JsonResponse, None]:
        REF = f"{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}"
        try:
            self._check_permission(request, "edit promotion")
            promotion = Promotion.objects.filter(
                id=promotion_id, created_by=request.user.creator_id()
            ).first()
            if not promotion:
                messages.error(request, "Permission denied.")
                return redirect(get_redirect_url(request))
            data = request.POST
            required = ("employee_id", "designation_id", "promotion_title", "promotion_date")
            missing = [f for f in required if not data.get(f)]
            if missing:
                messages.error(
                    request,
                    f"{missing[0].replace('_',' ').capitalize()} is required",
                )
                return redirect(get_redirect_url(request))
            for field in (
                "employee_id",
                "designation_id",
                "promotion_title",
                "promotion_date",
                "description",
            ):
                if data.get(field) is not None:
                    setattr(promotion, field, data.get(field))
            promotion.save()
            messages.success(request, "Promotion successfully updated.")
            return redirect("promotion_index")
        except PermissionDenied as e:
            return default_permission_denial(
                request,
                e,
                ref=REF,
                logger=self.logger,
            )
        except Exception as e:
            return default_undefined_exception(
                request,
                e,
                ref=REF,
                logger=self.logger,
            )

    def destroy(self, request: HttpRequest, promotion_id:str) -> Union[HttpResponse, JsonResponse, None]:
        REF = f"{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}"
        try:
            self._check_permission(request, "delete promotion")
            promotion = Promotion.objects.filter(
                id=promotion_id, created_by=request.user.creator_id()
            ).first()
            if not promotion:
                raise PermissionDenied
            promotion.delete()
            messages.success(request, "Promotion successfully deleted.")
            return redirect("promotion_index")
        except PermissionDenied as e:
            return default_permission_denial(
                request,
                e,
                ref=REF,
                logger=self.logger,
            )
        except Exception as e:
            return default_undefined_exception(
                request,
                e,
                ref=REF,
                logger=self.logger,
            )
