import inspect
import logging
from django.contrib import messages
from django.core.exceptions import PermissionDenied, ValidationError
from django.core.validators import validate_email
from django.http import HttpRequest, HttpResponse, HttpResponseNotFound
from django.shortcuts import redirect, render
from .....Http.Controllers._helpers.http import get_redirect_url
from .....Http.Controllers._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .....Http.Controllers._traits.controller import Controller
from ...Entities.join_us import JoinUs
from ...Entities.landing_page_setting import LandingPageSetting

logger = logging.getLogger(__name__)

class JoinUsController(Controller):

  def index(self, request: HttpRequest) -> HttpResponse:
    C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name
    REF = f"{C}::{M}"
    try:
      self.authorize("view_any")
      if not request.user.is_superuser:
        raise PermissionDenied("Permission denied")
      entries = JoinUs.objects.all()
      return render(request, "landingpage/joinus.html", {"join_us": entries})
    except PermissionDenied as e:
      return default_permission_denial(request, err=e, ref=REF, logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e, ref=REF, logger=logger)

  def create(self, request: HttpRequest) -> HttpResponse:
    return render(request, "landingpage/create.html")

  def store(self, request: HttpRequest) -> HttpResponse:
    C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name
    REF = f"{C}::{M}"
    try:
      self.authorize("update")
      if request.method != "POST":
        return redirect(get_redirect_url(request, default="joinus_index"))
      data = {
        "joinus_status": "on" if request.POST.get("joinus_status") else "off",
        "joinus_heading": request.POST.get("joinus_heading", ""),
        "joinus_description": request.POST.get("joinus_description", "")
      }
      for k, v in data.items():
        LandingPageSetting.set_value(k, v)
      messages.success(request, "Setting updated successfully")
      return redirect(get_redirect_url(request, default="joinus_index"))
    except PermissionDenied as e:
      return default_permission_denial(request, err=e, ref=REF, logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e, ref=REF, logger=logger)

  def show(self, request: HttpRequest, id: int) -> HttpResponse:
    return render(request, "landingpage/joinus.html")

  def edit(self, request: HttpRequest, id: int) -> HttpResponse:
    return render(request, "landingpage/joinus.html")

  def update(self, request: HttpRequest, id: int) -> HttpResponse:
    return redirect("joinus_index")

  def destroy(self, request: HttpRequest, id: int) -> HttpResponse:
    C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name
    REF = f"{C}::{M}"
    try:
      self.authorize("delete")
      try:
        entry = JoinUs.objects.get(pk=id)
      except JoinUs.DoesNotExist:
        return HttpResponseNotFound("JoinUs entry not found")
      entry.delete()
      messages.success(request, "Entry deleted successfully")
      return redirect(get_redirect_url(request, default="joinus_index"))
    except PermissionDenied as e:
      return default_permission_denial(request, err=e, ref=REF, logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e, ref=REF, logger=logger)

  def join_us_user_store(self, request: HttpRequest) -> HttpResponse:
    C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name
    REF = f"{C}::{M}"
    try:
      if request.method != "POST":
        return redirect("joinus_index")
      email = request.POST.get("email", "").strip()
      try:
        validate_email(email)
      except ValidationError:
        messages.error(request, "Invalid email address")
        return redirect(get_redirect_url(request, default="joinus_index"))
      if JoinUs.objects.filter(email=email).exists():
        messages.error(request, "This email has already joined")
        return redirect(get_redirect_url(request, default="joinus_index"))
      JoinUs.objects.create(email=email)
      messages.success(request, "You are joined with our community")
      return redirect(get_redirect_url(request, default="joinus_index"))
    except Exception as e:
      return default_undefined_exception(request, err=e, ref=REF, logger=logger)
