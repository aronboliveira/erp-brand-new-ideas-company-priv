from django.db import models
from django.utils import timezone
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_user_creation, uuid_def_primary,default_char_field, VALID_MYSQL_MIN_DATE, VOID

class Branch(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = default_char_field()
  address = models.TextField(max_length=65535)
  creation = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now, **VOID)
  representative = models.ForeignKey('Employee', on_delete=models.SET_NULL, db_index=True, **VOID)
  created_by = default_user_creation('%(class)s_created_by')

  def __str__(self) -> str:
    return self.name
