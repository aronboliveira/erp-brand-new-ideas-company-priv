from django.db import models
from ..contact.contact import Contact
from .._helpers.connectors.user_connected import UserConnected
from .._helpers.fields import default_char_field, VOID
from django.contrib.auth import get_user_model

User = get_user_model()

class UserContact(Contact, UserConnected):
  parent = models.ForeignKey(
    User,
    on_delete=models.CASCADE,
    related_name='child_contacts',
    db_column='parent_id',
    db_index=True
  )
  role = default_char_field()
  job_title = default_char_field(voidable=True)
  notes = models.TextField(**VOID, max_length=65535)
  preferred_contact_method = models.CharField(
      max_length=50,
      choices=[('email', 'Email'), ('phone', 'Phone'), ('whatsapp', 'WhatsApp'), ('other', 'Other')],
      default='email'
  )
  last_contacted_at = models.DateTimeField(**VOID)

  class Meta:
    db_table = 'user_contacts'

  def __str__(self) -> str:
    return f"UserContact(user_id={self.user.id}, role='{self.role}')"
