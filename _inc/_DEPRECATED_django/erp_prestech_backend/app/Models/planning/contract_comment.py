from django.db import models
from .._helpers.connectors.contract_connected import ContractConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_user_creation, default_text_field,
                               NO_COMMENT)
from .._helpers.connectors.user_connected import UserConnected
from typing import TYPE_CHECKING
if TYPE_CHECKING:
  from ..individuals.user import User
class ContractComment(DefaultTimed, ContractConnected, UserConnected):
  id = models.UUIDField(**uuid_def_primary())
  comment = default_text_field(default=NO_COMMENT)
  created_by = default_user_creation('%(class)s_created_by')
  class Meta:
    db_table = 'contract_comment'
  @property
  def user(self) -> "User":
    from ..individuals.user import User
    return User.objects.filter(id=self.created_by_id).first()
