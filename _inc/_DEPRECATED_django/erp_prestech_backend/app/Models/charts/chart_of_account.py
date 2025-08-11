from django.db import models
from django.db.models import Sum
from .._helpers.describable import Describable
from .._helpers.fields import default_char_field

class ChartOfAccount(Describable):
  name = default_char_field(db_index=True)
  code = models.CharField(max_length=100, db_index=True)
  type = models.ForeignKey(
    "ChartOfAccountType",
    on_delete=models.SET_NULL,
    null=True,
    related_name="accounts",
    db_index=True
  )
  sub_type = models.ForeignKey(
    "ChartOfAccountSubType",
    on_delete=models.SET_NULL,
    null=True,
    related_name="accounts",
    db_index=True
  )
  is_enabled = models.BooleanField(default=True)

  def accounts(self) -> models.Model:
    """Returns the first JournalItem associated with this account, if available."""
    return self.journalitem_set.first()

  def balance(self) -> dict:
    journal_items = self.journalitem_set.aggregate(
      total_credit=Sum("credit"),
      total_debit=Sum("debit")
    )
    total_credit = journal_items.get("total_credit") or 0
    total_debit = journal_items.get("total_debit") or 0
    net_amount = total_credit - total_debit
    return {
      "total_credit": total_credit,
      "total_debit": total_debit,
      "net_amount": net_amount
    }

  def __str__(self) -> str:
    return f"{self.code} - {self.name}"

  class Meta:
    db_table = "chart_of_account"
