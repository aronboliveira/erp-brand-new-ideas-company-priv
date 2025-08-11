from typing import Optional
from django.db import models
from django.utils import timezone
from .._helpers.describable import Describable
from .._helpers.fields import default_char_field, VALID_MYSQL_MIN_DATE
from ..individuals.employee import Employee
class Complaint(Describable):
  complaint_from = models.ForeignKey('Employee',
    on_delete=models.CASCADE,
    related_name="complaints_made",
    db_index=True
  )
  complaint_against = models.ForeignKey('Employee',
    on_delete=models.CASCADE,
    related_name="complaints_received",
    db_index=True
  )
  title = default_char_field()
  complaint_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now)

  class Meta:
    db_table = "complaints"

  @property
  def employee(self) -> Optional[Employee]:
    return Employee.objects.filter(pk=self.complaint_against_id).first()

  @property
  def complaint_from_employee(self) -> Optional[Employee]:
    return Employee.objects.filter(pk=self.complaint_from_id).first()

  @property
  def complaint_against_employee(self) -> Optional[Employee]:
    return Employee.objects.filter(pk=self.complaint_against_id).first()
