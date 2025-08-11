from django.db import models
from django.utils import timezone
from .._helpers.connectors.branch_connected import BranchConnected
from .._helpers.describable import Describable
from .._helpers.connectors.department_connected import DepartmentConnected
from .._helpers.fields import VALID_MYSQL_MIN_DATE
class Announcement(Describable, BranchConnected, DepartmentConnected):
    start_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now, db_index=True)
    end_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], db_index=True)

    class Meta:
        db_table = "announcement"
        ordering = ["-start_date"]

    def __str__(self) -> str:
        return self.title
