from django.db import models
import logging
from typing import Optional, TYPE_CHECKING

if TYPE_CHECKING:
    from ...individuals.customer import Customer

logger = logging.getLogger(__name__)

class CustomerConnected(models.Model):
    customer_related_name: str = '%(class)s_customer'

    class Meta:
        abstract = True
  
    def __init_subclass__(cls, **kwargs) -> None:
        super().__init_subclass__(**kwargs)
        cls.add_to_class('customer', models.ForeignKey(
            'Customer',
            on_delete=models.CASCADE,
            related_name=getattr(cls, 'customer_related_name', None),
            db_index=True
        ))
    
    @property
    def client(self):
        return self.customer
    
    @client.setter
    def client(self, value):
        self.customer = value

    def get_customer_by_id(self, customer_id: str) -> Optional['Customer']:
        """
        Fetch Customer by ID from database.
        Returns Customer instance or None.
        """
        try:
            from ...individuals.customer import Customer
            qs = Customer.objects.filter(id=customer_id)
            if not qs.exists():
                logger.warning(f'No Customer found with ID {customer_id} for {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple Customers ({qs.count()}) with ID {customer_id} for {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching Customer by ID {customer_id}: {str(e)}', exc_info=True)
            return None

    def get_customer_by_name(self, name: str) -> Optional['Customer']:
        """
        Fetch Customer by exact name match.
        Returns Customer instance or None.
        """
        try:
            from ...individuals.customer import Customer
            qs = Customer.objects.filter(name=name)
            if not qs.exists():
                logger.warning(f'No Customer found with name {name} for {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple Customers ({qs.count()}) with name {name} for {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching Customer by name {name}: {str(e)}', exc_info=True)
            return None