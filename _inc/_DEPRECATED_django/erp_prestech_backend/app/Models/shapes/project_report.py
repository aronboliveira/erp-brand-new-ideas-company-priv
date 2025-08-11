import logging
from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation
class ProjectReport(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary)
  created_by = default_user_creation('%(class)s_created_by')
  class Meta:
    db_table = 'project_report'
    ordering = ['-created-at']
  @staticmethod
  def assign_user(user: str) -> str:
    from ..individuals.user import User
    try:
      assign_arr = user.split(',')
      user_name = ""
      for assign in assign_arr:
        assign_user = User.objects.filter(id=assign).first()
        user_name += assign_user.name + "," if assign_user else ""
      return user_name
    except Exception as e:
      logging.error(f"Failed to assign user in ProjectReport: {e}")
      return ""

  @staticmethod
  def milestone(id: str) -> str:
    from ..planning.milestone import Milestone
    try:
      milestone_obj = Milestone.objects.filter(id=id).first()
      return milestone_obj.title if milestone_obj else ""
    except Exception as e:
      logging.error(f"Failed to retrieve milestone in ProjectReport: {e}")
      return ""

  @staticmethod
  def status(id: str) -> str:
    from ..planning.task_stage import TaskStage
    try:
      status_obj = TaskStage.objects.filter(id=id).first()
      return status_obj.name if status_obj else ""
    except Exception as e:
      logging.error(f"Failed to retrieve status in ProjectReport: {e}")
      return ""