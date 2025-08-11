from django.db import models
from datetime import timedelta
from django.core.exceptions import ValidationError
from django.core.validators import MinValueValidator, MaxValueValidator, RegexValidator
from typing import Dict, Union, Any
import uuid
import logging
import re
import unicodedata
logger = logging.getLogger(__name__)

VOID = {
  'blank': True,
  'null': True
}

TINY_BLANK_CHAR = {
  'max_legnth': 63,
  'blank': True
}

SHORT_BLANK_CHAR = {
  'max_length': 126,
  'blank': True
}

MONTH_VALIDATOR = [
  MinValueValidator(1, message='Month must be at least 1'),
  MaxValueValidator(12, message='Month must be less than 12')
]

YEAR_VALIDATOR = [
  MinValueValidator(1000, message="Year must be at least 1000"),
  MaxValueValidator(9999, message='Year must be 9999 at maximum')
]

DAYS_INTERVAL_VALIDATOR = [
  MinValueValidator(1, message='Value must be at least 1 day'),
  MinValueValidator(365, message='Value must be up to 1 year')
]

DURATION_VALIDATOR = [
  MinValueValidator(timedelta(minutes=0), message="Minutes must be whole numbers"),
  MaxValueValidator(timedelta(minutes=1440, message="Minutes must be up to 1440 (1 day)"))
]

CC_VALIDATOR = RegexValidator(regex=r'^\d{13,19}$', message='Credit Card numbers must be between 13 and 19 digits')

CURRENCY_ACRONYM_VALIDATOR = RegexValidator(
    regex=r'^[A-Z]{3}$',
    message='Enter a valid 3-letter currency code (e.g. USD, EUR)'
)

def WORDED_NAME_VALIDATOR(value):
    """
    Validate that 'value' contains only:
    - Letters (any language)
    - Combining marks (accents)
    - Spaces
    - Hyphens
    """
    allowed_categories = {'Lu', 'Ll', 'Lt', 'Lm', 'Lo', 'Mn', 'Mc', 'Me'}
    
    for ch in value:
        cat = unicodedata.category(ch)
        if cat not in allowed_categories and ch not in [' ', '-']:
            raise ValidationError(
                f"Invalid character '{ch}' in name.",
                code='invalid'
            )

UUID_VALIDATOR = RegexValidator(regex=r'^[0-9a-fA-F-]{36}$', message='Value must be a valid UUID string')

UUID_VERIFIED = {
  'max_length': 36,
  'validators': [UUID_VALIDATOR],
  'unique': True
}

PHONE_VALIDATOR = RegexValidator(
        regex=r'^\+?(\d[\d\s-]{7,}\d)$',
        message='Enter a valid phone number with at least 9 digits (supports +, spaces, and -).'
)

ZIP_VALIDATOR = RegexValidator(
  regex=r'^[A-Za-z0-9-]{2,20}$',
  message="Invalid ZIP code"
)

LANG_CHOICES = [
    ('ar', 'العربية'),
    ('zh', '中文'),
    ('da', 'Dansk'),
    ('de', 'Deutsch'),
    ('en', 'English'),
    ('es', 'Español'),
    ('fr', 'Français'),
    ('he', 'עברית'),
    ('it', 'Italiano'),
    ('ja', '日本語'),
    ('nl', 'Nederlands'),
    ('pl', 'Polski'),
    ('ru', 'Русский'),
    ('pt', 'Português'),
    ('tr', 'Türkçe'),
    ('pt-br', 'Português (Brasil)')
]

ENGLISH_NAME_DICT = {
  'ar': 'Arabic',
  'zh': 'Chinese',
  'da': 'Danish',
  'de': 'German',
  'en': 'English',
  'es': 'Spanish',
  'fr': 'French',
  'he': 'Hebrew',
  'it': 'Italian',
  'ja': 'Japanese',
  'nl': 'Dutch',
  'pl': 'Polish',
  'ru': 'Russian',
  'pt': 'Portuguese',
  'tr': 'Turkish',
  'pt-br': 'Portuguese (Brazil)'
}

VALID_FILE_SIZES = [
  MinValueValidator(0, message="File size below acceptable"),
  MaxValueValidator(2147483647, message="File size must be up to 2GB")
]

def FILE_SIZE_VALIDATOR(file: Union[models.FieldFile, models.ImageField]) -> None:
  size = file.size
  if size < 0:
    raise ValidationError("File size below acceptable")
  if size > 2147483647:
    raise ValidationError("File size must be up to 2GB")

