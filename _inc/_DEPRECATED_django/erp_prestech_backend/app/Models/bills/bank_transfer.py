from django.db import models
from django.utils import timezone
from .._helpers.describable import Describable
from .._helpers.fields import (default_char_field,
                               DEFAULTED_PAY_METHODS, VALID_MYSQL_MIN_DATE)
from typing import TYPE_CHECKING
if TYPE_CHECKING:
  from ..companies.bank_account import BankAccount
class BankTransfer(Describable):
  from_account = models.ForeignKey('BankAccount', on_delete=models.CASCADE, related_name='bank_transfers_from')
  to_account = models.ForeignKey('BankAccount', on_delete=models.CASCADE, related_name='bank_transfers_to')
  amount = models.DecimalField(max_digits=20, decimal_places=2)
  date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now)
  payment_method = models.CharField(**DEFAULTED_PAY_METHODS)
  reference = default_char_field(voidable=True)

  def from_bank_account(self) -> BankAccount:
    return self.from_account

  def to_bank_account(self) -> BankAccount:
    return self.to_account

  class Meta:
    ordering = ('-created_at',)
