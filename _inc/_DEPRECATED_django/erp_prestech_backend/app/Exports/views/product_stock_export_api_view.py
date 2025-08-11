import logging
from django.http import HttpResponse
from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework import status
from ..product_stock_export import ProductStockExport

logger = logging.getLogger(__name__)

class ProductStockExportAPIView(APIView):
  def get(self, request, *args, **kwargs):
    try:
      dummy_stock_data = [
        {
          "id": "ST-1001",
          "product_id": "Desk Chair",
          "quantity": "20",
          "type": "In",
          "description": "New stock arrived",
          "date": "2024-09-01"
        },
        {
          "id": "ST-1002",
          "product_id": "Office Table",
          "quantity": "5",
          "type": "Out",
          "description": "Sold some tables",
          "date": "2024-09-02"
        }
      ]
      exporter = ProductStockExport(dummy_stock_data, company_name="ERP Inc")
      exporter.format_data()
      exporter.dataframe()
      filename = exporter.export_to_excel()
      if not filename:
        return Response({"error": "No stock data to export."}, status=status.HTTP_204_NO_CONTENT)

      try:
        with open(filename, "rb") as f:
          content = f.read()
      except FileNotFoundError as fnfe:
        logger.error(f"File not found: {str(fnfe)}")
        return Response({"error": "File not found."}, status=status.HTTP_404_NOT_FOUND)

      resp = HttpResponse(content, content_type="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")
      resp["Content-Disposition"] = f'attachment; filename="{filename}"'
      return resp

    except PermissionError as pe:
      logger.error(f"Permission error: {str(pe)}")
      return Response({"error": "Permission error."}, status=status.HTTP_403_FORBIDDEN)
    except Exception as e:
      logger.error(f"Unexpected error: {str(e)}")
      return Response({"error": "Unexpected error occurred."}, status=status.HTTP_500_INTERNAL_SERVER_ERROR)
