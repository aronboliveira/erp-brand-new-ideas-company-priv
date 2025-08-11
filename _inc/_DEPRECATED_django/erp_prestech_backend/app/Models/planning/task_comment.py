from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_user_creation, 
                               default_text_field, DEFAULT_USER_TYPED, NO_COMMENT)
from .._helpers.connectors.user_connected import UserConnected
from typing import TYPE_CHECKING
if TYPE_CHECKING:
  from ..individuals.user import User
class TaskComment(DefaultTimed, UserConnected):
  id = models.UUIDField(**uuid_def_primary())
  task = models.ForeignKey('Task', on_delete=models.CASCADE, db_index=True)
  comment = default_text_field(default=NO_COMMENT)
  user_type = models.CharField(**DEFAULT_USER_TYPED, db_index=True)
  created_by = default_user_creation('%(class)s_created_by')
  
  def user(self) -> "User":
    return self.created_by
  
  class Meta:
    db_table = 'task_comment'
