from django.db import models
from django.core.files.storage import default_storage
from .default_timed import DefaultTimed
from .fields import (uuid_def_primary, default_char_field, default_user_creation, 
                     FILE_SIZE_VALIDATOR, VOID)
class Describable(DefaultTimed):
	
  class Meta:
    abstract = True
  
  id = models.UUIDField(**uuid_def_primary())
  title = default_char_field(default='NO GIVEN TITLE', **VOID)
  description = models.TextField(max_length=65535, default="NO GIVEN DESCRIPTION", **VOID)
  notes = models.TextField(max_length=2048, default="No notes taken" **VOID)
  created_by = default_user_creation('%(class)s_created_by')
  document_url = models.URLField(max_length=200, **VOID)
  attachments = models.FileField(upload_to='%(class)s_attachments/', storage=default_storage, 
                                 validators=[FILE_SIZE_VALIDATOR], **VOID)