from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework import status
from ..CommonEmailTemplate import CommonEmailTemplate

class SendCommonEmailView(APIView):
    def post(self, request):
        # Extract required data from the POST request
        template_data = request.data.get('template')
        settings_data = request.data.get('settings')
        recipients = request.data.get('recipients', [])
        
        if not all([template_data, settings_data, recipients]):
            return Response(
                {"error": "Missing template, settings, or recipients data."},
                status=status.HTTP_400_BAD_REQUEST
            )

        # Instantiate the email builder class
        email_template = CommonEmailTemplate(template_data, settings_data)
        email_message = email_template.build()
        # Set the recipient list
        email_message.to = recipients
        
        # Send the email
        email_message.send()

        return Response({"message": "Email sent successfully"}, status=status.HTTP_200_OK)
