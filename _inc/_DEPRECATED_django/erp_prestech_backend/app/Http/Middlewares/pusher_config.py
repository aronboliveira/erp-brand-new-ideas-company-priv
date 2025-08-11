import logging
from django.http import HttpRequest
from django.utils.deprecation import MiddlewareMixin
from ...Models.utils.utility import Utility

logger = logging.getLogger(__name__)

class PusherConfig(MiddlewareMixin):
  def process_request(self, request: HttpRequest) -> None:
    try:
      settings_dict = Utility.settings_by_id(1)
      if settings_dict:
        request.pusher_config = {
          'key': settings_dict.get('pusher_app_key', ''),
          'secret': settings_dict.get('pusher_app_secret', ''),
          'app_id': settings_dict.get('pusher_app_id', ''),
          'cluster': settings_dict.get('pusher_app_cluster', ''),
        }
    except Exception as e:
      logger.error("Failed in PusherConfig.process_request: %s", e)
