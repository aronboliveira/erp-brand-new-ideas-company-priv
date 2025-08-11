from django.db import models
import logging
from typing import Optional, TYPE_CHECKING
if TYPE_CHECKING:
  from ...companies.vendor import Vendor

logger = logging.getLogger(__name__)

class VendorConnected(models.Model):
  vendor_related_name: str = '%(class)s_vendor'

  class Meta:
    abstract = True
  
  def __init_subclass__(cls, **kwargs) -> None:
    super().__init_subclass__(**kwargs)
    cls.add_to_class('vendor', models.ForeignKey('Vendor', on_delete=models.CASCADE, 
          related_name=getattr(cls, 'vendor_related_name', None), db_index=True))

  def get_vendor_by_id(self, vendor_id: str) -> Optional[Vendor]:
    try:
      from ...companies.vendor import Vendor
      qs = Vendor.objects.filter(id=vendor_id)
      if not qs.exists():
        logger.log('Failed to return %(class)s instance using id ' + vendor_id)
        return None
      if len(qs) > 1:
        logger.warning('More than one %(class)s was returned in the QuerySet using the id ' + vendor_id)
      return qs.first()
    except Exception as e:
      logger.warning(f'Invalidated attempt to get vendor by id: {e}')
      return None

  def get_vendor_by_name(self, name) -> Optional[Vendor]:
    try:
      from ...companies.vendor import Vendor
      qs = Vendor.objects.filter(name=name)
      if not qs.exists():
        logger.log('Failed to return %(class)s instance using name ' + name)
        return None
      if len(qs) > 1:
        logger.warning('More than one %(class)s was returned in the QuerySet using the name ' + name)
      return qs.first()
    except Exception as e:
      logger.warning(f'Invalidated attempt to get vendor by name: {e}')
      return None

