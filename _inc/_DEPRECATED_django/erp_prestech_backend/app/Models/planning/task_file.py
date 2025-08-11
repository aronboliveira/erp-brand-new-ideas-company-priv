from django.db import models
from django.core.files.storage import default_storage
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_char_field, default_user_creation, 
                               FILE_SIZE_VALIDATOR, DEFAULT_USER_TYPED, VALID_FILE_SIZES)
from typing import TYPE_CHECKING
if TYPE_CHECKING:
  from ..individuals.user import User
  from .task import Task
class TaskFile(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  file = models.FileField(upload_to='task_files/', storage=default_storage, validators=[FILE_SIZE_VALIDATOR], db_index=True)
  file_size = models.IntegerField(validators=VALID_FILE_SIZES, db_index=True)
  name = default_char_field()
  extension = models.CharField(max_length=10)
  file_size = models.IntegerField()
  task = models.ForeignKey('Task', on_delete=models.CASCADE, db_index=True)
  user_type = models.CharField(**DEFAULT_USER_TYPED, db_index=True)
  created_by = default_user_creation('%(class)s_created_by')
  
  def user(self) -> "User":
    return self.created_by

  def get_task(self) -> "Task":
    return self.task
  
  class Meta:
    db_table = 'task_file'
