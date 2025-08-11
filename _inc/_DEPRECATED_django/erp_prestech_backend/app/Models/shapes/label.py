from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_char_field, default_user_creation

class Label(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = default_char_field()
  color = models.CharField(max_length=20, default='primary', choices=[
    ("primary", "Primary"),
    ("secondary", "Secondary"),
    ("danger", "Danger"),
    ("warning", "Warning"),
    ("info", "Info"),
    ("success", "Success"),
  ])
  pipeline = models.ForeignKey("Pipeline", on_delete=models.CASCADE, related_name="labels")
  created_by = default_user_creation("%(class)s_created_by")
  
  class Meta:
    db_table = "label"
  
  def __str__(self) -> str:
    return self.name
