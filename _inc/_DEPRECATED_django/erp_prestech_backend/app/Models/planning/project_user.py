from django.db import models
from .._helpers.connectors.project_connected import ProjectConnected
from .._helpers.connectors.user_connected import UserConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation
class ProjectUser(DefaultTimed, ProjectConnected, UserConnected):
  id = models.UUIDField(**uuid_def_primary())
  invited_by = models.ForeignKey('User', on_delete=models.SET_NULL, null=True, blank=True, related_name='project_user_invites')
  created_by = default_user_creation('%(class)s_created_by')
  
  def __str__(self) -> str:
    return f"ProjectUser (project: {self.project.uuid}, user: {self.user.uuid})"
  
  @property
  def project_users(self):
    from ..individuals.user import User
    return User.objects.filter(uuid=self.user.uuid).first()