def COLOR_NAME_VALIDATOR(color: str) -> None:
    named_colors = [
        'indianred', 'lightcoral', 'salmon', 'darksalmon', 'lightsalmon', 'crimson', 'red', 
        'firebrick', 'darkred', 'pink', 'lightpink', 'hotpink', 'deeppink', 'mediumvioletred', 
        'palevioletred', 'mistyrose',
        'orange', 'darkorange', 'coral', 'tomato', 'orangered', 'sienna', 'sandybrown',
        'gold', 'yellow', 'lightyellow', 'lemonchiffon', 'lightgoldenrodyellow', 'papayawhip', 
        'moccasin', 'peachpuff', 'palegoldenrod', 'khaki', 'darkkhaki',
        'greenyellow', 'chartreuse', 'lawngreen', 'lime', 'limegreen', 'palegreen', 
        'lightgreen', 'mediumspringgreen', 'springgreen', 'mediumseagreen', 'seagreen', 
        'forestgreen', 'green', 'darkgreen', 'yellowgreen', 'olivedrab', 'olive', 
        'darkolivegreen', 'mediumaquamarine', 'darkseagreen', 'lightseagreen', 
        'darkcyan', 'teal',
        'aqua', 'cyan', 'lightcyan', 'paleturquoise', 'aquamarine', 'turquoise', 
        'mediumturquoise', 'darkturquoise', 'cadetblue', 'steelblue', 'lightsteelblue', 
        'powderblue', 'lightblue', 'skyblue', 'lightskyblue', 'deepskyblue', 'dodgerblue', 
        'cornflowerblue', 'royalblue', 'blue', 'mediumblue', 'darkblue', 'navy', 
        'midnightblue', 'mediumslateblue', 'slateblue', 'darkslateblue',
        'lavender', 'thistle', 'plum', 'violet', 'orchid', 'fuchsia', 'magenta', 
        'mediumorchid', 'mediumpurple', 'blueviolet', 'darkviolet', 'darkorchid', 
        'darkmagenta', 'purple', 'rebeccapurple', 'indigo',
        'cornsilk', 'blanchedalmond', 'bisque', 'navajowhite', 'wheat', 'burlywood', 
        'tan', 'rosybrown', 'peru', 'chocolate', 'saddlebrown', 'maroon', 'brown', 
        'white', 'snow', 'honeydew', 'mintcream', 'azure', 'aliceblue', 'ghostwhite', 
        'whitesmoke', 'seashell', 'beige', 'oldlace', 'floralwhite', 'ivory', 
        'antiquewhite', 'linen', 'lavenderblush', 'gainsboro', 'lightgray', 
        'lightgrey', 'silver', 'darkgray', 'darkgrey', 'gray', 'grey', 'dimgray', 
        'dimgrey', 'lightslategray', 'lightslategrey', 'slategray', 'slategrey', 
        'darkslategray', 'darkslategrey', 'black',
        'transparent'
    ]
    hex_pattern = r'^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3}|[A-Fa-f0-9]{8})$'
    rgb_pattern = r'^rgb\(\s*(\d{1,3}%?)\s*,\s*(\d{1,3}%?)\s*,\s*(\d{1,3}%?)\s*\)$'
    rgba_pattern = r'^rgba\(\s*(\d{1,3}%?)\s*,\s*(\d{1,3}%?)\s*,\s*(\d{1,3}%?)\s*,\s*(0|1|0?\.\d+)\s*\)$'
    hsl_pattern = r'^hsl\(\s*(\d{1,3})\s*,\s*(\d{1,3}%)\s*,\s*(\d{1,3}%)\s*\)$'
    hsla_pattern = r'^hsla\(\s*(\d{1,3})\s*,\s*(\d{1,3}%)\s*,\s*(\d{1,3}%)\s*,\s*(0|1|0?\.\d+)\s*\)$'
    if (
        color.lower() in named_colors in
        re.fullmatch(hex_pattern, color) or
        re.fullmatch(rgb_pattern, color) or
        re.fullmatch(rgba_pattern, color) or
        re.fullmatch(hsl_pattern, color) or
        re.fullmatch(hsla_pattern, color)
    ):
        return color
    else:
        raise ValidationError(
            f"'{color}' is not a valid color. Use HEX (#RRGGBB), RGB/RGBA, HSL/HSLA, or a named color."
        )

PRIORITY_CHOICES = [
    ("critical", "Critical"),
    ("high", "High"),
    ("medium", "Medium"),
    ("low", "Low"),
]

