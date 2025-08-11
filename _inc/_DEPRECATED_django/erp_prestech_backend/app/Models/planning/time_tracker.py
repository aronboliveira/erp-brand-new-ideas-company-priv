from django.db import models
from .._helpers.connectors.project_connected import ProjectConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_char_field, default_user_creation, 
                               UUID_VALIDATOR, VOID)
from ..utils.utility import Utility
class TimeTracker(DefaultTimed, ProjectConnected):
  id = models.UUIDField(**uuid_def_primary())
  task = models.ForeignKey('ProjectTask', on_delete=models.CASCADE, related_name='time_trackers', db_index=True)
  is_active = models.BooleanField(default=True)
  tag_id = models.CharField(**VOID, max_length=36, validators=[UUID_VALIDATOR], db_index=True)
  name = default_char_field()
  is_billable = models.BooleanField(default=False)
  start_time = models.DateTimeField(auto_now_add=True)
  end_time = models.DateTimeField(**VOID)
  total_time = models.PositiveIntegerField()
  created_by = default_user_creation('%(class)s_created_by', db_index=True)
  
  @property
  def project_name(self) -> str:
    return self.project.project_name if self.project else ''
  @property
  def project_task(self) -> str:
    return self.task.name if self.task else ''
  @property
  def total(self) -> str:
    return Utility.second_to_time(self.total_time) or "00:00:00"
  def __str__(self) -> str:
    return f"{self.name} ({self.project_name})"
