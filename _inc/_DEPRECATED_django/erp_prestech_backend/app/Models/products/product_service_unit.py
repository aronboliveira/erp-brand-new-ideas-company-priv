from django.db import models
from .._helpers.describable import Describable
from .._helpers.fields import default_char_field
from .product_category import PRODUCT_CATEGORY_CHOICES
class ProductServiceUnit(Describable):
    name = default_char_field(db_index=True, help_text='Name of abbreviation')
    abbraviation = default_char_field(voidable=True, help_text='Short code for the unit')
    category = models.CharField(default='common', choices=PRODUCT_CATEGORY_CHOICES)
    
    class Meta:
        db_table = "product_service_units"
        verbose_name = "Product Service Unit"
        verbose_name_plural = "Product Service Units"

    def __str__(self) -> str:
        return self.name
