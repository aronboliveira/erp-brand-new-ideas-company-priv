from django.db import models
from .._helpers.connectors.branch_connected import BranchConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_user_creation, default_char_field, 
                               PHONE_VALIDATOR, VOID)
class Trainer(DefaultTimed, BranchConnected):
  id = models.UUIDField(**uuid_def_primary())
  firstname = default_char_field()
  lastname = default_char_field()
  contact = models.CharField(max_length=126, blank=True, validators=[PHONE_VALIDATOR])
  email = models.EmailField(max_length=254, unique=True)
  address = models.TextField(**VOID, max_length=65535)
  expertise = models.TextField(**VOID, max_length=65535)
  created_by = default_user_creation('%(class)s_created_by', db_index=True)

  def __str__(self) -> str:
    return f"{self.firstname} {self.lastname}"
