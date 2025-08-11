import logging;
import pandas as pd;
from django.http import HttpResponse;
from rest_framework.views import APIView;
from rest_framework.response import Response;
from rest_framework import status;
from ..invoice_export import InvoiceExport;
logger = logging.getLogger(__name__);
class InvoiceExportAPIView(APIView):
  def get(self, request, *args, **kwargs):
    try:
      dummy_invoices = [
        {
          "invoice_id": "INV-001",
          "issue_date": "2024-05-01",
          "due_date": "2024-05-10",
          "send_date": "2024-05-02",
          "category_id": "Income",
          "ref_number": "REF123",
          "status": "Paid"
        },
        {
          "invoice_id": "INV-002",
          "issue_date": "2024-06-10",
          "due_date": "2024-06-20",
          "send_date": "2024-06-11",
          "category_id": "Income",
          "ref_number": "REF456",
          "status": "Pending"
        }
      ];
      exporter = InvoiceExport(dummy_invoices, company_name="ERP Inc");
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
