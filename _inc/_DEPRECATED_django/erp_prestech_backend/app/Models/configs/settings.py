from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation

class Setting(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = models.CharField(max_length=191)
  value = models.TextField(blank=True, null=True, max_length=65535)
  created_by = default_user_creation('%(class)s_created_by')

  class Meta:
    db_table = 'settings'
    unique_together = ('name', 'created_by')
    verbose_name = 'Setting'
    verbose_name_plural = 'Settings'
    ordering = ('-created_at',)

  def __str__(self) -> str:
    return f"{self.name} = {self.value}"
