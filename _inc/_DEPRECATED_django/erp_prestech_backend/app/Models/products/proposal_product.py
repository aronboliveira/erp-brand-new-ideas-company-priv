from django.db import models
import logging
from .product_service import ProductService
from .product import Product
class ProposalProduct(Product):
  proposal = models.ForeignKey('Proposal', on_delete=models.CASCADE,
                                related_name='proposal_products')

  class Meta:
    db_table = 'proposal_product'
    ordering = ['-created_at']

  def get_product(self) -> 'ProductService':
    try:
      return self.product
    except Exception as e:
      logging.error(f"Failed to retrieve product in ProposalProduct: {e}")
      return None
