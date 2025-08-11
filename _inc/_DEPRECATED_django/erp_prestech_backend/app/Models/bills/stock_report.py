from django.db import models
from .._helpers.describable import Describable
from .._helpers.fields import default_decimal_12

class StockReport(Describable):
  product = models.ForeignKey(
    'ProductService',
    on_delete=models.SET_NULL,
    null=True,
    blank=True,
    related_name='stock_reports',
    db_index=True
  )
  quantity = default_decimal_12()
  type = models.CharField(max_length=50, db_index=True)
  type_id = models.IntegerField(max_length=36)

  def __str__(self) -> str:
    return f"Stock Report for {self.product}"

  @classmethod
  def products(cls, product_str: str) -> str:
    """
    Given a comma-separated string of product IDs, iterates over the IDs
    and returns the name of the last found ProductService (or empty string if none).
    """
    product_ids = product_str.split(',')
    result = ""
    from ..products.product_service import ProductService
    for pid in product_ids:
      prod = ProductService.objects.filter(id=pid.strip()).first()
      result = prod.name if prod else ""
    return result
