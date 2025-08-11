from django.db import models
from django.utils import timezone
from .._helpers.describable import Describable
from .._helpers.fields import VALID_MYSQL_MIN_DATE
from .._helpers.connectors.employee_connected import EmployeeConnected
class Resignation(Describable, EmployeeConnected):
  notice_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE],default=timezone.now)
  resignation_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])

  class Meta:
    db_table = 'resignation'
