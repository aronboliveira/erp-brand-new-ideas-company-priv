from django.db import models
from .._helpers.connectors.chart_connected import ChartOfAccountConnected
from .._helpers.describable import Describable
from .._helpers.fields import default_decimal_12, UUID_VERIFIED, DEFAULT_USER_TYPED

class BillAccount(Describable, ChartOfAccountConnected):
  price = default_decimal_12()
  account_type = models.CharField(**DEFAULT_USER_TYPED)
  ref_id = models.CharField(**UUID_VERIFIED)

  def __str__(self) -> str:
    return f"BillAccount for {self.chart_account} - {self.price}"

  class Meta:
    ordering = ('-created_at',)
