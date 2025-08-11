from django.db import models
from django.core.files.storage import default_storage
from .._helpers.fields import (defalt_decicmal_10, default_char_field, 
                               VOID, VALID_FILE_SIZES, FILE_SIZE_VALIDATOR)
from .._helpers.describable import Describable

class Plan(Describable):
  name = default_char_field()
  price = defalt_decicmal_10({})
  duration = models.CharField(max_length=10, default='month', choices=[('lifetime', 'Lifetime'),('month', 'Per Month'),('year', 'Per Year')])
  # TODO NECESSÁRIO DEFINIR FUTURAMENTE
  max_users = models.PositiveIntegerField(default=32, db_index=True)
  max_customers = models.PositiveIntegerField(default=32)
  max_vendors = models.PositiveIntegerField(default=32)
  max_clients = models.PositiveIntegerField(default=32)
  storage_limit = models.PositiveIntegerField(default=1073741822, db_index=True)
  image = models.ImageField(upload_to='plan_images/', storage=default_storage, validators=[FILE_SIZE_VALIDATOR], **VOID)
  image_size = models.IntegerField(validators=VALID_FILE_SIZES, **VOID)
  crm = models.BooleanField(default=False)
  hrm = models.BooleanField(default=False)
  account = models.BooleanField(default=False)
  project = models.BooleanField(default=False)
  pos = models.BooleanField(default=False)
  chatgpt = models.BooleanField(default=False)
  
  class Meta:
    verbose_name = "Plan"
    verbose_name_plural = "Plans"
    
  def __str__(self) -> str:
    return self.name
  @classmethod
  def total_plan(cls) -> int:
    return cls.objects.count()
  @classmethod
  def most_purchased_plan(cls):
    from ..individuals.user import User
    free_plan = cls.objects.filter(price__lte=0).first()
    if not free_plan:
      return None
    return (User.objects.filter(type='company')
            .exclude(plan=free_plan)
            .values('plan')
            .annotate(total=models.Count('plan'))
            .order_by('-total')
            .first())
  @classmethod
  def get_plan(cls, id: str):
    if not hasattr(cls, '_cached_plan'):
      try:
        cls._cached_plan = cls.objects.get(uuid=id)
      except cls.DoesNotExist:
        cls._cached_plan = None
    return cls._cached_plan
