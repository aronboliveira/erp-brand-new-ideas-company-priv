from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_char_field, uuid_def_primary, default_user_creation

class Document(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = default_char_field()
  is_required = models.BooleanField(default=False)
  created_by = default_user_creation("%(class)s_created_by")
  class Meta:
    db_table = "document"
    ordering = ["name"]
  def __str__(self) -> str:
    return self.name
