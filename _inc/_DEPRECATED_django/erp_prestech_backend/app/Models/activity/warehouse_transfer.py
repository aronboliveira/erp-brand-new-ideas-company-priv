from django.db import models
from django.contrib.auth import get_user_model
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, defalt_decicmal_10, VALID_MYSQL_MIN_DATE

User = get_user_model()

class WarehouseTransfer(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  from_warehouse = models.ForeignKey(
    'Warehouse',
    on_delete=models.CASCADE,
    related_name='outgoing_transfers'
  )
  to_warehouse = models.ForeignKey(
    'Warehouse',
    on_delete=models.CASCADE,
    related_name='incoming_transfers'
  )
  product = models.ForeignKey(
    'ProductService',
    on_delete=models.CASCADE,
    related_name='warehouse_transfers'
  )
  quantity = defalt_decicmal_10()
  date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])
  created_by = default_user_creation('%(class)s_created_by')

  def __str__(self) -> str:
    return f"Transfer {self.id}: {self.quantity} of {self.product} from {self.from_warehouse} to {self.to_warehouse} on {self.date}"
