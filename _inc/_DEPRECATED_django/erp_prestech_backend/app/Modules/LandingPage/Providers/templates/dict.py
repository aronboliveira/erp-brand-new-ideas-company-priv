TEMPLATES = [
    {
        # ... other settings ...
        'OPTIONS': {
            'context_processors': [
                # Default processors...
                'django.template.context_processors.debug',
                'django.template.context_processors.request',
                'django.contrib.auth.context_processors.auth',
                'django.contrib.messages.context_processors.messages',
                # Add your custom provider:
                'landingpage.providers.add_menu_provider.add_menu',
            ],
        },
    },
]
INSTALLED_APPS = [
    # ... other apps ...
    'landingpage.apps.LandingPageConfig',
]
