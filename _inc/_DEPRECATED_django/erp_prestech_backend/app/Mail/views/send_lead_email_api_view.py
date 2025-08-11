from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework import status
from ..send_lead_email import SendLeadEmail

class SendLeadEmailAPIView(APIView):
    def post(self, request):
        l_arr = request.data.get('lArr')
        subject = request.data.get('subject')
        recipients = request.data.get('recipients', [])

        if not all([l_arr, subject, recipients]):
            return Response(
                {"error": "Missing lArr, subject, or recipients."},
                status=status.HTTP_400_BAD_REQUEST
            )

        email_builder = SendLeadEmail(l_arr=l_arr, subject=subject)
        email = email_builder.build()
        email.to = recipients
        email.send()

        return Response({"message": "Lead email sent successfully."}, status=status.HTTP_200_OK)
