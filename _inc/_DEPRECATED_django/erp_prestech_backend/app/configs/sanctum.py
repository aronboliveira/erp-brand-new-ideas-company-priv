import os

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

# 1. Stateful Domains
# In Laravel, Sanctum’s "stateful" key defines the list of domains which will receive stateful API authentication cookies.
# In Django, you can derive your CSRF trusted origins from a similar list.
SANCTUM_STATEFUL_DOMAINS = os.environ.get(
    'SANCTUM_STATEFUL_DOMAINS',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1'
).split(',')

# Django requires a URL scheme (http or https) in CSRF_TRUSTED_ORIGINS.
CSRF_TRUSTED_ORIGINS = [f'http://{domain.strip()}' for domain in SANCTUM_STATEFUL_DOMAINS]

# 2. Guards (Authentication)
# Laravel’s 'guard' => ['web'] implies using session-based authentication.
# In Django (with Django REST Framework), you can use SessionAuthentication as the default.
REST_FRAMEWORK = {
    'DEFAULT_AUTHENTICATION_CLASSES': [
        'rest_framework.authentication.SessionAuthentication',
        # You could also add other authentication classes if needed.
    ],
}

# 3. Expiration
# Sanctum’s 'expiration' is set to null, meaning tokens do not expire.
# If you’re using a token-based authentication system (e.g., JWT), you could set a similar variable.
SANCTUM_EXPIRATION = None

# 4. Middleware
# Laravel Sanctum specifies middleware for verifying CSRF tokens and encrypting cookies.
# Django provides similar functionality through its own middleware.
MIDDLEWARE = [
    'django.middleware.security.SecurityMiddleware',
    'django.contrib.sessions.middleware.SessionMiddleware',
    'django.middleware.common.CommonMiddleware',
    'django.middleware.csrf.CsrfViewMiddleware',  # Verifies CSRF tokens.
    'django.contrib.auth.middleware.AuthenticationMiddleware',
    'django.contrib.messages.middleware.MessageMiddleware',
    # Django does not encrypt cookies by default; if you need that functionality,
    # you can implement a custom middleware or use third‑party packages.
]

# Optionally, you may want to define where user-uploaded files are stored:
MEDIA_ROOT = os.path.join(BASE_DIR, 'media')
MEDIA_URL = '/media/'
