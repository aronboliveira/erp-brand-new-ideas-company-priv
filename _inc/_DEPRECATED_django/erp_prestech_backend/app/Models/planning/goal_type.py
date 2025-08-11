from django.db import models
from .._helpers.describable import Describable
from .._helpers.fields import default_char_field

class GoalType(Describable):
  name = default_char_field()
  
  class Meta:
    db_table = "goal_type"
  
  def __str__(self) -> str:
    return self.name
