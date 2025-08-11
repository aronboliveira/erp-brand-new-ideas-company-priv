from django.db import models
from ..contact.email import Email
from .._helpers.connectors.deal_connected import DealConnected
from .._helpers.fields import default_char_field
class DealEmail(Email, DealConnected):
    to = models.EmailField()
    subject = default_char_field()

    def __str__(self) -> str:
        return f"DealEmail(deal={self.deal}, to={self.to})"
