from django.db import models
import logging
from typing import Optional, TYPE_CHECKING

if TYPE_CHECKING:
    from ...individuals.user import User

logger = logging.getLogger(__name__)

class UserConnected(models.Model):
    user_related_name: str = '%(class)s_user'

    class Meta:
        abstract = True

    def __init_subclass__(cls, **kwargs) -> None:
        super().__init_subclass__(**kwargs)
        cls.add_to_class('user', models.ForeignKey(
            'User',
            on_delete=models.CASCADE,
            related_name=getattr(cls, 'user_related_name', None),
            db_index=True
        ))

    def get_user_by_id(self, user_id: str) -> Optional['User']:
        """Fetch User by ID from database."""
        try:
            from ...individuals.user import User
            qs = User.objects.filter(id=user_id)
            if not qs.exists():
                logger.warning(f'No User found with ID {user_id} in {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple Users ({qs.count()}) with ID {user_id} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching User by ID {user_id}: {str(e)}', exc_info=True)
            return None

    def get_user_by_name(self, name: str) -> Optional['User']:
        """Fetch User by exact name match."""
        try:
            from ...individuals.user import User
            qs = User.objects.filter(name=name)
            if not qs.exists():
                logger.warning(f'No User found with name {name} in {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple Users ({qs.count()}) with name {name} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching User by name {name}: {str(e)}', exc_info=True)
            return None