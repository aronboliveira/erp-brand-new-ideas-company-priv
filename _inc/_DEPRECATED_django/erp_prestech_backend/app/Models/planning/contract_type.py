from django.db import models
from .._helpers.connectors.contract_connected import ContractConnected
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_char_field, uuid_def_primary, default_user_creation
class ContractType(DefaultTimed, ContractConnected):
  id = models.UUIDField(**uuid_def_primary())
  name = default_char_field()
  created_by = default_user_creation('%(class)s_created_by')
  contract_option = models.CharField(
      max_length=50,
      choices=[
          ('management', 'Management'),
          ('services', 'Services'),
          ('monthly', 'Monthly'),
          ('fixed', 'Fixed'),
          ('others', 'Others')
      ],
      default='monthly',
      db_index=True
  )
  interval_option = models.CharField(
      max_length=50,
      choices=[
          ('lifetime', 'Lifetime'),
          ('monthly', 'Monthly'),
          ('year', 'Year'),
          ('others', 'Others')
      ],
      default='year',
      db_index=True
  )

  class Meta:
    db_table = 'contract_type'
