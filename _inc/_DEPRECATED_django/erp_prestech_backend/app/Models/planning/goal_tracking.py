from django.db import models
from django.core.validators import MinValueValidator, MaxValueValidator
from django.utils import timezone
from .._helpers.connectors.branch_connected import BranchConnected
from .._helpers.describable import Describable
from .._helpers.fields import (default_char_field, defalt_decicmal_10,
                               COMPLETION_CHOICES, VALID_MYSQL_MIN_DATE)
class GoalTracking(Describable, BranchConnected):
  goal_type = models.ForeignKey("GoalType", on_delete=models.DO_NOTHING, related_name="goal_trackings")
  start_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now)
  end_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])
  subject = default_char_field()
  target_achievement = defalt_decicmal_10()
  rating = models.IntegerField(validators=[
    MinValueValidator(0, message='Mininum value for rating is 0'),
    MaxValueValidator(100, message='Maximum value for rating is 100')
  ])
  status = models.CharField(max_length=126, default='pending', choices=COMPLETION_CHOICES)
  
  class Meta:
    db_table = "goal_tracking"
  
  def __str__(self) -> str:
    return f"GoalTracking: {self.subject}"
