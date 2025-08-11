from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_char_field, default_user_creation

class FormBuilder(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  form = models.ForeignKey('Form', on_delete=models.CASCADE, related_name='form_builders', db_column='form')
  name = default_char_field()
  type = models.CharField(max_length=50)
  created_by = default_user_creation("%(class)s_created")
  
  FIELD_TYPES = [
    ("text", "Text"),
    ("email", "Email"),
    ("number", "Number"),
    ("date", "Date"),
    ("textarea", "Textarea"),
  ]
  
  class Meta:
    db_table = "form_builder"
  
  def __str__(self) -> str:
    return self.name
