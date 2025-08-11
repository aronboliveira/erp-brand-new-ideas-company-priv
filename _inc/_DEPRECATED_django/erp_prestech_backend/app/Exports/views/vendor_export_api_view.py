import logging
from django.http import HttpResponse
from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework import status
from ..vendor_export import VendorExport

logger = logging.getLogger(__name__)

class VendorExportAPIView(APIView):
  def get(self, request, *args, **kwargs):
    try:
      # Dummy data simulating a list of vendor objects
      dummy_vendors = [
        {
          "vendor_id": "VEND-001",
          "name": "ABC Supply",
          "email": "abc@supply.com",
          "contact": "+123456789",
          "billing_name": "ABC HQ",
          "billing_country": "USA",
          "billing_state": "Texas",
          "billing_city": "Austin",
          "billing_phone": "+123456789",
          "billing_zip": "73301",
          "billing_address": "Somewhere in Austin",
          "shipping_name": "ABC Ship",
          "shipping_country": "USA",
          "shipping_state": "Texas",
          "shipping_city": "Austin",
          "shipping_phone": "+123456789",
          "shipping_zip": "73302",
          "shipping_address": "Somewhere else in Austin",
          "balance": "150.00"
        },
        {
          "vendor_id": "VEND-002",
          "name": "XYZ Distributors",
          "email": "contact@xyzdist.com",
          "contact": "+987654321",
          "billing_name": "XYZ HQ",
          "billing_country": "USA",
          "billing_state": "California",
          "billing_city": "San Diego",
          "billing_phone": "+987654321",
          "billing_zip": "92101",
          "billing_address": "Market St 500",
          "shipping_name": "XYZ Ship",
          "shipping_country": "USA",
          "shipping_state": "California",
          "shipping_city": "San Diego",
          "shipping_phone": "+987654321",
          "shipping_zip": "92102",
          "shipping_address": "Market St 501",
          "balance": "400.00"
        }
      ]
      exporter = VendorExport(dummy_vendors, company_name="ERP Inc")
      exporter.format_data()
      exporter.dataframe()
      filename = exporter.export_to_excel("vendor_export.xlsx")
      if not filename:
        return Response({"error":"No vendor data found to export."}, status=status.HTTP_204_NO_CONTENT)

      try:
        with open(filename, "rb") as f:
          content = f.read()
      except Exception as e:
        logger.error(f"File reading error: {str(e)}")
        return Response({"error":"Error reading exported file."}, status=status.HTTP_500_INTERNAL_SERVER_ERROR)

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
