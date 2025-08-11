from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_char_field, default_user_creation, uuid_def_primary

class PayslipType(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = default_char_field()
  created_by = default_user_creation('%(class)s_created_by')

  class Meta:
    db_table = "payslip_type"
