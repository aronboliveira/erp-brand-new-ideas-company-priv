import inspect, json, logging
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse, HttpResponseNotFound
from django.shortcuts import render, redirect
from .....Http.Controllers._traits.controller import Controller
from ...Entities.landing_page_setting import LandingPageSetting
from .....Http.Controllers._helpers.error_handlers import default_permission_denial, default_undefined_exception

logger = logging.getLogger(__name__)

class CustomPageController(Controller):

  def index(self, request: HttpRequest) -> HttpResponse:
    C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name 
    REF = f'{C}::{M}'
    try:
      self.authorize('viewAny')
      if not request.user.is_superuser: raise PermissionDenied('Permission denied.')
      settings_dict = LandingPageSetting.settings()
      pages_json = settings_dict.get('menubar_page','[]')
      try: pages = json.loads(pages_json)
      except json.JSONDecodeError as e: 
        logger.error(f"{REF} JSON parse error: {e}") 
        pages = []
      return render(request,'landingpage/menubar/index.html',
                    {'pages':pages,'settings':settings_dict})
    except PermissionDenied as e:
      return default_permission_denial(request,err=e,ref=REF,logger=logger)
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  def create(self, request: HttpRequest) -> HttpResponse:
    C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name 
    REF = f'{C}::{M}'
    try:
      self.authorize('create')
      return render(request,'landingpage/menubar/create.html')
    except PermissionDenied as e:
      return default_permission_denial(request,err=e,ref=REF,logger=logger)
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  def store(self, request: HttpRequest) -> HttpResponse:
    C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name 
    REF = f'{C}::{M}'
    try:
      self.authorize('create')
      if request.method != 'POST': return redirect('landingpage_index')
      settings_dict = LandingPageSetting.settings()
      data_list = json.loads(settings_dict.get('menubar_page','[]') or '[]')
      f = request.POST
      name = f.get('menubar_page_name','')
      content = f.get('menubar_page_contant','')
      template = f.get('template_name','')
      slug = name.lower().replace(' ','_')
      datas = {
        'menubar_page_name': name,
        'page_slug': slug,
        'template_name': template,
        'page_url': f.get('page_url','') if template=='page_url' else '',
        'menubar_page_contant': '' if template=='page_url' else content
      }
      for chk in ('header','footer','login'): datas[chk] = 'on' if f.get(chk) else 'off'
      data_list.append(datas)
      LandingPageSetting.set_value('menubar_page', json.dumps(data_list))
      messages.success(request,'Page added successfully.')
      return redirect('landingpage_index')
    except PermissionDenied as e:
      return default_permission_denial(request,err=e,ref=REF,logger=logger)
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  def edit(self, request: HttpRequest, key) -> HttpResponse:
    C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name 
    REF = f'{C}::{M}'
    try:
      self.authorize('update')
      settings_dict = LandingPageSetting.settings()
      pages_json = settings_dict.get('menubar_page','[]')
      try: pages = json.loads(pages_json)
      except json.JSONDecodeError: pages = []
      try: 
        idx = int(key) 
        page = pages[idx]
      except (IndexError,ValueError): return HttpResponseNotFound("Page not found.")
      return render(request,'landingpage/menubar/edit.html',
                    {'page':page,'key':idx})
    except PermissionDenied as e:
      return default_permission_denial(request,err=e,ref=REF,logger=logger)
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  def update(self, request: HttpRequest, key) -> HttpResponse:
    C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name 
    REF = f'{C}::{M}'
    try:
      self.authorize('update')
      if request.method != 'POST': return redirect('landingpage_index')
      settings_dict = LandingPageSetting.settings()
      data_json = settings_dict.get('menubar_page','[]')
      try: data_list = json.loads(data_json)
      except json.JSONDecodeError: data_list = []
      try: 
        idx = int(key) 
        data_list[idx]
      except (IndexError,ValueError): 
        return HttpResponseNotFound("Page not found.")
      f = request.POST
      name = f.get('menubar_page_name','')
      content = f.get('menubar_page_contant','')
      template = f.get('template_name','')
      page_url = f.get('page_url','')
      slug = name.lower().replace(' ','_')
      updated = {
        'menubar_page_name': name,
        'page_slug': slug,
        'template_name': template,
        'page_url': page_url if template=='page_url' else '',
        'menubar_page_contant': '' if template=='page_url' else content
      }
      for chk in ('login','header','footer'): updated[chk] = 'on' if f.get(chk) else 'off'
      data_list[idx] = updated
      LandingPageSetting.set_value('menubar_page', json.dumps(data_list))
      messages.success(request,'Page updated successfully.')
      return redirect('landingpage_index')
    except PermissionDenied as e:
      return default_permission_denial(request,err=e,ref=REF,logger=logger)
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  def destroy(self, request: HttpRequest, key) -> HttpResponse:
    C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name 
    REF = f'{C}::{M}'
    try:
      self.authorize('delete')
      if request.method != 'POST': return redirect('landingpage_index')
      settings_dict = LandingPageSetting.settings()
      pages_json = settings_dict.get('menubar_page','[]')
      try: pages = json.loads(pages_json)
      except json.JSONDecodeError: pages = []
      try: 
        idx = int(key) 
        pages.pop(idx)
      except (IndexError,ValueError): 
        return HttpResponseNotFound("Page not found.")
      LandingPageSetting.set_value('menubar_page', json.dumps(pages))
      messages.success(request,'Page deleted successfully.')
      return redirect('landingpage_index')
    except PermissionDenied as e:
      return default_permission_denial(request,err=e,ref=REF,logger=logger)
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  def custom_store(self, request: HttpRequest) -> HttpResponse:
    C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name 
    REF = f'{C}::{M}'
    try:
      self.authorize('update')
      if request.method != 'POST': return redirect('landingpage_index')
      f = request.POST 
      files = request.FILES 
      data = {}
      if 'site_logo' in files:
        sf = files['site_logo'] 
        ext = sf.name.split('.')[-1]
        fn = f"site_logo.{ext}"
        res = LandingPageSetting.upload_file(sf, fn, 'uploads/landing_page_image')
        if res.get('flag') == 0: 
          messages.error(request,res.get('msg','')) 
          return redirect('landingpage_index')
        data['site_logo'] = fn
      data['site_description'] = f.get('site_description','')
      for k, v in data.items(): LandingPageSetting.set_value(k, v)
      messages.success(request,'Page add successfully.')
      return redirect('landingpage_index')
    except PermissionDenied as e:
      return default_permission_denial(request,err=e,ref=REF,logger=logger)
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  def custom_page(self, request: HttpRequest, slug) -> HttpResponse:
    C, M = self.__class__.__name__, inspect.currentframe().f_code.co_name 
    REF = f'{C}::{M}'
    try:
      settings_dict = LandingPageSetting.settings()
      pages_json = settings_dict.get('menubar_page','[]')
      try: pages = json.loads(pages_json)
      except json.JSONDecodeError: pages = []
      for p in pages:
        if p.get('page_slug') == slug:
          return render(request,'landingpage/layouts/custompage.html',
                        {'page':p,'settings':settings_dict})
      return HttpResponseNotFound("Page not found.")
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)
