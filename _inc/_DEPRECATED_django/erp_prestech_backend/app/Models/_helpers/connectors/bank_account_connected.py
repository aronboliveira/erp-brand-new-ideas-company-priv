from django.db import models
import logging
from typing import Optional, TYPE_CHECKING
if TYPE_CHECKING:
  from ...companies.bank_account import BankAccount

logger = logging.getLogger(__name__)

class BankAccountConnected(models.Model):
    bank_account_related_name: str = '%(class)s_bank_account'

    class Meta:
        abstract = True
  
    def __init_subclass__(cls, **kwargs) -> None:
        super().__init_subclass__(**kwargs)
        cls.add_to_class('account', models.ForeignKey(
            'BankAccount',
            on_delete=models.CASCADE,
            related_name=getattr(cls, 'bank_account_related_name', None),
            db_index=True
        ))

    def get_bank_account_by_id(self, account_id: str) -> Optional['BankAccount']:
        """
        Fetch BankAccount by ID from database (not instance cache).
        Returns BankAccount instance or None.
        """
        try:
            from ...companies.bank_account import BankAccount
            qs = BankAccount.objects.filter(id=account_id)
            
            if not qs.exists():
                logger.warning(f'No BankAccount found with ID {account_id} for {self.__class__.__name__}')
                return None
            if len(qs) > 1:
                logger.warning(f'Multiple BankAccounts ({len(qs)}) found with ID {account_id} for {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching BankAccount by ID {account_id}: {str(e)}')
            return None

    def get_bank_account_by_details(self, holder_name: str, bank_name: str) -> Optional['BankAccount']:
        """
        Fetch BankAccount using holder_name + bank_name combination.
        Returns BankAccount instance or None.
        """
        try:
            from ...companies.bank_account import BankAccount
            qs = BankAccount.objects.filter(
                holder_name=holder_name,
                bank_name=bank_name
            )
            if not qs.exists():
                logger.warning(f'No BankAccount found for {holder_name}@{bank_name} in {self.__class__.__name__}')
                return None
            if len(qs) > 1:
                logger.warning(f'Multiple BankAccounts ({len(qs)}) for {holder_name}@{bank_name} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching BankAccount by details {holder_name}@{bank_name}: {str(e)}')
            return None