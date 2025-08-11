from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_user_creation, uuid_def_primary, default_char_field
class Commission(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  employee = models.OneToOneField(
    'Employee',
    on_delete=models.CASCADE,
    related_name="commission",
    db_column="employee_id",
    db_index = True
  )
  title = default_char_field()
  amount = models.DecimalField(max_digits=15, decimal_places=2, db_index=True)
  created_by = default_user_creation('%(class)s_created_by')

  COMMISSION_TYPE = {
    "fixed": "Fixed",
    "percentage": "Percentage",
  }

  class Meta:
    db_table = "commission"
