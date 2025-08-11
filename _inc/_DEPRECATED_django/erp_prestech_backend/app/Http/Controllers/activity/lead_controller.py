import logging
from typing import Any, Union
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse, JsonResponse, FileResponse
from django.shortcuts import get_object_or_404, redirect, render
import inspect
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._helpers.http import get_redirect_url
from .._traits.controller import Controller
from ....Models.utils.utility import Utility
from ....Models.activity.client_deal import ClientDeal
from ....Models.activity.deal import Deal
from ....Models.activity.deal_call import DealCall
from ....Models.activity.deal_discussion import DealDiscussion
from ....Models.activity.deal_file import DealFile
from ....Models.activity.lead import Lead
from ....Models.activity.lead_activity_log import LeadActivityLog
from ....Models.activity.lead_discussion import LeadDiscussion
from ....Models.activity.lead_file import LeadFile
from ....Models.activity.lead_stage import LeadStage
from ....Models.activity.source import Source
from ....Models.activity.stage import Stage
from ....Models.activity.user_deal import UserDeal
from ....Models.activity.user_lead import UserLead
from ....Models.configs.pipeline import Pipeline
from ....Models.contact.deal_email import DealEmail
from ....Models.contact.lead_call import LeadCall
from ....Models.contact.lead_email import LeadEmail
from ....Models.individuals.user import User
from ....Models.products.product_service import ProductService
from ....Models.shapes.label import Label

logger = logging.getLogger(__name__)

