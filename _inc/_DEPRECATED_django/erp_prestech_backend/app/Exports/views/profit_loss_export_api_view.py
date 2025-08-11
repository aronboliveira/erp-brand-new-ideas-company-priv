import logging
import os
from django.http import HttpResponse
from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework import status
from ..ProfitLossExport import ProfitLossExport

logger = logging.getLogger(__name__)

class ProfitLossExportAPIView(APIView):
  def get(self, request, *args, **kwargs):
    try:
      dummy_data = [
        {
          "Type": "Income",
          "account": [
            {"account_name": "Sales", "account_code": "4000", "netAmount": 5000},
            {"account_name": "Total Income", "account_code": "", "netAmount": 5000},
          ]
        },
        {
          "Type": "Costs of Goods Sold",
          "account": [
            {"account_name": "Materials", "account_code": "5000", "netAmount": 2000},
            {"account_name": "Total Costs of Goods Sold", "account_code":"", "netAmount": 2000},
          ]
        },
        {
          "Type": "Expenses",
          "account": [
            {"account_name": "Rent", "account_code": "6000", "netAmount": 1000},
            {"account_name": "Total Expenses", "account_code":"", "netAmount": 1000},
          ]
        }
      ]

      exporter = ProfitLossExport(dummy_data, "2024-01-01", "2024-12-31", "ERP Inc")
      exporter.format_data()
      exporter.dataframe()
      filename = exporter.export_to_excel("profit_loss_export.xlsx")

      if not filename:
        return Response({"error":"No data to export."}, status=status.HTTP_204_NO_CONTENT)

      try:
        with open(filename, "rb") as f:
          content = f.read()
      except FileNotFoundError as fnfe:
        logger.error(f"File not found: {str(fnfe)}")
        return Response({"error":"File not found."}, status=status.HTTP_404_NOT_FOUND)

      response = HttpResponse(content, content_type="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")
      response["Content-Disposition"] = f'attachment; filename="{filename}"'
      return response

    except Exception as e:
      logger.error(f"Unexpected error: {str(e)}")
      return Response({"error":"Unexpected error occurred."}, status=status.HTTP_500_INTERNAL_SERVER_ERROR)
