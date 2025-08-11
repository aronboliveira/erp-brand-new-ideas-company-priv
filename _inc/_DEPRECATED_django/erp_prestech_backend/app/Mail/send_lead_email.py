from django.core.mail import EmailMultiAlternatives
from django.template.loader import render_to_string

class SendLeadEmail:
    def __init__(self, l_arr, subject, from_email=None):
        """
        :param l_arr: dict, data to render in the template
        :param subject: str, email subject
        :param from_email: optional sender email
        """
        self.l_arr = l_arr
        self.subject = subject
        self.from_email = from_email or 'noreply@example.com'

    def build(self):
        """
        Builds an EmailMultiAlternatives email from a template.
        Assumes template at templates/email/lead_mail.html
        """
        body = render_to_string('email/lead_mail.html', {'lArr': self.l_arr})
        
        email = EmailMultiAlternatives(
            subject=self.subject,
            body=body,
            from_email=self.from_email,
            to=[],  # recipients set by caller
        )
        return email
