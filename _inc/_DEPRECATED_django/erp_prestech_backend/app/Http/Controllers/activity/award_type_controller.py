import inspect
import logging
from django.contrib import messages
from django.contrib.auth.decorators import login_required
from django.http import HttpResponse, HttpResponseRedirect, HttpRequest
from django.shortcuts import get_object_or_404, redirect, render
from django.utils.decorators import method_decorator
from django.views.decorators.http import require_http_methods
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from ....Models.activity.award_type import AwardType
from .._traits.controller import Controller
logger = logging.getLogger(__name__)
class AwardTypeController(Controller):

    @method_decorator(login_required)
    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        return (
            redirect(get_redirect_url(request))
            if not request.user.has_perm('manage award type')
            else render(
                request,
                'awardtype/index.html',
                {'awardtypes': AwardType.objects.filter(created_by=request.user.creator_id)},
            )
        )

    @method_decorator(login_required)
    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        return (
            redirect(get_redirect_url(request))
            if not request.user.has_perm('create award type')
            else render(request, 'awardtype/create.html')
        )

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(['POST'])
    def store(cls, request) -> HttpResponseRedirect:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name

        if not request.user.has_perm('create award type'):
            return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)

        name = request.POST.get('name')
        if not name or len(name) > 20:
            messages.error(request, 'Name is required and must be at most 20 characters.')
            return redirect(get_redirect_url(request))

        try:
            AwardType.objects.create(name=name, created_by=request.user.creator_id)
            messages.success(request, 'AwardType successfully created.')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

        return redirect('awardtype.index')

    @method_decorator(login_required)
    @classmethod
    def show(cls, request, id: int) -> HttpResponseRedirect:
        return redirect('awardtype.index')  # TODO: consider handling not-found or permissions

    @method_decorator(login_required)
    @classmethod
    def edit(cls, request, id: int) -> HttpResponse:
        awardtype = get_object_or_404(AwardType, id=id)
        return (
            redirect(get_redirect_url(request))
            if not request.user.has_perm('edit award type') or awardtype.created_by != request.user.creator_id
            else render(request, 'awardtype/edit.html', {'awardtype': awardtype})
        )

    @method_decorator(login_required)
    @classmethod
    @require_http_methods(['POST'])
    def update(cls, request, id: int) -> HttpResponseRedirect:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name

        awardtype = get_object_or_404(AwardType, id=id)
        if not request.user.has_perm('edit award type') or awardtype.created_by != request.user.creator_id:
            return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)

        name = request.POST.get('name')
        if not name or len(name) > 20:
            messages.error(request, 'Name is required and must be at most 20 characters.')
            return redirect(get_redirect_url(request))

        try:
            awardtype.name = name
            awardtype.save()
            messages.success(request, 'AwardType successfully updated.')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

        return redirect('awardtype.index')

    @method_decorator(login_required)
    @classmethod
    def destroy(cls, request, id: int) -> HttpResponseRedirect:
        CN = cls.__name__
        MN = inspect.currentframe().f_code.co_name

        awardtype = get_object_or_404(AwardType, id=id)
        if not request.user.has_perm('delete award type') or awardtype.created_by != request.user.creator_id:
            return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)

        try:
            awardtype.delete()
            messages.success(request, 'AwardType successfully deleted.')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

        return redirect('awardtype.index')
