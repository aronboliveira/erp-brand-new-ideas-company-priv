from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, defalt_decicmal_10, default_char_field
from .._helpers.connectors.employee_connected import EmployeeConnected
class Allowance(DefaultTimed, EmployeeConnected):
  id = models.UUIDField(**uuid_def_primary())
  allowance_option = models.ForeignKey('AllowanceOption', on_delete=models.CASCADE, related_name='allowances', db_index=True)
  title = default_char_field()
  amount = defalt_decicmal_10({})
  created_by = default_user_creation('%(class)s_created_by')

  ALLOWANCE_TYPE = {
    'fixed': 'Fixed',
    'percentage': 'Percentage',
  }

  def __str__(self) -> str:
    return f"{self.title} - {self.amount}"

  class Meta:
    ordering = ('-created_at',)
