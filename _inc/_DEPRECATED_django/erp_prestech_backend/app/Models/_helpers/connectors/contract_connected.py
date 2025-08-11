from django.db import models
import logging
from typing import Optional, TYPE_CHECKING

if TYPE_CHECKING:
    from ...planning.contract import Contract

logger = logging.getLogger(__name__)

class ContractConnected(models.Model):
    contract_related_name: str = '%(class)s_contract'

    class Meta:
        abstract = True

    def __init_subclass__(cls, **kwargs) -> None:
        super().__init_subclass__(**kwargs)
        cls.add_to_class('contract', models.ForeignKey(
            'contract',
            on_delete=models.CASCADE,
            related_name=getattr(cls, 'contract_related_name', None),
            db_index=True
        ))

    def get_contract_by_id(self, contract_id: str) -> Optional['Contract']:
        """Fetch contract by ID from database."""
        try:
            from ...planning.contract import Contract
            qs = Contract.objects.filter(id=contract_id)
            if not qs.exists():
                logger.warning(f'No contract found with ID {contract_id} in {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple contracts ({qs.count()}) with ID {contract_id} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching contract by ID {contract_id}: {str(e)}', exc_info=True)
            return None

    def get_contract_by_subject(self, subject: str) -> Optional['Contract']:
        """Fetch contract by exact subject match."""
        try:
            from ...planning.contract import Contract
            qs = Contract.objects.filter(subject=subject)
            if not qs.exists():
                logger.warning(f'No contract found with subject {subject} in {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple contracts ({qs.count()}) with name {subject} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching contract by name {subject}: {str(e)}', exc_info=True)
            return None