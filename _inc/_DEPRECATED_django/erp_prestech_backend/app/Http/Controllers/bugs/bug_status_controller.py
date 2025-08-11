from django.contrib import messages
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import get_object_or_404, redirect, render
from ....Models.bugs.bug_status import BugStatus
from .._traits.controller import Controller
from .._helpers.http import get_redirect_url
from django.core.exceptions import PermissionDenied
from ....configs.messages_templates import get_exception_class_message

class BugStatusController(Controller):

  def index(self, request: HttpRequest) -> HttpResponse:
    try:
      bug_status = BugStatus.objects.filter(created_by=request.user.creator_id()).order_by('order')
      return render(request, 'bugstatus/index.html', {'bugStatus': bug_status})
    except Exception as e:
      print(f"[BugStatusController.index] Failed to load bug statuses: {e.__class__.__name__}: {e}")
      messages.error(request, "Error loading bug statuses.")
      return redirect(get_redirect_url(request))

  def create(self, request: HttpRequest) -> HttpResponse:
    try:
      return render(request, 'bugstatus/create.html', {})
    except Exception as e:
      print(f"[BugStatusController.create] Failed to load create view: {e.__class__.__name__}: {e}")
      messages.error(request, "Error loading create view.")
      return redirect(get_redirect_url(request))

  def store(self, request: HttpRequest) -> HttpResponse:
    try:
      if request.method == 'POST':
        title = request.POST.get('title')
        if not title or len(title) > 20:
          messages.error(request, "Title is required and must be at most 20 characters.")
          return redirect('bugstatus_index')
        all_status = BugStatus.objects.filter(created_by=request.user.creator_id()).order_by('-id').first()
        order_value = all_status.order + 1 if all_status else 0
        status = BugStatus(title=title, created_by=request.user.creator_id(), order=order_value)
        status.save()
        messages.success(request, "Bug status successfully created.")
        return redirect('bugstatus_index')
      else:
        messages.error(request, "Invalid request method.")
        return redirect('bugstatus_index')
    except Exception as e:
      print(f"[BugStatusController.store] Failed to store bug status: {e.__class__.__name__}: {e}")
      messages.error(request, "Error storing bug status.")
      return redirect(get_redirect_url(request))

  def edit(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      bug_status = get_object_or_404(BugStatus, pk=id)
      return (render(request, 'bugstatus/edit.html', {'bugStatus': bug_status})
              if bug_status.created_by == request.user.creator_id()
              else JsonResponse({'error': "Permission denied."}, status=401))
    except Exception as e:
      print(f"[BugStatusController.edit] Failed to load edit view: {e.__class__.__name__}: {e}")
      messages.error(request, "Error loading edit view.")
      return redirect(get_redirect_url(request))

  def update(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      bug_status = get_object_or_404(BugStatus, pk=id)
      if bug_status.created_by == request.user.creator_id():
        if request.method == 'POST':
          title = request.POST.get('title')
          if not title or len(title) > 20:
            messages.error(request, "Title is required and must be at most 20 characters.")
            return redirect('bugstatus_index')
          bug_status.title = title
          bug_status.save()
          messages.success(request, "Bug status successfully updated.")
          return redirect('bugstatus_index')
        else:
          messages.error(request, "Invalid request method.")
          return redirect('bugstatus_index')
      else:
        messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))
        return redirect(get_redirect_url(request))
    except Exception as e:
      print(f"[BugStatusController.update] Failed to update bug status: {e.__class__.__name__}: {e}")
      messages.error(request, "Error updating bug status.")
      return redirect(get_redirect_url(request))

  def destroy(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      bug_status = get_object_or_404(BugStatus, pk=id)
      if bug_status.created_by == request.user.creator_id():
        bug_status.delete()
        messages.success(request, "Bug status successfully deleted.")
        return redirect('bugstatus_index')
      else:
        messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))
        return redirect(get_redirect_url(request))
    except Exception as e:
      print(f"[BugStatusController.destroy] Failed to delete bug status: {e.__class__.__name__}: {e}")
      messages.error(request, "Error deleting bug status.")
      return redirect(get_redirect_url(request))

  def order(self, request: HttpRequest) -> JsonResponse:
    try:
      if request.method == 'POST':
        order_list = request.POST.getlist('order')
        for key, item in enumerate(order_list):
          status = BugStatus.objects.filter(id=item).first()
          if status:
            status.order = key
            status.save()
        return JsonResponse({'success': True})
      else:
        return JsonResponse({'error': "Invalid request method."}, status=400)
    except Exception as e:
      print(f"[BugStatusController.order] Failed to update order: {e.__class__.__name__}: {e}")
      return JsonResponse({'error': "Error updating order."}, status=500)
