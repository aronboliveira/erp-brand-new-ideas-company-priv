from django.db import models
from django.utils import timezone
from .._helpers.describable import Describable
from .._helpers.fields import default_char_field, VALID_MYSQL_MIN_DATE
from .._helpers.connectors.employee_connected import EmployeeConnected
from typing import TYPE_CHECKING
if TYPE_CHECKING:
  from ..individuals.employee import Employee
class Travel(Describable, EmployeeConnected):
  start_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE],default=timezone.now)
  end_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])
  purpose_of_visit = default_char_field()
  place_of_visit = default_char_field()
 
  def get_employee(self) -> "Employee":
    return self.employee

  class Meta:
    db_table = 'travels'
