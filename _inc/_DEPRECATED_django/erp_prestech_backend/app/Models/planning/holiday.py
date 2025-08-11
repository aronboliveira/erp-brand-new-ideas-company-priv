from django.db import models
from django.utils import timezone
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (default_char_field, default_user_creation, uuid_def_primary, 
                               VALID_MYSQL_MIN_DATE, VOID)

class Holiday(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now)
  end_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], **VOID)
  occasion = default_char_field()
  holiday_type = models.CharField(
      max_length=50,
      choices=[
          ('multiple_days', 'Multiple days'),
          ('full_day', 'Full Day'),
          ('half_day', 'Half Day')
      ],
      default='full_day'
  )
  created_by = default_user_creation("%(class)s_created_by")
  
  class Meta:
    db_table = "holiday"
  
  def __str__(self) -> str:
    return self.occasion
