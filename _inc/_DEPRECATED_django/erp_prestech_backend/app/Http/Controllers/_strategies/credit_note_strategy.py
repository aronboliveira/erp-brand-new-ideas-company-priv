import logging
from ....Models.bills.credit_note import CreditNote
from ....Models.bills.invoice import Invoice
from ....Models.utils.utility import Utility

logger = logging.getLogger(__name__)


class CreditNoteStrategy:
    """
    Business rules for creating and updating credit notes.
    """

    @staticmethod
    def create_credit_note(
        invoice: Invoice,
        amount: float,
        description: str,
        date_val: str,
        user,
    ) -> CreditNote:
        if amount > invoice.get_due():
            max_due = user.price_format(invoice.get_due())
            raise ValueError(f"Maximum {max_due} credit limit exceeded for this invoice.")

        credit = CreditNote()
        for field, value in {
            "invoice": invoice.id,
            "customer": invoice.customer_id,
            "date": date_val,
            "amount": amount,
            "description": description,
        }.items():
            setattr(credit, field, value)
        credit.save()

        Utility.update_user_balance("customer", invoice.customer_id, amount, "debit")
        return credit

    @staticmethod
    def update_credit_note(
        invoice: Invoice,
        credit_note: CreditNote,
        amount: float,
        description: str,
        date_val: str,
        user,
    ) -> CreditNote:
        if amount > invoice.get_due() + credit_note.amount:
            max_due = user.price_format(invoice.get_due())
            raise ValueError(f"Maximum {max_due} credit limit exceeded for this invoice.")

        Utility.update_user_balance("customer", invoice.customer_id, credit_note.amount, "credit")

        for field, value in {
            "date": date_val,
            "amount": amount,
            "description": description,
        }.items():
            setattr(credit_note, field, value)
        credit_note.save()

        Utility.update_user_balance("customer", invoice.customer_id, amount, "debit")
        return credit_note
