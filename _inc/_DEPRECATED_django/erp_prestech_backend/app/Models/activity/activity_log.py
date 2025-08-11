from django.db import models
from django.utils.translation import gettext as _
import json
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_user_creation, default_char_field,
                               default_text_field)
from .._helpers.connectors.deal_connected import DealConnected
from .._helpers.connectors.project_connected import ProjectConnected
from .._helpers.connectors.task_connected import TaskConnected
from .._helpers.connectors.user_connected import UserConnected
class ActivityLog(DefaultTimed, DealConnected, ProjectConnected, TaskConnected, UserConnected):
  id = models.UUIDField(**uuid_def_primary())
  log_type = default_char_field(voidable=False)
  remark = default_text_field(default='No remark written')
  created_by = default_user_creation('%(class)s_created_by')
  _user_data = None  # Class-level cache

  def get_remark(self) -> str:
    if ActivityLog._user_data is None:
      ActivityLog._user_data = self._fetch_get_remark()
    return ActivityLog._user_data

  def _fetch_get_remark(self) -> str:
    try:
      remark_obj = json.loads(self.remark)
    except json.JSONDecodeError as error:
      print(f"Failed to load JSON for remark: {error}")
      return self.remark
    user_name = self.user.name if self.user else ''
    mapping = {
      'Invite User': lambda: f"{user_name} {_('has invited')} <b>{remark_obj.get('title')}</b>",
      'User Assigned to the Task': lambda: f"{user_name} {_('has assigned task')} <b>{remark_obj.get('task_name')}</b> {_('to')} <b>{remark_obj.get('member_name')}</b>",
      'User Removed from the Task': lambda: f"{user_name} {_('has removed')} <b>{remark_obj.get('member_name')}</b> {_('from task')} <b>{remark_obj.get('task_name')}</b>",
      'Upload File': lambda: f"{user_name} {_('Upload new file')} <b>{remark_obj.get('file_name')}</b>",
      'Create Bug': lambda: f"{user_name} {_('Created new bug')} <b>{remark_obj.get('title')}</b>",
      'Create Milestone': lambda: f"{user_name} {_('Create new milestone')} <b>{remark_obj.get('title')}</b>",
      'Create Task': lambda: f"{user_name} {_('Create new Task')} <b>{remark_obj.get('title')}</b>",
      'Move Task': lambda: f"{user_name} {_('Moved the Task')} <b>{remark_obj.get('title')}</b> {_('from')} {remark_obj.get('old_stage')} {_('to')} {remark_obj.get('new_stage')}",
      'Create Expense': lambda: f"{user_name} {_('Create new Expense')} <b>{remark_obj.get('title')}</b>",
      'Add Product': lambda: f"{user_name} {_('Add new Products')} <b>{remark_obj.get('title')}</b>",
      'Update Sources': lambda: f"{user_name} {_('Update Sources')}",
      'Create Deal Call': lambda: f"{user_name} {_('Create new Deal Call')}",
      'Create Deal Email': lambda: f"{user_name} {_('Create new Deal Email')}",
      'Move': lambda: f"{user_name} {_('Moved the deal')} <b>{remark_obj.get('title')}</b> {_('from')} {remark_obj.get('old_status')} {_('to')} {remark_obj.get('new_status')}",
    }
    return mapping.get(self.log_type, lambda: self.remark)()

  def log_icon(self) -> str:
    icons = {
      'Invite User': 'ti-user',
      'User Assigned to the Task': 'ti-user-check',
      'User Removed from the Task': 'ti-user-x',
      'Upload File': 'ti-cloud-upload',
      'Create Bug': 'ti-bug',
      'Create Milestone': 'ti-crop',
      'Create Task': 'ti-list',
      'Move Task': 'ti-command',
      'Create Expense': 'ti-clipboard-list',
      'Move': 'ti-arrows-maximize',
      'Add Product': 'ti-shopping-cart-plus',
      'Update Sources': 'ti-brand-open-source',
      'Create Deal Call': 'ti-phone-plus',
      'Create Deal Email': 'ti-record-mail',
      'Create Invoice': 'ti-file-plus',
      'Add Contact': 'ti-notebook',
    }
    return icons.get(self.log_type, '')
  
  class Meta:
    ordering = ('-created_at',)
