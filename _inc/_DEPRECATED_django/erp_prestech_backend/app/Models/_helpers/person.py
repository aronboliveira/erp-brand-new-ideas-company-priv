from django.db import models
from .default_timed import DefaultTimed
from .fields import (default_char_field, default_user_creation, uuid_def_primary,
                     VOID, GENDER_CHOICES, SHORT_BLANK_CHAR, WORDED_NAME_VALIDATOR,
                     default_text_field)
class Person(DefaultTimed):
  class Meta:
    abstract = True
    
  id = models.UUIDField(**uuid_def_primary()) 
  name = default_char_field(db_index=True)
  phone = models.CharField(max_length=20, blank=True)
  email = models.EmailField(max_length=254, unique=True)
  email_verified_at = models.DateTimeField(**VOID)
  gender = models.CharField(
      max_length=126,
      default='other',
      choices=GENDER_CHOICES
  )
  country = models.CharField(validators=[WORDED_NAME_VALIDATOR], **SHORT_BLANK_CHAR)
  state = models.CharField(validators=[WORDED_NAME_VALIDATOR], **SHORT_BLANK_CHAR)
  city = models.CharField(validators=[WORDED_NAME_VALIDATOR], **SHORT_BLANK_CHAR)
  address = default_text_field(default='No address defined', voidable=True)
  created_by = default_user_creation('%(class)s_created_by')