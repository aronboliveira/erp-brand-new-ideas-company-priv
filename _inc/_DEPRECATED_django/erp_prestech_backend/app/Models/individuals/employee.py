from django.db import models
from .._helpers.connectors.branch_connected import BranchConnected
from .._helpers.worker import Worker
from .._helpers.fields import (default_char_field, uuid_def_primary, SHORT_BLANK_CHAR, 
                               MAX_SAFE_DECIMAL, TINY_BLANK_CHAR, VOID, VALID_MYSQL_MIN_DATE)
from .._helpers.connectors.department_connected import DepartmentConnected
from .user import User
class Employee(User, Worker, BranchConnected, DepartmentConnected):
    designation = models.ForeignKey('Designation', on_delete=models.SET_NULL, null=True, related_name='employees', db_index=True)
    salary_type = models.ForeignKey('PayslipType', on_delete=models.SET_NULL, null=True, related_name='employees', db_index=True)
    hire_date = models.DateField(**VOID, validators=[VALID_MYSQL_MIN_DATE])
    employee_id = models.UUIDField(**uuid_def_primary(pk=False))
    company_doj = models.DateField(**VOID,validators=[VALID_MYSQL_MIN_DATE])
    documents = models.JSONField(**VOID)
    account_holder_name = default_char_field(voidable=True)
    account_number = models.CharField(**TINY_BLANK_CHAR)
    bank_name = default_char_field(voidable=True)
    bank_identifier_code = models.CharField(**SHORT_BLANK_CHAR)
    category = models.CharField(
        max_length=50,
        choices=[
            ('technical', 'Technical'),
            ('administrative', 'Administrative'),
            ('hr', 'Human Resources'),
            ('design', 'Design'),
            ('sales', 'Sales'),
            ('marketing', 'Marketing'),
            ('support', 'Support'),
            ('qa', 'Quality Assurance'),
            ('management', 'Management'),
            ('other', 'Other')
        ],
        default='other'
    )
    branch_location = default_char_field(voidable=True)
    tax_payer_id = models.UUIDField(**uuid_def_primary(pk=False))
    salary = models.DecimalField(max_digits=12, decimal_places=2, default=0, validators=[MAX_SAFE_DECIMAL])
         
    def __str__(self) -> str:
        return self.name
