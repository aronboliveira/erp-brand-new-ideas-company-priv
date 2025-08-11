from django.db import models
from .._helpers.connectors.bank_account_connected import BankAccountConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_user_creation, uuid_def_primary, defalt_decicmal_10, VALID_MYSQL_MIN_DATE
from typing import TYPE_CHECKING
if TYPE_CHECKING:
  from ..companies.bank_account import BankAccount
class PosPayment(DefaultTimed, BankAccountConnected):
  id = models.UUIDField(**uuid_def_primary())
  pos = models.ForeignKey("Pos", on_delete=models.CASCADE, help_text="Point of Sale record", db_index=True)
  date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], db_index=True)
  amount = defalt_decicmal_10()
  discount = defalt_decicmal_10()
  created_by = default_user_creation('%(class)s_created_by')

  def get_bank_account(self) -> BankAccount:
    return self.account

  class Meta:
    db_table = "pos_payment"
