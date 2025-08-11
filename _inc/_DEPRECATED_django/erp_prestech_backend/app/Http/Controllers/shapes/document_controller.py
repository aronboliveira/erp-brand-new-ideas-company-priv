import logging
import inspect
from django.core.exceptions import PermissionDenied
from django.http import HttpRequest, HttpResponse
from django.shortcuts import render, redirect, get_object_or_404
from django.contrib import messages
from typing import Any, Dict
from .._helpers.http import get_redirect_url
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller
from ....Models.shapes.document import Document

logger = logging.getLogger(__name__)


class DocumentController(Controller):
    def __init__(self, **kwargs):
        super().__init__(**kwargs)
        self.middleware(['auth'])

    @classmethod
    def _set_document(cls, doc: Document, data: Dict[str, Any], request_user, is_new: bool = False) -> Document:
        for field in ('name', 'is_required'):
            setattr(doc, field, data.get(field))
        if is_new:
            doc.created_by = request_user.creator_id()
        return doc

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('manage document type') or (_ for _ in ()).throw(
                PermissionDenied('Permission denied.')
            )
            documents = Document.objects.filter(created_by=request.user.creator_id())
            return render(request, 'document/index.html', {'documents': documents})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('create document type') or (_ for _ in ()).throw(
                PermissionDenied('Permission denied.')
            )
            return render(request, 'document/create.html')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger, json='Permission denied.')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger, json='An error occurred.')

    @classmethod
    def store(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('create document type') or (_ for _ in ()).throw(
                PermissionDenied('Permission denied.')
            )
            data = request.POST
            name = data.get('name', '')
            for cond, msg in [(not name, 'Name is required.'), (len(name) > 20, 'Name must be at most 20 characters.')]:
                if cond:
                    messages.error(request, msg)
                    return redirect(get_redirect_url(request))
            document = cls._set_document(Document(), data, request.user, is_new=True)
            document.save()
            messages.success(request, 'Document type successfully created.')
            return redirect('document.index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def show(cls, request: HttpRequest, document_id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            return redirect('document.index')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def edit(cls, request: HttpRequest, document_id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('edit document type') or (_ for _ in ()).throw(
                PermissionDenied('Permission denied.')
            )
            document = get_object_or_404(Document, pk=document_id)
            document.created_by == request.user.creator_id() or (_ for _ in ()).throw(
                PermissionDenied('Permission denied.')
            )
            return render(request, 'document/edit.html', {'document': document})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger, json='Permission denied.')
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger, json='An error occurred.')

    @classmethod
    def update(cls, request: HttpRequest, document_id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('edit document type') or (_ for _ in ()).throw(
                PermissionDenied('Permission denied.')
            )
            document = get_object_or_404(Document, pk=document_id)
            document.created_by == request.user.creator_id() or (_ for _ in ()).throw(
                PermissionDenied('Permission denied.')
            )
            data = request.POST
            name = data.get('name', '')
            for cond, msg in [(not name, 'Name is required.'), (len(name) > 20, 'Name must be at most 20 characters.')]:
                if cond:
                    messages.error(request, msg)
                    return redirect(get_redirect_url(request))
            cls._set_document(document, data, request.user).save()
            messages.success(request, 'Document type successfully updated.')
            return redirect('document.index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def destroy(cls, request: HttpRequest, document_id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            request.user.has_perm('delete document type') or (_ for _ in ()).throw(
                PermissionDenied('Permission denied.')
            )
            document = get_object_or_404(Document, pk=document_id)
            document.created_by == request.user.creator_id() or (_ for _ in ()).throw(
                PermissionDenied('Permission denied.')
            )
            document.delete()
            messages.success(request, 'Document type successfully deleted.')
            return redirect('document.index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)
