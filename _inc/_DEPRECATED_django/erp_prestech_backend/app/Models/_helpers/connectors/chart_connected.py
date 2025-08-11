from django.db import models
import logging
from typing import Optional, TYPE_CHECKING
from ..fields import VOID
if TYPE_CHECKING:
  from ...charts.chart_of_account import ChartOfAccount

logger = logging.getLogger(__name__)

class ChartOfAccountConnected(models.Model):
  chart_of_acccount_related_name: str = '%(class)s_chart_of_acccount'

  class Meta:
    abstract = True
  
  def __init_subclass__(cls, **kwargs) -> None:
    super().__init_subclass__(**kwargs)
    cls.add_to_class('chart_of_acccount', models.ForeignKey('ChartOfAccount', on_delete=models.SET_NULL, 
          related_name=getattr(cls, 'chart_of_acccount_related_name', None), db_index=True, **VOID))

  def get_chart_of_acccount_by_id(self, chart_of_acccount_id: str) -> Optional[ChartOfAccount]:
    try:
      from ...charts.chart_of_account import ChartOfAccount
      qs = ChartOfAccount.objects.filter(id=chart_of_acccount_id)
      if not qs.exists():
        logger.log('Failed to return %(class)s instance using id ' + chart_of_acccount_id)
        return None
      if len(qs) > 1:
        logger.warning('More than one %(class)s was returned in the QuerySet using the id ' + chart_of_acccount_id)
      return qs.first()
    except Exception as e:
      logger.warning(f'Invalidated attempt to get chart_of_acccount by id: {e}')
      return None

  def get_chart_of_acccount_by_name(self, name) -> Optional[ChartOfAccount]:
    try:
      from ...charts.chart_of_account import ChartOfAccount
      qs = ChartOfAccount.objects.filter(name=name)
      if not qs.exists():
        logger.log('Failed to return %(class)s instance using name ' + name)
        return None
      if len(qs) > 1:
        logger.warning('More than one %(class)s was returned in the QuerySet using the name ' + name)
      return qs.first()
    except Exception as e:
      logger.warning(f'Invalidated attempt to get chart_of_acccount by name: {e}')
      return None

