import json
import os
import time
from django.contrib import messages
from django.http import HttpRequest, HttpResponse, JsonResponse
from django.shortcuts import get_object_or_404, redirect, render
from ....Models.companies.branch import Branch
from ....Models.companies.company_policy import CompanyPolicy
from ....Models.utils.utility import Utility
from .._traits.controller import Controller
from .._helpers.http import get_redirect_url
from django.core.exceptions import PermissionDenied
from ....configs.messages_templates import get_exception_class_message

class CompanyPolicyController(Controller):

  def index(self, request: HttpRequest) -> HttpResponse:
    try:
      return (render(request, 'companyPolicy/index.html', {'companyPolicy': CompanyPolicy.objects.filter(created_by=request.user.creator_id()).prefetch_related('branches')})
              if request.user.has_perm('manage company policy')
              else (messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__)) or redirect(get_redirect_url(request))))
    except Exception as e:
      print(f"[CompanyPolicyController.index] Failed: {e.__class__.__name__}: {e}")
      messages.error(request, "CompanyPolicyController.index: Error")
      return redirect(get_redirect_url(request))

  def create(self, request: HttpRequest) -> HttpResponse:
    try:
      return (render(request, 'companyPolicy/create.html', {'branch': {'': 'Select Branch', **{b.id: b.name for b in Branch.objects.filter(created_by=request.user.creator_id())}}})
              if request.user.has_perm('create company policy')
              else JsonResponse({'error': "Permission denied."}, status=401))
    except Exception as e:
      print(f"[CompanyPolicyController.create] Failed: {e.__class__.__name__}: {e}")
      messages.error(request, "CompanyPolicyController.create: Error")
      return redirect(get_redirect_url(request))

  def store(self, request: HttpRequest) -> HttpResponse:
    try:
      if not request.user.has_perm('create company policy'):
        messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))
        return redirect(get_redirect_url(request))
      branch_val = request.POST.get('branch')
      title = request.POST.get('title')
      if not branch_val or not title:
        messages.error(request, "Branch and Title are required.")
        return redirect(get_redirect_url(request))
      fileNameToStore = ''
      if request.FILES.get('attachment'):
        file_obj = request.FILES['attachment']
        originalName = file_obj.name
        filename = os.path.splitext(originalName)[0]
        extension = os.path.splitext(originalName)[1].lstrip('.')
        fileNameToStore = f"{filename}_{int(time.time())}.{extension}"
        dir_path = 'uploads/companyPolicy/'
        image_path = os.path.join(dir_path, fileNameToStore)
        if os.path.exists(image_path):
          os.remove(image_path)
        upload_result = Utility.upload_file(request, 'attachment', fileNameToStore, dir_path, [])
        if upload_result.get('flag') != 1:
          messages.error(request, upload_result.get('msg'))
          return redirect(get_redirect_url(request))
      policy = CompanyPolicy()
      policy.branch = branch_val
      policy.title = title
      policy.description = request.POST.get('description')
      policy.attachment = fileNameToStore if request.FILES.get('attachment') else ''
      policy.created_by = request.user.creator_id()
      policy.save()
      setting = Utility.settings(request.user.creator_id())
      branch_obj = get_object_or_404(Branch, pk=branch_val)
      policyNotificationArr = {'company_policy_name': title, 'branch_name': branch_obj.name}
      if setting.get('policy_notification') == 1:
        Utility.send_slack_msg('new_company_policy', policyNotificationArr)
      if setting.get('telegram_policy_notification') == 1:
        Utility.send_telegram_msg('new_company_policy', policyNotificationArr)
      webhook = Utility.webhookSetting('New Company Policy')
      if webhook:
        parameter = json.dumps(policy.__dict__)
        status = Utility.WebhookCall(webhook.get('url'), parameter, webhook.get('method'))
        if status:
          messages.success(request, "Company policy successfully created.")
          return redirect('company-policy_index')
        else:
          messages.error(request, "Webhook call failed.")
          return redirect(get_redirect_url(request))
      messages.success(request, "Company policy successfully created.")
      return redirect('company-policy_index')
    except Exception as e:
      print(f"[CompanyPolicyController.store] Failed: {e.__class__.__name__}: {e}")
      messages.error(request, "CompanyPolicyController.store: Error")
      return redirect(get_redirect_url(request))

  def show(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      return render(request, 'companyPolicy/show.html', {})
    except Exception as e:
      print(f"[CompanyPolicyController.show] Failed: {e.__class__.__name__}: {e}")
      messages.error(request, "CompanyPolicyController.show: Error")
      return redirect(get_redirect_url(request))

  def edit(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      return (render(request, 'companyPolicy/edit.html', {'branch': {'': 'Select Branch', **{b.id: b.name for b in Branch.objects.filter(created_by=request.user.creator_id())}}, 'companyPolicy': get_object_or_404(CompanyPolicy, pk=id)})
              if request.user.has_perm('edit company policy')
              else JsonResponse({'error': "Permission denied."}, status=401))
    except Exception as e:
      print(f"[CompanyPolicyController.edit] Failed: {e.__class__.__name__}: {e}")
      messages.error(request, "CompanyPolicyController.edit: Error")
      return redirect(get_redirect_url(request))

  def update(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      if not request.user.has_perm('create company policy'):
        messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))
        return redirect(get_redirect_url(request))
      branch_val = request.POST.get('branch')
      title = request.POST.get('title')
      if not branch_val or not title:
        messages.error(request, "Branch and Title are required.")
        return redirect(get_redirect_url(request))
      fileNameToStore = ''
      if request.FILES.get('attachment'):
        file_obj = request.FILES['attachment']
        originalName = file_obj.name
        filename = os.path.splitext(originalName)[0]
        extension = os.path.splitext(originalName)[1].lstrip('.')
        fileNameToStore = f"{filename}_{int(time.time())}.{extension}"
        dir_path = 'uploads/companyPolicy/'
        image_path = os.path.join(dir_path, fileNameToStore)
        if os.path.exists(image_path):
          os.remove(image_path)
        upload_result = Utility.upload_file(request, 'attachment', fileNameToStore, dir_path, [])
        if upload_result.get('flag') != 1:
          messages.error(request, upload_result.get('msg'))
          return redirect(get_redirect_url(request))
      companyPolicy = get_object_or_404(CompanyPolicy, pk=id)
      companyPolicy.branch = branch_val
      companyPolicy.title = title
      companyPolicy.description = request.POST.get('description')
      if request.FILES.get('attachment'):
        companyPolicy.attachment = fileNameToStore
      companyPolicy.created_by = request.user.creator_id()
      companyPolicy.save()
      messages.success(request, "Company policy successfully updated.")
      return redirect('company-policy_index')
    except Exception as e:
      print(f"[CompanyPolicyController.update] Failed: {e.__class__.__name__}: {e}")
      messages.error(request, "CompanyPolicyController.update: Error")
      return redirect(get_redirect_url(request))

  def destroy(self, request: HttpRequest, id: int) -> HttpResponse:
    try:
      if not request.user.has_perm('delete document'):
        messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))
        return redirect(get_redirect_url(request))
      companyPolicy = get_object_or_404(CompanyPolicy, pk=id)
      if companyPolicy.created_by != request.user.creator_id():
        messages.error(request,get_exception_class_message(PermissionDenied, __class__.__name__))
        return redirect(get_redirect_url(request))
      companyPolicy.delete()
      dir_path = os.path.join('uploads/companyPolicy/')
      if companyPolicy.attachment:
        file_path = os.path.join(dir_path, companyPolicy.attachment)
        if os.path.exists(file_path):
          os.unlink(file_path)
      messages.success(request, "Company policy successfully deleted.")
      return redirect('company-policy_index')
    except Exception as e:
      print(f"[CompanyPolicyController.destroy] Failed: {e.__class__.__name__}: {e}")
      messages.error(request, "CompanyPolicyController.destroy: Error")
      return redirect(get_redirect_url(request))
