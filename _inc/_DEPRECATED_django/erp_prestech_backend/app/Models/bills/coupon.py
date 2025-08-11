from django.db import models
from .._helpers.describable import Describable
from .._helpers.fields import default_char_field
class Coupon(Describable):
  name = default_char_field()
  code = models.CharField(max_length=50, unique=True)
  discount = models.DecimalField(max_digits=8, decimal_places=2)
  limit = models.PositiveIntegerField()
  
  def used_coupon(self) -> int:
    from .user_coupon import UserCoupon
    return UserCoupon.objects.filter(coupon=self.uuid).count()

  class Meta:
    db_table = "coupon"
