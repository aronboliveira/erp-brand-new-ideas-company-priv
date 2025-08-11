from django.db import models
import logging
from typing import Optional, TYPE_CHECKING

if TYPE_CHECKING:
    from ...planning.project import Project

logger = logging.getLogger(__name__)

class ProjectConnected(models.Model):
    project_related_name: str = '%(class)s_project'

    class Meta:
        abstract = True

    def __init_subclass__(cls, **kwargs) -> None:
        super().__init_subclass__(**kwargs)
        cls.add_to_class('project', models.ForeignKey(
            'Project',
            on_delete=models.SET_NULL,
            related_name=getattr(cls, 'project_related_name', None),
            db_index=True
        ))

    def get_project_by_id(self, project_id: str) -> Optional['Project']:
        """Fetch Project by ID from database."""
        try:
            from ...planning.project import Project
            qs = Project.objects.filter(id=project_id)
            if not qs.exists():
                logger.warning(f'No Project found with ID {project_id} in {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple Projects ({qs.count()}) with ID {project_id} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching Project by ID {project_id}: {str(e)}', exc_info=True)
            return None

    def get_project_by_name(self, name: str) -> Optional['Project']:
        """Fetch Project by exact name match."""
        try:
            from ...planning.project import Project
            qs = Project.objects.filter(name=name)
            if not qs.exists():
                logger.warning(f'No Project found with name {name} in {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple Projects ({qs.count()}) with name {name} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching Project by name {name}: {str(e)}', exc_info=True)
            return None