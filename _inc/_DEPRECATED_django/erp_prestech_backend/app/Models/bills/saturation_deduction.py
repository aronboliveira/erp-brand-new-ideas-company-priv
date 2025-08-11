from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_char_field, default_user_creation, uuid_def_primary, defalt_decicmal_10
from .._helpers.connectors.employee_connected import EmployeeConnected
class SaturationDeduction(DefaultTimed, EmployeeConnected):
  id = models.UUIDField(**uuid_def_primary())
  deduction_option = models.ForeignKey(
    'DeductionOption',
    on_delete=models.CASCADE,
    related_name='saturation_deductions',
    db_index=True
  )
  title = default_char_field(db_index=True)
  amount = defalt_decicmal_10()
  created_by = default_user_creation('%(class)s_created_by', db_index=True)
  
  # Mapping of deduction types
  saturation_deduction_type = {
    'fixed': 'Fixed',
    'percentage': 'Percentage'
  }

  class Meta:
    db_table = 'saturation_deduction'
