from django.db import models
import logging
from typing import Optional, TYPE_CHECKING

if TYPE_CHECKING:
    from ...companies.department import Department

logger = logging.getLogger(__name__)

class DepartmentConnected(models.Model):
    department_related_name: str = '%(class)s_department'

    class Meta:
        abstract = True

    def __init_subclass__(cls, **kwargs) -> None:
        super().__init_subclass__(**kwargs)
        cls.add_to_class('department', models.ForeignKey(
            'Department',
            on_delete=models.SET_NULL,
            related_name=getattr(cls, 'department_related_name', None),
            db_index=True
        ))

    def get_department_by_id(self, dept_id: str) -> Optional['Department']:
        """Fetch Department by ID from database."""
        try:
            from ...companies.department import Department
            qs = Department.objects.filter(id=dept_id)
            if not qs.exists():
                logger.warning(f'No Department found with ID {dept_id} in {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple Departments ({qs.count()}) with ID {dept_id} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching Department by ID {dept_id}: {str(e)}', exc_info=True)
            return None

    def get_department_by_name(self, name: str) -> Optional['Department']:
        """Fetch Department by exact name match."""
        try:
            from ...companies.department import Department
            qs = Department.objects.filter(name=name)
            if not qs.exists():
                logger.warning(f'No Department found with name {name} in {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple Departments ({qs.count()}) with name {name} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching Department by name {name}: {str(e)}', exc_info=True)
            return None