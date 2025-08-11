import logging
from typing import Any

from ....Models.bills.bill import Bill
from ....Models.bills.debit_note import DebitNote
from ....Models.utils.utility import Utility

logger = logging.getLogger(__name__)


class DebitNoteStrategy:
    """Business rules for creating and updating debit notes."""

    @staticmethod
    def create_debit_note(
        bill: Bill,
        amount: float,
        description: str,
        date_val: str,
        user: Any,
    ) -> DebitNote:
        if amount > bill.get_due():
            max_due = user.price_format(bill.get_due())
            raise ValueError(f"Maximum {max_due} debit limit exceeded for this bill.")

        debit = DebitNote()
        for field, value in {
            "bill": bill.id,
            "vendor": bill.vendor_id,
            "date": date_val,
            "amount": amount,
            "description": description,
        }.items():
            setattr(debit, field, value)
        debit.save()

        Utility.update_user_balance("vendor", bill.vendor_id, amount, "credit")
        return debit

    @staticmethod
    def update_debit_note(
        bill: Bill,
        debit_note: DebitNote,
        amount: float,
        description: str,
        date_val: str,
        user: Any,
    ) -> DebitNote:
        if amount > bill.get_due():
            max_due = user.price_format(bill.get_due())
            raise ValueError(f"Maximum {max_due} debit limit exceeded for this bill.")

        Utility.update_user_balance("vendor", bill.vendor_id, debit_note.amount, "debit")

        for field, value in {
            "date": date_val,
            "amount": amount,
            "description": description,
        }.items():
            setattr(debit_note, field, value)
        debit_note.save()

        Utility.update_user_balance("vendor", bill.vendor_id, amount, "credit")
        return debit_note
