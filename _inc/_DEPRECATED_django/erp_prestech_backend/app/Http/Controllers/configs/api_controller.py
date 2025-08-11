import base64
import logging
import os
from datetime import datetime
from django.conf import settings
from django.contrib.auth import authenticate
from django.contrib.auth.decorators import login_required
from django.contrib.auth.models import update_last_login
from django.http import JsonResponse, HttpRequest
from django.utils.timezone import now
from rest_framework.permissions import IsAuthenticated
from rest_framework.response import Response
from rest_framework.views import APIView
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class ApiController(Controller):
  @staticmethod
  @login_required
  def stop_tracker(request: HttpRequest) -> JsonResponse:
    try:
      from django.http import JsonResponse
      from ....Models.planning.time_tracker import TimeTracker
      from ....Models.utils.utility import Utility
      if request.user.is_client():
        return JsonResponse({'error': "Permission denied."}, status=403)
      name = request.POST.get('name')
      project_id = request.POST.get('project_id')
      if not (name and len(name) <= 120):
        return JsonResponse({'error': "Name is required and must be 120 characters or less."}, status=400)
      if not (project_id and str(project_id).isdigit()):
        return JsonResponse({'error': "Project ID is required and must be an integer."}, status=400)
      tracker = TimeTracker.objects.filter(created_by=request.user.id, is_active=1).first()
      if tracker:
        end_time = request.POST.get('end_time') or datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        tracker.end_time = end_time
        tracker.is_active = 0
        tracker.total_time = Utility.diffance_to_time(tracker.start_time, tracker.end_time)
        tracker.save()
        return JsonResponse({'success': "Add Time successfully."})
      return JsonResponse({'error': "Tracker not found."}, status=404)
    except Exception as e:
      logger.exception(f"ApiController.stop_tracker failed: {e}")
      return JsonResponse({'error': f"ApiController.stop_tracker: {e}"}, status=500)
  
  class LoginView(APIView):
    def post(self, request: HttpRequest) -> Response:
      from ....Models.utils.utility import Utility
      user = authenticate(request, username=request.data.get('email'), password=request.data.get('password'))
      if user is None:
        return Response({'error': 'Credentials not match'}, status=401)
      token = user.tokens.create(name="API Token").key
      update_last_login(None, user)
      settings_obj = Utility.settings(user.id)
      settings_payload = {'shot_time': settings_obj.get('interval_time') or 0.5}
      return Response({'token': token, 'user': user.id, 'settings': settings_payload}, status=200)
  
  class LogoutView(APIView):
    permission_classes = [IsAuthenticated]
    def post(self, request: HttpRequest) -> Response:
      try:
        request.user.auth_token.delete()
        return Response({'message': 'Tokens Revoked'}, status=200)
      except Exception as e:
        logger.exception(f"ApiController.LogoutView failed: {e}")
        return Response({'error': str(e)}, status=500)
  
  class GetProjectsView(APIView):
    permission_classes = [IsAuthenticated]
    def get(self, request: HttpRequest) -> Response:
      try:
        from ....Models.planning.project import Project
        from ....Models.planning.project_user import ProjectUser
        user = request.user
        projects = (Project.objects.filter(id__in=ProjectUser.objects.filter(user_id=user.id).values_list('project_id', flat=True)).prefetch_related('tasks').all() if user.type != 'company' else Project.objects.filter(created_by=user.id).prefetch_related('tasks').all())
        project_data = [p.serialize() for p in projects]
        return Response({'projects': project_data, 'message': 'Get Project List successfully.'})
      except Exception as e:
        logger.exception(f"ApiController.GetProjectsView failed: {e}")
        return Response({'error': str(e)}, status=500)
  
  class AddTrackerView(APIView):
    permission_classes = [IsAuthenticated]
    def post(self, request: HttpRequest) -> Response:
      try:
        from ....Models.utils.utility import Utility
        from ....Models.planning.time_tracker import TimeTracker
        from ....Models.planning.project_task import ProjectTask
        user = request.user
        action = request.data.get('action')
        if action == 'start':
          task_id = request.data.get('task_id')
          if not task_id:
            return Response({'error': 'task_id is required'}, status=401)
          task = ProjectTask.objects.filter(pk=task_id).first()
          if not task:
            return Response({'error': 'Invalid task'}, status=401)
          project_id = task.project_id if task.project_id else None
          TimeTracker.objects.filter(created_by=user.id, is_active=1).update(end_time=now())
          tracker = TimeTracker.objects.create(
            name=request.data.get('workin_on', ''),
            project_id=project_id,
            is_billable=request.data.get('is_billable') or 0,
            tag_id=request.data.get('workin_on', ''),
            start_time=request.data.get('time') or now(),
            task_id=task_id,
            created_by=user.id
          )
          tracker.action = 'start'
          return Response({'tracker': tracker.serialize(), 'message': 'Track successfully created.'})
        else:
          task_id = request.data.get('task_id')
          tracker_id = request.data.get('traker_id')
          if not task_id or not tracker_id:
            return Response({'error': 'task_id and traker_id are required'}, status=401)
          tracker = TimeTracker.objects.filter(id=tracker_id).first()
          if not tracker:
            return Response({'error': 'Tracker not found'}, status=404)
          tracker.end_time = request.data.get('time') or now()
          tracker.is_active = 0
          tracker.total_time = Utility.diffance_to_time(tracker.start_time, tracker.end_time)
          tracker.save()
          return Response({'tracker': tracker.serialize(), 'message': 'Stop time successfully.'})
      except Exception as e:
        logger.exception(f"ApiController.AddTrackerView failed: {e}")
        return Response({'error': str(e)}, status=500)
  
  class UploadImageView(APIView):
    permission_classes = [IsAuthenticated]
    def post(self, request: HttpRequest) -> Response:
      try:
        from ....Models.shapes.track_photo import TrackPhoto
        user = request.user
        image_data = base64.b64decode(request.data.get('img'))
        file_name = request.data.get('imgName')
        tracker_id = request.data.get('tracker_id')
        folder = os.path.join(settings.MEDIA_ROOT, 'uploads', 'traker_images', str(tracker_id) if tracker_id else '')
        os.makedirs(folder, exist_ok=True)
        path = os.path.join(folder, file_name)
        with open(path, 'wb') as f:
          f.write(image_data)
        TrackPhoto.objects.create(
          track_id=tracker_id,
          user_id=user.id,
          img_path=os.path.join('uploads/traker_images', str(tracker_id), file_name),
          time=request.data.get('time'),
          status=1
        )
        return Response({'message': 'Uploaded successfully.'})
      except Exception as e:
        logger.exception(f"ApiController.UploadImageView failed: {e}")
        return Response({'error': str(e)}, status=500)
