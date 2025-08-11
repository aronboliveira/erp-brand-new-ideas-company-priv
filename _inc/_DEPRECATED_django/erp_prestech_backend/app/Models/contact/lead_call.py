from django.db import models
from .._helpers.connectors.lead_connected import LeadConnected
from .._helpers.connectors.user_connected import UserConnected
from .._helpers.describable import Describable
from .._helpers.fields import (default_char_field, default_text_field, DURATION_VALIDATOR)
from typing import TYPE_CHECKING
if TYPE_CHECKING:
  from ..individuals.user import User
class LeadCall(Describable, LeadConnected, UserConnected):
  subject = default_char_field(db_index=True)
  call_type = default_char_field(db_index=True)
  duration = models.DurationField(db_index=True, max_length=4, validators=DURATION_VALIDATOR)
  call_result = default_text_field(default='No call result was written')
  
  def get_lead_call_user(self) -> "User":
    return self.user
  
  class Meta:
    db_table = 'lead_call'
