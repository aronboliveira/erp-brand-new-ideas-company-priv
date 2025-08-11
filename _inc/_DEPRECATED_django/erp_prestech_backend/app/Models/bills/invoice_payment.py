from django.db import models
from django.utils import timezone
from .._helpers.connectors.bank_account_connected import BankAccountConnected
from .._helpers.describable import Describable
from .._helpers.connectors.invoice_connected import InvoiceConnected
from .._helpers.fields import (default_char_field, default_decimal_12, UUID_VERIFIED,
                               VOID, CURRENCY_ACRONYM_VALIDATOR,
                               DEFAULTED_PAY_METHODS, VALID_MYSQL_MIN_DATE)
class InvoicePayment(Describable, BankAccountConnected, InvoiceConnected):
  date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now)
  amount = default_decimal_12()
  payment_method = models.CharField(**DEFAULTED_PAY_METHODS)
  order_id = models.CharField(UUID_VERIFIED)
  currency = models.CharField(max_length=10, validators=[CURRENCY_ACRONYM_VALIDATOR])
  txn_id = default_char_field(max_lenght=36, voidable=True)
  payment_type = models.CharField(max_length=50)
  receipt = default_char_field(voidable=True)
  reference = default_char_field(voidable=True)

  def __str__(self) -> str:
    return f"InvoicePayment #{self.id}"
