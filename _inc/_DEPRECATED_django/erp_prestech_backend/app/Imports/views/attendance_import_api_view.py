from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework import status
import logging
import pandas as pd

from ..attendance_import import AttendanceImport

logger = logging.getLogger(__name__)

class AttendanceImportAPI(APIView):
    """
    Example API to handle attendance import from an Excel file upload.
    """

    def post(self, request, *args, **kwargs):
        uploaded_file = request.FILES.get('file')
        if not uploaded_file:
            return Response(
                {"error": "No file provided."}, 
                status=status.HTTP_400_BAD_REQUEST
            )
        
        # Initialize importer
        importer = AttendanceImport()

        try:
            # Directly load the in-memory file using pandas
            df = pd.read_excel(uploaded_file, header=None)
            df.replace({pd.np.nan: ""}, inplace=True)

            # Optionally rename columns if needed:
            # df.columns = ["Name", "Date", "Status", ...]

            for index, row in df.iterrows():
                importer.model(row)

            logger.info("File imported successfully.")
            return Response({"status": "Success"}, status=status.HTTP_200_OK)

        except Exception as e:
            logger.error(f"Error importing attendance: {str(e)}")
            return Response(
                {"error": "File import error."},
                status=status.HTTP_400_BAD_REQUEST
            )
