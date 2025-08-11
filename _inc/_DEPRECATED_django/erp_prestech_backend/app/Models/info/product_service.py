from decimal import Decimal
from typing import List, Optional, Any
from django.db import models
from .._helpers.connectors.category_connected import CategoryConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, defalt_decicmal_10, default_char_field, VOID
from django.db.models import QuerySet

class ProductService(DefaultTimed, CategoryConnected):
    id = models.UUIDField(**uuid_def_primary())
    name = default_char_field(db_index=True)
    sku = default_char_field(voidable=True,db_index=True)
    sale_price = defalt_decicmal_10()
    purchase_price = defalt_decicmal_10()
    tax = models.ForeignKey(
        "Tax",
        on_delete=models.SET_NULL,
        **VOID,
        related_name="product_services",
        db_index=True
    )
    unit = models.ForeignKey(
        "ProductServiceUnit",
        on_delete=models.SET_NULL,
        **VOID,
        related_name="product_services",
        db_index=True
    )
    type = models.CharField(max_length=50, db_index=True)
    sale_chartaccount = models.ForeignKey('ChartOfAccount', on_delete=models.SET_NULL, **VOID, related_name='sale_chart_of_account', db_index=True)
    expense_chartaccount = models.ForeignKey('ChartOfAccount', on_delete=models.SET_NULL, **VOID, related_name='expense_chart_of_account', db_index=True)
    created_by = default_user_creation('%(class)s_created_by', db_index=True)

    @property
    def taxes_object(self) -> Optional[Any]:
        return self.tax

    @property
    def unit_object(self) -> Optional[Any]:
        return self.unit

    @property
    def category_object(self) -> Optional[Any]:
        return self.category

    @staticmethod
    def get_tax_list(taxes_str: str) -> List[Optional[Any]]:
        tax_ids = [x.strip() for x in taxes_str.split(",") if x.strip()]
        from ..bills.tax import Tax  # Adjust path if necessary
        tax_list: List[Optional[Any]] = []
        for tax_id in tax_ids:
            try:
                tax_obj = Tax.objects.get(pk=tax_id)
                tax_list.append(tax_obj)
            except Tax.DoesNotExist:
                tax_list.append(None)
        return tax_list

    @staticmethod
    def tax_rate(taxes_str: str) -> Decimal:
        tax_ids = [x.strip() for x in taxes_str.split(",") if x.strip()]
        total_rate: Decimal = Decimal("0.00")
        from ..bills.tax import Tax
        for tax_id in tax_ids:
            try:
                tax_obj = Tax.objects.get(pk=tax_id)
                total_rate += tax_obj.rate
            except Tax.DoesNotExist:
                continue
        return total_rate

    @staticmethod
    def tax_data(taxes_str: str) -> str:
        tax_ids = [x.strip() for x in taxes_str.split(",") if x.strip()]
        names: List[str] = []
        from ..bills.tax import Tax
        for tax_id in tax_ids:
            try:
                tax_obj = Tax.objects.get(pk=tax_id)
                names.append(tax_obj.name)
            except Tax.DoesNotExist:
                names.append("")
        return ",".join(names)

    @classmethod
    def get_all_products(cls, user: Any) -> QuerySet:
        creator_id = user.creator_id()
        return cls.objects.filter(type="product", created_by=creator_id).select_related("category").order_by("-id")

    def get_total_product_quantity(self, user: Any) -> int:
        purchased_quantity = 0
        pos_quantity = 0
        product_id = self.id
        from ..activity.pos import Pos
        from ..activity.purchase import Purchase
        from ..products.pos_product import PosProduct
        from ..products.purchase_product import PurchaseProduct

        purchases = Purchase.objects.filter(created_by=user.creator_id())
        if hasattr(user, "isUser") and user.isUser():
            purchases = purchases.filter(warehouse_id=user.warehouse_id)
        for purchase in purchases:
            purchased_item = PurchaseProduct.objects.filter(purchase_id=purchase.id, product_id=product_id).first()
            if purchased_item:
                purchased_quantity += purchased_item.quantity

        poses = Pos.objects.filter(created_by=user.creator_id())
        if hasattr(user, "isUser") and user.isUser():
            poses = poses.filter(warehouse_id=user.warehouse_id)
        for pos in poses:
            pos_item = PosProduct.objects.filter(pos_id=pos.id, product_id=product_id).first()
            if pos_item:
                pos_quantity += pos_item.quantity

        total_quantity = purchased_quantity - pos_quantity
        return total_quantity

    @classmethod
    def tax_id(cls, product_id: int, user: Any) -> Any:
        product = cls.objects.filter(id=product_id, created_by=user.creator_id()).values("tax_id").first()
        return product["tax_id"] if product is not None else 0

    def warehouse_product(self, warehouse_id: int) -> int:
        from ..products.warehouse_product import WarehouseProduct
        wp = WarehouseProduct.objects.filter(warehouse_id=warehouse_id, product_id=self.id).first()
        return wp.quantity if wp is not None else 0
