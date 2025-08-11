from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_char_field, default_user_creation, uuid_def_primary, defalt_decicmal_10
from .._helpers.connectors.employee_connected import EmployeeConnected
class Overtime(DefaultTimed, EmployeeConnected):
  id = models.UUIDField(**uuid_def_primary())
  title = default_char_field(db_index=True)
  number_of_days = models.PositiveIntegerField()
  hours = models.DecimalField(max_digits=5, decimal_places=2)
  rate = defalt_decicmal_10()
  created_by = default_user_creation('%(class)s_created_by')

  class Meta:
    db_table = "overtime"
