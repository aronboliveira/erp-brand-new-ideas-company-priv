from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_char_field, default_user_creation
from .form_builder import FormBuilder

class FormField(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  form = models.ForeignKey(
    FormBuilder,
    on_delete=models.CASCADE,
    db_column="form_id",
    related_name="form_field"
  )
  name = default_char_field()
  type = models.CharField(max_length=50)
  created_by = default_user_creation("%(class)s_created")
  
  class Meta:
    db_table = "form_field"
  
  def __str__(self) -> str:
    return self.name
