from django.core.mail import EmailMultiAlternatives
from django.template.loader import render_to_string

class CommonEmailTemplate:
    def __init__(self, template, settings):
        """
        :param template: A dict with keys "from", "subject", and "content"
        :param settings: A dict with key "mail_from_address"
        """
        self.template = template
        self.settings = settings

    def build(self):
        """
        Builds an EmailMultiAlternatives message using a markdown template.
        Assumes there is a template file at "email/common_email_template.md"
        in your Django templates directory.
        """
        # Construct the sender: using the display name from the template and the email from settings
        from_email = f"{self.template['from']} <{self.settings['mail_from_address']}>"
        subject = self.template['subject']
        # Render the email body using a markdown template with the provided content
        context = {'content': self.template['content']}
        body = render_to_string("email/common_email_template.md", context)
        # Create the email message instance (recipients can be set later)
        email_message = EmailMultiAlternatives(
            subject=subject,
            body=body,
            from_email=from_email,
            to=[],  # to be set when sending
        )
        return email_message
