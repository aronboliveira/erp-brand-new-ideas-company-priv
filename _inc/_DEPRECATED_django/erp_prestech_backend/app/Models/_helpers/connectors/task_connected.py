from django.db import models
import logging
from typing import Optional, TYPE_CHECKING

if TYPE_CHECKING:
    from ...planning.task import Task

logger = logging.getLogger(__name__)

class TaskConnected(models.Model):
    task_related_name: str = '%(class)s_task'

    class Meta:
        abstract = True

    def __init_subclass__(cls, **kwargs) -> None:
        super().__init_subclass__(**kwargs)
        cls.add_to_class('task', models.ForeignKey(
            'Task',
            on_delete=models.SET_NULL,
            related_name=getattr(cls, 'task_related_name', None),
            db_index=True
        ))

    def get_task_by_id(self, task_id: str) -> Optional['Task']:
        """Fetch Task by ID from database."""
        try:
            from ...planning.task import Task
            qs = Task.objects.filter(id=task_id)
            if not qs.exists():
                logger.warning(f'No Task found with ID {task_id} in {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple Tasks ({qs.count()}) with ID {task_id} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching Task by ID {task_id}: {str(e)}', exc_info=True)
            return None

    def get_task_by_title(self, title: str) -> Optional['Task']:
        """Fetch Task by exact title match."""
        try:
            from ...planning.task import Task
            qs = Task.objects.filter(title=title)
            if not qs.exists():
                logger.warning(f'No Task found with title {title} in {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple Tasks ({qs.count()}) with title {title} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching Task by title {title}: {str(e)}', exc_info=True)
            return None