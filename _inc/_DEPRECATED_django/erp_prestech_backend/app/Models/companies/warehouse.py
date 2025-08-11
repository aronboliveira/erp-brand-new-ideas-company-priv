from django.db import models
from django.contrib.auth import get_user_model
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_user_creation, uuid_def_primary, default_char_field, VOID

User = get_user_model()

class Warehouse(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = default_char_field()
  address = models.TextField(**VOID, max_length=65535)
  city = default_char_field(voidable=True)
  city_zip = models.CharField(max_length=20, **VOID)
  created_by = default_user_creation('%(class)s_created_by')

  class Meta:
    db_table = 'warehouses'

  def __str__(self) -> str:
    return self.name

  @classmethod
  def warehouse_id(cls, warehouse_id: str, creator_id: str) -> str:
    warehouse = cls.objects.filter(id=warehouse_id, created_by=creator_id).first()
    return warehouse.id if warehouse else 0
