from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_user_creation, default_decimal_12, 
                               FIN_STATUS_CHOICES, VALID_MYSQL_MIN_DATE)
from .._helpers.connectors.employee_connected import EmployeeConnected
from typing import TYPE_CHECKING
if TYPE_CHECKING:
  from ..individuals.employee import Employee
class Payslip(DefaultTimed, EmployeeConnected):
  id = models.UUIDField(**uuid_def_primary())
  net_payble = default_decimal_12()
  basic_salary = default_decimal_12()
  salary_month = models.DateField(validators=[VALID_MYSQL_MIN_DATE], db_index=True)
  status = models.CharField(max_length=50, default='draft', choices=FIN_STATUS_CHOICES)
  allowance = default_decimal_12()
  commission = default_decimal_12()
  loan = default_decimal_12()
  saturation_deduction = default_decimal_12()
  other_payment = default_decimal_12()
  overtime = default_decimal_12()
  created_by = default_user_creation('%(class)s_created_by')

  @classmethod
  def get_employee_by_id(cls, employee_id: int) -> "Employee":
    from ..individuals.employee import Employee
    return Employee.objects.filter(id=employee_id).first()

  def __str__(self) -> str:
    return f"Payslip for {self.employee} - {self.salary_month}"
