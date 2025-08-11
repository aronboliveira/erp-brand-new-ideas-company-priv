from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_user_creation, uuid_def_primary
from .._helpers.connectors.employee_connected import EmployeeConnected
class MeetingEmployee(DefaultTimed, EmployeeConnected):
  id = models.UUIDField(**uuid_def_primary())
  meeting = models.ForeignKey('Meeting', on_delete=models.CASCADE)
  created_by = default_user_creation('%(class)s_created_by')

  class Meta:
    db_table = "meeting_employee"
