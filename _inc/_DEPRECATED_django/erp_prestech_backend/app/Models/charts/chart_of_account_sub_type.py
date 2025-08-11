from django.db import models
from .._helpers.connectors.chart_connected import ChartOfAccountConnected
from .._helpers.describable import Describable
from .._helpers.fields import default_char_field
class ChartOfAccountSubType(Describable, ChartOfAccountConnected):
  name = default_char_field(db_index=True)
  type = models.ForeignKey(
    "ChartOfAccountType",
    on_delete=models.CASCADE,
    related_name="sub_types"
  )
  
  def __str__(self) -> str:
    return f'${self.__class__.__name__} {self.name or 'ANONYMOUS'} created'

  class Meta:
    db_table = "chart_of_account_sub_type"
