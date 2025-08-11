from django.db import models
from django.contrib.auth import get_user_model
from .email_template import EmailTemplate
from .._helpers.connectors.user_connected import UserConnected
User = get_user_model()

class UserEmailTemplate(EmailTemplate, UserConnected):
    is_active = models.BooleanField(default=True)

    def __str__(self) -> str:
        return f"Template {self.template.uuid} for {self.user}"

    class Meta:
        db_table = "user_email_template"
