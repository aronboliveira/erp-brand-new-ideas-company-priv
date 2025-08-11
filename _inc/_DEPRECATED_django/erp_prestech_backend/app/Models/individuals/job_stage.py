from django.db import models
from django.utils import timezone
from typing import Dict
from django.db.models import QuerySet
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import uuid_def_primary, default_char_field, default_user_creation

class JobStage(DefaultTimed):
    id = models.UUIDField(**uuid_def_primary())
    title = default_char_field()
    order = models.PositiveIntegerField(default=0)
    created_by = default_user_creation('%(class)s_created_by')
    status = models.CharField(max_length=126, default='waiting_start', choices=[('waiting_start', 'Waiting for complete submission'),
                                                                            ('hr_analysis', 'Under HR analysis'), ('technical_analysis', 'Under Technical analysis'), 
                                                                            ('adm_analysis', 'Under managment analysis'), ('acceptance', 'Processing acceptance'), 
                                                                            ('complete', 'Complete'), ('hired', 'Already hired'), ('rejected', 'Processing rejection')])
    
    def __str__(self) -> str:
        return self.title
    
    def applications(self, filter_dict: Dict) -> QuerySet:
        from .job_application import JobApplication
        queryset = JobApplication.objects.filter(
            created_by=self.created_by,
            is_archive=False,
            stage=self,
            created_at__gte=filter_dict.get('start_date', timezone.now().date()),
            created_at__lte=filter_dict.get('end_date', timezone.now().date()),
        )
        if 'job' in filter_dict and filter_dict['job']:
            queryset = queryset.filter(job=filter_dict['job'])
        return queryset.order_by('order')
