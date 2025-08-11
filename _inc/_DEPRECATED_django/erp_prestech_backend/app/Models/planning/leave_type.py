from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (default_char_field, uuid_def_primary, default_user_creation,
                               DAYS_INTERVAL_VALIDATOR)

class LeaveType(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  title = default_char_field()
  days = models.IntegerField(default=1, validators=DAYS_INTERVAL_VALIDATOR)
  created_by = default_user_creation('%(class)s_created_by')
  
  class Meta:
    db_table = 'leave_types'
  def __str__(self) -> str:
    return f"{self.title} ({self.days} days)"

