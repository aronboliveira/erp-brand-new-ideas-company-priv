from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation

class IpRestrict(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  ip = models.GenericIPAddressField(unpack_ipv4=True)
  created_by = default_user_creation('%(class)s_created_by')

  def __str__(self) -> str:
    return self.ip

  class Meta:
    ordering = ('-created_at',)
