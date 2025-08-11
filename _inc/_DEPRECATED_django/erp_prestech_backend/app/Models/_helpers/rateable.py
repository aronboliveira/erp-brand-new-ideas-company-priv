from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (default_user_creation, default_char_field, uuid_def_primary, 
                               EVAL_CHOICES, VOID)
class Rateable(DefaultTimed):
  
  class Meta:
    abstract = True

  id = models.UUIDField(**uuid_def_primary())
  integrity = models.TextField(max_length=2048)
  integrity_rating = default_char_field(
      choices=EVAL_CHOICES,
      default='average'
  )
  attendance = models.TextField(max_length=2048)
  attendance_rating = default_char_field(
      choices=EVAL_CHOICES,
      default='average'
  )
  customer_experience = models.TextField(max_length=2048)
  customer_experience_rating = default_char_field(
      choices=EVAL_CHOICES,
      default='average'
  )
  administration = models.TextField(max_length=2048)
  administration_rating = default_char_field(
    choices=EVAL_CHOICES,
    default='average',
    voidable=True
  )
  professionalism = models.TextField(max_length=2048)
  professionalism_rating = default_char_field(
    choices=EVAL_CHOICES,
    default='average',
    voidable=True
  )
  marketing = models.TextField(max_length=2048, **VOID)
  marketing_rating = default_char_field(
    choices=EVAL_CHOICES,
    default='average',
    voidable=True
  )
  overall_rating = default_char_field(
      choices=EVAL_CHOICES,
      default='average',
      voidable=True
  )
  rating = models.JSONField(**VOID)
  created_by = default_user_creation("%(class)s_created_by")