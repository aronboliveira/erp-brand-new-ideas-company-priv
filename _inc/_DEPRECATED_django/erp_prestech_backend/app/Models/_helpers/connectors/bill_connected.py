from django.db import models
import logging
from typing import Optional, TYPE_CHECKING
if TYPE_CHECKING:
  from ...bills.bill import Bill

logger = logging.getLogger(__name__)

class BillConnected(models.Model):
  bill_related_name: str = '%(class)s_bill'

  class Meta:
    abstract = True
  
  def __init_subclass__(cls, **kwargs) -> None:
    super().__init_subclass__(**kwargs)
    cls.add_to_class('bill', models.ForeignKey('Bill', on_delete=models.CASCADE, 
          related_name=getattr(cls, 'bill_related_name', None), db_index=True))

  def get_bill_by_id(self, bill_id: str) -> Optional[Bill]:
    try:
      from ...bills.bill import Bill
      qs = Bill.objects.filter(id=bill_id)
      if not qs.exists():
        logger.log('Failed to return %(class)s instance using id ' + bill_id)
        return None
      if len(qs) > 1:
        logger.warning('More than one %(class)s was returned in the QuerySet using the id ' + bill_id)
      return qs.first()
    except Exception as e:
      logger.warning(f'Invalidated attempt to get bill by id: {e}')
      return None
