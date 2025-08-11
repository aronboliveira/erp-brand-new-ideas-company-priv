import logging
from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation
from .._helpers.connectors.lead_connected import LeadConnected
from .._helpers.connectors.user_connected import UserConnected
from typing import Optional, TYPE_CHECKING
if TYPE_CHECKING:
  from ..individuals.user import User
class UserLead(DefaultTimed, LeadConnected, UserConnected):
  id = models.UUIDField(**uuid_def_primary())
  created_by = default_user_creation('%(class)s_created_by')

  class Meta:
    db_table = 'user_lead'
    ordering = ['-created_at']

  def get_lead_user(self) -> Optional[User]:
    try:
      return self.user
    except Exception as e:
      logging.error(f"Failed to get lead user in UserLead: {e}")
      return None
