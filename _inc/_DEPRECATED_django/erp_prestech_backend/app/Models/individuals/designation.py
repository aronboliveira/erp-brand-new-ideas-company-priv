from django.db import models
from .._helpers.default_timed import DefaultTimed
from .._helpers.connectors.department_connected import DepartmentConnected
from .._helpers.fields import uuid_def_primary, default_char_field, default_user_creation
class Designation(DefaultTimed, DepartmentConnected):
    id = models.UUIDField(**uuid_def_primary())
    name = default_char_field()
    created_by = default_user_creation('%(class)s_created_by', db_index=True)
    
    class Meta:
        db_table = 'designations'
        ordering = ('-created_at',)
    
    def __str__(self) -> str:
        return self.name
