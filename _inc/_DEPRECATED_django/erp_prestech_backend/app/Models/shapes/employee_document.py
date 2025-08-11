from .document import Document
from .._helpers.connectors.employee_connected import EmployeeConnected
class EmployeeDocument(Document, EmployeeConnected):
  
  class Meta:
    db_table = "employee_document"
    ordering = ["-id"]
  def __str__(self) -> str:
    return f"Document {self.document_id} for Employee {self.employee_id}"
