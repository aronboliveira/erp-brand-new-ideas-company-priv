from django.db import models
from .._helpers.describable import Describable
from .._helpers.fields import default_char_field, VOID
from .product import PRODUCT_CATEGORY_CHOICES
class ProductCategory(Describable):
    name = default_char_field()
    parent_category = models.ForeignKey('self', on_delete=models.SET_NULL, related_name="subcategories", **VOID)
    type = models.CharField(default='other', choices=PRODUCT_CATEGORY_CHOICES)

    class Meta:
        db_table = "product_category"
    
    def __str__(self) -> str:
        return self.name
