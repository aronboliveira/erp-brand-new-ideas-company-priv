# settings.py
import os
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent.parent

INSTALLED_APPS = [
    # ...
    'corsheaders',  # add this
    # ...
]

MIDDLEWARE = [
    # ...
    'corsheaders.middleware.CorsMiddleware',  # must come before CommonMiddleware
    'django.middleware.common.CommonMiddleware',
    # ...
]

# ------------------------------------------------------------------------------
# CORS Configuration (similar to Laravel's config/cors.php)
# ------------------------------------------------------------------------------

# If you want to allow all methods (GET, POST, PATCH, DELETE, etc.):
CORS_ALLOW_ALL_METHODS = True

# The Laravel config allows '*' for all origins:
CORS_ALLOW_ALL_ORIGINS = True
# Alternatively, if you only want certain domains:
# CORS_ALLOWED_ORIGINS = [
#     "https://example.com",
#     "https://sub.example.com",
# ]

# If you specifically wanted to allow all headers:
CORS_ALLOW_HEADERS = [
    "*",
]
# Or a custom list of headers:
# CORS_ALLOW_HEADERS = [
#     "content-type",
#     "authorization",
#     "x-requested-with",
#     ...
# ]

# If you need to expose any custom headers to the browser:
# E.g., 'exposed_headers' => [] in Laravel corresponds to Django’s:
CORS_EXPOSE_HEADERS = [
    # "Content-Disposition",  # example if you want to expose certain headers
]

# If in Laravel you have 'supports_credentials' => false, 
# in Django you would set:
CORS_ALLOW_CREDENTIALS = False
# (If you wanted to send cookies or Authorization headers cross-site, set this to True.)

# The "max_age" in Laravel indicates the maximum time (in seconds) that the results of a
# preflight request can be cached. The Django config option is:
CORS_PREFLIGHT_MAX_AGE = 0  # 0 means no caching of preflight responses

# If you want certain paths only (like 'api/*') to allow CORS,
# you’d typically handle that in your server routing or more advanced usage 
# of django-cors-headers. For example, you could write a custom middleware 
# or define a regex-based approach if needed.

# By default, django-cors-headers applies to all routes unless further restricted.