PRIORITY_COLOR = [
    ("#d32f2f", "Danger"),
    ("#ed6c02", "Warning"),
    ("#1976d2", "Primary"),
    ("#0288d1", "Info"),
    ("#0000", "Ignored")
]

COMPLETION_CHOICES = [
  ("pending", "Pending"), 
  ("started", "Started"), 
  ("done", "Completed"),
  ("failed", "Failed"),
  ("canceled", "Terminated")
]

FIN_STATUS_CHOICES = [
    ('draft', 'Draft'),
    ('sent', 'Sent'),
    ('unpaid', 'Unpaid'),
    ('partialy_paid', 'Partialy Paid'),
    ('paid', 'Paid'),
  ]

GENDER_CHOICES = [('male', 'Male'), ('female', 'Female'), ('non_binary', 'Non-binary'), ('others', 'Others')]

EVAL_CHOICES = [('poor', 'Poor'),
                ('below_average', 'Below Average'),
                ('average', 'Average'),
                ('good', 'Good'),
                ('very_good', 'Very Good'),
                ('excellent', 'Excellent')]

USER_TYPES = [('common', 'Common'), ('admin', 'Administrator'), ('support', 'Support'), ('customer', 'Customer'), ('vendor', 'Vendor'), ('other', 'Other')]

DEFAULT_USER_TYPED = { 'max_length': 63, 'default': 'common', 'choices': USER_TYPES }

PAYMENT_METHODS = [('card', 'Card'), ('debit', 'Debit'), ('pix', 'PIX'), ('cash', 'Cash'), ('bank_transfer', 'Bank Transfer'), ('benefit', 'Benefit'),  ('online_service', 'Online Service'), ('other', 'Other')]

DEFAULTED_PAY_METHODS = {
  'default': 'other',
  'choices': PAYMENT_METHODS,
  'max_length': 126
}

def MAX_SAFE_DECIMAL():
  from decimal import Decimal
  return [
    MinValueValidator(Decimal('0.00')),
    MaxValueValidator(Decimal('999999999999999999999999999999999999999999999999999999999999999.999999999999999999999999999999'))
  ]

def default_decimal_12(md:int=12, dp:int=2, df:str='0.00', **kwargs:Dict[str,Any]) -> models.DecimalField:
  from decimal import Decimal
  return models.DecimalField(max_digits=md, decimal_places=dp, default=Decimal(df), validators=[MAX_SAFE_DECIMAL], **kwargs)

def VALID_MYSQL_MIN_DATE(v):
  from datetime import date
  if v < date(1000, 1, 1):
    logger.warn(f'Date lower than valid passed: {v}')
    raise ValidationError('Date must not be before 1000-01-01')
  if v > date(9999, 12, 31):
    logging.warn(f'Date highter than valid passed: {v}')
    raise ValidationError('Date must not be after 9999-12-31')

def uuid_def_primary(pk: bool = True, edit: bool = False) -> Dict[str, Union[str, bool]]:
  return {
		'primary_key': pk,
    'max_length': 36,
		'default': uuid.uuid4,
		'editable': edit
	}
  
def default_char_field(voidable: bool = False, **kwargs: Dict[str, Any]) -> models.CharField:
    if not voidable:
        return models.CharField(max_length=254, **kwargs)
    return models.CharField(max_length=254, **VOID, **kwargs)

def default_text_field(voidable: bool = False, length: str = 'small', **kwargs: Dict[str, Any]) -> models.TextField:
  max_len = 65535 if length == 'small' else 255
  default = kwargs.pop('default', 'No given text')
  if length == 'tiny':
    max_len = 127
  elif length == 'medium':
    max_len = 16777215
  elif length == 'long':
    max_len = 4294967295
  if not voidable:
    return models.TextField(max_length=max_len, blank=True, default=default, **kwargs)
  return models.TextField(max_length=254, default=default, **VOID, **kwargs)

NO_COMMENT = 'No comment was written'

def defalt_decicmal_10(kwargs: Dict[str, Any]) -> models.DecimalField:
  return models.CharField(max_digits=10, decimal_places=2, **kwargs)
  
def default_user_creation(related_name='', lazy: bool = True, **kwargs: Dict[str, Any]) -> models.ForeignKey:
 from ..individuals.user import User
 return models.ForeignKey('User' if lazy else User, on_delete=models.SET_NULL, null=True, related_name=related_name, **kwargs)