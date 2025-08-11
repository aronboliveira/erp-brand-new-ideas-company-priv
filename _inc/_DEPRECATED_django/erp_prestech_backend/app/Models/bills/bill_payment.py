from django.db import models
from django.utils import timezone
from .._helpers.connectors.bank_account_connected import BankAccountConnected
from .._helpers.connectors.bill_connected import BillConnected
from .._helpers.describable import Describable
from .._helpers.fields import (default_char_field,
                               DEFAULTED_PAY_METHODS, VALID_MYSQL_MIN_DATE)
class BillPayment(Describable, BankAccountConnected, BillConnected):
  date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now)
  payment_method = models.CharField(**DEFAULTED_PAY_METHODS)
  reference = default_char_field(voidable=True, max_length=36)

  def __str__(self) -> str:
    return f"BillPayment #{self.id}"

  class Meta:
    ordering = ('-created_at',)
