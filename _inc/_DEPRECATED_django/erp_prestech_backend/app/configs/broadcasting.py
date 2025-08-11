import os
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent.parent

# -----------------------------------------------------------------------------
# BROADCASTING-LIKE SETTINGS IN DJANGO
# -----------------------------------------------------------------------------

# In Laravel, 'default' => env('BROADCAST_DRIVER', 'null') chooses which
# broadcast driver is used. In Django, there's no single “broadcast driver.”
# You might define something like this:
BROADCAST_DRIVER = os.getenv('BROADCAST_DRIVER', 'null')

# For example, if using Django Channels, you could set:
# CHANNEL_LAYERS = {
#     "default": {
#         "BACKEND": "channels_redis.core.RedisChannelLayer",
#         "CONFIG": {
#             "hosts": [("localhost", 6379)],
#         },
#     },
# }

# Or if you were using Pusher or Ably for real-time updates, you’d configure
# them with your keys here:

PUSHER_APP_KEY = os.getenv('PUSHER_APP_KEY')
PUSHER_APP_SECRET = os.getenv('PUSHER_APP_SECRET')
PUSHER_APP_ID = os.getenv('PUSHER_APP_ID')
PUSHER_HOST = os.getenv('PUSHER_HOST')
PUSHER_PORT = int(os.getenv('PUSHER_PORT', 443))
PUSHER_SCHEME = os.getenv('PUSHER_SCHEME', 'https')

ABLY_KEY = os.getenv('ABLY_KEY')

# If you were using a Python-based Pusher library or Ably library,
# you’d store configuration here, e.g.:

BROADCAST_CONNECTIONS = {
    "pusher": {
        "driver": "pusher",
        "key": PUSHER_APP_KEY,
        "secret": PUSHER_APP_SECRET,
        "app_id": PUSHER_APP_ID,
        "options": {
            "host": PUSHER_HOST,
            "port": PUSHER_PORT,
            "scheme": PUSHER_SCHEME,
            "encrypted": True,
            "useTLS": PUSHER_SCHEME == 'https',
        },
        "client_options": {
            # equivalent to Guzzle client options in Laravel
        },
    },
    "ably": {
        "driver": "ably",
        "key": ABLY_KEY,
    },
    "redis": {
        "driver": "redis",
        "connection": "default",
        # In Django Channels or a custom Redis Pub/Sub, you’d define your
        # Redis config in CHANNEL_LAYERS or a custom manager.
    },
    "log": {
        "driver": "log",
        # If you want to just log all broadcast messages, you can do it here.
    },
    "null": {
        "driver": "null",
        # No-op / disabled broadcast.
    },
}

# Use BROADCAST_DRIVER to pick which block in BROADCAST_CONNECTIONS to use.
ACTIVE_BROADCAST_CONFIG = BROADCAST_CONNECTIONS.get(BROADCAST_DRIVER, BROADCAST_CONNECTIONS["null"])
