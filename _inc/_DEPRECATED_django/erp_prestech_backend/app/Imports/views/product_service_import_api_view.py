from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework import status
import pandas as pd
import logging

from ..product_service_import import ProductServiceImport

logger = logging.getLogger(__name__)

class ProductServiceImportAPI(APIView):
    """
    Example API endpoint to handle product/service Excel file uploads.
    """

    def post(self, request, *args, **kwargs):
        uploaded_file = request.FILES.get('file')
        if not uploaded_file:
            return Response({"error": "No file provided."},
                            status=status.HTTP_400_BAD_REQUEST)
        
        importer = ProductServiceImport()
        try:
            df = pd.read_excel(uploaded_file, header=None)
            # If your file has headers:
            # df.columns = ["ProductName", "ServiceType", "Price", ...]
            df.replace({pd.np.nan: ""}, inplace=True)

            for _, row in df.iterrows():
                importer.model(row)

            logger.info("Product/Service file imported successfully.")
            return Response({"status": "Success"}, status=status.HTTP_200_OK)

        except Exception as e:
            logger.error(f"File import error: {str(e)}")
            return Response({"error": "Failed to import product/service data."},
                            status=status.HTTP_400_BAD_REQUEST)
