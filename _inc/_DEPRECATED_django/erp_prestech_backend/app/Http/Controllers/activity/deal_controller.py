import datetime
import json
import os
import inspect
from django.db import transaction
from django.contrib import messages
from django.http import FileResponse, HttpResponse, HttpRequest, JsonResponse
from django.shortcuts import redirect, render
from django.urls import reverse
from django.core.files.storage import default_storage
from django.conf import settings
from django.core.exceptions import PermissionDenied
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller
from ....Models.activity.activity_log import ActivityLog
from ....Models.activity.deal import Deal
from ....Models.activity.source import Source
from ....Models.activity.stage import Stage
from ....Models.activity.client_deal import ClientDeal
from ....Models.activity.deal_call import DealCall
from ....Models.activity.deal_discussion import DealDiscussion
from ....Models.activity.deal_file import DealFile
from ....Models.activity.user_deal import UserDeal
from ....Models.configs.pipeline import Pipeline
from ....Models.configs.client_permission import ClientPermission
from ....Models.contact.deal_email import DealEmail
from ....Models.individuals.user import User
from ....Models.planning.deal_task import DealTask
from ....Models.products.product_service import ProductService
from ....Models.shapes.custom_field import CustomField
from ....Models.shapes.label import Label
from ....Models.utils.utility import Utility
import logging
logger = logging.getLogger(__name__)

