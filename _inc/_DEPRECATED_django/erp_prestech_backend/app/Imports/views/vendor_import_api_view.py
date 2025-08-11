from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework import status
import pandas as pd
import logging
from ..vendor_import import VendorImport
logger = logging.getLogger(__name__)
class VendorImportAPI(APIView):
    """
    An example APIView for uploading an Excel file containing vendor data.
    """

    def post(self, request, *args, **kwargs):
        uploaded_file = request.FILES.get('file')
        if not uploaded_file:
            return Response(
                {"error": "No file provided."},
                status=status.HTTP_400_BAD_REQUEST
            )

        importer = VendorImport()
        try:
            df = pd.read_excel(uploaded_file, header=None)
            # If your file has a header row:
            # df.columns = ["VendorName", "VendorAddress", "PhoneNumber", ...]
            df.replace({pd.np.nan: ""}, inplace=True)
            for _, row in df.iterrows():
                importer.model(row)
            logger.info("Vendor file imported successfully.")
            return Response({"status": "Success"}, status=status.HTTP_200_OK)
        except Exception as e:
            logger.error(f"File import error: {str(e)}")
            return Response(
                {"error": "Failed to import vendor data."},
                status=status.HTTP_400_BAD_REQUEST
            )
