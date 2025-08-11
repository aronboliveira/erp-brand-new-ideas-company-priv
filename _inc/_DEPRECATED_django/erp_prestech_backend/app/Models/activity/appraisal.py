from django.db import models
from .._helpers.connectors.branch_connected import BranchConnected
from .._helpers.rateable import Rateable
from .._helpers.fields import default_text_field, VALID_MYSQL_MIN_DATE
from .._helpers.connectors.employee_connected import EmployeeConnected
class Appraisal(Rateable, BranchConnected, EmployeeConnected):
  appraisal_date = models.DateField(db_index=True, validators=[VALID_MYSQL_MIN_DATE])
  remark = default_text_field('No remak was made')
  technical = ['None', 'Beginner', 'Intermediate', 'Advanced', 'Expert / Leader']
  organizational = ['None', 'Beginner', 'Intermediate', 'Advanced']

  def __str__(self) -> str:
    return f"Appraisal {self.id}"

  @property
  def branches(self) -> models.ForeignKey:
    return self.branch

  @property
  def employees(self) -> models.ForeignKey:
    return self.employee

  class Meta:
    ordering = ('-created_at',)
