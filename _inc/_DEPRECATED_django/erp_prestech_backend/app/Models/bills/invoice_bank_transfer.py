from django.db import models
from django.utils import timezone
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_user_creation, default_char_field, 
                               FIN_STATUS_CHOICES, VALID_MYSQL_MIN_DATE)
from .._helpers.connectors.invoice_connected import InvoiceConnected
class InvoiceBankTransfer(DefaultTimed, InvoiceConnected):
  id = models.UUIDField(**uuid_def_primary())
  order = models.ForeignKey(
    'Order',
    on_delete=models.CASCADE,
    related_name='invoice_bank_transfers',
    db_index=True
  )
  amount = models.DecimalField(max_digits=20, decimal_places=2, db_index=True)
  status = models.CharField(max_length=50, default='draft', choices=FIN_STATUS_CHOICES)
  date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now)
  receipt = default_char_field(voidable=True)
  created_by = default_user_creation('%(class)s_created_by')

  def __str__(self) -> str:
    return f"InvoiceBankTransfer {self.id}"
