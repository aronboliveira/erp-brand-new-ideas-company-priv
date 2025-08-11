from django.db import models
from django.core.cache import cache
from typing import Optional
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_char_field, default_user_creation,
                               LANG_CHOICES, ENGLISH_NAME_DICT)

class Language(DefaultTimed):
    id = models.UUIDField(**uuid_def_primary())
    LANGUAGE_CODES = [lang[0] for lang in LANG_CHOICES]
    CODE_CHOICES = [
        (code, (lambda _code: next(
            (name for code_, name in LANG_CHOICES if code_ == _code),
            _code.upper()
        ))(code))
        for code in LANGUAGE_CODES
    ]
    code = models.CharField(max_length=10, unique=True,
                            choices=CODE_CHOICES, default='en')
    NATIVE_NAMES = [lang[1] for lang in LANG_CHOICES]
    ENGLISH_NAMES = [(name, 
                      ENGLISH_NAME_DICT[name] if ENGLISH_NAME_DICT[name] else f'{name[:1].upper()}{name[1:]}'
                      ) for name in NATIVE_NAMES]
    full_name = default_char_field()
    created_by = default_user_creation("%(class)s_created_by")

    @classmethod
    def language_data(cls, code: str) -> Optional["Language"]:
        cache_key = f"language_data_{code}"
        language = cache.get(cache_key)
        if language is None:
            language = cls.objects.filter(code=code).first()
            cache.set(cache_key, language, 86400)
        return language

    def __str__(self) -> str:
        return self.full_name
