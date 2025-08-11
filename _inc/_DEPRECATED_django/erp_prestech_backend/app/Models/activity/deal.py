from django.db import models
from .._helpers.describable import Describable
from .._helpers.fields import (defalt_decicmal_10, default_char_field, 
                               COMPLETION_CHOICES, VOID)
from .._helpers.connectors.pipeline_connected import PipelineConnected
from ..individuals.user import User
class Deal(Describable, PipelineConnected):
  name = default_char_field()
  price = defalt_decicmal_10()
  stage = models.ForeignKey(
    'Stage', on_delete=models.SET_NULL,
    **VOID, related_name='deals'
  )
  group = models.ForeignKey(
    'Group', on_delete=models.SET_NULL,
    **VOID, related_name='deals'
  )
  sources = models.OneToManyField('Source', related_name='deals_sources')
  products = default_char_field(voidable=True)
  labels = default_char_field(voidable=True)
  permissions_field = default_char_field(voidable=True)
  status = models.CharField(max_length=50, default='pending', choices=COMPLETION_CHOICES, **VOID)
  is_active = models.BooleanField(default=True)
  custom_field = None

  permissions = [
    'Client View Tasks',
    'Client View Products',
    'Client View Sources',
    'Client View Contacts',
    'Client View Files',
    'Client View Invoices',
    'Client View Custom fields',
    'Client View Members',
    'Client Add File',
    'Client Deal Activity',
  ]

  statuses = {
    'Active': 'Active',
    'Won': 'Won',
    'Loss': 'Loss',
  }

  clients = models.ManyToManyField(
    User,
    related_name='client_deals',
    db_table='client_deals',
  )
  users = models.ManyToManyField(
    User,
    related_name='user_deals',
    db_table='user_deals',
  )

  def get_labels(self) -> models.QuerySet:
    from ..shapes.label import Label
    if self.labels:
      try:
        label_ids = [int(x.strip()) for x in self.labels.split(',') if x.strip()]
        return Label.objects.filter(id__in=label_ids)
      except ValueError as e:
        print(f"Failed to parse labels in Deal.get_labels: {e}")
        return Label.objects.none()
    return Label.objects.none()

  @property
  def label_objects(self) -> models.QuerySet:
    return self.get_labels()

  @property
  def product_objects(self) -> models.QuerySet:
    from ..products.product_service import ProductService
    if self.products:
      try:
        product_ids = [int(x.strip()) for x in self.products.split(',') if x.strip()]
        return ProductService.objects.filter(id__in=product_ids)
      except ValueError as e:
        print(f"Failed to parse products in Deal.product_objects: {e}")
        return ProductService.objects.none()
    return ProductService.objects.none()

  @property
  def source_objects(self) -> models.QuerySet:
    from ..activity.source import Source
    if self.sources:
      try:
        source_ids = [int(x.strip()) for x in self.sources.split(',') if x.strip()]
        return Source.objects.filter(id__in=source_ids)
      except ValueError as e:
        print(f"Failed to parse sources in Deal.source_objects: {e}")
        return Source.objects.none()
    return Source.objects.none()

  @property
  def files(self) -> models.QuerySet:
    return self.deal_files.all()

  @property
  def tasks(self) -> models.QuerySet:
    return self.dealtask_set.all()

  @property
  def complete_tasks(self) -> models.QuerySet:
    return self.dealtask_set.filter(status=1)

  @property
  def invoices(self) -> models.QuerySet:
    return self.invoice_set.all()

  @property
  def calls(self) -> models.QuerySet:
    return self.deal_calls.all()

  @property
  def emails(self) -> models.QuerySet:
    return self.dealemail_set.order_by('-id')

  @property
  def activities(self) -> models.QuerySet:
    return self.activitylog_set.order_by('-id')

  @property
  def discussions(self) -> models.QuerySet:
    return self.deal_discussions.order_by('-id')

  @classmethod
  def get_deal_summary(cls, deals: list, user: User) -> str:
    total = sum(deal.price for deal in deals)
    return user.price_format(total)

  def __str__(self) -> str:
    return self.name
