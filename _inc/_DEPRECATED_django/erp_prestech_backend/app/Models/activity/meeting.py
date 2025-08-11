from django.db import models
from .._helpers.connectors.branch_connected import BranchConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.connectors.department_connected import DepartmentConnected
from .._helpers.connectors.employee_connected import EmployeeConnected
from .._helpers.fields import default_user_creation, uuid_def_primary, default_char_field, VOID, VALID_MYSQL_MIN_DATE
class Meeting(DefaultTimed, BranchConnected, DepartmentConnected, EmployeeConnected):
  id = models.UUIDField(**uuid_def_primary())
  title = default_char_field(db_index=True)
  date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])
  time = models.TimeField()
  note = models.TextField(**VOID, max_length=65535)
  created_by = default_user_creation('%(class)s_created_by')

  def __str__(self) -> str:
    return self.title

  class Meta:
    db_table = "meeting"
