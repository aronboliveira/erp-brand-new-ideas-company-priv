import logging
from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_user_creation, default_text_field,
                               DEFAULT_USER_TYPED, NO_COMMENT)
from typing import Optional, TYPE_CHECKING
if TYPE_CHECKING:
  from ..individuals.user import User

class BugComment(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  comment = default_text_field(default=NO_COMMENT)
  bug = models.ForeignKey('Bug', on_delete=models.CASCADE, related_name='comments')
  user_type = models.CharField(**DEFAULT_USER_TYPED)
  created_by = default_user_creation('%(class)s_created_by')

  @property
  def comment_user(self) -> Optional[User]:
    return self.created_by

  class Meta:
    db_table = 'bug_comments'
    ordering = ('-created_at',)
    # Add indexes if frequently filtered by: bug/created_at/user_type
    # Consider UniqueConstraint for (bug, created_by) if needed

  def save(self, *args, **kwargs) -> None:
    try:
      super().save(*args, **kwargs)
    except Exception as e:
      logging.error(f"Failed to save BugComment {self.id}: {str(e)}")
      raise
