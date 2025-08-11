from django.db import models
from .._helpers.connectors.deal_connected import DealConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (default_user_creation, uuid_def_primary, default_text_field,
                               NO_COMMENT)
from ..individuals.user import User
class DealDiscussion(DefaultTimed, DealConnected):
  id = models.UUIDField(**uuid_def_primary())
  comment = default_text_field(default=NO_COMMENT)
  created_by = default_user_creation('%(class)s_created_by')

  def user(self) -> 'User':
    # Return the related creator user.
    return self.created_by

  def __str__(self) -> str:
    return f"DealDiscussion(deal_id={self.deal.id if self.deal else None}, created_by={self.created_by.id if self.created_by else None})"
