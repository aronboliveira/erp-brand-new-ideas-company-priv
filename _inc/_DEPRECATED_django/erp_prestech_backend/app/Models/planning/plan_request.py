from django.db import models
from .._helpers.connectors.user_connected import UserConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_user_creation, uuid_def_primary
from typing import TYPE_CHECKING
if TYPE_CHECKING:
  from ..individuals.user import User
  from .plan import Plan
class PlanRequest(DefaultTimed, UserConnected):
  id = models.UUIDField(**uuid_def_primary())
  duration = models.CharField(max_length=10, default='month', choices=[('lifetime', 'Lifetime'),('month', 'Per Month'),('year', 'Per Year')])
  plan = models.ForeignKey('Plan', on_delete=models.CASCADE, db_index=True)
  created_by = default_user_creation('%(class)s_created_by', db_index=True)
  
  def get_plan(self) -> "Plan":
    return self.plan
  
  def get_user(self) -> "User":
    return self.user
  
  class Meta:
    db_table = 'plan_request'
