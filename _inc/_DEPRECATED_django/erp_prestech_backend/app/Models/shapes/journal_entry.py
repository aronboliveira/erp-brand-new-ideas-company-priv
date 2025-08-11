from django.db import models
from django.utils import timezone
import logging
from .._helpers.describable import Describable
from .._helpers.fields import (default_char_field,
                               VALID_MYSQL_MIN_DATE)
  
logger = logging.getLogger(__name__)
  
class JournalEntry(Describable):
  date = models.DateField(validators=[VALID_MYSQL_MIN_DATE],default=timezone.now)
  reference = default_char_field()
  journal = models.ForeignKey('Journal', on_delete=models.CASCADE)
  
  def accounts(self):
    return self.journalitem_set.all()
  
  def total_credit(self) -> float:
    total: float = 0
    try:
      for account in self.accounts():
        total += account.credit
    except Exception as e:
      logger.error(f"Failed to calculate total credit in JournalEntry: {e}")
      total = 0
    return total
  
  def total_debit(self) -> float:
    total: float = 0
    try:
      for account in self.accounts():
        total += account.debit
    except Exception as e:
      logger.error(f"Failed to calculate total debit in JournalEntry: {e}")
      total = 0
    return total
  
  class Meta:
    db_table = 'journal_entry'
