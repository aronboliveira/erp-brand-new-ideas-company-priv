from django.db import models
from .._helpers.connectors.customer_connected import CustomerConnected
from .._helpers.describable import Describable
from .._helpers.fields import (default_char_field, default_decimal_12, 
                               COMPLETION_CHOICES, VOID)
from .._helpers.default_timed import DefaultTimed

class EstimationProduct(Describable):
  estimation = models.ForeignKey('Estimation', on_delete=models.CASCADE, related_name='estimation_products', db_index=True)
  product = models.ForeignKey('ProductService', on_delete=models.CASCADE, related_name='estimation_products', db_index=True)
  price = models.DecimalField(max_digits=15, decimal_places=2, default=0.00)
  quantity = models.PositiveIntegerField(default=1)
  
  def __str__(self) -> str:
    return f"EstimationProduct {self.uuid} (Estimation {self.estimation_id}, Product {self.product_id})"


class Estimation(Describable, CustomerConnected):
  status = default_char_field(default='pending', choices=COMPLETION_CHOICES)
  issue_date = models.DateField(**VOID)
  discount = default_decimal_12()
  tax = models.ForeignKey('Tax', on_delete=models.SET_NULL, **VOID, related_name='estimations', db_index=True)
  terms = models.TextField(**VOID, max_length=65535)
  products = models.ManyToManyField('ProductService', through='EstimationProduct', related_name='estimations', db_index=True)
  
  class Meta:
    db_table = 'estimations'
  def __str__(self) -> str:
    return f"Estimation #{self.estimation_id} (Status: {self.status})"
  def get_sub_total(self) -> float:
    qs = self.estimation_products.all()
    subtotal = 0.0
    for ep in qs:
      subtotal += ep.price * ep.quantity
    return subtotal
  def get_tax_amount(self) -> float:
    sub_total = self.get_sub_total()
    if sub_total <= 0 or not self.tax:
      return 0.0
    taxable_amount = float(sub_total - float(self.discount))
    rate = float(self.tax.rate)
    tax_value = taxable_amount * (rate / 100.0)
    return tax_value
  def get_total(self) -> float:
    return float(self.get_sub_total()) - float(self.discount) + float(self.get_tax_amount())
  def payments(self):
    return self.payment_set.all()
  def get_due(self) -> float:
    all_payments = self.payments()
    total_paid = sum(p.amount for p in all_payments)
    return self.get_total() - total_paid
  @staticmethod
  def get_estimation_summary(estimates: list) -> float:
    overall = 0.0
    for e in estimates:
      overall += e.get_total()
    return overall
