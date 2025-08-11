from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_char_field, default_user_creation, uuid_def_primary, VOID

class Note(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  note = models.TextField(max_length=65535, **VOID)
  module_id = models.CharField(max_length=63)
  module_type = default_char_field()
  created_by = default_user_creation('%(class)s_created_by', db_index=True)
  class Meta:
    ordering = ['-id']
