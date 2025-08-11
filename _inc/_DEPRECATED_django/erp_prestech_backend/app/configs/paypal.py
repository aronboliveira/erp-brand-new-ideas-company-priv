import os

PAYPAL_CONFIG = {
    # The mode, which can be 'sandbox' or 'live'
    "mode": os.environ.get("PAYPAL_MODE", "sandbox"),

    "sandbox": {
        "client_id": os.environ.get("PAYPAL_SANDBOX_CLIENT_ID", ""),
        "client_secret": os.environ.get("PAYPAL_SANDBOX_CLIENT_SECRET", ""),
        # Static app id for sandbox as per PayPal docs
        "app_id": "APP-80W284485P519543T",
    },

    "live": {
        "client_id": os.environ.get("PAYPAL_LIVE_CLIENT_ID", ""),
        "client_secret": os.environ.get("PAYPAL_LIVE_CLIENT_SECRET", ""),
        "app_id": os.environ.get("PAYPAL_LIVE_APP_ID", ""),
    },

    # The payment action, typically 'Sale', 'Authorization', or 'Order'
    "payment_action": os.environ.get("PAYPAL_PAYMENT_ACTION", "Sale"),

    # The default currency (e.g., 'USD')
    "currency": os.environ.get("PAYPAL_CURRENCY", "USD"),

    # The URL for payment notifications
    "notify_url": os.environ.get("PAYPAL_NOTIFY_URL", ""),

    # The locale for the gateway (e.g., 'en_US')
    "locale": os.environ.get("PAYPAL_LOCALE", "en_US"),

    # Whether to validate SSL when creating the API client.
    "validate_ssl": os.environ.get("PAYPAL_VALIDATE_SSL", "true").lower() in ["true", "1"],
}
