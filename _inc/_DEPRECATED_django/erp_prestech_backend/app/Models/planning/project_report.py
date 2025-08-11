from .._helpers.connectors.project_connected import ProjectConnected
from .._helpers.describable import Describable
from ..individuals.user import User
from .milestone import Milestone
from .task_stage import TaskStage
class ProjectReport(Describable, ProjectConnected):
  
  class Meta:
    abstract = True
    
  @staticmethod
  def assign_user(user_ids: str) -> str:
    assign_arr = [uid.strip() for uid in user_ids.split(',') if uid.strip()]
    user_names = []
    for uid in assign_arr:
      try:
        user = User.objects.get(uuid=uid)
        user_names.append(user.name)
      except User.DoesNotExist:
        continue
    return ",".join(user_names)
  @staticmethod
  def milestone(milestone_id: str) -> str:
    try:
      milestone_obj = Milestone.objects.get(uuid=milestone_id)
      return milestone_obj.title
    except Milestone.DoesNotExist:
      return ""
  @staticmethod
  def status(task_stage_id: str) -> str:
    try:
      status_obj = TaskStage.objects.get(uuid=task_stage_id)
      return status_obj.name
    except TaskStage.DoesNotExist:
      return ""
