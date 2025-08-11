from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (default_char_field, default_user_creation, uuid_def_primary,
                               default_text_field, LANG_CHOICES, VOID)
class MarkupTemplate(DefaultTimed):
  
	class Meta:
		abstract = True
  
	id = models.UUIDField(**uuid_def_primary())
	lang = default_char_field(max_length=20, default='en', choices=LANG_CHOICES, db_idnex=True)
	content = default_text_field(length='medium', **VOID)
	created_by = default_user_creation("%(class)s_created")