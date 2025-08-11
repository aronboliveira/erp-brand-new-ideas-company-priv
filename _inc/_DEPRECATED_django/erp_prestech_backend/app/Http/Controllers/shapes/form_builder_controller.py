import logging
import inspect
from typing import Any
from uuid import UUID
from django.core.exceptions import PermissionDenied
from django.contrib import messages
from django.shortcuts import render, redirect, get_object_or_404
from django.http import HttpRequest
from ....Models.shapes.form_builder import FormBuilder
from ....Models.shapes.form_field import FormField
from ....Models.shapes.form_field_response import FormFieldResponse
from ....Models.shapes.form_response import FormResponse
from ....Models.individuals.user import User
from .._helpers.error_handlers import default_permission_denial, default_undefined_exception
from .._traits.controller import Controller

logger = logging.getLogger(__name__)

class FormBuilderController(Controller):
    MFN = staticmethod(lambda: inspect.currentframe().f_back.f_code.co_name)

    @classmethod
    def _set_form_builder(cls, fb: FormBuilder, **attrs):
        for k, v in attrs.items():
            setattr(fb, k, v)

    @classmethod
    def index(cls, request: HttpRequest) -> Any:
        user = request.user
        if not user.is_authenticated:
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger,
                                            redirect_path='login')
        if not user.has_perm('manage_form_builder'):
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)
        try:
            forms = FormBuilder.objects.filter(created_by=user.creator_id)
            return render(request, 'form_builder/index.html', {'forms': forms})
        except Exception as e:
            return default_undefined_exception(request, err=e,
                                               ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)

    @classmethod
    def create(cls, request: HttpRequest) -> Any:
        user = request.user
        if not user.is_authenticated:
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger,
                                            redirect_path='login')
        try:
            return render(request, 'form_builder/create.html')
        except Exception as e:
            return default_undefined_exception(request, err=e,
                                               ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)

    @classmethod
    def store(cls, request: HttpRequest) -> Any:
        user = request.user
        if not user.is_authenticated:
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger,
                                            redirect_path='login')
        if not user.has_perm('create_form_builder'):
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)
        try:
            if request.method == 'POST':
                name = request.POST.get('name','').strip()
                if not name:
                    messages.error(request, 'Name is required.')
                    return redirect('form_builder_index')
                fb = FormBuilder()
                code = f"{User.objects.make_random_password(8)}{User.objects.make_random_password(8)}"
                cls._set_form_builder(fb,
                    name=name,
                    code=code,
                    is_active=request.POST.get('is_active')=='1',
                    created_by=user.creator_id
                )
                fb.save()
                messages.success(request, 'Form successfully created.')
            return redirect('form_builder_index')
        except Exception as e:
            return default_undefined_exception(request, err=e,
                                               ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)

    @classmethod
    def show(cls, request: HttpRequest, form_builder_id: int) -> Any:
        user = request.user
        if not user.is_authenticated:
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger,
                                            redirect_path='login')
        if not user.has_perm('manage_form_field'):
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)
        try:
            fb = get_object_or_404(FormBuilder, pk=form_builder_id)
            if fb.created_by != user.creator_id:
                return redirect('somewhere_else')
            return render(request, 'form_builder/show.html', {'form_builder': fb})
        except Exception as e:
            return default_undefined_exception(request, err=e,
                                               ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)

    @classmethod
    def edit(cls, request: HttpRequest, form_builder_id: int) -> Any:
        user = request.user
        if not user.is_authenticated:
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger,
                                            redirect_path='login')
        if not user.has_perm('edit_form_builder'):
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)
        try:
            fb = get_object_or_404(FormBuilder, pk=form_builder_id)
            if fb.created_by != user.creator_id:
                return default_permission_denial(request, err=PermissionDenied(),
                                                ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)
            return render(request, 'form_builder/edit.html', {'form_builder': fb})
        except Exception as e:
            return default_undefined_exception(request, err=e,
                                               ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)

    @classmethod
    def update(cls, request: HttpRequest, form_builder_id: int) -> Any:
        user = request.user
        if not user.is_authenticated:
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger,
                                            redirect_path='login')
        if not user.has_perm('edit_form_builder'):
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)
        try:
            if request.method == 'POST':
                fb = get_object_or_404(FormBuilder, pk=form_builder_id)
                if fb.created_by != user.creator_id:
                    return default_permission_denial(request, err=PermissionDenied(),
                                                    ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)
                name = request.POST.get('name','').strip()
                if not name:
                    messages.error(request, 'Name is required.')
                    return redirect('form_builder_index')
                cls._set_form_builder(fb,
                    name=name,
                    is_active=request.POST.get('is_active')=='1',
                    is_lead_active=False
                )
                fb.save()
                messages.success(request, 'Form successfully updated.')
            return redirect('form_builder_index')
        except Exception as e:
            return default_undefined_exception(request, err=e,
                                               ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)

    @classmethod
    def destroy(cls, request: HttpRequest, form_builder_id: int) -> Any:
        user = request.user
        if not user.is_authenticated:
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger,
                                            redirect_path='login')
        if not user.has_perm('delete_form_builder'):
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)
        try:
            fb = get_object_or_404(FormBuilder, pk=form_builder_id)
            if fb.created_by != user.owner_id:
                return default_permission_denial(request, err=PermissionDenied(),
                                                ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)
            FormField.objects.filter(form_id=fb.id).delete()
            FormFieldResponse.objects.filter(form_id=fb.id).delete()
            FormResponse.objects.filter(form_id=fb.id).delete()
            fb.delete()
            messages.success(request, 'Form successfully deleted!')
            return redirect('form_builder_index')
        except Exception as e:
            return default_undefined_exception(request, err=e,
                                               ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)

    @classmethod
    def field_create(cls, request: HttpRequest, form_id: int) -> Any:
        user = request.user
        if not user.is_authenticated:
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger,
                                            redirect_path='login')
        if not user.has_perm('create_form_field'):
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)
        try:
            fb = get_object_or_404(FormBuilder, pk=form_id)
            if fb.created_by != user.creator_id:
                return default_permission_denial(request, err=PermissionDenied(),
                                                ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)
            return render(request, 'form_builder/field_create.html', {
                'types': FormBuilder.FIELD_TYPES,
                'formbuilder': fb
            })
        except Exception as e:
            return default_undefined_exception(request, err=e,
                                               ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)

    @classmethod
    def field_show(cls, request: HttpRequest, form_id: int, field_uuid: UUID) -> Any:
        try:
            Controller.authorize(request, 'view_form_field')
            fb = get_object_or_404(FormBuilder, pk=form_id)
            if fb.created_by != request.user.creator_id:
                raise PermissionDenied()
            ff = get_object_or_404(FormField, uuid=field_uuid, form_id=fb.id)
            return render(request, 'form_builder/field_show.html', {
                'form_field': ff, 'formbuilder': fb
            })
        except PermissionDenied as e:
            return default_permission_denial(request, err=e,
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger,
                                            json={})
        except Exception as e:
            return default_undefined_exception(request, err=e,
                                               ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)

    @classmethod
    def field_store(cls, request: HttpRequest, form_id: int) -> Any:
        user = request.user
        if not user.is_authenticated:
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger,
                                            redirect_path='login')
        if not user.has_perm('create_form_field'):
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)
        try:
            if request.method == 'POST':
                fb = get_object_or_404(FormBuilder, pk=form_id)
                if fb.created_by != user.creator_id:
                    return default_permission_denial(request, err=PermissionDenied(),
                                                    ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)
                names = request.POST.getlist('name')
                types = request.POST.getlist('type')
                FormField.objects.bulk_create([
                    FormField(form_id=fb.id, name=n.strip(), type=t, created_by=user.creator_id)
                    for n, t in zip(names, types) if n.strip()
                ])
                messages.success(request, 'Field successfully created.')
            return redirect('somewhere_else')
        except Exception as e:
            return default_undefined_exception(request, err=e,
                                               ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)

    @classmethod
    def field_edit(cls, request: HttpRequest, form_id: int, field_id: int) -> Any:
        user = request.user
        if not user.is_authenticated:
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger,
                                            redirect_path='login')
        if not user.has_perm('edit_form_field'):
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)
        try:
            fb = get_object_or_404(FormBuilder, pk=form_id)
            if fb.created_by != user.creator_id:
                return default_permission_denial(request, err=PermissionDenied(),
                                                ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)
            ff = get_object_or_404(FormField, pk=field_id)
            return render(request, 'form_builder/field_edit.html', {
                'form_field': ff,
                'types': FormBuilder.FIELD_TYPES,
                'form': fb
            })
        except Exception as e:
            return default_undefined_exception(request, err=e,
                                               ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)

    @classmethod
    def field_update(cls, request: HttpRequest, form_id: int, field_id: int) -> Any:
        user = request.user
        if not user.is_authenticated:
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger,
                                            redirect_path='login')
        if not user.has_perm('edit_form_field'):
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)
        try:
            if request.method == 'POST':
                fb = get_object_or_404(FormBuilder, pk=form_id)
                if fb.created_by != user.creator_id:
                    return default_permission_denial(request, err=PermissionDenied(),
                                                    ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)
                name = request.POST.get('name','').strip()
                if not name:
                    messages.error(request, 'Name is required.')
                    return redirect('somewhere_else')
                ff = get_object_or_404(FormField, pk=field_id)
                ff.name = name
                ff.type = request.POST.get('type', ff.type)
                ff.save()
                messages.success(request, 'Form successfully updated.')
            return redirect('somewhere_else')
        except Exception as e:
            return default_undefined_exception(request, err=e,
                                               ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)

    @classmethod
    def field_destroy(cls, request: HttpRequest, form_id: int, field_id: int) -> Any:
        user = request.user
        if not user.is_authenticated:
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger,
                                            redirect_path='login')
        if not user.has_perm('delete_form_field'):
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)
        try:
            fb = get_object_or_404(FormBuilder, pk=form_id)
            if fb.created_by != user.creator_id:
                return default_permission_denial(request, err=PermissionDenied(),
                                                ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)
            if (resp := (
                    FormFieldResponse.objects.filter(subject_id=field_id) |
                    FormFieldResponse.objects.filter(name_id=field_id) |
                    FormFieldResponse.objects.filter(email_id=field_id)
                ).first()):
                messages.error(
                    request,
                    f"Please remove this field from Convert Lead — response ID {resp.id} exists."
                )
                return redirect('somewhere_else')
            ff = FormField.objects.filter(pk=field_id).first()
            if not ff:
                messages.error(request, 'Field not found.')
                return redirect('somewhere_else')
            ff.delete()
            messages.success(request, 'Form successfully deleted.')
            return redirect('somewhere_else')
        except Exception as e:
            return default_undefined_exception(request, err=e,
                                               ref=f"{cls.__name__}::{cls.MFN()}", logger=logger)

    @classmethod
    def view_response(cls, request: HttpRequest, form_id: int) -> Any:
        user = request.user
        if not user.is_authenticated:
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.CN}::{cls.MFN()}", logger=logger,
                                            redirect_path='login')
        if not user.has_perm('view_form_response'):
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.CN}::{cls.MFN()}", logger=logger)
        try:
            fb = FormBuilder.objects.filter(pk=form_id).first()
            if not fb:
                messages.error(request, 'Form not found.')
                return redirect('somewhere_else')
            if fb.created_by != user.creator_id:
                return redirect('somewhere_else')
            return render(request, 'form_builder/response.html', {'form': fb})
        except Exception as e:
            return default_undefined_exception(request, err=e,
                                               ref=f"{cls.CN}::{cls.MFN()}", logger=logger)

    @classmethod
    def response_detail(cls, request: HttpRequest, response_id: int) -> Any:
        user = request.user
        if not user.is_authenticated:
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.CN}::{cls.MFN()}", logger=logger,
                                            redirect_path='login')
        if not user.has_perm('view_form_response'):
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.CN}::{cls.MFN()}", logger=logger)
        try:
            fr = get_object_or_404(FormResponse, pk=response_id)
            fb = get_object_or_404(FormBuilder, pk=fr.form_id)
            if fb.created_by != user.creator_id:
                messages.error(request, 'Permission Denied.')
                return redirect('somewhere_else')
            try:
                data = fr.get_response_dict()
            except Exception:
                data = {}
            return render(request, 'form_builder/response_detail.html', {'response': data})
        except Exception as e:
            return default_undefined_exception(request, err=e,
                                               ref=f"{cls.CN}::{cls.MFN()}", logger=logger)

    @classmethod
    def form_view(cls, request: HttpRequest, code: str) -> Any:
        if not code:
            messages.error(request, 'Permission Denied.')
            return redirect('login')
        try:
            fb = FormBuilder.objects.filter(code__iexact=code).first()
            if not fb:
                messages.error(request, 'Form not found, please contact admin.')
                return redirect('login')
            ctx = {'code': code, 'form': fb}
            if fb.is_active:
                ctx['objFields'] = fb.form_field.all()
            return render(request, 'form_builder/form_view.html', ctx)
        except Exception as e:
            return default_undefined_exception(request, err=e,
                                               ref=f"{cls.CN}::{cls.MFN()}", logger=logger)

    @classmethod
    def form_view_store(cls, request: HttpRequest) -> Any:
        if request.method != 'POST':
            messages.error(request, 'Permission Denied.')
            return redirect('login')
        try:
            code = request.POST.get('code','').strip()
            fb = FormBuilder.objects.filter(code__iexact=code).first()
            if not fb:
                messages.error(request, 'Something went wrong.')
                return redirect('login')
            resp = {}
            for key, val in request.POST.items():
                if key.startswith('field[') and val:
                    try:
                        fid = int(key[6:-1])
                        field = FormField.objects.filter(pk=fid).first()
                        if field:
                            resp[field.name] = val
                    except ValueError:
                        continue
            from ....Models.activity.lead_stage import LeadStage
            from ....Models.activity.lead import Lead
            FormResponse.objects.create(form_id=fb.id, response=resp)
            if fb.is_lead_active and (bind := fb.field_response):
                email = request.POST.get(f'field[{bind.email_id}]','').strip()
                if email:
                    if User.objects.filter(email__iexact=email).exists():
                        messages.error(request, 'Email already exists in our record.')
                        return redirect(request.META.get('HTTP_REFERER','login'))
                    stage = LeadStage.objects.filter(
                        pipeline_id=bind.pipeline_id,
                        created_by=fb.created_by
                    ).first()
                    if stage:
                        lead = Lead()
                        cls._set_form_builder(lead,
                            name=request.POST.get(f'field[{bind.name_id}]','No Name'),
                            email=email,
                            subject=request.POST.get(f'field[{bind.subject_id}]',''),
                            user_id=bind.user_id,
                            pipeline_id=bind.pipeline_id,
                            stage_id=stage.id,
                            created_by=fb.created_by,
                            date='2025-01-01'
                        )
                        lead.save()
                        from ....Models.activity.user_lead import UserLead
                        for uid in (fb.created_by, bind.user_id):
                            UserLead.objects.create(user_id=uid, lead_id=lead.id)
            messages.success(request, 'Data submitted successfully.')
            return redirect(request.META.get('HTTP_REFERER','login'))
        except Exception as e:
            return default_undefined_exception(request, err=e,
                                               ref=f"{cls.CN}::{cls.MFN()}", logger=logger)

    @classmethod
    def form_field_bind(cls, request: HttpRequest, form_id: int) -> Any:
        user = request.user
        if not user.is_authenticated:
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.CN}::{cls.MFN()}", logger=logger,
                                            redirect_path='login')
        if getattr(user, 'type', None) != 'company':
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.CN}::{cls.MFN()}", logger=logger)
        try:
            from ....Models.configs.pipeline import Pipeline
            fb = get_object_or_404(FormBuilder, pk=form_id)
            if fb.created_by != user.creator_id:
                return default_permission_denial(request, err=PermissionDenied(),
                                                ref=f"{cls.CN}::{cls.MFN()}", logger=logger)
            types = {f.id: f.name for f in fb.form_field.all()}
            bind = FormFieldResponse.objects.filter(form_id=form_id).first()
            users = {u.id: u.name for u in User.objects.filter(created_by=user.creator_id).exclude(type='client')}
            pipelines = {p.id: p.name for p in Pipeline.objects.filter(created_by=user.creator_id)}
            return render(request, 'form_builder/form_field.html', {
                'form': fb, 'types': types, 'formField': bind,
                'users': users, 'pipelines': pipelines
            })
        except Exception as e:
            return default_undefined_exception(request, err=e,
                                               ref=f"{cls.CN}::{cls.MFN()}", logger=logger)

    @classmethod
    def bind_store(cls, request: HttpRequest, form_id: int) -> Any:
        user = request.user
        if not user.is_authenticated:
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.CN}::{cls.MFN()}", logger=logger,
                                            redirect_path='login')
        if getattr(user, 'type', None) != 'company':
            return default_permission_denial(request, err=PermissionDenied(),
                                            ref=f"{cls.CN}::{cls.MFN()}", logger=logger)
        try:
            fb = get_object_or_404(FormBuilder, pk=form_id)
            fb.is_lead_active = request.POST.get('is_lead_active')=='1'
            fb.save()
            if fb.created_by != user.creator_id:
                return default_permission_denial(request, err=PermissionDenied(),
                                                ref=f"{cls.CN}::{cls.MFN()}", logger=logger)
            if fb.is_lead_active:
                keys = ('subject_id','name_id','email_id','user_id','pipeline_id')
                vals = {k: request.POST.get(k,'').strip() for k in keys}
                if not all(vals.values()):
                    fb.is_lead_active = False
                    fb.save()
                    messages.error(request, 'All fields are required.')
                    return redirect('somewhere_else')
                bind = FormFieldResponse.objects.filter(form_id=fb.id).first()
                if bind:
                    for attr, v in vals.items():
                        setattr(bind, attr, v)
                    bind.save()
                else:
                    FormFieldResponse.objects.create(form_id=fb.id, **vals)
            messages.success(request, 'Setting saved successfully!')
            return redirect('somewhere_else')
        except Exception as e:
            return default_undefined_exception(request, err=e,
                                               ref=f"{cls.CN}::{cls.MFN()}", logger=logger)
