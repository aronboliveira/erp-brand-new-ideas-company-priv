from django.db import models
from .._helpers.connectors.bill_connected import BillConnected
from .._helpers.connectors.chart_connected import ChartOfAccountConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, default_decimal_12
class BillProduct(DefaultTimed, BillConnected, ChartOfAccountConnected):
  id = models.UUIDField(**uuid_def_primary())
  product = models.ForeignKey(
    'ProductService',
    on_delete=models.SET_NULL,
    null=True,
    related_name='bill_products'
  )
  quantity = default_decimal_12()
  tax = default_decimal_12()
  discount = default_decimal_12()
  total = default_decimal_12()
  created_by = default_user_creation('%(class)s_created_by')

  def get_product(self) -> models.Model:
    return self.product

  def get_chart_account(self) -> models.Model:
    return self.chart_account

  def __str__(self) -> str:
    return f"BillProduct for {self.product} (Bill: {self.bill})"

  class Meta:
    ordering = ('-created_at',)
