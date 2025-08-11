import os
from django.core.files.storage import default_storage
from django.core.exceptions import ValidationError
from django.utils.translation import gettext_lazy as _
from django.db import models
from ....Models._helpers.default_timed import DefaultTimed
from ....Models._helpers.fields import uuid_def_primary, def_char_field, def_user_creation, VOID

class LandingPageSetting(DefaultTimed):
    id = models.UUIDField(**uuid_def_primary())
    name = def_char_field()
    value = models.TextField(**VOID, max_length=65535)
    created_by = def_user_creation('%(class)s_created_by')
    
    class Meta:
        db_table = 'landing_page_settings'
        ordering = ('-created_at',)
    
    def __str__(self) -> str:
        return f"{self.name} = {self.value}"
    
    @classmethod
    def settings(cls) -> dict:
        defaults = {
            "topbar_status": "on",
            "topbar_notification_msg": "70% Special Offer. Don’t Miss it. The offer ends in 72 hours.",
            "menubar_status": "on",
            "menubar_page": "",
            "site_logo": "",
            "site_description": "",
            "home_status": "on",
            "home_offer_text": "",
            "home_title": "Home",
            "home_heading": "",
            "home_description": "",
            "home_trusted_by": "",
            "home_live_demo_link": "",
            "home_buy_now_link": "",
            "home_banner": "",
            "home_logo": "",
            "feature_status": "on",
            "feature_title": "Features",
            "feature_heading": "",
            "feature_description": "",
            "feature_buy_now_link": "",
            "feature_of_features": "",
            "highlight_feature_heading": "",
            "highlight_feature_description": "",
            "highlight_feature_image": "",
            "other_features": "",
            "discover_status": "on",
            "discover_heading": "",
            "discover_description": "",
            "discover_live_demo_link": "",
            "discover_buy_now_link": "",
            "discover_of_features": "",
            "screenshots_status": "on",
            "screenshots_heading": "",
            "screenshots_description": "",
            "screenshots": "",
            "plan_status": "on",
            "plan_title": "Plan",
            "plan_heading": "",
            "plan_description": "",
            "faq_status": "on",
            "faq_title": "Faq",
            "faq_heading": "",
            "faq_description": "",
            "faqs": "",
            "testimonials_status": "on",
            "testimonials_heading": "",
            "testimonials_description": "",
            "testimonials_long_description": "",
            "testimonials": "",
            "footer_status": "on",
            "joinus_status": "on",
            "joinus_heading": "",
            "joinus_description": "",
        }
        for setting in cls.objects.all():
            defaults[setting.name] = setting.value
        return defaults

    @classmethod
    def landing_page_setting(cls) -> dict:
        if not hasattr(cls, '_cached_settings'):
            cls._cached_settings = cls.settings()
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

    @staticmethod
    def key_wise_upload_file(django_request, file_field_name: str, forced_filename: str, path: str, data_key: str, custom_validation=None) -> dict:
        file_obj = None
        if file_field_name in django_request.FILES and data_key in django_request.FILES[file_field_name]:
            file_obj = django_request.FILES[file_field_name][data_key].get(file_field_name)
        if not file_obj:
            return {'flag': 0, 'msg': "File not found in nested structure"}
        save_path = f"{path}/{forced_filename}"
        try:
            saved_path = default_storage.save(save_path, file_obj)
            return {
                'flag': 1,
                'msg': 'success',
                'url': default_storage.url(saved_path)
            }
        except ValidationError as exc:
            return {'flag': 0, 'msg': str(exc)}
        except Exception as exc:
            return {'flag': 0, 'msg': str(exc)}
