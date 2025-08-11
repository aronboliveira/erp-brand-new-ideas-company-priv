@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        YieldingConstants,
        StacksConstants,
        UsersConstants
    };
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_SCR_PG)
    <script>
        const mailPatch = {
        ar:{email_template_toggle_failed:'فشل تحديث حالة قالب البريد الإلكتروني.'},
        da:{email_template_toggle_failed:'Kunne ikke opdatere skabelonstatus.'},
        de:{email_template_toggle_failed:'Aktualisieren des E‑Mail‑Vorlagenstatus fehlgeschlagen.'},
        en:{email_template_toggle_failed:'Failed to update e‑mail template status.'},
        es:{email_template_toggle_failed:'Error al actualizar el estado de la plantilla de correo.'},
        fr:{email_template_toggle_failed:'Échec de la mise à jour du statut du modèle d’e‑mail.'},
        he:{email_template_toggle_failed:'עדכון סטטוס תבנית הדוא״ל נכשל.'},
        it:{email_template_toggle_failed:'Impossibile aggiornare lo stato del modello e‑mail.'},
        ja:{email_template_toggle_failed:'メールテンプレートの状態を更新できませんでした。'},
        nl:{email_template_toggle_failed:'Kon status van e‑mailsjabloon niet bijwerken.'},
        pl:{email_template_toggle_failed:'Nie udało się zaktualizować statusu szablonu e‑mail.'},
        pt:{email_template_toggle_failed:'Falha ao atualizar o estado do modelo de e‑mail.'},
        'pt-br':{email_template_toggle_failed:'Falha ao atualizar o status do modelo de e‑mail.'},
        ru:{email_template_toggle_failed:'Не удалось обновить статус шаблона письма.'},
        tr:{email_template_toggle_failed:'E‑posta şablonu durumu güncellenemedi.'},
        zh:{email_template_toggle_failed:'更新电子邮件模板状态失败。'}
        };
        window.translations = Object.keys(window.translations||{}).length
        ? Object.keys(mailPatch).reduce((a,l)=>{a[l]={...(a[l]||{}),...mailPatch[l]};return a;},window.translations)
        : mailPatch;
    </script>
    <script defer>
        (() => {
        const CSRF  = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const lang  = (() => {
            const l = (sessionStorage.getItem('erp-np-lang') || document.documentElement.lang || 'en')
                        .toLowerCase().replace(/_/g,'-');
            return l==='pt-br'?l:l.slice(0,2);
        })();
        const t   = k => window.translations?.[lang]?.[k] || window.translations?.en?.[k] || '# ERROR';
        const pop = (msg,type='error') => window.show_toastr
            ? window.show_toastr(type, msg, type)
            : alert(msg);
        
        document.addEventListener('click', e => {
            const cb = e.target.closest('.email-template-checkbox');
            if (!cb) return;
        
            const url = cb.dataset.url;
            const val = cb.value ?? '';
            if (!url) { pop(t('email_template_toggle_failed')); return; }
        
            fetch(url, {
            method : 'PUT',
            headers: {
                'X-CSRF-TOKEN':''+CSRF,
                'Content-Type' : 'application/json',
                'Accept'       : 'application/json'
            },
            body: JSON.stringify({ status: val })
            })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(res => {
            if (!res?.is_success) return Promise.reject();
            pop(res.success ?? 'OK', 'success');
        
            cb.value = (val === '1' ? '0' : '1');
            })
            .catch(() => pop(t('email_template_toggle_failed')));
        });
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    @if($user?->{UsersConstants::COL_TP} == PermissionsConstants::SA ||
        $user?->{UsersConstants::COL_TP} == PermissionsConstants::CPN)
        {{__('Email Notification')}}
    @else
        {{__('Email Templates')}}
    @endif
@endsection
@section(YieldingConstants::ADM_PG_TTL)
    <div class="d-inline-block">
        @if($user?->{UsersConstants::COL_TP} == PermissionsConstants::SA ||
                                        $user?->{UsersConstants::COL_TP} == PermissionsConstants::CPN)
            <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Email Notification')}}</h5>
        @else
            <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Email Templates')}}</h5>
        @endif
    </div>
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    @if($user?->{UsersConstants::COL_TP} == PermissionsConstants::SA ||
        $user?->{UsersConstants::COL_TP} == PermissionsConstants::CPN)
        <li class="breadcrumb-item active" aria-current="page">{{__('Email Notification')}}</li>
    @else
        <li class="breadcrumb-item active" aria-current="page">{{__('Email Template')}}</li>
    @endif
@endsection
{{--@section('action-btn')--}}
{{--    <div class="float-end">--}}
{{--        <a href="#" class="btn btn-sm btn-primary" data-ajax-popup="true"--}}
{{--                   data-title="{{__('Create New Email Template')}}" title="{{__('Create')}}" data-url="{{route('email_template.create')}}">--}}
{{--                    <i class="ti ti-plus"></i> </a>--}}
{{--    </div>--}}

{{--@endsection--}}
@section(YieldingConstants::ADM_CTT)
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <h5></h5>
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                        <tr>
                            <th scope="col" class="sort" data-sort="name"> {{__('Name')}}</th>
                            @if($user?->{UsersConstants::COL_TP} == PermissionsConstants::SA ||
                                $user?->{UsersConstants::COL_TP} == PermissionsConstants::CPN)
                                <th class="text-end">{{__('On / Off')}}</th>
                            @else
                                <th class="text-end">{{__('Action')}}</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>

                        @foreach ($EmailTemplates as $EmailTemplate)
                            <tr>
                                <td>{{ $EmailTemplate->name }}</td>
                                <td>
                                    @if($user?->{UsersConstants::COL_TP} == PermissionsConstants::SA)
                                        <div class="text-end">
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="{{ route('manage.email.language',[$EmailTemplate->id,$user?->lang]) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-toggle="tooltip" title="{{__('View')}}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endif

                                    @if($user?->{UsersConstants::COL_TP} == PermissionsConstants::SA ||
                                        $user?->{UsersConstants::COL_TP} == PermissionsConstants::CPN)
                                        <div class="text-end">

                                            <div class="form-check form-switch d-inline-block">
                                                <label class="form-check-label form-switch">
                                                    <input type="checkbox" class="form-check-input email-template-checkbox" id="email_tempalte_{{!empty($EmailTemplate->template)?$EmailTemplate->template->id:''}}"
                                                           @if(!empty($EmailTemplate->template)?$EmailTemplate->template->is_active:'0' == 1) checked="checked" @endif type="checkbox" value="{{!empty($EmailTemplate->template)?$EmailTemplate->template->is_active:''}} "
                                                           data-url="{{route('status.email.language',[!empty($EmailTemplate->template)?$EmailTemplate->template->id:''])}}"/>
                                                    <span class="slider1 round"></span>
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
