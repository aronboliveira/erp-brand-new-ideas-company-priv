from pathlib import Path
import os

BASE_DIR = Path(__file__).resolve().parent.parent
INSTALLED_APPS = [
    'app',
    'django.contrib.admin',
    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.messages',
    'django.contrib.staticfiles',
]
TEMPLATES = [
    {
        'BACKEND': 'django.template.backends.django.DjangoTemplates',
        'DIRS': [BASE_DIR / "app" / "templates"],
        'APP_DIRS': True,
        'OPTIONS': {
            'context_processors': [
                'django.template.context_processors.debug',
                'django.template.context_processors.request',
                'django.contrib.auth.context_processors.auth',
                'django.contrib.messages.context_processors.messages',
            ],
        },
    },
]

# Quick-start development settings - unsuitable for production
# See https://docs.djangoproject.com/en/5.1/howto/deployment/checklist/

# SECURITY WARNING: keep the secret key used in production secret!
SECRET_KEY = os.getenv("SECRET_KEY")

# SECURITY WARNING: don't run with debug turned on in production!
DEBUG = True

ALLOWED_HOSTS = []

MIDDLEWARE = [
    'django.middleware.security.SecurityMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.middleware.common.CommonMiddleware',
    'django.middleware.csrf.CsrfViewMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'django.contrib.messages.middleware.MessageMiddleware',
    'django.middleware.clickjacking.XFrameOptionsMiddleware',
]

ROOT_URLCONF = 'erp_prestech_backend.urls'

WSGI_APPLICATION = 'erp_prestech_backend.wsgi.application'


# Database
# https://docs.djangoproject.com/en/5.1/ref/settings/#databases

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.sqlite3',
        'NAME': BASE_DIR / 'db.sqlite3',
    }
}


# Password validation
# https://docs.djangoproject.com/en/5.1/ref/settings/#auth-password-validators

AUTH_PASSWORD_VALIDATORS = [
    {
        'NAME': 'django.contrib.auth.password_validation.UserAttributeSimilarityValidator',
    },
    {
        'NAME': 'django.contrib.auth.password_validation.MinimumLengthValidator',
    },
    {
        'NAME': 'django.contrib.auth.password_validation.CommonPasswordValidator',
    },
    {
        'NAME': 'django.contrib.auth.password_validation.NumericPasswordValidator',
    },
]


# Internationalization
# https://docs.djangoproject.com/en/5.1/topics/i18n/

LANGUAGE_CODE = 'en-us'

TIME_ZONE = 'UTC'

USE_I18N = True

USE_TZ = True


# Static files (CSS, JavaScript, Images)
# https://docs.djangoproject.com/en/5.1/howto/static-files/

STATIC_URL = 'static/'

# Default primary key field type
# https://docs.djangoproject.com/en/5.1/ref/settings/#default-auto-field

DEFAULT_AUTO_FIELD = 'django.db.models.BigAutoField'

LOGIN_REDIRECT_URL = '/'
EMPLOYEE_HOME = 'hrm-dashboard'
MAINTENANCE_MODE = False
MAINTENANCE_EXCEPT_URLS = [
    r'^status$',         # /status
    r'^api/health$',     # /api/health
]
MIDDLEWARE = [
    # ...
    'yourapp.middleware.PreventRequestsDuringMaintenanceMiddleware',
    # ...
    
]
USE_X_FORWARDED_HOST = True  # Trust X-Forwarded-Host
SECURE_PROXY_SSL_HEADER = ('HTTP_X_FORWARDED_PROTO', 'https')  # e.g. trust X-Forwarded-Proto
ALLOWED_HOSTS = [
    '.example.com',  # trusts example.com and all subdomains
    'localhost',     # if you also need local dev
]
INSTALLED_APPS = [
    # ...
    'your_app.apps.AuthServiceProviderConfig',
    # ...
]
REST_FRAMEWORK = {
    'DEFAULT_THROTTLE_CLASSES': [
        'rest_framework.throttling.UserRateThrottle',
        'rest_framework.throttling.AnonRateThrottle',
    ],
    'DEFAULT_THROTTLE_RATES': {
        'user': '60/minute',
        'anon': '60/minute',
    }
}

import os
from pathlib import Path

