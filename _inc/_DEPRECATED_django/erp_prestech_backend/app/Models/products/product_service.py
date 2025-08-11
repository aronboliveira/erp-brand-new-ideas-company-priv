from typing import List, Any
from django.db import models
from django.db.models import F
from django.contrib.auth import get_user_model
from .._helpers.connectors.category_connected import CategoryConnected
from .._helpers.describable import Describable
from .._helpers.fields import (default_char_field, UUID_VERIFIED,
                               VOID, default_decimal_12)
from .product_service_category import PRODUCT_SERVICE_CATEGORY_CHOICES

User = get_user_model()

class ProductService(Describable, CategoryConnected):
    name = default_char_field()
    sku = models.CharField(max_length=100, **VOID)
    sale_price = default_decimal_12()
    purchase_price = default_decimal_12()
    tax = models.ForeignKey("Tax", on_delete=models.SET_NULL, **VOID, related_name="product_services")
    unit = models.ForeignKey("ProductServiceUnit", on_delete=models.SET_NULL, **VOID, related_name="product_services")
    type = models.CharField(max_length=50, default='other', choices=PRODUCT_SERVICE_CATEGORY_CHOICES)
    sale_chartaccount_id = models.CharField(**UUID_VERIFIED, **VOID)
    expense_chartaccount_id = models.CharField(**UUID_VERIFIED, **VOID)

    def taxes_obj(self) -> Any:
        return self.tax

    def unit_obj(self) -> Any:
        return self.unit

    def category_obj(self) -> Any:
        return self.category

    def get_taxes(self, taxes: str) -> List[Any]:
        tax_ids = [tid.strip() for tid in taxes.split(",") if tid.strip()]
        from .. import Tax
        return list(Tax.objects.filter(id__in=tax_ids))

    def tax_rate(self, taxes: str) -> float:
        total_rate = 0.0
        tax_ids = [tid.strip() for tid in taxes.split(",") if tid.strip()]
        from .. import Tax
        for tax_obj in Tax.objects.filter(id__in=tax_ids):
            total_rate += tax_obj.rate
        return total_rate

    @classmethod
    def tax_data(cls, taxes: str) -> str:
        tax_ids = [tid.strip() for tid in taxes.split(",") if tid.strip()]
        from .. import Tax
        names = []
        for tax_obj in Tax.objects.filter(id__in=tax_ids):
            names.append(tax_obj.name)
        return ",".join(names)

    @classmethod
    def get_all_products(cls, authuser: Any) -> models.QuerySet:
        qs = cls.objects.filter(
            type="product",
            created_by=authuser.creator_id()
        ).select_related("category").order_by("-created_at").annotate(categoryname=F("category__name"))
        return qs

    def get_total_product_quantity(self, authuser: Any) -> float:
        purchased_quantity = 0.0
        pos_quantity = 0.0
        product_id = self.uuid
        from .. import Purchase, PurchaseProduct, Pos, PosProduct
        purchases = Purchase.objects.filter(created_by=authuser.creator_id())
        if hasattr(authuser, "isUser") and authuser.isUser():
            purchases = purchases.filter(warehouse_id=authuser.warehouse_id)
        for purchase in purchases:
            pp = PurchaseProduct.objects.filter(purchase_id=purchase.uuid, product_id=product_id).first()
            if pp:
                purchased_quantity += pp.quantity
        poses = Pos.objects.filter(created_by=authuser.creator_id())
        if hasattr(authuser, "isUser") and authuser.isUser():
            poses = poses.filter(warehouse_id=authuser.warehouse_id)
        for pos in poses:
            pp = PosProduct.objects.filter(pos_id=pos.uuid, product_id=product_id).first()
            if pp:
                pos_quantity += pp.quantity
        return purchased_quantity - pos_quantity

    @classmethod
    def tax_id(cls, product_id: Any, authuser: Any) -> Any:
        prod = cls.objects.filter(uuid=product_id, created_by=authuser.creator_id()).values("tax_id").first()
        return prod["tax_id"] if prod and prod.get("tax_id") is not None else 0

    def warehouse_product(self, product_id: Any, warehouse_id: Any) -> float:
        from .. import WarehouseProduct
        wp = WarehouseProduct.objects.filter(warehouse_id=warehouse_id, product_id=product_id).first()
        return wp.quantity if wp else 0

    def __str__(self) -> str:
        return self.name
