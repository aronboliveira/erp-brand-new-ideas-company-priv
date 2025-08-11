from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, default_text_field

class CustomQuestion(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  question = default_text_field(default='No question label was defined')
  is_required = models.CharField(max_length=3, choices=[("yes", "Yes"), ("no", "No")], default="no")
  created_by = default_user_creation("%(class)s_created_by")
  IS_REQUIRED_CHOICES = {
    "yes": "Yes",
    "no": "No",
  }
  class Meta:
    verbose_name = "Custom Question"
    verbose_name_plural = "Custom Questions"
    ordering = ["-id"]
  def __str__(self) -> str:
    return self.question
