import os
from pathlib import Path
from django.utils.text import slugify

BASE_DIR = Path(__file__).resolve().parent.parent

# -----------------------------------------------------------------------------
# CACHE
# -----------------------------------------------------------------------------
# In Laravel, 'default' => env('CACHE_DRIVER', 'file')
# In Django, you set a default cache by naming it "default" in the CACHES dict.

DEFAULT_CACHE_DRIVER = os.getenv('CACHE_DRIVER', 'file')

# KEY PREFIX
# In Laravel: 'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_cache_')
# In Django, you can similarly do:
APP_NAME = os.getenv('APP_NAME', 'laravel')
CACHE_PREFIX = os.getenv('CACHE_PREFIX', slugify(APP_NAME) + '_cache_')

# Django defines caches in a single dictionary called CACHES. 
# Each entry is named, e.g. "default", "redis", "memcached", etc.
CACHES = {
    # The default cache (analogous to "default" in Laravel)
    "default": {
        # In Laravel: 'file' => ['driver' => 'file', 'path' => ...]
        # In Django: file-based caching:
        "BACKEND": "django.core.cache.backends.filebased.FileBasedCache",
        # For the path, approximate the Laravel approach:
        "LOCATION": os.path.join(BASE_DIR, "cache_data"),  
        # Prefix is used to avoid collisions across multiple apps / environments
        "KEY_PREFIX": CACHE_PREFIX,
    },

    # "array" store in Laravel. In Django, you can use LocMemCache for in-memory:
    "local_memory": {
        "BACKEND": "django.core.cache.backends.locmem.LocMemCache",
        "LOCATION": "unique-snowflake",
        "KEY_PREFIX": CACHE_PREFIX,
    },

    # "database" store in Laravel. Django has a built-in DB cache:
    "database": {
        "BACKEND": "django.core.cache.backends.db.DatabaseCache",
        # Create this table via 'python manage.py createcachetable cache_table'
        # or your chosen name.  
        "LOCATION": "cache_table",
        "KEY_PREFIX": CACHE_PREFIX,
    },

    # "memcached" store in Laravel => Memcached in Django.
    # If you have memcached running, you can do:
    "memcached": {
        "BACKEND": "django.core.cache.backends.memcached.MemcachedCache",
        # or 'django.core.cache.backends.memcached.PyLibMCCache'
        "LOCATION": [
            # This is an example. You can pass multiple servers in a list
            f"{os.getenv('MEMCACHED_HOST', '127.0.0.1')}:{os.getenv('MEMCACHED_PORT', '11211')}"
        ],
        "KEY_PREFIX": CACHE_PREFIX,
        # Django doesn't support SASL config by default; you'd need a library 
        # or workaround if your Memcached is SASL-protected.
    },

    # "redis" store in Laravel => Django typically uses 'django-redis' package
    # so you'd `pip install django-redis` and configure:
    "redis": {
        "BACKEND": "django_redis.cache.RedisCache",
        "LOCATION": f"redis://{os.getenv('REDIS_HOST', '127.0.0.1')}:{os.getenv('REDIS_PORT', '6379')}/1",
        "OPTIONS": {
            "CLIENT_CLASS": "django_redis.client.DefaultClient",
            # Any connection or lock options can go here:
        },
        "KEY_PREFIX": CACHE_PREFIX,
    },

    # "null" store in Laravel => Django uses 'django.core.cache.backends.dummy.DummyCache'
    # which effectively does nothing (similar to a "null" driver).
    "null": {
        "BACKEND": "django.core.cache.backends.dummy.DummyCache",
        "KEY_PREFIX": CACHE_PREFIX,
    },

    # "apc", "octane", and "dynamodb" are not directly built-in Django equivalents.
    # - APC is a PHP extension, not used in Python.
    # - Octane is a Laravel concurrency server, not applicable to Django.
    # - DynamoDB would require a custom backend or library that uses AWS DynamoDB.
    # If you want DynamoDB, you'd find or build a Django-compatible backend.
}

# Now you can choose the default backend based on CACHE_DRIVER environment var:
# For instance, if CACHE_DRIVER=redis, you might do:
if DEFAULT_CACHE_DRIVER in CACHES:
    CACHES["default"] = CACHES[DEFAULT_CACHE_DRIVER]