THIRD_PARTY_SERVICES = {
    "mailgun": {
        "domain": os.getenv("MAILGUN_DOMAIN"),
        "secret": os.getenv("MAILGUN_SECRET"),
        "endpoint": os.getenv("MAILGUN_ENDPOINT", "api.mailgun.net"),
        "scheme": "https"
    },
    "postmark": {
        "token": os.getenv("POSTMARK_TOKEN"),
    },
    "ses": {
        "key": os.getenv("AWS_ACCESS_KEY_ID"),
        "secret": os.getenv("AWS_SECRET_ACCESS_KEY"),
        "region": os.getenv("AWS_DEFAULT_REGION", "us-east-1"),
    },
    "cashfree": {
        "key": os.getenv("CASHFREE_KEY", ""),
        "secret": os.getenv("CASHFREE_SECRET", ""),
        "url": os.getenv("CASHFREE_URL", "https://sandbox.cashfree.com/pg/orders"),
    },
}
EMAIL_BACKEND = 'anymail.backends.mailgun.EmailBackend'
DEFAULT_FROM_EMAIL = 'noreply@example.com'

ANYMAIL = {
    "MAILGUN_API_KEY": os.getenv("MAILGUN_SECRET"),
    "MAILGUN_SENDER_DOMAIN": os.getenv("MAILGUN_DOMAIN"),
    # etc...
}

BASE_DIR = Path(__file__).resolve().parent.parent

GOOGLE_CALENDAR_AUTH_PROFILE = os.getenv('GOOGLE_CALENDAR_AUTH_PROFILE', 'service_account')

# We define two “profiles” here, similar to Laravel's "auth_profiles" block:
GOOGLE_CALENDAR_AUTH_PROFILES = {
    "service_account": {
        # Path to the JSON containing service account credentials
        "credentials_json": os.getenv(
            "SERVICE_ACCOUNT_CREDENTIALS_JSON",
            os.path.join(BASE_DIR, "storage/googlecalendar/service-account-credentials.json")
        ),
    },
    "oauth": {
        # Path to the JSON file containing OAuth2 credentials
        "credentials_json": os.getenv(
            "OAUTH_CREDENTIALS_JSON",
            os.path.join(BASE_DIR, "storage/app/google-calendar/oauth-credentials.json")
        ),
        # Path to the JSON file containing OAuth2 token
        "token_json": os.getenv(
            "OAUTH_TOKEN_JSON",
            os.path.join(BASE_DIR, "storage/app/google-calendar/oauth-token.json")
        ),
    },
}

# The default calendar ID
GOOGLE_CALENDAR_ID = os.getenv('GOOGLE_CALENDAR_ID', '')

# The user email to impersonate, if any
GOOGLE_CALENDAR_USER_TO_IMPERSONATE = os.getenv('GOOGLE_CALENDAR_IMPERSONATE', None)

import os
from .hashers import CustomBCryptSHA256PasswordHasher, CustomArgon2PasswordHasher

HASH_DRIVER = os.getenv('HASH_DRIVER', 'bcrypt')  # "bcrypt" or "argon"

if HASH_DRIVER == 'bcrypt':
    PASSWORD_HASHERS = [
        'myproject.hashers.CustomBCryptSHA256PasswordHasher',
        # fallback hashers:
        'django.contrib.auth.hashers.PBKDF2PasswordHasher',
        'django.contrib.auth.hashers.PBKDF2SHA1PasswordHasher',
        'django.contrib.auth.hashers.Argon2PasswordHasher',
        'django.contrib.auth.hashers.SCryptPasswordHasher',
    ]
elif HASH_DRIVER == 'argon':
    PASSWORD_HASHERS = [
        'myproject.hashers.CustomArgon2PasswordHasher',
        # fallback hashers:
        'django.contrib.auth.hashers.PBKDF2PasswordHasher',
        'django.contrib.auth.hashers.PBKDF2SHA1PasswordHasher',
        'django.contrib.auth.hashers.BCryptSHA256PasswordHasher',
        'django.contrib.auth.hashers.SCryptPasswordHasher',
    ]
else:
    # fallback to Django's default
    PASSWORD_HASHERS = [
        'django.contrib.auth.hashers.PBKDF2PasswordHasher',
        'django.contrib.auth.hashers.PBKDF2SHA1PasswordHasher',
        'django.contrib.auth.hashers.BCryptSHA256PasswordHasher',
        'django.contrib.auth.hashers.Argon2PasswordHasher',
        'django.contrib.auth.hashers.SCryptPasswordHasher',
    ]

CHAT_GPT_KEY = ''