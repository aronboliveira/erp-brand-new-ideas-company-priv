from django.db import models
from typing import Any
class DefaultTimed(models.Model):
  created_at = models.DateTimeField(auto_now_add=True)
  updated_at = models.DateTimeField(auto_now=True)
  class Meta:
    abstract = True
  def save(self, *args: Any, **kwargs: Any) -> None:
    if hasattr(self, '_custom_created_at'):
      self.created_at = self._custom_created_at
    if hasattr(self, '_custom_updated_at'):
      self.updated_at = self._custom_updated_at
    super().save(*args, **kwargs)
    
  def set_custom_times(self, created_at: models.DateTimeField = None, updated_at: models.DateTimeField = None) -> None:
    self._custom_created_at = created_at
    self._custom_updated_at = updated_at