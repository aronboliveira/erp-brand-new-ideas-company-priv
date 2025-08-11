from django.db import models
from django.contrib.auth import get_user_model
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, default_char_field, VOID
from .._helpers.connectors.pipeline_connected import PipelineConnected
from .._helpers.connectors.user_connected import UserConnected
User = get_user_model()
class Lead(DefaultTimed, PipelineConnected, UserConnected):
  id = models.UUIDField(**uuid_def_primary())
  name = default_char_field()
  email = models.EmailField(max_length=254)
  subject = default_char_field()
  sources = default_char_field(voidable=True)
  products = default_char_field(voidable=True)
  notes = models.TextField(blank=True, null=True, max_length=65535)
  labels = default_char_field(voidable=True)
  order = models.PositiveIntegerField(default=0)
  stage = models.ForeignKey('LeadStage', on_delete=models.SET_NULL, **VOID)
  created_by = default_user_creation('%(class)s_created_by')
  users = models.ManyToManyField(User, related_name='assigned_leads', db_table='user_leads')
  is_active = models.BooleanField(default=True)
  date = models.DateTimeField(auto_now_add=True)

  def __str__(self) -> str:
    return self.name

  @property
  def label_objects(self):
    from ..shapes.label import Label
    if self.labels:
      try:
        label_ids = [int(x.strip()) for x in self.labels.split(',') if x.strip()]
        return Label.objects.filter(id__in=label_ids)
      except ValueError:
        return Label.objects.none()
    return Label.objects.none()

  @property
  def product_objects(self):
    from ..products.product_service import ProductService
    if self.products:
      try:
        product_ids = [int(x.strip()) for x in self.products.split(',') if x.strip()]
        return ProductService.objects.filter(id__in=product_ids)
      except ValueError:
        return ProductService.objects.none()
    return ProductService.objects.none()

  @property
  def source_objects(self):
    from ..activity.source import Source
    if self.sources:
      try:
        source_ids = [int(x.strip()) for x in self.sources.split(',') if x.strip()]
        return Source.objects.filter(id__in=source_ids)
      except ValueError:
        return Source.objects.none()
    return Source.objects.none()

  @property
  def files(self):
    return self.leadfile_set.all()

  @property
  def activities(self):
    return self.leadactivitylog_set.order_by('-created_at')

  @property
  def discussions(self):
    return self.leaddiscussion_set.order_by('-created_at')

  @property
  def calls(self):
    return self.leadcall_set.all()

  @property
  def emails(self):
    return self.leademail_set.order_by('-created_at')

  class Meta:
    ordering = ('-created_at',)
