from django.db import models
from django.utils import timezone
from .._helpers.describable import Describable
from .._helpers.fields import default_char_field, VALID_MYSQL_MIN_DATE, COLOR_NAME_VALIDATOR, VOID
from .._helpers.connectors.employee_connected import EmployeeConnected
class Event(Describable, EmployeeConnected):
  title = default_char_field()
  start_date = models.DateField(db_index=True, validators=[VALID_MYSQL_MIN_DATE], default=timezone.now)
  end_date = models.DateField(db_index=True, validators=[VALID_MYSQL_MIN_DATE])
  color = models.CharField(max_length=50, validator=[COLOR_NAME_VALIDATOR], 
                           default='gray', help_text='Event color for display purposes', **VOID)

  def __str__(self) -> str:
    return self.title

  class Meta:
    ordering = ('-created_at',)
