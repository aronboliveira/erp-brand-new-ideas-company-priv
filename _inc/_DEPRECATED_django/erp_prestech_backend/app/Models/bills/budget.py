from django.db import models
from django.utils import timezone
from typing import Union
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, default_char_field, VOID, VALID_MYSQL_MIN_DATE

class Budget(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = default_char_field()
  start_date = models.DateField(**VOID,validators=[VALID_MYSQL_MIN_DATE], default=timezone.now)
  end_date = models.DateField(**VOID,validators=[VALID_MYSQL_MIN_DATE])
  period = models.CharField(max_length=50)
  created_by = default_user_creation('%(class)s_created_by')
  PERIOD = {
    'monthly': 'Monthly',
    'quarterly': 'Quarterly',
    'half-yearly': 'Half Yearly',
    'yearly': 'Yearly',
  }

  def get_availability_date(self) -> str:
    date_format = "%b-%Y"
    date_str = ""
    if self.start_date:
      date_str = self.start_date.strftime(date_format)
    if self.end_date:
      date_str += " - " + self.end_date.strftime(date_format)
    return date_str

  @staticmethod
  def percentage(actual: Union[int, float], budget: Union[int, float]) -> str:
    if actual == 0:
      return "0.00"
    percentage = budget * 100 / actual
    return format(percentage, ".2f")

  class Meta:
    ordering = ('-created_at',)
