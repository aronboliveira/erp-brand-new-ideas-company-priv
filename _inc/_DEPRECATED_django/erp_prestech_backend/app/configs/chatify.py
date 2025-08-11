import os

# -----------------------------------------------------------------------------
# MESSENGER / CHAT-LIKE SETTINGS
# -----------------------------------------------------------------------------

MESSENGER_NAME = os.getenv('CHATIFY_NAME', 'Messenger')

# The path/prefix used for chat routes in Laravel is `chats`. In Django, 
# you’d typically define this in your `urls.py` (e.g., `path('chats/', ...)`). 
# But you can store it here for convenience.
MESSENGER_PATH = os.getenv('CHATIFY_PATH', 'chats')

# Routes config in Chatify is a list of middleware, a route prefix, and a namespace. 
# In Django, route prefixes are handled in `urls.py`, and middleware is 
# usually applied globally or per-view. So you can store the concept of them here:
MESSENGER_ROUTES = {
    "PREFIX": os.getenv('CHATIFY_ROUTES_PREFIX', 'chats'),
    "MIDDLEWARE": os.getenv('CHATIFY_ROUTES_MIDDLEWARE'),
    "NAMESPACE": os.getenv('CHATIFY_ROUTES_NAMESPACE', 'myproject.mychatapp.views'),
}

# PUSHER CREDENTIALS
# Chatify uses Pusher for realtime communications; in Django, you’d typically 
# configure Pusher or a similar websocket library in your code. 
# We just store environment variables here:
PUSHER_CONFIG = {
    "KEY": os.getenv('PUSHER_APP_KEY'),
    "SECRET": os.getenv('PUSHER_APP_SECRET'),
    "APP_ID": os.getenv('PUSHER_APP_ID'),
    "OPTIONS": {
        "cluster": os.getenv('PUSHER_APP_CLUSTER'),
        "useTLS": os.getenv('PUSHER_APP_USETLS', 'false').lower() == 'true',
    },
}

# USER AVATAR SETTINGS
MESSENGER_USER_AVATAR = {
    "FOLDER": "uploads/avatar",
    "DEFAULT": "avatar.png",
}

# ATTACHMENTS SETTINGS
MESSENGER_ATTACHMENTS = {
    "FOLDER": "attachments",
    # In Laravel Chatify, "download_route_name" is the named route for downloads.
    # In Django, you’d define a route in your urls.py like `path('attachments/<id>/download', ...)`.
    "DOWNLOAD_ROUTE_NAME": "attachments_download",

    # Allowed file extensions
    "ALLOWED_IMAGES": ["png", "jpg", "jpeg", "gif"],
    "ALLOWED_FILES": ["zip", "rar", "txt"],
}
