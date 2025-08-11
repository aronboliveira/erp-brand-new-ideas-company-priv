import inspect
import logging
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.http import HttpRequest, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.utils.decorators import method_decorator
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from ....Models.activity.complaint import Complaint
from ....Models.individuals.employee import Employee
from ....Models.utils.utility import Utility
from .._traits.controller import Controller
logger = logging.getLogger(__name__)

class ComplaintController(Controller):

    @method_decorator(login_required)
    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
      CN = cls.__name__ 
      MN = inspect.currentframe().f_code.co_name
      try:
        if not request.user.has_perm('manage complaint'):
            return default_permission_denial(request, err=None, ref=f'{CN}::{MN}')
        if request.user.type == 'Employee':
            emp = Employee.objects.filter(user_id=request.user.id).first()
            complaints = Complaint.objects.filter(complaint_from=emp.id).select_related('complaintFrom')
        else:
            emp = None
            complaints = Complaint.objects.filter(
                created_by=request.user.creator_id()
            ).select_related('complaintFrom')
        return render(request, 'complaint/index.html', {'complaints': complaints})
      except Exception as e:
        default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        return get_redirect_url(request)

    @method_decorator(login_required)
    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
      CN = cls.__name__ 
      MN = inspect.currentframe().f_code.co_name
      try:
        if not request.user.has_perm('create complaint'):
            return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', 
                                             json={'error': 'Permission Denied.', 'status': 401})
        user = request.user
        current_employee = Employee.objects.filter(user_id=user.id).values_list('name', 'id')
        if user.type == 'Employee':
            employees = Employee.objects.exclude(user_id=user.id).values_list('name', 'id')
        else:
            employees = Employee.objects.filter(
                created_by=user.creator_id()
            ).values_list('name', 'id')

        return render(request, 'complaint/create.html', {
            'employees': dict(employees),
            'current_employee': dict(current_employee)
        })
      except Exception as e:
        default_undefined_exception(request, err=e,
                                    ref=f'{CN}::{MN}', logger=logger)
        return get_redirect_url(request)

    @method_decorator(login_required)
    @classmethod
    def store(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
          if not request.user.has_perm('create complaint'):
              return default_permission_denial(request, err=None, ref=f'{CN}::{MN}')
          if request.user.type != 'Employee' and not request.POST.get('complaint_from'):
              messages.error(request, "Complaint from is required.")
              return redirect(get_redirect_url(request))
          if not request.POST.get('complaint_against') or not request.POST.get('title') or not request.POST.get('complaint_date'):
              messages.error(request, "Complaint against, title, and complaint date are required.")
              return redirect(get_redirect_url(request))

          complaint = Complaint()
          try:
              if request.user.type == 'Employee':
                  emp = Employee.objects.filter(user_id=request.user.id).first()
                  complaint.complaint_from = emp.id
              else:
                  complaint.complaint_from = request.POST['complaint_from']

              complaint.complaint_against = request.POST['complaint_against']
              complaint.title = request.POST['title']
              complaint.complaint_date = request.POST['complaint_date']
              complaint.description = request.POST.get('description', '')
              complaint.created_by = request.user.creator_id()
              complaint.save()
          except Exception as e:
              return default_undefined_exception(request, 
                                                err=e, 
                                                ref=f'{CN}::{MN}',
                                                logger=logger)

          try:
              settings_obj = Utility.settings()
              if settings_obj.get('complaint_resent') == 1:
                  employee = get_object_or_404(Employee, pk=complaint.complaint_against)
                  complaintArr = {
                      'complaint_name': employee.name,
                      'complaint_title': complaint.title,
                      'complaint_against': complaint.complaint_against,
                      'complaint_date': complaint.complaint_date,
                      'complaint_description': complaint.description,
                  }
                  resp = Utility.sendEmailTemplate('complaint_resent', {employee.id: employee.email}, complaintArr)
                  success_msg = "Complaint successfully created."
                  if resp and resp.get('is_success') is False and resp.get('error'):
                      success_msg += f"<br><span class='text-danger'>{resp['error']}</span>"
                  messages.success(request, success_msg)
                  return redirect('complaint_index')
          except Exception as e:
              return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}')

          messages.success(request, "Complaint successfully created.")
          return redirect('complaint_index')
        except Exception as e:
          default_undefined_exception(request, err=e,
                                      ref=f'{CN}::{MN}', logger=logger)
          get_redirect_url(request)

    @method_decorator(login_required)
    @classmethod
    def show(cls, request: HttpRequest, complaint_id: int) -> HttpResponse:
        # TODO: implement detailed view or permission check
        return redirect('complaint_index')

    @method_decorator(login_required)
    @classmethod
    def edit(cls, request: HttpRequest, complaint_id: int) -> HttpResponse:
        CN = cls.__name__ 
        MN = inspect.currentframe().f_code.co_name
        complaint = get_object_or_404(Complaint, pk=complaint_id)
        if not request.user.has_perm('edit complaint') or complaint.created_by != request.user.creator_id():
            return default_permission_denial(request, err=None, 
                                             ref=f'{CN}::{MN}',
                                             logger=logger,
                                             json={'error':'Permission denied.', 'status': 401})

        user = request.user
        current_employee = Employee.objects.filter(user_id=user.id).values_list('name', 'id')
        if user.type == 'Employee':
            employees = Employee.objects.exclude(user_id=user.id).values_list('name', 'id')
        else:
            employees = Employee.objects.filter(
                created_by=user.creator_id()
            ).values_list('name', 'id')

        return render(request, 'complaint/edit.html', {
            'complaint': complaint,
            'employees': dict(employees),
            'current_employee': dict(current_employee)
        })

    @method_decorator(login_required)
    @classmethod
    def update(cls, request: HttpRequest, complaint_id: int) -> HttpResponse:
        CN = cls.__name__ 
        MN = inspect.currentframe().f_code.co_name
        if not request.user.has_perm('edit complaint'):
            return default_permission_denial(request, err=None,
                                             ref=f'{CN}::{MN}', logger=logger)

        complaint = get_object_or_404(Complaint, pk=complaint_id)
        if complaint.created_by != request.user.creator_id():
            return default_permission_denial(request, err=None, 
                                             ref=f'{CN}::{MN}', logger=logger)

        if request.user.type != 'Employee' and not request.POST.get('complaint_from'):
            messages.error(request, "Complaint from is required.")
            return redirect(get_redirect_url(request))
        if not request.POST.get('complaint_against') or not request.POST.get('title') or not request.POST.get('complaint_date'):
            messages.error(request, "Complaint against, title, and complaint date are required.")
            return redirect(get_redirect_url(request))

        try:
            if request.user.type == 'Employee':
                emp = Employee.objects.filter(user_id=request.user.id).first()
                complaint.complaint_from = emp.id
            else:
                complaint.complaint_from = request.POST['complaint_from']

            complaint.complaint_against = request.POST['complaint_against']
            complaint.title = request.POST['title']
            complaint.complaint_date = request.POST['complaint_date']
            complaint.description = request.POST.get('description', '')
            complaint.save()
            messages.success(request, "Complaint successfully updated.")
        except Exception as e:
            return default_undefined_exception(request, err=e, 
                                               ref=f'{CN}::{MN}', logger=logger)

        return redirect('complaint_index')

    @method_decorator(login_required)
    @classmethod
    def destroy(cls, request: HttpRequest, complaint_id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        if not request.user.has_perm('delete complaint'):
            return default_permission_denial(request, err=None, 
                                             ref=f'{CN}::{MN}', logger=logger)

        complaint = get_object_or_404(Complaint, pk=complaint_id)
        if complaint.created_by != request.user.creator_id():
            return default_permission_denial(request, err=None, 
                                             ref=f'{CN}::{MN}', logger=logger)

        try:
            complaint.delete()
            messages.success(request, "Complaint successfully deleted.")
        except Exception as e:
            return default_undefined_exception(request, err=e, 
                                               ref=f'{CN}::{MN}', logger=logger)

        return redirect('complaint_index')
