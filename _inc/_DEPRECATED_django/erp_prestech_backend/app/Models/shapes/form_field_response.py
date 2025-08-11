from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, UUID_VERIFIED, VOID
from .form_builder import FormBuilder  
class FormFieldResponse(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  form = models.ForeignKey(
    FormBuilder,
    on_delete=models.CASCADE,
    db_column="form_id",
    related_name="field_responses"
  )
  subject_id = models.CharField(**UUID_VERIFIED, **VOID)
  name_id = models.CharField(**UUID_VERIFIED, **VOID)
  email_id = models.CharField(**UUID_VERIFIED, **VOID)
  user_id = models.CharField(**UUID_VERIFIED, **VOID)
  pipeline_id = models.CharField(**UUID_VERIFIED, **VOID)
  
  class Meta:
    db_table = "form_field_response"
  
  def __str__(self) -> str:
    return f"FormFieldResponse for form {self.form_id}"
