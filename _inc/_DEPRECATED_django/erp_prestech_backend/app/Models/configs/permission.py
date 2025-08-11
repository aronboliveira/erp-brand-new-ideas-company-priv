from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation
from ..individuals.user import User

class Permission(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = models.CharField(max_length=255, unique=True)
  guard_name = models.CharField(max_length=50, default='web')
  created_by = default_user_creation('%(class)s_created_by')

  class Meta:
    verbose_name = "Permission"
    verbose_name_plural = "Permissions"
    ordering = ["name"]

  def __str__(self) -> str:
    return self.name

  @staticmethod
  def user_can(user: User, permission_name: str) -> bool:
    """
    Check if the given user has the specified permission.
    Assumes a many-to-many relationship between User and Permission via a related table.
    """
    if not user.is_authenticated:
      return False
    if user.has_perm(permission_name):
      return True
    return user.permissions.filter(name=permission_name).exists()
