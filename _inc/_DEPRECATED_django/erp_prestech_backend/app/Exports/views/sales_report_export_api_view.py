import logging
from django.http import HttpResponse
from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework import status
from ..sales_report_export import SalesReportExport

logger = logging.getLogger(__name__)

class SalesReportExportAPIView(APIView):
  def get(self, request, *args, **kwargs):
    try:
      # For demonstration, item-based data
      dummy_data = [
        {
          "name": "Desk Chair",
          "invoice_count": 30,
          "price": 3000,
          "avg_price": 100
        },
        {
          "name": "Office Table",
          "invoice_count": 20,
          "price": 4000,
          "avg_price": 200
        }
      ]
      # Or else if report_name != 'Item' then structure includes "total_tax"
      report_name = "Item"
      exporter = SalesReportExport(
        dummy_data,
        "2024-01-01",
        "2024-12-31",
        "ERP Inc",
        report_name
      )
      exporter.format_data()
      exporter.dataframe()
      filename = exporter.export_to_excel("sales_report_export.xlsx")
      if not filename:
        return Response({"error": "No data to export."}, status=status.HTTP_204_NO_CONTENT)

      try:
        with open(filename, "rb") as f:
          content = f.read()
      except Exception as e:
        logger.error(f"Error reading file: {str(e)}")
        return Response({"error": "Error reading exported file."}, status=status.HTTP_500_INTERNAL_SERVER_ERROR)

      resp = HttpResponse(
        content,
        content_type="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
      )
      resp["Content-Disposition"] = f'attachment; filename="{filename}"'
      return resp
    except FileNotFoundError as fnfe:
      logger.error(f"File not found: {str(fnfe)}")
      return Response({"error": "File not found."}, status=status.HTTP_404_NOT_FOUND)
    except PermissionError as pe:
      logger.error(f"Permission error: {str(pe)}")
      return Response({"error": "Permission error."}, status=status.HTTP_403_FORBIDDEN)
    except Exception as e:
      logger.error(f"Unexpected error: {str(e)}")
      return Response({"error": "Unexpected error occurred."}, status=status.HTTP_500_INTERNAL_SERVER_ERROR)
