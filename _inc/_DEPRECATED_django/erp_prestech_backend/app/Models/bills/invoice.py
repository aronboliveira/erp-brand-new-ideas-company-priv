from django.db import models
from django.utils import timezone
from decimal import Decimal
from .._helpers.connectors.category_connected import CategoryConnected
from .._helpers.connectors.customer_connected import CustomerConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_user_creation, 
                               FIN_STATUS_CHOICES, VALID_MYSQL_MIN_DATE)
from ..utils.utility import Utility
class Invoice(DefaultTimed, CategoryConnected, CustomerConnected):
  id = models.UUIDField(**uuid_def_primary())
  issue_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now)
  due_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])
  ref_number = models.CharField(max_length=36, blank=True)
  status = models.CharField(max_length=20, defaul='draft', choices=FIN_STATUS_CHOICES)
  created_by = default_user_creation('%(class)s_created_by')

  def get_sub_total(self) -> Decimal:
    return sum(item.price * item.quantity for item in self.invoice_products.all())

  def get_total_discount(self) -> Decimal:
    return sum(item.discount for item in self.invoice_products.all())

  def get_total_tax(self) -> Decimal:
    total_tax: Decimal = Decimal('0.00')
    for item in self.invoice_products.all():
      rate = Utility.total_tax_rate(item.tax)
      taxable = (item.price * item.quantity) - item.discount
      total_tax += (rate / 100) * taxable
    return total_tax

  def get_total(self) -> Decimal:
    return self.get_sub_total() - self.get_total_discount() + self.get_total_tax()

  def invoice_total_credit_note(self) -> Decimal:
    credit_total = self.credit_notes.aggregate(total=models.Sum('amount'))['total']
    return credit_total or Decimal('0.00')

  def get_due(self) -> Decimal:
    paid = self.payments.aggregate(total=models.Sum('amount'))['total'] or Decimal('0.00')
    return self.get_total() - paid - self.invoice_total_credit_note()

  @classmethod
  def change_status(cls, invoice_id: str, status: str) -> None:
    try:
      invoice = cls.objects.get(id=invoice_id)
      invoice.status = status
      invoice.save()
    except cls.DoesNotExist as e:
      import logging
      logging.error(f"Failed to change status for Invoice {invoice_id}: {e}")

  def __str__(self) -> str:
    return f"Invoice {self.invoice_id}"
