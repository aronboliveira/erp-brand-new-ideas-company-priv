# asgi.py

import os
from django.core.asgi import get_asgi_application
from ..Http.Middlewares.Authenticate import Authenticate
from channels.routing import ProtocolTypeRouter, URLRouter
import your_app.routing

os.environ.setdefault("DJANGO_SETTINGS_MODULE", "your_project.settings")

application = ProtocolTypeRouter({
    "http": get_asgi_application(),
    "websocket": Authenticate(
        URLRouter(
            your_app.routing.websocket_urlpatterns
        )
    ),
})
