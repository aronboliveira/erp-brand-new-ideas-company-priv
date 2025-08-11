from django.db import models
import logging
from typing import Optional, TYPE_CHECKING

if TYPE_CHECKING:
    from ...activity.deal import Deal

logger = logging.getLogger(__name__)

class DealConnected(models.Model):
    deal_related_name: str = '%(class)s_deal'

    class Meta:
        abstract = True

    def __init_subclass__(cls, **kwargs) -> None:
        super().__init_subclass__(**kwargs)
        cls.add_to_class('deal', models.ForeignKey(
            'Deal',
            on_delete=models.CASCADE,
            related_name=getattr(cls, 'deal_related_name', None),
            db_index=True
        ))

    def get_deal_by_id(self, deal_id: str) -> Optional['Deal']:
        """Fetch Deal by ID from database."""
        try:
            from ...activity.deal import Deal
            qs = Deal.objects.filter(id=deal_id)
            if not qs.exists():
                logger.warning(f'No Deal found with ID {deal_id} in {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple Deals ({qs.count()}) with ID {deal_id} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching Deal by ID {deal_id}: {str(e)}', exc_info=True)
            return None

    def get_deal_by_name(self, name: str) -> Optional['Deal']:
        """Fetch Deal by exact name match."""
        try:
            from ...activity.deal import Deal
            qs = Deal.objects.filter(name=name)
            if not qs.exists():
                logger.warning(f'No Deal found with name {name} in {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple Deals ({qs.count()}) with name {name} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching Deal by name {name}: {str(e)}', exc_info=True)
            return None