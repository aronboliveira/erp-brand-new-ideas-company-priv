from django.http import JsonResponse

class ApiResponser:
    """
    This mixin provides standardized success and error JSON responses.
    Can be used in any class-based view or utility class that needs to return API responses.
    """

    def success(self, data, message=None, code=200):
        """
        Return a success JSON response. 
        :param data: dict or string
        :param message: optional message string
        :param code: HTTP status code
        :return: JsonResponse
        """
        return JsonResponse({
            'is_success': True,
            'message': message,
            'data': data
        }, status=code)

    def error(self, message=None, code=400, data=None):
        """
        Return an error JSON response.
        :param message: error message string
        :param code: HTTP status code
        :param data: optional data
        :return: JsonResponse
        """
        return JsonResponse({
            'is_success': False,
            'message': message,
            'data': data
        }, status=code)
