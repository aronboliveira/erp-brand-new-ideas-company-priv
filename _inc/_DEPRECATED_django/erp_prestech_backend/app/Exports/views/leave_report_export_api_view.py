import logging;
from django.http import HttpResponse;
from rest_framework.views import APIView;
from rest_framework.response import Response;
from rest_framework import status;
from ..leave_report_export import LeaveReportExport;

logger = logging.getLogger(__name__);

class LeaveReportExportAPIView(APIView):
  def get(self, request, *args, **kwargs):
    try:
      dummy_leaves_data = [
        {
          "employee_id": "EMP-10",
          "employee": "Alice Worker",
          "approved_leaves": 2,
          "rejected_leaves": 1,
          "pending_leaves": 0
        },
        {
          "employee_id": "EMP-11",
          "employee": "Bob Worker",
          "approved_leaves": 1,
          "rejected_leaves": 0,
          "pending_leaves": 2
        }
      ];
      exporter = LeaveReportExport(dummy_leaves_data, company_name="ERP Inc");
      exporter.format_data();
      exporter.dataframe();
      filename = exporter.export_to_excel();
      if not filename:
        return Response({"error":"No data to export."}, status=status.HTTP_204_NO_CONTENT);
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
