import logging, inspect
from typing import Union
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse, JsonResponse, FileResponse
from django.shortcuts import render, redirect, get_object_or_404
from django.urls import reverse
from django.utils import timezone
from django.db import transaction
from django.core.signing import Signer, BadSignature
from .._traits.controller import Controller
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from ....Models.bills.payslip import Payslip
from ....Models.individuals.employee import Employee
from ....Models.bills.allowance import Allowance
from ....Models.utils.utility import Utility
from ....Exports.payslip_export import PayslipExport

logger = logging.getLogger(__name__)

class PayslipController(Controller):
  
  @classmethod
  def _set_auth(cls, request: HttpRequest, perm=str) -> Union[bool, Exception]:
    ctrl = cls()
    ctrl.request = request
    ctrl.authorize(f'{perm} pay slip')
    return True

  @classmethod
  def index(cls, request: HttpRequest) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PayslipController._set_auth(request, 'manage')
      employees = Employee.objects.filter(created_by=request.user.creator_id()).all()
      month_map = {f'{m:02}':name for m,name in enumerate(
        ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'],1
      )}
      year_map = {str(y):str(y) for y in range(2023,2031)}
      return render(request,'payslip/index.html',{'employees':employees,'month':month_map,'year':year_map})
    except PermissionDenied as e:
      return default_permission_denial(request,err=e,ref=REF,logger=logger)
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  @classmethod
  def create(cls, request: HttpRequest) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PayslipController._set_auth(request, 'create')
      month_map = {f'{m:02}':name for m,name in enumerate(
        ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'],1
      )}
      year_map = {str(y):str(y) for y in range(2023,2031)}
      return render(request,'payslip/create.html',{'month':month_map,'year':year_map})
    except PermissionDenied as e:
      return default_permission_denial(request,err=e,ref=REF,logger=logger)
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  @classmethod
  def store(cls, request: HttpRequest) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PayslipController._set_auth(request, 'create')
      month = request.POST.get('month') 
      year = request.POST.get('year')
      if not month or not year:
        return redirect(reverse('payslip.index'))
      salary_month = f'{year}-{month}'
      existing = Payslip.objects.filter(
        salary_month=salary_month, created_by=request.user.creator_id()
      ).values_list('employee_id',flat=True)
      total_employees = Employee.objects.filter(
        created_by=request.user.creator_id(),
        company_doj__lte=timezone.datetime.strptime(f'{salary_month}-01','%Y-%m-%d')
      ).count()
      if total_employees > len(existing):
        with transaction.atomic():
          to_process = Employee.objects.filter(
            created_by=request.user.creator_id(),
            company_doj__lte=timezone.datetime.strptime(f'{salary_month}-01','%Y-%m-%d')
          ).exclude(id__in=existing)
          if to_process.filter(salary__lte=0).exists():
            return redirect(reverse('payslip.index'))
          for emp in to_process:
            ps = Payslip(
              employee_id=emp.id,
              net_payble=emp.get_net_salary(),
              salary_month=salary_month,
              status=0,
              basic_salary=emp.salary or 0,
              allowance=Employee.allowance(emp.id),
              commission=Employee.commission(emp.id),
              loan=Employee.loan(emp.id),
              saturation_deduction=Employee.saturation_deduction(emp.id),
              other_payment=Employee.other_payment(emp.id),
              overtime=Employee.overtime(emp.id),
              created_by=request.user.creator_id()
            )
            ps.save()
            setting = Utility.settings(request.user.creator_id())
            notif = {'year':salary_month}
            if setting.get('payslip_notification')==1:
              Utility.send_slack_msg('new_monthly_payslip',notif)
            if setting.get('telegram_payslip_notification')==1:
              Utility.send_telegram_msg('new_monthly_payslip',notif)
            webhook = Utility.webhookSetting('New Monthly Payslip')
            if webhook:
              if not Utility.WebhookCall(webhook['url'],ps.to_json(),webhook['method']):
                return redirect(reverse('payslip.index'))
        return redirect(reverse('payslip.index'))
      return redirect(reverse('payslip.index'))
    except PermissionDenied as e:
      return default_permission_denial(request,err=e,ref=REF,logger=logger)
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  @classmethod
  def destroy(cls, request: HttpRequest, id: int) -> JsonResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PayslipController._set_auth(request, 'delete')
      ps = Payslip.objects.filter(id=id,created_by=request.user.creator_id()).first()
      if ps:
        ps.delete() 
        return JsonResponse({'success':True})
      return JsonResponse({'error':'Not found'},status=404)
    except PermissionDenied as e:
      return default_permission_denial(request,err=e,ref=REF,logger=logger,json={})
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger,json={})

  @classmethod
  def show_employee(cls, request: HttpRequest, id: int) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PayslipController._set_auth(request, 'view')
      ps = get_object_or_404(Payslip,id=id,created_by=request.user.creator_id())
      return render(request,'payslip/show.html',{'payslip':ps})
    except PermissionDenied as e:
      return default_permission_denial(request,err=e,ref=REF,logger=logger)
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  @classmethod
  def search_json(cls, request: HttpRequest) -> JsonResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PayslipController._set_auth(request, 'view')
      date_str = request.POST.get('date_picker') or ''
      qs = Payslip.objects.select_related('employee').filter(
        salary_month=date_str, created_by=request.user.creator_id()
      )
      data = []
      for r in qs:
        emp = r.employee
        status = 'Paid' if r.status==1 else 'UnPaid'
        data.append([
          emp.id, emp.employee_id, emp.name,
          r.id, emp.salary_format(r.basic_salary),
          emp.salary_format(r.net_payble),
          status, reverse('employee.show',args=[emp.id])
        ])
      return JsonResponse({'data':data})
    except PermissionDenied as e:
      return default_permission_denial(request,err=e,ref=REF,logger=logger,json={})
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger,json={})

  @classmethod
  def pay_salary(cls, request: HttpRequest, id: int, date: str) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PayslipController._set_auth(request, 'update')
      ps = Payslip.objects.get(
        employee_id=id, salary_month=date, created_by=request.user.creator_id()
      )
      ps.status = 1 
      ps.save()
      return redirect(reverse('payslip.index'))
    except Payslip.DoesNotExist:
      return redirect(reverse('payslip.index'))
    except PermissionDenied as e:
      return default_permission_denial(request,err=e,ref=REF,logger=logger)
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  @classmethod
  def bulk_pay_create(cls, request: HttpRequest, date: str) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PayslipController._set_auth(request, 'view')
      all_ps = Payslip.objects.filter(salary_month=date,created_by=request.user.creator_id())
      unpaid = all_ps.filter(status=0)
      return render(request,'payslip/bulkcreate.html',{'employees':all_ps,'unpaid_employees':unpaid,'date':date})
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  @classmethod
  def bulk_payment(cls, request: HttpRequest, date: str) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PayslipController._set_auth(request, 'update')
      with transaction.atomic():
        Payslip.objects.filter(
          salary_month=date, created_by=request.user.creator_id(), status=0
        ).update(status=1)
      return redirect(reverse('payslip.index'))
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  @classmethod
  def employee_payslip(cls, request: HttpRequest) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PayslipController._set_auth(request, 'view')
      emp = get_object_or_404(Employee,user_id=request.user.id)
      slips = Payslip.objects.filter(employee_id=emp.id)
      return render(request,'payslip/employeepayslip.html',{'payslip':slips})
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  @classmethod
  def pdf(cls, request: HttpRequest, id: int, month: str) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PayslipController._set_auth(request, 'view')
      ps = get_object_or_404(Payslip,
        employee_id=id, salary_month=month, created_by=request.user.creator_id()
      )
      emp = get_object_or_404(Employee,id=ps.employee_id)
      detail = Utility.employee_payslip_detail(id,month)
      return render(request,'payslip/pdf.html',
                    {'payslip':ps,'employee':emp,'payslipDetail':detail})
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  @classmethod
  def send(cls, request: HttpRequest, id: int, month: str) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    signer = Signer()
    try:
      PayslipController._set_auth(request, 'send')
      settings = Utility.settings(request.user.creator_id())
      if settings.get('payslip_sent')==1:
        ps = get_object_or_404(Payslip,
          employee_id=id, salary_month=month, created_by=request.user.creator_id()
        )
        emp = get_object_or_404(Employee,id=ps.employee_id)
        encrypted_id = signer.sign(str(ps.id))
        url = request.build_absolute_uri(
          reverse('payslip.payslipPdf',args=[encrypted_id])
        )
        payload = {
          'employee_name':emp.name,'employee_email':emp.email,
          'payslip_name':ps.name,'payslip_salary_month':ps.salary_month,
          'payslip_url':url
        }
        Utility.sendEmailTemplate('payslip_sent',{emp.id:emp.email},payload)
      return redirect(reverse('payslip.index'))
    except BadSignature:
      messages.error(request,'Invalid token.')
      return redirect(reverse('payslip.index'))
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  @classmethod
  def payslip_pdf(cls, request: HttpRequest, token: str) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    signer = Signer()
    try:
      PayslipController._set_auth(request, 'view')
      ps_id = int(signer.unsign(token))
      ps = get_object_or_404(Payslip,id=ps_id,created_by=request.user.creator_id())
      emp = get_object_or_404(Employee,id=ps.employee_id)
      detail = Utility.employee_payslip_detail(ps.employee_id,ps.salary_month)
      return render(request,'payslip/payslipPdf.html',
                    {'payslip':ps,'employee':emp,'payslipDetail':detail})
    except BadSignature:
      messages.error(request,'Invalid link.')
      return redirect(reverse('payslip.index'))
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  @classmethod
  def edit_employee(cls, request: HttpRequest, id: int) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      ctrl = cls()
      ctrl.request = request 
      ctrl.authorize('update pay slip')
      ps = get_object_or_404(Payslip,id=id,created_by=request.user.creator_id())
      return render(request,'payslip/salaryEdit.html',{'payslip':ps})
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  @classmethod
  def update_employee(cls, request: HttpRequest, id: int) -> HttpResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PayslipController._set_auth(request, 'update')
      for amount, pk in zip(request.POST.getlist('allowance'), request.POST.getlist('allowance_id')):
        a = get_object_or_404(Allowance, id=pk)
        a.amount = amount
        a.save()
      ps = get_object_or_404(Payslip, id=request.POST.get('payslip_id'))
      emp = get_object_or_404(Employee, id=ps.employee_id)
      for field in ['allowance','commission','loan','saturation_deduction','other_payment','overtime']:
        setattr(ps, field, getattr(emp, field)())
      ps.net_payble = emp.get_net_salary()
      ps.save()
      return redirect(reverse('payslip.index'))
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)

  @classmethod
  def export(cls, request: HttpRequest) -> FileResponse:
    REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
    try:
      PayslipController._set_auth(request, 'export')
      name = 'payslip_' + timezone.now().strftime('%Y-%m-%d_%H-%M-%S')
      response = PayslipExport(request).download(f'{name}.xlsx')
      return response
    except Exception as e:
      return default_undefined_exception(request,err=e,ref=REF,logger=logger)
