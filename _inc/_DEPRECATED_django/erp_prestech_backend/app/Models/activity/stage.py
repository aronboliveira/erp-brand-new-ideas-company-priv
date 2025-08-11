from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation
from .._helpers.connectors.pipeline_connected import PipelineConnected
from ..individuals.user import User
class Stage(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = models.CharField(max_length=255)
  order = models.PositiveIntegerField(default=0)
  created_by = default_user_creation('%(class)s_created_by')

  def get_deals(self, user: User):
    """
    Retrieve deals associated with this stage.
    
    - For a user with type 'client', return deals via the client-deals relation.
    - Otherwise, return deals via the user-deals relation.
    """
    from ..activity.deal import Deal
    if user.type == 'client':
      return Deal.objects.filter(clients=user, stage=self).order_by('order')
    return Deal.objects.filter(users=user, stage=self).order_by('order')

  def __str__(self) -> str:
    return self.name

  class Meta:
    ordering = ('-created_at',)
