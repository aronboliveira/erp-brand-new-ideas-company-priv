from django.db import models
from django.utils import timezone
from typing import Any, Dict, List, Optional
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_user_creation, VOID, 
                               COMPLETION_CHOICES, VALID_MYSQL_MIN_DATE,
                               UUID_VERIFIED)

class Pos(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  pos_id = models.CharField(**UUID_VERIFIED)
  customer = models.ForeignKey(
    'Customer', on_delete=models.SET_NULL,
    **VOID, related_name='poses'
  )
  warehouse = models.ForeignKey(
    'Warehouse', on_delete=models.SET_NULL,
    **VOID, related_name='poses'
  )
  pos_date = models.DateField(**VOID, validators=[VALID_MYSQL_MIN_DATE])
  category_id = models.CharField(**UUID_VERIFIED, **VOID)
  status = models.CharField(max_length=63, default='pending', choices=COMPLETION_CHOICES, blank=True)
  shipping_display = models.CharField(max_length=50, blank=True)
  created_by = default_user_creation('%(class)s_created_by')

  def __str__(self) -> str:
    return self.pos_id

  def pos_payment(self) -> Optional[models.Model]:
    return self.pospayment_set.first()

  def items(self) -> models.QuerySet:
    return self.posproduct_set.all()

  def taxes(self) -> Optional[models.Model]:
    return self.tax_set.first()

  def get_sub_total(self) -> float:
    sub_total: float = 0
    for product in self.items():
      sub_total += product.price * product.quantity
    return sub_total

  def get_total_discount(self) -> float:
    total_discount: float = 0
    for product in self.items():
      total_discount += product.discount
    return total_discount

  def get_total_tax(self) -> float:
    from ..utils.utility import Utility
    total_tax: float = 0
    for product in self.items():
      rate = Utility.total_tax_rate(product.tax)
      total_tax += (rate / 100) * (product.price * product.quantity)
    return total_tax

  def get_total(self) -> float:
    return (self.get_sub_total() - self.get_total_discount()) + self.get_total_tax()

  @classmethod
  def total_pos_amount(cls, month: bool = False, user: Optional[Any] = None) -> str:
    """
    Calculate total POS amount for the current user.
    If month is True, filter by current month and return the amount formatted using the user's price_format method.
    """
    queryset = cls.objects.filter(created_by=user)
    if month:
      now = timezone.now()
      queryset = queryset.filter(created_at__month=now.month)
    pos_amount: float = 0
    for pos in queryset:
      pos_amount += pos.get_total()
    return user.price_format(pos_amount) if user else ""

  @classmethod
  def get_pos_report_chart(cls, user: Optional[Any] = None) -> Dict[str, List[Any]]:
    """
    Returns a dictionary with 'label' and 'value' keys representing the last 10 days' total POS amounts.
    """
    ten_days_ago = timezone.now() - timezone.timedelta(days=10)
    poses = cls.objects.filter(created_by=user, created_at__gt=ten_days_ago).order_by('created_at')
    daily_sums: Dict[str, float] = {}
    for pos in poses:
      day_key = pos.created_at.strftime('%d%m')
      daily_sums[day_key] = daily_sums.get(day_key, 0) + pos.get_total()
    poses_array: Dict[str, List[Any]] = {'label': [], 'value': []}
    now = timezone.now()
    for i in range(10):
      day_date = (now - timezone.timedelta(days=i)).date()
      poses_array['label'].append(day_date.isoformat())
      day_key = day_date.strftime('%d%m')
      poses_array['value'].append(daily_sums.get(day_key, 0))
    return poses_array
