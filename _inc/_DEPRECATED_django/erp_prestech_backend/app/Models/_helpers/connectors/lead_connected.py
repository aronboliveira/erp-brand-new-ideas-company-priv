from django.db import models
import logging
from typing import Optional, TYPE_CHECKING

if TYPE_CHECKING:
    from ...activity.lead import Lead

logger = logging.getLogger(__name__)

class LeadConnected(models.Model):
    lead_related_name: str = '%(class)s_lead'

    class Meta:
        abstract = True

    def __init_subclass__(cls, **kwargs) -> None:
        super().__init_subclass__(**kwargs)
        cls.add_to_class('lead', models.ForeignKey(
            'Lead',
            on_delete=models.CASCADE,
            related_name=getattr(cls, 'lead_related_name', None),
            db_index=True
        ))

    def get_lead_by_id(self, lead_id: str) -> Optional['Lead']:
        """Fetch Lead by ID from database."""
        try:
            from ...activity.lead import Lead
            qs = Lead.objects.filter(id=lead_id)
            if not qs.exists():
                logger.warning(f'No Lead found with ID {lead_id} in {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple Leads ({qs.count()}) with ID {lead_id} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching Lead by ID {lead_id}: {str(e)}', exc_info=True)
            return None

    def get_lead_by_name(self, name: str) -> Optional['Lead']:
        """Fetch Lead by exact name match."""
        try:
            from ...activity.lead import Lead
            qs = Lead.objects.filter(name=name)
            if not qs.exists():
                logger.warning(f'No Lead found with name {name} in {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple Leads ({qs.count()}) with name {name} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching Lead by name {name}: {str(e)}', exc_info=True)
            return None