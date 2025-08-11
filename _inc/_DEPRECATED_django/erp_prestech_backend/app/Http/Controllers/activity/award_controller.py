import logging
import inspect
from typing import Any, Dict, Optional
from django.shortcuts import render, redirect, get_object_or_404
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.views.decorators.http import require_http_methods
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.db import transaction
from django.core.exceptions import PermissionDenied
from django.utils.decorators import method_decorator
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from ....configs.messages_templates import get_lacking_field_message
from ....Models.activity.award import Award
from ....Models.activity.award_type import AwardType
from ....Models.individuals.employee import Employee
from ....Models.utils.utility import Utility
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class AwardController(Controller):
    CL = __qualname__

    @staticmethod
    def MN() -> str:
        return inspect.currentframe().f_code.co_name

    @method_decorator(login_required)
    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        logger.info("%s.%s() called by user: %s", cls.CL, cls.MN(), request.user)
        try:
            if not request.user.has_perm('app.manage_award'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=f'{cls.CL}::{cls.MN()}',
                    logger=logger
                )

            employees = Employee.objects.filter(created_by=request.user.id)
            awardtypes = AwardType.objects.filter(created_by=request.user.id)

            if request.user.groups.filter(name='Employee').exists():
                emp = Employee.objects.filter(user_id=request.user.id).first()
                if not emp:
                    return default_permission_denial(
                        request,
                        err=PermissionDenied('Employee record not found.'),
                        ref=f'{cls.CL}::{cls.MN()}',
                        logger=logger
                    )
                awards = Award.objects.filter(employee_id=emp.id).select_related('employee', 'award_type')
                logger.debug("Awards filtered for employee ID: %s", emp.id)
            else:
                awards = Award.objects.filter(created_by=request.user.id).select_related('employee', 'award_type')
                logger.debug("Awards filtered for creator ID: %s", request.user.id)

            logger.info(
                "%s.%s() rendering award/index.html with %d awards.",
                cls.CL, cls.MN(), awards.count()
            )
            return render(request, 'award/index.html', {
                'awards': awards,
                'employees': employees,
                'awardtypes': awardtypes
            })

        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.CL}::{cls.MN()}',
                logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        logger.info("%s.%s() called by user: %s", cls.CL, cls.MN(), request.user)
        try:
            if not request.user.has_perm('app.create_award'):
                return JsonResponse({'error': 'Permission denied.'}, status=401)

            employees = Employee.objects.filter(created_by=request.user.id).values_list('id', 'name')
            awardtypes = AwardType.objects.filter(created_by=request.user.id).values_list('id', 'name')
            logger.debug(
                "%s.%s() fetched %d employees and %d award types.",
                cls.CL, cls.MN(), employees.count(), awardtypes.count()
            )
            return render(request, 'award/create.html', {
                'employees': employees,
                'awardtypes': awardtypes
            })

        except Exception as e:
            return JsonResponse({'error': f'An error occurred: {e}.'}, status=500)

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(["POST"])
    def store(cls, request: HttpRequest) -> HttpResponse:
        logger.info("%s.%s() called by user: %s", cls.CL, cls.MN(), request.user)
        try:
            if not request.user.has_perm('app.create_award'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=f'{cls.CL}::{cls.MN()}',
                    logger=logger
                )

            required_fields = ['employee_id', 'award_type', 'date', 'gift']
            for field in required_fields:
                if not request.POST.get(field):
                    messages.error(request, get_lacking_field_message(field, cls.CL))
                    return redirect(get_redirect_url(request))

            with transaction.atomic():
                award = Award(
                    employee_id=request.POST['employee_id'],
                    award_type_id=request.POST['award_type'],
                    date=request.POST['date'],
                    gift=request.POST['gift'],
                    description=request.POST.get('description', ''),
                    created_by=request.user.id
                )
                award.save()
                logger.info("Award created with ID: %s", award.id)

                setting = Utility.settings(request.user.id)
                emp = Employee.objects.get(id=request.POST['employee_id'])
                award_type = AwardType.objects.get(id=request.POST['award_type'])
                notif_data = {
                    'award_name': award_type.name,
                    'employee_name': emp.name,
                    'award_date': request.POST['date'],
                }
                if setting.get('award_notification') == 1:
                    Utility.send_slack_msg('new_award', notif_data)
                    logger.debug("Sent Slack notification for new award.")
                if setting.get('telegram_award_notification') == 1:
                    Utility.send_telegram_msg('new_award', notif_data)
                    logger.debug("Sent Telegram notification for new award.")

                resp: Optional[Dict[str, Any]] = None
                if setting.get('new_award') == 1:
                    awardArr = {
                        'award_name': emp.name,
                        'award_email': emp.email
                    }
                    resp = Utility.sendEmailTemplate('new_award', {emp.id: emp.email}, awardArr)
                    logger.debug("Email template sent with response: %s", resp)

                webhook = Utility.webhookSetting('New Award')
                if webhook:
                    parameter = Utility.to_json(award)
                    status = Utility.WebhookCall(webhook['url'], parameter, webhook['method'])
                    logger.debug("Webhook call status: %s", status)
                    if not status:
                        messages.error(request, 'Webhook call failed.')
                        return redirect(get_redirect_url(request))

            msg = 'Award successfully created.'
            if resp and (not resp.get('is_success')) and resp.get('error'):
                msg += f"<br><span class='text-danger'>{resp['error']}</span>"
            messages.success(request, msg)
            logger.info("%s.%s() completed successfully.", cls.CL, cls.MN())
            return redirect('award.index')

        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.CL}::{cls.MN()}',
                logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def edit(cls, request: HttpRequest, pk: Any) -> HttpResponse:
        logger.info("%s.%s() called for award ID: %s", cls.CL, cls.MN(), pk)
        try:
            award = get_object_or_404(Award, pk=pk)
            if not request.user.has_perm('app.edit_award') or award.created_by != request.user.id:
                return JsonResponse({'error': 'Permission denied.'}, status=401)

            employees = Employee.objects.filter(created_by=request.user.id).values_list('id', 'name')
            awardtypes = AwardType.objects.filter(created_by=request.user.id).values_list('id', 'name')
            logger.debug("%s.%s() fetched data for award ID: %s", cls.CL, cls.MN(), pk)
            return render(request, 'award/edit.html', {
                'award': award,
                'awardtypes': awardtypes,
                'employees': employees
            })

        except Exception as e:
            return JsonResponse({'error': f'An error occurred: {e}'}, status=500)

    @method_decorator(login_required)
    @classmethod
    def update(cls, request: HttpRequest, pk: Any) -> HttpResponse:
        logger.info("%s.%s() called for award ID: %s", cls.CL, cls.MN(), pk)
        try:
            award = get_object_or_404(Award, pk=pk)
            if not request.user.has_perm('app.edit_award') or award.created_by != request.user.id:
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=f'{cls.CL}::{cls.MN()}',
                    logger=logger
                )

            with transaction.atomic():
                award.employee_id = request.POST['employee_id']
                award.award_type_id = request.POST['award_type']
                award.date = request.POST['date']
                award.gift = request.POST['gift']
                award.description = request.POST.get('description', '')
                award.save()
                logger.info("Award ID [%s] updated successfully.", pk)
            messages.success(request, 'Award successfully updated.')
            return redirect('award.index')

        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.CL}::{cls.MN()}',
                logger=logger
            )

    @method_decorator(login_required)
    @classmethod
    def destroy(cls, request: HttpRequest, pk: Any) -> HttpResponse:
        logger.info("%s.%s() called for award ID: %s", cls.CL, cls.MN(), pk)
        try:
            award = get_object_or_404(Award, pk=pk)
            if not request.user.has_perm('app.delete_award') or award.created_by != request.user.id:
                return default_permission_denial(
                    request,
                    err=PermissionDenied('Permission denied.'),
                    ref=f'{cls.CL}::{cls.MN()}',
                    logger=logger
                )

            with transaction.atomic():
                award.delete()
                logger.info("Award ID [%s] deleted successfully.", pk)
            messages.success(request, 'Award successfully deleted.')
            return redirect('award.index')

        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{cls.CL}::{cls.MN()}',
                logger=logger
            )
