from collections import defaultdict
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.core.exceptions import PermissionDenied
from django.db import transaction
from django.http import HttpRequest, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.utils.decorators import method_decorator
from ....Models.configs.pipeline import Pipeline
from ....Models.shapes.label import Label
from ....configs.messages_templates import get_exception_class_message
import logging
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class LabelController(Controller):
  @method_decorator(login_required)
  def get(self, request: HttpRequest, *args, **kwargs) -> HttpResponse:
    try:
      if not request.user.has_perm('crm.manage_label'): raise PermissionDenied
      labels = Label.objects.filter(created_by=request.user.id).select_related('pipeline').order_by('pipeline_id')
      pipelines = defaultdict(lambda: {'name': '', 'labels': []})
      for label in labels:
        pipelines[label.pipeline_id]['name'] = label.pipeline.name
        pipelines[label.pipeline_id]['labels'].append(label)
      return render(request, 'labels/index.html', {'pipelines': dict(pipelines)})
    except Exception as e:
      logger.exception(f"LabelController.index: {str(e)}") 
      messages.error(request, "Error loading labels") 
      return redirect('home')

  @method_decorator(login_required)
  def create(self, request: HttpRequest) -> HttpResponse:
    try:
      if not request.user.has_perm('crm.create_label'): raise PermissionDenied
      pipelines = Pipeline.objects.filter(created_by=request.user.id).values_list('id', 'name')
      return render(request, 'labels/create.html', {'pipelines': dict(pipelines), 'colors': Label.COLORS})
    except Exception as e:
      logger.exception(f"LabelController.create: {str(e)}") 
      messages.error(request, "Error loading form") 
      return redirect('labels.index')

  @transaction.atomic
  @method_decorator(login_required)
  def store(self, request: HttpRequest) -> HttpResponse:
    try:
      if not request.user.has_perm('crm.create_label'): return self._permission_denied()
      name = request.POST.get('name') 
      pipeline_id = request.POST.get('pipeline_id') 
      color = request.POST.get('color')
      if not all([name, pipeline_id, color]) or len(name) > 20: return self._validation_error('Invalid label data')
      
      Label.objects.create(
        name=name, color=color, pipeline_id=pipeline_id,
        created_by=request.user.id
      )
      return redirect('labels.index')
    except Exception as e:
      logger.exception(f"LabelController.store: {str(e)}") 
      messages.error(request, "Error creating label") 
      return redirect('labels.create')

  @method_decorator(login_required)
  def edit(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      label = get_object_or_404(Label, pk=id)
      if not request.user.has_perm('crm.edit_label') or label.created_by != request.user.id: raise PermissionDenied
      pipelines = Pipeline.objects.filter(created_by=request.user.id).values_list('id', 'name')
      return render(request, 'labels/edit.html', {'label': label, 'pipelines': dict(pipelines), 'colors': Label.COLORS})
    except Exception as e:
      logger.exception(f"LabelController.edit: {str(e)}") 
      messages.error(request, "Error loading label") 
      return redirect('labels.index')

  @transaction.atomic
  @method_decorator(login_required)
  def update(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      label = get_object_or_404(Label, pk=id)
      if not request.user.has_perm('crm.edit_label') or label.created_by != request.user.id: return self._permission_denied()
      name = request.POST.get('name') 
      pipeline_id = request.POST.get('pipeline_id') 
      color = request.POST.get('color')
      if not all([name, pipeline_id, color]) or len(name) > 20: return self._validation_error('Invalid label data')
      
      label.name = name 
      label.color = color 
      label.pipeline_id = pipeline_id 
      label.save()
      return redirect('labels.index')
    except Exception as e:
      logger.exception(f"LabelController.update: {str(e)}") 
      messages.error(request, "Error updating label") 
      return redirect('labels.edit', id=id)

  @transaction.atomic
  @method_decorator(login_required)
  def destroy(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      label = get_object_or_404(Label, pk=id)
      if not request.user.has_perm('crm.delete_label') or label.created_by != request.user.id: return self._permission_denied()
      label.delete() 
      return redirect('labels.index')
    except Exception as e:
      logger.exception(f"LabelController.destroy: {str(e)}") 
      messages.error(request, "Error deleting label") 
      return redirect('labels.index')

  def _permission_denied(self) -> HttpResponse:
    messages.error(self.request,get_exception_class_message(PermissionDenied, __class__.__name__)) 
    return redirect('labels.index')

  def _validation_error(self, msg: str) -> HttpResponse:
    messages.error(self.request, msg) 
    return redirect(self.request.META.get('HTTP_REFERER', 'labels.index'))