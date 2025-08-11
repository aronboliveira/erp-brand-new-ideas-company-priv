from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework import status
from ..TestMail import TestMail

class TestMailAPIView(APIView):
    def post(self, request):
        recipients = request.data.get('recipients', [])

        if not recipients:
            return Response(
                {"error": "Missing recipients."},
                status=status.HTTP_400_BAD_REQUEST
            )

        email_builder = TestMail()
        email = email_builder.build()
        email.to = recipients
        email.send()

        return Response({"message": "Test email sent successfully."}, status=status.HTTP_200_OK)
