from django.db import models
from .._helpers.connectors.project_connected import ProjectConnected
from .._helpers.describable import Describable
from .._helpers.fields import default_char_field, VALID_MYSQL_MIN_DATE, VOID
from ..individuals.user import User
class Task(Describable, ProjectConnected):
  title = default_char_field(db_index=True)
  agent_or_manager = models.ForeignKey('Employee', on_delete=models.SET_NULL, **VOID, db_index=True)
  date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], db_index=True)
  time = models.TimeField()
  module_type = default_char_field()
  module_id = models.CharField(max_length=36, db_index=True)
  assign_to = models.ForeignKey('User', on_delete=models.SET_NULL, null=True, related_name='assigned_tasks', db_index=True)
  milestone = models.ForeignKey('Milestone', on_delete=models.SET_NULL, null=True, related_name='tasks', db_index=True)

  def task_user(self) -> 'User':
    return self.assign_to

  def comments(self):
    return self.taskcomment_set.order_by('-id')

  def task_files(self):
    return self.taskfile_set.order_by('-id')

  def task_checklist(self):
    return self.checklist_set.order_by('-id')

  def task_complete_checklist_count(self) -> int:
    return self.checklist_set.filter(status='1').count()

  def task_total_checklist_count(self) -> int:
    return self.checklist_set.count()

  def __str__(self) -> str:
    return self.title
