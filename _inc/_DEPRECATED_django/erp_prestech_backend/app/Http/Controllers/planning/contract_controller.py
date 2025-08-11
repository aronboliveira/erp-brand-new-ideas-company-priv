import json
import logging
import os
from datetime import datetime, timedelta
from django.conf import settings
from django.contrib import messages
from django.http import FileResponse, JsonResponse, HttpRequest, HttpResponse
from django.shortcuts import get_object_or_404, redirect, render
from django.urls import reverse
from django.core import signing
from ....Models.individuals.user import User
from ....Models.planning.contract import Contract
from ....Models.planning.project import Project
from ....Models.utils.utility import Utility
from .._traits.controller import Controller
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
import inspect

logger = logging.getLogger(__name__)

class ContractController(Controller):

    def index(self, request: HttpRequest) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('manage contract'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            if request.user.type == 'company':
                contracts = Contract.objects.filter(created_by=request.user.creator_id()).select_related('clients','projects','types')
                curr_month = Contract.objects.filter(created_by=request.user.creator_id(), start_date__month=datetime.now().month)
                curr_week = Contract.objects.filter(
                    created_by=request.user.creator_id(),
                    start_date__gte=datetime.now().date() - datetime.now().date().weekday(),
                    start_date__lte=datetime.now().date() + (6 - datetime.now().date().weekday())
                )
                last_30days = Contract.objects.filter(
                    created_by=request.user.creator_id(),
                    start_date__gt=datetime.now().date() - timedelta(days=30)
                )
                cnt_contract = {
                    'total': Contract.getContractSummary(contracts),
                    'this_month': Contract.getContractSummary(curr_month),
                    'this_week': Contract.getContractSummary(curr_week),
                    'last_30days': Contract.getContractSummary(last_30days),
                }
                return render(request, 'contract/index.html', {'contracts': contracts, 'cnt_contract': cnt_contract})
            elif request.user.type == 'client':
                contracts = Contract.objects.filter(client_name=request.user.id).select_related('types')
                curr_month = Contract.objects.filter(client_name=request.user.id, start_date__month=datetime.now().month)
                curr_week = Contract.objects.filter(
                    client_name=request.user.id,
                    start_date__gte=datetime.now().date() - datetime.now().date().weekday(),
                    start_date__lte=datetime.now().date() + (6 - datetime.now().date().weekday())
                )
                last_30days = Contract.objects.filter(
                    client_name=request.user.creator_id(),
                    start_date__gt=datetime.now().date() - timedelta(days=30)
                )
                cnt_contract = {
                    'total': Contract.getContractSummary(contracts),
                    'this_month': Contract.getContractSummary(curr_month),
                    'this_week': Contract.getContractSummary(curr_week),
                    'last_30days': Contract.getContractSummary(last_30days),
                }
                return render(request, 'contract/index.html', {'contracts': contracts, 'cnt_contract': cnt_contract})
            contracts = Contract.objects.filter(created_by=request.user.creator_id()).select_related('clients','projects','types')
            return render(request, 'contract/index.html', {'contracts': contracts})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def create(self, request: HttpRequest) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            from ....Models.planning.contract_type import ContractType
            contract_types_qs = ContractType.objects.filter(created_by=request.user.creator_id()).values('id', 'name')
            contract_types = {t['id']: t['name'] for t in contract_types_qs}
            clients_qs = User.objects.filter(type='client', created_by=request.user.creator_id()).values('id', 'name')
            clients = {'0': "Select Client", **{str(c['id']): c['name'] for c in clients_qs}}
            project_qs = Project.objects.filter(created_by=request.user.creator_id()).values('id', 'project_name')
            project = {p['id']: p['project_name'] for p in project_qs}
            return render(request, 'contract/create.html', {
                'contractTypes': contract_types,
                'clients': clients,
                'project': project
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def store(self, request: HttpRequest) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('create contract'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            for field in ['client_name', 'subject', 'type', 'value', 'start_date', 'end_date']:
                if not request.POST.get(field):
                    messages.error(request, f"{field} is required.")
                    return redirect(get_redirect_url(request))
            contract = Contract(
                client_name=request.POST.get('client_name'),
                subject=request.POST.get('subject'),
                project_id=request.POST.get('project_id'),
                type=request.POST.get('type'),
                value=request.POST.get('value'),
                start_date=request.POST.get('start_date'),
                end_date=request.POST.get('end_date'),
                description=request.POST.get('description'),
                created_by=request.user.creator_id()
            )
            contract.save()
            settings_obj = Utility.settings()
            resp = {}
            if settings_obj.get('new_contract') == 1:
                client = get_object_or_404(User, pk=request.POST.get('client_name'))
                contract_arr = {
                    'contract_subject': request.POST.get('subject'),
                    'contract_client': client.name,
                    'contract_value': request.user.priceFormat(request.POST.get('value')),
                    'contract_start_date': request.user.dateFormat(request.POST.get('start_date')),
                    'contract_end_date': request.user.dateFormat(request.POST.get('end_date')),
                    'contract_description': request.POST.get('description'),
                }
                resp = Utility.sendEmailTemplate('new_contract', {client.id: client.email}, contract_arr)
            setting = Utility.settings(request.user.creator_id())
            client = get_object_or_404(User, pk=request.POST.get('client_name'))
            notif_arr = {
                'contract_subject': request.POST.get('subject'),
                'contract_client': client.name,
                'contract_value': request.user.priceFormat(request.POST.get('value')),
                'contract_start_date': request.user.dateFormat(request.POST.get('start_date')),
                'contract_end_date': request.user.dateFormat(request.POST.get('end_date')),
                'user_name': request.user.name,
            }
            if setting.get('contract_notification') == 1:
                Utility.send_slack_msg('new_contract', notif_arr)
            if setting.get('telegram_contract_notification') == 1:
                Utility.send_telegram_msg('new_contract', notif_arr)
            webhook = Utility.webhookSetting('New Contract')
            if webhook:
                parameter = json.dumps(contract.__dict__)
                if Utility.WebhookCall(webhook.get('url'), parameter, webhook.get('method')):
                    messages.success(request, "Contract successfully created!" + (
                        f"<br> <span class='text-danger'>{resp.get('error')}</span>" if resp and not resp.get('is_success') and resp.get('error') else ""
                    ))
                    return redirect('contract_index')
                messages.error(request, "Webhook call failed.")
                return redirect(get_redirect_url(request))
            messages.success(request, "Contract successfully created!" + (
                f"<br> <span class='text-danger'>{resp.get('error')}</span>" if resp and not resp.get('is_success') and resp.get('error') else ""
            ))
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def show(self, request: HttpRequest, id: int) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('show contract'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            contract = get_object_or_404(Contract, pk=id)
            if contract.created_by != request.user.creator_id():
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            return render(request, 'contract/show.html', {'contract': contract, 'client': contract.client})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def edit(self, request: HttpRequest, id: int) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            from ....Models.planning.contract_type import ContractType
            contract = get_object_or_404(Contract, pk=id)
            types_qs = ContractType.objects.filter(created_by=request.user.creator_id()).values('id','name')
            types = {t['id']: t['name'] for t in types_qs}
            clients_qs = User.objects.filter(type='client', created_by=request.user.creator_id()).values('id','name')
            clients = {c['id']: c['name'] for c in clients_qs}
            project_qs = Project.objects.filter(created_by=request.user.creator_id()).values('id','project_name')
            project = {p['id']: p['project_name'] for p in project_qs}
            return render(request, 'contract/edit.html', {
                'contractTypes': types,
                'clients': clients,
                'contract': contract,
                'project': project
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def update(self, request: HttpRequest, id: int) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit contract'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            for field in ['client_name', 'subject', 'type', 'value', 'start_date', 'end_date']:
                if not request.POST.get(field):
                    messages.error(request, f"{field} is required.")
                    return redirect(get_redirect_url(request))
            contract = get_object_or_404(Contract, pk=id)
            contract.client_name = request.POST.get('client_name')
            contract.subject = request.POST.get('subject')
            contract.project_id = request.POST.get('project_id')
            contract.type = request.POST.get('type')
            contract.value = request.POST.get('value')
            contract.start_date = request.POST.get('start_date')
            contract.end_date = request.POST.get('end_date')
            contract.description = request.POST.get('description')
            contract.save()
            messages.success(request, "Contract successfully updated.")
            return redirect('contract_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def destroy(self, request: HttpRequest, id: int) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('delete contract'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            contract = get_object_or_404(Contract, pk=id)
            contract.delete()
            messages.success(request, "Contract successfully deleted.")
            return redirect('contract_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def description(self, request: HttpRequest, id: int) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            contract = get_object_or_404(Contract, pk=id)
            return render(request, 'contract/description.html', {'contract': contract})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def grid(self, request: HttpRequest) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if request.user.type not in ['company', 'client']:
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            if request.user.type == 'company':
                contracts = Contract.objects.filter(created_by=request.user.creator_id())
            else:
                contracts = Contract.objects.filter(client_name=request.user.id)
            return render(request, 'contract/grid.html', {'contracts': contracts})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def file_upload(self, request: HttpRequest, id: int) -> JsonResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if request.user.type not in ['company', 'client']:
                return JsonResponse({'is_success': False, 'error': "Permission Denied."}, status=401)
            contract = get_object_or_404(Contract, pk=id)
            if 'file' not in request.FILES:
                messages.error(request, "File is required.")
                return redirect(get_redirect_url(request))
            size = request.FILES['file'].size
            if Utility.updateStorageLimit(request.user.creator_id(), size) != 1:
                messages.error(request, "Storage limit exceeded.")
                return redirect(get_redirect_url(request))
            from ....Models.planning.contract_attachment import ContractAttachment
            fname = f"{id}{request.FILES['file'].name}"
            dir_path = 'contract_attachment/'
            result = Utility.upload_file(request, 'file', fname, dir_path, [])
            if result.get('flag') != 1:
                messages.error(request, result.get('msg'))
                return redirect(get_redirect_url(request))
            fa = ContractAttachment.objects.create(
                contract_id=id,
                user_id=request.user.id,
                files=fname
            )
            return JsonResponse({
                'is_success': True,
                'download': reverse('contracts_file_download', args=[contract.id, fa.id]),
                'delete': reverse('contracts_file_delete', args=[contract.id, fa.id])
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def file_download(self, request: HttpRequest, id: int, file_id: int) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if request.user.type != 'company':
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            from ....Models.planning.contract_attachment import ContractAttachment
            fa = get_object_or_404(ContractAttachment, pk=file_id)
            path = os.path.join(settings.BASE_DIR, 'storage', 'contract_attachment', fa.files)
            if not os.path.exists(path):
                messages.error(request, "File does not exist.")
                return redirect(get_redirect_url(request))
            resp = FileResponse(open(path, 'rb'))
            resp['Content-Length'] = os.path.getsize(path)
            return resp
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def file_delete(self, request: HttpRequest, id: int, file_id: int) -> JsonResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            from ....Models.planning.contract_attachment import ContractAttachment
            fa = ContractAttachment.objects.filter(pk=file_id).first()
            if not fa:
                return JsonResponse({'is_success': False, 'error': "File does not exist."}, status=200)
            path = os.path.join(settings.BASE_DIR, 'storage', 'contract_attachment', fa.files)
            if os.path.exists(path):
                os.remove(path)
            fa.delete()
            messages.success(request, "Contract file successfully deleted.")
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def contract_status_edit(self, request: HttpRequest, id: int) -> None:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            contract = get_object_or_404(Contract, pk=id)
            contract.status = request.POST.get('status')
            contract.save()
        except Exception as e:
            logger.exception(f"{CN}::{MN} failed: {e}")

    def comment_store(self, request: HttpRequest, id: int) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            from ....Models.planning.contract_comment import ContractComment
            comment = ContractComment(
                comment=request.POST.get('comment'),
                contract_id=id,
                user_id=request.user.id
            )
            comment.save()
            messages.success(request, "Comments successfully created!")
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def contract_description_store(self, request: HttpRequest, id: int) -> JsonResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if request.user.type != 'company':
                return JsonResponse({'is_success': False, 'error': "Permission Denied."}, status=401)
            contract = get_object_or_404(Contract, pk=id)
            if contract.created_by != request.user.creator_id():
                return JsonResponse({'is_success': False, 'error': "Permission Denied."}, status=401)
            contract.contract_description = request.POST.get('contract_description')
            contract.save()
            return JsonResponse({'is_success': True, 'success': "Contract description successfully saved!"}, status=200)
        except Exception as e:
            logger.error(f'{CN}::{MN} raised an error: ${e}')
            return JsonResponse({'is_success': False, 'error': "An error occurred."}, status=500)

    def comment_destroy(self, request: HttpRequest, id: int) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            from ....Models.planning.contract_comment import ContractComment
            comment = get_object_or_404(ContractComment, pk=id)
            comment.delete()
            messages.success(request, "Comment successfully deleted!")
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def note_store(self, request: HttpRequest, id: int) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            from ....Models.planning.contract_notes import ContractNotes
            note = ContractNotes(
                contract_id=id,
                notes=request.POST.get('notes'),
                user_id=request.user.id
            )
            note.save()
            messages.success(request, "Note successfully saved.")
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def note_destroy(self, request: HttpRequest, id: int) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            from ....Models.planning.contract_notes import ContractNotes
            note = get_object_or_404(ContractNotes, pk=id)
            note.delete()
            messages.success(request, "Note successfully deleted!")
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def clientwise_project(self, request: HttpRequest, client_id: int) -> JsonResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            from ....Models.planning.project import Project
            projects = Project.objects.filter(client_id=client_id)
            return JsonResponse([{'id': p.id, 'name': p.project_name} for p in projects], safe=False)
        except Exception as e:
            logger.error(f'{CN}::{MN} raised an error: ${e}')
            return JsonResponse({'error': "Something went wrong."}, status=500)

    def print_contract(self, request: HttpRequest, id: int) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            contract = get_object_or_404(Contract, pk=id)
            settings_obj = Utility.settings()
            logo = settings_obj.get('logo_path', 'uploads/logo/')
            company_logo = Utility.getValByName('company_logo')
            img = f"{logo}/{company_logo or 'logo-dark.png'}"
            color = f"#{settings_obj.get('invoice_color', '000000')}"
            font_color = Utility.getFontColor(color)
            return render(request, 'contract/preview.html', {
                'contract': contract,
                'color': color,
                'img': img,
                'settings': settings_obj,
                'font_color': font_color
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def copy_contract(self, request: HttpRequest, id: int) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            contract = get_object_or_404(Contract, pk=id)
            from ....Models.planning.contract_type import ContractType
            clients_qs = User.objects.filter(type='Client').values('id','name')
            clients = {c['id']: c['name'] for c in clients_qs}
            types_qs = ContractType.objects.filter(created_by=request.user.creator_id()).values('id','name')
            types = {t['id']: t['name'] for t in types_qs}
            project_qs = Project.objects.filter(created_by=request.user.creator_id()).values('id','title')
            project = {p['id']: p['title'] for p in project_qs}
            contract.date = f"{contract.start_date} to {contract.end_date}"
            return render(request, 'contract/copy.html', {
                'contract': contract,
                'contractTypes': types,
                'clients': clients,
                'project': project
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def copy_contract_store(self, request: HttpRequest) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if request.user.type != 'company':
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            for field in ['client','subject','project_id','type','value','start_date','end_date']:
                if not request.POST.get(field):
                    messages.error(request, f"{field} is required.")
                    return redirect(reverse('contract_index'))
            contract = Contract(
                client_name=request.POST.get('client'),
                subject=request.POST.get('subject'),
                project_id=','.join(request.POST.getlist('project_id')),
                type=request.POST.get('type'),
                value=request.POST.get('value'),
                start_date=request.POST.get('start_date'),
                end_date=request.POST.get('end_date'),
                description=request.POST.get('description'),
                created_by=request.user.creator_id()
            )
            contract.save()
            settings_obj = Utility.settings()
            if settings_obj.get('new_contract') == 1:
                client = get_object_or_404(User, pk=request.POST.get('client'))
                arr = {
                    'contract_subject': request.POST.get('subject'),
                    'contract_client': client.name,
                    'contract_value': request.user.priceFormat(request.POST.get('value')),
                    'contract_start_date': request.user.dateFormat(request.POST.get('start_date')),
                    'contract_end_date': request.user.dateFormat(request.POST.get('end_date')),
                    'contract_description': request.POST.get('description'),
                }
                resp = Utility.sendEmailTemplate('new_contract', {client.id: client.email}, arr)
                messages.success(request, "Contract successfully created!" + (
                    f"<br> <span class='text-danger'>{resp.get('error')}</span>" if resp and not resp.get('is_success') and resp.get('error') else ""
                ))
                return redirect('contract_index')
            setting = Utility.settings(request.user.creator_id())
            if setting.get('telegram_contract_notification') == 1:
                msg = f"{request.POST.get('subject')} created by {request.user.name}."
                Utility.send_telegram_msg(msg)
            messages.success(request, "Contract successfully created.")
            return redirect('contract_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def send_mail_contract(self, request: HttpRequest, id: int) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            contract = get_object_or_404(Contract, pk=id)
            setting = Utility.settings()
            if setting.get('new_contract') == 1:
                client = get_object_or_404(User, pk=contract.client_name)
                arr = {
                    'email': client.email,
                    'contract_subject': contract.subject,
                    'contract_client': client.name,
                    'contract_start_date': contract.start_date,
                    'contract_end_date': contract.end_date,
                }
                resp = Utility.sendEmailTemplate('new_contract', {client.id: client.email}, arr)
                messages.success(request, "Email sent successfully!" + (
                    f"<br> <span class='text-danger'>{resp.get('error')}</span>" if resp and not resp.get('is_success') and resp.get('error') else ""
                ))
                return redirect('contract_show', contract.id)
            return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def signature(self, request: HttpRequest, id: int) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            contract = get_object_or_404(Contract, pk=id)
            return render(request, 'contract/signature.html', {'contract': contract})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    def signature_store(self, request: HttpRequest) -> JsonResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            contract = get_object_or_404(Contract, pk=request.POST.get('contract_id'))
            if request.user.type == 'company':
                contract.company_signature = request.POST.get('company_signature')
            elif request.user.type == 'client':
                contract.client_signature = request.POST.get('client_signature')
            else:
                return JsonResponse({'Success': False, 'error': "Permission Denied."}, status=401)
            contract.save()
            return JsonResponse({'Success': True, 'message': "Contract signed successfully."}, status=200)
        except Exception as e:
            logger.error(f'{CN}::{MN} raised an error: ${e}')
            return JsonResponse({'Success': False, 'error': "An error occurred."}, status=500)

    def pdf_fromc_ontract(self, request: HttpRequest, contract_id: str) -> HttpResponse:
        CN = self.__class__.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            try:
                decrypted_id = signing.loads(contract_id)
            except Exception as de:
                return default_undefined_exception(request, err=de, ref=f'{CN}::{MN}', logger=logger)
            contract = get_object_or_404(Contract, pk=decrypted_id)
            return render(request, 'contract/template.html', {'contract': contract})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
