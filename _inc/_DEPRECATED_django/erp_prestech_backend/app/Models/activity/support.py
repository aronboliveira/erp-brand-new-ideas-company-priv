from django.db import models
import logging
from typing import Dict
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_char_field, default_user_creation, uuid_def_primary, VALID_MYSQL_MIN_DATE
from .._helpers.connectors.user_connected import UserConnected
from ..individuals.user import User

logger = logging.getLogger(__name__)

class Support(DefaultTimed, UserConnected):
  id = models.UUIDField(**uuid_def_primary())
  subject = default_char_field()
  priority = default_char_field()
  end_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])
  ticket_code = default_char_field()
  ticket_created = models.ForeignKey(
    'User',
    null=True,
    blank=True,
    on_delete=models.SET_NULL,
    related_name='support_ticket_created'
  )
  status = default_char_field()
  created_by = default_user_creation('%(class)s_created_by')

  priority_choices = ['Low', 'Medium', 'High', 'Critical']
  status_choices = {
    'Open': 'Open',
    'Close': 'Close',
    'On Hold': 'On Hold'
  }

  @classmethod
  def get_status_choices(cls) -> Dict[str, str]:
    return cls.status_choices

  def assign_user(self) -> User:
    return self.user

  def reply_unread(self, current_user: User) -> int:
    from ..contact.support_reply import SupportReply
    try:
      if getattr(current_user, 'type', None) == 'Employee':
        count = SupportReply.objects.filter(support=self, is_read=False).exclude(user=current_user).count()
      else:
        count = SupportReply.objects.filter(support=self, is_read=False).count()
      return count
    except Exception as e:
      logger.error(f"Failed to retrieve unread replies in Support: {e}")
      return 0

  class Meta:
    db_table = 'support'
    ordering = ('-created_at',)