class LeadController(Controller):
 
  @classmethod
  def _set_auth(cls, request: HttpRequest, perm: str, suffix='') -> Union[Exception, bool]:
      self = cls()
      self.request = request
      self.authorize(f'{perm} lead{f' {suffix}' if suffix else ''}')
      return True
  
  @classmethod
  def index(cls, request: HttpRequest) -> HttpResponse:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      LeadController._set_auth(request, 'manage')
      user = request.user
      pipe_qs = Pipeline.objects.filter(created_by=user.creator_id())
      default = user.default_pipeline
      pipeline = pipe_qs.filter(id=default).first() if default else None
      pipeline = pipeline or pipe_qs.first()
      pipelines = pipe_qs.values_list('name','id')
      return render(request,'leads/index.html',{'pipelines':pipelines,'pipeline':pipeline})
    except PermissionDenied as e:
      return default_permission_denial(
        request,
        err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def lead_list(cls, request: HttpRequest) -> HttpResponse:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      LeadController._set_auth(request, 'manage')
      user = request.user
      pipe_qs = Pipeline.objects.filter(created_by=user.creator_id())
      default = user.default_pipeline
      pipeline = pipe_qs.filter(id=default).first() if default else None
      pipeline = pipeline or pipe_qs.first()
      pipelines = pipe_qs.values_list('name','id')
      leads = Lead.objects.filter(userlead__user_id=user.id,pipeline_id=pipeline.id).order_by('order')
      return render(request,'leads/list.html',{'pipelines':pipelines,'pipeline':pipeline,'leads':leads})
    except PermissionDenied as e:
      return default_permission_denial(
        request,
        err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def create(cls, request: HttpRequest) -> HttpResponse:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      LeadController._set_auth(request, 'create')
      users = User.objects.filter(created_by=request.user.creator_id()).exclude(type__in=['client','company'],id=request.user.id).values_list('name','id')
      return render(request,'leads/create.html',{'users':users})
    except PermissionDenied as e:
      return default_permission_denial(
        request,
        err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def store(cls, request: HttpRequest) -> HttpResponse:
    import json
    from json import JSONDecodeError
    from datetime import datetime
    from .._helpers.security import email_validation, user_id_validation
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      LeadController._set_auth(request, 'create')
      user = request.user
      data = request.POST
      email = ''
      errors = []
      for k in ('subject', 'name', 'email'):
        if not data.get(k):
          errors.append(f'{k.capitalize()} is required!')
        if k == 'email':
          email = k
      errors = email_validation(email, Lead.objects) + user_id_validation(
        data=data,
        manager='users',
        filters={'created_by': user.creator_id()}
      )
      if errors:
        logger.error(f'{cls.__name__}::{inspect.currentframe().f_code.co_name} validation failed: {f"\\n\\n".join(errors)}')
        messages.error(request, errors[0])
        return redirect(get_redirect_url(request))
      pipes = Pipeline.objects.filter(created_by=user.creator_id())
      default_pipe = pipes.filter(id=user.default_pipeline).first() or pipes.first()
      stage = LeadStage.objects.filter(pipeline_id=default_pipe.id).first()
      if not stage:
        messages.error(request,'Please Create Stage for This Pipeline.')
        return redirect(get_redirect_url(request))
      lead_props = {}
      for prop in ('name', 'email', 'phone', 'subject', 'user_id'):
        lead_props[prop] = data.get(prop)
      for k, v in {
        'pipeline_id': default_pipe.id,
        'stage_id': stage.id,
        'created_by': user.creator_id(),
        'date': datetime.date.today().isoformat()
      }.items():
        lead_props[k] = v
      lead = Lead(**lead_props)
      lead.save()
      usr_ids = [user.id] + ([int(data.get('user_id'))] if data.get('user_id')!=str(user.id) else [])
      for uid in usr_ids:
        UserLead.objects.create(user_id=uid,lead_id=lead.id)
      setting = Utility.settings(user.creator_id())
      lead_arr = {'user_name':user.name,'lead_name':lead.name,'lead_email':lead.email}
      if setting.get('lead_notification')==1: Utility.send_slack_msg('new_lead',lead_arr)
      if setting.get('telegram_lead_notification')==1: Utility.send_telegram_msg('new_lead',lead_arr)
      webhook = Utility.webhookSetting('New Lead')
      if webhook:
        try:
          status = Utility.WebhookCall(webhook['url'],json.dumps(lead.__dict__),webhook['method'])
          if not status:
            messages.error(request,'Webhook call failed.')
            return redirect(get_redirect_url(request))
        except JSONDecodeError as e:
          logger.error(f'{cls.__name__}::{inspect.currentframe().f_code.co_name} raised a JSON decoding error: {e}')
          messages.error(request,'Failed to decode json')
        except Exception as e:
          logger.error(f'{cls.__name__}::{inspect.currentframe().f_code.co_name} raised an undefined error: {e}')
          messages.error(request,'Failed to process webhook')
      messages.success(request,'Lead successfully created!')
      return redirect(get_redirect_url(request))
    except PermissionDenied as e:
      return default_permission_denial(
        request,
        err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def show(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      self = cls()
      self.request = request
      lead = get_object_or_404(Lead,pk=lead_id)
      if not lead.is_active:
        messages.error(request,'Permission Denied.')
        return redirect(get_redirect_url(request))
      calendar_tasks = []
      deal = Deal.objects.filter(id=lead.is_converted).first()
      stages = LeadStage.objects.filter(pipeline_id=lead.pipeline_id,created_by=lead.created_by)
      idx = next((i for i,s in enumerate(stages,1) if s.id==lead.stage_id),len(stages))
      percentage = f"{(idx*100)/len(stages):.0f}"
      return render(request,'leads/show.html',{'lead':lead,'calendarTasks':calendar_tasks,'deal':deal,'precentage':percentage})
    except PermissionDenied as e:
      return default_permission_denial(
        request,
        err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def edit(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      LeadController._set_auth(request, 'edit')
      lead = get_object_or_404(Lead,pk=lead_id)
      if lead.created_by != request.user.creator_id():
        raise PermissionDenied('User lacks permission.')
      pipelines = Pipeline.objects.filter(created_by=lead.created_by).values_list('name','id')
      sources = Source.objects.filter(created_by=lead.created_by).values_list('name','id')
      products = ProductService.objects.filter(created_by=lead.created_by).values_list('name','id')
      users = User.objects.filter(created_by=lead.created_by).exclude(type__in=['client','company'],id=request.user.id).values_list('name','id')
      lead.sources = lead.sources.split(',')
      lead.products = lead.products.split(',')
      return render(request,'leads/edit.html',{'lead':lead,'pipelines':pipelines,'sources':sources,'products':products,'users':users})
    except PermissionDenied as e:
      return default_permission_denial(
        request,
        err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def update(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
    from .._helpers.security import email_validation
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      LeadController._set_auth(request, 'edit')
      lead = get_object_or_404(Lead,pk=lead_id)
      if lead.created_by != request.user.creator_id():
        raise PermissionDenied('User lacks permission.')
      data = request.POST
      errors = []
      email = ''
      for k in ('name', 'email'):
        if not data.get(k):
          errors.append(f'{k.capitalize()} is required.')
        if k == 'email':
          email = k
      if email:
        errors.extend(email_validation(email, Lead.objects))
      field_mappings = {
        'name': ('name', None),
        'email': ('email', None),
        'phone': ('phone', None),
        'subject': ('subject', None),
        'user_id': ('user_id', None),
        'pipeline_id': ('pipeline_id', None),
        'stage_id': ('stage_id', None),
        'sources': ('sources', lambda x: ','.join(x.getlist('sources'))),
        'products': ('products', lambda x: ','.join(x.getlist('products'))),
        'notes': ('notes', None)
      }
      for attr, (data_key, processor) in field_mappings.items():
        value = processor(data) if processor else data.get(data_key)
        setattr(lead, attr, value)
      lead.save()
      messages.success(request,'Lead successfully updated!')
      return redirect(get_redirect_url(request))
    except PermissionDenied as e:
      return default_permission_denial(
        request,
        err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def destroy(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      LeadController._set_auth(request, 'delete')
      lead = get_object_or_404(Lead,pk=lead_id)
      if lead.created_by != request.user.creator_id():
        raise PermissionDenied('User lacks permission.')
      LeadDiscussion.objects.filter(lead_id=lead.id).delete()
      LeadFile.objects.filter(lead_id=lead.id).delete()
      UserLead.objects.filter(lead_id=lead.id).delete()
      LeadActivityLog.objects.filter(lead_id=lead.id).delete()
      lead.delete()
      messages.success(request,'Lead successfully deleted!')
      return redirect(get_redirect_url(request))
    except PermissionDenied as e:
      return default_permission_denial(
        request,
        err=e,
        ref=REF,
        logger=logger
      )
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def json(cls, request: HttpRequest) -> JsonResponse:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      stages = LeadStage.objects.filter(pipeline_id=request.GET.get('pipeline_id')).values_list('name','id') if request.GET.get('pipeline_id') else []
      return JsonResponse(dict(stages),safe=False)
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF,
        logger=logger
      )
  
  @classmethod
  def file_upload(cls, request: HttpRequest, lead_id: int) -> JsonResponse:
      import time
      import json
      handler_name = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
      try:
          LeadController._set_auth(request, 'edit')
          lead = get_object_or_404(Lead, pk=lead_id)
          if lead.created_by != request.user.creator_id():
              raise PermissionDenied('User lacks permission.')
          file = request.FILES['file']
          size = file.size
          res = Utility.updateStorageLimit(request.user.creator_id(), size)
          fname = file.name
          fpath = f"{lead.id}_{int(time.time())}_{fname}"
          iff = LeadFile.objects.create(lead_id=lead.id, file_name=fname, file_path=fpath)
          if res == 1:
              file.save(f'lead_files/{fpath}')
              result = {
                  'is_success': True,
                  'download': f"/leads/{lead.id}/download/{iff.id}/",
                  'delete': f"/leads/{lead.id}/delete/{iff.id}/"
              }
          else:
              result = {'is_success': True, 'status': 1, 'success_msg': res}
          LeadActivityLog.objects.create(
              user_id=request.user.id,
              lead_id=lead.id,
              log_type='Upload File',
              remark=json.dumps({'file_name': fname})
          )
          return JsonResponse(result, safe=False)
      except PermissionDenied as e:
          return default_permission_denial(request, ref=handler_name, err=e)
      except Exception as e:
          return default_undefined_exception(request, ref=handler_name, err=e)

  @classmethod
  def file_download(cls, request: HttpRequest, lead_id: int, file_id: int) -> Union[HttpResponse, FileResponse]:
      import os
      from django.conf import settings
      handler_name = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
      try:
          LeadController._set_auth(request, 'edit')
          lead = get_object_or_404(Lead, pk=lead_id)
          if lead.created_by != request.user.creator_id():
              raise PermissionDenied('User lacks permission.')
          lf = get_object_or_404(LeadFile, pk=file_id)
          return FileResponse(
              open(os.path.join(settings.MEDIA_ROOT, 'lead_files', lf.file_path), 'rb'),
              as_attachment=True,
              filename=lf.file_name
          )
      except PermissionDenied as e:
          return default_permission_denial(request, ref=handler_name, err=e)
      except Exception as e:
          return default_undefined_exception(request, ref=handler_name, err=e)

  @classmethod
  def file_delete(cls, request: HttpRequest, lead_id: int, file_id: int) -> JsonResponse:
      handler_name = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
      try:
          LeadController._set_auth(request, 'edit')
          lead = get_object_or_404(Lead, pk=lead_id)
          if lead.created_by != request.user.creator_id():
              raise PermissionDenied('User lacks permission.')
          file = get_object_or_404(LeadFile, pk=file_id)
          path = f"lead_files/{file.file_path}"
          Utility.changeStorageLimit(request.user.creator_id(), path)
          file.delete()
          return JsonResponse({'is_success': True}, safe=False)
      except PermissionDenied as e:
          return default_permission_denial(request, ref=handler_name, err=e)
      except Exception as e:
          return default_undefined_exception(request, ref=handler_name, err=e)

  @classmethod
  def note_store(cls, request: HttpRequest, lead_id: int) -> JsonResponse:
      handler_name = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
      try:
          LeadController._set_auth(request, 'edit')
          lead = get_object_or_404(Lead, pk=lead_id)
          if lead.created_by != request.user.creator_id():
              raise PermissionDenied('User lacks permission.')
          lead.notes = request.POST.get('notes')
          lead.save()
          return JsonResponse({'is_success': True, 'success': 'Note successfully saved!'}, safe=False)
      except PermissionDenied as e:
          return default_permission_denial(request, ref=handler_name, err=e)
      except Exception as e:
          return default_undefined_exception(request, ref=handler_name, err=e)

  @classmethod
  def labels(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
      handler_name = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
      try:
          LeadController._set_auth(request, 'edit')
          lead = get_object_or_404(Lead, pk=lead_id)
          labels = Label.objects.filter(
              pipeline_id=lead.pipeline_id,
              created_by=lead.created_by
          )
          selected = lead.labels().values_list('name', 'id') if lead.labels() else []
          return render(
              request,
              'leads/labels.html',
              {'lead': lead, 'labels': labels, 'selected': dict(selected)}
          )
      except PermissionDenied as e:
          return default_permission_denial(request, ref=handler_name, err=e)
      except Exception as e:
          return default_undefined_exception(request, ref=handler_name, err=e)

  @classmethod
  def label_store(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
      handler_name = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
      try:
          LeadController._set_auth(request, 'edit')
          lead = get_object_or_404(Lead, pk=lead_id)
          labels = request.POST.getlist('labels')
          lead.labels = ','.join(labels) if labels else ''
          lead.save()
          messages.success(request, 'Labels successfully updated!')
          return redirect(get_redirect_url(request))
      except PermissionDenied as e:
          return default_permission_denial(request, ref=handler_name, err=e)
      except Exception as e:
          return default_undefined_exception(request, ref=handler_name, err=e)

  @classmethod
  def user_edit_show(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
      handler_name = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
      try:
          LeadController._set_auth(request, 'edit')
          lead = get_object_or_404(Lead, pk=lead_id)
          users = User.objects.filter(created_by=lead.created_by)\
              .exclude(id__in=UserLead.objects.filter(lead_id=lead.id)\
              .values_list('user_id', flat=True))\
              .values_list('name', 'id')
          return render(request, 'leads/users.html', {'lead': lead, 'users': users})
      except PermissionDenied as e:
          return default_permission_denial(request, ref=handler_name, err=e)
      except Exception as e:
          return default_undefined_exception(request, ref=handler_name, err=e)

  @classmethod
  def user_update(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
      handler_name = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
      try:
          LeadController._set_auth(request, 'edit')
          lead = get_object_or_404(Lead, pk=lead_id)
          for uid in request.POST.getlist('users'):
              UserLead.objects.create(user_id=uid, lead_id=lead.id)
          messages.success(request, 'Users successfully updated!')
          return redirect(get_redirect_url(request))
      except PermissionDenied as e:
          return default_permission_denial(request, ref=handler_name, err=e)
      except Exception as e:
          return default_undefined_exception(request, ref=handler_name, err=e)

  @classmethod
  def user_destroy(cls, request: HttpRequest, lead_id: int, user_id: int) -> HttpResponse:
      handler_name = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
      try:
          LeadController._set_auth(request, 'edit')
          UserLead.objects.filter(lead_id=lead_id, user_id=user_id).delete()
          messages.success(request, 'User successfully deleted!')
          return redirect(get_redirect_url(request))
      except PermissionDenied as e:
          return default_permission_denial(request, ref=handler_name, err=e)
      except Exception as e:
          return default_undefined_exception(request, ref=handler_name, err=e)

  @classmethod
  def product_edit(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
      handler_name = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
      try:
          LeadController._set_auth(request, 'edit')
          lead = get_object_or_404(Lead, pk=lead_id)
          products = ProductService.objects \
              .filter(created_by=lead.created_by) \
              .exclude(id__in=lead.products.split(',')) \
              .values_list('name', 'id')
          return render(request, 'leads/products.html', {'lead': lead, 'products': products})
      except PermissionDenied as e:
          return default_permission_denial(request, ref=handler_name, err=e)
      except Exception as e:
          return default_undefined_exception(request, ref=handler_name, err=e)

  @classmethod
  def product_update(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
      handler_name = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
      try:
          LeadController._set_auth(request, 'edit')
          lead = get_object_or_404(Lead, pk=lead_id)
          new = request.POST.getlist('products')
          old = lead.products.split(',') if lead.products else []
          lead.products = ','.join(old + new)
          lead.save()
          messages.success(request, 'Products successfully updated!')
          return redirect(get_redirect_url(request))
      except PermissionDenied as e:
          return default_permission_denial(request, ref=handler_name, err=e)
      except Exception as e:
          return default_undefined_exception(request, ref=handler_name, err=e)

  @classmethod
  def product_destroy(cls, request: HttpRequest, lead_id: int, product_id: int) -> HttpResponse:
      handler_name = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
      try:
          LeadController._set_auth(request, 'edit')
          lead = get_object_or_404(Lead, pk=lead_id)
          prods = lead.products.split(',')
          prods.remove(str(product_id))
          lead.products = ','.join(prods)
          lead.save()
          messages.success(request, 'Products successfully deleted!')
          return redirect(get_redirect_url(request))
      except PermissionDenied as e:
          return default_permission_denial(request, ref=handler_name, err=e)
      except Exception as e:
          return default_undefined_exception(request, ref=handler_name, err=e)

  @classmethod
  def source_edit(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
      handler_name = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
      try:
          LeadController._set_auth(request, 'edit')
          lead = get_object_or_404(Lead, pk=lead_id)
          sources = Source.objects.filter(created_by=lead.created_by)
          selected = lead.sources().values_list('name', 'id') if lead.sources() else []
          return render(request, 'leads/sources.html',
                        {'lead': lead, 'sources': sources, 'selected': dict(selected)})
      except PermissionDenied as e:
          return default_permission_denial(request, ref=handler_name, err=e)
      except Exception as e:
          return default_undefined_exception(request, ref=handler_name, err=e)

  @classmethod
  def source_update(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
      handler_name = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
      try:
          LeadController._set_auth(request, 'edit')
          lead = get_object_or_404(Lead, pk=lead_id)
          labels = request.POST.getlist('sources')
          lead.sources = ','.join(labels)
          lead.save()
          messages.success(request, 'Sources successfully updated!')
          return redirect(get_redirect_url(request))
      except PermissionDenied as e:
          return default_permission_denial(request, ref=handler_name, err=e)
      except Exception as e:
          return default_undefined_exception(request, ref=handler_name, err=e)

  @classmethod
  def source_destroy(cls, request: HttpRequest, lead_id: int, source_id: int) -> HttpResponse:
      handler_name = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
      try:
          LeadController._set_auth(request, 'edit')
          lead = get_object_or_404(Lead, pk=lead_id)
          srcs = lead.sources.split(',')
          srcs.remove(str(source_id))
          lead.sources = ','.join(srcs)
          lead.save()
          messages.success(request, 'Sources successfully deleted!')
          return redirect(get_redirect_url(request))
      except PermissionDenied as e:
          return default_permission_denial(request, ref=handler_name, err=e)
      except Exception as e:
          return default_undefined_exception(request, ref=handler_name, err=e)

  @classmethod
  def discussion_create(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
      self = cls()
      self.request = request
      handler_name = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
      try:
          lead = get_object_or_404(Lead, pk=lead_id)
          if lead.created_by != request.user.creator_id():
              raise PermissionDenied
          return render(request, 'leads/discussions.html', {'lead': lead})
      except PermissionDenied as e:
          return default_permission_denial(request, ref=handler_name, err=e)
      except Exception as e:
          return default_undefined_exception(request, ref=handler_name, err=e)

  @classmethod
  def discussion_store(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
      self = cls()
      self.request = request
      handler_name = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
      try:
          lead = get_object_or_404(Lead, pk=lead_id)
          if lead.created_by != request.user.creator_id():
              raise PermissionDenied
          disc = LeadDiscussion()
          for k, v in {
              'comment': request.POST.get('comment'),
              'lead_id': lead.id,
              'created_by': request.user.id
          }.items():
              disc[k] = v
          disc.save()
          messages.success(request, 'Message successfully added!')
          return redirect(get_redirect_url(request))
      except PermissionDenied as e:
          return default_permission_denial(request, ref=handler_name, err=e)
      except Exception as e:
          return default_undefined_exception(request, ref=handler_name, err=e)
  
  @classmethod
  def order(cls, request: HttpRequest) -> JsonResponse:
      import json
      REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
      try:
          LeadController._set_auth(request, 'move')
          data = request.POST
          lid = data.get('lead_id')
          lead = cls.lead(lid)
          new_stage = data.get('stage_id')
          if lead.stage_id != int(new_stage):
              nxt = LeadStage.objects.get(id=new_stage)
              lead.stage_id = nxt.id
              lead.save()
              LeadActivityLog.objects.create(
                  user_id=request.user.id,
                  lead_id=lead.id,
                  log_type='Move',
                  remark=json.dumps({
                      'title': lead.name,
                      'old_status': lead.stage.name,
                      'new_status': nxt.name
                  })
              )
              Utility.sendEmailTemplate(
                  'Move Lead',
                  {u.id: u.email for u in lead.users},
                  {
                      'lead_name': lead.name,
                      'lead_pipeline': lead.pipeline.name,
                      'lead_old_stage': lead.stage.name,
                      'lead_new_stage': nxt.name
                  }
              )
          for idx, lid in enumerate(data.getlist('order')):
              ld = cls.lead(lid)
              ld.order = idx
              ld.stage_id = new_stage
              ld.save()
          return JsonResponse({'success': True}, safe=False)
      except PermissionDenied as e:
          return default_permission_denial(request, err=e, ref=REF, logger=logger)
      except Exception as e:
          return default_undefined_exception(request, err=e, ref=REF, logger=logger)

  @classmethod
  def lead(cls, item: Any) -> Lead:
      return Lead.objects.get(id=item)

  @classmethod
  def show_convert_to_deal(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
      try:
          lead = get_object_or_404(Lead, pk=lead_id)
          clients = User.objects.filter(type='client', created_by=request.user.creator_id())
          exist = clients.filter(email=lead.email).first()
          return render(request, 'leads/convert.html', {
              'lead': lead,
              'exist_client': exist,
              'clients': clients
          })
      except Exception as e:
          ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
          return default_undefined_exception(request, err=e, ref=ref, logger=logger)

  @classmethod
  def convert_to_deal(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
      import shutil
      from ....Models.individuals.role import Role
      try:
          lead = get_object_or_404(Lead, pk=lead_id)
          usr = request.user
          if request.POST.get('client_check') == 'exist':
              client = User.objects.filter(
                  type='client',
                  email=request.POST.get('clients'),
                  created_by=usr.creator_id()
              ).first()
              if not client:
                  messages.error(request, 'Client is not available now.')
              return redirect(get_redirect_url(request))
          else:
              from django.contrib.auth.hashers import make_password
              data = request.POST
              role = Role.objects.get(name='client')
              client = User.objects.create(
                  name=data.get('client_name'),
                  email=data.get('client_email'),
                  password=make_password(data.get('client_password')),
                  type='client',
                  lang='en',
                  created_by=usr.creator_id()
              )
              client.assign_role(role)
              Utility.sendEmailTemplate(
                  'New User',
                  {client.id: client.email},
                  {'email': data.get('client_email'), 'password': data.get('client_password')}
              )
          stage = Stage.objects.filter(pipeline_id=lead.pipeline_id).first()
          if not stage:
              messages.error(request, 'Please Create Stage for This Pipeline.')
              return redirect(get_redirect_url(request))
          is_transfer = request.POST.getlist('is_transfer')
          transfer_fields = ['sources', 'products', 'notes']
          transfer_values = {
              field: getattr(lead, field) if field in is_transfer else ''
              for field in transfer_fields
          }
          deal = Deal(
              name=request.POST.get('name'),
              price=request.POST.get('price'),
              pipeline_id=lead.pipeline_id,
              stage_id=stage.id,
              labels=lead.labels,
              status='Active',
              created_by=lead.created_by,
              **transfer_values
          )
          deal.save()
          ClientDeal.objects.create(deal_id=deal.id, client_id=client.id)
          Utility.sendEmailTemplate(
              'Assign Deal',
              {client.id: client.email},
              {
                  'deal_name': deal.name,
                  'deal_pipeline': Pipeline.objects.get(id=lead.pipeline_id).name,
                  'deal_stage': stage.name,
                  'deal_status': deal.status,
                  'deal_price': usr.price_format(deal.price)
              }
          )
          for ul in UserLead.objects.filter(lead_id=lead.id):
              UserDeal.objects.create(user_id=ul.user_id, deal_id=deal.id)
          if 'discussion' in is_transfer:
              for d in LeadDiscussion.objects.filter(lead_id=lead.id, created_by=usr.creator_id()):
                  DealDiscussion.objects.create(
                      deal_id=deal.id,
                      comment=d.comment,
                      created_by=d.created_by
                  )
          if 'files' in is_transfer:
              for f in LeadFile.objects.filter(lead_id=lead.id):
                  src = f"/storage/lead_files/{f.file_path}"
                  dst = f"/storage/deal_files/{f.file_path}"
                  shutil.copy(src, dst)
                  DealFile.objects.create(
                      deal_id=deal.id, file_name=f.file_name, file_path=f.file_path
                  )
          if 'calls' in is_transfer:
              for c in LeadCall.objects.filter(lead_id=lead.id):
                  DealCall.objects.create(
                      deal_id=deal.id,
                      subject=c.subject,
                      call_type=c.call_type,
                      duration=c.duration,
                      user_id=c.user_id,
                      description=c.description,
                      call_result=c.call_result
                  )
          if 'emails' in is_transfer:
              for e in LeadEmail.objects.filter(lead_id=lead.id):
                  DealEmail.objects.create(
                      deal_id=deal.id,
                      to=e.to,
                      subject=e.subject,
                      description=e.description
                  )
          lead.is_converted = deal.id
          lead.save()
          setting = Utility.settings(usr.creator_id())
          arr = {'lead_user_name': lead.name, 'lead_name': lead.name, 'lead_email': lead.email}
          if setting.get('leadtodeal_notification') == 1:
              Utility.send_slack_msg('lead_to_deal_conversion', arr)
          if setting.get('telegram_leadtodeal_notification') == 1:
              Utility.send_telegram_msg('lead_to_deal_conversion', arr)
          webhook = Utility.webhookSetting('Lead to Deal Conversion')
          if webhook:
              import json
              status = Utility.WebhookCall(webhook['url'], json.dumps(lead.__dict__), webhook['method'])
              if not status:
                  messages.error(request, 'Webhook call failed.')
              return redirect(get_redirect_url(request))
          messages.success(request, 'Lead sucessfully converted!')
          return redirect(get_redirect_url(request))
      except Exception as e:
          ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
          return default_undefined_exception(request, err=e, ref=ref, logger=logger)

  @classmethod
  def call_create(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
      try:
          LeadController._set_auth(request, perm='create', suffix='call')
          lead = get_object_or_404(Lead, pk=lead_id)
          users = UserLead.objects.filter(lead_id=lead.id)
          return render(request, 'leads/calls.html', {'lead': lead, 'users': users})
      except Exception as e:
          logger.error(f"{cls.__name__}::{inspect.currentframe().f_code.co_name} failed: {e}")
          return JsonResponse({'error': 'An error occurred.'}, status=500)

  @classmethod
  def call_store(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
      REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
      import json
      from .._helpers.security import user_id_validation
      try:
          LeadController._set_auth(request, perm='create', suffix='call')
          lead = get_object_or_404(Lead, pk=lead_id)
          data = request.POST
          errors = []
          for k in ('subject', 'call_type', 'user_id'):
              if not data.get(k):
                  errors.append(f'{k.capitalize().replace("_", " ")} is required.')
          if data.get('user_id'):
              errors.extend(user_id_validation(data=data, manager=UserLead.objects, filters={'lead_id': lead.id}))
          try:
              LeadCall.objects.create(
                  lead_id=lead.id,
                  subject=data.get('subject'),
                  call_type=data.get('call_type'),
                  duration=data.get('duration'),
                  user_id=data.get('user_id'),
                  description=data.get('description'),
                  call_result=data.get('call_result')
              )
              LeadActivityLog.objects.create(
                  user_id=request.user.id,
                  lead_id=lead.id,
                  log_type='create lead call',
                  remark=json.dumps({'title': 'Create new Lead Call'})
              )
          except json.JSONDecodeError as e:
              return default_undefined_exception(request, err=e, ref=REF, logger=logger)
          except Exception as e:
              return default_undefined_exception(request, err=e, ref=REF, logger=logger)
          messages.success(request, 'Call sucessfully created!')
          return redirect(get_redirect_url(request))
      except Exception as e:
          return default_undefined_exception(request, err=e, ref=REF, logger=logger)

  @classmethod
  def call_edit(cls, request: HttpRequest, lead_id: int, call_id: int) -> HttpResponse:
      try:
          LeadController._set_auth(request, perm='edit', suffix='call')
          lead = get_object_or_404(Lead, pk=lead_id)
          call = get_object_or_404(LeadCall, pk=call_id)
          users = UserLead.objects.filter(lead_id=lead.id)
          return render(request, 'leads/calls.html', {'lead': lead, 'call': call, 'users': users})
      except Exception as e:
          logger.error(f"{cls.__name__}::{inspect.currentframe().f_code.co_name} failed: {e}")
          return JsonResponse({'error': 'An error occurred.'}, status=500)

  @classmethod
  def call_update(cls, request: HttpRequest, lead_id: int, call_id: int) -> HttpResponse:
      try:
          LeadController._set_auth(request, perm='edit', suffix='call')
          data = request.POST
          call = get_object_or_404(LeadCall, pk=call_id)
          for k in ('subject', 'call_type', 'duration', 'user_id', 'description', 'call_result'):
              call[k] = data.get(k)
          call.save()
          messages.success(request, 'Call sucessfully updated!')
          return redirect(get_redirect_url(request))
      except Exception as e:
          ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
          return default_undefined_exception(request, err=e, ref=ref, logger=logger)

  @classmethod
  def call_destroy(cls, request: HttpRequest, lead_id: int, call_id: int) -> HttpResponse:
      try:
          LeadController._set_auth(request, perm='delete', suffix='call')
          get_object_or_404(LeadCall, pk=call_id).delete()
          messages.success(request, 'Call sucessfully deleted!')
          return redirect(get_redirect_url(request))
      except Exception as e:
          ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
          return default_undefined_exception(request, err=e, ref=ref, logger=logger)

  @classmethod
  def email_create(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
      try:
          LeadController._set_auth(request, perm='create', suffix='call')
          lead = get_object_or_404(Lead, pk=lead_id)
          return render(request, 'leads/emails.html', {'lead': lead})
      except Exception as e:
          logger.error(f"{cls.__name__}::{inspect.currentframe().f_code.co_name} failed: {e}")
          return JsonResponse({'error': 'An error occurred.'}, status=500)

  @classmethod
  def email_store(cls, request: HttpRequest, lead_id: int) -> HttpResponse:
      REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
      import json
      from django.core.exceptions import PermissionDenied
      from ....Mail.views.send_lead_email_api_view import SendLeadEmail
      from ....Models.contact.email import Email
      try:
          LeadController._set_auth(request, perm='create', suffix='call')
          lead = get_object_or_404(Lead, pk=lead_id)
          data = request.POST
          LeadEmail.objects.create(
              lead_id=lead.id,
              to=data.get('to'),
              subject=data.get('subject'),
              description=data.get('description')
          )
          try:
              Email.send(SendLeadEmail({
                  'lead_name': lead.name,
                  'to': data.get('to'),
                  'subject': data.get('subject'),
                  'description': data.get('description')
              }, Utility.settings()))
          except Exception as e:
              smtp_error = 'E-Mail has been not sent due to SMTP configuration failure or block'
              ref = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
              logger.error(f'{ref} failed to send lead email: {smtp_error}\n\n{e}')
          LeadActivityLog.objects.create(
              user_id=request.user.id,
              lead_id=lead.id,
              log_type='create lead email',
              remark=json.dumps({'title': 'Create new Deal Email'})
          )
          msg = f"Email successfully created!{('<br>'+smtp_error) if 'smtp_error' in locals() else ''}"
          messages.success(request, msg)
          return redirect(get_redirect_url(request))
      except PermissionDenied as e:
          return default_permission_denial(request, err=e, ref=REF, logger=logger)
      except Exception as e:
          return default_undefined_exception(request, err=e, ref=REF, logger=logger)