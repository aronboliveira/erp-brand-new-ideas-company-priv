from django.db import models
from django.core.files.storage import default_storage
from .._helpers.connectors.branch_connected import BranchConnected
from .._helpers.describable import Describable
from .._helpers.fields import (default_char_field, 
                               FILE_SIZE_VALIDATOR, VOID, VALID_FILE_SIZES)
class CompanyPolicy(Describable, BranchConnected):
  title = default_char_field()
  file = models.FileField(upload_to='company_policies/', validators=[FILE_SIZE_VALIDATOR], storage=default_storage, **VOID)
  file_size = models.IntegerField(validators=VALID_FILE_SIZES, **VOID)

  def __str__(self) -> str:
    return self.title
