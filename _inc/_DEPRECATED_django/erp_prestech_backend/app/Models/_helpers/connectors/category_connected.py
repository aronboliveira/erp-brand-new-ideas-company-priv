from django.db import models
import logging
from typing import Optional, TYPE_CHECKING
from ..fields import VOID

if TYPE_CHECKING:
    from ...products.product_service_category import ProductServiceCategory

logger = logging.getLogger(__name__)

class CategoryConnected(models.Model):
    category_related_name: str = '%(class)s_category'

    class Meta:
        abstract = True
  
    def __init_subclass__(cls, **kwargs) -> None:
        super().__init_subclass__(**kwargs)
        cls.add_to_class('category', models.ForeignKey(
            'ProductServiceCategory',
            on_delete=models.SET_NULL,
            related_name=getattr(cls, 'category_related_name', None),
            db_index=True
            **VOID,
        ))
    
    @property
    def client(self):
        return self.category
    
    @client.setter
    def client(self, value):
        self.category = value

    def get_category_by_id(self, category_id: str) -> Optional['ProductServiceCategory']:
        """
        Fetch ProductServiceCategory by ID from database.
        Returns ProductServiceCategory instance or None.
        """
        try:
            from ...products.product_service_category import ProductServiceCategory
            qs = ProductServiceCategory.objects.filter(id=category_id)
            if not qs.exists():
                logger.warning(f'No ProductServiceCategory found with ID {category_id} for {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple ProductServiceCategorys ({qs.count()}) with ID {category_id} for {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching ProductServiceCategory by ID {category_id}: {str(e)}', exc_info=True)
            return None

    def get_category_by_name(self, name: str) -> Optional['ProductServiceCategory']:
        """
        Fetch ProductServiceCategory by exact name match.
        Returns ProductServiceCategory instance or None.
        """
        try:
            from ...products.product_service_category import ProductServiceCategory
            qs = ProductServiceCategory.objects.filter(name=name)
            if not qs.exists():
                logger.warning(f'No ProductServiceCategory found with name {name} for {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple ProductServiceCategorys ({qs.count()}) with name {name} for {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching ProductServiceCategory by name {name}: {str(e)}', exc_info=True)
            return None