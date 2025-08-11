import logging
from django.http import HttpResponse;
from rest_framework.views import APIView;
from rest_framework.response import Response;
from rest_framework import status;
from ..customer_export import CustomerExport
logger = logging.getLogger(__name__);
class CustomerExportAPIView(APIView):
  def get(self, request, *args, **kwargs):
    try:
      dummy_customers = [
        {
          "customer_id": "CUST-001",
          "name": "John Doe",
          "email": "john@example.com",
          "contact": "+123456789",
          "billing_name": "John Inc",
          "billing_country": "USA",
          "billing_state": "CA",
          "billing_city": "Los Angeles",
          "billing_phone": "+123456789",
          "billing_zip": "90001",
          "billing_address": "Sunset Blvd 123",
          "shipping_name": "John Inc S",
          "shipping_country": "USA",
          "shipping_state": "CA",
          "shipping_city": "Los Angeles",
          "shipping_phone": "+123456789",
          "shipping_zip": "90002",
          "shipping_address": "Sunset Blvd 124",
          "balance": "50.00"
        },
        {
          "customer_id": "CUST-002",
          "name": "Jane Smith",
          "email": "jane@example.com",
          "contact": "+987654321",
          "billing_name": "Jane LLC",
          "billing_country": "USA",
          "billing_state": "NY",
          "billing_city": "New York",
          "billing_phone": "+987654321",
          "billing_zip": "10001",
          "billing_address": "Times Square 10",
          "shipping_name": "Jane LLC S",
          "shipping_country": "USA",
          "shipping_state": "NY",
          "shipping_city": "New York",
          "shipping_phone": "+987654321",
          "shipping_zip": "10002",
          "shipping_address": "Times Square 11",
          "balance": "120.00"
        }
      ];
      exporter = CustomerExport(dummy_customers, company_name="ERP Inc");
      exporter.format_data();
      exporter.dataframe();
      filename = exporter.export_to_excel();
      if not filename:
        return Response({"error": "Failed to generate Excel"}, status=status.HTTP_500_INTERNAL_SERVER_ERROR);
      with open(filename,"rb") as f:
        content = f.read();
      resp = HttpResponse(content, content_type="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
      resp["Content-Disposition"] = f'attachment; filename="{filename}"';
      return resp;
    except FileNotFoundError as e:
      logger.error(f"File not found: {str(e)}");
      return Response({"error":"File not found."}, status=status.HTTP_404_NOT_FOUND);
    except PermissionError as e:
      logger.error(f"Permission error: {str(e)}");
      return Response({"error":"Permission error."}, status=status.HTTP_403_FORBIDDEN);
    except Exception as e:
      logger.error(f"Unexpected error: {str(e)}");
      return Response({"error":"Unexpected error occurred."}, status=status.HTTP_500_INTERNAL_SERVER_ERROR);
