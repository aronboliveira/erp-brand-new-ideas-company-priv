from django.db import models
from django.core.files.storage import default_storage
from django.core.exceptions import ValidationError
from django.utils.translation import gettext_lazy as _
import os
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, VOID

class LandingPageSetting(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = models.CharField(max_length=255, unique=True)
  value = models.TextField(max_length=65535, **VOID)
  created_by = default_user_creation('%(class)s_created_by')

  class Meta:
    db_table = 'landing_page_settings'
    ordering = ('-created_at',)

  @staticmethod
  def default_settings() -> dict:
      settings = {}
      sections = {
          'topbar': {
              'status': 'on',
              'notification_msg': '70% Special Offer. Don’t Miss it. The offer ends in 72 hours.',
          },
          'menubar': {
              'status': 'on',
              'page': '',
          },
          'site': {
              'logo': '',
              'description': '',
          },
          'home': {
              'status': 'on',
              'offer_text': '',
              'title': 'Home',
              'heading': '',
              'description': '',
              'trusted_by': '',
              'live_demo_link': '',
              'buy_now_link': '',
              'banner': '',
              'logo': '',
          },
          'feature': {
              'status': 'on',
              'title': 'Features',
              'heading': '',
              'description': '',
              'buy_now_link': '',
              'of_features': '',
          },
          'highlight_feature': {
              'heading': '',
              'description': '',
              'image': '',
          },
          'discover': {
              'status': 'on',
              'heading': '',
              'description': '',
              'live_demo_link': '',
              'buy_now_link': '',
              'of_features': '',
          },
          'screenshots': {
              'status': 'on',
              'heading': '',
              'description': '',
              '': '',
          },
          'plan': {
              'status': 'on',
              'title': 'Plan',
              'heading': '',
              'description': '',
          },
          'faq': {
              'status': 'on',
              'title': 'Faq',
              'heading': '',
              'description': '',
          },
          'testimonials': {
              'status': 'on',
              'heading': '',
              'description': '',
              'long_description': '',
              '': '',
          },
          'footer': {
              'status': 'on',
          },
          'joinus': {
              'status': 'on',
              'heading': '',
              'description': '',
          },
      }
      
      for section, fields in sections.items():
          for suffix, value in fields.items():
              key = f"{section}_{suffix}" if suffix else section
              settings[key] = value
      
      settings.update({
          "other_features": "",
          "faqs": "",
      })
      
      return settings

  @classmethod
  def get_settings(cls) -> dict:
    settings_dict = cls.default_settings()
    for setting in cls.objects.all():
      settings_dict[setting.name] = setting.value
    return settings_dict

  @classmethod
  def get_cached_settings(cls) -> dict:
    if not hasattr(cls, '_cached_settings'):
      cls._cached_settings = cls.get_settings()
    return cls._cached_settings

  @staticmethod
  def upload_file(file, path: str, name: str = None, allowed_extensions: list = None, max_size: int = None) -> dict:
    if not name:
      name = file.name
    ext = os.path.splitext(name)[1][1:].lower()
    if allowed_extensions and ext not in allowed_extensions:
      raise ValidationError(_('File type not allowed.'))
    if max_size and file.size > max_size:
      raise ValidationError(_('File size exceeds limit.'))
    final_path = os.path.join(path, name)
    saved_path = default_storage.save(final_path, file)
    return {
      'flag': 1,
      'msg': 'success',
      'url': default_storage.url(saved_path)
    }
