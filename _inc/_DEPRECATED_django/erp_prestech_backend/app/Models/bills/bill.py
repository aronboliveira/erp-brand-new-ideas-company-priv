from django.db import models
from django.db.models import Sum
from django.utils import timezone
from decimal import Decimal
from typing import Optional, Any
from .._helpers.connectors.category_connected import CategoryConnected
from .._helpers.connectors.vendor_connected import VendorConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, CURRENCY_ACRONYM_VALIDATOR, VALID_MYSQL_MIN_DATE
from ..utils.utility import Utility
class Bill(DefaultTimed, CategoryConnected, VendorConnected):
  id = models.UUIDField(**uuid_def_primary())
  VENDOR_STATUSES = [
    ('Draft', 'Draft'),
    ('Sent', 'Sent'),
    ('Unpaid', 'Unpaid'),
    ('Partialy Paid', 'Partially Paid'),
    ('Paid', 'Paid'),
  ]
  currency = models.CharField(max_length=10, validators=[CURRENCY_ACRONYM_VALIDATOR])
  bill_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now)
  due_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])
  order_number = models.PositiveIntegerField()
  created_by = default_user_creation('%(class)s_created_by')

  def customer(self) -> Optional[Any]:
    return self.vendor.customer if hasattr(self.vendor, 'customer') else None

  def employee(self) -> Optional[Any]:
    return self.vendor.employee if hasattr(self.vendor, 'employee') else None

  def tax(self) -> Optional[Any]:
    return self.taxes.first()

  def get_sub_total(self) -> Decimal:
    sub_total = sum(item.price * item.quantity for item in self.items.all())
    account_total = sum(account.price for account in self.accounts.all())
    return sub_total + account_total

  def get_total_discount(self) -> Decimal:
    return sum(item.discount for item in self.items.all())

  def get_total_tax(self) -> Decimal:
    total_tax = Decimal('0.00')
    for item in self.items.all():
      tax_rate = Utility.total_tax_rate(item.tax)
      taxable_amount = (item.price * item.quantity) - item.discount
      total_tax += (tax_rate / 100) * taxable_amount
    return total_tax

  def get_account_total(self) -> Decimal:
    return sum(account.price for account in self.accounts.all())

  def get_total(self) -> Decimal:
    return (self.get_sub_total() - self.get_total_discount()) + self.get_total_tax()

  def bill_total_debit_note(self) -> Decimal:
    return self.debit_notes.aggregate(total=Sum('amount'))['total'] or Decimal('0.00')

  def get_due(self) -> Decimal:
    paid = self.payments.aggregate(total=Sum('amount'))['total'] or Decimal('0.00')
    return (self.get_total() - paid) - self.bill_total_debit_note()

  def __str__(self) -> str:
    return f"Bill {self.bill_id} - {self.currency}"

  class Meta:
    ordering = ('-created_at',)
