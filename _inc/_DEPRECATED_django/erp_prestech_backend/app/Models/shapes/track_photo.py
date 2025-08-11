from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import (uuid_def_primary, default_user_creation, 
                               UUID_VERIFIED, VOID)
from .._helpers.connectors.user_connected import UserConnected
class TrackPhoto(DefaultTimed, UserConnected):
    id = models.UUIDField(**uuid_def_primary())
    track_id = models.CharField(**UUID_VERIFIED, **VOID, db_index=True)
    img_path = models.URLField(max_length=200, **VOID)
    time = models.CharField(max_length=128, **VOID)
    status = models.SmallIntegerField(default=0, choices=[(0, 'Pending review'), (1, 'Approved'), (2, 'Rejected'), 
                                                          (3, 'Archived'), (4, 'Deleted')], db_index=True)
    created_by = default_user_creation("%(class)s_created_by", db_index=True)

    def __str__(self) -> str:
        return f"TrackPhoto {self.id}"
