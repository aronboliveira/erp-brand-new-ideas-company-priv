from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, default_char_field
from ..contact.contact import Contact
from ..companies.company import Company
from ..individuals.hrm_employee import HrmEmployee

class Activity(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  module_id = models.UUIDField(**uuid_def_primary(pk=False))
  module_type = models.CharField(max_length=100, db_index=True)
  log_type = default_char_field()
  created_by = default_user_creation('%(class)s_created_by')

  @staticmethod
  def get_activity(module_type: str, module_id: str) -> dict:
    result = {'name': '-'}
    if module_type == 'contact':
      contact = Contact.objects.filter(id=module_id).order_by('-id').first()
      result = {'name': contact.name} if contact else result
    elif module_type == 'company':
      company = Company.objects.filter(id=module_id).order_by('-id').first()
      result = {'name': company.name} if company else result
    elif module_type == 'Employee':
      employee = HrmEmployee.objects.filter(id=module_id).order_by('-id').first()
      result = {'name': f"{employee.first_name} {employee.last_name}"} if employee else result
    return result

  def log_icon(self) -> str:
    icon_map = {
      'Invite User': 'ti-user',
      'User Assigned to the Task': 'ti-user-check',
      'User Removed from the Task': 'ti-user-x',
      'Upload File': 'ti-cloud-upload',
      'Create Milestone': 'ti-crop',
      'Create Bug': 'ti-bug',
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
    return icon_map.get(self.log_type, '')

  class Meta:
    ordering = ('-created_at',)
