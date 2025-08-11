from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_user_creation, uuid_def_primary, default_char_field, VOID

class Company(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = default_char_field(db_index=True)
  industry = default_char_field(voidable=True)
  website = models.URLField(max_length=200, **VOID)
  email = models.EmailField(max_length=254, unique=True, help_text='Official given company email', **VOID)
  phone = models.CharField(max_length=50, help_text='Offical given company telephone number', **VOID)
  address = models.TextField(max_length=65535, help_text='Given physical address', **VOID)
  created_by = default_user_creation('%(class)s_created_by')

  def __str__(self) -> str:
    return self.name
