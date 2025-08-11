from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_char_field, uuid_def_primary, default_user_creation

class TerminationType(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = default_char_field(db_index=True)
  created_by = default_user_creation('%(class)s_created_by')
  class Meta:
    db_table = 'termination_type'
    ordering = ['name']
    verbose_name = 'Termination Type'
    verbose_name_plural = 'Termination Types'
  def __str__(self) -> str:
    return self.name
