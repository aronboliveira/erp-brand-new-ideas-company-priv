from django.db import models
from .._helpers.connectors.bank_account_connected import BankAccountConnected
from .._helpers.connectors.category_connected import CategoryConnected
from .._helpers.connectors.customer_connected import CustomerConnected
from .._helpers.describable import Describable
from .._helpers.fields import (default_decimal_12,
                               DEFAULTED_PAY_METHODS,
                               VALID_MYSQL_MIN_DATE)
class Revenue(Describable, BankAccountConnected, CategoryConnected, CustomerConnected):
  date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], db_index=True)
  amount = default_decimal_12()
  recurring = models.BooleanField(default=False)
  payment_method = models.CharField(**DEFAULTED_PAY_METHODS)
  reference = models.CharField(max_length=254, blank=True)

  def __str__(self) -> str:
    return f"Revenue of {self.amount} on {self.date}"
