from django.db import models
from .person import Person
from .fields import VOID, VALID_MYSQL_MIN_DATE
class Worker(Person):
  class Meta:
    abstract = True
  dob = models.DateField(validators=[VALID_MYSQL_MIN_DATE], **VOID)
  skill = models.TextField(max_length=65535, **VOID)