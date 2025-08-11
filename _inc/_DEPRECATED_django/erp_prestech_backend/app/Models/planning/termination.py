from django.db import models
from django.utils import timezone
from .._helpers.describable import Describable
from .._helpers.fields import VALID_MYSQL_MIN_DATE
from .._helpers.connectors.employee_connected import EmployeeConnected
class Termination(Describable, EmployeeConnected):
  notice_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE],default=timezone.now)
  termination_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])
  termination_type = models.ForeignKey('TerminationType', on_delete=models.SET_NULL, null=True, related_name='terminations', db_index=True)
  
  class Meta:
    db_table = 'termination'
    ordering = ['-termination_date']
  def __str__(self) -> str:
    return f"Termination of Employee #{self.employee_id} on {self.termination_date}"
  @property
  def termination_type_obj(self):
    from .termination_type import TerminationType
    return TerminationType.objects.filter(uuid=self.termination_type.uuid).first() if self.termination_type else None
  @property
  def employee_obj(self):
    from ..individuals.employee import Employee
    return Employee.objects.filter(uuid=self.employee.uuid).first() if self.employee else None
