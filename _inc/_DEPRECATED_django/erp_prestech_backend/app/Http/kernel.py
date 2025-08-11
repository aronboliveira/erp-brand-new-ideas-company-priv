# kernel.py

class Kernel:
    """
    This class emulates the structure of a Laravel HTTP Kernel in a Django context.
    In Django, middleware are typically configured in settings.py via the MIDDLEWARE list.
    The following lists are provided as a reference for mapping Laravel middleware
    into Django equivalents. You must implement these middleware classes according
    to Django's middleware API.
    """

    # Global HTTP middleware stack.
    # These middleware run on every request.
    middleware = [
        # 'app.middleware.TrustProxiesMiddleware',  # Uncomment if implemented
        'app.middleware.HandleCorsMiddleware',
        'app.middleware.PreventRequestsDuringMaintenanceMiddleware',
        'app.middleware.ValidatePostSizeMiddleware',
        'app.middleware.TrimStringsMiddleware',
        'app.middleware.ConvertEmptyStringsToNullMiddleware',
    ]

    # Middleware groups for different types of routes.
    middleware_groups = {
        'web': [
            'app.middleware.EncryptCookiesMiddleware',
            # In Laravel, AddQueuedCookiesToResponse is used; in Django, you might handle cookies in your response middleware
            'django.middleware.http.ConditionalGetMiddleware',  # as a placeholder
            'django.contrib.sessions.middleware.SessionMiddleware',
            'app.middleware.ShareErrorsFromSessionMiddleware',
            'django.middleware.csrf.CsrfViewMiddleware',  # Equivalent to VerifyCsrfToken
            'app.middleware.SubstituteBindingsMiddleware',
        ],
        'api': [
            # Optionally include a stateful authentication middleware if needed
            # 'app.middleware.EnsureFrontendRequestsAreStatefulMiddleware',  # as in Laravel (commented out)
            'app.middleware.ThrottleMiddleware',  # Representing throttle:api functionality
            'app.middleware.SubstituteBindingsMiddleware',
        ],
    }

    # Route middleware that can be applied individually or to groups.
    # In Django you might apply these via view decorators.
    route_middleware = {
        'auth': 'app.middleware.AuthenticateMiddleware',
        'auth.basic': 'app.middleware.AuthenticateWithBasicAuthMiddleware',
        'auth.session': 'django.contrib.sessions.middleware.SessionMiddleware',  # or a custom implementation
        'cache.headers': 'app.middleware.SetCacheHeadersMiddleware',
        'can': 'app.middleware.AuthorizeMiddleware',
        'guest': 'app.middleware.RedirectIfAuthenticatedMiddleware',
        'password.confirm': 'app.middleware.RequirePasswordMiddleware',
        'signed': 'app.middleware.ValidateSignatureMiddleware',
        'throttle': 'app.middleware.ThrottleRequestsMiddleware',
        'verified': 'app.middleware.EnsureEmailIsVerifiedMiddleware',
        'XSS': 'app.middleware.XSSMiddleware',
        'revalidate': 'app.middleware.RevalidateBackHistoryMiddleware',
        'pusher': 'app.middleware.PusherConfigMiddleware',
    }
