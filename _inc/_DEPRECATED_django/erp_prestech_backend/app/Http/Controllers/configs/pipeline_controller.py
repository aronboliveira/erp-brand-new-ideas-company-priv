import inspect
import logging
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse
from django.shortcuts import render, redirect, get_object_or_404
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._helpers.http import get_redirect_url
from .._traits.controller import Controller
from ....Models.configs.pipeline import Pipeline
from ....Models.activity.deal import Deal
from ....Models.activity.deal_discussion import DealDiscussion
from ....Models.activity.deal_file import DealFile
from ....Models.activity.client_deal import ClientDeal
from ....Models.activity.user_deal import UserDeal
from ....Models.planning.deal_task import DealTask
from ....Models.activity.activity_log import ActivityLog

logger = logging.getLogger(__name__)

class PipelineController(Controller):
  @classmethod
  def index(cls, request: HttpRequest) -> HttpResponse:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls() 
    self.request = request
    try:
      self.authorize('manage_pipeline')
      pipelines = Pipeline.objects.filter(created_by=request.user.creator_id())
      return render(request, 'pipelines/index.html', {'pipelines': pipelines})
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
    self = cls() 
    self.request = request
    try:
      self.authorize('create_pipeline')
      return render(request, 'pipelines/create.html')
    except PermissionDenied as e:
      return default_permission_denial(
        request,
        err=e,
        ref=REF,
        logger=logger,
        json={'error': 'PermissionDenied.'}
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
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls() 
    self.request = request
    try:
      self.authorize('create_pipeline')
      name = request.POST.get('name', '').strip()
      if not name or len(name) > 20:
        messages.error(request, 'Name is required and must be <= 20 characters.')
        return redirect('pipelines.index')
      pipeline = Pipeline(name=name, created_by=request.user.creator_id()) 
      pipeline.save()
      messages.success(request, 'Pipeline successfully created!')
      return redirect('pipelines.index')
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
  def show(cls, request: HttpRequest, pipeline_id: str) -> HttpResponse:
    return redirect('pipelines.index')

  @classmethod
  def edit(cls, request: HttpRequest, pipeline_id: str) -> HttpResponse:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls() 
    self.request = request
    try:
      self.authorize('edit_pipeline')
      pipeline = get_object_or_404(Pipeline, pk=pipeline_id)
      if pipeline.created_by != request.user.creator_id():
        return default_permission_denial(
							request,
							err='ID Blocked.',
							ref=REF,
							logger=logger,
							json={'error': 'PermissionDenied.'}
						)
      return render(request, 'pipelines/edit.html', {'pipeline': pipeline})
    except PermissionDenied as e:
      return default_permission_denial(
        request,
        err=e,
        ref=REF,
        logger=logger,
        json={'error': 'PermissionDenied.'}
      )
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=REF,
        logger=logger
      )

  @classmethod
  def update(cls, request: HttpRequest, pipeline_id: str) -> HttpResponse:
    REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls() 
    self.request = request
    try:
      self.authorize('edit_pipeline')
      pipeline = get_object_or_404(Pipeline, pk=pipeline_id)
      if pipeline.created_by != request.user.creator_id():
        messages.error(request, 'Permission denied.')
        return redirect(get_redirect_url(request))
      name = request.POST.get('name', '').strip()
      if not name or len(name) > 20:
        messages.error(request, 'Name is required and must be <= 20 characters.')
        return redirect('pipelines.index')
      pipeline.name = name 
      pipeline.save()
      messages.success(request, 'Pipeline successfully updated!')
      return redirect('pipelines.index')
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
  def destroy(cls, request: HttpRequest, pipeline_id: str) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    self = cls(); self.request = request
    try:
      self.authorize('delete_pipeline');
      pipeline = get_object_or_404(Pipeline, pk=pipeline_id)
      if pipeline.created_by != request.user.creator_id():
        messages.error(request, 'Permission denied.');
        return redirect(get_redirect_url(request))
      if pipeline.stages.count() == 0:
        for stage in pipeline.stages.all():
          deals = Deal.objects.filter(pipeline_id=pipeline.id, stage_id=stage.id)
          for deal in deals:
            for _cls in (DealDiscussion, DealFile, ClientDeal,
                         UserDeal, DealTask, ActivityLog):
              _cls.objects.filter(deal_id=deal.id).delete()
            deal.delete()
          stage.delete()
        pipeline.delete()
        messages.success(request, 'Pipeline successfully deleted!');
      else:
        messages.error(
          request,
          'There are some Stages and Deals on Pipeline, please remove it first!'
        );
      return redirect('pipelines.index')
    except PermissionDenied as e:
      return default_permission_denial(request, err=e, ref=REF, logger=logger)
    except Exception as e:
      return default_undefined_exception(request, err=e, ref=REF, logger=logger)