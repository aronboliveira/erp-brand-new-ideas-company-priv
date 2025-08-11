from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_char_field, default_user_creation
from .._helpers.connectors.lead_connected import LeadConnected
class LeadFile(DefaultTimed, LeadConnected):
  id = models.UUIDField(**uuid_def_primary())
  file_name = default_char_field()
  file_path = default_char_field()
  created_by = default_user_creation('%(class)s_created_by')

  class Meta:
    db_table = 'lead_file'
    ordering = ['-created_at']
