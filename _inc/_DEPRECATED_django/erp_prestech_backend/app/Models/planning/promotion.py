from django.db import models
from .._helpers.describable import Describable
from .._helpers.fields import default_char_field, VALID_MYSQL_MIN_DATE
from .._helpers.connectors.employee_connected import EmployeeConnected
class Promotion(Describable, EmployeeConnected):
  designation = models.ForeignKey('Designation', on_delete=models.CASCADE,related_name='promotions')
  promotion_title = default_char_field()
  promotion_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])

  class Meta:
    db_table = 'promotion'
    ordering = ['-created_at']
