from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, default_char_field
from ..individuals.user import User
from typing import TYPE_CHECKING
if TYPE_CHECKING:
    from .user_email_template import UserEmailTemplate
class EmailTemplate(DefaultTimed):
    id = models.UUIDField(**uuid_def_primary())
    name = default_char_field()
    from_email = models.EmailField(max_length=254)
    created_by = default_user_creation('%(class)s_created_by')

    def __str__(self) -> str:
        return self.name

    def get_user_template(self, user: "User") -> "UserEmailTemplate":
        return self.user_email_templates.filter(user_id=user.id).first()

    @classmethod
    def email_template_data(cls) -> "EmailTemplate":
        if not hasattr(cls, "_template_data"):
            cls._template_data = cls.objects.first()
        return cls._template_data

    class Meta:
        db_table = "email_template"
