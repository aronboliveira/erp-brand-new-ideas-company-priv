import datetime
from django.db import models
from django.utils import timezone
from .._helpers.colorable import Colorable
from .._helpers.connectors.project_connected import ProjectConnected
from .._helpers.fields import default_char_field
class TaskStage(Colorable, ProjectConnected):
  name = default_char_field(db_index=True)
  complete = models.BooleanField(default=False)
  order = models.PositiveIntegerField(default=0, db_index=True)
  STAGES = ["Todo", "In Progress", "Review", "Done"]

  def __str__(self) -> str:
    return self.name

  @classmethod
  def get_chart_data(cls, user: any) -> dict:
    now = timezone.now()
    date_format = "%Y-%m-%d"
    labels = []
    arr_date = []
    for i in range(7):
      date_obj = now - datetime.timedelta(days=i)
      labels.append(date_obj.strftime("%a"))
      arr_date.append(date_obj.strftime(date_format))
    stages = cls.objects.filter(created_by=user.creator_id())
    arr_task = []
    from ..planning.project_task import ProjectTask
    if user.type == 'company':
      for stage in stages:
        data = [ProjectTask.objects.filter(stage=stage,updated_at__date=d).count() for d in arr_date]
        dataset = {'name': stage.name, 'backgroundColor': 'transparent','borderColor': stage.color, 'data': data}
        arr_task.append(dataset)
    elif user.type == 'client':
      for stage in stages:
        data = [ProjectTask.objects.filter(stage=stage,updated_at__date=d,project__client_id=user.id).count() for d in arr_date]
        dataset = {'name': stage.name, 'backgroundColor': 'transparent','borderColor': stage.color, 'data': data}
        arr_task.append(dataset)
    else:
      for stage in stages:
        data = [ProjectTask.objects.filter(assign_to__contains=str(user.id),stage=stage,updated_at__date=d).count() for d in arr_date]
        dataset = {'name': stage.name, 'backgroundColor': 'transparent','borderColor': stage.color, 'data': data}
        arr_task.append(dataset)
    if arr_task:
      arr_task[-1]['backgroundColor'] = '#ccc'
    chart_data = {'label': labels, 'dataset': arr_task}
    return chart_data
