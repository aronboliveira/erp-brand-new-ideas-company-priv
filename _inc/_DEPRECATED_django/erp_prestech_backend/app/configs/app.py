import os
from pathlib import Path
import django.core.management.utils as utils

# Build paths inside the project like this: BASE_DIR / 'subdir'.
BASE_DIR = Path(__file__).resolve().parent.parent

##############################################################################
# Basic Application Settings
##############################################################################

# Equivalent of 'name' => env('APP_NAME', 'Laravel')
APP_NAME = os.getenv('APP_NAME', 'Django')

# Equivalent of 'env' => env('APP_ENV', 'production')
ENV = os.getenv('APP_ENV', 'production')

# Equivalent of 'debug' => (bool) env('APP_DEBUG', false)
DEBUG = bool(os.getenv('APP_DEBUG', False))

# Equivalent of 'url' => env('APP_URL', 'http://localhost')
BASE_URL = os.getenv('APP_URL', 'http://localhost')

# Equivalent of 'asset_url' => env('ASSET_URL')
ASSET_URL = os.getenv('ASSET_URL')

##############################################################################
# Time and Language Settings
##############################################################################

# Equivalent of 'timezone' => env('TIMEZONE', 'UTC')
TIME_ZONE = os.getenv('TIMEZONE', 'UTC')

# Set Django’s time zone
# If you're using the default, you'd just do:
# TIME_ZONE = 'UTC'

# Equivalent of 'locale' => 'en', 'fallback_locale' => 'en'
# Django’s default language code can be set like this:
LANGUAGE_CODE = 'en-us'

# Django uses the concept of LOCALE_PATHS for translations; you can list 
# additional paths if you have them. The fallback_locale concept is not 
# as common in Django, but you can configure additional languages in 
# LANGUAGES if needed.
# e.g. LANGUAGES = [('en', 'English'), ('fr', 'French'), ...]

##############################################################################
# Secret Key / Encryption Key
##############################################################################

# Equivalent of 'key' => env('APP_KEY') and 'cipher' => 'AES-256-CBC'
# Django requires a SECRET_KEY for cryptographic signing. 
# If not provided, you can generate one as a fallback (not recommended in production).
SECRET_KEY = os.getenv('APP_KEY', utils.get_random_secret_key())

# Laravel's 'cipher' is not directly used in Django, 
# but you could store it here if you need custom encryption logic:
CIPHER = 'AES-256-CBC'

##############################################################################
# Installed Apps (Providers / Packages)
##############################################################################

# In Laravel, "providers" register services and “aliases” define class shortcuts.
# In Django, you add third-party libraries and Django apps to INSTALLED_APPS. 
# Below is a placeholder example that includes default Django apps plus 
# a few hypothetical third-party apps (similar to how you'd install “providers” in Laravel).

INSTALLED_APPS = [
    # Default Django apps
    'django.contrib.admin',
    'django.contrib.auth',
    'django.contrib.contenttypes',
    'django.contrib.sessions',
    'django.contrib.messages',
    'django.contrib.staticfiles',

    # Third-party apps you might install (examples):
    # 'rest_framework',            # If using Django REST Framework
    # 'django_celery_beat',       # If you're using Celery scheduling
    # 'django_celery_results',    # If you're using Celery result backend

    # Your own local apps (replacing "App\Providers\AppServiceProvider", etc.)
    # 'myproject.myapp',
    # 'myproject.another_app',
]

##############################################################################
# Middleware (roughly analogous to some Service Provider tasks)
##############################################################################

MIDDLEWARE = [
    'django.middleware.security.SecurityMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.middleware.common.CommonMiddleware',
    'django.middleware.csrf.CsrfViewMiddleware',
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'django.contrib.messages.middleware.MessageMiddleware',
    'django.middleware.clickjacking.XFrameOptionsMiddleware',
]

##############################################################################
# Maintenance Mode
##############################################################################
# Laravel’s maintenance mode is typically a quick switch in the framework.
# In Django, there’s no built-in “maintenance mode,” but you can simulate it 
# via custom middleware or third-party packages (e.g. django-maintenance-mode).
##############################################################################

# Example if using a package like django-maintenance-mode (just for illustration):
# MAINTENANCE_MODE = False  # or True
# # This would be toggled in your environment or code.

##############################################################################
# Other Common Django Settings
##############################################################################

ROOT_URLCONF = 'myproject.urls'
WSGI_APPLICATION = 'myproject.wsgi.application'

# Database settings, analogous to Laravel’s DatabaseServiceProvider
DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.sqlite3',
        'NAME': BASE_DIR / 'db.sqlite3',
    }
}

# Templates, static files, logging, etc. would also go here

