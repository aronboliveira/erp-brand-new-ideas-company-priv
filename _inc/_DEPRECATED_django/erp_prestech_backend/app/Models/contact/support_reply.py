from django.db import models
from .._helpers.connectors.user_connected import UserConnected
from .._helpers.describable import Describable
class SupportReply(Describable, UserConnected):
  support = models.ForeignKey('Support', on_delete=models.CASCADE)
  is_read = models.BooleanField(default=False)

  class Meta:
    db_table = 'support_reply'
