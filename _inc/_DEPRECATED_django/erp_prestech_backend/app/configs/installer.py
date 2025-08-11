# settings.py (Django)

import os
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent.parent

INSTALLER_CONFIG = {
    # -------------------------------------------------------------------------
    # Core Requirements
    # -------------------------------------------------------------------------
    # Equivalent to 'core' => ['minPhpVersion' => '8.1'] in Laravel
    # We'll store a minimum Python version or other environment checks
    "core": {
        "min_python_version": (3, 9),  # e.g., 3.9
    },

    # -------------------------------------------------------------------------
    # Final Step Options
    # -------------------------------------------------------------------------
    # In Laravel: 'final' => ['key' => true, 'publish' => false]
    # You can define which final steps you want to take in Django
    "final": {
        "generate_secret_key": True,
        "collect_static": False,  # example placeholder
    },

    # -------------------------------------------------------------------------
    # Requirements
    # -------------------------------------------------------------------------
    # In Laravel, it checks extension_loaded('openssl', 'pdo', etc.).
    # In Django, you might check Python packages or environment checks.
    "requirements": {
        "python_packages": [
            # e.g., you can list packages you want to ensure are installed:
            "openssl",  # in reality, you’d check if “pyOpenSSL” or system openssl is installed
            "requests",
            "psycopg2",  # if you need PostgreSQL
            # etc.
        ],
        "apache": [
            "mod_rewrite",
        ],
    },

    # -------------------------------------------------------------------------
    # Folder Permissions
    # -------------------------------------------------------------------------
    # In Laravel: 'permissions' => [... => '777']
    # Django doesn't typically do an internal check of folder perms, but you can replicate:
    "permissions": {
        "storage/": "777",
        "storage/framework/": "777",
        "storage/logs/": "777",
        "storage/uploads/": "777",
        "bootstrap/cache/": "777",
        "resources/lang/": "777",
    },

    # -------------------------------------------------------------------------
    # Environment Form Wizard Validation
    # -------------------------------------------------------------------------
    # In Laravel: `'environment' => ['form' => ['rules' => [...]]]`.
    # In Django, you'd typically create a Django Form or ModelForm for this.
    # We'll just store some rules to illustrate the structure:
    "environment": {
        "form": {
            "rules": {
                "app_name": {"required": True, "type": str, "max_length": 50},
                "environment": {"required": True, "type": str, "max_length": 50},
                "environment_custom": {"required_if": "environment=other", "max_length": 50},
                "app_debug": {"required": True, "type": str},
                # ... and so on
                # "database_connection": {"required": True, "type": str, "max_length": 50},
                # etc.
            },
        },
    },

    # -------------------------------------------------------------------------
    # Installed Middleware Options
    # -------------------------------------------------------------------------
    # In Laravel: 'installed' => [...]
    # This might define what to do if the app is already installed.
    "installed": {
        "redirectOptions": {
            "route": {"name": "welcome", "data": {}},
            "abort": {"type": "404"},
            "dump": {"data": "Dumping a not found message."},
        },
    },

    # -------------------------------------------------------------------------
    # Selected Installed Middleware Option
    # -------------------------------------------------------------------------
    # In Laravel: 'installedAlreadyAction' => ''
    "installedAlreadyAction": "",  # e.g. 'route', 'abort', 'dump'

    # -------------------------------------------------------------------------
    # Updater Enabled
    # -------------------------------------------------------------------------
    # In Laravel: 'updaterEnabled' => 'true'
    "updaterEnabled": True,  # or bool(os.getenv("UPDATER_ENABLED", True))
}

# forms.py (Django example)
from django import forms
from django.conf import settings

class EnvironmentSetupForm(forms.Form):
    app_name = forms.CharField(max_length=50, required=True)
    environment = forms.CharField(max_length=50, required=True)
    environment_custom = forms.CharField(max_length=50, required=False)
    # ...
    # Then in your clean method, you can handle 'required_if: environment=other', etc.

    def clean_environment_custom(self):
        env_val = self.cleaned_data.get('environment')
        custom_val = self.cleaned_data.get('environment_custom')
        if env_val == 'other' and not custom_val:
            raise forms.ValidationError("This field is required if environment is 'other'.")
        return custom_val
