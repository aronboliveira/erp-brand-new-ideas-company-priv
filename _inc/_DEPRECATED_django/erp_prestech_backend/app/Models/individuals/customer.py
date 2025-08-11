from django.contrib.auth.models import AbstractBaseUser, PermissionsMixin
from django.db import models
from .._helpers.deliverable import Deliverable
from .._helpers.fields import default_text_field, VALID_MYSQL_MIN_DATE, VOID

class Customer(AbstractBaseUser, PermissionsMixin, Deliverable):
    birth_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], **VOID)
    marketing_consent = models.DateTimeField(help_text="Timestamp when user consented to marketing communications", **VOID)
    newsletter_opt_in = models.BooleanField(default=False)
    preferences_notes = default_text_field(default='No preference notes were written', voidable=True) 
    communication_prefs = models.JSONField(
        default=dict,
        **VOID
    )
    social_profiles = models.JSONField(
        default=dict,
        **VOID
    )
    account = models.ForeignKey('User', on_delete=models.CASCADE, db_index=True, **VOID)
    
    USERNAME_FIELD = 'email'
    REQUIRED_FIELDS = ['name']
    
    def __str__(self) -> str:
        return self.name
    
    def creator_id(self):
        return self.id if getattr(self, "type", None) in ("company", "super admin") else self.created_by
    
    def auth_id(self):
        return self.id
