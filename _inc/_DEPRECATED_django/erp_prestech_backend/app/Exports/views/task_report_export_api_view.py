import logging
from django.http import HttpResponse
from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework import status
from ..task_report_export import TaskReportExport

logger = logging.getLogger(__name__)

class TaskReportExportAPIView(APIView):
  def get(self, request, project_id, *args, **kwargs):
    try:
      # In actual usage, you'd parse project_id from the URL and query the DB
      exporter = TaskReportExport(project_id, company_name="ERP Inc")
      exporter.format_data()
      exporter.dataframe()
      filename = exporter.export_to_excel("task_report_export.xlsx")
      if not filename:
        return Response({"error":"No tasks found to export."}, status=status.HTTP_204_NO_CONTENT)
      try:
        with open(filename, "rb") as f:
          content = f.read()
      except Exception as e:
        logger.error(f"File I/O error: {str(e)}")
        return Response({"error":"Error reading exported file."}, status=status.HTTP_500_INTERNAL_SERVER_ERROR)
      resp = HttpResponse(content, content_type="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")
      resp["Content-Disposition"] = f'attachment; filename="{filename}"'
      return resp
    except FileNotFoundError as fnfe:
      logger.error(f"File not found: {str(fnfe)}")
      return Response({"error":"File not found."}, status=status.HTTP_404_NOT_FOUND)
    except PermissionError as pe:
      logger.error(f"Permission error: {str(pe)}")
      return Response({"error":"Permission error."}, status=status.HTTP_403_FORBIDDEN)
    except Exception as e:
      logger.error(f"Unexpected error: {str(e)}")
      return Response({"error":"Unexpected error occurred."}, status=status.HTTP_500_INTERNAL_SERVER_ERROR)
