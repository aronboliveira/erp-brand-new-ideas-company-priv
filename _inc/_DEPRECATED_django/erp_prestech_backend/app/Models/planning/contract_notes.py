from django.db import models
from .._helpers.connectors.contract_connected import ContractConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, default_text_field
from .._helpers.connectors.user_connected import UserConnected
from typing import TYPE_CHECKING
if TYPE_CHECKING:
  from ..individuals.user import User
class ContractNotes(DefaultTimed, ContractConnected, UserConnected):
  id = models.UUIDField(**uuid_def_primary())
  notes = default_text_field(default='No notes were written')
  created_by = default_user_creation('%(class)s_created_by')
  class Meta:
    db_table = 'contract_notes'
  @property
  def user(self) -> "User":
    from ..individuals.user import User
    return User.objects.filter(id=self.created_by_id).first()
