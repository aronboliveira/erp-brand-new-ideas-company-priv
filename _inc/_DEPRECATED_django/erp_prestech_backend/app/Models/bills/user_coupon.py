from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation
from .._helpers.connectors.user_connected import UserConnected
from typing import TYPE_CHECKING
if TYPE_CHECKING:
  from ..individuals.user import User
  from ..bills.coupon import Coupon
class UserCoupon(DefaultTimed, UserConnected):
  id = models.UUIDField(**uuid_def_primary())
  coupon = models.ForeignKey(
    'Coupon',
    on_delete=models.CASCADE,
    db_column='coupon',
    related_name='user_coupons'
  )
  created_by = default_user_creation('%(class)s_created_by')

  class Meta:
    db_table = 'user_coupons'

  def user_detail(self) -> "User":
    return self.user

  def coupon_detail(self) -> "Coupon":
    return self.coupon

  def __str__(self) -> str:
    return f"UserCoupon for User ID {self.user_id} -> Coupon ID {self.coupon_id}"
