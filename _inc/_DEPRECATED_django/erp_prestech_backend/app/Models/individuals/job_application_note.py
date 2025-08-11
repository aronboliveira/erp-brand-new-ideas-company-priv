from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, default_text_field

class JobApplicationNote(DefaultTimed):
    id = models.UUIDField(**uuid_def_primary())
    application = models.ForeignKey('JobApplication', on_delete=models.CASCADE, related_name='notes')
    note_created_fk = models.ForeignKey(
        'User',
        on_delete=models.SET_NULL,
        db_column='note_created',
        null=True,
        blank=True,
        related_name='+'
    )
    note = default_text_field(default='No note was written')
    created_by = default_user_creation('%(class)s_created_by')
