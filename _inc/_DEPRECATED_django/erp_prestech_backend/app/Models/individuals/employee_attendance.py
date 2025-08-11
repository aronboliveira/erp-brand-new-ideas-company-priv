from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_user_creation, 
                               SHORT_BLANK_CHAR, VALID_MYSQL_MIN_DATE, VOID)
from .._helpers.connectors.employee_connected import EmployeeConnected
class EmployeeAttendance(DefaultTimed, EmployeeConnected):
    id = models.UUIDField(**uuid_def_primary())
    status = models.CharField(max_length=50, default='present', choices=[('present', 'Present'), ('absent', 'Absent'), 
                                                      ('half_day', 'Half Day'), ('remote', 'Remote'), 
                                                      ('leave', 'Leave')])
    date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])
    status = models.CharField(max_length=50)
    clock_in = models.TimeField(**VOID)
    clock_out = models.TimeField(**VOID)
    late = models.DurationField(**VOID)
    early_leaving = models.DurationField(**VOID)
    overtime = models.DurationField(**VOID)
    total_rest = models.DurationField(**VOID)
    remarks = models.TextField(max_length=65535, **VOID)
    location = models.CharField(**SHORT_BLANK_CHAR)
    created_by = default_user_creation('%(class)s_created_by', db_index=True)
    
    def __str__(self) -> str:
        return f"Attendance for {self.employee} on {self.date}"
    
    class Meta:
        ordering = ('-created_at',)
