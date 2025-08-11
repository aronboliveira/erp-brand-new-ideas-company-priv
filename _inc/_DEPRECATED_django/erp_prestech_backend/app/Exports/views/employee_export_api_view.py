import logging;
from django.http import HttpResponse;
from rest_framework.views import APIView;
from rest_framework.response import Response;
from rest_framework import status;
from ..employee_export import EmployeeExport;

logger = logging.getLogger(__name__);

class EmployeeExportAPIView(APIView):
  def get(self, request, *args, **kwargs):
    try:
      dummy_employees = [
        {
          "name": "Alice Worker",
          "date_of_birth": "1990-01-01",
          "gender": "Female",
          "phone_number": "+123456789",
          "address": "123 Work Ave",
          "email": "alice@company.com",
          "branch_id": "Main Branch",
          "department_id": "Sales",
          "designation_id": "Sales Manager",
          "date_of_join": "2020-05-10",
          "account_holder_name": "Alice W",
          "account_number": "111122223333",
          "bank_name": "Bank of People",
          "bank_identifier_code": "BOP123",
          "branch_location": "Downtown",
          "salary": "3000"
        },
        {
          "name": "Bob Worker",
          "date_of_birth": "1988-03-15",
          "gender": "Male",
          "phone_number": "+987654321",
          "address": "456 Corporate Blvd",
          "email": "bob@company.com",
          "branch_id": "Secondary Branch",
          "department_id": "IT",
          "designation_id": "Developer",
          "date_of_join": "2019-09-01",
          "account_holder_name": "Bob W",
          "account_number": "444455556666",
          "bank_name": "People's Bank",
          "bank_identifier_code": "PBK987",
          "branch_location": "Uptown",
          "salary": "4000"
        }
      ];
      exporter = EmployeeExport(dummy_employees, company_name="ERP Inc");
      exporter.format_data();
      exporter.dataframe();
      filename = exporter.export_to_excel();
      if not filename:
        return Response({"error":"No data to export."}, status=status.HTTP_204_NO_CONTENT);
      with open(filename,"rb") as f:
        content = f.read();
      response = HttpResponse(content, content_type="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
      response["Content-Disposition"] = f'attachment; filename="{filename}"';
      return response;
    except FileNotFoundError as fnfe:
      logger.error(f"File not found: {str(fnfe)}");
      return Response({"error":"File not found."}, status=status.HTTP_404_NOT_FOUND);
    except PermissionError as pe:
      logger.error(f"Permission error: {str(pe)}");
      return Response({"error":"Permission error."}, status=status.HTTP_403_FORBIDDEN);
    except Exception as e:
      logger.error(f"Unexpected error: {str(e)}");
      return Response({"error":"Unexpected error occurred."}, status=status.HTTP_500_INTERNAL_SERVER_ERROR);
