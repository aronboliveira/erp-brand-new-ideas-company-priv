from django.db import models
from django.core.files.storage import default_storage
from .default_timed import DefaultTimed
from .fields import (uuid_def_primary, default_char_field, default_user_creation,
                               default_text_field, VOID, TINY_BLANK_CHAR, FILE_SIZE_VALIDATOR, 
                               WORDED_NAME_VALIDATOR, PHONE_VALIDATOR, SHORT_BLANK_CHAR,
                               LANG_CHOICES, ZIP_VALIDATOR)
class Deliverable(DefaultTimed):
  
  class Meta:
    abstract = True
  
  id = models.UUIDField(**uuid_def_primary())
  lang = models.CharField(max_length=20, default='en', choices=LANG_CHOICES)
  name = default_char_field()
  email = models.EmailField(max_length=254, unique=True)
  secondary_email = models.EmailField(max_length=254, **VOID)
  contact = models.CharField(**TINY_BLANK_CHAR)
  avatar = models.ImageField(upload_to='avatars/', storage=default_storage, validators=[FILE_SIZE_VALIDATOR], **VOID)
  avatar_url = models.URLField(max_length=200, **VOID)
  email_verified_at = models.DateTimeField(**VOID)
  is_active = models.BooleanField(default=True)
  billing_country = default_char_field(validators=[WORDED_NAME_VALIDATOR])
  billing_state = default_char_field(validators=[WORDED_NAME_VALIDATOR])
  billing_city = default_char_field(validators=[WORDED_NAME_VALIDATOR])
  billing_phone = models.CharField(max_length=50, validators=[PHONE_VALIDATOR])
  billing_phone_verified_at = models.DateTimeField(**VOID)
  billing_zip = models.CharField(max_length=20, validators=[ZIP_VALIDATOR])
  billing_address = default_text_field(default='No billing address was given')
  shipping_name = default_char_field(voidable=True)
  shipping_country = models.CharField(**SHORT_BLANK_CHAR,validators=[WORDED_NAME_VALIDATOR])
  shipping_state = models.CharField(**SHORT_BLANK_CHAR,validators=[WORDED_NAME_VALIDATOR])
  shipping_city = models.CharField(**SHORT_BLANK_CHAR,validators=[WORDED_NAME_VALIDATOR])
  shipping_phone = models.CharField(**TINY_BLANK_CHAR,validators=[PHONE_VALIDATOR])
  shipping_phone_verified_at = models.DateTimeField(**VOID)
  shipping_zip = models.CharField(max_length=20, blank=True, validators=[ZIP_VALIDATOR])
  shipping_address = default_text_field(default='No shipping address was given')
  notes = default_text_field(length='medium', default='No notes were taken', **VOID)
  created_by = default_user_creation('%(class)s_created_by')