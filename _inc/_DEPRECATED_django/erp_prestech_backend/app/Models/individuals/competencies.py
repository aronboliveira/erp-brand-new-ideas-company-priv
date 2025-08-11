from django.db import models
from .._helpers.describable import Describable
from .._helpers.fields import default_char_field
class Competencies(Describable):
    name = default_char_field()
    type = models.ForeignKey('PerformanceType', on_delete=models.CASCADE, 
                             related_name='types', db_index=True)
    category = models.CharField(
        max_length=50,
        choices=[
            ('technical', 'Technical'),
            ('administrative', 'Administrative'),
            ('hr', 'HR'),
            ('design', 'Design'),
            ('sales', 'Sales'),
            ('marketing', 'Marketing'),
            ('support', 'Support'),
            ('qa', 'Quality Assurance'),
            ('management', 'Management')
        ],
        default='technical'
    )
    
    def __str__(self) -> str:
        return self.name
    
    @property
    def performance(self):
        return self.type_
