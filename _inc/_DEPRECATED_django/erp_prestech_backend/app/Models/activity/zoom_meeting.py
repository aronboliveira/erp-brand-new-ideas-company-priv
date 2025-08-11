import logging
from datetime import datetime, timedelta
from django.db import models
from .._helpers.connectors.customer_connected import CustomerConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_user_creation, default_char_field,
                               DURATION_VALIDATOR, COMPLETION_CHOICES)
from .._helpers.connectors.project_connected import ProjectConnected
from .._helpers.connectors.user_connected import UserConnected
from typing import List, Optional, TYPE_CHECKING
if TYPE_CHECKING:
  from ..individuals.user import User
  from ..planning.project import Project

class ZoomMeeting(DefaultTimed, CustomerConnected, ProjectConnected, UserConnected):
  id = models.UUIDField(**uuid_def_primary())
  title = default_char_field()
  meeting_id = models.UUIDField(**uuid_def_primary(pk=False))
  start_date = models.DateTimeField(auto_now_add=True)
  duration = models.DurationField(max_length=4, validators=DURATION_VALIDATOR)
  start_url = models.URLField(max_length=1024)
  password = default_char_field()
  join_url = models.URLField(max_length=1024)
  status = models.CharField(max_length=50, default='pending', choices=COMPLETION_CHOICES)
  created_by = default_user_creation('%(class)s_created_by')

  class Meta:
    db_table = 'zoom_meeting'
    ordering = ['-created_at']

  @property
  def customer_name(self) -> str:
    try:
      customer_obj = self.customer
      return customer_obj.name if customer_obj else ""
    except Exception as e:
      logging.error(f"Failed to get customer name in ZoomMeeting: {e}")
      return ""

  def check_date_time(self) -> int:
    try:
      end_time = self.start_date + timedelta(minutes=self.duration)
      return 1 if end_time > datetime.now() else 0
    except Exception as e:
      logging.error(f"Failed to check date time in ZoomMeeting: {e}")
      return 0

  def project_name(self) -> Optional[Project]:
    try:
      return self.project
    except Exception as e:
      logging.error(f"Failed to get project name in ZoomMeeting: {e}")
      return None

  def user_name(self) -> Optional[User]:
    try:
      return self.user
    except Exception as e:
      logging.error(f"Failed to get user name in ZoomMeeting: {e}")
      return None

  def get_users(self, users: str) -> List[User]:
    try:
      user_arr = users.split(',')
      user_list: List[User] = []
      for uid in user_arr:
        user_obj = User.objects.filter(id=uid).first()
        if user_obj:
          user_list.append(user_obj)
      return user_list
    except Exception as e:
      logging.error(f"Failed to get users in ZoomMeeting: {e}")
      return []
