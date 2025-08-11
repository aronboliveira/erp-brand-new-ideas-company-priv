from django.db import models
from django.conf import settings
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, default_char_field

class WebhookSetting(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  MODULE_CHOICES = [
    ('new lead', 'New Lead'),
    ('lead to deal conversion', 'Lead to Deal Conversion'),
    ('new project', 'New Project'),
    ('task stage updated', 'Task Stage Updated'),
    ('new deal', 'New Deal'),
    ('new contract', 'New Contract'),
    ('new task', 'New Task'),
    ('new task comment', 'New Task Comment'),
    ('new monthly payslip', 'New Monthly Payslip'),
    ('new announcement', 'New Announcement'),
    ('new support ticket', 'New Support Ticket'),
    ('new meeting', 'New Meeting'),
    ('new award', 'New Award'),
    ('new holiday', 'New Holiday'),
    ('new event', 'New Event'),
    ('new company policy', 'New Company Policy'),
    ('new invoice', 'New Invoice'),
    ('new bill', 'New Bill'),
    ('new budget', 'New Budget'),
    ('new revenue', 'New Revenue'),
    ('new invoice payment', 'New Invoice Payment'),
  ]
  METHOD_CHOICES = [
    ('get', 'GET'),
    ('post', 'POST'),
  ]
  module = models.CharField(
    max_length=255,
    default='new lead',
    choices=MODULE_CHOICES,
    help_text="Module event that triggers the webhook."
  )
  url = models.URLField(max_length=1024, help_text="Endpoint to which the webhook will send data.")
  method = models.CharField(
    max_length=10,
    default='get',
    choices=METHOD_CHOICES,
    default='post',
    help_text="HTTP method to use for the webhook."
  )
  created_by = default_user_creation('%(class)s_created_by')

  def __str__(self) -> str:
    return f"{self.module} - {self.method}"

  class Meta:
    ordering = ('-created_at',)
