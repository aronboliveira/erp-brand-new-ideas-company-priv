import logging
import inspect
from typing import Any
from django.http import HttpRequest
from django.shortcuts import render, redirect, get_object_or_404
from django.contrib import messages
from django.core.exceptions import PermissionDenied

from ....Models.companies.branch import Branch
from ....Models.companies.job import Job
from ....Models.companies.job_category import JobCategory
from ....Models.individuals.job_stage import JobStage
from ....Models.individuals.job_application import JobApplication
from ....Models.individuals.job_application_note import JobApplicationNote
from ....Models.shapes.custom_question import CustomQuestion
from ....Models.utils.utility import Utility

from .._traits.controller import Controller
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception

logger = logging.getLogger(__name__)

class JobController(Controller):

    @classmethod
    def index(cls, request: HttpRequest) -> Any:
        CNAME = cls.__name__; MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            user = request.user
            if not user.is_authenticated:
                messages.error(request, 'Permission denied.')
                return redirect('login')
            if not user.has_perm('manage_job'):
                messages.error(request, 'Permission denied.')
                return redirect('somewhere_else')

            jobs_qs = Job.objects.filter(created_by=user.creator_id).select_related('createdBy')
            jobs_qs = jobs_qs.prefetch_related('branches')
            data = {
                'total':    jobs_qs.count(),
                'active':   jobs_qs.filter(status='active').count(),
                'in_active':jobs_qs.filter(status='in_active').count(),
            }
            return render(request, 'job/index.html', {'jobs': jobs_qs, 'data': data})
        except PermissionDenied as err:
            return default_permission_denial(request, err=err, ref=REF, logger=logger)
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def create(cls, request: HttpRequest) -> Any:
        CNAME = cls.__name__; MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            user = request.user
            if not user.is_authenticated:
                messages.error(request, 'Permission denied.')
                return redirect('login')

            categories_qs = JobCategory.objects.filter(created_by=user.creator_id)
            categories_dict = {'': '--', **{str(c.id): c.title for c in categories_qs}}

            branches_qs = Branch.objects.filter(created_by=user.creator_id)
            branches_dict = {'0': 'All', **{str(b.id): b.name for b in branches_qs}}

            status = Job.STATUS
            questions_qs = CustomQuestion.objects.filter(created_by=user.creator_id)

            return render(request, 'job/create.html', {
                'categories':    categories_dict,
                'status':        status,
                'branches':      branches_dict,
                'customQuestion':questions_qs
            })
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def store(cls, request: HttpRequest) -> Any:
        CNAME = cls.__name__; MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            user = request.user
            if not user.is_authenticated:
                messages.error(request, 'Permission denied.')
                return redirect('login')
            if not user.has_perm('create_job'):
                messages.error(request, 'Permission denied.')
                return redirect('job_index')

            if request.method == 'POST':
                required = [
                    'title','branch','category','skill','position',
                    'start_date','end_date','description','requirement'
                ]
                for f in required:
                    if not request.POST.get(f,'').strip():
                        messages.error(request, f'{f} is required.')
                        return redirect('job_create')

                job = Job(
                    title            = request.POST['title'].strip(),
                    branch           = request.POST['branch'].strip(),
                    category         = request.POST['category'].strip(),
                    skill            = request.POST['skill'].strip(),
                    position         = request.POST.get('position','0'),
                    status           = request.POST.get('status','active'),
                    start_date       = request.POST.get('start_date',''),
                    end_date         = request.POST.get('end_date',''),
                    description      = request.POST.get('description',''),
                    requirement      = request.POST.get('requirement',''),
                    code             = Utility.unique_code(),
                    applicant        = ','.join(request.POST.getlist('applicant')),
                    visibility       = ','.join(request.POST.getlist('visibility')),
                    custom_question  = ','.join(request.POST.getlist('custom_question')),
                    created_by       = user.creator_id
                )
                job.save()
                messages.success(request, 'Job successfully created.')
            return redirect('job_index')
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def show(cls, request: HttpRequest, job_id: int) -> Any:
        CNAME = cls.__name__; MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            user = request.user
            if not user.is_authenticated:
                messages.error(request, 'Permission denied.')
                return redirect('login')

            job = get_object_or_404(Job, pk=job_id)
            status = Job.STATUS
            job.applicant  = job.applicant.split(',')  if job.applicant else []
            job.visibility = job.visibility.split(',') if job.visibility else []
            job.skill      = job.skill.split(',')      if job.skill else []

            return render(request, 'job/show.html', {
                'status': status,
                'job':    job
            })
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def edit(cls, request: HttpRequest, job_id: int) -> Any:
        CNAME = cls.__name__; MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            user = request.user
            if not user.is_authenticated:
                messages.error(request, 'Permission denied.')
                return redirect('login')

            job = get_object_or_404(Job, pk=job_id)
            categories_qs = JobCategory.objects.filter(created_by=user.creator_id)
            categories_dict = {'': '--', **{str(c.id): c.title for c in categories_qs}}

            branches_qs = Branch.objects.filter(created_by=user.creator_id)
            branches_dict = {'0': 'All', **{str(b.id): b.name for b in branches_qs}}

            status = Job.STATUS
            job.applicant       = job.applicant.split(',')       if job.applicant else []
            job.visibility      = job.visibility.split(',')      if job.visibility else []
            job.custom_question = job.custom_question.split(',') if job.custom_question else []

            questions_qs = CustomQuestion.objects.filter(created_by=user.creator_id)

            return render(request, 'job/edit.html', {
                'categories':    categories_dict,
                'status':        status,
                'branches':      branches_dict,
                'job':           job,
                'customQuestion':questions_qs
            })
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def update(cls, request: HttpRequest, job_id: int) -> Any:
        CNAME = cls.__name__; MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            user = request.user
            if not user.is_authenticated:
                messages.error(request, 'Permission denied.')
                return redirect('login')
            if not user.has_perm('edit_job'):
                messages.error(request, 'Permission denied.')
                return redirect('job_index')

            if request.method == 'POST':
                job = get_object_or_404(Job, pk=job_id)
                required = [
                    'title','branch','category','skill','position',
                    'start_date','end_date','description','requirement'
                ]
                for f in required:
                    if not request.POST.get(f,'').strip():
                        messages.error(request, f'{f} is required.')
                        return redirect('somewhere_else')

                job.title           = request.POST['title'].strip()
                job.branch          = request.POST['branch'].strip()
                job.category        = request.POST['category'].strip()
                job.skill           = request.POST['skill'].strip()
                job.position        = request.POST.get('position','0')
                job.status          = request.POST.get('status','active')
                job.start_date      = request.POST.get('start_date','')
                job.end_date        = request.POST.get('end_date','')
                job.description     = request.POST.get('description','')
                job.requirement     = request.POST.get('requirement','')
                job.applicant       = ','.join(request.POST.getlist('applicant'))
                job.visibility      = ','.join(request.POST.getlist('visibility'))
                job.custom_question = ','.join(request.POST.getlist('custom_question'))
                job.save()

                messages.success(request, 'Job successfully updated.')
            return redirect('job_index')
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def destroy(cls, request: HttpRequest, job_id: int) -> Any:
        CNAME = cls.__name__; MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            user = request.user
            if not user.is_authenticated:
                messages.error(request, 'Permission denied.')
                return redirect('login')

            job_ids = JobApplication.objects.filter(job=job_id).values_list('id', flat=True)
            JobApplicationNote.objects.filter(application_id__in=job_ids).delete()
            JobApplication.objects.filter(job=job_id).delete()
            get_object_or_404(Job, pk=job_id).delete()

            messages.success(request, 'Job successfully deleted.')
            return redirect('job_index')
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def career(cls, request: HttpRequest, user_id: int, lang: str) -> Any:
        CNAME = cls.__name__; MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            request.session['lang'] = lang
            jobs_qs = Job.objects.filter(created_by=user_id)\
                                 .select_related('createdBy')\
                                 .prefetch_related('branches')
            company_settings = {}
            languages      = Utility.languages()
            current_lang   = request.session.get('lang', 'en')
            return render(request, 'job/career.html', {
                'companySettings': company_settings,
                'jobs':            jobs_qs,
                'languages':       languages,
                'currantLang':     current_lang,
                'id':              user_id
            })
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def job_requirement(cls, request: HttpRequest, code: str, lang: str) -> Any:
        CNAME = cls.__name__; MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            job = Job.objects.filter(code=code).first()
            if not job:
                messages.error(request, 'Job not found.')
                return redirect('somewhere_else')
            if job.status == 'in_active':
                messages.error(request, 'Permission Denied.')
                return redirect(request.META.get('HTTP_REFERER', 'somewhere_else'))

            request.session['lang'] = lang
            company_settings = {}
            languages       = Utility.languages()
            current_lang    = request.session.get('lang') or (job.createdBy.lang if job.createdBy else 'en')

            return render(request, 'job/requirement.html', {
                'companySettings': company_settings,
                'job':             job,
                'languages':       languages,
                'currantLang':     current_lang
            })
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def job_apply(cls, request: HttpRequest, code: str, lang: str) -> Any:
        CNAME = cls.__name__; MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            request.session['lang'] = lang
            job = Job.objects.filter(code=code).first()
            if not job:
                messages.error(request, 'Job not found.')
                return redirect('somewhere_else')

            company_settings = {}
            questions        = CustomQuestion.objects.filter(created_by=job.created_by)
            languages        = Utility.languages()
            current_lang     = request.session.get('lang') or (job.createdBy.lang if job.createdBy else 'en')

            return render(request, 'job/apply.html', {
                'companySettings': company_settings,
                'job':             job,
                'questions':       questions,
                'languages':       languages,
                'currantLang':     current_lang
            })
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)

    @classmethod
    def job_apply_data(cls, request: HttpRequest, code: str) -> Any:
        CNAME = cls.__name__; MNAME = inspect.currentframe().f_code.co_name
        REF = f"{CNAME}::{MNAME}"
        try:
            if request.method != 'POST':
                messages.error(request, 'Method not allowed.')
                return redirect('somewhere_else')

            name  = request.POST.get('name','').strip()
            email = request.POST.get('email','').strip()
            phone = request.POST.get('phone','').strip()
            if not (name and email and phone):
                messages.error(request, 'Name, Email, and Phone are required.')
                return redirect(request.META.get('HTTP_REFERER','somewhere_else'))

            job = Job.objects.filter(code=code).first()
            if not job:
                messages.error(request, 'Job not found.')
                return redirect('somewhere_else')

            profile_file = request.FILES.get('profile')
            resume_file  = request.FILES.get('resume')
            profile_name = resume_name = ''
            if profile_file:
                result = Utility.update_storage_limit(job.created_by, profile_file.size)
                if result == 1:
                    profile_name = Utility.handle_upload(profile_file, 'uploads/job/profile') or ''
            if resume_file:
                result = Utility.update_storage_limit(job.created_by, resume_file.size)
                if result == 1:
                    resume_name = Utility.handle_upload(resume_file, 'uploads/job/resume') or ''

            stage = JobStage.objects.filter(created_by=job.created_by).first()
            ja = JobApplication(
                job               = job.id,
                name              = name,
                email             = email,
                phone             = phone,
                profile           = profile_name,
                resume            = resume_name,
                cover_letter      = request.POST.get('cover_letter',''),
                dob               = request.POST.get('dob',''),
                gender            = request.POST.get('gender',''),
                country           = request.POST.get('country',''),
                state             = request.POST.get('state',''),
                city              = request.POST.get('city',''),
                custom_question   = Utility.json_dumps_safe(request.POST.getlist('question')),
                created_by        = job.created_by,
                stage             = stage.id if stage else None
            )
            ja.save()

            messages.success(request, 'Job application successfully sent')
            return redirect(request.META.get('HTTP_REFERER','somewhere_else'))
        except Exception as err:
            return default_undefined_exception(request, err=err, ref=REF, logger=logger)
