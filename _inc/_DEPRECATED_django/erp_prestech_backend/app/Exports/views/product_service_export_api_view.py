import logging;
import pandas as pd;
from django.http import HttpResponse;
from rest_framework.views import APIView;
from rest_framework.response import Response;
from rest_framework import status;
from ..product_service_export import ProductServiceExport;

logger = logging.getLogger(__name__);

class ProductServiceExportAPIView(APIView):
  def get(self, request, *args, **kwargs):
    try:
      dummy_products = [
        {
          "id": "101",
          "item": "Desk Chair",
          "sku": "CHAIR-01",
          "sale_price": "100.00",
          "purchase_price": "70.00",
          "tax": "10%",
          "category": "Office Furniture",
          "unit": "Pieces",
          "type": "product",
          "description": "Comfortable chair with wheels"
        },
        {
          "id": "102",
          "item": "Office Table",
          "sku": "TABLE-01",
          "sale_price": "200.00",
          "purchase_price": "120.00",
          "tax": "10%",
          "category": "Office Furniture",
          "unit": "Pieces",
          "type": "product",
          "description": "Large wooden table"
        }
      ];
      exporter = ProductServiceExport(dummy_products, company_name="ERP Inc");
      exporter.format_data();
      exporter.dataframe();
      filename = exporter.export_to_excel();
      if not filename:
        return Response({"error":"No product data to export."}, status=status.HTTP_204_NO_CONTENT);
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
