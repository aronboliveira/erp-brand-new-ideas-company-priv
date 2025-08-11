from datetime import datetime, timedelta
import logging
import uuid
from django.db import models
from ..individuals.user import User
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_char_field, default_user_creation,
                               COLOR_NAME_VALIDATOR)
class ProjectStages(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = default_char_field()
  color = models.CharField(max_length=100, validators=[COLOR_NAME_VALIDATOR])
  order = models.PositiveIntegerField(default=0)
  created_by = default_user_creation('%(class)s_created_by')

  class Meta:
    db_table = 'project_stages'
    ordering = ['-created_at']

  def tasks(self, project_id: uuid.UUID, user: 'User') -> list:
    try:
      from .task import Task
      return (Task.objects.filter(stage=self.id, project_id=project_id)
              .order_by('order').all() if user.type in ['client', 'company']
              else Task.objects.filter(stage=self.id, assign_to=user.id,
                                       project_id=project_id).order_by('order').all())
    except Exception as e:
      logging.error(f"Failed to get tasks in ProjectStages: {e}")
      return []

  @staticmethod
  def get_chart_data(user: 'User') -> dict:
    from .task import Task
    try:
      date_format = "%Y-%m-%d"
      arr_date = []
      arr_day = {"label": []}
      now = datetime.now()
      for i in range(7):
        date_obj = now - timedelta(days=i)
        arr_date.append(date_obj.strftime(date_format))
        arr_day["label"].append(date_obj.strftime("%a"))
      stages = ProjectStages.objects.filter(created_by=user.creator_id()).all()
      arr_task = []
      i = 0
      if user.type == 'company':
        for stage in stages:
          data = []
          for d in arr_date:
            count = Task.objects.filter(stage=stage.id,
                                        updated_at__date=d).count()
            data.append(count)
          dataset = {
            'label': stage.name,
            'fill': '!0',
            'backgroundColor': 'transparent',
            'borderColor': stage.color,
            'data': data
          } 
          arr_task.append(dataset)
          i += 1
        arr_task_data = {**arr_day, 'dataset': arr_task} 
        if arr_task_data['dataset']:
          arr_task_data['dataset'][i - 1].pop('fill', None)
          arr_task_data['dataset'][i - 1]['backgroundColor'] = '#ccc'
        return arr_task_data
      elif user.type == 'client':
        for stage in stages:
          data = []
          for d in arr_date:
            count = Task.objects.filter(project__client=user.id,
                                        stage=stage.id,
                                        updated_at__date=d).count()
            data.append(count)
          dataset = {
            'label': stage.name,
            'fill': '!0',
            'backgroundColor': 'transparent',
            'borderColor': stage.color,
            'data': data
          } 
          arr_task.append(dataset)
          i += 1
        arr_task_data = {**arr_day, 'dataset': arr_task} 
        if arr_task_data['dataset']:
          arr_task_data['dataset'][i - 1].pop('fill', None)
          arr_task_data['dataset'][i - 1]['backgroundColor'] = '#ccc'
        return arr_task_data
      else:
        for stage in stages:
          data = []
          for d in arr_date:
            count = Task.objects.filter(assign_to=user.id,
                                        stage=stage.id,
                                        updated_at__date=d).count()
            data.append(count)
          dataset = {
            'label': stage.name,
            'fill': '!0',
            'backgroundColor': 'transparent',
            'borderColor': stage.color,
            'data': data
          } 
          arr_task.append(dataset)
          i += 1
        arr_task_data = {**arr_day, 'dataset': arr_task} 
        if arr_task_data['dataset']:
          arr_task_data['dataset'][i - 1].pop('fill', None)
          arr_task_data['dataset'][i - 1]['backgroundColor'] = '#ccc'
        return arr_task_data
    except Exception as e:
      logging.error(f"Failed to get chart data in ProjectStages: {e}")
      return {}
