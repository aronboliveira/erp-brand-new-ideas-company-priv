from django.db import models
from typing import Union
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_char_field, default_user_creation
from ..individuals.user import User
class Location(DefaultTimed):
    id = models.UUIDField(**uuid_def_primary())
    name = default_char_field()
    company_id = models.CharField(max_length=36)
    is_active = models.BooleanField(default=True)
    created_by = default_user_creation("%(class)s_created_by")

    class Meta:
        db_table = "locations"
        ordering = ("-created_at",)

    def __str__(self) -> str:
        return self.name

    @classmethod
    def user_current_location(cls, user: User) -> Union[str, int]:
        company_id = getattr(user, "company_id", None)
        if user.user_type == "company":
            location = cls.objects.filter(id=user.current_location, company_id=company_id, is_active=True).first()
            return str(location.id) if location else 0
        elif user.user_type not in ["company", "super admin"]:
            if getattr(user, "current_location", 0) == 0:
                user.current_location = user.location_id
                user.save(update_fields=["current_location"])
            location = cls.objects.filter(id=user.current_location, company_id=company_id).first()
            return str(location.id) if location else 0
        return 0

    def __str__(self) -> str:
        return self.name
