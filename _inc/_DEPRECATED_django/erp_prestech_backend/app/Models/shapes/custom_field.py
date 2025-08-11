from django.db import models
from django.db import connection
from django.utils import timezone
from .._helpers.default_timed import DefaultTimed
from .._helpers.fields import default_char_field, uuid_def_primary, default_user_creation

class CustomField(DefaultTimed):
  id = models.UUIDField(**uuid_def_primary())
  name = default_char_field()
  FIELD_TYPES = [
      ("text", "Text"),
      ("email", "Email"),
      ("number", "Number"), 
      ("date", "Date"),
      ("textarea", "Textarea"),
  ]
  MODULES = [
      ("user", "User"),
      ("customer", "Customer"),
      ("vendor", "Vendor"),
      ("product", "Product"),
      ("proposal", "Proposal"),
      ("invoice", "Invoice"),
      ("bill", "Bill"),
      ("account", "Account"),
      ('other', 'Other')
  ]
  type = default_char_field(choices=FIELD_TYPES, default='text')
  module = default_char_field(choices=MODULES, default='other')
  created_by = default_user_creation("%(class)s_created_by")

  class Meta:
    db_table = "custom_fields"
    
  @staticmethod
  def save_data(obj: models.Model, data: dict) -> None:
    if data:
      record_id = obj.id
      now = timezone.now().strftime("%Y-%m-%d %H:%M:%S")
      with connection.cursor() as cursor:
        for field_id, value in data.items():
          cursor.execute(
            """
            INSERT INTO custom_field_values (record_id, field_id, value, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s)
            ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = VALUES(updated_at)
            """,
            [record_id, field_id, value, now, now],
          )
  @staticmethod
  def get_data(obj: models.Model, module: str) -> dict:
    with connection.cursor() as cursor:
      cursor.execute(
        """
        SELECT cfv.value, cf.id
        FROM custom_field_values cfv
        JOIN custom_fields cf ON cfv.field_id = cf.id
        WHERE cf.module = %s AND cfv.record_id = %s
        """,
        [module, obj.id],
      )
      return {row[1]: row[0] for row in cursor.fetchall()}
