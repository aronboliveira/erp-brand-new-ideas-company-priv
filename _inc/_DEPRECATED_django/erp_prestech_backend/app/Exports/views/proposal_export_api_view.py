import logging
from rest_framework.views import APIView
from rest_framework.response import Response
from rest_framework import status
from django.http import HttpResponse

logger = logging.getLogger(__name__)

class ProposalExportAPIView(APIView):
    def get(self, request, *args, **kwargs):
        try:
            # Dummy data structure to simulate proposals
            dummy_proposals = [
                {
                    "proposal_id": "PROP-001",
                    "issue_date": "2024-01-01",
                    "send_date": "2024-01-02",
                    "category_id": "Income",
                    "status": 0  # Assuming status index
                },
                {
                    "proposal_id": "PROP-002",
                    "issue_date": "2024-02-01",
                    "send_date": "2024-02-02",
                    "category_id": "Income",
                    "status": 1
                }
            ]

            from ..proposal_export import ProposalExport  # Path depends on your project
            exporter = ProposalExport(dummy_proposals, company_name='ERP Inc')
            exporter.format_data()
            exporter.dataframe()

            filename = exporter.export_to_excel()

            if not filename:
                return Response({"error": "Failed to generate Proposal Excel"}, status=status.HTTP_500_INTERNAL_SERVER_ERROR)

            with open(filename, 'rb') as f:
                content = f.read()

            resp = HttpResponse(content, content_type="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")
            resp['Content-Disposition'] = f'attachment; filename="{filename}"'
            return resp

        except FileNotFoundError as e:
            logger.error(f"File not found: {str(e)}")
            return Response({"error": "File not found"}, status=status.HTTP_404_NOT_FOUND)

        except PermissionError as e:
            logger.error(f"Permission error: {str(e)}")
            return Response({"error": "Permission error"}, status=status.HTTP_403_FORBIDDEN)

        except Exception as e:
            logger.error(f"Unexpected error: {str(e)}")
            return Response({"error": "Unexpected error occurred"}, status=status.HTTP_500_INTERNAL_SERVER_ERROR)
