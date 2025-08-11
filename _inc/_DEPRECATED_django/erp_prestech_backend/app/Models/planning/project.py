from django.db import models
from django.db.models import Sum
from django.utils import timezone
from decimal import Decimal
from django.contrib.auth import get_user_model
from .._helpers.connectors.customer_connected import CustomerConnected
from .._helpers.describable import Describable
from .._helpers.fields import (default_char_field, default_decimal_12, VALID_MYSQL_MIN_DATE, 
                               VOID, FILE_SIZE_VALIDATOR, VALID_FILE_SIZES,
                               COMPLETION_CHOICES, PRIORITY_COLOR, UUID_VERIFIED)

User = get_user_model()

class Project(Describable, CustomerConnected):
  name = default_char_field(db_index=True)
  start_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE],default=timezone.now)
  end_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])
  project_image = models.ImageField(upload_to='projects/', validators=[FILE_SIZE_VALIDATOR], **VOID)
  image_size = models.IntegerField(validators=VALID_FILE_SIZES, **VOID)
  budget = default_decimal_12()
  estimated_hrs = models.DecimalField(max_digits=6, decimal_places=2, default=Decimal('0.00'))
  project_stage_id = models.CharField(**UUID_VERIFIED, **VOID)
  status = default_char_field(default='pending', choices=COMPLETION_CHOICES)
  status_color = default_char_field(default='#0000', choices=PRIORITY_COLOR)
  tags = default_char_field(voidable=True)
  
  PROJECT_STATUS = {'in_progress':'In Progress','on_hold':'On Hold','complete':'Complete','canceled':'Canceled'}
  STATUS_COLOR = {'on_hold':'warning','in_progress':'info','complete':'success','canceled':'danger'}
  @property
  def img_image(self) -> str:
    from django.core.files.storage import default_storage
    if self.project_image and default_storage.exists(self.project_image.name):
      url = default_storage.url(self.project_image.name)
    else:
      url = default_storage.url('uploads/avatar/default.png')
    return f'src="{url}"'
  def milestones(self):
    return self.milestone_set.all().order_by('-id')
  @classmethod
  def project_hrs(cls, project_id, task_id='') -> dict:
    tasks = cls.project_task(project_id)
    total_hrs = sum(task.estimated_hrs for task in tasks)
    return {'allocated': total_hrs}
  _project_task_cache = {}
  @classmethod
  def project_task(cls, project_id):
    if project_id not in cls._project_task_cache:
      from .project_task import ProjectTask
      tasks = ProjectTask.objects.filter(project_id=project_id)
      cls._project_task_cache[project_id] = tasks
    return cls._project_task_cache[project_id]
  def project_progress(self, last_task_stage_id) -> dict:
    total_tasks = self.tasks.count()
    completed_tasks = self.tasks.filter(stage_id=last_task_stage_id, is_complete=True).count()
    percentage = int((completed_tasks / total_tasks) * 100) if total_tasks > 0 else 0
    from ..utils.utility import Utility
    color = Utility.get_progress_color(percentage)
    return {'color': color, 'percentage': f"{percentage}%"}
  def project_progress_copy(self, user_id) -> dict:
    from .task_stage import TaskStage
    last_stage = TaskStage.objects.filter(created_by=user_id).order_by('-order').first()
    total_tasks = self.tasks.count()
    percentage = int((self.tasks.filter(stage_id=last_stage.id, is_complete=True).count()/ total_tasks)*100) if last_stage and total_tasks>0 else 0
    from ..utils.utility import Utility
    color = Utility.get_progress_color(percentage)
    return {'color': color, 'percentage': f"{percentage}%"}
  def tasks(self):
    return self.projecttask_set.all().order_by('-id')
  users = models.ManyToManyField(User, through='ProjectUser', related_name='projects')
  def client(self):
    return self.client
  def project_attachments(self):
    from .. import TaskFile
    task_ids = self.tasks().values_list('id', flat=True)
    return TaskFile.objects.filter(task_id__in=list(task_ids))
  def activities(self):
    return self.activitylog_set.all().order_by('-id')
  def expense(self):
    return self.expense_set.all().order_by('-id')
  @classmethod
  def get_project_assigned_timesheet_html(cls, projects_timesheet, timesheets, days, project_id=None) -> str:
    context = {'timesheetArray': {}, 'totalDateTimes': {}, 'calculatedtotaltaskdatetime': 0, 'days': days, 'allProjects': project_id=='0'}
    from django.template.loader import render_to_string
    return render_to_string('projects/timesheets/week.html', context)
  def tasksections(self):
    return self.milestone_set.all().order_by('-id')
  @classmethod
  def get_assigned_project_tasks(cls, project_id=None, stage_id=None, filterdata=None):
    from .project_task import ProjectTask
    from django.contrib.auth import get_user_model
    current_user = get_user_model().objects.first()
    tasks = ProjectTask.objects.all()
    if project_id:
      tasks = tasks.filter(project_id=project_id)
    assigned_ids = current_user.projects.values_list('projecttask__id', flat=True)
    tasks = tasks.filter(id__in=list(assigned_ids))
    if stage_id:
      tasks = tasks.filter(stage_id=stage_id)
    return tasks
  def timesheets(self):
    return self.timesheet_set.all().order_by('-id')
  @classmethod
  def delete_project(cls, project_id) -> bool:
    from .project_task import ProjectTask
    project = cls.objects.filter(uuid=project_id).first()
    if project:
      from django.core.files.storage import default_storage
      if project.project_image and default_storage.exists(project.project_image.name):
        default_storage.delete(project.project_image.name)
      project.milestone_set.all().delete()
      project.activitylog_set.all().delete()
      project.timesheet_set.all().delete()
      project.users.clear()
      task_ids = project.projecttask_set.values_list('id', flat=True)
      if task_ids:
        ProjectTask.delete_task(list(task_ids))
      project.delete()
      return True
    return False
  def label(self):
    from ..shapes.label import Label
    return Label.objects.filter(id=self.status).first()
  def project_user(self):
    return self.projectuser_set.all()
  def count_task(self, user_id=0) -> str:
    current_user = User.objects.first()
    if current_user.check_project(self.uuid) == 'Owner':
      complete = self.projecttask_set.filter(is_complete=True).count()
      total = self.projecttask_set.count()
    else:
      complete = self.projecttask_set.filter(is_complete=True, assign_to__icontains=str(user_id)).count()
      total = self.projecttask_set.filter(assign_to__icontains=str(user_id)).count()
    return f"{complete}/{total}"
  @classmethod
  def get_project_status(cls) -> dict:
    current_user = User.objects.first()
    project_data = {}
    if current_user.type == 'company':
      on_going = cls.objects.filter(status='in_progress', created_by=current_user.id).count()
      on_hold = cls.objects.filter(status='on_hold', created_by=current_user.id).count()
      completed = cls.objects.filter(status='complete', created_by=current_user.id).count()
      canceled = cls.objects.filter(status='canceled', created_by=current_user.id).count()
      total = on_going + on_hold + completed
      project_data['on_going'] = (on_going / total * 100) if total else 0
      project_data['on_hold'] = (on_hold / total * 100) if total else 0
      project_data['completed'] = (completed / total * 100) if total else 0
    elif current_user.type == 'client':
      on_going = cls.objects.filter(status='in_progress', client_id=current_user.id).count()
      on_hold = cls.objects.filter(status='on_hold', client_id=current_user.id).count()
      completed = cls.objects.filter(status='complete', client_id=current_user.id).count()
      canceled = cls.objects.filter(status='canceled', client_id=current_user.id).count()
      total = on_going + on_hold + completed + canceled
      project_data['on_going'] = int(on_going / total * 100) if total else 0
      project_data['on_hold'] = int(on_hold / total * 100) if total else 0
      project_data['completed'] = int(completed / total * 100) if total else 0
      project_data['canceled'] = int(canceled / total * 100) if total else 0
    else:
      from .. import ProjectUser
      qs = ProjectUser.objects.filter(user_id=current_user.id).select_related('project')
      on_going = qs.filter(project__status='in_progress').count()
      on_hold = qs.filter(project__status='on_hold').count()
      completed = qs.filter(project__status='complete').count()
      canceled = qs.filter(project__status='canceled').count()
      total = on_going + on_hold + completed + canceled
      project_data['on_going'] = (on_going / total * 100) if total else 0
      project_data['on_hold'] = (on_hold / total * 100) if total else 0
      project_data['completed'] = (completed / total * 100) if total else 0
      project_data['canceled'] = (canceled / total * 100) if total else 0
    return project_data
  def project_last_stage(self):
    from .task_stage import TaskStage
    current_user = User.objects.first()
    return TaskStage.objects.filter(created_by=current_user.creator_id()).order_by('-order').first()
  def project_total_task(self, project_id) -> int:
    from .project_task import ProjectTask
    return ProjectTask.objects.filter(project_id=project_id).count()
  def project_complete_task(self, project_id, last_stage_id) -> int:
    from .project_task import ProjectTask
    return ProjectTask.objects.filter(project_id=project_id, stage_id=last_stage_id).count()
  def project_milestone_progress(self) -> dict:
    from ..planning.milestone import Milestone
    total_milestones = Milestone.objects.filter(project_id=self.uuid).count()
    total_progress_sum = Milestone.objects.filter(project_id=self.uuid).aggregate(total=Sum('progress'))['total'] or 0
    if total_milestones > 0:
      percentage = int(total_progress_sum / total_milestones)
      return {'percentage': f"{percentage}%"}
    else:
      return {'percentage': 0}
  def __str__(self) -> str:
    return self.name
