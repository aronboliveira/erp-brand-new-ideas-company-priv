from django.db import models
from .product import Product
class PosProduct(Product):
    pos = models.ForeignKey("Pos", on_delete=models.CASCADE, related_name="pos_products")

    def __str__(self) -> str:
        return f"PosProduct #{self.uuid} - Product: {self.product}"

    class Meta:
        db_table = "pos_product"
