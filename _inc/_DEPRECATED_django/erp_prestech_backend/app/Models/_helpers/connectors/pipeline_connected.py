from django.db import models
import logging
from typing import Optional, TYPE_CHECKING

if TYPE_CHECKING:
    from ...configs.pipeline import Pipeline

logger = logging.getLogger(__name__)

class PipelineConnected(models.Model):
    pipeline_related_name: str = '%(class)s_pipeline'

    class Meta:
        abstract = True

    def __init_subclass__(cls, **kwargs) -> None:
        super().__init_subclass__(**kwargs)
        cls.add_to_class('pipeline', models.ForeignKey(
            'Pipeline',
            on_delete=models.SET_NULL,
            related_name=getattr(cls, 'pipeline_related_name', None),
            db_index=True
        ))

    def get_pipeline_by_id(self, pipeline_id: str) -> Optional['Pipeline']:
        """Fetch Pipeline by ID from database."""
        try:
            from ...configs.pipeline import Pipeline
            qs = Pipeline.objects.filter(id=pipeline_id)
            if not qs.exists():
                logger.warning(f'No Pipeline found with ID {pipeline_id} in {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple Pipelines ({qs.count()}) with ID {pipeline_id} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching Pipeline by ID {pipeline_id}: {str(e)}', exc_info=True)
            return None

    def get_pipeline_by_name(self, name: str) -> Optional['Pipeline']:
        """Fetch Pipeline by exact name match."""
        try:
            from ...configs.pipeline import Pipeline
            qs = Pipeline.objects.filter(name=name)
            if not qs.exists():
                logger.warning(f'No Pipeline found with name {name} in {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple Pipelines ({qs.count()}) with name {name} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching Pipeline by name {name}: {str(e)}', exc_info=True)
            return None