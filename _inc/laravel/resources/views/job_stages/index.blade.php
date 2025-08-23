@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        UsersConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user)
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Job Stage')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Job Stage')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can(PermissionsConstants::CR_JST)
            @php
                $createRoute = Route::has(ViewsConstants::JB_STG.'.create')
                    ? route(ViewsConstants::JB_STG.'.create')
                    : '#';
            @endphp
            <a
                href="#"
                data-url="{{ $createRoute }}"
                data-ajax-popup="true"
                data-title="{{ __('Create New Job Stage') }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="{{ ViewClassNamesConstants::BT_SM_PM }}"
            ></a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-3">
            @include('layouts.hrm_setup')
        </div>
        <div class="col-9">
            <div class="card">
                <div class="card-body">
                    <div class="tab-content tab-bordered">
                        <div class="tab-pane fade show active" role="tabpanel">
                            <ul class="list-unstyled list-group sortable stage">
                                @foreach ($stages as $stage)
                                    <li class="{{ ViewClassNamesConstants::DFL_AIC_JCB_IT }}" data-id="{{$stage->id}}">
                                        <h6 class="mb-0">
                                            <i class="{{ ViewClassNamesConstants::TI_AR_M3 }}" data-feather="move"></i>
                                            <span>{{$stage->title}}</span>
                                        </h6>
                                        <span class="float-end">
                                            @can(PermissionsConstants::ED_JST)
                                                @php
                                                    $editRoute = Route::has(ViewsConstants::JB_STG.'.edit')
                                                        ? route(ViewsConstants::JB_STG.'.edit', $stage->id)
                                                        : '#';
                                                    $editMessage = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::JB_STG,
                                                        'edit_job_stage_unavailable'
                                                    ) ?? 'Edit job stage route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_PRIM }}">
                                                    <a
                                                        href="#"
                                                        class="{{ ViewClassNamesConstants::BT_SM_CT }}"
                                                        data-url="{{ $editRoute }}"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Job Stage') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        data-original-title="{{ __('Edit') }}"
                                                    >
                                                        <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can(PermissionsConstants::DEL_JST)
                                                @php
                                                    $destroyRoute = Route::has(ViewsConstants::JB_STG.'.destroy')
                                                        ? route(ViewsConstants::JB_STG.'.destroy', $stage->id)
                                                        : '#';
                                                    $formId = 'delete-form-'.$stage->id;
                                                    $btnId = 'delete-btn-'.$stage->id;
                                                    $deleteMessage = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::JB_STG,
                                                        'delete_job_stage_unavailable'
                                                    ) ?? 'Delete job stage route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                    {!! Collective\Html\FormFacade::open([
                                                        'url'      => $destroyRoute,
                                                        'method'   => 'delete',
                                                        'id'       => $formId,
                                                    ]) !!}
                                                    <a
                                                        href="#"
                                                        id="{{ $btnId }}"
                                                        class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"
                                                        data-url="{{ $destroyRoute }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Delete') }}"
                                                    >
                                                        <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
                                                    </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                </div>
                                            @endcan
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <p class="mt-4"><strong>{{__('Note')}} : </strong><b>{{__('You can easily change order of job stage using drag & drop.')}}</b></p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
        <script>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
            'ar': {
                'create_job_stage_unavailable': 'مسار إنشاء مرحلة الوظيفة غير متاح. يرجى الاتصال بالدعم الفني أو مسؤول النطاق.'
            },
            'da': {
                'create_job_stage_unavailable': 'Ruten til oprettelse af jobstadie er ikke tilgængelig. Kontakt teknisk support eller din domæneadministrator.'
            },
            'de': {
                'create_job_stage_unavailable': 'Die Route zum Erstellen der Jobphase ist nicht verfügbar. Bitte wenden Sie sich an den technischen Support oder Ihren Domain-Administrator.'
            },
            'en': {
                'create_job_stage_unavailable': 'Create job stage route is unavailable. Please contact technical support or your domain administrator.'
            },
            'es': {
                'create_job_stage_unavailable': 'La ruta de creación de etapa de trabajo no está disponible. Contacte al soporte técnico o a su administrador de dominio.'
            },
            'fr': {
                'create_job_stage_unavailable': 'La route de création des étapes du job n\'est pas disponible. Contactez le support technique ou votre administrateur de domaine.'
            },
            'he': {
                'create_job_stage_unavailable': 'נתיב יצירת שלב העבודה אינו זמין. אנא פנה לתמיכה טכנית או למנהל הדומיין שלך.'
            },
            'it': {
                'create_job_stage_unavailable': 'La rotta di creazione della fase di lavoro non è disponibile. Contatta il supporto tecnico o l\'amministratore del dominio.'
            },
            'ja': {
                'create_job_stage_unavailable': '求人ステージ作成ルートが利用できません。テクニカルサポートまたはドメイン管理者にお問い合わせください。'
            },
            'nl': {
                'create_job_stage_unavailable': 'Aanmaakroute voor jobfase is niet beschikbaar. Neem contact op met technische ondersteuning of uw domeinbeheerder.'
            },
            'pl': {
                'create_job_stage_unavailable': 'Trasa tworzenia etapu pracy jest niedostępna. Skontaktuj się z pomocą techniczną lub administratorem domeny.'
            },
            'pt': {
                'create_job_stage_unavailable': 'A rota de criação da fase de trabalho não está disponível. Entre em contato com o suporte técnico ou seu administrador de domínio.'
            },
            'pt-br': {
                'create_job_stage_unavailable': 'A rota para criar estágio de trabalho não está disponível. Entre em contato com o suporte técnico ou o administrador do domínio.'
            },
            'ru': {
                'create_job_stage_unavailable': 'Маршрут создания этапа вакансии недоступен. Пожалуйста, обратитесь в техническую поддержку или к администратору домена.'
            },
            'tr': {
                'create_job_stage_unavailable': 'İş aşaması oluşturma rotası kullanılamıyor. Lütfen teknik destek veya alan yöneticinizle iletişime geçin.'
            },
            'zh': {
                'create_job_stage_unavailable': '创建工作阶段路由不可用。请联系技术支持或您的域管理员。'
            }
        };
