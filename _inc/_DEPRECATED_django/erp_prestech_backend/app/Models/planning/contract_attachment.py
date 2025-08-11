from django.db import models
from django.core.files.storage import default_storage
from .._helpers.connectors.contract_connected import ContractConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, FILE_SIZE_VALIDATOR, VOID
from .._helpers.connectors.user_connected import UserConnected
class ContractAttachment(DefaultTimed, ContractConnected, UserConnected):
  id = models.UUIDField(**uuid_def_primary())
  file = models.FileField(upload_to='contract_attachments/', storage=default_storage, validators=[FILE_SIZE_VALIDATOR])
  file_url = models.URLField(max_length=200, **VOID)
  created_by = default_user_creation('%(class)s_created_by')
  class Meta:
    db_table = 'contract_attachment'
