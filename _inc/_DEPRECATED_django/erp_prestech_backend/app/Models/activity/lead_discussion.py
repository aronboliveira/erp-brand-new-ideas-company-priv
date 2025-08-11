from django.db import models
import logging
from .._helpers.default_timed import DefaultTimed
from .._helpers.connectors.lead_connected import LeadConnected
from .._helpers.fields import (uuid_def_primary, default_user_creation, 
                               default_text_field, NO_COMMENT, VOID)
from typing import Optional, TYPE_CHECKING
if TYPE_CHECKING:
  from ..individuals.user import User
class LeadDiscussion(DefaultTimed, LeadConnected):
  id = models.UUIDField(**uuid_def_primary())
  comment = default_text_field(voidable=True, default=NO_COMMENT)
  created_by = default_user_creation('%(class)s_created_by')

  class Meta:
    db_table = 'lead_discussion'
    ordering = ['-created_at']

  def get_user(self) -> Optional[User]:
    try:
      return self.created_by
    except Exception as e:
      logging.error(f"Failed to get user for LeadDiscussion: {e}")
      return None
