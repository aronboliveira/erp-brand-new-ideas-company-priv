from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_char_field, default_user_creation

class NotificationTemplates(DefaultTimed):
    id = models.UUIDField(**uuid_def_primary())
    name = default_char_field()
    type = default_char_field()
    slug = default_char_field(db_index=True)
    created_by = default_user_creation('%(class)s_created_by')

    class Meta:
        db_table = "notification_templates"
