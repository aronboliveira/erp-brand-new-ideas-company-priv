import logging
import inspect
from django.contrib import messages
from django.http import HttpRequest, HttpResponse
from django.shortcuts import render, redirect, get_object_or_404
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller
from ....Models.shapes.custom_question import CustomQuestion

logger = logging.getLogger(__name__)

class CustomQuestionController(Controller):

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('manage custom question'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            qs = CustomQuestion.objects.filter(created_by=request.user.creator_id())
            return render(request, 'customQuestion/index.html', {'questions': qs})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            return render(request, 'customQuestion/create.html', {'is_required': CustomQuestion.is_required})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def store(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('create custom question'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            data = request.POST
            if not data.get('question'):
                messages.error(request, 'Validation Error: question required.')
                return redirect(get_redirect_url(request))
            cq = CustomQuestion()
            cls._set_custom_question(cq, data, request.user.creator_id())
            messages.success(request, 'Question successfully created.')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def show(cls, request: HttpRequest, custom_question_id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            return redirect('custom_question_index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def edit(cls, request: HttpRequest, custom_question_id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            cq = get_object_or_404(CustomQuestion, pk=custom_question_id)
            return render(request, 'customQuestion/edit.html', {
                'custom_question': cq,
                'is_required': CustomQuestion.is_required
            })
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def update(cls, request: HttpRequest, custom_question_id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit custom question'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            data = request.POST
            if not data.get('question'):
                messages.error(request, 'Validation Error: question required.')
                return redirect(get_redirect_url(request))
            cq = get_object_or_404(CustomQuestion, pk=custom_question_id)
            cls._set_custom_question(cq, data, None)
            messages.success(request, 'Question successfully updated.')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def destroy(cls, request: HttpRequest, custom_question_id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('delete custom question'):
                return default_permission_denial(request, err=None, ref=f'{CN}::{MN}', logger=logger)
            cq = get_object_or_404(CustomQuestion, pk=custom_question_id)
            cq.delete()
            messages.success(request, 'Question successfully deleted.')
            return redirect(get_redirect_url(request))
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def _set_custom_question(cls, obj: CustomQuestion, data, user_id=None):
        obj.question = data.get('question')
        obj.is_required = data.get('is_required')
        if user_id is not None:
            obj.created_by = user_id
        obj.save()
