from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_char_field, default_user_creation, defalt_decicmal_10
from .._helpers.connectors.employee_connected import EmployeeConnected
class OtherPayment(DefaultTimed, EmployeeConnected):
  id = models.UUIDField(**uuid_def_primary())
  title = default_char_field(help_text='The title of the alternative method of payment')
  amount = defalt_decicmal_10({})
  created_by = default_user_creation('%(class)s_created_by')
  other_payment_type = {'fixed': 'Fixed', 'percentage': 'Percentage'}

  class Meta:
    db_table = 'other_payment'
    ordering = ('-created_at',)
