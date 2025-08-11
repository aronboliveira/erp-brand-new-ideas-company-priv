from django.db import models
from .._helpers.describable import Describable
from .._helpers.fields import VALID_MYSQL_MIN_DATE
from .._helpers.connectors.branch_connected import BranchConnected
from .._helpers.connectors.department_connected import DepartmentConnected
from .._helpers.connectors.employee_connected import EmployeeConnected
from typing import TYPE_CHECKING
if TYPE_CHECKING:
  from ..companies.branch import Branch
  from ..companies.department import Department
  from ..individuals.employee import Employee
class Transfer(Describable, BranchConnected, DepartmentConnected, EmployeeConnected):
  transfer_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])

  class Meta:
    db_table = "transfer"

  @property
  def department_obj(self) -> Department:
    return self.department

  @property
  def branch_obj(self) -> Branch:
    return self.branch

  @property
  def employee_obj(self) -> Employee:
    return self.employee
