from django.db import models
from django.utils import timezone
from decimal import Decimal
from typing import Dict, Any, Union
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (default_char_field, default_user_creation, uuid_def_primary, 
                               default_decimal_12, VALID_MYSQL_MIN_DATE)

class Goal(DefaultTimed):
  GOAL_TYPE = ["Invoice", "Bill", "Revenue", "Payment"]
  id = models.UUIDField(**uuid_def_primary())
  name = default_char_field()
  type = models.ForeignKey('GoalType', on_delete=models.SET_NULL, db_index=True)
  from_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now)
  to_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])
  amount = default_decimal_12()
  is_display = models.BooleanField(default=False)
  created_by = default_user_creation("%(class)s_created")
  
  def target(
    self, 
    type_index: int, 
    from_value: str, 
    to_value: str, 
    amount: Union[float, Decimal], 
    user: Any
  ) -> Dict[str, Union[float, Decimal]]:
    """
    Calculate the progress percentage for the given goal based on the type.
    Expects:
      - type_index: an integer index into GOAL_TYPE
      - from_value, to_value: strings representing a date prefix (e.g., "2023-03")
      - amount: target amount
      - user: the current user (assumed to have a method/attribute creator_id())
    """
    total: Union[float, Decimal] = 0
    from_date_str = f"{from_value}-00"
    to_date_str = f"{to_value}-00"
    goal_type_str = Goal.GOAL_TYPE[type_index]
    if goal_type_str == "Invoice":
      from ...Models.bills.invoice import Invoice
      qs = Invoice.objects.filter(
          created_by=user.creator_id(),
          issue_date__gte=from_date_str,
          issue_date__lte=to_date_str,
      )
      for invoice in qs:
        total += invoice.get_total()
    elif goal_type_str == "Bill":
      from ...Models.bills.bill import Bill
      qs = Bill.objects.filter(
          created_by=user.creator_id(),
          bill_date__gte=from_date_str,
          bill_date__lte=to_date_str,
      )
      for bill in qs:
        total += bill.get_total()
    elif goal_type_str == "Revenue":
      from ...Models.bills.revenue import Revenue
      qs = Revenue.objects.filter(
          created_by=user.creator_id(),
          date__gte=from_date_str,
          date__lte=to_date_str,
      )
      for revenue in qs:
        total += revenue.amount
    elif goal_type_str == "Payment":
      from ...Models.bills.payment import Payment
      qs = Payment.objects.filter(
          created_by=user.creator_id(),
          date__gte=from_date_str,
          date__lte=to_date_str,
      )
      for payment in qs:
        total += payment.amount
    percentage = (total * 100) / float(amount) if amount else 0
    return {"percentage": percentage, "total": total}
  
  def __str__(self) -> str:
    return self.name
