import base64
import json
import logging
from datetime import datetime
import requests
from django.conf import settings
from ..Models.utils.utility import Utility  # Custom utility to fetch user settings, e.g., Utility::settings()

logger = logging.getLogger(__name__)

class ZoomMeetingBase:
    MEETING_TYPE_SCHEDULE = 2  # Based on Zoom API

    def __init__(self):
        self.client = requests.Session()
        self.meeting_url = "https://api.zoom.us/v2/"

    def retrieve_zoom_url(self):
        return self.meeting_url

    def to_zoom_time_format(self, date_time: str):
        try:
            date = datetime.fromisoformat(date_time)
            return date.strftime('%Y-%m-%dT%H:%M:%S')
        except Exception as e:
            logger.error(f"ZoomMeetingBase.to_zoom_time_format: {str(e)}")
            return ''

    def create_meeting(self, user_id, data):
        path = "users/me/meetings"
        url = self.retrieve_zoom_url() + path
        headers = self.get_headers(user_id)

        payload = {
            "topic": data.get("title"),
            "type": self.MEETING_TYPE_SCHEDULE,
            "start_time": self.to_zoom_time_format(data.get("start_time")),
            "duration": data.get("duration"),
            "password": data.get("password"),
            "agenda": data.get("agenda") or None,
            "timezone": "Asia/Kolkata",
            "settings": {
                "host_video": data.get("host_video") == "1",
                "participant_video": data.get("participant_video") == "1",
                "waiting_room": True,
            },
        }

        response = self.client.post(url, headers=headers, data=json.dumps(payload))
        return self._process_response(response, expected_code=201)

    def update_meeting(self, user_id, meeting_id, data):
        path = f"meetings/{meeting_id}"
        url = self.retrieve_zoom_url() + path
        headers = self.get_headers(user_id)

        payload = {
            "topic": data.get("title"),
            "type": self.MEETING_TYPE_SCHEDULE,
            "start_time": self.to_zoom_time_format(data.get("start_time")),
            "duration": data.get("duration"),
            "agenda": data.get("agenda") or None,
            "timezone": settings.TIME_ZONE,
            "settings": {
                "host_video": data.get("host_video") == "1",
                "participant_video": data.get("participant_video") == "1",
                "waiting_room": True,
            },
        }

        response = self.client.patch(url, headers=headers, data=json.dumps(payload))
        return self._process_response(response, expected_code=204)

    def get_meeting(self, user_id, meeting_id):
        path = f"meetings/{meeting_id}"
        url = self.retrieve_zoom_url() + path
        headers = self.get_headers(user_id)

        response = self.client.get(url, headers=headers)
        return self._process_response(response)

    def delete_meeting(self, user_id, meeting_id):
        path = f"meetings/{meeting_id}"
        url = self.retrieve_zoom_url() + path
        headers = self.get_headers(user_id)

        response = self.client.delete(url, headers=headers)
        return {
            "success": response.status_code == 204
        }

    def get_headers(self, user_id):
        token = self.get_token(user_id)
        return {
            "Authorization": f"Bearer {token}",
            "Content-Type": "application/json",
            "Accept": "application/json"
        }

    def get_token(self, user_id):
        settings_dict = Utility.get_user_settings(user_id)  # Implement this function

        account_id = settings_dict.get("zoom_account_id")
        client_id = settings_dict.get("zoom_client_id")
        client_secret = settings_dict.get("zoom_client_secret")

        if account_id and client_id and client_secret:
            auth_header = base64.b64encode(f"{client_id}:{client_secret}".encode()).decode()
            headers = {
                "Authorization": f"Basic {auth_header}"
            }
            form = {
                "grant_type": "account_credentials",
                "account_id": account_id
            }

            response = self.client.post("https://zoom.us/oauth/token", headers=headers, data=form)
            if response.status_code == 200:
                token = response.json().get("access_token")
                return token

        return None

    def _process_response(self, response, expected_code=200):
        return {
            "success": response.status_code == expected_code,
            "data": response.json() if response.content else {}
        }
