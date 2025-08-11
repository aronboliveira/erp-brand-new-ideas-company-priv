from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_user_creation, default_char_field,
                               default_text_field, LANG_CHOICES)
class EmailTemplateLang(DefaultTimed):
    id = models.UUIDField(**uuid_def_primary())
    parent = models.ForeignKey(
        "EmailTemplate",
        on_delete=models.CASCADE,
        related_name="translations",
        db_index= True
    )
    lang = models.CharField(max_length=20, db_index=True, default='en', choices=LANG_CHOICES)
    subject = default_char_field()
    content = default_text_field(length='medium', default='No content was written')
    created_by = default_user_creation('%(class)s_created_by')

    def __str__(self) -> str:
        return f"{self.lang} - {self.subject}"

    class Meta:
        db_table = "email_template_lang"
