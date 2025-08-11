from .employee import Employee
from django.core.exceptions import ValidationError

class HrmEmployee(Employee):
    
    def __new__(cls, *args, **kwargs):
        kwargs.pop('category', None)
        _self = super(HrmEmployee, cls).__new__(cls)
        category = _self._meta.get_field('category')
        for k, v in {'choices': [('hr', 'HR')], 'default': 'hr'}.items():
            setattr(category, k, v)
        return _self
        
    def __init__(self, *args, **kwargs):
        super(HrmEmployee, self).__init__(*args, **kwargs)
        if self.category != 'hr':
            raise ValidationError(f'{self.__class__.__name__} must be of category hr')
    
    class Meta:
        verbose_name = "HRM Employee"
        verbose_name_plural = "HRM Employees"
        ordering = ('-created_at',)
    
    def __str__(self) -> str:
        return f"{self.first_name} {self.last_name}"
