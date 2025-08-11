import inspect
import json
import logging
import time
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse, HttpResponseNotFound
from django.shortcuts import redirect, render
from .....Http.Controllers._helpers.error_handlers import (
  default_permission_denial, default_undefined_exception
)
from .....Http.Controllers._helpers.http import get_redirect_url
from .....Http.Controllers._traits.controller import Controller
from ...Entities.landing_page_setting import LandingPageSetting

logger = logging.getLogger(__name__)

class ScreenshotsController(Controller):

  def _load_list(self, key: str) -> list:
    C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name
    try:
      return json.loads(LandingPageSetting.landingPageSetting().get(key, '[]'))
    except json.JSONDecodeError as e:
      logger.error(f"{C}::{M} JSON parse error for {key}: {e}")
      return []

  def _persist(self, key: str, lst: list) -> None:
    LandingPageSetting.set_value(key, json.dumps(lst))

  def index(self, request: HttpRequest) -> HttpResponse:
    C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name
    REF = f"{C}::{M}"
    try:
      self.authorize("viewAny")
      if not request.user.is_superuser:
        raise PermissionDenied("Permission denied")
      settings_dict = LandingPageSetting.landingPageSetting()
      screenshots = self._load_list("screenshots")
      return render(request, "landingpage/screenshots/index.html", {
        "settings": settings_dict,
        "screenshots": screenshots
      })
    except PermissionDenied as e:
      return default_permission_denial(request, err=e, ref=REF, logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e, ref=REF, logger=logger)

  def create(self, request: HttpRequest) -> HttpResponse:
    self.authorize("create")
    return render(request, "landingpage/screenshots/create.html")

  def store(self, request: HttpRequest) -> HttpResponse:
    C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name
    REF = f"{C}::{M}"
    try:
      self.authorize("update")
      if request.method != "POST":
        return redirect("screenshots_index")
      lst = self._load_list("screenshots")
      entry = {"screenshots_heading": request.POST.get("screenshots_heading","")}
      if "screenshots" in request.FILES:
        f = request.FILES["screenshots"]
        ext = f.name.rsplit(".",1)[-1]
        fn = f"{int(time.time())}-screenshots.{ext}"
        res = LandingPageSetting.upload_file(f, fn, "uploads/landing_page_image")
        if res.get("flag") != 1:
          messages.error(request, res.get("msg","Upload error"))
          return redirect(get_redirect_url(request))
        entry["screenshots"] = fn
      lst.append(entry)
      self._persist("screenshots", lst)
      messages.success(request, "Screenshots added successfully")
      return redirect(get_redirect_url(request))
    except PermissionDenied as e:
      return default_permission_denial(request, err=e, ref=REF, logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e, ref=REF, logger=logger)

  def edit(self, request: HttpRequest, key) -> HttpResponse:
    self.authorize("update")
    lst = self._load_list("screenshots")
    try:
      idx = int(key)
      item = lst[idx]
    except (IndexError, ValueError):
      return HttpResponseNotFound("Screenshot item not found")
    return render(request, "landingpage/screenshots/edit.html", {"screenshot": item, "key": idx})

  def update(self, request: HttpRequest, key) -> HttpResponse:
    C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name
    REF = f"{C}::{M}"
    try:
      self.authorize("update")
      if request.method != "POST":
        return redirect("screenshots_index")
      lst = self._load_list("screenshots")
      try:
        idx = int(key)
        lst[idx]
      except (IndexError, ValueError):
        return HttpResponseNotFound("Screenshot item not found")
      entry = lst[idx]
      entry["screenshots_heading"] = request.POST.get("screenshots_heading","")
      if "screenshots" in request.FILES:
        f = request.FILES["screenshots"]
        ext = f.name.rsplit(".",1)[-1]
        fn = f"{int(time.time())}-screenshots.{ext}"
        res = LandingPageSetting.upload_file(f, fn, "uploads/landing_page_image")
        if res.get("flag") != 1:
          messages.error(request, res.get("msg","Upload error"))
          return redirect(get_redirect_url(request))
        entry["screenshots"] = fn
      lst[idx] = entry
      self._persist("screenshots", lst)
      messages.success(request, "Screenshots updated successfully")
      return redirect(get_redirect_url(request))
    except PermissionDenied as e:
      return default_permission_denial(request, err=e, ref=REF, logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e, ref=REF, logger=logger)

  def destroy(self, request: HttpRequest, key) -> HttpResponse:
    C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name
    REF = f"{C}::{M}"
    try:
      self.authorize("delete")
      if request.method != "POST":
        return redirect("screenshots_index")
      lst = self._load_list("screenshots")
      try:
        idx = int(key)
        lst.pop(idx)
      except (IndexError, ValueError):
        return HttpResponseNotFound("Screenshot item not found")
      self._persist("screenshots", lst)
      messages.success(request, "Screenshots deleted successfully")
      return redirect(get_redirect_url(request))
    except PermissionDenied as e:
      return default_permission_denial(request, err=e, ref=REF, logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e, ref=REF, logger=logger)
