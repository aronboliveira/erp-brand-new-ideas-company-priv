import logging;
import pandas as pd;
from django.http import HttpResponse;
from rest_framework.views import APIView;
from rest_framework.parsers import MultiPartParser, FormParser;
from rest_framework.response import Response;
from rest_framework import status, serializers;
from ..balance_sheet_export import BalanceSheetExport
logger = logging.getLogger(__name__);
class FileUploadSerializer(serializers.Serializer):
  file = serializers.FileField();
class BalanceSheetExportAPIView(APIView):
  parser_classes = (MultiPartParser, FormParser);
  def post(self, request, *args, **kwargs):
    serializer = FileUploadSerializer(data=request.data);
    if not serializer.is_valid():
      return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST);
    file = serializer.validated_data['file'];
    ext = file.name.split('.')[-1].lower();
    try:
      if ext == 'csv':
        try:
          df = pd.read_csv(file);
        except pd.errors.ParserError as pe:
          logger.error(f"CSV parsing error: {str(pe)}");
          return Response({"error": "CSV parsing error."}, status=status.HTTP_400_BAD_REQUEST);
        except ValueError as ve:
          logger.error(f"CSV value error: {str(ve)}");
          return Response({"error": "CSV value error."}, status=status.HTTP_400_BAD_REQUEST);
        data = df.to_dict(orient='records');
        raw_data = {"Sheet1": data};
      elif ext == 'xlsx':
        try:
          xls = pd.ExcelFile(file);
        except (IOError, ValueError) as e:
          logger.error(f"Excel file reading error: {str(e)}");
          return Response({"error": "Error reading Excel file."}, status=status.HTTP_400_BAD_REQUEST);
        raw_data = {};
        for sheet in xls.sheet_names:
          try:
            df = pd.read_excel(xls, sheet_name=sheet);
          except (IOError, ValueError) as e:
            logger.error(f"Error reading sheet {sheet}: {str(e)}");
            continue;
          raw_data[sheet] = df.to_dict(orient='records');
      else:
        return Response({"error": "Unsupported file format."}, status=status.HTTP_400_BAD_REQUEST);
    except (IOError, TypeError, PermissionError) as fe:
      logger.error(f"File processing error: {str(fe)}");
      return Response({"error": "Error processing file."}, status=status.HTTP_500_INTERNAL_SERVER_ERROR);
    try:
      exporter = BalanceSheetExport(raw_data, "2024-01-01", "2024-12-31", "ERP Inc");
      exporter.run();
    except (ValueError, TypeError) as de:
      logger.error(f"Export data error: {str(de)}");
      return Response({"error": "Export failed due to invalid data."}, status=status.HTTP_500_INTERNAL_SERVER_ERROR);
    except PermissionError as pe:
      logger.error(f"Export permission error: {str(pe)}");
      return Response({"error": "Export failed due to permission error."}, status=status.HTTP_500_INTERNAL_SERVER_ERROR);
    except Exception as e:
      logger.error(f"Export error: {str(e)}");
      return Response({"error": "Export failed."}, status=status.HTTP_500_INTERNAL_SERVER_ERROR);
    try:
      with open("balance_sheet.xlsx", "rb") as f:
        content = f.read();
    except IOError as ioe:
      logger.error(f"IOError reading exported file: {str(ioe)}");
      return Response({"error": "Error reading exported file."}, status=status.HTTP_500_INTERNAL_SERVER_ERROR);
    return HttpResponse(content, content_type="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");