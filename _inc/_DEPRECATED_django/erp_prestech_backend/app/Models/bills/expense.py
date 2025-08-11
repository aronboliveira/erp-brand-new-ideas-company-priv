from django.db import models
from django.core.files.storage import default_storage
from django.utils import timezone
from .._helpers.describable import Describable
from .._helpers.fields import (default_char_field, 
                               VOID, VALID_MYSQL_MIN_DATE, FILE_SIZE_VALIDATOR, default_decimal_12)
from .._helpers.connectors.project_connected import ProjectConnected
class Expense(Describable, ProjectConnected):
  name = default_char_field()
  date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now)
  amount = default_decimal_12()
  attachment = models.FileField(upload_to='expenses/', validators=[FILE_SIZE_VALIDATOR], storage=default_storage, **VOID)
  task = models.ForeignKey(
    'ProjectTask',
    on_delete=models.SET_NULL,
    null=True,
    blank=True,
    related_name='expenses'
  )

  class Meta:
    verbose_name = "Expense"
    verbose_name_plural = "Expenses"
    ordering = ['-date']

  def __str__(self) -> str:
    return f"{self.name} - {self.amount}"
