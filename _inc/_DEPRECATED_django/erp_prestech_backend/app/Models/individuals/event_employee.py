from django.db import models
from .employee import Employee
class EventEmployee(Employee):
    event = models.ForeignKey('Event', on_delete=models.CASCADE)
    
    class Meta:
        db_table = 'event_employee'
        ordering = ('-created_at',)
