import logging
from django.contrib import messages
from django.db import transaction
from django.http import HttpRequest, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from .._helpers.http import get_redirect_url
from .._traits.controller import Controller
from ....Models.individuals.job_stage import JobStage

logger = logging.getLogger(__name__)

class JobStageController(Controller):
  def index(self, request: HttpRequest) -> HttpResponse:
    try:
      self.authorize('viewAny', ['JobStage'])
      stages = JobStage.objects.filter(created_by=request.user.creator_id()).order_by('order')
      return render(request, 'job_stage/index.html', {'stages': stages})
    except Exception as e:
      logger.error(f"Method index failed to list job stages: {e}")
      messages.error(request, str(e))
      return redirect(get_redirect_url(request))

  def create(self, request: HttpRequest) -> HttpResponse:
    try:
      self.authorize('create', ['JobStage'])
      return render(request, 'job_stage/create.html')
    except Exception as e:
      logger.error(f"Method create failed to render form: {e}")
      messages.error(request, str(e))
      return redirect(get_redirect_url(request))

  def store(self, request: HttpRequest) -> HttpResponse:
    try:
      self.authorize('create', ['JobStage'])
      data = request.POST
      if not data.get('title'):
        messages.error(request, 'Title is required.')
        return redirect(get_redirect_url(request))
      with transaction.atomic():
        js = JobStage(title=data['title'], created_by=request.user.creator_id())
        js.save()
      messages.success(request, 'Job stage successfully created.')
      return redirect(request.META.get('HTTP_REFERER', get_redirect_url(request)))
    except Exception as e:
      logger.error(f"Method store failed to save job stage: {e}")
      messages.error(request, str(e))
      return redirect(get_redirect_url(request))

  def show(self, request: HttpRequest, job_stage_id: int) -> HttpResponse:
    from ....Models.individuals.job_application import JobApplication
    try:
      self.authorize('view', ['JobStage', job_stage_id])
      js = get_object_or_404(JobStage, pk=job_stage_id)
      applications = JobApplication.objects.filter(created_by=request.user.creator_id(), job_stage=js)
      return render(request, 'job_stage/show.html', {'job_stage': js, 'applications': applications})
    except Exception as e:
      logger.error(f"Method show failed: {e}")
      messages.error(request, str(e))
      return redirect(get_redirect_url(request))

  def edit(self, request: HttpRequest, job_stage_id: int) -> HttpResponse:
    try:
      self.authorize('update', ['JobStage', job_stage_id])
      js = get_object_or_404(JobStage, pk=job_stage_id)
      return render(request, 'job_stage/edit.html', {'job_stage': js})
    except Exception as e:
      logger.error(f"Method edit failed: {e}")
      messages.error(request, str(e))
      return redirect(get_redirect_url(request))

  def update(self, request: HttpRequest, job_stage_id: int) -> HttpResponse:
    try:
      self.authorize('update', ['JobStage', job_stage_id])
      data = request.POST
      if not data.get('title'):
        messages.error(request, 'Title is required.')
        return redirect(get_redirect_url(request))
      js = get_object_or_404(JobStage, pk=job_stage_id)
      js.title = data['title']
      js.created_by = request.user.creator_id()
      js.save()
      messages.success(request, 'Job stage successfully updated.')
      return redirect(request.META.get('HTTP_REFERER', get_redirect_url(request)))
    except Exception as e:
      logger.error(f"Method update failed: {e}")
      messages.error(request, str(e))
      return redirect(get_redirect_url(request))

  def destroy(self, request: HttpRequest, job_stage_id: int) -> HttpResponse:
    try:
      self.authorize('delete', ['JobStage', job_stage_id])
      js = get_object_or_404(JobStage, pk=job_stage_id)
      if js.created_by != request.user.creator_id():
        messages.error(request, 'Permission denied.')
        return redirect(get_redirect_url(request))
      js.delete()
      messages.success(request, 'Job stage successfully deleted.')
      return redirect(request.META.get('HTTP_REFERER', get_redirect_url(request)))
    except Exception as e:
      logger.error(f"Method destroy failed: {e}")
      messages.error(request, str(e))
      return redirect(get_redirect_url(request))

  def order(self, request: HttpRequest) -> HttpResponse:
    try:
      self.authorize('update', ['JobStage'])
      order_list = request.POST.getlist('order[]') or request.POST.getlist('order')
      for idx, item in enumerate(order_list):
        js = get_object_or_404(JobStage, pk=item)
        js.order = idx
        js.save()
      return HttpResponse(status=204)
    except Exception as e:
      logger.error(f"Method order failed to reorder job stages: {e}")
      messages.error(request, str(e))
      return redirect(get_redirect_url(request))
