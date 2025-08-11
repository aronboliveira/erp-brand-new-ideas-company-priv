import logging
from django.db import models
from .._helpers.connectors.bank_account_connected import BankAccountConnected
from .._helpers.describable import Describable
from .._helpers.fields import (default_char_field, 
                               DEFAULTED_PAY_METHODS, VALID_MYSQL_MIN_DATE)
from typing import Optional, TYPE_CHECKING
if TYPE_CHECKING:
  from ..companies.bank_account import BankAccount
  
class PurchasePayment(Describable, BankAccountConnected):
  purchase = models.ForeignKey(
    "Purchase",
    on_delete=models.CASCADE,
    related_name="purchase_payments"
  )
  date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], db_index=True)
  payment_method = models.CharField(**DEFAULTED_PAY_METHODS)
  reference = default_char_field()

  class Meta:
    db_table = "purchase_payment"
    ordering = ["-created_at"]

  def get_bank_account(self) -> Optional[BankAccount]:
    try:
      return self.account
    except Exception as e:
      logging.error(f"Failed to get bank account in PurchasePayment: {e}")
      return None
