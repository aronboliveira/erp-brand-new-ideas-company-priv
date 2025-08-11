# somewhere in your code, e.g. google_calendar.py
from django.conf import settings
from google.oauth2 import service_account
from google.auth.transport.requests import Request
from googleapiclient.discovery import build

def get_calendar_service():
    profile_type = settings.GOOGLE_CALENDAR_AUTH_PROFILE
    profile_settings = settings.GOOGLE_CALENDAR_AUTH_PROFILES[profile_type]

    if profile_type == "service_account":
        creds = service_account.Credentials.from_service_account_file(
            profile_settings["credentials_json"]
        )

        # If you want to impersonate a user with domain-wide delegation:
        user_to_impersonate = settings.GOOGLE_CALENDAR_USER_TO_IMPERSONATE
        if user_to_impersonate:
            creds = creds.with_subject(user_to_impersonate)

    elif profile_type == "oauth":
        # Example flow: load from token or do refresh if needed.
        # (Pseudo-code, you might store the token, check expiry, refresh, etc.)
        creds = None
        # ...
        # If you have a saved token in JSON, load it and create credentials object
        # ...
        # If no token or invalid, do OAuth flow, etc.
        # ...
        pass

    # Build the service
    service = build('calendar', 'v3', credentials=creds)
    return service

def list_events():
    service = get_calendar_service()
    # Use the default calendar ID from settings
    calendar_id = settings.GOOGLE_CALENDAR_ID
    events_result = service.events().list(calendarId=calendar_id).execute()
    events = events_result.get('items', [])
    return events
