# landingpage/apps.py
import os
from django.apps import AppConfig
from django.conf import settings

class LandingPageConfig(AppConfig):
    name = 'landingpage'
    verbose_name = 'Landing Page'

    def ready(self):
        # Translations:
        # Django automatically discovers translations placed under a "locale" folder within the app.
        # If you need to add additional paths for translations, you can update settings.LOCALE_PATHS.

        # Configuration:
        # If your module has additional settings, you can either document them for users to add to settings.py
        # or load a default settings file here (though that’s not common in Django).
        default_config_path = os.path.join(os.path.dirname(__file__), 'config', 'landingpage.py')
        if os.path.exists(default_config_path):
            # You could load default settings here (for example, via exec or similar),
            # but usually it’s better to instruct users to include these in their settings.
            pass

        # Templates:
        # Django looks for templates in the "templates" folder of each app (if you use the AppDirectoriesLoader)
        # or in directories listed in TEMPLATES['DIRS'].
        # So as long as your templates are under "landingpage/templates/landingpage", they will be found.

        # Migrations:
        # Django automatically discovers migrations under the "migrations" folder in your app.
        pass
