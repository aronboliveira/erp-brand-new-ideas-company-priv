import datetime
from django.contrib import messages
from django.contrib.auth.hashers import make_password
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.utils import timezone
from ....Models.individuals.role import Role
from ....Models.individuals.user import User
from ....Models.planning.contract import Contract
from ....Models.planning.estimation import Estimation
from ....Models.planning.plan import Plan
from ....Models.shapes.custom_field import CustomField
from ....Models.utils.utility import Utility
from .._traits.controller import Controller
from .._helpers.http import get_redirect_url
from django.core.exceptions import PermissionDenied
from ....configs.messages_templates import get_exception_class_message

class ClientController(Controller):

  def index(self, request: HttpRequest) -> HttpResponse:
    try:
      if request.user.has_perm('manage client'):
        clients = User.objects.filter(created_by=request.user.creator_id(), type='client')
        return render(request, 'clients/index.html', {'clients': clients})
      else:
        messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))
        return redirect(get_redirect_url(request))
    except Exception as e:
      print(f"[ClientController.index] Failed to load clients: {e.__class__.__name__}: {e}")
      messages.error(request, "ClientController.index: Error")
      return redirect(get_redirect_url(request))

  def create(self, request: HttpRequest) -> HttpResponse:
    try:
      if request.user.has_perm('create client'):
        return (render(request, 'clients/createAjax.html', {})
                if request.GET.get('ajax')
                else render(request, 'clients/create.html', {'customFields': CustomField.objects.filter(module='client')}))
      else:
        return JsonResponse({'error': "Permission Denied."}, status=401)
    except Exception as e:
      print(f"[ClientController.create] Failed to load create view: {e.__class__.__name__}: {e}")
      messages.error(request, "ClientController.create: Error")
      return redirect(get_redirect_url(request))

  def store(self, request: HttpRequest) -> HttpResponse:
    try:
      if request.user.has_perm('create client'):
        default_language_obj = Utility.get_setting_value('default_language', request.user.creator_id())
        name = request.POST.get('name')
        email = request.POST.get('email')
        password = request.POST.get('password')
        if not name or not email or not password:
          error_msg = "Name, Email and Password are required."
          return (JsonResponse({'error': error_msg}, status=401)
                  if request.GET.get('ajax')
                  else (messages.error(request, error_msg) or redirect(get_redirect_url(request))))
        if User.objects.filter(email=email).exists():
          error_msg = "Email already exists."
          return (JsonResponse({'error': error_msg}, status=401)
                  if request.GET.get('ajax')
                  else (messages.error(request, error_msg) or redirect(get_redirect_url(request))))
        objCustomer = request.user
        creator = get_object_or_404(User, pk=objCustomer.creator_id())
        total_client = User.objects.filter(created_by=request.user.creator_id(), type='client').count()
        plan = get_object_or_404(Plan, pk=creator.plan)
        if total_client < plan.max_clients or plan.max_clients == -1:
          role = Role.get_by_name('client')  # TODO: Implement actual role retrieval
          client = User.objects.create(
            name=name,
            email=email,
            job_title=request.POST.get('job_title'),
            password=make_password(password),
            type='client',
            lang=(default_language_obj if default_language_obj else 'en'),
            created_by=request.user.creator_id(),
            email_verified_at=timezone.now(),
          )
          settings_obj = Utility.settings()
          if settings_obj.get('new_client') == 1:
            role_r = Role.get_by_name('client')  # TODO: Replace with actual role assignment
            client.assign_role(role_r)  # TODO: Implement assign_role method on User model
            client.password_plain = password  # Store plain password temporarily for email
            clientArr = {
              'client_name': client.name,
              'client_email': client.email,
              'client_password': client.password_plain,
            }
            resp = Utility.sendEmailTemplate('new_client', [client.email], clientArr)
            success_msg = "Client successfully added." + (
              f"<br> <span class='text-danger'>{resp.get('error')}</span>"
              if resp and not resp.get('is_success') and resp.get('error') else ""
            )
            messages.success(request, success_msg)
            return redirect('clients_index')
          messages.success(request, "Client successfully created.")
          return redirect('clients_index')
        else:
          messages.error(request, "Your user limit is over, Please upgrade plan.")
          return redirect(get_redirect_url(request))
      else:
        return (JsonResponse({'error': "Permission Denied."}, status=401)
                if request.GET.get('ajax')
                else (messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__)) or redirect(get_redirect_url(request))))
    except Exception as e:
      print(f"[ClientController.store] Failed to store client: {e.__class__.__name__}: {e}")
      messages.error(request, "ClientController.store: Error")
      return redirect(get_redirect_url(request))

  def show(self, request: HttpRequest, client_id: int) -> HttpResponse:
    try:
      client = get_object_or_404(User, pk=client_id)
      usr = request.user
      if client and usr.id == client.creator_id() and client.id != usr.id and client.type == 'client':
        estimations = client.clientEstimations().order_by('-id')
        curr_month = client.clientEstimations().filter(issue_date__month=datetime.date.today().month)
        curr_week = client.clientEstimations().filter(
          issue_date__gte=timezone.now().date() - datetime.timedelta(days=timezone.now().date().weekday()),
          issue_date__lte=timezone.now().date() + datetime.timedelta(days=6 - timezone.now().date().weekday())
        )
        last_30days = client.clientEstimations().filter(issue_date__gt=timezone.now().date() - datetime.timedelta(days=30))
        cnt_estimation = {}
        cnt_estimation['total'] = Estimation.getEstimationSummary(estimations)
        cnt_estimation['this_month'] = Estimation.getEstimationSummary(curr_month)
        cnt_estimation['this_week'] = Estimation.getEstimationSummary(curr_week)
        cnt_estimation['last_30days'] = Estimation.getEstimationSummary(last_30days)
        cnt_estimation['cnt_total'] = estimations.count()
        cnt_estimation['cnt_this_month'] = curr_month.count()
        cnt_estimation['cnt_this_week'] = curr_week.count()
        cnt_estimation['cnt_last_30days'] = last_30days.count()
        contracts = client.clientContracts().order_by('-id')
        curr_month_contracts = client.clientContracts().filter(start_date__month=datetime.date.today().month)
        curr_week_contracts = client.clientContracts().filter(
          start_date__gte=timezone.now().date() - datetime.timedelta(days=timezone.now().date().weekday()),
          start_date__lte=timezone.now().date() + datetime.timedelta(days=6 - timezone.now().date().weekday())
        )
        last_30days_contracts = client.clientContracts().filter(start_date__gt=timezone.now().date() - datetime.timedelta(days=30))
        cnt_contract = {}
        cnt_contract['total'] = Contract.getContractSummary(contracts)
        cnt_contract['this_month'] = Contract.getContractSummary(curr_month_contracts)
        cnt_contract['this_week'] = Contract.getContractSummary(curr_week_contracts)
        cnt_contract['last_30days'] = Contract.getContractSummary(last_30days_contracts)
        cnt_contract['cnt_total'] = contracts.count()
        cnt_contract['cnt_this_month'] = curr_month_contracts.count()
        cnt_contract['cnt_this_week'] = curr_week_contracts.count()
        cnt_contract['cnt_last_30days'] = last_30days_contracts.count()
        return render(request, 'clients/show.html', {
          'client': client,
          'estimations': estimations,
          'cnt_estimation': cnt_estimation,
          'contracts': contracts,
          'cnt_contract': cnt_contract
        })
      else:
        messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))
        return redirect(get_redirect_url(request))
    except Exception as e:
      print(f"[ClientController.show] Failed to show client: {e.__class__.__name__}: {e}")
      messages.error(request, "ClientController.show: Error")
      return redirect(get_redirect_url(request))

  def edit(self, request: HttpRequest, client_id: int) -> HttpResponse:
    try:
      if request.user.has_perm('edit client'):
        client = get_object_or_404(User, pk=client_id)
        if client.created_by == request.user.creator_id():
          client.customField = CustomField.getData(client, 'client')  # TODO: Implement getData method
          customFields = CustomField.objects.filter(module='client')
          return render(request, 'clients/edit.html', {'client': client, 'customFields': customFields})
        else:
          return JsonResponse({'error': "Invalid Client."}, status=401)
      else:
        return JsonResponse({'error': "Permission Denied."}, status=401)
    except Exception as e:
      print(f"[ClientController.edit] Failed to load edit view: {e.__class__.__name__}: {e}")
      messages.error(request, "ClientController.edit: Error")
      return redirect(get_redirect_url(request))

  def update(self, request: HttpRequest, client_id: int) -> HttpResponse:
    try:
      if request.user.has_perm('edit client'):
        client = get_object_or_404(User, pk=client_id)
        if client.created_by == request.user.creator_id():
          name = request.POST.get('name')
          email = request.POST.get('email')
          if not name or not email:
            messages.error(request, "Name and Email are required.")
            return redirect(get_redirect_url(request))
          if User.objects.filter(email=email).exclude(pk=client.id).exists():
            messages.error(request, "Email already exists.")
            return redirect(get_redirect_url(request))
          client.name = name
          client.email = email
          if request.POST.get('password'):
            client.password = make_password(request.POST.get('password'))
          client.save()
          CustomField.saveData(client, request.POST.get('customField'))  # TODO: Implement saveData method
          messages.success(request, "Client Updated Successfully!")
          return redirect(get_redirect_url(request))
        else:
          messages.error(request, "Invalid Client.")
          return redirect(get_redirect_url(request))
      else:
        messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))
        return redirect(get_redirect_url(request))
    except Exception as e:
      print(f"[ClientController.update] Failed to update client: {e.__class__.__name__}: {e}")
      messages.error(request, "ClientController.update: Error")
      return redirect(get_redirect_url(request))

  def destroy(self, request: HttpRequest, client_id: int) -> HttpResponse:
    try:
      client = get_object_or_404(User, pk=client_id)
      if client.created_by == request.user.creator_id():
        estimation = Estimation.objects.filter(client_id=client.id).first()
        if not estimation:
          client.delete()
          messages.success(request, "Client Deleted Successfully!")
          return redirect(get_redirect_url(request))
        else:
          messages.error(request, "This client has assigned some estimation.")
          return redirect(get_redirect_url(request))
      else:
        messages.error(request, "Invalid Client.")
        return redirect(get_redirect_url(request))
    except Exception as e:
      print(f"[ClientController.destroy] Failed to delete client: {e.__class__.__name__}: {e}")
      messages.error(request, "ClientController.destroy: Error")
      return redirect(get_redirect_url(request))

  def client_password(self, request: HttpRequest, encrypted_id: str) -> HttpResponse:
    try:
      eId = int(encrypted_id)  # TODO: Replace with proper decryption
    except Exception as e:
      messages.error(request, "Invalid client id.")
      return redirect(get_redirect_url(request))
    user_obj = get_object_or_404(User, pk=eId)
    client = User.objects.filter(created_by=user_obj.creator_id(), type='client').first()
    return render(request, 'clients/reset.html', {'user': user_obj, 'client': client})

  def client_password_reset(self, request: HttpRequest, client_id: int) -> HttpResponse:
    try:
      password = request.POST.get('password')
      password_confirmation = request.POST.get('password_confirmation')
      if not password or password != password_confirmation:
        messages.error(request, "Password confirmation does not match.")
        return redirect(get_redirect_url(request))
      user_obj = get_object_or_404(User, pk=client_id)
      user_obj.password = make_password(password)
      user_obj.save()
      messages.success(request, "Client Password successfully updated.")
      return redirect('clients_index')
    except Exception as e:
      print(f"[ClientController.client_password_reset] Failed to reset password: {e.__class__.__name__}: {e}")
      messages.error(request, "ClientController.client_password_reset: Error")
      return redirect(get_redirect_url(request))
