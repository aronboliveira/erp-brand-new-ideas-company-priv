from django.db import models
from decimal import Decimal
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, defalt_decicmal_10
from .._helpers.connectors.invoice_connected import InvoiceConnected
class InvoiceProduct(DefaultTimed, InvoiceConnected):
  id = models.UUIDField(**uuid_def_primary())
  product = models.ForeignKey(
    'ProductService',
    on_delete=models.SET_NULL,
    null=True,
    blank=True,
    related_name='invoice_products'
  )
  quantity = defalt_decicmal_10()
  tax = defalt_decicmal_10()
  discount = defalt_decicmal_10()
  total = defalt_decicmal_10()
  created_by = default_user_creation('%(class)s_created_by')

  def __str__(self) -> str:
    return f"InvoiceProduct: {self.product} (Invoice: {self.invoice})"