Object.keys(t).forEach(
  k =>
    (window.translations[k] = {
      ...(window.translations[k] || {}),
      ...t[k],
    })
);
     
          })();
    </script>
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    @if($user?->{UsersConstants::COL_TP} === PermissionsConstants::CPN)
        <script defer>
            $(function () {
                $(".sortable").sortable();
                $(".sortable").disableSelection();
                $(".sortable").sortable({
                    stop: function () {
                        var order = [];
                        $(this).find('li').each(function (index, data) {
                            order[index] = $(data).attr('data-id');
                        });
                        $.ajax({
                            url: "{{route(ViewsConstants::JB_STG.'.order')}}",
                            data: {order: order, _token: $('meta[name="csrf-token"]').attr('content')},
                            type: 'POST',
                            success: function (data) {
                            },
                            error: function (data) {
                                data = data.responseJSON;
                                toastr('Error', data.error, 'error')
                            }
                        })
                    }
                });
            });
        </script>
    @endif
    @can(PermissionsConstants::CR_JST)
        <script defer>
            (() => {
                const errFb = "# ERROR";
                const dataSVLocalized = "data-sv-localized";
                const dataClientLocalized = "data-client-localized";
                const dataGuardMsg = "data-guard-msg";
                const listenerAttr = "data-create-listener-active";
                const msgKey = "create_job_stage_unavailable";
                const getMessage = (el, key) => {
                    let msg = errFb;
                    if (
                        el.getAttribute(dataSVLocalized) === "true" ||
                        el.getAttribute(dataClientLocalized) === "true"
                    ) {
                        msg = el.getAttribute(dataGuardMsg) ?? errFb;
                    } else {
                        let lang = (
                            window.sessionStorage.getItem("erp-np-lang") ||
                            document.documentElement.lang ||
                            "en"
                        )
                            .toLowerCase()
                            .replace(/_/g, "-");
                        lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                        msg =
                            window.translations?.[lang]?.[key] ||
                            el.getAttribute(dataGuardMsg) ||
                            window.translations?.["en"]?.[key] ||
                            errFb;
                        if (msg !== errFb) {
                            el.setAttribute(dataGuardMsg, msg);
                            el.setAttribute(dataClientLocalized, "true");
                        }
                    }
                    return msg;
                };
                const route = "{{ $createRoute }}";
                const el = document.querySelector(
                    `[data-ajax-popup="true"][data-url="${route}"]`
                );
                if (!el) return;
                if (el.getAttribute(listenerAttr) === "true") return;
                el.setAttribute(listenerAttr, "true");
                const onClick = event => {
                    try {
                        const url = el.getAttribute("data-url");
                        const href = el.href;
                        if ((!url || url === "#") && (!href || href === "#")) {
                            event.preventDefault();
                            const message = getMessage(el, msgKey);
                            const bootstrapLink = document.querySelector(
                                'link[href*="bootstrap"]'
                            );
                            const containerId = "toast-container";
                            let container = document.getElementById(containerId);
                            if (!container) {
                                container = document.createElement("div");
                                container.id = containerId;
                                document.body.appendChild(container);
                            }
                            if (bootstrapLink && window.bootstrap) {
                                const toastEl = document.createElement("div");
                                toastEl.className = "toast";
                                toastEl.setAttribute("role", "alert");
                                toastEl.setAttribute("aria-live", "assertive");
                                toastEl.setAttribute("aria-atomic", "true");
                                const body = document.createElement("div");
                                body.className = "toast-body";
                                body.textContent = message;
                                toastEl.appendChild(body);
                                container.appendChild(toastEl);
                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                            } else {
                                alert(message);
                            }
                        }
                    } catch (error) {}
                };
                el.addEventListener("click", onClick);
                const observer = new MutationObserver(() => {
                    if (!document.body.contains(el)) {
                        observer.disconnect();
                        el.removeEventListener("click", onClick);
                    }
                });
                observer.observe(document.body, { childList: true, subtree: true });
            })();
        </script>
    @endcan
    @can(PermissionsConstants::ED_JST)
        <script defer>
            (() => {
                const listenerAttr = 'data-edit-listener-active';
                const msg = "{{ $editMessage }}";
                const el = document.querySelector(
                    `[data-ajax-popup="true"][data-url="{{ $editRoute }}"]`
                );
                if (!el) return;
                if (el.getAttribute(listenerAttr) === 'true') return;
                el.setAttribute(listenerAttr, 'true');
                const onClick = event => {
                    try {
                        const url = el.getAttribute('data-url');
                        const href = el.href;
                        if ((!url || url === '#') && (!href || href === '#')) {
                            event.preventDefault();
                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                            const containerId = 'toast-container';
                            let container = document.getElementById(containerId);
                            if (!container) {
                                container = document.createElement('div');
                                container.id = containerId;
                                document.body.appendChild(container);
                            }
                            if (bootstrapLink && window.bootstrap) {
                                const toastEl = document.createElement('div');
                                toastEl.className = 'toast';
                                toastEl.setAttribute('role','alert');
                                toastEl.setAttribute('aria-live','assertive');
                                toastEl.setAttribute('aria-atomic','true');
                                const body = document.createElement('div');
                                body.className = 'toast-body';
                                body.textContent = msg;
                                toastEl.appendChild(body);
                                container.appendChild(toastEl);
                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                            } else {
                                alert(msg);
                            }
                            el.setAttribute('data-failed-route', 'true');
                        }
                    } catch (error) {}
                };
                el.addEventListener('click', onClick);
                const observer = new MutationObserver(() => {
                    if (!document.body.contains(el)) {
                        observer.disconnect();
                        el.removeEventListener('click', onClick);
                    }
                });
                observer.observe(document.body, { childList: true, subtree: true });
            })();
        </script>
    @endcan
    @can(PermissionsConstants::DEL_JST)
        <script defer>
            (() => {
                const dataUrlAttr = 'data-url';
                const listenerAttr = 'data-delete-listener-active';
                const btn = document.getElementById('{{ $btnId }}');
                if (!btn) return;
                if (btn.getAttribute(listenerAttr) === 'true') return;
                btn.setAttribute(listenerAttr, 'true');
                const onClick = event => {
                    try {
                        const url = btn.getAttribute(dataUrlAttr);
                        const href = btn.href;
                        if ((!url || url === '#') && (!href || href === '#')) {
                            event.preventDefault();
                            const message = "{{ $delMessage }}";
                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                            const containerId = 'toast-container';
                            let container = document.getElementById(containerId);
                            if (!container) {
                                container = document.createElement('div');
                                container.id = containerId;
                                document.body.appendChild(container);
                            }
                            if (bootstrapLink && window.bootstrap) {
                                const toastEl = document.createElement('div');
                                toastEl.className = 'toast';
                                toastEl.setAttribute('role', 'alert');
                                toastEl.setAttribute('aria-live', 'assertive');
                                toastEl.setAttribute('aria-atomic', 'true');
                                const body = document.createElement('div');
                                body.className = 'toast-body';
                                body.textContent = message;
                                toastEl.appendChild(body);
                                container.appendChild(toastEl);
                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                            } else {
                                alert(message);
                            }
                            el.setAttribute('data-failed-route', 'true');
                            return;
                        }
                        const form = document.getElementById('{{ $formId }}');
                        if (form) form.submit();
                    } catch (error) {}
                };
                btn.addEventListener('click', onClick);
                const observer = new MutationObserver(() => {
                    if (!document.body.contains(btn)) {
                        observer.disconnect();
                        btn.removeEventListener('click', onClick);
                    }
                });
                observer.observe(document.body, { childList: true, subtree: true });
            })();
        </script>
    @endcan
@endpush