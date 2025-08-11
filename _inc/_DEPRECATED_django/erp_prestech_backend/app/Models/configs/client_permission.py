from django.db import models
from .._helpers.connectors.customer_connected import CustomerConnected
from .._helpers.connectors.deal_connected import DealConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, default_char_field
class ClientPermission(DefaultTimed, CustomerConnected, DealConnected):
  id = models.UUIDField(**uuid_def_primary())
  permissions = default_char_field()
  created_by = default_user_creation('%(class)s_created_by')

  def __str__(self) -> str:
    return f"ClientPermission(client_id={self.client_id}, deal_id={self.deal_id}, permissions={self.permissions})"

  class Meta:
    ordering = ('-created_at',)
