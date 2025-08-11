from django.db import models
from .._helpers.connectors.lead_connected import LeadConnected
from .._helpers.describable import Describable
from .._helpers.fields import default_char_field
class LeadEmail(Describable, LeadConnected):
    to = models.EmailField(max_length=254)
    subject = default_char_field()

    class Meta:
        db_table = "lead_email"
        ordering = ["-created_at"]
