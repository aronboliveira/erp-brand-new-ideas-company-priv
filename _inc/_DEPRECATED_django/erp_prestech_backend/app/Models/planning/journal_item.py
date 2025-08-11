from django.db import models
from decimal import Decimal
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_char_field, uuid_def_primary, default_user_creation, default_decimal_12

class JournalItem(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  journal = default_char_field(db_index=True, help_text='The name or slug for the journal')
  account = models.ForeignKey("ChartOfAccount", on_delete=models.SET_NULL, null=True, 
                              related_name="journal_items", db_index=True)
  debit = default_decimal_12()
  credit = default_decimal_12()
  created_by = default_user_creation('%(class)s_created_by', db_index=True)
  
  def __str__(self) -> str:
    return f"JournalItem {self.journal} - Account: {self.account} | Debit: {self.debit} | Credit: {self.credit}"
