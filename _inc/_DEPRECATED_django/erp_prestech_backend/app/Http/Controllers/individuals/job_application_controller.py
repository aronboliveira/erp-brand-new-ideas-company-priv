import logging
import inspect
from django.contrib.auth.decorators import login_required, permission_required
from django.db import transaction
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import redirect, render
from django.urls import reverse
from django.utils.decorators import method_decorator
from .._traits.controller import Controller
from .._helpers.error_handlers import default_undefined_exception

logger = logging.getLogger(__name__)

class JobApplicationController(Controller):

    @method_decorator(login_required)
    @classmethod
    @permission_required('app.manage_job_application', raise_exception=True)
    def index(cls, request: HttpRequest) -> HttpResponse:
        try:
            filter_ = {'start_date': '', 'end_date': '', 'job': ''}
            stages = []
            jobs = []
            return render(request, 'jobApplication/index.html', {
                'stages': stages,
                'jobs': jobs,
                'filter': filter_
            })
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                redirect_path=reverse('job-application.index')
            )

    @method_decorator(login_required)
    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        try:
            jobs = []
            questions = []
            return render(request, 'jobApplication/create.html', {
                'jobs': jobs,
                'questions': questions
            })
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to retrieve create form'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    @permission_required('app.create_job_application', raise_exception=True)
    @transaction.atomic
    def store(cls, request: HttpRequest) -> HttpResponse:
        if request.method != 'POST':
            return JsonResponse({'error': 'Invalid request method'}, status=405)
        try:
            data_ = request.POST or {}
            job_id = data_.get('job')
            name_ = data_.get('name')
            email_ = data_.get('email')
            phone_ = data_.get('phone')
            if not (job_id and name_ and email_ and phone_):
                return JsonResponse({'error': 'Validation failed'}, status=422)
            return redirect(reverse('job-application.index'))
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to create job application'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    @permission_required('app.show_job_application', raise_exception=True)
    def show(cls, request: HttpRequest, ids: str) -> HttpResponse:
        try:
            notes = []
            stages = []
            return render(request, 'jobApplication/show.html', {
                'jobApplication': None,
                'notes': notes,
                'stages': stages
            })
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                redirect_path=reverse('job-application.index')
            )

    @method_decorator(login_required)
    @classmethod
    @permission_required('app.delete_job_application', raise_exception=True)
    @transaction.atomic
    def destroy(cls, request: HttpRequest, application_id: int) -> HttpResponse:
        if request.method != 'POST':
            return JsonResponse({'error': 'Invalid request method'}, status=405)
        try:
            return redirect(reverse('job-application.index'))
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to delete job application'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    @permission_required('app.move_job_application', raise_exception=True)
    @transaction.atomic
    def order(cls, request: HttpRequest) -> HttpResponse:
        if request.method != 'POST':
            return JsonResponse({'error': 'Invalid request method'}, status=405)
        try:
            return redirect(reverse('job-application.index'))
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to reorder job applications'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    @permission_required('app.add_job_application_skill', raise_exception=True)
    @transaction.atomic
    def add_skill(cls, request: HttpRequest, application_id: int) -> HttpResponse:
        if request.method != 'POST':
            return JsonResponse({'error': 'Invalid request method'}, status=405)
        try:
            if not request.POST.get('skill'):
                return JsonResponse({'error': 'Validation failed'}, status=422)
            return redirect(reverse('job-application.show', args=[application_id]))
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to add skill'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    @permission_required('app.add_job_application_note', raise_exception=True)
    @transaction.atomic
    def add_note(cls, request: HttpRequest, application_id: int) -> HttpResponse:
        if request.method != 'POST':
            return JsonResponse({'error': 'Invalid request method'}, status=405)
        try:
            if not request.POST.get('note'):
                return JsonResponse({'error': 'Validation failed'}, status=422)
            return redirect(reverse('job-application.show', args=[application_id]))
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to add note'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    @permission_required('app.delete_job_application_note', raise_exception=True)
    @transaction.atomic
    def destroy_note(cls, request: HttpRequest, note_id: int) -> HttpResponse:
        if request.method != 'POST':
            return JsonResponse({'error': 'Invalid request method'}, status=405)
        try:
            return redirect(reverse('job-application.show', args=[1]))
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to delete note'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    def rating(cls, request: HttpRequest, application_id: int) -> JsonResponse:
        try:
            return JsonResponse({'success': 'Rating updated'}, status=200)
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to update rating'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    @transaction.atomic
    def archive(cls, request: HttpRequest, application_id: int) -> HttpResponse:
        if request.method != 'POST':
            return JsonResponse({'error': 'Invalid request method'}, status=405)
        try:
            return redirect(reverse('job.application.candidate'))
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to archive'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    @permission_required('app.manage_job_onBoard', raise_exception=True)
    def candidate(cls, request: HttpRequest) -> HttpResponse:
        try:
            archive_application = []
            return render(request, 'jobApplication/candidate.html', {
                'archive_application': archive_application
            })
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                redirect_path=reverse('job-application.index')
            )

    @method_decorator(login_required)
    @classmethod
    @permission_required('app.manage_job_onBoard', raise_exception=True)
    def job_on_board(cls, request: HttpRequest) -> HttpResponse:
        try:
            job_on_boards = []
            return render(request, 'jobApplication/onboard.html', {
                'jobOnBoards': job_on_boards
            })
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                redirect_path=reverse('job-application.index')
            )

    @method_decorator(login_required)
    @classmethod
    def job_board_create(cls, request: HttpRequest, application_id: int) -> HttpResponse:
        try:
            status = []
            job_type = []
            salary_duration = []
            salary_type = []
            applications = []
            return render(request, 'jobApplication/onboardCreate.html', {
                'id': application_id,
                'status': status,
                'applications': applications,
                'job_type': job_type,
                'salary_type': salary_type,
                'salary_duration': salary_duration
            })
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to retrieve job board creation form'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    @transaction.atomic
    def job_board_store(cls, request: HttpRequest, application_id: int) -> HttpResponse:
        if request.method != 'POST':
            return JsonResponse({'error': 'Invalid request method'}, status=405)
        try:
            return redirect(reverse('job.on.board'))
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to store job board'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    @transaction.atomic
    def job_board_update(cls, request: HttpRequest, board_id: int) -> HttpResponse:
        if request.method != 'POST':
            return JsonResponse({'error': 'Invalid request method'}, status=405)
        try:
            return redirect(reverse('job.on.board'))
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to update job board'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    def job_board_edit(cls, request: HttpRequest, board_id: int) -> HttpResponse:
        try:
            job_on_board = None
            status = []
            job_type = []
            salary_duration = []
            salary_type = []
            return render(request, 'jobApplication/onboardEdit.html', {
                'jobOnBoard': job_on_board,
                'status': status,
                'job_type': job_type,
                'salary_type': salary_type,
                'salary_duration': salary_duration
            })
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to retrieve job board edit form'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    @transaction.atomic
    def job_board_delete(cls, request: HttpRequest, board_id: int) -> HttpResponse:
        if request.method != 'POST':
            return JsonResponse({'error': 'Invalid request method'}, status=405)
        try:
            return redirect(reverse('job.on.board'))
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to delete job board'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    def job_board_convert(cls, request: HttpRequest, board_id: int) -> HttpResponse:
        try:
            company_settings = {}
            documents = []
            branches = {}
            departments = {}
            designations = {}
            employees = []
            employees_id = ''
            return render(request, 'jobApplication/convert.html', {
                'jobOnBoard': None,
                'employees': employees,
                'employeesId': employees_id,
                'departments': departments,
                'designations': designations,
                'documents': documents,
                'branches': branches,
                'company_settings': company_settings
            })
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to retrieve job board conversion form'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    @transaction.atomic
    def job_board_convert_data(cls, request: HttpRequest, board_id: int) -> HttpResponse:
        if request.method != 'POST':
            return JsonResponse({'error': 'Invalid request method'}, status=405)
        try:
            name_ = request.POST.get('name')
            dob_ = request.POST.get('dob')
            gender_ = request.POST.get('gender')
            phone_ = request.POST.get('phone')
            email_ = request.POST.get('email')
            password_ = request.POST.get('password')
            dept_ = request.POST.get('department_id')
            desg_ = request.POST.get('designation_id')
            address_ = request.POST.get('address')
            if not (name_ and dob_ and gender_ and phone_ and email_ and password_ and dept_ and desg_ and address_):
                return JsonResponse({'error': 'Validation failed'}, status=422)
            return redirect(reverse('job.on.board'))
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to convert job board'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    def get_by_job(cls, request: HttpRequest) -> JsonResponse:
        try:
            job_data = {}
            return JsonResponse(job_data, safe=False)
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to retrieve job'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    @transaction.atomic
    def stage_change(cls, request: HttpRequest) -> JsonResponse:
        if request.method != 'POST':
            return JsonResponse({'error': 'Invalid request method'}, status=405)
        try:
            return JsonResponse({'success': 'Candidate stage changed'}, status=200)
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to change stage'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    def offerletter_pdf(cls, request: HttpRequest, application_id: int) -> HttpResponse:
        try:
            return render(request, 'jobApplication/template/offerletterpdf.html', {
                'Offerletter': None,
                'name': None
            })
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to retrieve offer letter PDF'},
                status=400
            )

    @method_decorator(login_required)
    @classmethod
    def offerletter_doc(cls, request: HttpRequest, application_id: int) -> HttpResponse:
        try:
            return render(request, 'jobApplication/template/offerletterdocx.html', {
                'Offerletter': None,
                'name': None
            })
        except Exception as e:
            return default_undefined_exception(
                request, err=e,
                ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
                logger=logger,
                json={'error': 'Failed to retrieve offer letter Doc'},
                status=400
            )
