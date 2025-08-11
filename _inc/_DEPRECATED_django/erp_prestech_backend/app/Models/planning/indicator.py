from django.db import models
from typing import Any, Optional
from .._helpers.connectors.branch_connected import BranchConnected
from .._helpers.rateable import Rateable
class Indicator(Rateable, BranchConnected):
  designation = models.ForeignKey("Designation", on_delete=models.CASCADE, related_name="indicators", db_index=True)
  technical = ["None", "Beginner", "Intermediate", "Advanced", "Expert / Leader"]
  organizational = ["None", "Beginner", "Intermediate", "Advanced"]
  
  def __str__(self) -> str:
    return f"Indicator {self.id}"
  
  @property
  def branches(self) -> Any:
    return self.branch
  
  @property
  def departments(self) -> Optional[Any]:
    from ..companies import Department
    return Department.objects.filter(id=getattr(self, "department_id", None)).first()
  
  @property
  def designations(self) -> Any:
    return self.designation
  
  @property
  def user(self) -> Any:
    return self.created_user
