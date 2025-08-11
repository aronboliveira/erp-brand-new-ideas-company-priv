from django.db import models
from django.utils import timezone
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_user_creation, uuid_def_primary, VALID_MYSQL_MIN_DATE

class JobOnBoard(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  application = models.OneToOneField(
    'JobApplication',
    on_delete=models.CASCADE,
    db_column='application',
    related_name='job_on_board'
  )
  joining_date = models.DateField(validators=[VALID_MYSQL_MIN_DATE], default=timezone.now, db_index=True)
  JOB_TYPE_CHOICES = [('select', 'Select Status'),
                    ('pending', 'Pending'),
                    ('cancel', 'Cancel'),
                    ('confirm', 'Confirm'),
                  ]
  STATUS_CHOICES = [('select', 'Select Status'),
                    ('pending', 'Pending'),
                    ('cancel', 'Cancel'),
                    ('confirm', 'Confirm'),
                  ]
  SALARY_DURATION_CHOICES = [('select', 'Select Salary Duration'),
                              ('monthly', 'Monthly'),
                              ('weekly', 'Weekly'),
                            ]
  type = models.CharField(max_length=126, default='select', choices=JOB_TYPE_CHOICES)
  status = models.CharField(max_length=126, default='select', choices=STATUS_CHOICES)
  salary_interval = models.CharField(max_length=126, default='select', choices=SALARY_DURATION_CHOICES)
  convert_to_employee = models.BooleanField(default=False)
  created_by = default_user_creation('%(class)s_created_by')
