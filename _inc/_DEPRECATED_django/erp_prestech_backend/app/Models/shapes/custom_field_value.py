from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_text_field
import uuid
class CustomFieldValue(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  record_id = models.UUIDField(default=uuid.uuid4, editable=False, primary_key=False)
  field = models.ForeignKey("CustomField", on_delete=models.CASCADE)
  value = default_text_field(default='No value for the field was defined')
  
  class Meta:
    db_table = "custom_field_value"
