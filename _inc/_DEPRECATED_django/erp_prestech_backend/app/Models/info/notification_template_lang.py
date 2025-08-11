from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_user_creation, 
                               default_text_field, LANG_CHOICES)
class NotificationTemplateLang(DefaultTimed):
    id = models.UUIDField(**uuid_def_primary())
    parent = models.ForeignKey('NotificationTemplates',
        on_delete=models.CASCADE,
        related_name="translations",
        db_index=True
    )
    lang = models.CharField(max_length=20, db_index=True, default='en', choices=LANG_CHOICES)
    content = default_text_field(length='medium', default='No content was written')
    variables = default_text_field(default='No variables were defined')
    created_by = default_user_creation('%(class)s_created_by')

    def __str__(self) -> str:
        return f"TemplateLang ({self.lang}) for Parent ID {self.parent.uuid}"

    class Meta:
        db_table = "notification_template_lang"
