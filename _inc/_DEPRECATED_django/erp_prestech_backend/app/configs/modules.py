import os
from pathlib import Path

# Define base paths similar to Laravel's base_path() and public_path()
BASE_DIR = Path(__file__).resolve().parent.parent
PUBLIC_DIR = os.path.join(BASE_DIR, "public")

MODULES_CONFIG = {
    # Default module namespace.
    "namespace": "Modules",

    # Module stubs configuration used for generating module files.
    "stubs": {
        "enabled": False,
        "path": os.path.join(BASE_DIR, "vendor", "nwidart", "laravel-modules", "src", "Commands", "stubs"),
        "files": {
            "routes/web": "Routes/web.php",
            "routes/api": "Routes/api.php",
            "views/index": "Resources/views/index.blade.php",
            "views/master": "Resources/views/layouts/master.blade.php",
            "scaffold/config": "Config/config.php",
            "composer": "composer.json",
            "assets/js/app": "Resources/assets/js/app.js",
            "assets/sass/app": "Resources/assets/sass/app.scss",
            "vite": "vite.config.js",
            "package": "package.json",
        },
        "replacements": {
            "routes/web": ["LOWER_NAME", "STUDLY_NAME"],
            "routes/api": ["LOWER_NAME"],
            "vite": ["LOWER_NAME"],
            "json": ["LOWER_NAME", "STUDLY_NAME", "MODULE_NAMESPACE", "PROVIDER_NAMESPACE"],
            "views/index": ["LOWER_NAME"],
            "views/master": ["LOWER_NAME", "STUDLY_NAME"],
            "scaffold/config": ["STUDLY_NAME"],
            "composer": [
                "LOWER_NAME",
                "STUDLY_NAME",
                "VENDOR",
                "AUTHOR_NAME",
                "AUTHOR_EMAIL",
                "MODULE_NAMESPACE",
                "PROVIDER_NAMESPACE",
            ],
        },
        "gitkeep": True,
    },

    # Paths used by the module system.
    "paths": {
        # Location to store generated modules.
        "modules": os.path.join(BASE_DIR, "Modules"),
        # Location for module assets.
        "assets": os.path.join(PUBLIC_DIR, "Modules"),
        # Location where migrations are published.
        "migration": os.path.join(BASE_DIR, "database", "migrations"),
        # Generator paths: customize where different module folders will be generated.
        "generator": {
            "config": {"path": "Config", "generate": True},
            "command": {"path": "Console", "generate": True},
            "migration": {"path": "Database/Migrations", "generate": True},
            "seeder": {"path": "Database/Seeders", "generate": True},
            "factory": {"path": "Database/factories", "generate": True},
            "model": {"path": "Entities", "generate": True},
            "routes": {"path": "Routes", "generate": True},
            "controller": {"path": "Http/Controllers", "generate": True},
            "filter": {"path": "Http/Middleware", "generate": True},
            "request": {"path": "Http/Requests", "generate": True},
            "provider": {"path": "Providers", "generate": True},
            "assets": {"path": "Resources/assets", "generate": True},
            "lang": {"path": "Resources/lang", "generate": True},
            "views": {"path": "Resources/views", "generate": True},
            "test": {"path": "Tests/Unit", "generate": True},
            "test-feature": {"path": "Tests/Feature", "generate": True},
            "repository": {"path": "Repositories", "generate": False},
            "event": {"path": "Events", "generate": False},
            "listener": {"path": "Listeners", "generate": False},
            "policies": {"path": "Policies", "generate": False},
            "rules": {"path": "Rules", "generate": False},
            "jobs": {"path": "Jobs", "generate": False},
            "emails": {"path": "Emails", "generate": False},
            "notifications": {"path": "Notifications", "generate": False},
            "resource": {"path": "Transformers", "generate": False},
            "component-view": {"path": "Resources/views/components", "generate": False},
            "component-class": {"path": "View/Components", "generate": False},
        },
    },

    # Package commands that the module system exposes.
    "commands": [
        "CommandMakeCommand",
        "ComponentClassMakeCommand",
        "ComponentViewMakeCommand",
        "ControllerMakeCommand",
        "DisableCommand",
        "DumpCommand",
        "EnableCommand",
        "EventMakeCommand",
        "JobMakeCommand",
        "ListenerMakeCommand",
        "MailMakeCommand",
        "MiddlewareMakeCommand",
        "NotificationMakeCommand",
        "ProviderMakeCommand",
        "RouteProviderMakeCommand",
        "InstallCommand",
        "ListCommand",
        "ModuleDeleteCommand",
        "ModuleMakeCommand",
        "FactoryMakeCommand",
        "PolicyMakeCommand",
        "RequestMakeCommand",
        "RuleMakeCommand",
        "MigrateCommand",
        "MigrateFreshCommand",
        "MigrateRefreshCommand",
        "MigrateResetCommand",
        "MigrateRollbackCommand",
        "MigrateStatusCommand",
        "MigrationMakeCommand",
        "ModelMakeCommand",
        "PublishCommand",
        "PublishConfigurationCommand",
        "PublishMigrationCommand",
        "PublishTranslationCommand",
        "SeedCommand",
        "SeedMakeCommand",
        "SetupCommand",
        "UnUseCommand",
        "UpdateCommand",
        "UseCommand",
        "ResourceMakeCommand",
        "TestMakeCommand",
        "LaravelModulesV6Migrator",
    ],

    # Scan configuration: define folders that should be scanned for modules.
    "scan": {
        "enabled": False,
        "paths": [
            os.path.join(BASE_DIR, "vendor", "*", "*"),
        ],
    },

    # Composer file template configuration (metadata).
    "composer": {
        "vendor": "nwidart",
        "author": {
            "name": "Nicolas Widart",
            "email": "n.widart@gmail.com",
        },
        "composer_output": False,
    },

    # Caching configuration for the module system.
    "cache": {
        "enabled": False,
        "driver": "file",
        "key": "laravel-modules",
        "lifetime": 60,
    },

    # Registration settings: which custom namespaces should be auto‑registered.
    "register": {
        "translations": True,
        # Load files on boot or within a register method. (Note: boot is not compatible with some systems.)
        "files": "register",
    },

    # Activators: configuration for module activation. For example, a file‑based activator.
    "activators": {
        "file": {
            # Reference to your activator class (you would implement a corresponding Python class).
            "class": "FileActivator",  # Replace with your Python activator class reference.
            "statuses_file": os.path.join(BASE_DIR, "modules_statuses.json"),
            "cache_key": "activator.installed",
            "cache_lifetime": 604800,
        },
    },

    # Select the default activator.
    "activator": "file",
}
