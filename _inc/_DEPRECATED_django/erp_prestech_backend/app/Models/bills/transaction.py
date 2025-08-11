import logging
from typing import Any, Dict
from django.db import models
from .._helpers.connectors.bank_account_connected import BankAccountConnected
from .._helpers.connectors.category_connected import CategoryConnected
from .._helpers.connectors.customer_connected import CustomerConnected
from .._helpers.describable import Describable
from .._helpers.connectors.user_connected import UserConnected
from .._helpers.fields import (defalt_decicmal_10,
                               VALID_MYSQL_MIN_DATE, VOID)
class Transaction(Describable, BankAccountConnected, CategoryConnected, CustomerConnected, UserConnected):
  type = models.CharField(max_length=20, db_index=True)
  amount = defalt_decicmal_10()
  date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], db_index=True)
  payment = models.ForeignKey(
    'InvoicePayment',
    on_delete=models.SET_NULL,
    null=True,
    blank=True,
    related_name='transactions',
    db_index=True
  )
  user_type = models.CharField(max_length=50, **VOID)

  @staticmethod
  def add_transaction(data: Dict[str, Any]) -> None:
    try:
      Transaction.objects.create(**data)
    except Exception as e:
      logging.error(f"Failed to add transaction: {e}")

  @staticmethod
  def edit_transaction(data: Dict[str, Any]) -> None:
    try:
      tx = Transaction.objects.filter(payment_id=data['payment_id'], type=data['type']).first()
      if tx:
        for key in ['account', 'amount', 'description', 'date', 'category']:
          setattr(tx, key, data[key])
        tx.save()
    except Exception as e:
      logging.error(f"Failed to edit transaction: {e}")

  @staticmethod
  def destroy_transaction(payment_id: Any, tx_type: str, user_type: str) -> None:
    try:
      Transaction.objects.filter(payment_id=payment_id, type=tx_type, user_type=user_type).delete()
    except Exception as e:
      logging.error(f"Failed to destroy transaction: {e}")

  @staticmethod
  def accounts(account_csv: str) -> str:
    from ..companies.bank_account import BankAccount
    names = []
    for acc_id in account_csv.split(','):
      try:
        if acc_id and int(acc_id) != 0:
          acc = BankAccount.objects.filter(id=int(acc_id)).first()
          if acc:
            names.append(f"{acc.bank_name} {acc.holder_name}")
      except Exception as e:
        logging.error(f"Error processing account id {acc_id}: {e}")
    return ', '.join(names)

  def __str__(self) -> str:
    return f"Transaction #{self.id}"
