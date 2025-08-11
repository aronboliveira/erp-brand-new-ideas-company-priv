import logging
from django.contrib import messages
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import get_object_or_404, redirect, render
from ....Models.activity.performance_type import PerformanceType
from ....Models.individuals.competencies import Competencies
from .._traits.controller import Controller
from .._helpers.http import get_redirect_url
from django.core.exceptions import PermissionDenied
from ....configs.messages_templates import get_exception_class_message
logger = logging.getLogger(__name__)
class CompetenciesController(Controller):
  def index(self, request: HttpRequest) -> HttpResponse:
    try:
      return (render(request, 'competencies/index.html', {'competencies': Competencies.objects.filter(created_by=request.user.creator_id())})
              if request.user.has_perm('Manage Competencies')
              else (messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__)), redirect(get_redirect_url(request)))[1])  # Logic should be refactored
    except Exception as e:
      logger.exception(f"CompetenciesController.index failed: {e}")
      messages.error(request, "An error occurred while fetching competencies.")
      return redirect(get_redirect_url(request))
  def create(self, request: HttpRequest) -> HttpResponse:
    try:
      performance_qs = PerformanceType.objects.filter(created_by=request.user.creator_id())
      performance = {pt.id: pt.name for pt in performance_qs}
      return render(request, 'competencies/create.html', {'performance': performance})
    except Exception as e:
      logger.exception(f"CompetenciesController.create failed: {e}")
      messages.error(request, "An error occurred.")
      return redirect(get_redirect_url(request))
  def store(self, request: HttpRequest) -> HttpResponse:
    try:
      return (redirect('competencies_index') 
              if request.user.has_perm('Create Competencies') and request.POST.get('name') and request.POST.get('type') 
              and (Competencies.objects.create(name=request.POST.get('name'), type=request.POST.get('type'), created_by=request.user.creator_id()) or True)
              and messages.success(request, "Competencies successfully created.")
              else (messages.error(request, "Name and Type are required."), redirect(get_redirect_url(request)))[1]
             ) if request.user.has_perm('Create Competencies') else (messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__)), redirect(get_redirect_url(request)))[1]
    except Exception as e:
      logger.exception(f"CompetenciesController.store failed: {e}")
      messages.error(request, "An error occurred while creating competencies.")
      return redirect(get_redirect_url(request))
  def show(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      return redirect('competencies_index')
    except Exception as e:
      logger.exception(f"CompetenciesController.show failed: {e}")
      messages.error(request, "An error occurred.")
      return redirect('competencies_index')
  def edit(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      competency = get_object_or_404(Competencies, pk=id)
      performance_qs = PerformanceType.objects.filter(created_by=request.user.creator_id())
      performance = {pt.id: pt.name for pt in performance_qs}
      return render(request, 'competencies/edit.html', {'competencies': competency, 'performance': performance})
    except Exception as e:
      logger.exception(f"CompetenciesController.edit failed: {e}")
      messages.error(request, "An error occurred while editing competencies.")
      return redirect(get_redirect_url(request))
  def update(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      return (redirect('competencies_index')
              if request.user.has_perm('Edit Competencies') and request.POST.get('name') and request.POST.get('type')
              and (lambda c: (setattr(c, 'name', request.POST.get('name')), setattr(c, 'type', request.POST.get('type')), c.save(), messages.success(request, "Competencies successfully updated."), True)(get_object_or_404(Competencies, pk=id))) 
              else (messages.error(request, "Name and Type are required."), redirect(get_redirect_url(request)))[1]
             ) if request.user.has_perm('Edit Competencies') else (messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__)), redirect(get_redirect_url(request)))[1]
    except Exception as e:
      logger.exception(f"CompetenciesController.update failed: {e}")
      messages.error(request, "An error occurred while updating competencies.")
      return redirect(get_redirect_url(request))
  def destroy(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      return (get_object_or_404(Competencies, pk=id).delete(), messages.success(request, "Competencies successfully deleted."), redirect('competencies_index'))[2] if request.user.has_perm('Delete Competencies') else (messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__)), redirect(get_redirect_url(request)))[1]
    except Exception as e:
      logger.exception(f"CompetenciesController.destroy failed: {e}")
      messages.error(request, "An error occurred while deleting competencies.")
      return redirect(get_redirect_url(request))
