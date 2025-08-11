from django.db import models
from django.utils import timezone
from .._helpers.describable import Describable
from .._helpers.fields import (default_char_field, 
                               VALID_MYSQL_MIN_DATE, VOID)
class Warning(Describable):
    warning_to = models.ForeignKey(
        "Employee",
        on_delete=models.SET_NULL,
        related_name="warnings_received",
        db_index=True,
        **VOID
    )
    warning_by = models.ForeignKey(
        "Employee",
        on_delete=models.SET_NULL,
        related_name="warnings_issued",
        db_index=True,
        **VOID
    )
    subject = default_char_field(db_index=True,)
    warning_date = models.DateField(null=True, blank=True, default=timezone.now, validators=[VALID_MYSQL_MIN_DATE])

    def __str__(self) -> str:
        return f"{self.subject} ({self.warning_date})"
