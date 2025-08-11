import os

# PayTabs configuration
PAYTABS_CONFIG = {
    # Your merchant profile id, as found on your PayTabs Merchant Dashboard.
    "profile_id": os.environ.get("paytabs_profile_id", None),
    # Your Server Key from the PayTabs Merchant Dashboard > Developers > Key Management.
    "server_key": os.environ.get("paytabs_server_key", None),
    # The currency registered with your PayTabs account.
    # Accepted values: 'AED', 'EGP', 'SAR', 'OMR', 'JOD', 'US'
    "currency": os.environ.get("paytabs_currency", None),
    # The region registered with your PayTabs account.
    # Accepted values: 'ARE', 'EGY', 'SAU', 'OMN', 'JOR', 'GLOBAL'
    "region": os.environ.get("paytabs_region", None),
}
