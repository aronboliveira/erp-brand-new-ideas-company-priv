# settings.py
import os
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent.parent

# 1) Session Engine
SESSION_DRIVER = os.getenv('SESSION_DRIVER', 'db')  # e.g., 'file', 'db', 'cache'
if SESSION_DRIVER == 'db':
    SESSION_ENGINE = 'django.contrib.sessions.backends.db'
elif SESSION_DRIVER == 'cache':
    SESSION_ENGINE = 'django.contrib.sessions.backends.cache'
elif SESSION_DRIVER == 'cookie':
    SESSION_ENGINE = 'django.contrib.sessions.backends.signed_cookies'
# else if 'file', you'd need a custom backend or third-party library

# 2) Session Lifetime in seconds (Laravel is in minutes)
SESSION_COOKIE_AGE = int(os.getenv('SESSION_LIFETIME', 120)) * 60

# 3) Expire on close
SESSION_EXPIRE_AT_BROWSER_CLOSE = (os.getenv('SESSION_EXPIRE_ON_CLOSE', 'false').lower() == 'true')

# 4) Encryption: Django does signing automatically, no direct "encrypt" analog

# 5) If using 'db', it stores in django_session by default
# You can set a DB alias if needed:
SESSION_DB_ALIAS = os.getenv('SESSION_CONNECTION', 'default')

# 6) No direct 'lottery' config in Django; rely on built-in session cleanup approach

# 7) Cookie settings
SESSION_COOKIE_NAME = os.getenv('SESSION_COOKIE', 'myapp_session')
SESSION_COOKIE_PATH = os.getenv('SESSION_PATH', '/')
SESSION_COOKIE_DOMAIN = os.getenv('SESSION_DOMAIN', None)
SESSION_COOKIE_SECURE = bool(os.getenv('SESSION_SECURE_COOKIE', False))
SESSION_COOKIE_HTTPONLY = True

# same_site can be "None", "Lax", "Strict", or None
SAMESITE_VALUE = os.getenv('SESSION_SAME_SITE')  # e.g., 'none', 'lax', 'strict'
SESSION_COOKIE_SAMESITE = SAMESITE_VALUE if SAMESITE_VALUE else None
