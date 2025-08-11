from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation

class Permission(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = models.CharField(max_length=100, unique=True)
  created_by = default_user_creation('%(class)s_created_by', db_index = True)

  def __str__(self) -> str:
    return self.name

class Role(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = models.CharField(max_length=100, unique=True)
  permissions = models.ManyToManyField('Permission', blank=True, related_name='roles')
  created_by = default_user_creation('%(class)s_created_by', db_index=True)

  def __str__(self) -> str:
    return self.name

  @classmethod
  def find_by_name(cls, name: str) -> "Role":
    return cls.objects.get(name=name)

  @classmethod
  def find_by_id(cls, role_id: str) -> "Role":
    return cls.objects.get(id=role_id)

  def give_permission_to(self, permission: any) -> None:
    if isinstance(permission, (list, tuple, set)):
      for perm in permission:
        self.give_permission_to(perm)
    else:
      if isinstance(permission, str):
        perm_obj = Permission.objects.get(name=permission)
        self.permissions.add(perm_obj)
      elif isinstance(permission, Permission):
        self.permissions.add(permission)

  def get_permission_names(self) -> list:
    return list(self.permissions.values_list('name', flat=True))

  def assign_role_to_user(self, user: any) -> None:
    user.roles.add(self)
