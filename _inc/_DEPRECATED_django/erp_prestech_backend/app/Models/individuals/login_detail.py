from django.db import models
from django.contrib.auth import get_user_model
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_user_creation, VOID

User = get_user_model()

class LoginDetail(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  user = models.ForeignKey(User, on_delete=models.CASCADE, related_name='login_details')
  ip = models.GenericIPAddressField(unpack_ipv4=True)
  date = models.DateTimeField(auto_now_add=True)
  details = models.TextField(**VOID, max_length=65535)
  created_by = default_user_creation('%(class)s_created_by')

  class Meta:
    verbose_name = "Login Detail"
    verbose_name_plural = "Login Details"

  def __str__(self) -> str:
    return f"LoginDetail by {self.user} on {self.date}"
