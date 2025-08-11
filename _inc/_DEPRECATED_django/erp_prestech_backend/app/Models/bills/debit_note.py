from django.db import models
from django.utils import timezone
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, defalt_decicmal_10, default_char_field, VALID_MYSQL_MIN_DATE
class DebitNote(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  bill = default_char_field()
  vendor = models.OneToOneField(
    'Vendor',
    on_delete=models.CASCADE
  )
  amount = defalt_decicmal_10()
  date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now)
  created_by = default_user_creation('%(class)s_created_by')

  class Meta:
    db_table = "debit_note"
