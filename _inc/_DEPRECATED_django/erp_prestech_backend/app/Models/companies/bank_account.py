from django.db import models
from .._helpers.connectors.chart_connected import ChartOfAccountConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_user_creation, uuid_def_primary, default_char_field, default_decimal_12
class BankAccount(DefaultTimed, ChartOfAccountConnected):
  id = models.UUIDField(**uuid_def_primary())
  holder_name = default_char_field()
  bank_name = default_char_field()
  account_number = default_char_field(db_index=True)
  opening_balance = default_decimal_12()
  contact_number = default_char_field(voidable=True)
  bank_address = default_char_field(voidable=True)
  created_by = default_user_creation('%(class)s_created_by')

  def __str__(self) -> str:
    return f"{self.holder_name} - {self.bank_name}"
