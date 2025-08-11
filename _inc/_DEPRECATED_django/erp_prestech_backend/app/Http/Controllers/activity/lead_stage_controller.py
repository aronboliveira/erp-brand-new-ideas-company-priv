import logging
import inspect
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.db import transaction
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import get_object_or_404, redirect, render
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from ....Models.activity.lead_stage import LeadStage
from ....Models.configs.pipeline import Pipeline
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class LeadStageController(Controller):
    def __init__(self, **kwargs):
        super().__init__(**kwargs)
        self.middleware(['auth','XSS'])

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('manage lead stage'):
                raise PermissionDenied('User lacks permission: manage lead stage')
            qs = LeadStage.objects.select_related('pipeline').filter(
                pipeline__created_by=request.user.owner_id(),
                created_by=request.user.owner_id()
            ).order_by('pipeline_id','order')
            pipelines = {}
            for ls in qs:
                pid = ls.pipeline_id
                if pid not in pipelines:
                    pipelines[pid] = {'name': ls.pipeline.name, 'lead_stages': []}
                pipelines[pid]['lead_stages'].append(ls)
            return render(request, 'lead_stages/index.html', {'pipelines': pipelines})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('create lead stage'):
                raise PermissionDenied('User lacks permission: create lead stage')
            pipelines = Pipeline.objects.filter(
                created_by=request.user.owner_id()
            ).values_list('name','id')
            return render(request, 'lead_stages/create.html', {'pipelines': pipelines})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    @transaction.atomic
    def store(cls, request: HttpRequest) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('create lead stage'):
                raise PermissionDenied('User lacks permission: create lead stage')
            data = request.POST
            name = data.get('name')
            pid = data.get('pipeline_id')
            if not name or len(name) > 20 or not pid:
                messages.error(request, 'Name and Pipeline are required.')
                return redirect('lead_stages_index')
            ls = LeadStage(name=name, pipeline_id=pid, created_by=request.user.owner_id())
            ls.save()
            messages.success(request, 'Lead Stage successfully created!')
            return redirect('lead_stages_index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def edit(cls, request: HttpRequest, lead_stage_id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('edit lead stage'):
                raise PermissionDenied('User lacks permission: edit lead stage')
            ls = get_object_or_404(LeadStage, pk=lead_stage_id)
            if ls.created_by != request.user.owner_id():
                return default_permission_denial(request, err=PermissionDenied(), ref=REF, logger=logger, json={})
            pipelines = Pipeline.objects.filter(
                created_by=request.user.owner_id()
            ).values_list('name','id')
            return render(request, 'lead_stages/edit.html', {
                'leadStage': ls,
                'pipelines': pipelines
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def update(cls, request: HttpRequest, lead_stage_id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('edit lead stage'):
                raise PermissionDenied('User lacks permission: edit lead stage')
            ls = get_object_or_404(LeadStage, pk=lead_stage_id)
            if ls.created_by != request.user.owner_id():
                raise PermissionDenied('User lacks permission: edit lead stage')
            data = request.POST
            name = data.get('name')
            pid = data.get('pipeline_id')
            if not name or len(name) > 20 or not pid:
                messages.error(request, 'Name and Pipeline are required.')
                return redirect('lead_stages_index')
            ls.name = name
            ls.pipeline_id = pid
            ls.save()
            messages.success(request, 'Lead Stage successfully updated!')
            return redirect('lead_stages_index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def destroy(cls, request: HttpRequest, lead_stage_id: int) -> HttpResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('delete lead stage'):
                raise PermissionDenied('User lacks permission: delete lead stage')
            ls = get_object_or_404(LeadStage, pk=lead_stage_id)
            ls.delete()
            messages.success(request, 'Lead Stage successfully deleted!')
            return redirect('lead_stages_index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def order(cls, request: HttpRequest) -> JsonResponse:
        CNAME = cls.__name__
        MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if not request.user.has_perm('manage lead stage'):
                raise PermissionDenied('User lacks permission: manage lead stage')
            order_list = request.POST.getlist('order')
            for idx, item in enumerate(order_list):
                ls = LeadStage.objects.filter(id=item).first()
                if ls:
                    ls.order = idx
                    ls.save()
            return JsonResponse({'success': True})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger, json={})
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=REF,
                logger=logger,
                json='Operation failed.',
                status=500
            )
