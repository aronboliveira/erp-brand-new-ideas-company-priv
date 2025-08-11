from django.core.mail import EmailMultiAlternatives
from django.template.loader import render_to_string

class TestMail:
    def __init__(self, from_email=None):
        """
        :param from_email: Optional sender email
        """
        self.subject = 'Mail send for testing purpose.'
        self.from_email = from_email or 'noreply@example.com'

    def build(self):
        """
        Builds a static test email.
        Assumes template at templates/email/test_mail.html
        """
        body = render_to_string('email/test_mail.html')

        email = EmailMultiAlternatives(
            subject=self.subject,
            body=body,
            from_email=self.from_email,
            to=[],  # Recipients to be added when sending
        )
        return email
