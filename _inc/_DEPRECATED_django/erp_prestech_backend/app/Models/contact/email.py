from django.db import models
from .._helpers.describable import Describable
from .._helpers.fields import VOID
class Email(Describable):
    email = models.EmailField(max_length=254, unique=True)
    module_type = models.CharField(max_length=100)
    module_id = models.UUIDField(max_length=36, **VOID)

    class Meta:
        db_table = "email"
        ordering = ["-created_at"]
