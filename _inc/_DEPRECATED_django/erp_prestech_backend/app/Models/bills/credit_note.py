from django.db import models
from django.utils import timezone
from .._helpers.default_timed import DefaultTimed
from .._helpers.connectors.customer_connected import CustomerConnected
from .._helpers.fields import uuid_def_primary, default_user_creation, defalt_decicmal_10, VALID_MYSQL_MIN_DATE
class CreditNote(DefaultTimed, CustomerConnected):
  id = models.UUIDField(**uuid_def_primary())
  invoice = models.ForeignKey('Invoice',
    on_delete=models.SET_NULL,
    null=True,
    related_name="credit_notes",
    db_column="invoice"
  )
  amount = defalt_decicmal_10()
  date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now)
  created_by = default_user_creation('%(class)s_created_by')

  class Meta:
    db_table = "credit_note"
