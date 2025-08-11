from django.db import models
from .._helpers.describable import Describable
from .._helpers.fields import default_char_field, VALID_MYSQL_MIN_DATE
from .._helpers.connectors.employee_connected import EmployeeConnected
class Award(Describable, EmployeeConnected):
  award_type = models.ForeignKey('AwardType', on_delete=models.CASCADE, db_index=True)
  date = models.DateField(db_index=True, validators=[VALID_MYSQL_MIN_DATE])
  gift = default_char_field()

  def __str__(self) -> str:
    return f"Award({self.employee}, {self.award_type}, {self.date})"

  class Meta:
    ordering = ('-created_at',)
