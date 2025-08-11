import os
from pathlib import Path
from django.utils.text import slugify

BASE_DIR = Path(__file__).resolve().parent.parent

##############################################################################
# Default Database Connection
##############################################################################
# In Laravel: 'default' => env('DB_CONNECTION', 'mysql')
# In Django: We name the default connection "default" in the DATABASES dict.
DEFAULT_DB_ENGINE = os.getenv('DB_CONNECTION', 'mysql')  # or 'sqlite', 'pgsql', etc.

##############################################################################
# DATABASES
##############################################################################
# Django defines database connections in a dictionary called DATABASES, where
# each key is a connection alias (commonly "default").
# Below is an example that sets up potential connections for SQLite, MySQL, 
# PostgreSQL, and SQL Server (sqlsrv). Then, we pick which one to treat as 
# "default" based on the environment variable DB_CONNECTION.
##############################################################################

DATABASES = {
    # SQLite
    "sqlite": {
        "ENGINE": "django.db.backends.sqlite3",
        # In Laravel: 'database' => env('DB_DATABASE', database_path('database.sqlite'))
        "NAME": os.getenv("DB_DATABASE", os.path.join(BASE_DIR, "database.sqlite")),
    },

    # MySQL
    "mysql": {
        "ENGINE": "django.db.backends.mysql",
        "NAME": os.getenv("DB_DATABASE", "forge"),
        "USER": os.getenv("DB_USERNAME", "forge"),
        "PASSWORD": os.getenv("DB_PASSWORD", ""),
        "HOST": os.getenv("DB_HOST", "127.0.0.1"),
        "PORT": os.getenv("DB_PORT", "3306"),
        # Extra options can go here, e.g. SSL
        "OPTIONS": {
            # If you have a custom CA cert, you could specify:
            # 'ssl': {'ca': os.getenv('MYSQL_ATTR_SSL_CA')},
            # MySQL "strict" mode or collation can also be set here.
        },
    },

    # PostgreSQL
    "pgsql": {
        "ENGINE": "django.db.backends.postgresql",
        "NAME": os.getenv("DB_DATABASE", "forge"),
        "USER": os.getenv("DB_USERNAME", "forge"),
        "PASSWORD": os.getenv("DB_PASSWORD", ""),
        "HOST": os.getenv("DB_HOST", "127.0.0.1"),
        "PORT": os.getenv("DB_PORT", "5432"),
        # Additional connection settings
        "OPTIONS": {
            # 'sslmode': 'require',
        },
    },

    # SQL Server (sqlsrv in Laravel)
    # Django doesn't have a built-in SQL Server backend, but you can use 
    # third-party packages like mssql-django or django-pyodbc-azure.
    "sqlsrv": {
        "ENGINE": "mssql",
        "NAME": os.getenv("DB_DATABASE", "forge"),
        "USER": os.getenv("DB_USERNAME", "forge"),
        "PASSWORD": os.getenv("DB_PASSWORD", ""),
        "HOST": os.getenv("DB_HOST", "localhost"),
        "PORT": os.getenv("DB_PORT", "1433"),
        "OPTIONS": {
            # Example if using mssql-django:
            # 'driver': 'ODBC Driver 17 for SQL Server',
            # 'encrypt': os.getenv('DB_ENCRYPT', 'yes') == 'yes',
            # 'trust_server_certificate': os.getenv('DB_TRUST_SERVER_CERTIFICATE', 'false') == 'true',
        },
    },
}

# Pick which connection is the Django default based on DB_CONNECTION env variable.
# e.g. If DB_CONNECTION=mysql, then DATABASES["default"] = DATABASES["mysql"].
if DEFAULT_DB_ENGINE in DATABASES:
    DATABASES["default"] = DATABASES[DEFAULT_DB_ENGINE]
else:
    # Fallback to SQLite if the specified engine doesn't match any key.
    DATABASES["default"] = DATABASES["sqlite"]

##############################################################################
# Django Migrations
##############################################################################
# In Laravel, 'migrations' => 'migrations' sets the table name used by Laravel
# for tracking migrations. In Django, migrations are tracked automatically per
# app in separate files under "migrations" directories. There's no direct 
# single “migrations” table to configure. So there's nothing to map here.

##############################################################################
# Redis
##############################################################################
# Laravel uses 'redis' => [...] to define Redis connection details for 
# default and cache. Django doesn't have built-in Redis support, but you
# can use a library like django-redis for caching or channels. Below is
# an example caching config using django-redis.

REDIS_HOST = os.getenv('REDIS_HOST', '127.0.0.1')
REDIS_PORT = os.getenv('REDIS_PORT', '6379')
REDIS_PASSWORD = os.getenv('REDIS_PASSWORD', None)
REDIS_DB_DEFAULT = os.getenv('REDIS_DB', '0')
REDIS_DB_CACHE = os.getenv('REDIS_CACHE_DB', '1')
REDIS_PREFIX = slugify(os.getenv('APP_NAME', 'laravel')) + "_database_"

# Using django-redis for caching (approx. "redis" => ['default', 'cache'] in Laravel):
CACHES = {
    "default": {
        "BACKEND": "django_redis.cache.RedisCache",
        # Typically a single DB for "default" usage:
        "LOCATION": f"redis://:{REDIS_PASSWORD}@{REDIS_HOST}:{REDIS_PORT}/{REDIS_DB_DEFAULT}" if REDIS_PASSWORD
                    else f"redis://{REDIS_HOST}:{REDIS_PORT}/{REDIS_DB_DEFAULT}",
        "OPTIONS": {
            "CLIENT_CLASS": "django_redis.client.DefaultClient",
        },
        "KEY_PREFIX": REDIS_PREFIX,
    },
    # Additional "cache" connection if you want a separate DB index for caching
    "cache_connection": {
        "BACKEND": "django_redis.cache.RedisCache",
        "LOCATION": f"redis://:{REDIS_PASSWORD}@{REDIS_HOST}:{REDIS_PORT}/{REDIS_DB_CACHE}" if REDIS_PASSWORD
                    else f"redis://{REDIS_HOST}:{REDIS_PORT}/{REDIS_DB_CACHE}",
        "OPTIONS": {
            "CLIENT_CLASS": "django_redis.client.DefaultClient",
        },
        "KEY_PREFIX": REDIS_PREFIX,
    },
}

# If you want the "default" to be the "cache" Redis connection, you can do:
# CACHES["default"] = CACHES["cache_connection"]

