from django.core.mail import EmailMultiAlternatives
from django.template.loader import render_to_string

class SendDealEmail:
    def __init__(self, d_arr, subject, from_email=None):
        """
        :param d_arr: dict, data to render in the email
        :param subject: str, subject of the email
        :param from_email: optional sender email (e.g., 'Sender <noreply@example.com>')
        """
        self.d_arr = d_arr
        self.subject = subject
        self.from_email = from_email or 'noreply@example.com'

    def build(self):
        """
        Returns an EmailMultiAlternatives object ready to send.
        Assumes template is located at templates/email/deal_mail.html
        """
        # Render the email body using the provided d_arr
        body = render_to_string('email/deal_mail.html', {'dArr': self.d_arr})

        email = EmailMultiAlternatives(
            subject=self.subject,
            body=body,
            from_email=self.from_email,
            to=[],  # to be set by the caller
        )
        return email
