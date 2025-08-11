from django.db import models
from django.db.models import Q
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, default_char_field, VOID
from ..individuals.user import User

class BugStatus(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  title = default_char_field()
  created_by = default_user_creation('%(class)s_created_by')
  order = models.PositiveIntegerField(default=0, **VOID)

  def bugs(self, project_id: int, user: User):
    from .bug import Bug
    return (
      Bug.objects.filter(status=self.id, project_id=project_id)
      .order_by('order')
      if user.type in ['company', 'client']
      else Bug.objects.filter(status=self.id, project_id=project_id)
               .filter(Q(assign_to__icontains=str(user.id)))
               .order_by('order')
    )

  def assign_bugs(self, project_id: int, user: User):
    from .bug import Bug
    return Bug.objects.filter(
      status=self.id, project_id=project_id, assign_to=str(user.id)
    ).order_by('order')

  def __str__(self) -> str:
    return self.title

  class Meta:
    ordering = ('-created_at',)
