from django.db import models
from .product import Product
class PurchaseProduct(Product):
    purchase = models.ForeignKey("Purchase", on_delete=models.CASCADE, related_name="items")

    def __str__(self) -> str:
        return f"PurchaseProduct {self.uuid} - Product: {self.product}"

    class Meta:
        db_table = "purchase_product"
