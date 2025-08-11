from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, default_char_field

class AllowanceOption(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = default_char_field()
  created_by = default_user_creation('%(class)s_created_by')

  def __str__(self) -> str:
    return self.name

  class Meta:
    ordering = ('-created_at',)
