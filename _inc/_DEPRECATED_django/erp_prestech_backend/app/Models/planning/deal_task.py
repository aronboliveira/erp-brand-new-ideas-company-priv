from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_char_field, VALID_MYSQL_MIN_DATE
class DealTask(DefaultTimed):
  PRIORITIES = ((1, 'Low'), (2, 'Medium'), (3, 'High'))
  STATUS_CHOICES = ((0, 'On Going'), (1, 'Completed'))
  id = models.ForeignKey('Deal', on_delete=models.CASCADE, related_name='deal_tasks', db_column='deal_id')
  name = default_char_field()
  date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])
  time = models.TimeField()
  priority = models.PositiveSmallIntegerField(default=1, choices=PRIORITIES, default=2)
  status = models.PositiveSmallIntegerField(default=0, choices=STATUS_CHOICES, default=0)
  class Meta:
    verbose_name = "Deal Task"
    verbose_name_plural = "Deal Tasks"
    ordering = ['-date', 'time']
  def __str__(self) -> str:
    return f"{self.name} ({self.get_priority_display()})"
