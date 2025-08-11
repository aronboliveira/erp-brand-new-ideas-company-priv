import logging
from django.http import HttpResponse
from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework import status
from ..trial_balance_export import TrialBalanceExport

logger = logging.getLogger(__name__)

class TrialBalanceExportAPIView(APIView):
  def get(self, request, *args, **kwargs):
    try:
      dummy_data = {
        "Assets": [
          {"name": "Cash", "code": "1000", "totalDebit": 5000, "totalCredit": 0},
          {"name": "Accounts Receivable", "code": "1100", "totalDebit": 3000, "totalCredit": 0}
        ],
        "Liabilities": [
          {"name": "Accounts Payable", "code": "2000", "totalDebit": 0, "totalCredit": 2000}
        ]
      }
      exporter = TrialBalanceExport(dummy_data, "2024-01-01", "2024-12-31", "ERP Inc")
      exporter.format_data()
      exporter.dataframe()
      filename = exporter.export_to_excel("trial_balance_export.xlsx")
      if not filename:
        return Response({"error":"No data to export."}, status=status.HTTP_204_NO_CONTENT)
      try:
        with open(filename, "rb") as f:
          content = f.read()
      except Exception as e:
        logger.error(f"File I/O error: {str(e)}")
        return Response({"error": "Error reading exported file."}, status=status.HTTP_500_INTERNAL_SERVER_ERROR)
      resp = HttpResponse(
        content,
        content_type="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
      )
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
