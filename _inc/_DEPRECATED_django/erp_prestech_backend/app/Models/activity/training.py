from django.db import models
from .._helpers.connectors.branch_connected import BranchConnected
from .._helpers.describable import Describable
from .._helpers.fields import defalt_decicmal_10, VALID_MYSQL_MIN_DATE, COMPLETION_CHOICES
from .._helpers.connectors.employee_connected import EmployeeConnected
class Training(Describable, BranchConnected, EmployeeConnected):
  trainer_option = models.CharField(
    max_length=10,
    default="Internal",
    choices=[("Internal", "Internal"), ("External", "External")],
  )
  training_type = models.ForeignKey('TrainingType',
    on_delete=models.SET_NULL,
    null=True,
    related_name="trainings"
  )
  trainer = models.ForeignKey('Trainer',
    on_delete=models.SET_NULL,
    null=True,
    related_name="trainings"
  )
  status= models.CharField(max_length=63, default='pending', choices=COMPLETION_CHOICES)
  training_cost = defalt_decicmal_10()
  start_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])
  end_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])

  # Static option maps
  OPTIONS = ["Internal", "External"]
  PERFORMANCE = ["Not Concluded", "Satisfactory", "Average", "Poor", "Excellent"]

  class Meta:
    verbose_name = "Training"
    verbose_name_plural = "Trainings"
    ordering = ["-uuid"]

  def __str__(self) -> str:
    return f"Training #{self.uuid}"
