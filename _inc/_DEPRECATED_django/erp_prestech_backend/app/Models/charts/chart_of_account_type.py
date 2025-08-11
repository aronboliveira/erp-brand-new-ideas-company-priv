from .._helpers.connectors.chart_connected import ChartOfAccountConnected
from .._helpers.describable import Describable
from .._helpers.fields import default_char_field
class ChartOfAccountType(Describable, ChartOfAccountConnected):
  name = default_char_field()

  def __str__(self) -> str:
    return self.name

  class Meta:
    db_table = "chart_of_account_type"
