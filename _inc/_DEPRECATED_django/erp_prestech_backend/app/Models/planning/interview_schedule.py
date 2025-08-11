from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_user_creation, 
                               VALID_MYSQL_MIN_DATE, NO_COMMENT, default_text_field)
from .._helpers.connectors.employee_connected import EmployeeConnected
from typing import TYPE_CHECKING
if TYPE_CHECKING:
  from ..individuals.user import User
  from ..individuals.job_application import JobApplication
class InterviewSchedule(DefaultTimed, EmployeeConnected):
  id = models.UUIDField(**uuid_def_primary())
  candidate = models.ForeignKey("JobApplication", on_delete=models.CASCADE, related_name="interview_schedules")
  date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], db_index=True)
  time = models.TimeField(db_index=True)
  comment = default_text_field(default=NO_COMMENT, voidable=True)
  employee_response = default_text_field(default='No response from the employee', voidable=True)
  created_by = default_user_creation("%(class)s_created_by", db_index=True)
  
  def get_applications(self) -> "JobApplication":
    return self.candidate
  
  def get_users(self) -> "User":
    return self.employee
  
  class Meta:
    db_table = "interview_schedule"
