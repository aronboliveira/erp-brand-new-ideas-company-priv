from .._helpers.connectors.project_connected import ProjectConnected
from .._helpers.describable import Describable
from .._helpers.fields import default_char_field
class Milestone(Describable, ProjectConnected):
  title = default_char_field()
  status = default_char_field(
      max_length = 63,
      choices=[
          ('pending', 'Pending'),
          ('in_progress', 'In Progress'),
          ('completed', 'Completed'),
          ('delayed', 'Delayed')
      ],
      default='pending'
  )
  
  def __str__(self) -> str:
    return self.title
