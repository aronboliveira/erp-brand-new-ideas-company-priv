from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation
from .._helpers.connectors.user_connected import UserConnected
from .._helpers.connectors.deal_connected import DealConnected
from ..individuals.user import User
class UserDeal(DefaultTimed, DealConnected, UserConnected):
  id = models.UUIDField(**uuid_def_primary())
  created_by = default_user_creation('%(class)s_created_by')
  def get_user_deal(self) -> 'User':
    # Since a proper ForeignKey is used, simply return the related user
    return self.user

  def __str__(self) -> str:
    return f"User Deal(user={self.user.id if self.user else None}, deal_id={self.deal.id if self.deal else None})"
