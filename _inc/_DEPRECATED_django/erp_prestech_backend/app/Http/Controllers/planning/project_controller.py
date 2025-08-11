import base64
import inspect
import json
import logging
from datetime import date
from typing import Any, Dict, Union
from datetime import datetime
from django.conf import settings
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.contrib.contenttypes.models import ContentType
from django.core import signing
from django.core.exceptions import PermissionDenied
from django.core.files.storage import default_storage
from django.db.models import Count, Q
from django.http import HttpRequest, HttpResponse, HttpResponseForbidden, Http404, JsonResponse
from django.shortcuts import render, redirect, get_object_or_404
from django.template.loader import render_to_string
from django.urls import reverse
from django.utils import timezone, translation
from django.utils.decorators import method_decorator
from django.views.decorators.http import require_POST
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._helpers.http import get_redirect_url
from .._helpers.security import permission_required_custom, security_logger
from .._traits.controller import Controller
from ....Models.activity.activity_log import ActivityLog
from ....Models.bugs.bug import Bug
from ....Models.bugs.bug_status import BugStatus
from ....Models.bugs.bug_comment import BugComment
from ....Models.bugs.bug_file import BugFile
from ....Models.configs.permission import Permission
from ....Models.individuals.user import User
from ....Models.planning.milestone import Milestone
from ....Models.planning.forms.milestone_form import MilestoneForm
from ....Models.planning.project import Project
from ....Models.planning.project_task import ProjectTask
from ....Models.planning.project_user import ProjectUser
from ....Models.planning.task_stage import TaskStage
from ....Models.planning.time_tracker import TimeTracker
from ....Models.utils.utility import Utility

logger = logging.getLogger(__name__)

