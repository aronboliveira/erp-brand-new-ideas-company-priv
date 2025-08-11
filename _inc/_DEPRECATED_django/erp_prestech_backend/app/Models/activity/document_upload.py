from django.db import models
from django.core.files.storage import default_storage
from .._helpers.describable import Describable
from .._helpers.fields import default_char_field, FILE_SIZE_VALIDATOR
class DocumentUpload(Describable):
  name = default_char_field()
  role = models.IntegerField()
  document = models.FileField(upload_to="document_uploads/", storage=default_storage, 
                              max_length=200, validators=[FILE_SIZE_VALIDATOR])

  class Meta:
    db_table = 'document_upload'

  def __str__(self) -> str:
    return self.name
