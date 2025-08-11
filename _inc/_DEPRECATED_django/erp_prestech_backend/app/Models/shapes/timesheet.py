from django.db import models
from .._helpers.describable import Describable
from .._helpers.fields import VALID_MYSQL_MIN_DATE
from .._helpers.connectors.project_connected import ProjectConnected

class Timesheet(Describable, ProjectConnected):
    task = models.ForeignKey("ProjectTask", on_delete=models.CASCADE, related_name="timesheets")
    date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])
    time = models.DurationField()

    def __str__(self) -> str:
        return f"Timesheet: {self.project} - {self.task} on {self.date}"
