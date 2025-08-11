from django.db import models
from django.utils import timezone
from .._helpers.describable import Describable
from .._helpers.connectors.employee_connected import EmployeeConnected
from .._helpers.fields import (default_char_field, default_decimal_12,
                               VALID_MYSQL_MIN_DATE)

class Asset(Describable, EmployeeConnected):
  name = default_char_field()
  purchase_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE],default=timezone.now)
  supported_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])
  amount = default_decimal_12()
  
  def __str__(self) -> str:
    return self.name
  @property
  def employees(self) -> models.QuerySet:
    from ..individuals.employee import Employee
    ids = self.employee_id.split(",") if self.employee_id else []
    return Employee.objects.filter(user_id__in=ids)
  def users(self) -> list:
    from ..individuals.employee import Employee
    from ..individuals.user import User
    if not hasattr(self, "_cached_users"):
      user_list = []
      user_ids = self.employee_id.split(",") if self.employee_id else []
      for uid in user_ids:
        emp = Employee.objects.filter(user_id=uid).first()
        if emp:
          user = User.objects.filter(id=emp.user_id).first()
          if user:
            user_list.append(user)
      self._cached_users = user_list
    return self._cached_users
