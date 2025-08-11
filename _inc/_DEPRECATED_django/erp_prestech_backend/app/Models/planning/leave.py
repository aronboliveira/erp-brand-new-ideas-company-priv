from django.db import models
from django.utils import timezone
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (default_user_creation, uuid_def_primary, 
                               default_text_field, VALID_MYSQL_MIN_DATE)
from .._helpers.connectors.employee_connected import EmployeeConnected
class Leave(DefaultTimed, EmployeeConnected):
    STATUS_CHOICES = [
        ('pending', 'Pending'),
        ('approved', 'Approved'),
        ('rejected', 'Rejected'),
    ]
    id = models.UUIDField(**uuid_def_primary())
    leave_type = models.ForeignKey(
        'LeaveType',
        on_delete=models.SET_NULL,
        null=True,
        related_name='leaves',
        db_index=True
    )
    applied_on = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now, db_index=True)
    start_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now, db_index=True)
    end_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], db_index=True)
    total_leave_days = models.DecimalField(max_digits=5, decimal_places=2)
    leave_reason = default_text_field(default='No reason was defined')
    remark = default_text_field(default='No remark was written')
    status = models.CharField(max_length=20, default='pending', choices=STATUS_CHOICES, db_index=True)
    created_by = default_user_creation('%(class)s_created_by')

    def __str__(self):
        return f"{self.employee.name} - {self.leave_type.name} ({self.start_date} to {self.end_date})"
