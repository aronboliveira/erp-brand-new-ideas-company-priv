from django.db import models
from django.utils.translation import gettext_lazy as _
from django.contrib.auth import get_user_model
from .._helpers.fields import uuid_def_primary, default_user_creation, VOID
from .._helpers.default_timed import DefaultTimed
from .._helpers.connectors.lead_connected import LeadConnected
from .._helpers.connectors.user_connected import UserConnected
User = get_user_model()
class LeadActivityLog(DefaultTimed, LeadConnected, UserConnected):
  id = models.UUIDField(**uuid_def_primary())
  log_type = models.CharField(
    max_length=20,
    default='move',
    choices=(
      ('move', 'Move'),
      ('add_product', 'Add Product'),
      ('upload_file', 'Upload File'),
      ('update_sources', 'Update Sources'),
      ('create_lead_call', 'Create Lead Call'),
      ('create_lead_email', 'Create Lead Email'),
    )
  )
  remark = models.JSONField(_('Remark Data'), default=dict, **VOID, help_text='Additional data related to the action')
  created_by = default_user_creation('%(class)s_created_by')

  ICON_MAP = {
    'move': 'ti-arrows-maximize',
    'add_product': 'ti-layout-grid-add',
    'upload_file': 'ti-cloud-upload',
    'update_sources': 'ti-brand-open-source',
    'create_lead_call': 'ti-phone-plus',
    'create_lead_email': 'ti-mail',
  }

  class Meta:
    verbose_name = _('Lead Activity Log')
    verbose_name_plural = _('Lead Activity Logs')
    ordering = ['-created_at']

  def __str__(self) -> str:
    return f"{self.user.name} - {self.get_log_type_display()}"

  @property
  def formatted_remark(self) -> str:
    user_name = self.user.name if self.user else ''
    remark_obj = self.remark if isinstance(self.remark, dict) else {}
    match self.log_type:
      case 'upload_file':
        return _("{user} uploaded new file {file}").format(
          user=user_name, file=f"<b>{remark_obj.get('file_name', '')}</b>"
        )
      case 'add_product':
        return _("{user} added new products {title}").format(
          user=user_name, title=f"<b>{remark_obj.get('title', '')}</b>"
        )
      case 'update_sources':
        return _("{user} updated sources").format(user=user_name)
      case 'create_lead_call':
        return _("{user} created new lead call").format(user=user_name)
      case 'create_lead_email':
        return _("{user} created new lead email").format(user=user_name)
      case 'move':
        return _("{user} moved deal {title} from {old} to {new}").format(
          user=user_name,
          title=f"<b>{remark_obj.get('title', '')}</b>",
          old=_(remark_obj.get('old_status', '').title()),
          new=_(remark_obj.get('new_status', '').title())
        )
      case _:
        return str(self.remark) if not isinstance(self.remark, dict) else ''

  @property
  def log_icon(self) -> str:
    return self.ICON_MAP.get(self.log_type, '')
