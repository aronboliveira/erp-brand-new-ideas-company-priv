from django.db import models
from .._helpers.connectors.user_connected import UserConnected
from .._helpers.describable import Describable
class UserToDo(Describable, UserConnected):
  is_complete = models.BooleanField(default=False)
  
  class Meta:
    db_table = 'user_to_dos'
  def __str__(self) -> str:
    return f"{self.title} - {'Complete' if self.is_complete else 'Pending'}"
