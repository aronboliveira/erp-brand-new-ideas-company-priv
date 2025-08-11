from django.db import models
import logging
from typing import Optional, TYPE_CHECKING

if TYPE_CHECKING:
    from ...bills.invoice import Invoice

logger = logging.getLogger(__name__)

class InvoiceConnected(models.Model):
    invoice_related_name: str = '%(class)s_invoice'

    class Meta:
        abstract = True

    def __init_subclass__(cls, **kwargs) -> None:
        super().__init_subclass__(**kwargs)
        cls.add_to_class('Invoice', models.ForeignKey(
            'invoice',
            on_delete=models.CASCADE,
            related_name=getattr(cls, 'invoice_related_name', None),
            db_index=True
        ))

    def get_invoice_by_id(self, invoice_id: str) -> Optional['Invoice']:
        """Fetch invoice by ID from database."""
        try:
            from ...bills.invoice import Invoice
            qs = Invoice.objects.filter(id=invoice_id)
            if not qs.exists():
                logger.warning(f'No invoice found with ID {invoice_id} in {self.__class__.__name__}')
                return None
            if qs.count() > 1:
                logger.warning(f'Multiple invoices ({qs.count()}) with ID {invoice_id} in {self.__class__.__name__}')
            return qs.first()
        except Exception as e:
            logger.error(f'Error fetching invoice by ID {invoice_id}: {str(e)}', exc_info=True)
            return None