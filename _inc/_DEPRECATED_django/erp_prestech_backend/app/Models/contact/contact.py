from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, default_char_field, VOID
class Contact(DefaultTimed):
    id = models.UUIDField(**uuid_def_primary())
    name = default_char_field(db_index=True)
    email = models.EmailField(max_length=254, unique=True, **VOID)
    phone = models.CharField(max_length=50, **VOID)
    company = models.ForeignKey(
        "Company",
        on_delete=models.SET_NULL,
        null=True,
        blank=True,
        related_name="contacts",
        db_index=True
    )
    created_by = default_user_creation('%(class)s_created_by', db_index=True)

    def __str__(self) -> str:
        return self.name

    class Meta:
        db_table = "contact"
