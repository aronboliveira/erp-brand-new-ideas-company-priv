from django.db import models
from django.utils import timezone
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (default_char_field, uuid_def_primary, default_user_creation, 
                               default_text_field, VALID_MYSQL_MIN_DATE)
class Schedule(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  note = default_text_field(default='No notes were written')
  schedule_type = default_char_field(db_index=True)
  start_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE],default=timezone.now, db_index=True)
  start_time = models.TimeField()
  module_id = models.CharField(max_length=36, db_index=True)
  module_type = default_char_field(db_index=True)
  created_by = default_user_creation('schedule_created_by')
  class Meta:
    ordering = ['-id']
