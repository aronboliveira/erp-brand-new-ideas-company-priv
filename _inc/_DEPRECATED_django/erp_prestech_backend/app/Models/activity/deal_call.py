from django.db import models
from .._helpers.connectors.deal_connected import DealConnected
from .._helpers.describable import Describable
from .._helpers.connectors.user_connected import UserConnected
from .._helpers.fields import (default_char_field, 
                               VOID, DURATION_VALIDATOR)
from typing import TYPE_CHECKING
if TYPE_CHECKING:
  from ..individuals.user import User
class DealCall(Describable, DealConnected, UserConnected):
  subject = default_char_field()
  call_type = default_char_field()
  duration = models.DurationField(max_length=4, validators=DURATION_VALIDATOR, db_index=True)
  call_result = models.TextField(**VOID, db_index=True, max_length=65535, default='No result was described')

  def get_deal_call_user(self) -> 'User':
    # Return the related user via the foreign key.
    return self.user

  def __str__(self) -> str:
    return f"DealCall(subject={self.subject}, user_id={self.user.id if self.user else None})"
