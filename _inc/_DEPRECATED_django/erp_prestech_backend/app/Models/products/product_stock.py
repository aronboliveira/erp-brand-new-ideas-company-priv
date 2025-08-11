from django.db import models
from .._helpers.describable import Describable
from .product_service import ProductService

class ProductStock(Describable):
    product_service = models.ForeignKey(
        ProductService,
        on_delete=models.CASCADE,
        related_name='stocks',
        db_index=True
    )
    quantity = models.IntegerField()
    type = models.CharField(max_length=50)
    type_id = models.IntegerField(default=0)

    class Meta:
        db_table = 'product_stock'

    def __str__(self) -> str:
        return f'{self.quantity} {self.type} for {self.product_service}'
