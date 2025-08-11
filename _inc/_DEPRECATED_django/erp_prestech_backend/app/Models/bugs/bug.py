from django.db import models
from django.utils import timezone
from .._helpers.describable import Describable
from .._helpers.connectors.project_connected import ProjectConnected
from .._helpers.fields import (default_char_field, 
                               VOID, VALID_MYSQL_MIN_DATE)
from typing import Optional, TYPE_CHECKING
if TYPE_CHECKING:
  from ..individuals.user import User
  from .bug_status import BugStatus
class Bug(Describable, ProjectConnected):
  bug_id = models.CharField(max_length=36, unique=True)
  title = default_char_field()
  priority = models.CharField(
    max_length=10,
    default='low',
    choices=[('low', 'Low'), ('medium', 'Medium'), ('high', 'High')]
  )
  start_date = models.DateField(**VOID, validators=[VALID_MYSQL_MIN_DATE], default=timezone.now)
  due_date = models.DateField(**VOID, validators=[VALID_MYSQL_MIN_DATE])
  status = models.ForeignKey('BugStatus', on_delete=models.SET_NULL, null=True, related_name='bugs', db_index=True)
  assign_to = default_char_field()  # CSV list of user IDs

  def bug_status(self) -> Optional['BugStatus']:
    return self.status

  def assign_to_user(self) -> Optional[User]:
    # Note: assign_to is a CSV; this returns the first matching user, if any
    return User.objects.filter(id=self.assign_to).first()

  def created_by_user(self) -> Optional[User]:
    return self.created_by

  def comments(self) -> models.QuerySet:
    return self.bugcomment_set.all().order_by('-created_at')

  def bug_files(self) -> models.QuerySet:
    return self.bugfile_set.all().order_by('-created_at')

  def users(self) -> models.QuerySet:
    user_ids = [int(uid) for uid in self.assign_to.split(',') if uid.strip().isdigit()]
    return User.objects.filter(id__in=user_ids)

  def __str__(self) -> str:
    return self.title
