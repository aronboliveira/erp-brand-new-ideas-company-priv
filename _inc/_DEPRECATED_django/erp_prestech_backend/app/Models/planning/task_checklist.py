from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_char_field, default_user_creation,
                               COMPLETION_CHOICES, DEFAULT_USER_TYPED)
from typing import TYPE_CHECKING
if TYPE_CHECKING:
  from ..individuals.user import User
class TaskChecklist(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = default_char_field(db_index=True)
  task = models.ForeignKey('Task', on_delete=models.CASCADE, db_index=True)
  user_type = models.CharField(**DEFAULT_USER_TYPED, db_index=True)
  created_by = default_user_creation('%(class)s_created_by')
  status = models.CharField(max_length=50, default='pending', choices=COMPLETION_CHOICES)
  
  def user(self) -> "User":
    return self.created_by
  
  class Meta:
    db_table = 'task_checklist'
