from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation
from ..individuals.user import User

class Pipeline(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = models.CharField(max_length=255)
  created_by = default_user_creation('%(class)s_created_by')

  def get_stages(self, user: User):
    from ..activity.source import Stage
    owner_id = getattr(user, 'owner_id', None)
    if owner_id is None:
      raise ValueError("The user does not have an 'owner_id' attribute.")
    return Stage.objects.filter(pipeline=self, created_by=owner_id).order_by('order')

  def get_lead_stages(self, user: User):
    from ..activity.lead_stage import LeadStage
    owner_id = getattr(user, 'owner_id', None)
    if owner_id is None:
      raise ValueError("The user does not have an 'owner_id' attribute.")
    return LeadStage.objects.filter(pipeline=self, created_by=owner_id).order_by('order')

  def __str__(self) -> str:
    return self.name

  class Meta:
    ordering = ('-created_at',)
