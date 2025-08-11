import datetime
from django.db import models
from django.contrib.auth import get_user_model
from django.utils import timezone
from typing import Any, Dict
from .._helpers.connectors.category_connected import CategoryConnected
from .._helpers.connectors.vendor_connected import VendorConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, default_char_field, VOID, VALID_MYSQL_MIN_DATE
User = get_user_model()

class Purchase(DefaultTimed, CategoryConnected, VendorConnected):
  id = models.UUIDField(**uuid_def_primary())
  # Converted warehouse_id into a proper ForeignKey while preserving the original DB column name.
  warehouse = models.ForeignKey(
    'Warehouse', on_delete=models.SET_NULL,
    **VOID, related_name='purchases',
    db_column='warehouse_id'
  )
  purchase_date = models.DateField(**VOID, validators=[VALID_MYSQL_MIN_DATE], default=timezone.now)
  purchase_number = default_char_field(voidable=True)
  discount_apply = models.BooleanField(default=False)
  created_by = default_user_creation('%(class)s_created_by')

  STATUSES = ['Draft', 'Sent', 'Unpaid', 'Partialy Paid', 'Paid']

  def __str__(self) -> str:
    return self.purchase_number or f"Purchase {self.id}"

  def get_sub_total(self) -> float:
    subtotal: float = 0
    for item in self.purchaseproduct_set.all():
      subtotal += item.price * item.quantity
    return subtotal

  def get_total_discount(self) -> float:
    total_discount: float = 0
    for item in self.purchaseproduct_set.all():
      total_discount += item.discount
    return total_discount

  def get_total_tax(self) -> float:
    total_tax: float = 0
    from ..utils.utility import Utility
    for item in self.purchaseproduct_set.all():
      tax_rate = Utility.total_tax_rate(item.tax)
      total_tax += (tax_rate / 100) * (item.price * item.quantity - item.discount)
    return total_tax

  def get_total(self) -> float:
    return (self.get_sub_total() - self.get_total_discount()) + self.get_total_tax()

  def get_due(self) -> float:
    total_payments = sum(payment.amount for payment in self.purchasepayment_set.all())
    return self.get_total() - total_payments

  @classmethod
  def total_purchase_amount(cls, user: Any, month: bool = False) -> str:
    """
    Calculate the total purchase amount filtered by the user's creator.
    If month is True, filter for the current month before converting the total using user's price_format method.
    """
    qs = cls.objects.filter(created_by=user.creator_id())
    if month:
      current_month = timezone.now().month
      qs = qs.filter(created_at__month=current_month)
    total_amount: float = sum(purchase.get_total() for purchase in qs)
    return user.price_format(total_amount)

  @classmethod
  def get_purchase_report_chart(cls, user: Any) -> Dict[str, list]:
    """
    Generate a purchase report chart for the last 10 days grouped by day (formatted as YYYY-MM-DD).
    Returns a dictionary with 'label' and 'value' keys.
    """
    ten_days_ago = timezone.now() - datetime.timedelta(days=10)
    qs = cls.objects.filter(
      created_at__gt=ten_days_ago,
      created_by=user.creator_id()
    ).order_by('created_at')
    totals: Dict[str, float] = {}
    for purchase in qs:
      day_key = purchase.created_at.strftime('%d%m')
      totals[day_key] = totals.get(day_key, 0) + purchase.get_total()
    labels = []
    values = []
    today = timezone.now().date()
    for i in range(10):
      day = today - datetime.timedelta(days=i)
      labels.append(day.strftime('%Y-%m-%d'))
      day_key = day.strftime('%d%m')
      values.append(totals.get(day_key, 0))
    return {'label': labels, 'value': values}
