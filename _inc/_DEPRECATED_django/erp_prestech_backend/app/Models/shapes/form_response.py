from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary
from .form_builder import FormBuilder

class FormResponse(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  form = models.ForeignKey(
    FormBuilder,
    on_delete=models.CASCADE,
    db_column="form_id",
    related_name="responses"
  )
  response = models.JSONField()
  
  class Meta:
    db_table = "form_response"
  
  def __str__(self) -> str:
    return f"Response for form {self.form_id}"
