import logging
from typing import Any
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.db import transaction
from ....configs.messages_templates import get_exception_class_message
from .._helpers.http import get_redirect_url
from ....Models.activity.commission import Commission
from ....Models.individuals.employee import Employee
from .._traits.controller import Controller
logger = logging.getLogger(__name__)
class CommissionController(Controller):
    
    def commission_create(self, request: HttpRequest, id: int) -> HttpResponse:
        logger.info("CommissionController.commission_create() called for employee ID: %s", id)
        try:
            employee: Employee = get_object_or_404(Employee, pk=id)
            commissions = Commission.commissiontype
            logger.debug("Commission types retrieved: %s", commissions)
            return render(request, 'commission/create.html', {
                'employee': employee,
                'commissions': commissions
            })
        except Exception as e:
            logger.exception("Error in commission_create: %s", e)
            messages.error(request, "An error occurred while loading the commission creation page.")
            return redirect(get_redirect_url(request))

    def store(self, request: HttpRequest) -> HttpResponse:
        logger.info("CommissionController.store() called by user: %s", request.user)
        try:
            if not request.user.has_perm('create commission'):
                logger.warning("User [%s] lacks 'create commission' permission.", request.user)
                messages.error(request, get_exception_class_message(PermissionDenied, __class__.__name__))
                return redirect(get_redirect_url(request))

            employee_id: Any = request.POST.get('employee_id')
            title: Any = request.POST.get('title')
            amount: Any = request.POST.get('amount')
            if not employee_id or not title or not amount:
                logger.error("Missing required field(s): employee_id=[%s], title=[%s], amount=[%s]", employee_id, title, amount)
                messages.error(request, "Employee, Title and Amount are required.")
                return redirect(get_redirect_url(request))

            # Critical financial operation: wrap in an atomic transaction.
            with transaction.atomic():
                commission: Commission = Commission()
                commission.employee_id = employee_id
                commission.title = title
                commission.type = request.POST.get('type')
                commission.amount = amount
                commission.created_by = request.user.creator_id()
                commission.save()
                logger.info("Commission created successfully with ID: %s", commission.id)
                
            messages.success(request, "Commission successfully created.")
            return redirect(get_redirect_url(request))
        except Exception as e:
            logger.exception("Error in store(): %s", e)
            messages.error(request, "An error occurred while creating commission.")
            return redirect(get_redirect_url(request))

    def show(self, request: HttpRequest, commission_id: int) -> HttpResponse:
        logger.info("CommissionController.show() called for commission ID: %s", commission_id)
        try:
            # The show method here redirects; you might add additional logging or processing.
            return redirect('commission_index')
        except Exception as e:
            logger.exception("Error in show(): %s", e)
            messages.error(request, "An error occurred while retrieving commission details.")
            return redirect(get_redirect_url(request))

    def edit(self, request: HttpRequest, commission_id: int) -> HttpResponse:
        logger.info("CommissionController.edit() called for commission ID: %s", commission_id)
        try:
            commission: Commission = get_object_or_404(Commission, pk=commission_id)
            if request.user.has_perm('edit commission'):
                if commission.created_by == request.user.creator_id():
                    commissions = Commission.commissiontype
                    logger.debug("Rendering edit view for commission ID: %s", commission_id)
                    return render(request, 'commission/edit.html', {
                        'commission': commission,
                        'commissions': commissions
                    })
                else:
                    logger.warning("User [%s] is not allowed to edit commission ID: %s", request.user, commission_id)
                    return JsonResponse({'error': "Permission denied."}, status=401)
            else:
                logger.warning("User [%s] lacks 'edit commission' permission.", request.user)
                return JsonResponse({'error': "Permission denied."}, status=401)
        except Exception as e:
            logger.exception("Error in edit(): %s", e)
            return JsonResponse({'error': "An error occurred."}, status=500)

    def update(self, request: HttpRequest, commission_id: int) -> HttpResponse:
        logger.info("CommissionController.update() called for commission ID: %s", commission_id)
        try:
            if not request.user.has_perm('edit commission'):
                logger.warning("User [%s] lacks 'edit commission' permission.", request.user)
                messages.error(request, get_exception_class_message(PermissionDenied, __class__.__name__))
                return redirect(get_redirect_url(request))

            commission: Commission = get_object_or_404(Commission, pk=commission_id)
            if commission.created_by != request.user.creator_id():
                logger.warning("User [%s] not authorized to update commission ID: %s", request.user, commission_id)
                messages.error(request, get_exception_class_message(PermissionDenied, __class__.__name__))
                return redirect(get_redirect_url(request))

            title: Any = request.POST.get('title')
            amount: Any = request.POST.get('amount')
            if not title or not amount:
                logger.error("Missing required update fields for commission ID [%s]: title=[%s], amount=[%s]", commission_id, title, amount)
                messages.error(request, "Title and Amount are required.")
                return redirect(get_redirect_url(request))

            # Critical update: using transaction.atomic() for ACID compliance.
            with transaction.atomic():
                commission.title = title
                commission.type = request.POST.get('type')
                commission.amount = amount
                commission.save()
                logger.info("Commission ID [%s] updated successfully.", commission_id)

            messages.success(request, "Commission successfully updated.")
            return redirect(get_redirect_url(request))
        except Exception as e:
            logger.exception("Error in update(): %s", e)
            messages.error(request, "An error occurred while updating commission.")
            return redirect(get_redirect_url(request))

    def destroy(self, request: HttpRequest, commission_id: int) -> HttpResponse:
        logger.info("CommissionController.destroy() called for commission ID: %s", commission_id)
        try:
            if not request.user.has_perm('delete commission'):
                logger.warning("User [%s] lacks 'delete commission' permission.", request.user)
                messages.error(request, get_exception_class_message(PermissionDenied, __class__.__name__))
                return redirect(get_redirect_url(request))

            commission: Commission = get_object_or_404(Commission, pk=commission_id)
            if commission.created_by != request.user.creator_id():
                logger.warning("User [%s] is not allowed to delete commission ID: %s", request.user, commission_id)
                messages.error(request, get_exception_class_message(PermissionDenied, __class__.__name__))
                return redirect(get_redirect_url(request))

            with transaction.atomic():
                commission.delete()
                logger.info("Commission ID [%s] deleted successfully.", commission_id)
            messages.success(request, "Commission successfully deleted.")
            return redirect(get_redirect_url(request))
        except Exception as e:
            logger.exception("Error in destroy(): %s", e)
            messages.error(request, "An error occurred while deleting commission.")
            return redirect(get_redirect_url(request))
