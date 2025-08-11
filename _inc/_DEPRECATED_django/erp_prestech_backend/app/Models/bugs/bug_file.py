from django.db import models
from django.core.files.storage import default_storage
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_user_creation, default_char_field, 
                               FILE_SIZE_VALIDATOR, VALID_FILE_SIZES, DEFAULT_USER_TYPED)

class BugFile(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  bug = models.ForeignKey('Bug', on_delete=models.CASCADE, db_index=True)
  file = models.FileField(upload_to='bug_files/', storage=default_storage, validators=[FILE_SIZE_VALIDATOR], db_index=True)
  file_size = models.IntegerField(validators=VALID_FILE_SIZES)
  name = default_char_field()
  extension = models.CharField(max_length=10)
  user_type = models.CharField(**DEFAULT_USER_TYPED)
  created_by = default_user_creation('%(class)s_created_by', db_index=True)

  class Meta:
    db_table = 'bug_file'
    ordering = ('-created_at',)
