import logging
import inspect
from django.core.exceptions import PermissionDenied
from django.contrib import messages
from django.db import transaction
from django.shortcuts import redirect, render
from django.http import HttpRequest, HttpResponse
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from ....Models.companies.job_category import JobCategory
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class JobCategoryController(Controller):

  @classmethod
  def index(cls, request: HttpRequest) -> HttpResponse:
    CN = cls.__name__
    MN = inspect.currentframe().f_code.co_name
    try:
      if not request.user.has_perm('manage_job_category'):
        return default_permission_denial(
          request,
          err=PermissionDenied('manage_job_category'),
          ref=f'{CN}::{MN}',
          logger=logger
        )
      categories = JobCategory.objects.filter(created_by=request.user.id)
      return render(request, 'jobCategory/index.html', {'categories': categories})
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=f'{CN}::{MN}',
        logger=logger
      )

  @classmethod
  def create(cls, request: HttpRequest) -> HttpResponse:
    CN = cls.__name__
    MN = inspect.currentframe().f_code.co_name
    try:
      return render(request, 'jobCategory/create.html')
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=f'{CN}::{MN}',
        logger=logger
      )

  @classmethod
  def store(cls, request: HttpRequest) -> HttpResponse:
    CN = cls.__name__
    MN = inspect.currentframe().f_code.co_name
    if request.method != 'POST':
      return redirect(get_redirect_url(request))
    try:
      if not request.user.has_perm('create_job_category'):
        return default_permission_denial(
          request,
          err=PermissionDenied('create_job_category'),
          ref=f'{CN}::{MN}',
          logger=logger
        )
      with transaction.atomic():
        title = request.POST.get('title', '').strip()
        if not title:
          messages.error(request, 'Title is required.')
          return redirect(get_redirect_url(request))
        jc = JobCategory(title=title, created_by=request.user.id)
        jc.save()
        messages.success(request, 'Job category successfully created.')
        return redirect(get_redirect_url(request))
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=f'{CN}::{MN}',
        logger=logger
      )

  @classmethod
  def show(cls, request: HttpRequest, jobcategory_id: int) -> HttpResponse:
    CN = cls.__name__
    MN = inspect.currentframe().f_code.co_name
    try:
      return redirect(get_redirect_url(request))
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=f'{CN}::{MN}',
        logger=logger
      )

  @classmethod
  def edit(cls, request: HttpRequest, jobcategory_id: int) -> HttpResponse:
    CN = cls.__name__
    MN = inspect.currentframe().f_code.co_name
    try:
      jc = JobCategory.objects.filter(id=jobcategory_id).first()
      if not jc:
        messages.error(request, 'Job category not found.')
        return redirect(get_redirect_url(request))
      return render(request, 'jobCategory/edit.html', {'jobCategory': jc})
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=f'{CN}::{MN}',
        logger=logger
      )

  @classmethod
  def update(cls, request: HttpRequest, jobcategory_id: int) -> HttpResponse:
    CN = cls.__name__
    MN = inspect.currentframe().f_code.co_name
    if request.method != 'POST':
      return redirect(get_redirect_url(request))
    try:
      if not request.user.has_perm('edit_job_category'):
        return default_permission_denial(
          request,
          err=PermissionDenied('edit_job_category'),
          ref=f'{CN}::{MN}',
          logger=logger
        )
      with transaction.atomic():
        jc = JobCategory.objects.filter(id=jobcategory_id).first()
        if not jc:
          messages.error(request, 'Job category not found.')
          return redirect(get_redirect_url(request))
        title = request.POST.get('title', '').strip()
        if not title:
          messages.error(request, 'Title is required.')
          return redirect(get_redirect_url(request))
        jc.title = title
        jc.save()
        messages.success(request, 'Job category successfully updated.')
        return redirect(get_redirect_url(request))
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=f'{CN}::{MN}',
        logger=logger
      )

  @classmethod
  def destroy(cls, request: HttpRequest, jobcategory_id: int) -> HttpResponse:
    CN = cls.__name__
    MN = inspect.currentframe().f_code.co_name
    try:
      if not request.user.has_perm('delete_job_category'):
        return default_permission_denial(
          request,
          err=PermissionDenied('delete_job_category'),
          ref=f'{CN}::{MN}',
          logger=logger
        )
      with transaction.atomic():
        jc = JobCategory.objects.filter(
          id=jobcategory_id,
          created_by=request.user.id
        ).first()
        if not jc:
          messages.error(request, 'Permission denied.')
          return redirect(get_redirect_url(request))
        jc.delete()
        messages.success(request, 'Job category successfully deleted.')
        return redirect(get_redirect_url(request))
    except Exception as e:
      return default_undefined_exception(
        request,
        err=e,
        ref=f'{CN}::{MN}',
        logger=logger
      )
