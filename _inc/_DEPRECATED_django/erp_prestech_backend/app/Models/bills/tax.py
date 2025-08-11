from django.core.validators import MinValueValidator, MaxValueValidator
from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, defalt_decicmal_10, default_user_creation, default_char_field

class Tax(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = default_char_field()
  rate = defalt_decicmal_10(validators=[
    MinValueValidator(0, message='Minimum is 0'),
    MaxValueValidator(100.00, message='Exceeds limits of 100%')
  ])
  created_by = default_user_creation('%(class)s_created_by')

  def __str__(self) -> str:
    return f"{self.name} ({self.rate}%)"
