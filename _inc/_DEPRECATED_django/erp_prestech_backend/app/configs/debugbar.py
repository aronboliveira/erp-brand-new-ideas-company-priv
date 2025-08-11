import os
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent.parent

# Debug Toolbar should only be enabled in DEBUG mode
DEBUG = True if os.getenv('APP_DEBUG', 'false').lower() == 'true' else False

# In Laravel debugbar: 'enabled' => env('DEBUGBAR_ENABLED', null) 
# If you want a separate override for debug toolbar:
DEBUG_TOOLBAR_ENABLED = os.getenv('DEBUGBAR_ENABLED', 'null')
if DEBUG_TOOLBAR_ENABLED.lower() in ['true', '1']:
    # Force enable
    DEBUG = True
elif DEBUG_TOOLBAR_ENABLED.lower() in ['false', '0']:
    # Force disable
    DEBUG = False

INSTALLED_APPS = [
    # ...
    'debug_toolbar',  # django-debug-toolbar
    # ...
]

MIDDLEWARE = [
    # ...
    'debug_toolbar.middleware.DebugToolbarMiddleware',  # must be near the top
    # ...
]

##############################################################################
# Debug Toolbar Configuration
##############################################################################
# This is where we configure which panels to include, etc.

# If certain URLs in Laravel are “except” from debug bar (like 'telescope*', 'horizon*'), 
# you can skip injecting the debug toolbar for certain paths in Django with a custom check 
# in INTERNAL_IPS or in show_toolbar callback.

INTERNAL_IPS = [
    # Typically 127.0.0.1 or your container IP if you're using Docker.
    "127.0.0.1",
]

# django-debug-toolbar settings:
DEBUG_TOOLBAR_CONFIG = {
    # In Laravel debugbar: 'inject' => true. 
    # Django’s debug-toolbar auto-injects by default, but we can fine-tune how it’s shown.
    'SHOW_TOOLBAR_CALLBACK': 'myproject.settings.show_toolbar',  # or set to None if you want the default
    
    # "except" in Laravel might skip certain routes from showing the debugbar.
    # In Django, you typically handle that in the callback or via middleware logic.

    # If you want to disable the debug toolbar for AJAX, you can do so with:
    # 'SHOW_TOOLBAR_CALLBACK': lambda request: not request.is_ajax(),

    # Themes: django-debug-toolbar doesn’t provide a built-in “dark” or “light” theme, 
    # but you can override CSS or use third-party solutions if you want theming. 
    # So 'theme' => 'auto' isn’t directly supported by django-debug-toolbar.
    
    # Timelines, SQL panels, request info, etc. are handled by the built-in or custom panels.
}

# If you want to exclude certain paths (similar to how 'except' => ['telescope*', 'horizon*']), 
# you can use a custom function:
def show_toolbar(request):
    # Example: if the path starts with "/horizon" or "/telescope", hide the toolbar
    if request.path.startswith("/horizon") or request.path.startswith("/telescope"):
        return False
    return DEBUG  # Show if DEBUG is True

# For controlling which panels show up, you use DEBUG_TOOLBAR_PANELS:
DEBUG_TOOLBAR_PANELS = [
    'debug_toolbar.panels.versions.VersionsPanel',
    'debug_toolbar.panels.timer.TimerPanel',
    'debug_toolbar.panels.settings.SettingsPanel',
    'debug_toolbar.panels.headers.HeadersPanel',
    'debug_toolbar.panels.request.RequestPanel',
    'debug_toolbar.panels.sql.SqlPanel',
    'debug_toolbar.panels.staticfiles.StaticFilesPanel',
    'debug_toolbar.panels.templates.TemplatesPanel',
    'debug_toolbar.panels.cache.CachePanel',
    'debug_toolbar.panels.signals.SignalsPanel',
    'debug_toolbar.panels.logging.LoggingPanel',
    'debug_toolbar.panels.redirects.RedirectsPanel',
    # Add or remove panels based on your needs
]

# Some advanced options in Laravel debugbar for collecting mail, events, etc. 
# can be handled via additional django-debug-toolbar panels or custom panels.

##############################################################################
# Additional or analogous debug functionalities
##############################################################################
# Storage settings for debug data (like 'storage' => ['driver' => 'file', ...]) 
# are not standard in django-debug-toolbar. The debug toolbar typically collects 
# data in-memory for each request. There's no built-in concept of storing past 
# data for retrieval. If you need that, you'd look at third-party tools or logs.

# "capture_ajax" => true in Laravel debugbar
# django-debug-toolbar automatically shows AJAX requests in the panel if 
# they contain the right headers. If you want to measure them differently, 
# you might configure the panel or a custom approach.

# "collectors" => [...] in Laravel is somewhat analogous to the debug toolbar panels 
# you enable in DEBUG_TOOLBAR_PANELS. There's no 1:1 match, but the idea is the same: 
# you decide what data is collected.

# If you want a “dark theme,” you’d override debug-toolbar’s CSS 
# or look for a community theme. There's no built-in theme switch for django-debug-toolbar.

##############################################################################
# URLs
##############################################################################
# You need to include debug_toolbar.urls in your project’s URL patterns:

# urls.py
# from django.urls import path, include
# from django.conf import settings
#
# urlpatterns = [
#     # ...
# ]
#
# if settings.DEBUG:
#     import debug_toolbar
#     urlpatterns += [path('__debug__/', include(debug_toolbar.urls))]

