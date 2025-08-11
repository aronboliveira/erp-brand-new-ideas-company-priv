import hashlib
import inspect
import logging
import time
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse
from django.shortcuts import redirect, render
from .....Http.Controllers._helpers.http import get_redirect_url
from .....Http.Controllers._traits.controller import Controller
from ...Entities.landing_page_setting import LandingPageSetting
from .....Http.Controllers._helpers.error_handlers import default_permission_denial, default_undefined_exception

logger = logging.getLogger(__name__)

class HomeController(Controller):

  @classmethod
  def _persist_settings(cls, data: dict) -> None:
    for k, v in data.items():
      LandingPageSetting.set_value(k, v)

  @classmethod
  def index(cls, request: HttpRequest) -> HttpResponse:
    C, M = cls.__name__, inspect.currentframe().f_code.co_name
    REF = f"{C}::{M}"
    try:
      cls.authorize(cls, "viewAny")
      if not request.user.is_superuser:
        raise PermissionDenied("Permission denied")
      settings_dict = getattr(LandingPageSetting, "landingPageSetting", 
                              LandingPageSetting.settings)()
      return render(request, "landingpage/homesection.html",
                    {"settings": settings_dict})
    except PermissionDenied as e:
      return default_permission_denial(request, err=e, ref=REF, logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e, ref=REF, logger=logger)

  @classmethod
  def create(cls, request: HttpRequest) -> HttpResponse:
    return render(request, "landingpage/create.html")

  @classmethod
  def store(cls, request: HttpRequest) -> HttpResponse:
    C, M = cls.__name__, inspect.currentframe().f_code.co_name
    REF = f"{C}::{M}"
    try:
      cls.authorize(cls, "update")
      if request.method != "POST":
        return redirect(get_redirect_url(request, default="home_index"))
      data = {"home_status": "on"}
      ref = request.META.get("HTTP_REFERER", "home_index")
      if "home_banner" in request.FILES:
        f = request.FILES["home_banner"]
        ext = f.name.rsplit(".", 1)[-1]
        fn = f"home_banner.{ext}"
        res = LandingPageSetting.upload_file(f, fn, "uploads/landing_page_image")
        if res.get("flag") != 1:
          messages.error(request, res.get("msg", "Upload error"))
          return redirect(get_redirect_url(request, default=ref))
        data["home_banner"] = fn
      saved = request.POST.get("savedlogo", "")
      keep = set(saved.split(",")) & set(
        LandingPageSetting.settings().get("home_logo", "").split(",") or []
      )
      if "home_logo" in request.FILES:
        urls = list(keep)
        for idx, file in enumerate(request.FILES.getlist("home_logo")):
          prefix = hashlib.md5(str(time.time()).encode()).hexdigest()
          name = f"{prefix}_{file.name}"
          res = LandingPageSetting.keyWiseUpload_file(
            request, "home_logo", name, "uploads/landing_page_image/", idx, []
          )
          if res.get("flag") == 1:
            urls.append(res.get("url"))
          else:
            messages.error(request, res.get("msg", "Upload error"))
            return redirect(get_redirect_url(request, default=ref))
        data["home_logo"] = ",".join(filter(None, urls))
      for fld in (
        "home_offer_text",
        "home_title",
        "home_heading",
        "home_description",
        "home_trusted_by",
        "home_live_demo_link",
        "home_buy_now_link",
      ):
        data[fld] = request.POST.get(fld, "")
      cls._persist_settings(data)
      messages.success(request, "Setting updated successfully.")
      return redirect(get_redirect_url(request, default=ref))
    except PermissionDenied as e:
      return default_permission_denial(request, err=e, ref=REF, logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e, ref=REF, logger=logger)

  @classmethod
  def show(cls, request: HttpRequest, id: int) -> HttpResponse:
    return render(request, "landingpage/show.html")

  @classmethod
  def edit(cls, request: HttpRequest, id: int) -> HttpResponse:
    return render(request, "landingpage/edit.html")

  @classmethod
  def update(cls, request: HttpRequest, id: int) -> HttpResponse:
    return redirect("home_index")

  @classmethod
  def destroy(cls, request: HttpRequest, id: int) -> HttpResponse:
    return redirect("home_index")
