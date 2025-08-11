from django.db import models
from .._helpers.connectors.deal_connected import DealConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_user_creation, uuid_def_primary, default_char_field
class DealFile(DefaultTimed, DealConnected):
  id = models.UUIDField(**uuid_def_primary())
  file_name = default_char_field()
  file_path = default_char_field()
  created_by = default_user_creation('%(class)s_created_by')

  def __str__(self) -> str:
    return f"DealFile(deal_id={self.deal.id if self.deal else None}, file_name={self.file_name})"
