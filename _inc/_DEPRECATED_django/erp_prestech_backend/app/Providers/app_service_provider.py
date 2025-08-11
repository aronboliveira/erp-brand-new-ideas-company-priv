from django.apps import AppConfig
from django.db.models import CharField

class AppServiceProvider(AppConfig):
    default_auto_field = 'django.db.models.BigAutoField'
    name = 'myapp'

    def ready(self):
        # Optional: override CharField max_length for MySQL 5.6+ compatibility
        CharField.default_max_length = 191