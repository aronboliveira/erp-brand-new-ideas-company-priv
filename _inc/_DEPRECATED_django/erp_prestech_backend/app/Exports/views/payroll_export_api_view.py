import logging;
from django.http import HttpResponse;
from rest_framework.views import APIView;
from rest_framework.response import Response;
from rest_framework import status;
from ..payroll_export import PayrollExport;

logger = logging.getLogger(__name__);

class PayrollExportAPIView(APIView):
  def get(self, request, *args, **kwargs):
    try:
      dummy_payroll_data = [
        {
          "employee_id": "EMP-001",
          "status": "Paid",
          "employee_name": "Alice Worker",
          "salary": "2000",
          "net_salary": "1800",
          "month": "2024-07"
        },
        {
          "employee_id": "EMP-002",
          "status": "UnPaid",
          "employee_name": "Bob Worker",
          "salary": "3000",
          "net_salary": "2900",
          "month": "2024-07"
        }
      ];
      exporter = PayrollExport(dummy_payroll_data, company_name="ERP Inc");
      exporter.format_data();
      exporter.dataframe();
      filename = exporter.export_to_excel();
      if not filename:
        return Response({"error":"No data to export"}, status=status.HTTP_204_NO_CONTENT);
      with open(filename,"rb") as f:
        content = f.read();
      resp = HttpResponse(content, content_type="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
      resp["Content-Disposition"] = f'attachment; filename="{filename}"';
      return resp;
    except FileNotFoundError as fnfe:
      logger.error(f"File not found: {str(fnfe)}");
      return Response({"error":"File not found."}, status=status.HTTP_404_NOT_FOUND);
    except PermissionError as pe:
      logger.error(f"Permission error: {str(pe)}");
      return Response({"error":"Permission error."}, status=status.HTTP_403_FORBIDDEN);
    except Exception as e:
      logger.error(f"Unexpected error: {str(e)}");
      return Response({"error":"Unexpected error occurred."}, status=status.HTTP_500_INTERNAL_SERVER_ERROR);
