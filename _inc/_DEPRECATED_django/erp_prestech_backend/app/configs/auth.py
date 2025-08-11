from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent.parent

# -----------------------------------------------------------------------------
# Django Authentication Settings
# -----------------------------------------------------------------------------

# This replaces the idea of 'defaults' => ['guard' => 'web', 'passwords' => 'users']
# In Django, you typically have a single set of default authentication behaviors:
AUTHENTICATION_BACKENDS = [
    # The default backend that uses the main User model (session-based).
    'django.contrib.auth.backends.ModelBackend',

    # You could add custom backends here if you want to treat “customers” or
    # “vendors” differently (similar to different Laravel guards).
    # Example:
    # 'myproject.auth_backends.CustomerBackend',
    # 'myproject.auth_backends.VendorBackend',
]

# Typically you define your primary user model here. If you have a custom user,
# use it instead of the default. This is roughly analogous to the 'provider' => 'users'
AUTH_USER_MODEL = 'myapp.User'  # e.g., 'App\\Models\\User' in Laravel

# -----------------------------------------------------------------------------
# Handling Multiple "Guards" in Django
# -----------------------------------------------------------------------------
# In Laravel, you have multiple guards like "web", "customer", "vendor", "api".
# In Django, you either define multiple backends or a single backend with
# multiple user roles. For example, if you wanted token-based auth for an API,
# you'd commonly use Django REST Framework:
#
# REST_FRAMEWORK = {
#     'DEFAULT_AUTHENTICATION_CLASSES': [
#         'rest_framework.authentication.TokenAuthentication',
#         # or JWT, Session, etc.
#     ],
# }
#
# That covers the idea of 'guard' => 'api' => 'driver' => 'token'.

# -----------------------------------------------------------------------------
# Password Reset & Authentication Expiry
# -----------------------------------------------------------------------------
# Laravel’s 'passwords' => [ 'users' => ... ] config is about how password
# resets are stored or expire. In Django, password resets are typically
# handled by built-in views and tokens (django.contrib.auth.tokens).
# There’s no direct database “password_resets” table by default. If you
# need to store them differently, you can implement a custom solution.
#
# The nearest built-in approach is the standard Django password reset flow:
PASSWORD_RESET_TIMEOUT = 60 * 60  # 60 minutes in seconds (Django 3.1+ uses seconds)

# If you wanted to specify a separate throttle or different reset strategies,
# you would do so using custom forms, a custom manager, or third-party packages.

# Laravel’s 'password_timeout' => 10800 means after 3 hours the user must
# re-enter their password. Django does not have an exact built-in
# “confirm password after X seconds” mechanism, but you could implement 
# it with custom middleware or views if needed.

PASSWORD_RESET_TIMEOUT = 3600  # 1 hour example
# For a “confirmation timeout,” you might approximate by session expiry settings.

# -----------------------------------------------------------------------------
# Example of Additional Models for “customers” or “vendors”
# -----------------------------------------------------------------------------
# If you want separate user tables the way Laravel might handle “providers” => [“customers”, “vendors”],
# you might define two additional models, e.g., models.Customer and models.Vendor,
# plus custom backends. A minimal example:

# AUTHENTICATION_BACKENDS = [
#     'django.contrib.auth.backends.ModelBackend',  # Default for main "User"
#     'myproject.auth_backends.CustomerBackend',    # Could log in as a "Customer"
#     'myproject.auth_backends.VendorBackend',      # Could log in as a "Vendor"
# ]

# Then in myproject/auth_backends.py you might define something like:
#
# from django.contrib.auth.backends import BaseBackend
# from myproject.models import Customer, Vendor
#
# class CustomerBackend(BaseBackend):
#     def authenticate(self, request, username=None, password=None, **kwargs):
#         try:
#             user = Customer.objects.get(username=username)
#         except Customer.DoesNotExist:
#             return None
#         if user.check_password(password):
#             return user
#         return None
#
#     def get_user(self, user_id):
#         try:
#             return Customer.objects.get(pk=user_id)
#         except Customer.DoesNotExist:
#             return None
#
# class VendorBackend(BaseBackend):
#     def authenticate(self, request, username=None, password=None, **kwargs):
#         try:
#             user = Vendor.objects.get(username=username)
#         except Vendor.DoesNotExist:
#             return None
#         if user.check_password(password):
#             return user
#         return None
#
#     def get_user(self, user_id):
#         try:
#             return Vendor.objects.get(pk=user_id)
#         except Vendor.DoesNotExist:
#             return None

# With these custom backends, you approximate Laravel’s concept of different
# providers and guards pointing to different tables/models.

# -----------------------------------------------------------------------------
# That’s the basic structure. Additional details like tokens, “hash” => false,
# or expiry times for tokens would typically be done in DRF or a custom
# authentication library.
