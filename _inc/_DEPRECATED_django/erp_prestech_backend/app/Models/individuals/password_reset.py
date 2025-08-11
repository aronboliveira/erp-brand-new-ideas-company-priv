from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation

class PasswordReset(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  email = models.EmailField(max_length=254)
  token = models.CharField(max_length=100, unique=True)
  created_by = default_user_creation('%(class)s_created_by')

  class Meta:
    db_table = 'password_resets'
    verbose_name = 'Password Reset'
    verbose_name_plural = 'Password Resets'

  def __str__(self) -> str:
    return f"Password Reset for {self.email} at {self.created_at}"
