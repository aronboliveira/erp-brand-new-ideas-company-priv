from django.http import HttpRequest, HttpResponse
from django.shortcuts import render, redirect, get_object_or_404
from django.contrib import messages
from django.core.exceptions import PermissionDenied
from typing import Any, Dict
import os, time, inspect
from .._helpers.http import get_redirect_url
from .._traits.controller import Controller
from ....Models.activity.document_upload import DocumentUpload
from ....Models.individuals.role import Role
from ....Models.utils.utility import Utility
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception

import logging
logger = logging.getLogger(__name__)


class DocumentUploadController(Controller):
    def __init__(self, **kwargs):
        super().__init__(**kwargs)
        self.middleware(['auth'])

    @classmethod
    def _set_document_upload(cls, doc: DocumentUpload, data: Dict[str, Any], request_user, is_new: bool = False):
        for field in ('name', 'role', 'description'):
            setattr(doc, field, data.get(field))
        if is_new:
            doc.created_by = request_user.creator_id()
        return doc

    @classmethod
    def index(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('manage document'):
                raise PermissionDenied('Permission denied.')
            if request.user.type == 'company':
                docs = DocumentUpload.objects.filter(created_by=request.user.creator_id())
            else:
                roles = request.user.roles.first()
                docs = DocumentUpload.objects.filter(
                    role__in=[roles.id if roles else 0, 0],
                    created_by=request.user.creator_id()
                )
            return render(request, 'documentUpload/index.html', {'documents': docs})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def create(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('create document'):
                raise PermissionDenied('Permission denied.')
            qs = Role.objects.filter(created_by=request.user.creator_id())
            roles = {role.id: role.name for role in qs}
            roles = {0: 'All', **roles}
            return render(request, 'documentUpload/create.html', {'roles': roles})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def store(cls, request: HttpRequest) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('create document'):
                raise PermissionDenied('Permission denied.')
            data = request.POST
            if not data.get('name'):
                messages.error(request, 'Name is required.')
                return redirect(get_redirect_url(request))
            doc = cls._set_document_upload(DocumentUpload(), data, request.user, is_new=True)
            if file_obj := request.FILES.get('document'):
                fname = f"{int(time.time())}_{file_obj.name}"
                doc.document = fname
                info = Utility.upload_file(request, 'document', fname, 'uploads/documentUpload', [])
                if info.get('flag') == 0:
                    messages.error(request, info.get('msg', 'File upload error.'))
                    return redirect(get_redirect_url(request))
            doc.save()
            messages.success(request, 'Document successfully uploaded.')
            return redirect('document-upload.index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def show(cls, request: HttpRequest, id: int) -> HttpResponse:
        return redirect('document-upload.index')

    @classmethod
    def edit(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            doc = get_object_or_404(DocumentUpload, pk=id)
            if not request.user.has_perm('edit document'):
                raise PermissionDenied('Permission denied.')
            qs = Role.objects.filter(created_by=request.user.creator_id())
            roles = {role.id: role.name for role in qs}
            roles = {0: 'All', **roles}
            return render(request, 'documentUpload/edit.html', {'roles': roles, 'document_upload': doc})
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger, json={'error':'Permission denied.'})
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def update(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('edit document'):
                raise PermissionDenied('Permission denied.')
            data = request.POST
            if not data.get('name') or len(data.get('name')) > 20:
                messages.error(request, 'Name is required and must be at most 20 characters.')
                return redirect(get_redirect_url(request))
            doc = get_object_or_404(DocumentUpload, pk=id)
            cls._set_document_upload(doc, data, request.user)
            if file_obj := request.FILES.get('document'):
                base, ext = os.path.splitext(file_obj.name)
                fname = f"{base}_{int(time.time())}{ext}"
                path = os.path.join('uploads/documentUpload/', fname)
                if os.path.exists(path):
                    os.remove(path)
                info = Utility.upload_file(request, 'document', fname, 'uploads/documentUpload/', [])
                if info.get('flag') != 1:
                    messages.error(request, info.get('msg', 'File upload error.'))
                    return redirect(get_redirect_url(request))
            doc.save()
            messages.success(request, 'Document successfully uploaded.')
            return redirect('document-upload.index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)

    @classmethod
    def destroy(cls, request: HttpRequest, id: int) -> HttpResponse:
        CN = cls.__name__; MN = inspect.currentframe().f_code.co_name
        try:
            if not request.user.has_perm('delete document'):
                raise PermissionDenied('Permission denied.')
            doc = get_object_or_404(DocumentUpload, pk=id)
            if doc.created_by != request.user.creator_id():
                raise PermissionDenied('Permission denied.')
            doc.delete()
            messages.success(request, 'Document successfully deleted.')
            return redirect('document-upload.index')
        except PermissionDenied as e:
            return default_permission_denial(request, err=e, ref=f'{CN}::{MN}', logger=logger)
        except Exception as e:
            return default_undefined_exception(request, err=e, ref=f'{CN}::{MN}', logger=logger)