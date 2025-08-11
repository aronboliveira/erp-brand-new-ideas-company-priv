from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation
from .._helpers.connectors.lead_connected import LeadConnected
from ..individuals.user import User

class LeadStage(DefaultTimed, LeadConnected):
  id = models.UUIDField(**uuid_def_primary())
  name = models.CharField(max_length=255)
  pipeline = models.ForeignKey('Pipeline', on_delete=models.SET_NULL, null=True, blank=True, related_name='stages')
  order = models.PositiveIntegerField(default=0)
  created_by = default_user_creation('%(class)s_created_by')

  def get_leads(self, user: User):
    from .lead import Lead
    if user.type == 'company':
      return Lead.objects.filter(
        created_by=user.creator_id(),
        stage=self
      ).order_by('order')
    else:
      return Lead.objects.filter(
        assigned_users=user,
        stage=self
      ).order_by('order')

  def __str__(self) -> str:
    return self.name

  class Meta:
    ordering = ('-created_at',)
