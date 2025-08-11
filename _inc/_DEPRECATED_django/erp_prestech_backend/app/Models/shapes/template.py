from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_char_field, 
                               default_text_field, default_user_creation, VOID)

class Template(DefaultTimed):
    id = models.UUIDField(**uuid_def_primary())
    template_name = default_char_field(db_index=True)
    prompt = default_text_field(length='medium', default='No prompt text was defined')
    field_json = models.JSONField(**VOID)
    is_tone = models.BooleanField(default=False)
    created_by = default_user_creation("%(class)s_created_by", db_index=True)

    def __str__(self) -> str:
        return self.template_name
