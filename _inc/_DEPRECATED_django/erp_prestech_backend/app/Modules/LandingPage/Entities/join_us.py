from django.db import models
from ....Models._helpers.default_timed import DefaultTimed
from ....Models._helpers.fields import uuid_def_primary, def_user_creation

class JoinUs(DefaultTimed):
    id = models.UUIDField(**uuid_def_primary())
    email = models.EmailField(max_length=254,unique=True)
    created_by = def_user_creation('%(class)s_created_by')
    
    class Meta:
        db_table = 'join_us'
        ordering = ('-created_at',)
    
    def __str__(self) -> str:
        return self.email
