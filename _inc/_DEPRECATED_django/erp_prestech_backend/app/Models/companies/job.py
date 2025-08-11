from django.db import models
from .._helpers.connectors.branch_connected import BranchConnected
from .._helpers.describable import Describable
from .._helpers.fields import (default_char_field, default_text_field,
                               VALID_MYSQL_MIN_DATE, VOID)
from ..shapes.custom_question import CustomQuestion
from django.contrib.auth import get_user_model
from django.utils import timezone
User = get_user_model()

class Job(Describable, BranchConnected):
  title = default_char_field(db_index=True)
  requirement = default_text_field(default='No requirement was defined')
  category = models.ForeignKey('JobCategory', on_delete=models.CASCADE, related_name='jobs')
  skill = default_char_field(voidable=True)
  position = default_char_field(voidable=True)
  start_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now, db_index=True)
  end_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], db_index=True)
  status = models.CharField(max_length=50, default='draft', choices=[('draft', 'Draft'), ('open', 'Open'), ('closed', 'Closed')])
  visibility = models.CharField(max_length=50, default='public', choices=[('public', 'Public'), ('protected', 'Protected'), ('private', 'Private')])
  applicant = models.CharField(max_length=127)
  code = models.CharField(max_length=100, **VOID)
  custom_question = default_char_field(voidable=True)

  STATUS_CHOICES = (
    ('active', 'Active'),
    ('in_active', 'In Active'),
  )

  @property
  def questions(self):
    if self.custom_question:
      ids = [int(x) for x in self.custom_question.split(',') if x.strip().isdigit()]
      return CustomQuestion.objects.filter(id__in=ids)
    return CustomQuestion.objects.none()

  def __str__(self) -> str:
    return self.title
