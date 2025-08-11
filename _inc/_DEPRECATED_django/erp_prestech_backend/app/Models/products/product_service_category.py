from typing import Any, List, Tuple
from django.db import models
from django.db.models import Sum, Count
from django.utils import timezone
from .._helpers.connectors.chart_connected import ChartOfAccountConnected
from .._helpers.describable import Describable
from .._helpers.fields import default_char_field
from ..utils.utility import Utility

PRODUCT_SERVICE_CATEGORY_CHOICES: List[Tuple[str, str]] = [
    ("tech_services", "Tech Services"),
    ("saas", "Software as a Service (SaaS)"),
    ("consulting", "Consulting"),
    ("maintenance", "Maintenance"),
    ("training", "Training"),
    ("financial", "Financial Services"),
    ("legal", "Legal Services"),
    ("marketing", "Marketing"),
    ("media_content", "Media & Content"),
    ("telecom", "Telecommunications"),
    ("cloud", "Cloud Services"),
    ("hosting", "Hosting & Domains"),
    ("freelance", "Freelance or Contract Work"),
    ("construction", "Construction & Trades"),
    ("cleaning", "Cleaning & Maintenance"),
    ("tourism", "Tourism & Hospitality"),
    ("transportation", "Transportation & Logistics"),
    ("healthcare", "Healthcare & Wellness"),
    ("product & service", "Product & Service"),
    ("income", "Income"),
    ("expense", "Expense"),
    ("asset", "Asset"),
    ("liability", "Liability"),
    ("equity", "Equity"),
    ("costs of good sold", "Costs of Goods Sold")
    ("other", "Other Services"),
]

class ProductServiceCategory(Describable, ChartOfAccountConnected):
    name = default_char_field(db_index=True)
    type = models.CharField(max_length=50, default='other', choices=PRODUCT_SERVICE_CATEGORY_CHOICES)

    def categories(self) -> models.QuerySet:
        return self.revenue_set.all()

    def income_category_revenue_amount(self, user: Any) -> float:
        year = timezone.now().year
        revenue_sum = self.revenue_set.filter(
            created_by=user.creator_id(),
            date__year=year
        ).aggregate(total=Sum("amount"))["total"] or 0
        invoices = self.invoice_set.filter(
            created_by=user.creator_id(),
            send_date__year=year
        )
        invoice_total = Utility.get_total_invoice_amounts(invoices)
        return revenue_sum + invoice_total

    def expense_category_amount(self, user: Any) -> float:
        year = timezone.now().year
        payment_sum = self.payment_set.filter(
            created_by=user.creator_id(),
            date__year=year
        ).aggregate(total=Sum("amount"))["total"] or 0
        bills = self.bill_set.filter(
            created_by=user.creator_id(),
            send_date__year=year
        )
        bill_total = sum(bill.get_total() for bill in bills)
        return payment_sum + bill_total

    @classmethod
    def get_all_categories(cls, user: Any) -> models.QuerySet:
        return cls.objects.filter(
            created_by=user.creator_id(),
            type="0"
        ).annotate(
            product_services=Count("productservice")
        ).order_by("-created_at")
    
    def __str__(self) -> str:
        return self.name
