from django.db import models
from .._helpers.connectors.bank_account_connected import BankAccountConnected
from .._helpers.connectors.category_connected import CategoryConnected
from .._helpers.connectors.chart_connected import ChartOfAccountConnected
from .._helpers.describable import Describable
from .._helpers.fields import (DEFAULTED_PAY_METHODS,
                               VALID_MYSQL_MIN_DATE, default_decimal_12)
from .._helpers.connectors.vendor_connected import VendorConnected
class Payment(Describable, BankAccountConnected, CategoryConnected, ChartOfAccountConnected, VendorConnected):
  date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], help_text='Date of payment', db_index=True)
  amount = default_decimal_12()
  payment_method = models.CharField(**DEFAULTED_PAY_METHODS)
  reference = models.CharField(max_length=100, blank=True)

  def __str__(self) -> str:
    return f"Payment {self.uuid} - Amount: {self.amount}"
