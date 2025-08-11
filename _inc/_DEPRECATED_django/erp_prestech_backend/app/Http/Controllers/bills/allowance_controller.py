from django.contrib import messages
from django.contrib.auth.mixins import LoginRequiredMixin
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from ....configs.messages_templates import get_exception_class_message
from .._helpers.http import get_redirect_url
from ....Models.bills.allowance import Allowance
from ....Models.bills.allowance_option import AllowanceOption
from ....Models.configs.permission import Permission
from ....Models.individuals.employee import Employee
from ....Models.utils.utility import Utility
from .._traits.controller import Controller

class AllowanceController(LoginRequiredMixin, Controller):

  def allowance_create(self, request: HttpRequest, employee_id: int) -> HttpResponse:
    try:
      allowance_options = AllowanceOption.objects.filter(
        created_by=Utility.current_user_creator_id(request.user)
      ).values_list('id', 'name')
    except Exception as e:
      print(f"Failed to fetch allowance options: {e.__class__.__name__}: {e}")
      messages.error(request, "Error fetching allowance options.")
      return redirect(get_redirect_url(request))
    employee = get_object_or_404(Employee, pk=employee_id)
    allowance_types = Allowance.ALLOWANCE_TYPE
    return render(request, 'allowance/create.html', {
      'employee': employee,
      'allowance_options': dict(allowance_options),
      'allowance_types': allowance_types
    })

  def store(self, request: HttpRequest) -> HttpResponse:
    if not Permission.user_can(request.user, 'create_allowance'):
      messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))
      return redirect(get_redirect_url(request))
    employee_id = request.POST.get('employee_id')
    option_id = request.POST.get('allowance_option')
    title = request.POST.get('title')
    allowance_type = request.POST.get('type')
    amount = request.POST.get('amount')
    if not all([employee_id, option_id, title, amount]):
      messages.error(request, "All fields are required.")
      return redirect(get_redirect_url(request))
    try:
      Allowance.objects.create(
        employee_id=employee_id,
        allowance_option_id=option_id,
        title=title,
        type=allowance_type,
        amount=amount,
        created_by=Utility.current_user_creator_id(request.user)
      )
      messages.success(request, "Allowance successfully created.")
    except Exception as e:
      messages.error(request, "Failed to create Allowance.")
      print(f"Failed to CREATE Allowance: {e.__class__.__name__}: {e}")
    return redirect(get_redirect_url(request))

  def show(self, request: HttpRequest, allowance_id: int) -> HttpResponse:
    return redirect('allowance_index')

  def edit(self, request: HttpRequest, allowance_id: int) -> HttpResponse:
    allowance = get_object_or_404(Allowance, pk=allowance_id)
    if not Permission.user_can(request.user, 'edit_allowance') or allowance.created_by != Utility.current_user_creator_id(request.user):
      return redirect(get_redirect_url(request))
    try:
      allowance_options = AllowanceOption.objects.filter(
        created_by=Utility.current_user_creator_id(request.user)
      ).values_list('id', 'name')
    except Exception as e:
      print(f"Failed to fetch allowance options: {e.__class__.__name__}: {e}")
      messages.error(request, "Error fetching allowance options.")
      return redirect(get_redirect_url(request))
    return render(request, 'allowance/edit.html', {
      'allowance': allowance,
      'allowance_options': dict(allowance_options),
      'allowance_types': Allowance.ALLOWANCE_TYPE
    })

  def update(self, request: HttpRequest, allowance_id: int) -> HttpResponse:
    allowance = get_object_or_404(Allowance, pk=allowance_id)
    if not Permission.user_can(request.user, 'edit_allowance') or allowance.created_by != Utility.current_user_creator_id(request.user):
      messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))
      return redirect(get_redirect_url(request))
    option_id = request.POST.get('allowance_option')
    title = request.POST.get('title')
    allowance_type = request.POST.get('type')
    amount = request.POST.get('amount')
    if not all([option_id, title, amount]):
      messages.error(request, "All fields are required.")
      return redirect(get_redirect_url(request))
    allowance.allowance_option_id = option_id
    allowance.title = title
    allowance.type = allowance_type
    allowance.amount = amount
    try:
      allowance.save()
      messages.success(request, "Allowance successfully updated.")
    except Exception as e:
      messages.error(request, "Failed to update Allowance.")
      print(f"Failed to UPDATE Allowance: {e.__class__.__name__}: {e}")
    return redirect(get_redirect_url(request))

  def destroy(self, request: HttpRequest, allowance_id: int) -> HttpResponse:
    allowance = get_object_or_404(Allowance, pk=allowance_id)
    if not Permission.user_can(request.user, 'delete_allowance') or allowance.created_by != Utility.current_user_creator_id(request.user):
      messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))
      return redirect(get_redirect_url(request))
    try:
      allowance.delete()
      messages.success(request, "Allowance successfully deleted.")
    except Exception as e:
      messages.error(request, "Failed to delete Allowance.")
      print(f"Failed to DELETE Allowance: {e.__class__.__name__}: {e}")
    return redirect(get_redirect_url(request))
