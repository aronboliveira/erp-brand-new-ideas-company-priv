from django.db import models
import logging
from typing import Optional, TYPE_CHECKING
if TYPE_CHECKING:
  from ...companies.branch import Branch

logger = logging.getLogger(__name__)

class BranchConnected(models.Model):
  branch_related_name: str = '%(class)s_branch'

  class Meta:
    abstract = True
  
  def __init_subclass__(cls, **kwargs) -> None:
    super().__init_subclass__(**kwargs)
    cls.add_to_class('branch', models.ForeignKey('Branch', on_delete=models.SET_NULL, 
          related_name=getattr(cls, 'branch_related_name', None), db_index=True))

  def get_branch_by_id(self, branch_id: str) -> Optional[Branch]:
    try:
      from ...companies.branch import Branch
      qs = Branch.objects.filter(id=branch_id)
      if not qs.exists():
        logger.log('Failed to return %(class)s instance using id ' + branch_id)
        return None
      if len(qs) > 1:
        logger.warning('More than one %(class)s was returned in the QuerySet using the id ' + branch_id)
      return qs.first()
    except Exception as e:
      logger.warning(f'Invalidated attempt to get branch by id: {e}')
      return None

  def get_branch_by_name(self, name) -> Optional[Branch]:
    try:
      from ...companies.branch import Branch
      qs = Branch.objects.filter(name=name)
      if not qs.exists():
        logger.log('Failed to return %(class)s instance using name ' + name)
        return None
      if len(qs) > 1:
        logger.warning('More than one %(class)s was returned in the QuerySet using the name ' + name)
      return qs.first()
    except Exception as e:
      logger.warning(f'Invalidated attempt to get branch by name: {e}')
      return None

