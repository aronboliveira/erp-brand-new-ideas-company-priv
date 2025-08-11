import datetime
from django.db import models
from django.utils import timezone
from django.contrib.auth import get_user_model
from .._helpers.connectors.customer_connected import CustomerConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_user_creation, default_char_field, 
                               VOID, DURATION_VALIDATOR)
from .._helpers.connectors.project_connected import ProjectConnected
from .._helpers.connectors.user_connected import UserConnected
User = get_user_model()

class ZoomMeeting(DefaultTimed, CustomerConnected, ProjectConnected, UserConnected):
    id = models.UUIDField(**uuid_def_primary())
    title = default_char_field()
    meeting_id = models.CharField(max_length=36, db_index=True)
    start_date = models.DateTimeField(auto_now_add=True, db_index=True)
    duration = models.DurationField(max_length=4, validators=DURATION_VALIDATOR, help_text="Duration in minutes")
    start_url = models.CharField(max_length=1024, **VOID)
    password = default_char_field(voidable=True)
    join_url = models.CharField(max_length=1024, **VOID)
    status = models.CharField(max_length=50, default='scheduled', choices=[('scheduled', 'Scheduled'), ('started', 'Started'), 
                                                      ('ended', 'Ended'), ('cancelled', 'Cancelled')], 
                              db_index=True, **VOID)
    created_by = default_user_creation('%(class)s_created_by')

    def __str__(self) -> str:
        return self.title

    @property
    def client_name(self) -> str:
        return self.client.name if self.client else ""

    @property
    def project_name(self) -> str:
        return self.project.project_name if self.project and hasattr(self.project, "project_name") else ""

    def check_datetime(self) -> int:
        meeting_end = self.start_date + datetime.timedelta(minutes=self.duration)
        return 1 if meeting_end > timezone.now() else 0

    def get_users(self, users_str: str) -> list:
        user_ids = [uid.strip() for uid in users_str.split(",") if uid.strip()]
        return list(User.objects.filter(id__in=user_ids))

    class Meta:
        db_table = "zoom_meeting"
