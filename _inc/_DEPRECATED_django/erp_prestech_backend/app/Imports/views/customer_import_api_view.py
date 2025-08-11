from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework import status
import pandas as pd
import logging
from ..customer_import import CustomerImport

logger = logging.getLogger(__name__)

class CustomerImportAPI(APIView):
    """
    Example API for uploading an Excel file to import customer data.
    """

    def post(self, request, *args, **kwargs):
        file_obj = request.FILES.get('file')
        if not file_obj:
            return Response({"error": "No file uploaded."},
                            status=status.HTTP_400_BAD_REQUEST)

        importer = CustomerImport()
        try:
            df = pd.read_excel(file_obj, header=None)
            # df.columns = ["Name", "Email", "Phone", ...]  # if needed
            df.replace({pd.np.nan: ""}, inplace=True)

            for _, row in df.iterrows():
                importer.model(row)

            logger.info("Customer data imported successfully.")
            return Response({"status": "Success"},
                            status=status.HTTP_200_OK)
        except Exception as e:
            logger.error(f"File import error: {str(e)}")
            return Response({"error": "Failed to import file."},
                            status=status.HTTP_400_BAD_REQUEST)
