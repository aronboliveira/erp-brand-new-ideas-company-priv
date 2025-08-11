from django.db import models
import logging
from typing import Optional, TYPE_CHECKING

if TYPE_CHECKING:
    from ...individuals.employee import Employee

logger = logging.getLogger(__name__)

class EmployeeConnected(models.Model):
    employee_related_name: str = '%(class)s_employee'

    class Meta:
        abstract = True

    def __init_subclass__(cls, **kwargs) -> None:
        super().__init_subclass__(**kwargs)
        cls.add_to_class('employee', models.ForeignKey(
            'Employee',
            on_delete=models.CASCADE,
            related_name=getattr(cls, 'employee_related_name', None),
            db_index=True
        ))

    def get_employee_by_id(self, emp_id: str) -> Optional['Employee']:
        """Fetch Employee by ID from database."""
        try:
            from ...individuals.employee import Employee
            qs = Employee.objects.filter(id=emp_id)
            if not qs.exists():
                logger.warning(f'No Employee found with ID {emp_id} in {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple Employees ({qs.count()}) with ID {emp_id} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching Employee by ID {emp_id}: {str(e)}', exc_info=True)
            return None

    def get_employee_by_name(self, name: str) -> Optional['Employee']:
        """Fetch Employee by exact name match."""
        try:
            from ...individuals.employee import Employee
            qs = Employee.objects.filter(name=name)
            if not qs.exists():
                logger.warning(f'No Employee found with name {name} in {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple Employees ({qs.count()}) with name {name} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching Employee by name {name}: {str(e)}', exc_info=True)
            return None