from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation
class ChMessage(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  created_by = default_user_creation('%(class)s_created_by')
  class Meta:
    db_table = 'ch_message'