class ProjectController(Controller):
    @classmethod
    def _set_auth(cls, request: HttpRequest, perm: str) -> Union[Exception, bool]:
        inst = cls()
        inst.request = request
        inst.authorize(perm)
        return True
    
    @staticmethod
    def _set_bug(data: Any) -> Dict[str, Any]:
        bug_props = {}
        for k in ('title', 'priority', 'start_date', 'due_date', 'assign_to', 'status_id',
                    'description'):
            if k == 'status_id': setattr(bug_props, k, data['status'])
            else: setattr(bug_props, k, data[k] or '' if k == 'description' else data[k])
        return bug_props

    @classmethod
    def _get_project(cls, project_id: str) -> Project:
        return get_object_or_404(Project, id=project_id)

    @classmethod
    def _handle_image_upload(cls, request: HttpRequest, creator_id: Any, project: Project = None) -> None:
        img = request.FILES.get('project_image')
        if not img:
            return
        size_ok = Utility.update_storage_limit(creator_id, img.size)
        if size_ok != 1:
            messages.error(request, f'Storage limit exceeded: {size_ok}')
            return
        if project and project.project_image:
            Utility.change_storage_limit(creator_id, project.project_image.name)
        filename = f'projects/{int(datetime.utcnow().timestamp())}_{img.name}'
        if project:
            project.project_image.save(filename, img)
        else:
            request._new_image = (filename, img)

    @classmethod
    def _assign_users(cls, request: HttpRequest, project: Project) -> None:
        creator = request.user.creator_id()
        current = request.user
        if current.type == 'company':
            ids = {current.id}
        else:
            ids = {creator, current.id}
        ids.update(request.POST.getlist('user'))
        ProjectUser.objects.bulk_create(
            [ProjectUser(project=project, user_id=uid) for uid in ids]
        )

    @classmethod
    def _notify_new_project(cls, request: HttpRequest, project: Project) -> None:
        creator = request.user.creator_id()
        setting = Utility.settings(creator)
        notif = {'project_name': project.project_name, 'user_name': request.user.name}
        if setting.get('project_notification'):
            Utility.send_slack_msg('new_project', notif)
        if setting.get('telegram_project_notification'):
            Utility.send_telegram_msg('new_project', notif)
        webhook = Utility.webhook_setting('New Project')
        if webhook and not Utility.webhook_call(webhook['url'], project.to_json(), webhook['method']):
            messages.error(request, 'Webhook call failed.')

    @classmethod
    def _build_project_data(cls, project: Project, user: User) -> dict:
        data = {}
        tasks = ProjectTask.objects.filter(project=project)
        total = tasks.count()
        done = tasks.filter(is_complete=True).count()
        data['task'] = {
            'total': total,
            'done': done,
            'percentage': Utility.get_percentage(done, total)
        }
        exp = sum(e.amount for e in project.expense.all())
        budget = project.budget or 0
        data['expense'] = {
            'allocated': budget,
            'total': exp,
            'percentage': Utility.get_percentage(exp, budget)
        }
        assigned = project.users.count()
        total_users = User.objects.filter(created_by=user.creator_id()).count()
        data['user_assigned'] = {
            'total': f'{assigned}/{total_users}',
            'percentage': Utility.get_percentage(assigned, total_users)
        }
        sd, ed = project.start_date, project.end_date
        total_days = (ed - sd).days
        rem_days = (date.today() - sd).days
        data['day_left'] = {
            'day': f'{rem_days}/{total_days}',
            'percentage': Utility.get_percentage(rem_days, total_days)
        }
        rem = tasks.filter(is_complete=False, created_by=user.creator_id()).count()
        data['open_task'] = {
            'tasks': f'{rem}/{total}',
            'percentage': Utility.get_percentage(rem, total)
        }
        ml = project.milestones()
        tot_m = ml.count()
        done_m = ml.filter(status__icontains='complete').count()
        data['milestone'] = {
            'total': f'{done_m}/{tot_m}',
            'percentage': Utility.get_percentage(done_m, tot_m)
        }
        times = sum(t.total_time for t in project.timesheets().filter(created_by=user.id))
        hrs = Utility.second_to_time(times)
        data['time_spent'] = {
            'total': hrs,
            'percentage': '100%'
        }
        alloc = Project.project_hrs(project.id)['allocated']
        data['task_allocated_hrs'] = {
            'hrs': alloc,
            'percentage': '100%'
        }
        seven = Utility.get_last_seven_days()
        ct, cts = [], []
        for day in seven:
            d = day
            tc = tasks.filter(is_complete=True, assign_to__icontains=str(user.id), marked_at__date=d).count()
            tt = sum(t.total_time for t in project.timesheets().filter(created_by=user.id, date__date=d))
            ct.append(tc)
            cts.append(tt)
        data['task_chart'] = {'chart': ct, 'total': sum(ct)}
        data['timesheet_chart'] = {'chart': cts, 'total': sum(cts)}
        return data

    @classmethod
    def index(cls, request: HttpRequest, view: str = 'grid') -> Union[HttpResponse, JsonResponse, None]:
        REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'manage project')
            return render(request, 'projects/index.html', {'view': view})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e,
                ref=REF,
                logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def create(cls, request: HttpRequest) -> Union[HttpResponse, JsonResponse, None]:
        REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'create project')
            cid = request.user.creator_id()
            users = User.objects.filter(created_by=cid).exclude(type='client')\
                                .values_list('name', 'id')
            clients = User.objects.filter(created_by=cid, type='client')\
                                  .values_list('name', 'id')
            return render(request, 'projects/create.html', {
                'clients': [('', 'Select Client')] + list(clients),
                'users': [('', 'Select User')] + list(users),
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e,
                ref=REF,
                logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def store(cls, request: HttpRequest) -> Union[HttpResponse, JsonResponse, None]:
        REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'create project')
            if not request.POST.get('project_name') or 'project_image' not in request.FILES:
                messages.error(request, 'Project name and image are required.')
                return redirect(reverse('projects.list'))
            props = {'created_by': request.user.creator_id(), 'copy_link_setting': (
                '{"member":"on","milestone":"off","basic_details":"on","activity":"off",'
                '"attachment":"on","bug_report":"on","task":"off","tracker_details":"off",'
                '"timesheet":"off","password_protected":"off"}'
            )}
            for src, dst, default in (
                ('project_name','project_name',None),
                ('start_date','start_date',None),
                ('end_date','end_date',None),
                ('client','client_id',None),
                ('budget','budget',0),
                ('description','description',''),
                ('status','status',''),
                ('estimated_hrs','estimated_hrs',''),
                ('tag','tags','')
            ):
                val = request.POST.get(src)
                props[dst] = val if val or default is None else default
            proj = Project(**props)
            proj.save()
            cls._handle_image_upload(request, request.user.creator_id(), proj)
            proj.save()
            cls._assign_users(request, proj)
            cls._notify_new_project(request, proj)
            messages.success(request, 'Project added successfully.')
            return redirect(reverse('projects.list'))
        except PermissionDenied as e:
            return default_permission_denial(request, err=e,
                ref=REF,
                logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e,
                ref=REF,
                logger=logger)

    @classmethod
    def show(cls, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'view project')
            project = cls._get_project(project_id)
            allowed = (
                (request.user.type == 'client' and project.client_id == request.user.id)
                or ProjectUser.objects.filter(project=project, user=request.user).exists()
            )
            if not allowed:
                return HttpResponseForbidden('Permission Denied.')
            data = cls._build_project_data(project, request.user)
            last = TaskStage.objects.filter(
                created_by=request.user.creator_id()
            ).order_by('-order').first()
            return render(request, 'projects/view.html', {
                'project': project, 'project_data': data, 'last_task': last,
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e,
                ref=REF,
                logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def edit(cls, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'edit project')
            project = cls._get_project(project_id)
            if project.created_by != request.user.creator_id():
                messages.error(request, 'Permission denied.')
                return redirect(reverse('projects.list'))
            clients = User.objects.filter(
                created_by=request.user.creator_id(), type='client'
            ).values_list('name', 'id')
            return render(request, 'projects/edit.html', {
                'project': project, 'clients': clients,
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e,
                ref=REF,
                logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def update(cls, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'edit project')
            project = cls._get_project(project_id)
            name = request.POST.get('project_name')
            if not name:
                messages.error(request, 'Project name is required.')
                return redirect(reverse('projects.list'))
            for field in ('project_name', 'start_date', 'end_date',
                          'budget', 'client', 'description', 'status',
                          'estimated_hrs', 'tag'):
                if val := request.POST.get(field):
                    setattr(project, field if field != 'tag' else 'tags', val)
            cls._handle_image_upload(request, request.user.creator_id(), project)
            project.save()
            messages.success(request, 'Project updated successfully.')
            return redirect(reverse('projects.list'))
        except PermissionDenied as e:
            return default_permission_denial(request, err=e,
                ref=REF,
                logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def destroy(cls, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'delete project')
            project = cls._get_project(project_id)
            if project.project_image:
                Utility.change_storage_limit(request.user.creator_id(), project.project_image.name)
            project.delete()
            messages.success(request, 'Project successfully deleted.')
            return redirect(get_redirect_url(request))
        except PermissionDenied as e:
            return default_permission_denial(request, err=e,
                ref=REF,
                logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)
            
    @method_decorator(login_required)
    @permission_required_custom('manage project', security_logger)
    def project_list(self, request: HttpRequest, view:str='grid') -> Union[HttpResponse, JsonResponse, None]:
        REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            return render(request, 'projects/index.html', {'view': view})
        except Exception as e:
            return default_undefined_exception(request, err=e,
                ref=REF, logger=logger)

    @method_decorator(login_required)
    @permission_required_custom('create project', security_logger)
    def project_create(request: HttpRequest) -> Union[HttpResponse, JsonResponse, None]:
        try:
            cid = request.user.creator_id()
            users = User.objects.filter(created_by=cid).exclude(type='client')\
                        .values_list('name','id')
            clients = User.objects.filter(created_by=cid, type='client')\
                        .values_list('name','id')
            return render(request, 'projects/create.html', {
                'users': [('', 'Select User')] + list(users),
                'clients': [('', 'Select Client')] + list(clients),
            })
        except Exception as e:
            return default_undefined_exception(request, err=e,
                ref='project_create', logger=logger)

    @method_decorator(login_required)
    @permission_required_custom('create project', security_logger)
    @require_POST
    def project_store(self, request: HttpRequest) -> Union[HttpResponse, JsonResponse, None]:
        REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            if not request.POST.get('project_name') or 'project_image' not in request.FILES:
                messages.error(request, 'Project name and image are required.')
                return redirect(request.META.get('HTTP_REFERER','projects:list'))
            props = {'created_by': request.user.creator_id(), 'copy_link_setting': (
                '{"member":"on","milestone":"off","basic_details":"on","activity":"off",'
                '"attachment":"on","bug_report":"on","task":"off","tracker_details":"off",'
                '"timesheet":"off","password_protected":"off"}'
            )}
            for src, dst, default in (
                ('project_name','project_name',None),
                ('start_date','start_date',None),
                ('end_date','end_date',None),
                ('client','client_id',None),
                ('budget','budget',0),
                ('description','description',''),
                ('status','status',''),
                ('estimated_hrs','estimated_hrs',''),
                ('tag','tags','')
            ):
                val = request.POST.get(src)
                props[dst] = val if val or default is None else default
            proj = Project(**props)
            img = request.FILES['project_image']
            ok = Utility.update_storage_limit(request.user.creator_id(), img.size)
            if ok == 1:
                path = f'projects/{int(datetime.utcnow().timestamp())}_{img.name}'
                proj.project_image.save(path, img)
            proj.save()
            base = [request.user.id] if request.user.type=='company' else [request.user.creator_id(), request.user.id]
            ids = set(base + request.POST.getlist('user'))
            ProjectUser.objects.bulk_create([ProjectUser(project=proj, user_id=uid) for uid in ids])
            setting = Utility.settings(request.user.creator_id())
            notif = {'project_name': proj.project_name, 'user_name': request.user.name}
            for key, fn in (('project_notification', Utility.send_slack_msg), ('telegram_project_notification', Utility.send_telegram_msg)):
                if setting.get(key):
                    fn('new_project', notif)
            webhook = Utility.webhook_setting('New Project')
            if webhook and not Utility.webhook_call(webhook['url'], proj.to_json(), webhook['method']):
                messages.error(request, 'Webhook call failed.')
                return redirect(request.META.get('HTTP_REFERER'))
            messages.success(request, 'Project added successfully.')
            return redirect(reverse('projects:list'))
        except PermissionDenied as e:
            return default_permission_denial(request, err=e,
                ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e,
                ref=REF, logger=logger)

    @method_decorator(login_required)
    @permission_required_custom('view project', security_logger)
    def project_show(self, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            proj = get_object_or_404(Project, pk=project_id)
            allowed = (
                request.user.type=='client' and proj.client_id==request.user.id
            ) or ProjectUser.objects.filter(project=proj, user=request.user).exists()
            if not allowed:
                return HttpResponseForbidden('Permission Denied.')
            data = ProjectController._build_project_data(proj, request.user)
            last = TaskStage.objects.filter(created_by=request.user.creator_id())\
                                    .order_by('-order').first()
            return render(request, 'projects/view.html', {
                'project': proj, 'project_data': data, 'last_task': last
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e,
                ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e,
                ref=REF, logger=logger)

    @method_decorator(login_required)
    @permission_required_custom('edit project', security_logger)
    def project_edit(self, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            proj = get_object_or_404(Project, pk=project_id, created_by=request.user.creator_id())
            clients = User.objects.filter(created_by=request.user.creator_id(), type='client')\
                        .values_list('name','id')
            return render(request, 'projects/edit.html', {
                'project': proj, 'clients': [('', 'Select Client')] + list(clients)
            })
        except Exception as e:
            return default_undefined_exception(request, err=e,
                ref=REF, logger=logger)

    @method_decorator(login_required)
    @permission_required_custom('edit project', security_logger)
    @require_POST
    def project_update(self, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            proj = get_object_or_404(Project, pk=project_id, created_by=request.user.creator_id())
            for src, dst in (
                ('project_name','project_name'),
                ('start_date','start_date'),
                ('end_date','end_date'),
                ('budget','budget'),
                ('client','client_id'),
                ('description','description'),
                ('status','status'),
                ('estimated_hrs','estimated_hrs'),
                ('tag','tags')
            ):
                if val := request.POST.get(src):
                    setattr(proj, dst, val)
            ProjectController._handle_image_upload(request, request.user.creator_id(), proj)
            proj.save()
            messages.success(request, 'Project updated successfully.')
            return redirect(reverse('projects:list'))
        except Exception as e:
            return default_undefined_exception(request, err=e,
                ref=REF, logger=logger)

    @method_decorator(login_required)
    @permission_required_custom('delete project', security_logger)
    @require_POST
    def project_destroy(self, request: HttpRequest, project_id:str) -> Union[HttpResponse, JsonResponse, None]:
        REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            proj = get_object_or_404(Project, pk=project_id)
            if proj.project_image:
                Utility.change_storage_limit(request.user.creator_id(), proj.project_image.name)
            proj.delete()
            messages.success(request, 'Project successfully deleted.')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e,
                ref=REF, logger=logger)

    @method_decorator(login_required)
    def invite_member_view(self, request: HttpRequest, project_id:str) -> Union[HttpResponse, JsonResponse, None]:
        REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            proj = get_object_or_404(Project, pk=project_id)
            existing = proj.users.values_list('id', flat=True)
            users = User.objects.filter(created_by=request.user.creator_id())\
                                .exclude(type='client').exclude(id__in=existing)
            return render(request, 'projects/invite.html', {
                'project_id': project_id, 'users': users
            })
        except Exception as e:
            return default_undefined_exception(request, err=e,
                ref=REF, logger=logger)

    @method_decorator(login_required)
    @require_POST
    def invite_project_user_member(self, request: HttpRequest) -> Union[HttpResponse, JsonResponse, None]:
        REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            pu = ProjectUser.objects.create(
                project_id=request.POST['project_id'],
                user_id=request.POST['user_id'],
                invited_by=request.user.id
            )
            ActivityLog.objects.create(
                user_id=request.user.id,
                project_id=pu.project_id,
                log_type='Invite User',
                remark={'title': request.user.name}
            )
            return JsonResponse({'code':200,'status':'Success','message':'User invited successfully.'})
        except Exception as e:
            return default_undefined_exception(request, err=e,
                ref=REF, logger=logger)

    @method_decorator(login_required)
    @require_POST
    def destroy_project_user(self, request: HttpRequest, project_id: str, user_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            proj = get_object_or_404(Project, pk=project_id, created_by=request.user.owner_id())
            ProjectUser.objects.filter(project=proj, user_id=user_id).delete()
            messages.success(request, 'User successfully deleted!')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e,
                ref=REF, logger=logger)

    @method_decorator(login_required)
    def load_user(self, request: HttpRequest) -> Union[HttpResponse, JsonResponse, None]:
        REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            proj = get_object_or_404(Project, pk=request.GET.get('project_id'))
            html = render_to_string('projects/users.html', {'project': proj}, request)
            return JsonResponse({'success':True,'html':html})
        except Exception as e:
            return default_undefined_exception(request, err=e,
                ref=REF, logger=logger)

    @method_decorator(login_required)
    @permission_required_custom('create milestone', security_logger)
    def milestone(self, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            proj = get_object_or_404(Project, pk=project_id)
            return render(request, 'projects/milestone.html', {'project': proj, 'form': MilestoneForm()})
        except Exception as e:
            return default_undefined_exception(request, err=e,
                ref=REF, logger=logger)

    @method_decorator(login_required)
    @permission_required_custom('create milestone', security_logger)
    @require_POST
    def milestone_store(self, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            project = get_object_or_404(Project, pk=project_id)
            form = MilestoneForm(request.POST)
            if not form.is_valid():
                messages.error(request, form.errors.as_text())
                return redirect(get_redirect_url(request))
            m = form.save(commit=False)
            m.project = project
            m.save()
            ActivityLog.objects.create(
                user_id=request.user.id,
                project_id=project_id,
                log_type='Create Milestone',
                remark={'title': m.title}
            )
            messages.success(request, 'Milestone successfully created.')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e,
                ref=REF, logger=logger)

    
    @method_decorator(login_required)
    @permission_required_custom('edit milestone', security_logger)
    def milestone_edit(self, request:HttpRequest, milestone_id:str, project_id:str) -> Union[HttpResponse, Http404, JsonResponse, None]:
        REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            project = get_object_or_404(Project, pk=project_id)
            m = get_object_or_404(Milestone, pk=milestone_id,project=project)
            form = MilestoneForm(instance=m)
            return render(request, 'projects/milestone_edit.html', {'project': project, 'milestone': m, 'form': form})
        except Exception as e:
            return default_undefined_exception(request, err=e,
                ref=REF, logger=logger)

    @method_decorator(login_required)
    @permission_required_custom('edit milestone', security_logger)
    @require_POST
    def milestone_update(self, request:HttpRequest, milestone_id:str, project_id:str) -> Union[HttpResponse, Http404, JsonResponse, None]:
        REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            project = get_object_or_404(Project,pk=project_id)
            m = get_object_or_404(Milestone, pk=milestone_id,proejct=project)
            form = MilestoneForm(request.POST, instance=m)
            if not form.is_valid():
                messages.error(request, form.errors.as_text())
                return redirect(get_redirect_url(request))
            form.save()
            messages.success(request, 'Milestone updated successfully.')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e,
                ref=REF, logger=logger)

    @method_decorator(login_required)
    @permission_required_custom('delete milestone', security_logger)
    @require_POST
    def milestone_destroy(self, request:HttpRequest, milestone_id: str, project_id:str) -> Union[HttpResponse, Http404, JsonResponse, None]:
        REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            project = get_object_or_404(Project,pk=project_id)
            m = get_object_or_404(Milestone, pk=milestone_id,proejct=project)
            m.delete()
            messages.success(request, 'Milestone successfully deleted.')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e,
                ref=REF, logger=logger)

    @method_decorator(login_required)
    @permission_required_custom('view milestone', security_logger)
    def milestone_show(self, request:HttpRequest, milestone_id:str,project_id:str) -> Union[HttpResponse, Http404, JsonResponse, None]:
        REF=f'{self.__class__.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            p = get_object_or_404(Project,pk=project_id)
            m = get_object_or_404(Milestone, pk=milestone_id,proejct=p)
            return render(request, 'projects/milestone_show.html', {'milestone': m})
        except Exception as e:
            return default_undefined_exception(request, err=e,
                ref=REF, logger=logger)
            
    @classmethod
    def filter_project_view(cls, request: HttpRequest) -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'manage project')
            user = request.user
            cid = user.creator_id()
            if user.type == 'client':
                project_ids = Project.objects.filter(
                    client_id=user.id,
                    created_by=cid
                ).values_list('id', flat=True)
            else:
                project_ids = user.projects.values_list('id', flat=True)
            view = request.GET.get('view')
            sort = request.GET.get('sort')
            if request.is_ajax() and view and sort:
                field, direction = sort.split('-', 1)
                order = f'-{field}' if direction.lower() == 'desc' else field
                qs = Project.objects.filter(id__in=project_ids).order_by(order)
                keyword = request.GET.get('keyword')
                if keyword:
                    qs = qs.filter(
                        Q(project_name__startswith=keyword) |
                        Q(tags__icontains=keyword)
                    )
                statuses = request.GET.getlist('status')
                if statuses:
                    qs = qs.filter(status__in=statuses)
                projects = qs
                last = TaskStage.objects.filter(
                    created_by=cid
                ).order_by('-order').first()
                html = render_to_string(
                    f'projects/{view}.html',
                    {'projects': projects,
                     'user_projects': set(project_ids),
                     'last_task': last},
                    request
                )
                return JsonResponse({'success': True, 'html': html})
            return HttpResponseForbidden('Permission Denied.')
        except PermissionDenied as e:
            return default_permission_denial(
                request, err=e, ref=REF, logger=logger
            )
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=REF, logger=logger
            )

    @classmethod
    def gantt(cls, request: HttpRequest, project_id: str, duration: str = 'Week') -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'view grant chart')
            project = cls._get_project(project_id)
            tasks_data = []
            for task in project.tasks.all():
                tasks_data.append({
                    'id': f'task_{task.id}',
                    'name': task.name,
                    'start': task.start_date,
                    'end': task.end_date,
                    'custom_class': task.priority_color or '#ecf0f1',
                    'progress': task.task_progress(project)['percentage'].strip('%'),
                    'extra': {
                        'priority': task.priority.capitalize(),
                        'comments': task.comments().count(),
                        'duration': (
                            f"{Utility.get_date_formatted(task.start_date)} - "
                            f"{Utility.get_date_formatted(task.end_date)}"
                        )
                    }
                })
            return render(
                request, 'projects/gantt.html',
                {'project': project, 'tasks': tasks_data, 'duration': duration}
            )
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    @require_POST
    def gantt_post(cls, request: HttpRequest, project_id: str) -> JsonResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            project = cls._get_project(project_id)
            cls._set_auth(request, 'view project task')
            tid = request.POST.get('task_id', '').lstrip('task_')
            task = get_object_or_404(ProjectTask, id=tid, project=project)
            task.start_date = request.POST.get('start')
            task.end_date = request.POST.get('end')
            task.save()
            return JsonResponse({'is_success': True, 'message': 'Time Updated'})
        except PermissionDenied:
            return JsonResponse({'is_success': False, 'message': "You can't change Date!"}, status=400)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger, json={})

    @classmethod
    def bug_list(cls, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'manage bug report')
            project = cls._get_project(project_id)
            if project.created_by != request.user.creator_id():
                raise PermissionDenied()
            user = request.user
            if user.type == 'company':
                bugs = Bug.objects.filter(project=project)
            elif user.type == 'client':
                bugs = Bug.objects.filter(project=project)
            else:
                bugs = Bug.objects.filter(
                    project=project,
                    assign_to__icontains=str(user.id)
                )
            return render(request, 'projects/bug.html', {'project': project, 'bugs': bugs})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def bug_create(cls, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'create bug report')
            cid = request.user.creator_id()
            priority = Bug._meta.get_field('priority').choices
            status = BugStatus.objects.filter(created_by=cid)\
                                      .values_list('id', 'title')
            users = {
                pu.user_id: pu.user.name
                for pu in ProjectUser.objects.filter(project_id=project_id)
            }
            return render(request, 'projects/bug_create.html', {
                'status': status,
                'project_id': project_id,
                'priority': priority,
                'users': users
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def _get_next_bug_number(cls, creator_id: Any) -> int:
        latest = Bug.objects.filter(created_by=creator_id)\
                            .order_by('-bug_id')\
                            .first()
        return 1 if not latest else latest.bug_id + 1

    @classmethod
    @require_POST
    def bug_store(cls, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'create bug report')
            data = request.POST
            for f in ('title', 'priority', 'status', 'assign_to', 'start_date', 'due_date'):
                if not data.get(f):
                    messages.error(request, f'{f.replace("_", " ").capitalize()} is required.')
                    return redirect(reverse('task.bug', args=[project_id]))
            creator = request.user.creator_id()
            bug_props = ProjectController._set_bug(data)
            bug = Bug(
                **bug_props,
                bug_id=cls._get_next_bug_number(creator),
                project_id=project_id,
                created_by=creator
            )
            bug.save()
            ActivityLog.objects.create(
                user_id=request.user.id,
                project_id=project_id,
                log_type='Create Bug',
                remark={'title': bug.title}
            )
            messages.success(request, 'Bug successfully created.')
            return redirect(reverse('task.bug', args=[project_id]))
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def bug_edit(cls, request: HttpRequest, project_id: str, bug_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'edit bug report')
            bug = get_object_or_404(Bug, id=bug_id)
            cid = request.user.creator_id()
            priority = Bug._meta.get_field('priority').choices
            status = BugStatus.objects.filter(created_by=cid)\
                                      .values_list('id', 'title')
            users = {
                pu.user_id: pu.user.name
                for pu in ProjectUser.objects.filter(project_id=project_id)
            }
            return render(request, 'projects/bug_edit.html', {
                'status': status,
                'project_id': project_id,
                'priority': priority,
                'users': users,
                'bug': bug
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    @require_POST
    def bug_update(cls, request: HttpRequest, project_id: str, bug_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'edit bug report')
            data = request.POST
            for f in ('title', 'priority', 'status', 'assign_to', 'start_date', 'due_date'):
                if not data.get(f):
                    messages.error(request, f'{f.replace("_", " ").capitalize()} is required.')
                    return redirect(reverse('task.bug', args=[project_id]))
            bug = get_object_or_404(Bug, id=bug_id)
            bug_props = ProjectController._set_bug(data)
            for k, v in bug_props.items():
                setattr(bug, k, v)
            bug.save()
            messages.success(request, 'Bug successfully updated.')
            return redirect(reverse('task.bug', args=[project_id]))
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    @require_POST
    def bug_destroy(cls, request: HttpRequest, project_id: str, bug_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'delete bug report')
            bug = get_object_or_404(Bug, id=bug_id)
            bug.delete()
            messages.success(request, 'Bug successfully deleted.')
            return redirect(reverse('task.bug', args=[project_id]))
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def bug_kanban(cls, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'move bug report')
            project = cls._get_project(project_id)
            if project.created_by != request.user.creator_id():
                raise PermissionDenied()
            bug_status = BugStatus.objects.filter(
                created_by=request.user.creator_id()
            ).order_by('order')
            return render(request, 'projects/bug_kanban.html', {
                'project': project,
                'bug_status': bug_status
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    @require_POST
    def bug_kanban_order(cls, request: HttpRequest) -> JsonResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'move bug report')
            bug = get_object_or_404(Bug, id=request.POST.get('bug_id'))
            status = get_object_or_404(BugStatus, id=request.POST.get('status_id'))
            bug.status = status
            bug.save()
            for idx, bid in enumerate(request.POST.getlist('order')):
                if bid != 'null':
                    b = Bug.objects.filter(id=bid).first()
                    if b:
                        b.order = idx
                        b.status = status
                        b.save()
            return JsonResponse({'success': True})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger, json={})

    @classmethod
    def bug_show(cls, request: HttpRequest, project_id: str, bug_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'view bug report')
            bug = get_object_or_404(Bug, id=bug_id, project_id=project_id)
            return render(request, 'projects/bug_show.html', {'bug': bug})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)


    @classmethod
    @require_POST
    def bug_comment_store(cls, request: HttpRequest, project_id: str, bug_id: str) -> JsonResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            bug = get_object_or_404(Bug, id=bug_id, project_id=project_id)
            comment = BugComment.objects.create(
                bug=bug,
                comment=request.POST.get('comment', ''),
                created_by=request.user.creator_id(),
                user_type=request.user.type
            )
            comment.delete_url = reverse('bug.comment.destroy', args=[comment.id])
            data = {
                'id': str(comment.id),
                'comment': comment.comment,
                'user_type': comment.user_type,
                'created_at': comment.created_at.isoformat(),
                'delete_url': comment.delete_url
            }
            return JsonResponse({'is_success': True, 'message': 'Bug comment successfully created.', 'data': data})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger, json={})

    @classmethod
    @require_POST
    def bug_comment_destroy(cls, request: HttpRequest, comment_id: str) -> JsonResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            comment = get_object_or_404(BugComment, id=comment_id)
            comment.delete()
            return JsonResponse({'is_success': True})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger, json={})

    @classmethod
    @require_POST
    def bug_file_store(cls, request: HttpRequest, project_id: str, bug_id: str) -> JsonResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            bug = get_object_or_404(Bug, id=bug_id, project_id=project_id)
            file = request.FILES['file']
            name = file.name
            ext = name[name.rfind('.'):]
            ts = int(timezone.now().timestamp())
            filename = f'bugs/{bug_id}{ts}_{name}'
            default_storage.save(filename, file)
            bf = BugFile.objects.create(
                bug=bug,
                file=filename,
                name=name,
                extension=ext,
                file_size=file.size,
                created_by=request.user.creator_id(),
                user_type=request.user.type
            )
            bf.delete_url = reverse('bug.comment.file.destroy', args=[bf.id])
            data = {
                'id': str(bf.id),
                'name': bf.name,
                'file': getattr(bf.file, 'url', bf.file),
                'extension': bf.extension,
                'file_size': bf.file_size,
                'delete_url': bf.delete_url
            }
            return JsonResponse({'is_success': True, 'data': data})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger, json={})

    @classmethod
    @require_POST
    def bug_file_destroy(cls, request: HttpRequest, file_id: str) -> JsonResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            bf = get_object_or_404(BugFile, id=file_id)
            if default_storage.exists(bf.file.name):
                default_storage.delete(bf.file.name)
            bf.delete()
            return JsonResponse({'is_success': True})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger, json={})
        
    @classmethod
    def tracker(cls, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'view time tracker')
            project = cls._get_project(project_id)
            trackers = TimeTracker.objects.filter(project=project)
            return render(request, 'time_trackers/index.html', {'trackers': trackers})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def get_project_chart(cls, request: HttpRequest) -> JsonResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            params = {
                'created_by': request.user.creator_id(),
                'project_id': request.GET.get('project_id'),
                'duration': request.GET.get('duration')
            }
            arr_duration = {}
            if params['duration'] == 'week':
                prev = Utility.get_first_seventh_week_day(-1)
                for d in prev['datePeriod']:
                    arr_duration[d.strftime('%Y-%m-%d')] = d.strftime('%a')

            arr_task = {'label': [], 'color': []}
            stages = TaskStage.objects.filter(
                created_by=params['created_by']
            ).order_by('order')
            for _date, label in arr_duration.items():
                qs = ProjectTask.objects.filter(updated_at__date=_date)
                if params['project_id']:
                    qs = qs.filter(project_id=params['project_id'])
                counts = qs.values('stage_id').annotate(total=Count('id'))
                data_map = {c['stage_id']: c['total'] for c in counts}

                for stage in stages:
                    arr_task.setdefault(stage.id, []).append(data_map.get(stage.id, 0))
                arr_task['label'].append(label)
            arr_task['stages'] = {s.id: s.name for s in stages}
            return JsonResponse(arr_task)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger, json={})

    @classmethod
    def copy_project(cls, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'create project')
            project = cls._get_project(project_id)
            return render(request, 'projects/copy.html', {'project': project})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    @require_POST
    def copy_project_store(cls, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'create project')
            orig = cls._get_project(project_id)
            project_props = {}
            for k in ('project_name', 'status', 'project_image', 'client_id',
                      'description', 'start_date', 'end_date', 'estimated_hrs'):
                setattr(project_props, k, orig[k])
            dup = Project.objects.create(
                **project_props,
                created_by=request.user.creator_id()
            )
            if 'user' in request.POST:
                for pu in ProjectUser.objects.filter(project=orig):
                    ProjectUser.objects.create(project=dup, user_id=pu.user_id)
            else:
                ProjectUser.objects.create(project=dup, user_id=request.user.id)

            messages.success(request, 'Project duplicated successfully.')
            return redirect(get_redirect_url(request))
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def copy_link_setting_create(cls, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'view project')
            project = get_object_or_404(
                Project.objects.select_related('users'),
                id=project_id,
                users=request.user
            )
            result = json.loads(project.copy_link_setting or '{}')
            return render(request, 'projects/copy_link_setting.html', {
                'project': project,
                'projectID': project_id,
                'result': result
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    @require_POST
    def copy_link_setting(cls, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'edit project')
            project = get_object_or_404(
                Project.objects.select_related('users'),
                id=project_id,
                users=request.user
            )
            data = {}
            for field in (
                'basic_details','member','milestone','client',
                'progress','activity','attachment','bug_report',
                'expense','task','tracker_details','timesheet',
                'password_protected'
            ):
                data[field] = 'on' if request.POST.get(field) else 'off'
            if data['password_protected'] == 'on':
                project.password = base64.b64encode(
                    request.POST.get('password','').encode()
                ).decode()
            else:
                project.password = None
            project.copy_link_setting = json.dumps(data)
            project.save()
            messages.success(request, 'Copy Link Setting Saved Successfully!')
            return redirect(get_redirect_url(request))
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @classmethod
    def project_link(cls, request: HttpRequest, project_id: str, lang: str = '') -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            try:
                real_id = signing.loads(project_id)
            except signing.BadSignature:
                messages.error(request, 'Project Not Found.')
                return redirect(get_redirect_url(request))
            project = cls._get_project(real_id)
            user = request.user if request.user.is_authenticated else User.objects.get(id=project.created_by)
            data = cls._build_project_data(project, user)
            stages = TaskStage.objects.filter(project=project).order_by('order')
            trackers = TimeTracker.objects.filter(project=project, created_by=user.id)
            bugs = Bug.objects.filter(project=project)
            tasks = ProjectTask.objects.filter(project=project)
            lang_code = lang or getattr(user, 'lang', settings.DEFAULT_ADMIN_LANG)
            translation.activate(lang_code)
            session_key = f'copy_pass_true{project.id}'
            setting = json.loads(project.copy_link_setting or '{}')
            if setting.get('password_protected') == 'on':
                supplied = request.POST.get('password','')
                real_pw = base64.b64decode(project.password.encode()).decode()
                if request.session.get(session_key) == f'{project.password}-{project.id}':
                    pass
                elif supplied == real_pw:
                    request.session[session_key] = f'{project.password}-{project.id}'
                else:
                    return render(request, 'projects/copy_link_password.html', {'id': project.id})
            return render(request, 'projects/copy_link.html', {
                'data': data,
                'project': project,
                'project_data': data,
                'stages': stages,
                'trackers': trackers,
                'usr': user,
                'bugs': bugs,
                'tasks': tasks,
                'lang': lang_code
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)
        
    @classmethod
    def project_copy_link(cls, request: HttpRequest, project_id: str) -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'view project')
            project = cls._get_project(project_id)
            allowed = (
                (request.user.type == 'client' and project.client_id == request.user.id)
                or ProjectUser.objects.filter(project=project, user=request.user).exists()
            )
            if not allowed:
                return HttpResponseForbidden('Permission Denied.')
            signed_id = signing.dumps(str(project.id))
            share_url = request.build_absolute_uri(
                reverse('projects.link', args=[signed_id])
            )
            return render(request, 'projects/copylink.html', {
                'project': project,
                'copy_link': share_url,
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)
        
    @method_decorator(login_required)
    @classmethod
    @permission_required_custom('manage project', security_logger)
    def user_permission(cls, request:HttpRequest, project_id: str, uid: str) -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'manage project')
            project = cls._get_project(project_id)
            user = get_object_or_404(User, id=uid)
            if not ProjectUser.objects.filter(project=project, user=user).exists():
                messages.error(request, 'User is not a member of this project.')
                return redirect(reverse('projects.list'))
            ct = ContentType.objects.get_for_model(Project)
            all_perms = Permission.objects.filter(content_type=ct,
                                                  codename__in=('view_project','change_project','delete_project'))
            user_perms = set(user.user_permissions.filter(content_type=ct).values_list('codename', flat=True))
            return render(request, 'projects/user_permission.html', {
                'project': project,
                'member': user,
                'permissions': all_perms,
                'user_perms': user_perms,
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    @permission_required_custom('manage project', security_logger)
    def user_permission_store(cls, request:HttpRequest, project_id: str, uid: str) -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'manage project')
            project = cls._get_project(project_id)
            user = get_object_or_404(User, id=uid)
            if not ProjectUser.objects.filter(project=project, user=user).exists():
                messages.error(request, 'User is not a member of this project.')
                return redirect(reverse('projects.user.permission', args=[project_id, uid]))
            ct = ContentType.objects.get_for_model(Project)
            selected = set(request.POST.getlist('permissions'))
            perms = Permission.objects.filter(content_type=ct, codename__in=selected)
            user.user_permissions.remove(*Permission.objects.filter(content_type=ct))
            user.user_permissions.add(*perms)
            messages.success(request, 'Permissions updated.')
            return redirect(reverse('projects.user.permission', args=[project_id, uid]))
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)

    @method_decorator(login_required)
    @classmethod
    @permission_required_custom('view project', security_logger)
    def share_project(cls, request:HttpRequest, project_id:str, lang: str = 'en') -> Union[HttpResponse, JsonResponse, None]:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'view project')
            if lang:
                translation.activate(lang)
            cid = request.user.creator_id()
            projects = Project.objects.filter(created_by=cid) if not project_id else get_object_or_404(Project,pk=project_id)
            return render(request, 'projects/shareproject.html', {
                'projects': projects,
                'lang': lang,
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=REF, logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=REF, logger=logger)
    
    @classmethod
    @require_POST
    def store_project_task_stages(cls, request: HttpRequest, project_id: str, slug: str) -> HttpResponse:
        from ....Models.configs.pipeline import Pipeline
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'manage project')
            project = cls._get_project(project_id)
            pipeline = get_object_or_404(
                Pipeline,
                slug=slug,
                created_by=request.user.creator_id()
            )
            TaskStage.objects.filter(
                project=project,
                created_by=request.user.creator_id()
            ).delete()
            for order, stage in enumerate(pipeline.stages.order_by('order')):
                TaskStage.objects.create(
                    name=stage.title,
                    project=project,
                    order=order,
                    created_by=request.user.creator_id()
                )
            messages.success(request, 'Project task stages updated successfully.')
            return redirect(reverse('projects.list'))
        except PermissionDenied as e:
            return default_permission_denial(
                request, err=e, ref=REF, logger=logger
            )
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=REF, logger=logger
            )

    @classmethod
    def remove_user_from_project(cls, request: HttpRequest, project_id: str, user_id: str) -> HttpResponse:
        REF = f'{cls.__name__}::{inspect.currentframe().f_code.co_name}'
        try:
            cls._set_auth(request, 'edit project')
            project = cls._get_project(project_id)
            if project.created_by != request.user.creator_id():
                messages.error(request, 'Permission denied.')
                return redirect(get_redirect_url(request))
            ProjectUser.objects.filter(
                project=project,
                user_id=user_id
            ).delete()
            messages.success(request, 'User successfully removed from project.')
            return redirect(get_redirect_url(request))
        except PermissionDenied as e:
            return default_permission_denial(
                request, err=e, ref=REF, logger=logger
            )
        except Exception as e:
            return default_undefined_exception(
                request, err=e, ref=REF, logger=logger
            )