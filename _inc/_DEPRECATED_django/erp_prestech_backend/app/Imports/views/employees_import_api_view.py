from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework import status
import pandas as pd
import logging

from ..employees_import import EmployeesImport

logger = logging.getLogger(__name__)

class EmployeesImportAPI(APIView):
    """
    Example API endpoint for uploading an Excel file containing employee data.
    """

    def post(self, request, *args, **kwargs):
        uploaded_file = request.FILES.get('file')
        if not uploaded_file:
            return Response(
                {"error": "No file provided."}, 
                status=status.HTTP_400_BAD_REQUEST
            )
        
        importer = EmployeesImport()
        try:
            df = pd.read_excel(uploaded_file, header=None)
            # If your file has headers, specify header=0 and rename the columns:
            # df.columns = ["Employee ID", "Name", "Position", "Email", ...]

            df.replace({pd.np.nan: ""}, inplace=True)

            for _, row in df.iterrows():
                importer.model(row)

            logger.info("Employees imported successfully via API.")
            return Response({"status": "Success"}, status=status.HTTP_200_OK)

        except Exception as e:
            logger.error(f"Error in importing employees: {str(e)}")
            return Response(
                {"error": "Failed to import employees from file."},
                status=status.HTTP_400_BAD_REQUEST
            )