class DealController(Controller):
    _deal_data = None

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            usr = request.user
            if usr.has_perm('manage deal'):
                if usr.default_pipeline:
                    pipeline = Pipeline.objects.filter(created_by=usr.owner_id(), id=usr.default_pipeline).first()
                    if not pipeline:
                        pipeline = Pipeline.objects.filter(created_by=usr.owner_id()).first()
                else:
                    pipeline = Pipeline.objects.filter(created_by=usr.owner_id()).first()
                pipelines = {p.id: p.name for p in Pipeline.objects.filter(created_by=usr.owner_id())}
                if usr.type == 'client':
                    id_deals = list(usr.client_deals.values_list('id', flat=True))
                else:
                    id_deals = list(usr.deals.values_list('id', flat=True))
                deals = Deal.objects.filter(id__in=id_deals, pipeline_id=pipeline.id)
                today = datetime.date.today()
                curr_month = Deal.objects.filter(
                    id__in=id_deals,
                    pipeline_id=pipeline.id,
                    created_at__month=today.month
                )
                start_week = today - datetime.timedelta(days=today.weekday())
                end_week = start_week + datetime.timedelta(days=6)
                curr_week = Deal.objects.filter(
                    id__in=id_deals,
                    pipeline_id=pipeline.id,
                    created_at__range=[start_week, end_week]
                )
                last_30days = Deal.objects.filter(
                    id__in=id_deals,
                    pipeline_id=pipeline.id,
                    created_at__gt=today - datetime.timedelta(days=30)
                )
                cnt_deal = {
                    'total': Deal.get_deal_summary(deals),
                    'this_month': Deal.get_deal_summary(curr_month),
                    'this_week': Deal.get_deal_summary(curr_week),
                    'last_30days': Deal.get_deal_summary(last_30days)
                }
                return render(
                    request,
                    'deals/index.html',
                    {'pipelines': pipelines, 'pipeline': pipeline, 'cnt_deal': cnt_deal}
                )
            else:
                return default_permission_denial(
                    request,
                    err=PermissionDenied('manage deal'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )

    @classmethod
    def deal_list(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            usr = request.user
            if usr.has_perm('manage deal'):
                if usr.default_pipeline:
                    pipeline = Pipeline.objects.filter(created_by=usr.owner_id(), id=usr.default_pipeline).first()
                    if not pipeline:
                        pipeline = Pipeline.objects.filter(created_by=usr.owner_id()).first()
                else:
                    pipeline = Pipeline.objects.filter(created_by=usr.owner_id()).first()
                pipelines = {p.id: p.name for p in Pipeline.objects.filter(created_by=usr.owner_id())}
                if usr.type == 'client':
                    id_deals = list(usr.client_deals.values_list('id', flat=True))
                else:
                    id_deals = list(usr.deals.values_list('id', flat=True))
                deals_qs = Deal.objects.filter(id__in=id_deals, pipeline_id=pipeline.id)
                today = datetime.date.today()
                curr_month = Deal.objects.filter(
                    id__in=id_deals,
                    pipeline_id=pipeline.id,
                    created_at__month=today.month
                )
                start_week = today - datetime.timedelta(days=today.weekday())
                end_week = start_week + datetime.timedelta(days=6)
                curr_week = Deal.objects.filter(
                    id__in=id_deals,
                    pipeline_id=pipeline.id,
                    created_at__range=[start_week, end_week]
                )
                last_30days = Deal.objects.filter(
                    id__in=id_deals,
                    pipeline_id=pipeline.id,
                    created_at__gt=today - datetime.timedelta(days=30)
                )
                cnt_deal = {
                    'total': Deal.get_deal_summary(deals_qs),
                    'this_month': Deal.get_deal_summary(curr_month),
                    'this_week': Deal.get_deal_summary(curr_week),
                    'last_30days': Deal.get_deal_summary(last_30days)
                }
                if usr.type == 'client':
                    deals = Deal.objects.filter(id__in=id_deals, pipeline_id=pipeline.id).extra(
                        tables=['client_deal'],
                        where=["client_deal.client_id = %s" % usr.id]
                    ).order_by('order')
                else:
                    deals = Deal.objects.filter(id__in=id_deals, pipeline_id=pipeline.id).extra(
                        tables=['user_deal'],
                        where=["user_deal.user_id = %s" % usr.id]
                    ).order_by('order')
                return render(
                    request,
                    'deals/list.html',
                    {'pipelines': pipelines, 'pipeline': pipeline, 'deals': deals, 'cnt_deal': cnt_deal}
                )
            else:
                return default_permission_denial(
                    request,
                    err=PermissionDenied('manage deal'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
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
            if request.user.has_perm('create deal'):
                clients = {
                    u.id: u.name
                    for u in User.objects.filter(
                        created_by=request.user.owner_id(),
                        type='client'
                    )
                }
                custom_fields = list(CustomField.objects.filter(module='deal'))
                return render(
                    request,
                    'deals/create.html',
                    {'clients': clients, 'custom_fields': custom_fields}
                )
            else:
                return JsonResponse({'error': 'Permission Denied.'}, status=401)
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
        try:
            usr = request.user
            if not usr.has_perm('create deal'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('create deal'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            Deal.objects.filter(created_by=usr.owner_id()).count()
            validator_errors = []
            if not request.POST.get('name'):
                validator_errors.append('Name is required.')
            if validator_errors:
                messages.error(request, validator_errors[0])
                return redirect(get_redirect_url(request))
            if usr.default_pipeline:
                pipeline = Pipeline.objects.filter(
                    created_by=usr.owner_id(),
                    id=usr.default_pipeline
                ).first()
                if not pipeline:
                    pipeline = Pipeline.objects.filter(created_by=usr.owner_id()).first()
            else:
                pipeline = Pipeline.objects.filter(created_by=usr.owner_id()).first()
            stage = Stage.objects.filter(pipeline_id=pipeline.id).first()
            if not stage:
                messages.error(request, 'Please Create Stage for This Pipeline.')
                return redirect(get_redirect_url(request))
            with transaction.atomic():
                deal = Deal()
                deal.name = request.POST.get('name')
                deal.phone = request.POST.get('phone', '')
                deal.price = float(request.POST.get('price', 0))
                deal.pipeline_id = pipeline.id
                deal.stage_id = stage.id
                deal.status = 'Active'
                deal.created_by = usr.owner_id()
                deal.save()
                client_ids = [int(x) for x in request.POST.getlist('clients')]
                for client in client_ids:
                    ClientDeal.objects.create(deal_id=deal.id, client_id=client)
                if usr.type == 'company':
                    usr_deals = [usr.id]
                else:
                    usr_deals = [usr.id, usr.owner_id()]
                for usr_deal in usr_deals:
                    UserDeal.objects.create(user_id=usr_deal, deal_id=deal.id)
                CustomField.save_data(deal, request.POST.get('customField'))
            settings_ = Utility.settings()
            resp = {}
            if settings_.get('deal_assigned') == 1:
                clients_emails = list(
                    User.objects.filter(id__in=client_ids).values_list('email', flat=True)
                )
                d_arr = {
                    'deal_name': deal.name,
                    'deal_pipeline': pipeline.name,
                    'deal_stage': stage.name,
                    'deal_status': deal.status,
                    'deal_price': usr.priceFormat(deal.price)
                }
                resp = Utility.send_email_template('deal_assigned', clients_emails, d_arr)
            setting = Utility.settings(usr.owner_id())
            deal_notification_arr = {'user_name': usr.name, 'deal_name': deal.name}
            if setting.get('deal_notification') == 1:
                Utility.send_slack_msg('new_deal', deal_notification_arr)
            if setting.get('telegram_deal_notification') == 1:
                Utility.send_telegram_msg('new_deal', deal_notification_arr)
            module = 'New Deal'
            webhook = Utility.webhookSetting(module)
            if webhook:
                parameter = json.dumps(deal.__dict__)
                status_call = Utility.WebhookCall(
                    webhook['url'],
                    parameter,
                    webhook['method']
                )
                if status_call:
                    msg = 'Deal successfully created!'
                    if resp and (not resp.get('is_success')) and resp.get('error'):
                        msg += '<br> <span class="text-danger">' + resp.get('error') + '</span>'
                    messages.success(request, msg)
                    return redirect(get_redirect_url(request))
                else:
                    messages.error(request, 'Webhook call failed.')
                    return redirect(get_redirect_url(request))
            msg = 'Deal successfully created!'
            if resp and (not resp.get('is_success')) and resp.get('error'):
                msg += '<br> <span class="text-danger">' + resp.get('error') + '</span>'
            messages.success(request, msg)
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )

    @classmethod
    def show(cls, request: HttpRequest, deal_id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            deal = Deal.objects.get(pk=deal_id)
            if deal.is_active:
                calendar_tasks = []
                if request.user.has_perm('view task'):
                    for task in deal.tasks.all():
                        calendar_tasks.append({
                            'title': task.name,
                            'start': task.date,
                            'url': reverse('deals_tasks_show', args=[deal.id, task.id]),
                            'className': 'bg-primary border-primary' if task.status else 'bg-warning border-warning'
                        })
                custom_fields = list(CustomField.objects.filter(module='deal'))
                deal.custom_field = list(CustomField.get_data(deal, 'deal'))
                permission = []
                return render(
                    request,
                    'deals/show.html',
                    {
                        'deal': deal,
                        'custom_fields': custom_fields,
                        'calendar_tasks': calendar_tasks,
                        'permission': permission
                    }
                )
            else:
                return default_permission_denial(
                    request,
                    err=PermissionDenied('view deal'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )

    @classmethod
    def edit(cls, request: HttpRequest, deal_id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            deal = Deal.objects.get(pk=deal_id)
            if request.user.has_perm('edit deal'):
                if deal.created_by == request.user.owner_id():
                    pipelines = {
                        p.id: p.name
                        for p in Pipeline.objects.filter(created_by=request.user.owner_id())
                    }
                    sources = {
                        s.id: s.name
                        for s in Source.objects.filter(created_by=request.user.owner_id())
                    }
                    products = {
                        p.id: p.name
                        for p in ProductService.objects.filter(created_by=request.user.owner_id())
                    }
                    deal.custom_field = CustomField.get_data(deal, 'deal')
                    custom_fields = list(CustomField.objects.filter(module='deal'))
                    deal.sources = deal.sources.split(',') if deal.sources else []
                    deal.products = deal.products.split(',') if deal.products else []
                    return render(
                        request,
                        'deals/edit.html',
                        {
                            'deal': deal,
                            'pipelines': pipelines,
                            'sources': sources,
                            'products': products,
                            'custom_fields': custom_fields
                        }
                    )
                else:
                    return JsonResponse({'error': 'Permission Denied.'}, status=401)
            else:
                return default_permission_denial(
                    request,
                    err=PermissionDenied('edit deal'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )

    @classmethod
    def update(cls, request: HttpRequest, deal_id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            deal = Deal.objects.get(pk=deal_id)
            if request.user.has_perm('edit deal'):
                if deal.created_by == request.user.owner_id():
                    validator_errors = []
                    name = request.POST.get('name', '')
                    if not name or len(name) > 20:
                        validator_errors.append('Name is required and must be at most 20 characters.')
                    if not request.POST.get('pipeline_id'):
                        validator_errors.append('Pipeline is required.')
                    if validator_errors:
                        messages.error(request, validator_errors[0])
                        return redirect(get_redirect_url(request))
                    deal.name = name
                    deal.phone = request.POST.get('phone', '')
                    deal.price = float(request.POST.get('price', 0))
                    deal.pipeline_id = request.POST.get('pipeline_id')
                    deal.stage_id = request.POST.get('stage_id')
                    sources = request.POST.getlist('sources')
                    products = request.POST.getlist('products')
                    deal.sources = ",".join([s for s in sources if s])
                    deal.products = ",".join([p for p in products if p])
                    deal.notes = request.POST.get('notes', '')
                    deal.save()
                    CustomField.save_data(deal, request.POST.get('customField'))
                    messages.success(request, 'Deal successfully updated!')
                    return redirect(get_redirect_url(request))
                else:
                    return default_permission_denial(
                        request,
                        err=PermissionDenied('edit deal'),
                        ref=f'{CN}::{MN}',
                        logger=logger
                    )
            else:
                return default_permission_denial(
                    request,
                    err=PermissionDenied('edit deal'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )

    @classmethod
    def destroy(cls, request: HttpRequest, deal_id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            deal = Deal.objects.get(pk=deal_id)
            if request.user.has_perm('delete deal'):
                if deal.created_by == request.user.owner_id():
                    DealDiscussion.objects.filter(deal_id=deal.id).delete()
                    DealFile.objects.filter(deal_id=deal.id).delete()
                    ClientDeal.objects.filter(deal_id=deal.id).delete()
                    UserDeal.objects.filter(deal_id=deal.id).delete()
                    DealTask.objects.filter(deal_id=deal.id).delete()
                    ActivityLog.objects.filter(deal_id=deal.id).delete()
                    deal.delete()
                    messages.success(request, 'Deal successfully deleted!')
                    return redirect(reverse('deals_index'))
                else:
                    return default_permission_denial(
                        request,
                        err=PermissionDenied('delete deal'),
                        ref=f'{CN}::{MN}',
                        logger=logger
                    )
            else:
                return default_permission_denial(
                    request,
                    err=PermissionDenied('delete deal'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )

    @classmethod
    def order(cls, request: HttpRequest) -> JsonResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            usr = request.user
            if usr.has_perm('move deal'):
                post = request.POST
                deal_obj = cls.deal(post.get('deal_id'))
                clients = list(
                    ClientDeal.objects.filter(deal_id=deal_obj.id).values_list('client_id', flat=True)
                )
                deal_users = list(deal_obj.users.values_list('id', flat=True))
                usrs = {
                    u.id: u.email
                    for u in User.objects.filter(id__in=list(set(deal_users + clients)))
                }
                if str(deal_obj.stage_id) != str(post.get('stage_id')):
                    new_stage = Stage.objects.get(pk=post.get('stage_id'))
                    ActivityLog.objects.create(
                        user_id=usr.id,
                        deal_id=deal_obj.id,
                        log_type='Move',
                        remark=json.dumps({
                            'title': deal_obj.name,
                            'old_status': deal_obj.stage.name,
                            'new_status': new_stage.name,
                        })
                    )
                    d_arr = {
                        'deal_name': deal_obj.name,
                        'deal_pipeline': deal_obj.email,
                        'deal_stage': deal_obj.stage.name,
                        'deal_status': deal_obj.status,
                        'deal_price': usr.priceFormat(deal_obj.price),
                        'deal_old_stage': deal_obj.stage.name,
                        'deal_new_stage': new_stage.name,
                    }
                    Utility.send_email_template('Move Deal', list(usrs.values()), d_arr)
                for key, item in enumerate(post.getlist('order')):
                    deal_item = cls.deal(item)
                    deal_item.order = key
                    deal_item.stage_id = post.get('stage_id')
                    deal_item.save()
                return JsonResponse({'success': 'Order updated successfully.'}, status=200)
            else:
                return JsonResponse({'error': 'Permission Denied.'}, status=401)
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )
  
    @classmethod
    def deal(cls, item: str) -> Deal:
        # CN = cls.__name__
        # MN = inspect.currentframe().f_code.co_name
        if cls._deal_data is None:
            cls._deal_data = Deal.objects.get(pk=item)
        return cls._deal_data

    @classmethod
    def labels(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if request.user.has_perm('edit deal'):
                deal = Deal.objects.get(pk=id)
                if deal.created_by == request.user.owner_id():
                    labels = {
                        l.id: l.name
                        for l in Label.objects.filter(
                            pipeline_id=deal.pipeline_id,
                            created_by=request.user.creator_id()
                        )
                    }
                    selected_qs = deal.labels() if hasattr(deal, 'labels') and callable(deal.labels) else None
                    selected = dict(selected_qs.values_list('id', 'name')) if selected_qs else {}
                    return render(request, 'deals/labels.html', {
                        'deal': deal,
                        'labels': labels,
                        'selected': selected
                    })
                else:
                    return JsonResponse({'error': 'Permission Denied.'}, status=401)
            else:
                return JsonResponse({'error': 'Permission Denied.'}, status=401)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def label_store(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if request.user.has_perm('edit deal'):
                deal = Deal.objects.get(pk=id)
                if deal.created_by == request.user.owner_id():
                    labels = request.POST.getlist('labels')
                    deal.labels = ",".join(labels) if labels else ''
                    deal.save()
                    messages.success(request, 'Labels successfully updated!')
                    return redirect(get_redirect_url(request))
                else:
                    messages.error(request, 'Permission Denied.')
                    return redirect(get_redirect_url(request))
            else:
                messages.error(request, 'Permission Denied.')
                return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def user_edit(cls, request: HttpRequest, id: int) -> HttpResponse:
        # CN = cls.__name__
        # MN = inspect.currentframe().f_code.co_name
        # TODO: Implement user_edit logic with defensive programming.
        return JsonResponse({'error': 'Not Implemented'}, status=501)

    @classmethod
    def user_update(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            user = request.user
            if not user.has_perm('edit deal'):
                messages.error(request, 'Permission Denied.')
                return redirect(get_redirect_url(request))
            deal = Deal.objects.get(pk=id)
            if deal.created_by != user.owner_id():
                messages.error(request, 'Permission Denied.')
                return redirect(get_redirect_url(request))
            users_list = request.POST.getlist('users')
            if not users_list:
                messages.error(request, 'Please Select Valid User!')
                return redirect(get_redirect_url(request))
            users_qs = User.objects.filter(id__in=[int(x) for x in users_list])
            for u in users_qs:
                UserDeal.objects.create(deal_id=deal.id, user_id=u.id)
            email_data = {
                'deal_name': deal.name,
                'deal_pipeline': deal.pipeline.name,
                'deal_stage': deal.stage.name,
                'deal_status': deal.status,
                'deal_price': user.priceFormat(deal.price),
            }
            resp = Utility.send_email_template(
                'Assign Deal',
                list(users_qs.values_list('email', flat=True)),
                email_data
            )
            msg = 'Users successfully updated!'
            if resp and not resp.get('is_success') and resp.get('error'):
                msg += f"<br> <span class='text-danger'>{resp.get('error')}</span>"
            messages.success(request, msg)
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def user_destroy(cls, request: HttpRequest, id: int, user_id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit deal'):
                messages.error(request, 'Permission Denied.')
                return redirect(get_redirect_url(request))
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                messages.error(request, 'Permission Denied.')
                return redirect(get_redirect_url(request))
            UserDeal.objects.filter(deal_id=deal.id, user_id=user_id).delete()
            messages.success(request, 'User successfully deleted!')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def client_edit(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit deal'):
                return JsonResponse({'error': 'Permission Denied.'}, status=401)
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return JsonResponse({'error': 'Permission Denied.'}, status=401)
            clients = {
                u.id: u.name
                for u in User.objects.filter(
                    created_by=request.user.owner_id(),
                    type='client'
                ).exclude(
                    id__in=ClientDeal.objects.filter(deal_id=deal.id)
                                              .values_list('client_id', flat=True)
                )
            }
            return render(request, 'deals/clients.html', {'deal': deal, 'clients': clients})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def client_update(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit deal'):
                messages.error(request, 'Permission Denied.')
                return redirect(get_redirect_url(request))
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                messages.error(request, 'Permission Denied.')
                return redirect(get_redirect_url(request))
            clients = [x for x in request.POST.getlist('clients') if x]
            if clients:
                for c in clients:
                    ClientDeal.objects.create(deal_id=deal.id, client_id=int(c))
                messages.success(request, 'Clients successfully updated!')
            else:
                messages.error(request, 'Please Select Valid Clients!')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def client_destroy(cls, request: HttpRequest, id: int, client_id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit deal'):
                messages.error(request, 'Permission Denied.')
                return redirect(get_redirect_url(request))
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                messages.error(request, 'Permission Denied.')
                return redirect(get_redirect_url(request))
            ClientDeal.objects.filter(deal_id=deal.id, client_id=client_id).delete()
            messages.success(request, 'Client successfully deleted!')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def product_edit(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit deal'):
                return JsonResponse({'error': 'Permission Denied.'}, status=401)
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return JsonResponse({'error': 'Permission Denied.'}, status=401)
            products = {
                p.id: p.name
                for p in ProductService.objects.filter(
                    created_by=request.user.owner_id()
                ).exclude(
                    id__in=deal.products.split(',') if deal.products else []
                )
            }
            return render(request, 'deals/products.html', {'deal': deal, 'products': products})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def product_update(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            user = request.user
            if not user.has_perm('edit deal'):
                messages.error(request, 'Permission Denied.')
                return redirect(get_redirect_url(request))
            deal = Deal.objects.get(pk=id)
            if deal.created_by != user.owner_id():
                messages.error(request, 'Permission Denied.')
                return redirect(get_redirect_url(request))
            products = [x for x in request.POST.getlist('products') if x]
            old_products = deal.products.split(',') if deal.products else []
            merged = list(set(old_products + products))
            deal.products = ",".join(merged)
            deal.save()
            obj_product = {
                p.id: p.name
                for p in ProductService.objects.filter(id__in=products)
            }
            ActivityLog.objects.create(
                user_id=user.id,
                deal_id=deal.id,
                log_type='Add Product',
                remark=json.dumps({'title': ",".join(obj_product.values())})
            )
            if products:
                messages.success(request, 'Products successfully updated!')
            else:
                messages.error(request, 'Please Select Valid Product!')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def product_destroy(cls, request: HttpRequest, id: int, product_id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit deal'):
                messages.error(request, 'Permission Denied.')
                return redirect(get_redirect_url(request))
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                messages.error(request, 'Permission Denied.')
                return redirect(get_redirect_url(request))
            products = deal.products.split(',') if deal.products else []
            products = [p for p in products if p != str(product_id)]
            deal.products = ",".join(products)
            deal.save()
            messages.success(request, 'Products successfully deleted!')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def file_upload(cls, request: HttpRequest, id: int) -> JsonResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit deal'):
                return JsonResponse({'is_success': False, 'error': 'Permission Denied.'}, status=401)
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return JsonResponse({'is_success': False, 'error': 'Permission Denied.'}, status=401)
            if 'file' not in request.FILES:
                messages.error(request, 'File is required.')
                return redirect(get_redirect_url(request))
            file_obj = request.FILES['file']
            image_size = file_obj.size
            result = Utility.update_storage_limit(request.user.creator_id(), image_size)
            file_name = file_obj.name
            file_path = f"{request.POST.get('deal_id')}_{hash(datetime.time())}_{file_obj.name}"
            file_record = DealFile.objects.create(
                deal_id=int(request.POST.get('deal_id')),
                file_name=file_name,
                file_path=file_path
            )
            if result == 1:
                default_storage.save(os.path.join('deal_files', file_path), file_obj)
                download_url = reverse('deals_file_download', args=[deal.id, file_record.id])
                delete_url = reverse('deals_file_delete', args=[deal.id, file_record.id])
                ret = {'is_success': True, 'download': download_url, 'delete': delete_url}
            else:
                ret = {'is_success': True, 'status': 1, 'success_msg': f"<br> <span class='text-danger'>{result}</span>"}
            ActivityLog.objects.create(
                user_id=request.user.id,
                deal_id=deal.id,
                log_type='Upload File',
                remark=json.dumps({'file_name': file_name})
            )
            return JsonResponse(ret)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def file_download(cls, request: HttpRequest, id: int, file_id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit deal'):
                messages.error(request, 'Permission Denied.')
                return redirect(get_redirect_url(request))
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                messages.error(request, 'Permission Denied.')
                return redirect(get_redirect_url(request))
            file_obj = DealFile.objects.get(pk=file_id)
            file_path = os.path.join(settings.BASE_DIR, 'storage', 'deal_files', file_obj.file_path)
            if os.path.exists(file_path):
                return FileResponse(open(file_path, 'rb'), as_attachment=True, filename=file_obj.file_name)
            else:
                messages.error(request, 'File does not exist.')
                return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def file_delete(cls, request: HttpRequest, id: int, file_id: int) -> JsonResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit deal'):
                return JsonResponse({'is_success': False, 'error': 'Permission Denied.'}, status=401)
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return JsonResponse({'is_success': False, 'error': 'Permission Denied.'}, status=401)
            file_obj = DealFile.objects.get(pk=file_id)
            file_path_rel = os.path.join('deal_files', file_obj.file_path)
            Utility.change_storage_limit(request.user.creator_id(), file_path_rel)
            full_path = os.path.join(settings.BASE_DIR, 'storage', 'deal_files', file_obj.file_path)
            if os.path.exists(full_path):
                os.remove(full_path)
            file_obj.delete()
            return JsonResponse({'is_success': True}, status=200)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
	
    @classmethod
    def note_store(cls, request: HttpRequest, id: int) -> JsonResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit deal'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('edit deal'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return default_permission_denial(
                    request,
                    err=PermissionDenied('edit deal'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            with transaction.atomic():
                deal.notes = request.POST.get('notes', '')
                deal.save()
            return JsonResponse({'is_success': True, 'success': 'Note successfully saved!'}, status=200)
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger,
                json={}
            )

    @classmethod
    def task_create(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('create task'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('create task'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return default_permission_denial(
                    request,
                    err=PermissionDenied('create task'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            priorities = DealTask.priorities
            status = DealTask.status
            return render(request, 'deals/tasks.html', {
                'deal': deal,
                'priorities': priorities,
                'status': status
            })
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )

    @classmethod
    def task_store(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            user = request.user
            if not user.has_perm('create task'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('create task'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            deal = Deal.objects.get(pk=id)
            clients = list(ClientDeal.objects.filter(deal_id=id).values_list('client_id', flat=True))
            deal_users = list(deal.users.values_list('id', flat=True))
            user_ids = list(set(deal_users + clients))
            usrs = {u.id: u.email for u in User.objects.filter(id__in=user_ids)}
            if deal.created_by != user.owner_id():
                return default_permission_denial(
                    request,
                    err=PermissionDenied('create task'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            for field in ['name', 'date', 'time', 'priority', 'status']:
                if not request.POST.get(field):
                    messages.error(request, f"{field.capitalize()} is required.")
                    return redirect(get_redirect_url(request))
            try:
                dt = datetime.datetime.strptime(
                    request.POST.get('date') + ' ' + request.POST.get('time'),
                    '%Y-%m-%d %H:%M:%S'
                )
                task_time = dt.time()
            except ValueError as ve:
                return default_undefined_exception(
                    request,
                    err=ve,
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            with transaction.atomic():
                deal_task = DealTask.objects.create(
                    deal_id=deal.id,
                    name=request.POST.get('name'),
                    date=request.POST.get('date'),
                    time=task_time,
                    priority=request.POST.get('priority'),
                    status=request.POST.get('status')
                )
                ActivityLog.objects.create(
                    user_id=user.id,
                    deal_id=deal.id,
                    log_type='Create Task',
                    remark=json.dumps({'title': deal_task.name})
                )
            t_arr = {
                'deal_name': deal.name,
                'deal_pipeline': deal.pipeline.name,
                'deal_stage': deal.stage.name,
                'deal_status': deal.status,
                'deal_price': user.priceFormat(deal.price),
                'task_name': deal_task.name,
                'task_priority': DealTask.priorities.get(deal_task.priority, ''),
                'task_status': DealTask.status.get(deal_task.status, '')
            }
            Utility.send_email_template('Create Task', list(usrs.values()), t_arr)
            messages.success(request, 'Task successfully created!')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )

    @classmethod
    def task_show(cls, request: HttpRequest, id: int, task_id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('view task'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('view task'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return default_permission_denial(
                    request,
                    err=PermissionDenied('view task'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            task = DealTask.objects.get(pk=task_id)
            return render(request, 'deals/tasksShow.html', {'task': task, 'deal': deal})
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )

    @classmethod
    def task_edit(cls, request: HttpRequest, id: int, task_id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit task'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('edit task'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return default_permission_denial(
                    request,
                    err=PermissionDenied('edit task'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            priorities = DealTask.priorities
            status = DealTask.status
            task = DealTask.objects.get(pk=task_id)
            return render(request, 'deals/tasks.html', {
                'task': task,
                'deal': deal,
                'priorities': priorities,
                'status': status
            })
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )

    @classmethod
    def task_update(cls, request: HttpRequest, id: int, task_id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit task'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('edit task'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return default_permission_denial(
                    request,
                    err=PermissionDenied('edit task'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            for field in ['name', 'date', 'time', 'priority', 'status']:
                if not request.POST.get(field):
                    messages.error(request, f"{field.capitalize()} is required.")
                    return redirect(get_redirect_url(request))
            task = DealTask.objects.get(pk=task_id)
            task.name = request.POST.get('name')
            task.date = request.POST.get('date')
            try:
                task.time = datetime.datetime.strptime(
                    request.POST.get('date') + ' ' + request.POST.get('time'),
                    '%Y-%m-%d %H:%M:%S'
                ).time()
            except ValueError as ve:
                return default_undefined_exception(
                    request,
                    err=ve,
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            task.priority = request.POST.get('priority')
            task.status = request.POST.get('status')
            task.save()
            messages.success(request, 'Task successfully updated!')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )

    @classmethod
    def task_update_status(cls, request: HttpRequest, id: int, task_id: int) -> JsonResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit task'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('edit task'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return default_permission_denial(
                    request,
                    err=PermissionDenied('edit task'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            if not request.POST.get('status'):
                return JsonResponse({'is_success': False, 'error': 'Status is required.'}, status=401)
            task = DealTask.objects.get(pk=task_id)
            task.status = 0 if request.POST.get('status') else 1
            task.save()
            return JsonResponse({
                'is_success': True,
                'success': 'Task successfully updated!',
                'status': task.status,
                'status_label': DealTask.status.get(task.status, '')
            }, status=200)
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger,
                json={}
            )

    @classmethod
    def task_destroy(cls, request: HttpRequest, id: int, task_id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('delete task'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('delete task'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return default_permission_denial(
                    request,
                    err=PermissionDenied('delete task'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            task = DealTask.objects.get(pk=task_id)
            with transaction.atomic():
                task.delete()
            messages.success(request, 'Task successfully deleted!')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )
  
    @classmethod
    def source_edit(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit deal'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('edit deal'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return default_permission_denial(
                    request,
                    err=PermissionDenied('edit deal'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            sources = {
                s.id: s.name
                for s in Source.objects.filter(created_by=request.user.owner_id())
            }
            selected_qs = deal.sources() if hasattr(deal, 'sources') and callable(deal.sources) else None
            selected = dict(selected_qs.values_list('id', 'name')) if selected_qs else {}
            return render(request, 'deals/sources.html', {
                'deal': deal,
                'sources': sources,
                'selected': selected
            })
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger,
                json={}
            )

    @classmethod
    def source_update(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            user = request.user
            if not user.has_perm('edit deal'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('edit deal'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            deal = Deal.objects.get(pk=id)
            if deal.created_by != user.owner_id():
                return default_permission_denial(
                    request,
                    err=PermissionDenied('edit deal'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            sources = request.POST.getlist('sources')
            deal.sources = ",".join(sources) if sources else ""
            with transaction.atomic():
                deal.save()
                ActivityLog.objects.create(
                    user_id=user.id,
                    deal_id=deal.id,
                    log_type='Update Sources',
                    remark=json.dumps({'title': 'Update Sources'})
                )
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )

    @classmethod
    def source_destroy(cls, request: HttpRequest, id: int, source_id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit deal'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('edit deal'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return default_permission_denial(
                    request,
                    err=PermissionDenied('edit deal'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            items = deal.sources.split(',') if deal.sources else []
            deal.sources = ",".join([s for s in items if s != str(source_id)])
            deal.save()
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )

    @classmethod
    def email_create(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('create deal email'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('create deal email'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return default_permission_denial(
                    request,
                    err=PermissionDenied('create deal email'),
                    ref=f'{CN}::{MN}',
                    logger=logger,
                    json={}
                )
            return render(request, 'deals/emails.html', {'deal': deal})
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger,
                json={}
            )

    @classmethod
    def email_store(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('create deal email'):
                return default_permission_denial(
                    request,
                    err=PermissionDenied('create deal email'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return default_permission_denial(
                    request,
                    err=PermissionDenied('create deal email'),
                    ref=f'{CN}::{MN}',
                    logger=logger
                )
            for field in ['to', 'subject', 'description']:
                if not request.POST.get(field):
                    messages.error(request, f"{field.capitalize()} is required.")
                    return redirect(get_redirect_url(request))
            DealEmail.objects.create(
                deal_id=deal.id,
                to=request.POST['to'],
                subject=request.POST['subject'],
                description=request.POST['description']
            )
            smtp_error = ''
            try:
                settings_ = Utility.settings()
                DealEmail.send_mail(
                    subject=request.POST['subject'],
                    message=request.POST['description'],
                    from_email=settings_.get('DEFAULT_FROM_EMAIL'),
                    recipient_list=[request.POST['to']]
                )
            except Exception as ex:
                smtp_error = 'E-Mail has not been sent due to SMTP configuration'
                logger.error(f"{CN}::{MN} SMTP error: {ex}")
            ActivityLog.objects.create(
                user_id=request.user.id,
                deal_id=deal.id,
                log_type='Create Deal Email',
                remark=json.dumps({'title': 'Create new Deal Email'})
            )
            msg = 'Email successfully created!'
            if smtp_error:
                msg += f'<br> <span class="text-danger">{smtp_error}</span>'
            messages.success(request, msg)
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(
                request,
                err=e,
                ref=f'{CN}::{MN}',
                logger=logger
            )
    
    @classmethod
    def permission(cls, request: HttpRequest, id: int, clientId: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit deal'):
                return default_permission_denial(request, err=PermissionDenied('edit deal'),
                                                ref=f'{CN}::{MN}', logger=logger)
            deal = Deal.objects.get(pk=id)
            client = User.objects.get(pk=clientId)
            selected_obj = client.clientPermission(deal.id) if hasattr(client, 'clientPermission') else None
            selected = selected_obj.permissions.split(',') if selected_obj and selected_obj.permissions else []
            permissions = Deal.permissions
            return render(request, 'deals/permissions.html', {
                'deal': deal,
                'client': client,
                'selected': selected,
                'permissions': permissions
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def permission_store(cls, request: HttpRequest, id: int, clientId: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit deal'):
                return default_permission_denial(request, err=PermissionDenied('edit deal'),
                                                ref=f'{CN}::{MN}', logger=logger)
            deal = Deal.objects.get(pk=id)
            client = User.objects.get(pk=clientId)
            perms = request.POST.getlist('permissions')
            perm_obj = client.clientPermission(deal.id) if hasattr(client, 'clientPermission') else None
            if perm_obj:
                perm_obj.permissions = ",".join(perms)
                perm_obj.save()
            elif perms:
                ClientPermission.objects.create(
                    client_id=clientId,
                    deal_id=deal.id,
                    permissions=",".join(perms)
                )
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def json_user(cls, request: HttpRequest) -> JsonResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            deal_id_str = request.POST.get('deal_id')
            if deal_id_str:
                try:
                    deal_id = int(deal_id_str)
                except ValueError:
                    return default_undefined_exception(request, err=ValueError('invalid id'),
                                                        ref=f'{CN}::{MN}', logger=logger, json={})
                deal = Deal.objects.get(pk=deal_id)
                users = dict(deal.users.values_list('id', 'name'))
                return JsonResponse(users, status=200)
            return JsonResponse({}, status=200)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger, json={})

    @classmethod
    def change_pipeline(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            user = request.user
            user.default_pipeline = request.POST.get('default_pipeline_id')
            user.save()
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def discussion_create(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return default_permission_denial(request, err=PermissionDenied('access discussion'),
                                                ref=f'{CN}::{MN}', logger=logger, json={})
            return render(request, 'deals/discussions.html', {'deal': deal})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def discussion_store(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            user = request.user
            deal = Deal.objects.get(pk=id)
            if deal.created_by != user.owner_id():
                return default_permission_denial(request, err=PermissionDenied('add discussion'),
                                                ref=f'{CN}::{MN}', logger=logger)
            DealDiscussion.objects.create(
                comment=request.POST.get('comment'),
                deal_id=deal.id,
                created_by=user.id
            )
            messages.success(request, 'Message successfully added!')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def change_status(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            deal = Deal.objects.filter(id=id).first()
            if not deal:
                raise Deal.DoesNotExist
            deal.status = request.POST.get('deal_status', deal.status)
            deal.save()
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def call_create(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('create deal call'):
                return default_permission_denial(request, err=PermissionDenied('create deal call'),
                                                ref=f'{CN}::{MN}', logger=logger, json={})
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return default_permission_denial(request, err=PermissionDenied('create deal call'),
                                                ref=f'{CN}::{MN}', logger=logger, json={})
            users = User.objects.filter(id__in=ClientDeal.objects.filter(deal_id=deal.id)
                                        .values_list('client_id', flat=True))
            return render(request, 'deals/calls.html', {'deal': deal, 'users': users})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def call_store(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            user = request.user
            if not user.has_perm('create deal call'):
                return default_permission_denial(request, err=PermissionDenied('create deal call'),
                                                ref=f'{CN}::{MN}', logger=logger)
            deal = Deal.objects.get(pk=id)
            if deal.created_by != user.owner_id():
                return default_permission_denial(request, err=PermissionDenied('create deal call'),
                                                ref=f'{CN}::{MN}', logger=logger)
            for field in ['subject', 'call_type', 'user_id']:
                if not request.POST.get(field):
                    messages.error(request, f"{field.capitalize()} is required.")
                    return redirect(get_redirect_url(request))
            with transaction.atomic():
                DealCall.objects.create(
                    deal_id=deal.id,
                    subject=request.POST['subject'],
                    call_type=request.POST['call_type'],
                    duration=request.POST.get('duration'),
                    user_id=request.POST['user_id'],
                    description=request.POST.get('description', ''),
                    call_result=request.POST.get('call_result', '')
                )
                ActivityLog.objects.create(
                    user_id=user.id,
                    deal_id=deal.id,
                    log_type='Create Deal Call',
                    remark=json.dumps({'title': 'Create new Deal Call'})
                )
            messages.success(request, 'Call successfully created!')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def call_edit(cls, request: HttpRequest, id: int, call_id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit deal call'):
                return default_permission_denial(request, err=PermissionDenied('edit deal call'),
                                                ref=f'{CN}::{MN}', logger=logger, json={})
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return default_permission_denial(request, err=PermissionDenied('edit deal call'),
                                                ref=f'{CN}::{MN}', logger=logger, json={})
            call = DealCall.objects.get(pk=call_id)
            users = User.objects.filter(id__in=ClientDeal.objects.filter(deal_id=deal.id)
                                        .values_list('client_id', flat=True))
            return render(request, 'deals/calls.html', {'call': call, 'deal': deal, 'users': users})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def call_update(cls, request: HttpRequest, id: int, call_id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit deal call'):
                return default_permission_denial(request, err=PermissionDenied('edit deal call'),
                                                ref=f'{CN}::{MN}', logger=logger)
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return default_permission_denial(request, err=PermissionDenied('edit deal call'),
                                                ref=f'{CN}::{MN}', logger=logger)
            for field in ['subject', 'call_type', 'user_id']:
                if not request.POST.get(field):
                    messages.error(request, f"{field.capitalize()} is required.")
                    return redirect(get_redirect_url(request))
            call = DealCall.objects.get(pk=call_id)
            call.subject = request.POST['subject']
            call.call_type = request.POST['call_type']
            call.duration = request.POST.get('duration')
            call.user_id = request.POST['user_id']
            call.description = request.POST.get('description', '')
            call.call_result = request.POST.get('call_result', '')
            call.save()
            messages.success(request, 'Call successfully updated!')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def call_destroy(cls, request: HttpRequest, id: int, call_id: int) -> HttpResponse:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('delete deal call'):
                return default_permission_denial(request, err=PermissionDenied('delete deal call'),
                                                ref=f'{CN}::{MN}', logger=logger)
            deal = Deal.objects.get(pk=id)
            if deal.created_by != request.user.owner_id():
                return default_permission_denial(request, err=PermissionDenied('delete deal call'),
                                                ref=f'{CN}::{MN}', logger=logger)
            call = DealCall.objects.get(pk=call_id)
            with transaction.atomic():
                call.delete()
            messages.success(request, 'Call successfully deleted!')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)