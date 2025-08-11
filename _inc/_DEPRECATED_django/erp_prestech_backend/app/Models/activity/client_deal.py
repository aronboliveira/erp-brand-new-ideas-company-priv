from .._helpers.default_timed import DefaultTimed
from .._helpers.connectors.customer_connected import CustomerConnected
from .deal import Deal

class ClientDeal(Deal, DefaultTimed, CustomerConnected):

  def __str__(self) -> str:
    return f"ClientDeal(client_id={self.customer_id}, deal_id={self.id})"

  class Meta:
    db_table = "clientdeal"
