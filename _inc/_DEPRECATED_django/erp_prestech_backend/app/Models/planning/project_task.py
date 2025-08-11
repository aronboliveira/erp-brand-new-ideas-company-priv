from django.db import models
from django.utils import timezone
from decimal import Decimal
from ..activity.activity_log import ActivityLog
from .task_checklist import TaskChecklist
from .task_comment import TaskComment
from .task_file import TaskFile
from ..shapes.timesheet import Timesheet
from ..utils.utility import Utility
from .._helpers.connectors.project_connected import ProjectConnected
from .._helpers.describable import Describable
from .._helpers.fields import (default_char_field, PRIORITY_CHOICES, PRIORITY_COLOR, 
                               VALID_MYSQL_MIN_DATE, VOID)
from typing import Optional, List, Dict, Any, Union, TYPE_CHECKING
if TYPE_CHECKING:
  from ..individuals.user import User
  from ..planning.milestone import Milestone
  from ..planning.project import Project
  from .task_stage import TaskStage
class ProjectTask(Describable, ProjectConnected):
    name = default_char_field()
    estimated_hrs = models.DecimalField(max_digits=6, decimal_places=2, default=Decimal("0.00"))
    start_date = models.DateField(**VOID,validators=[VALID_MYSQL_MIN_DATE],default=timezone.now)
    end_date = models.DateField(**VOID,validators=[VALID_MYSQL_MIN_DATE])
    priority = default_char_field(choices=PRIORITY_CHOICES, voidable=True)
    priority_color = default_char_field(default='#0000', choices=PRIORITY_COLOR, voidable=True)
    assign_to = default_char_field(voidable=True)  # Stores comma-separated user IDs
    milestone = models.ForeignKey("Milestone", on_delete=models.SET_NULL, **VOID, related_name="tasks")
    stage = models.ForeignKey("TaskStage", on_delete=models.SET_NULL, **VOID, related_name="tasks")
    order = models.PositiveIntegerField(default=0)
    is_favourite = models.BooleanField(default=False)
    is_complete = models.BooleanField(default=False)
    marked_at = models.DateTimeField(auto_now_add=True, **VOID)
    progress = models.IntegerField(default=0)

    def __str__(self) -> str:
        return self.name

    def milestone_obj(self) -> Optional[Milestone]:
        """Return the related Milestone object."""
        return self.milestone

    def assigned_users(self) -> models.QuerySet:
        """
        Returns a queryset of User objects whose IDs appear in the assign_to field.
        """
        if not self.assign_to:
            return User.objects.none()
        try:
            user_ids = [int(uid.strip()) for uid in self.assign_to.split(",") if uid.strip()]
        except ValueError:
            user_ids = []
        return User.objects.filter(id__in=user_ids)

    @property
    def task_user(self) -> Optional[User]:
        """
        Returns the first assigned user (if any) – mimicking a one-to-one relation on assign_to.
        """
        users = self.assigned_users()
        return users.first() if users.exists() else None

    def project_obj(self) -> Project:
        """Return the related Project object."""
        return self.project

    def stage_obj(self) -> Optional[TaskStage]:
        """Return the related TaskStage object."""
        return self.stage

    def task_progress(self, project_instance: Project) -> Dict[str, str]:
        """
        Calculate the task progress percentage based on checklist completion.
        Uses Utility.get_progress_color to derive a progress color.
        """
        total_checklist = self.checklist().count()
        completed_checklist = self.checklist().filter(status=1).count()
        percentage = int((completed_checklist / total_checklist) * 100) if total_checklist > 0 else 0
        color = Utility.get_progress_color(percentage)
        return {"color": color, "percentage": f"{percentage}%"}

    def checklist(self) -> models.QuerySet:
        """Return related TaskChecklist objects ordered descending by id."""
        # Explicitly using the TaskChecklist model:
        return TaskChecklist.objects.filter(task=self).order_by("-id")

    def task_files(self) -> models.QuerySet:
        """Return related TaskFile objects ordered descending by id."""
        return TaskFile.objects.filter(task=self).order_by("-id")

    def comments(self) -> models.QuerySet:
        """Return related TaskComment objects ordered descending by id."""
        return TaskComment.objects.filter(task=self).order_by("-id")

    def timesheets(self) -> models.QuerySet:
        """Return related Timesheet objects ordered descending by id."""
        return Timesheet.objects.filter(task=self).order_by("-id")

    def count_task_checklist(self) -> str:
        """Returns a string 'completed/total' for checklist items."""
        total = TaskChecklist.objects.filter(task=self).count()
        completed = TaskChecklist.objects.filter(task=self, status=1).count()
        return f"{completed}/{total}"

    def add_checklist_item(self, title: str, status: int, created_by: User) -> TaskChecklist:
        """
        Create and return a new TaskChecklist item associated with this task.
        """
        checklist_item = TaskChecklist.objects.create(
            task=self,
            title=title,
            status=status,
            created_by=created_by
        )
        return checklist_item

    def add_comment(self, content: str, created_by: User) -> TaskComment:
        """
        Create and return a new TaskComment for this task.
        """
        comment = TaskComment.objects.create(
            task=self,
            content=content,
            created_by=created_by
        )
        return comment

    def add_file(self, file_obj: Any, filename: Optional[str], created_by: User) -> TaskFile:
        """
        Create and return a new TaskFile for this task.
        'file_obj' is the uploaded file and 'filename' is an optional forced filename.
        """
        task_file = TaskFile.objects.create(
            task=self,
            file=file_obj,  # Assumes TaskFile has a FileField named "file"
            name=filename if filename else file_obj.name,
            created_by=created_by
        )
        return task_file

    def add_timesheet(self, hours: Decimal, description: str, created_by: User) -> Timesheet:
        """
        Create and return a new Timesheet entry for this task.
        """
        timesheet = Timesheet.objects.create(
            task=self,
            hours=hours,
            description=description,
            created_by=created_by
        )
        return timesheet

    @classmethod
    def delete_task(cls, task_ids: List[Union[str, Any]]) -> bool:
        """
        Deletes tasks with the given IDs and all their related attachments,
        timesheets, checklists, and comments.
        Uses Utility.check_file_exists_and_delete to remove file attachments.
        """
        for task_id in task_ids:
            try:
                task = cls.objects.get(id=task_id)
            except cls.DoesNotExist:
                continue
            attachments = TaskFile.objects.filter(task=task)
            file_list = [att.file.path for att in attachments if att.file]
            Utility.check_file_exists_and_delete(file_list)
            attachments.delete()
            Timesheet.objects.filter(task=task).delete()
            TaskChecklist.objects.filter(task=task).delete()
            TaskComment.objects.filter(task=task).delete()
            task.delete()
        return True

    def activity_log(self) -> models.QuerySet:
        """
        Returns ActivityLog records for the current user, project, and this task.
        For illustration purposes, current user is retrieved using User.objects.first() – replace this with your auth logic.
        """
        current_user = User.objects.first()  # Replace with proper authentication logic
        return ActivityLog.objects.filter(
            user_id=current_user.id,
            project_id=self.project.id,
            task_id=self.id
        )

    @classmethod
    def get_all_sectioned_task_list(
        cls, request: Any, project_instance: Project, filterdata: Optional[Dict[str, Any]] = None, not_task_ids: Optional[List[Any]] = None
    ) -> List[Dict[str, Any]]:
        """
        Returns a list of tasks grouped by milestone sections.
        Builds a nested structure based on project task sections and additional task info.
        Assumes that project_instance.tasks() and project_instance.tasksections() are implemented.
        """
        task_array: List[Dict[str, Any]] = []
        # Tasks with no milestone section
        tasks_without_section = project_instance.tasks().filter(milestone__isnull=True)
        if tasks_without_section.exists():
            section = {
                "section_id": "0",
                "section_name": "",
                "sectionsClass": "active",
                "sections": [task.to_dict() for task in tasks_without_section]
            }
            task_array.append(section)
        # For each milestone section
        milestone_dict = project_instance.tasksections().values("id", "title")
        for ms in milestone_dict:
            tasks = project_instance.tasks().filter(milestone_id=ms["id"])
            section = {
                "section_id": ms["id"],
                "section_name": ms["title"],
                "sectionsClass": "active",
                "sections": [task.to_dict() for task in tasks]
            }
            task_array.append(section)
        return task_array

    def to_dict(self) -> Dict[str, Any]:
        """
        Converts this task instance to a dictionary representation.
        """
        return {
            "id": str(self.id),
            "name": self.name,
            "description": self.description,
            "estimated_hrs": str(self.estimated_hrs),
            "start_date": self.start_date.isoformat() if self.start_date else None,
            "end_date": self.end_date.isoformat() if self.end_date else None,
            "priority": self.priority,
            "priority_color": self.priority_color,
            "assign_to": self.assign_to,
            "project_id": str(self.project.id) if self.project else None,
            "milestone_id": str(self.milestone.id) if self.milestone else None,
            "stage_id": str(self.stage.id) if self.stage else None,
            "order": self.order,
            "is_favourite": self.is_favourite,
            "is_complete": self.is_complete,
            "marked_at": self.marked_at.isoformat() if self.marked_at else None,
            "progress": self.progress,
            "created_at": self.created_at.isoformat() if self.created_at else None,
            "updated_at": self.updated_at.isoformat() if self.updated_at else None,
        }
