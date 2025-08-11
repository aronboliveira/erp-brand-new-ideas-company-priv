from django.db import models
from django.core.files.storage import default_storage
from django.utils import timezone
from .._helpers.describable import Describable
from .._helpers.connectors.project_connected import ProjectConnected
from .._helpers.connectors.customer_connected import CustomerConnected
from .._helpers.fields import (default_char_field, VALID_MYSQL_MIN_DATE, 
                               FIN_STATUS_CHOICES, FILE_SIZE_VALIDATOR, VOID)
class Contract(Describable, ProjectConnected, CustomerConnected):
  subject = default_char_field(voidable=True, db_index=True)
  value = models.DecimalField(max_digits=15, decimal_places=2, default=0.00)
  type = models.ForeignKey('ContractType', on_delete=models.SET_NULL, **VOID, related_name='contracts_of_type', db_index=True)
  start_date = models.DateField(**VOID,validators=[VALID_MYSQL_MIN_DATE], default=timezone.now)
  end_date = models.DateField(**VOID, validators=[VALID_MYSQL_MIN_DATE])
  status = default_char_field(
      voidable=True,
      choices=FIN_STATUS_CHOICES,
      default='draft'
  )
  contract_description = models.TextField(**VOID, max_length=65535)
  company_signature = models.URLField(max_length=200, **VOID)
  company_signature_image = models.ImageField(upload_to='company_signatures/', storage=default_storage, 
                                              validators=[FILE_SIZE_VALIDATOR], **VOID)
  client_signature = models.URLField(max_length=200, **VOID)
  client_signature_Image = models.ImageField(upload_to='client_signatures/', storage=default_storage, 
                                             validators=[FILE_SIZE_VALIDATOR], **VOID)
  
  class Meta:
    db_table = 'contracts'
  def __str__(self) -> str:
    return f"Contract {self.uuid} - {self.subject or 'No Subject'}"
  @staticmethod
  def status_dict() -> dict:
    return {'accept': 'Accept', 'decline': 'Decline'}
  def get_contract_summary(self, contracts: list) -> float:
    total: float = 0
    for c in contracts:
      total += c.value
    return total
  def files(self):
    return self.files.all()
  def notes(self):
    return self.notes_set.all()
  def comment(self):
    return self.comment_set.all()
