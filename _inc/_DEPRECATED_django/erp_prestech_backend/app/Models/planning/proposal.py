from decimal import Decimal
from django.db import models
from .._helpers.connectors.category_connected import CategoryConnected
from .._helpers.connectors.customer_connected import CustomerConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_user_creation, VOID, 
                               UUID_VERIFIED, VALID_MYSQL_MIN_DATE)
class Proposal(DefaultTimed, CategoryConnected, CustomerConnected):
  id = models.UUIDField(**uuid_def_primary())
  issue_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE])
  status = models.CharField(max_length=50, default='draft', choices=[('Draft','Draft'),('Open','Open'),
                                                                     ('Accepted','Accepted'),('Declined','Declined'),
                                                                     ('Close','Close')])
  is_convert = models.BooleanField(default=False)
  converted_invoice_id = models.CharField(**UUID_VERIFIED, **VOID)
  tax_field = models.ForeignKey('Tax', on_delete=models.SET_NULL, **VOID, related_name='proposal_tax')
  created_by = default_user_creation('%(class)s_created_by')

  def get_sub_total(self) -> Decimal:
    subtotal: Decimal = Decimal('0.00')
    for product in self.items.all():
      subtotal += product.price * product.quantity
    return subtotal

  def get_total_discount(self) -> Decimal:
    total_discount: Decimal = Decimal('0.00')
    for product in self.items.all():
      total_discount += product.discount
    return total_discount

  def get_total_tax(self) -> Decimal:
    total_tax: Decimal = Decimal('0.00')
    from ..utils.utility import Utility
    for product in self.items.all():
      rate: Decimal = Utility.total_tax_rate(product.tax)
      taxable_amount: Decimal = product.price * product.quantity - product.discount
      total_tax += (rate / Decimal('100')) * taxable_amount
    return total_tax

  def get_total(self) -> Decimal:
    return (self.get_sub_total() - self.get_total_discount()) + self.get_total_tax()

  def get_due(self) -> Decimal:
    total_paid: Decimal = Decimal('0.00')
    for payment in self.payments.all():
      total_paid += payment.amount
    credit_note_total: Decimal = Decimal('0.00')
    return self.get_total() - total_paid - credit_note_total

  @classmethod
  def change_status(cls, proposal_id: str, status: str) -> None:
    try:
      proposal = cls.objects.get(uuid=proposal_id)
      proposal.status = status
      proposal.save()
    except cls.DoesNotExist:
      pass

  def __str__(self) -> str:
    return self.proposal_id
