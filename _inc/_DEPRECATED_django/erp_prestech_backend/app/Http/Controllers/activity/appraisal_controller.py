import json
import logging
import inspect
from typing import Any, Dict
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import render, redirect
from django.db import transaction
from django.template.loader import render_to_string
from django.core.exceptions import PermissionDenied
from django.contrib import messages

from ....Models.individuals.competencies import Competencies
from ....Models.individuals.employee import Employee
from ....Models.activity.appraisal import Appraisal
from ....Models.activity.performance_type import PerformanceType
from ....Models.planning.indicator import Indicator
from ....Models.companies.branch import Branch

from .._traits.controller import Controller
from .._helpers.http import get_redirect_url
from .._helpers.security import permission_required_custom
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception

logger = logging.getLogger(__name__)


class AppraisalController(Controller):

    @classmethod
    @permission_required_custom('manage appraisal', logger)
    def index(cls, request: HttpRequest) -> HttpResponse:
        ref = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
        logger.info(f"{ref} called by user: {request.user}")
        try:
            user = request.user
            creator_id = user.creator_id()
            competency_count = Competencies.objects.filter(created_by=creator_id).count()

            if user.type == 'Employee':
                employee = Employee.objects.filter(user_id=user.id).first()
                if not employee:
                    logger.error(f"{ref} employee record not found for user: {user}")
                    messages.error(request, 'Employee record not found.')
                    return redirect(get_redirect_url(request))

                appraisals = (
                    Appraisal.objects
                    .filter(created_by=creator_id,
                            branch=employee.branch_id,
                            employee=employee.id)
                    .select_related('employees', 'branches')
                )
            else:
                appraisals = (
                    Appraisal.objects
                    .filter(created_by=creator_id)
                    .select_related('employees', 'branches')
                )

            context: Dict[str, Any] = {
                'appraisals': appraisals,
                'competency_count': competency_count,
            }
            return render(request, 'appraisal/index.html', context)

        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=ref, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=ref, logger=logger)


    @classmethod
    @permission_required_custom('create appraisal', logger)
    def create(cls, request: HttpRequest) -> HttpResponse:
        ref = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
        logger.info(f"{ref} called by user: {request.user}")
        try:
            creator_id = request.user.creator_id()
            performance = PerformanceType.objects.filter(created_by=creator_id)
            branches = Branch.objects.filter(created_by=creator_id)
            return render(request, 'appraisal/create.html', {
                'branches': branches,
                'performance': performance,
            })
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=ref, logger=logger)


    @classmethod
    @permission_required_custom('create appraisal', logger)
    def store(cls, request: HttpRequest) -> HttpResponse:
        ref = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
        logger.info(f"{ref} called by user: {request.user}")
        try:
            branch = request.POST.get('branch')
            employee_id = request.POST.get('employee')
            if not branch or not employee_id:
                logger.error(f"{ref} branch or employee missing in request")
                messages.error(request, 'Branch and Employee are required.')
                return redirect(get_redirect_url(request))

            with transaction.atomic():
                appraisal = Appraisal(
                    branch=branch,
                    employee=employee_id,
                    appraisal_date=request.POST.get('appraisal_date'),
                    rating=json.dumps(request.POST.get('rating'), ensure_ascii=False),  # TODO: ensure rating is JSON serializable
                    remark=request.POST.get('remark'),
                    created_by=request.user.creator_id()
                )
                appraisal.save()
                logger.info(f"{ref} created successfully with ID: {appraisal.id}")

            messages.success(request, 'Appraisal successfully created.')
            return redirect('appraisal.index')

        except Exception as err:
            return default_undefined_exception(request, err=err, ref=ref, logger=logger)


    @classmethod
    @permission_required_custom('view appraisal', logger)
    def show(cls, request: HttpRequest, appraisal: Appraisal) -> HttpResponse:
        ref = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
        logger.info(f"{ref} called for appraisal ID: {appraisal.id}")
        try:
            rating = json.loads(appraisal.rating) if appraisal.rating else None
            creator_id = request.user.creator_id()
            performance_types = PerformanceType.objects.filter(created_by=creator_id)
            employee = Employee.objects.filter(id=appraisal.employee).first()
            if not employee:
                logger.error(f"{ref} employee not found for appraisal ID: {appraisal.id}")
                messages.error(request, 'Employee record not found.')
                return redirect(get_redirect_url(request))

            indicator = Indicator.objects.filter(
                branch=employee.branch_id,
                department=employee.department_id,
                designation=employee.designation_id
            ).first()

            ratings = json.loads(indicator.rating) if indicator and indicator.rating else None

            context = {
                'appraisal': appraisal,
                'performance_types': performance_types,
                'ratings': ratings,
                'rating': rating,
            }
            return render(request, 'appraisal/show.html', context)

        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=ref, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=ref, logger=logger)


    @classmethod
    @permission_required_custom('edit appraisal', logger)
    def edit(cls, request: HttpRequest, appraisal: Appraisal) -> HttpResponse:
        ref = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
        logger.info(f"{ref} called for appraisal ID: {appraisal.id}")
        try:
            creator_id = request.user.creator_id()
            performance_types = PerformanceType.objects.filter(created_by=creator_id)
            branches = Branch.objects.filter(created_by=creator_id)
            ratings = json.loads(appraisal.rating) if appraisal.rating else None

            context = {
                'branches': branches,
                'appraisal': appraisal,
                'performance_types': performance_types,
                'ratings': ratings,
            }
            return render(request, 'appraisal/edit.html', context)

        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=ref, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=ref, logger=logger)


    @classmethod
    @permission_required_custom('edit appraisal', logger)
    def update(cls, request: HttpRequest, appraisal: Appraisal) -> HttpResponse:
        ref = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
        logger.info(f"{ref} called for appraisal ID: {appraisal.id}")
        try:
            branch = request.POST.get('branch')
            employee_id = request.POST.get('employee')
            if not branch or not employee_id:
                logger.error(f"{ref} branch or employee missing in update request")
                messages.error(request, 'Branch and Employee are required.')
                return redirect(get_redirect_url(request))

            with transaction.atomic():
                appraisal.branch = branch
                appraisal.employee = employee_id
                appraisal.appraisal_date = request.POST.get('appraisal_date')
                appraisal.rating = json.dumps(request.POST.get('rating'), ensure_ascii=False)
                appraisal.remark = request.POST.get('remark')
                appraisal.save()
                logger.info(f"{ref} updated successfully for ID: {appraisal.id}")

            messages.success(request, 'Appraisal successfully updated.')
            return redirect('appraisal.index')

        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=ref, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=ref, logger=logger)


    @classmethod
    @permission_required_custom('delete appraisal', logger)
    def destroy(cls, request: HttpRequest, appraisal: Appraisal) -> HttpResponse:
        ref = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
        logger.info(f"{ref} called for appraisal ID: {appraisal.id}")
        try:
            if appraisal.created_by != request.user.creator_id():
                logger.warning(f"{ref} unauthorized delete attempt on appraisal {appraisal.id} by {request.user}")
                raise PermissionDenied

            with transaction.atomic():
                appraisal.delete()
                logger.info(f"{ref} deleted successfully for ID: {appraisal.id}")

            messages.success(request, 'Appraisal successfully deleted.')
            return redirect('appraisal.index')

        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=ref, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=ref, logger=logger)


    @classmethod
    @permission_required_custom('manage appraisal', logger)
    def emp_by_star(cls, request: HttpRequest) -> JsonResponse:
        ref = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
        logger.info(f"{ref} called")
        try:
            employee = Employee.objects.filter(id=request.POST.get('employee')).first()
            if not employee:
                logger.error(f"{ref} employee not found with ID: {request.POST.get('employee')}")
                return JsonResponse({'success': False, 'html': ''})

            indicator = Indicator.objects.filter(
                branch=employee.branch_id,
                department=employee.department_id,
                designation=employee.designation_id
            ).first()

            ratings = json.loads(indicator.rating) if indicator and indicator.rating else []
            performance_types = PerformanceType.objects.filter(created_by=request.user.creator_id())

            html = render_to_string('appraisal/star.html', {
                'ratings': ratings,
                'performance_types': performance_types
            })
            return JsonResponse({'success': True, 'html': html})

        except Exception as err:
            logger.exception(f"{ref} raised an error: {err}")
            return JsonResponse({'success': False, 'html': ''})


    @classmethod
    @permission_required_custom('manage appraisal', logger)
    def emp_by_star1(cls, request: HttpRequest) -> JsonResponse:
        ref = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
        logger.info(f"{ref} called")
        try:
            employee = Employee.objects.filter(id=request.POST.get('employee')).first()
            appraisal = Appraisal.objects.filter(id=request.POST.get('appraisal')).first()
            if not employee or not appraisal:
                logger.error(f"{ref} employee or appraisal not found")
                return JsonResponse({'success': False, 'html': ''})

            indicator = Indicator.objects.filter(
                branch=employee.branch_id,
                department=employee.department_id,
                designation=employee.designation_id
            ).first()

            ratings = json.loads(indicator.rating) if indicator and indicator.rating else None
            rating = json.loads(appraisal.rating) if appraisal.rating else None
            performance_types = PerformanceType.objects.filter(created_by=request.user.creator_id())

            html = render_to_string('appraisal/staredit.html', {
                'ratings': ratings,
                'rating': rating,
                'performance_types': performance_types
            })
            return JsonResponse({'success': True, 'html': html})

        except Exception as err:
            logger.exception(f"{ref} raised an error: {err}")
            return JsonResponse({'success': False, 'html': ''})


    @classmethod
    @permission_required_custom('manage appraisal', logger)
    def get_employee(cls, request: HttpRequest) -> JsonResponse:
        ref = f"{cls.__name__}::{inspect.currentframe().f_code.co_name}"
        logger.info(f"{ref} called")
        try:
            branch_id = request.GET.get('branch_id')
            employees = list(Employee.objects.filter(branch_id=branch_id).values())
            return JsonResponse({'employee': employees})

        except Exception as err:
            logger.exception(f"{ref} raised an error: {err}")
            return JsonResponse({'employee': []})
