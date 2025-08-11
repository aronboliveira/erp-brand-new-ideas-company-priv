from django.db import models
from django.db.models import Sum
from decimal import Decimal
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (default_user_creation, default_char_field, uuid_def_primary, 
                               TINY_BLANK_CHAR, MONTH_VALIDATOR, YEAR_VALIDATOR, CC_VALIDATOR, 
                               CURRENCY_ACRONYM_VALIDATOR, PAYMENT_METHODS, COMPLETION_CHOICES)
from .._helpers.connectors.user_connected import UserConnected
from typing import Optional, TYPE_CHECKING
if TYPE_CHECKING:
  from ..bills.user_coupon import UserCoupon
class Order(DefaultTimed, UserConnected):
  id = models.UUIDField(**uuid_def_primary())
  plan = models.ForeignKey('Plan', on_delete=models.CASCADE, db_index=True)
  txn_id = models.UUIDField(max_length=36, **uuid_def_primary(pk=False))
  name = default_char_field(voidable=True)
  email = default_char_field(voidable=True)
  card_exp_month = models.PositiveIntegerField(validators=MONTH_VALIDATOR)
  card_number = default_char_field(voidable=True, validators=[CC_VALIDATOR])
  card_exp_year = models.CharField(**TINY_BLANK_CHAR, validators=YEAR_VALIDATOR)
  plan_name = default_char_field(voidable=True)
  price = models.DecimalField(max_digits=15, decimal_places=2, default=0)
  price_currency = models.CharField(**TINY_BLANK_CHAR, validators=[CURRENCY_ACRONYM_VALIDATOR])
  payment_status = models.CharField(**TINY_BLANK_CHAR, default='pending', choices=COMPLETION_CHOICES)
  payment_type = models.CharField(**TINY_BLANK_CHAR, default='other', choices=PAYMENT_METHODS)
  receipt = default_char_field(voidable=True)
  created_by = default_user_creation('%(class)s_created_by')

  class Meta:
    db_table = "orders"

  @classmethod
  def total_orders(cls) -> int:
    return cls.objects.count()

  @classmethod
  def total_orders_price(cls) -> Decimal:
    total = cls.objects.aggregate(sum_price=Sum("price"))
    return total["sum_price"] or Decimal(0)

  def total_coupon_used(self) -> Optional["UserCoupon"]:
    from ..bills.user_coupon import UserCoupon
    return UserCoupon.objects.filter(order=self.order_id).first()

  def __str__(self) -> str:
    return f"Order #{self.order_id} - {self.name or ''}"
