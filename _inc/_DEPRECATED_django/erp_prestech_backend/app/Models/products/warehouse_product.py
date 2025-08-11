from django.db import models
from .product import Product

class WarehouseProduct(Product):
    warehouse = models.ForeignKey("Warehouse", on_delete=models.CASCADE, related_name="warehouse_products")

    def __str__(self) -> str:
        return f"{self.product} in {self.warehouse} (Quantity: {self.quantity})"

    class Meta:
        db_table = "warehouse_product"