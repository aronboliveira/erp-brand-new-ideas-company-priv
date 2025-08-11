from django.db import models
from django.core.files.storage import default_storage
from .._helpers.worker import Worker
from .._helpers.fields import SHORT_BLANK_CHAR, VOID, FILE_SIZE_VALIDATOR

class JobApplication(Worker):
    job = models.ForeignKey('Job', on_delete=models.CASCADE, related_name='applications')
    profile = models.TextField(**VOID, max_length=65535)
    resume = models.FileField(upload_to='resumes/', validators=[FILE_SIZE_VALIDATOR], storage=default_storage, **VOID)
    cover_letter = models.TextField(**VOID, max_length=65535)
    country = models.CharField(**SHORT_BLANK_CHAR)
    state = models.CharField(**SHORT_BLANK_CHAR)
    city = models.CharField(**SHORT_BLANK_CHAR)
    stage = models.ForeignKey('JobStage', on_delete=models.SET_NULL, **VOID, related_name='applications')
    order = models.PositiveIntegerField(default=0)
    rating = models.DecimalField(max_digits=3, decimal_places=2, **VOID)
    is_archive = models.BooleanField(default=False)
    custom_question = models.JSONField(**VOID)
    
    def __str__(self) -> str:
        return f"{self.name} - {self.job}"
