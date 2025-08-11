from django.db import models
from .default_timed import DefaultTimed
from .fields import (uuid_def_primary, default_user_creation, 
                     COLOR_NAME_VALIDATOR, VOID)
class Colorable(DefaultTimed):
	
  class Meta:
    abstract = True
  
  id = models.UUIDField(**uuid_def_primary())
  created_by = default_user_creation('%(class)s_created_by')
  color = models.CharField(max_length=50, validator=[COLOR_NAME_VALIDATOR], 
                           default='gray', help_text='Event color for display purposes', **VOID)