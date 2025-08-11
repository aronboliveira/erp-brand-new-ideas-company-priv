import logging
from django.db import models
from django.utils import timezone
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_char_field, default_user_creation, 
                               defalt_decicmal_10, default_text_field, VALID_MYSQL_MIN_DATE)
from .._helpers.connectors.employee_connected import EmployeeConnected
from typing import Optional, TYPE_CHECKING
if TYPE_CHECKING:
  from ..individuals.employee import Employee
  from .loan_option import LoanOption
  
class Loan(DefaultTimed, EmployeeConnected):
  id = models.UUIDField(**uuid_def_primary())
  loan_option = models.ForeignKey('LoanOption', on_delete=models.CASCADE, db_index=True)
  title = default_char_field()
  amount = defalt_decicmal_10()
  start_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now)
  end_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])
  reason = default_text_field(default='No reason was given')
  created_by = default_user_creation('%(class)s_created_by')
  LOAN_TYPES = {
    'fixed': 'Fixed',
    'percentage': 'Percentage'
  }

  class Meta:
    db_table = 'loan'
    ordering = ('-created_at',)

  def get_employee(self) -> Optional['Employee']:
    try:
      return self.employee
    except Exception as e:
      logging.error(f"Failed to get employee for Loan: {e}")
      return None

  def get_loan_option(self) -> Optional['LoanOption']:
    try:
      return self.loan_option
    except Exception as e:
      logging.error(f"Failed to get loan option for Loan: {e}")
      return None
