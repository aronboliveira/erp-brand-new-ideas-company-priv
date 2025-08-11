from django.db import models
from .._helpers.fields import VOID
from ..info.announcement import Announcement
from .._helpers.connectors.employee_connected import EmployeeConnected
class EmployeeAnnouncement(Announcement, EmployeeConnected):
    status = models.CharField(max_length=50, choices=[('seen', 'Seen'), ('unseen', 'Unseen')], default='unseen')
    seen_at = models.DateTimeField(**VOID)
    class Meta:
        db_table = "employee_announcement"
        unique_together = ("announcement", "employee")
        ordering = ["-created_at"]

    def __str__(self) -> str:
        return f"Announcement {self.announcement} -> Employee {self.employee}"
