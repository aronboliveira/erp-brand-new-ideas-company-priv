import logging;
from django.http import HttpResponse;
from rest_framework.views import APIView;
from rest_framework.response import Response;
from rest_framework import status;
from ..bill_export import BillExport;

logger = logging.getLogger(__name__);

class BillExportAPIView(APIView):
  def get(self, request, *args, **kwargs):
    try:
      # Placeholder for ORM/Serializer logic; you would normally query the database
      dummy_bills = [
        {
          'bill_id': 'B001',
          'bill_date': '2024-01-01',
          'due_date': '2024-01-10',
          'order_no': 'ORD123',
          'status': 'Paid',
          'send_date': '2024-01-02',
          'category': 'Office Supplies'
        },
        {
          'bill_id': 'B002',
          'bill_date': '2024-02-05',
          'due_date': '2024-02-15',
          'order_no': 'ORD456',
          'status': 'Pending',
          'send_date': '2024-02-06',
          'category': 'Utilities'
        }
      ];

      exporter = BillExport(dummy_bills, company_name='ERP Inc');
      exporter.format_data();
      exporter.dataframe();
      filename = exporter.export_to_excel();

      if not filename:
        return Response({'error': 'Failed to generate Excel file.'}, status=status.HTTP_500_INTERNAL_SERVER_ERROR);

      with open(filename, 'rb') as f:
        response = HttpResponse(f.read(), content_type='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        response['Content-Disposition'] = f'attachment; filename="{filename}"';
        return response;

    except FileNotFoundError as e:
      logger.error(f'File not found: {str(e)}');
      return Response({'error': 'File not found.'}, status=status.HTTP_404_NOT_FOUND);

    except PermissionError as e:
      logger.error(f'Permission error: {str(e)}');
      return Response({'error': 'Permission denied.'}, status=status.HTTP_403_FORBIDDEN);

    except Exception as e:
      logger.error(f'Unexpected error: {str(e)}');
      return Response({'error': 'An unexpected error occurred.'}, status=status.HTTP_500_INTERNAL_SERVER_ERROR);
