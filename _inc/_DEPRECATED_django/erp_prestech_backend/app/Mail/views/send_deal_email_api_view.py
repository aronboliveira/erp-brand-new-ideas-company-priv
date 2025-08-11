from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework import status
from ..send_deal_email import SendDealEmail

class SendDealEmailAPIView(APIView):
    def post(self, request):
        d_arr = request.data.get('dArr')
        subject = request.data.get('subject')
        recipients = request.data.get('recipients', [])

        if not all([d_arr, subject, recipients]):
            return Response(
                {"error": "Missing dArr, subject, or recipients."},
                status=status.HTTP_400_BAD_REQUEST
            )

        email_builder = SendDealEmail(d_arr=d_arr, subject=subject)
        email = email_builder.build()
        email.to = recipients
        email.send()

        return Response({"message": "Deal email sent successfully."}, status=status.HTTP_200_OK)
