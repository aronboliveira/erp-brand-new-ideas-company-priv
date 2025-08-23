@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Crypt, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $billCreateRoute      = Route::has(ViewsConstants::BIL . '.create')
        ? route(ViewsConstants::BIL . '.create', 0)
        : '#';
    $billCreateBtnId      = 'bill-create-btn';
    $billCreateGuardMsg   = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::BIL,
        'bill_create_route_unavailable'
    ) ?? 'Bill create route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Bills')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
        <script>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
            ar:  { copy_link_unavailable: "نسخ الرابط غير متاح" },
            da:  { copy_link_unavailable: "Kopiering af link ikke tilgængelig" },
            de:  { copy_link_unavailable: "Link kopieren nicht verfügbar" },
            en:  { copy_link_unavailable: "Copy link unavailable" },
            es:  { copy_link_unavailable: "Copia de enlace no disponible" },
            fr:  { copy_link_unavailable: "Copie du lien non disponible" },
            he:  { copy_link_unavailable: "העתקת הקישור אינה זמינה" },
            it:  { copy_link_unavailable: "Copia del link non disponibile" },
            ja:  { copy_link_unavailable: "リンクのコピーは利用できません" },
            nl:  { copy_link_unavailable: "Kopiëren van de link niet beschikbaar" },
            pl:  { copy_link_unavailable: "Kopiowanie linku niedostępne" },
            pt:  { copy_link_unavailable: "Cópia do link indisponível" },
            "pt-br": { copy_link_unavailable: "Cópia do link indisponível" },
            ru:  { copy_link_unavailable: "Копирование ссылки недоступно" },
            tr:  { copy_link_unavailable: "Bağlantı kopyalama kullanılamıyor" },
            zh:  { copy_link_unavailable: "无法复制链接" }
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
    <script defer>
        (() => {
        const BS_LINK = 'link[href*="bootstrap"]';
        const toastContainer = (() => {
            const c = document.createElement('div');
            c.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.append(c);
            return c;
        })();

        const showError = key => {
            const errFb = '# ERROR';
            let lang = (window.sessionStorage.getItem('erp-np-lang') || document.documentElement.lang || 'en')
            .toLowerCase().replace(/_/g,'-');
            lang = lang === 'pt-br' ? lang : lang.slice(0,2);
            const msg = window.translations?.[lang]?.[key]
            || window.translations?.['en']?.[key]
            || errFb;
            if (toastContainer.querySelector(`.toast[data-error-key="${key}"]`)) return;
            if (document.querySelector(BS_LINK) && window.bootstrap?.Toast) {
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-bg-danger border-0';
            toast.dataset.errorKey = key;
            toast.setAttribute('role','alert');
            toast.setAttribute('aria-live','assertive');
            toast.setAttribute('aria-atomic','true');
            toast.innerHTML = `
                <div class="d-flex">
                <div class="toast-body">${msg}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast" aria-label="Close"></button>
                </div>`;
            toastContainer.append(toast);
            new window.bootstrap.Toast(toast).show();
            } else {
            alert(msg);
            }
        };

        try {
            document.querySelectorAll('.copy_link').forEach(el => {
            if (el.dataset.copyListener) return;
            el.dataset.copyListener = 'true';
            el.addEventListener('click', e => {
                e.preventDefault();
                const href = el.getAttribute('href') ?? '';
                if (!href) {
                console.error('No href to copy');
                showError('copy_link_unavailable');
                return;
                }
                try {
                const onCopy = evt => {
                    evt.clipboardData.setData('text/plain', href);
                    evt.preventDefault();
                };
                document.addEventListener('copy', onCopy, true);
                const success = document.execCommand('copy');
                document.removeEventListener('copy', onCopy, true);
                if (!success) throw new Error('execCommand returned false');
                show_toastr('success', window.translations?.['en']?.copy_link_success || 'Link copied', 'success');
                } catch (err) {
                console.error('Copy command failed:', err);
                showError('copy_link_unavailable');
                }
            });
            });
        } catch (err) {
            console.error('Failed to bind copy_link handlers:', err);
        }
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @php
            $exportRoute      = Route::has(ViewsConstants::BIL . '.export')
                ? route(ViewsConstants::BIL . '.export')
                : '#';
            $exportBtnId      = 'bill-export-btn';
            $exportGuardMsg   = Utility::fetchLinkMessage(
                $lang,
                ViewsConstants::BIL,
                'bill_export_route_unavailable'
            ) ?? 'Bill export route is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a
            id="{{ $exportBtnId }}"
            href="{{ $exportRoute }}"
            data-url="{{ $exportRoute }}"
            data-guard-msg="{{ $exportGuardMsg }}"
            class="{{ VC::BT_SM_PM }}"
            data-bs-toggle="tooltip"
            title="{{ __('Export') }}"
        >
            <i class="{{ VC::TI_EXP }}"></i>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer>
                (() => {
                    const btn = document.getElementById('{{ $exportBtnId }}');
                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                    btn.setAttribute('data-listener-active', 'true');
                    btn.addEventListener('click', event => {
                        try {
                            const href = btn.getAttribute('href');
                            const url  = btn.getAttribute('data-url');
                            if ((href && href !== '#') || (url && url !== '#')) return;
                            event.preventDefault();
                            const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                            let container       = document.getElementById('toast-container');
                            if (!container) {
                                container       = document.createElement('div');
                                container.id    = 'toast-container';
                                document.body.appendChild(container);
                            }
                            if (bootstrapLink && window.bootstrap) {
                                const toastEl      = document.createElement('div');
                                toastEl.className  = 'toast';
                                toastEl.setAttribute('role', 'alert');
                                toastEl.setAttribute('aria-live', 'assertive');
                                toastEl.setAttribute('aria-atomic', 'true');
                                const body         = document.createElement('div');
                                body.className     = 'toast-body';
                                body.textContent   = msg;
                                toastEl.appendChild(body);
                                container.appendChild(toastEl);
                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                            } else {
                                alert(msg);
                            }
                            btn.setAttribute('data-failed-route', 'true');
                        } catch (e) {}
                    });
                })();
            </script>
        @endpush
        @can('create bill')
            @php
                $billCreateRoute      = Route::has(ViewsConstants::BIL . '.create')
                    ? route(ViewsConstants::BIL . '.create', 0)
                    : '#';
                $billCreateBtnId      = 'bill-create-btn';
                $billCreateGuardMsg   = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::BIL,
                    'bill_create_route_unavailable'
                ) ?? 'Bill create route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a
                id="{{ $billCreateBtnId }}"
                href="{{ $billCreateRoute }}"
                data-url="{{ $billCreateRoute }}"
                data-guard-msg="{{ $billCreateGuardMsg }}"
                class="{{ VC::BT_SM_PM }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        const btn = document.getElementById('{{ $billCreateBtnId }}');
                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                        btn.setAttribute('data-listener-active', 'true');
                        btn.addEventListener('click', event => {
                            try {
                                const href = btn.getAttribute('href');
                                const url  = btn.getAttribute('data-url');
                                if ((href && href !== '#') || (url && url !== '#')) return;
                                event.preventDefault();
                                const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                let container       = document.getElementById('toast-container');
                                if (!container) {
                                    container       = document.createElement('div');
                                    container.id    = 'toast-container';
                                    document.body.appendChild(container);
                                }
                                if (bootstrapLink && window.bootstrap) {
                                    const toastEl      = document.createElement('div');
                                    toastEl.className  = 'toast';
                                    toastEl.setAttribute('role', 'alert');
                                    toastEl.setAttribute('aria-live', 'assertive');
                                    toastEl.setAttribute('aria-atomic', 'true');
                                    const body         = document.createElement('div');
                                    body.className     = 'toast-body';
                                    body.textContent   = msg;
                                    toastEl.appendChild(body);
                                    container.appendChild(toastEl);
                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                } else {
                                    alert(msg);
                                }
                                btn.setAttribute('data-failed-route', 'true');
                            } catch (e) {}
                        });
                    })();
                </script>
            @endpush
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        @php
                            $frmSubmitRoute   = Route::has(ViewsConstants::BIL . '.index')
                                ? route(ViewsConstants::BIL . '.index')
                                : '#';
                            $frmSubmitFormId  = 'frm_submit';
                            $frmSubmitMsg     = Utility::fetchLinkMessage(
                                $lang,
                                ViewsConstants::BIL,
                                'bill_index_route_unavailable'
                            ) ?? 'Bill index route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        {{ Form::open([
                            'route'            => $frmSubmitRoute,
                            'method'         => 'GET',
                            'id'             => $frmSubmitFormId,
                            'data-url'       => $frmSubmitRoute,
                            'data-guard-msg' => $frmSubmitMsg,
                        ]) }}
                            <div class="{{ ViewClassNamesConstants::R_ALC_JCE }}">
                                <div class="col-xl-10">
                                    <div class="{{ ViewClassNamesConstants::RW }}">
                                        <div class="col-3"></div>
                                        <div class="col-3"></div>
                                        <div class="{{ ViewClassNamesConstants::CL_XLG4 }} month">
                                            <div class="btn-box">
                                                {{ Form::label('bill_date', __('Bill Date'), ['class' => ViewClassNamesConstants::FM_LB]) }}
                                                {{ Form::text(
                                                    'bill_date',
                                                    request('bill_date'),
                                                    [
                                                        'class'    => ViewClassNamesConstants::FM_CT . ' month-btn',
                                                        'id'       => 'pc-daterangepicker-1',
                                                        'readonly' => true,
                                                    ]
                                                ) }}
                                            </div>
                                        </div>
                                        <div class="{{ ViewClassNamesConstants::CL_XLG4 }}">
                                            <div class="btn-box">
                                                {{ Form::label('status', __('Status'), ['class' => ViewClassNamesConstants::FM_LB]) }}
                                                {{ Form::select(
                                                    'status',
                                                    ['' => __('Select Status')] + $status,
                                                    request('status'),
                                                    ['class' => ViewClassNamesConstants::FM_CT_SL]
                                                ) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ ViewClassNamesConstants::C_AT_FEND }}">
                                    <div class="{{ ViewClassNamesConstants::DFL_JCB }}">
                                        <a
                                            href="#"
                                            class="{{ ViewClassNamesConstants::BT_SM_PM }}"
                                            onclick="document.getElementById('frm_submit').submit(); return false;"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}"
                                        >
                                            <span class="btn-inner--icon">
                                                <i class="{{ ViewClassNamesConstants::TI_SRC }}"></i>
                                            </span>
                                        </a>
                                        @php
                                            $resetRoute    = Route::has(ViewsConstants::BIL . '.index')
                                                ? route(ViewsConstants::BIL . '.index')
                                                : '#';
                                            $resetBtnId    = 'bill-reset-btn';
                                            $resetGuardMsg = Utility::fetchLinkMessage(
                                                $lang,
                                                ViewsConstants::BIL,
                                                'bill_index_route_unavailable'
                                            ) ?? 'Bill index route is unavailable. Please contact technical support or your domain administrator.';
                                        @endphp
                                        <a
                                            id="{{ $resetBtnId }}"
                                            href="{{ $resetRoute }}"
                                            data-url="{{ $resetRoute }}"
                                            data-guard-msg="{{ $resetGuardMsg }}"
                                            class="{{ VC::BT_SM_DG }}"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Reset') }}"
                                        >
                                            <span class="btn-inner--icon">
                                                <i class="{{ VC::TI_TRS_OFF }}"></i>
                                            </span>
                                        </a>
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (() => {
                                                    const btn = document.getElementById('{{ $resetBtnId }}');
                                                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                    btn.setAttribute('data-listener-active', 'true');
                                                    btn.addEventListener('click', event => {
                                                        try {
                                                            const href = btn.getAttribute('href');
                                                            const url  = btn.getAttribute('data-url');
                                                            if ((href && href !== '#') || (url && url !== '#')) return;
                                                            event.preventDefault();
                                                            const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            let container       = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container    = document.createElement('div');
                                                                container.id = 'toast-container';
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bootstrapLink && window.bootstrap) {
                                                                const toastEl      = document.createElement('div');
                                                                toastEl.className  = 'toast';
                                                                toastEl.setAttribute('role', 'alert');
                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                const body         = document.createElement('div');
                                                                body.className     = 'toast-body';
                                                                body.textContent   = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            btn.setAttribute('data-failed-route', 'true');
                                                        } catch (e) {}
                                                    });
                                                })();
                                            </script>
                                        @endpush
                                    </div>
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ ViewClassNamesConstants::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Bill') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Bill Date') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if(Gate::check('edit bill') || Gate::check('delete bill') || Gate::check('show bill'))
                                        <th width="10%">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bills as $bill)
                                    <tr>
                                        <td class="Id">
                                            @php
                                                $billShowRoute    = Route::has(ViewsConstants::BIL . '.show')
                                                    ? route(ViewsConstants::BIL . '.show', Crypt::encrypt($bill->id))
                                                    : '#';
                                                $billShowLinkId   = 'bill-show-' . $bill->id;
                                                $billShowMsg      = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::BIL,
                                                    'bill_show_route_unavailable'
                                                ) ?? 'Bill view route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <a
                                                id="{{ $billShowLinkId }}"
                                                href="{{ $billShowRoute }}"
                                                class="{{ VC::BT_OUTPM }}"
                                                data-url="{{ $billShowRoute }}"
                                                data-guard-msg="{{ $billShowMsg }}"
                                            >
                                                {{ $user?->billNumberFormat($bill->bill_id) }}
                                            </a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        const link = document.getElementById('{{ $billShowLinkId }}');
                                                        if (!link || link.getAttribute('data-listener-active') === 'true') return;
                                                        link.setAttribute('data-listener-active', 'true');
                                                        link.addEventListener('click', event => {
                                                            try {
                                                                const href = link.getAttribute('href');
                                                                const url  = link.getAttribute('data-url');
                                                                if ((href && href !== '#') || (url && url !== '#')) return;
                                                                event.preventDefault();
                                                                const msg           = link.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                let container       = document.getElementById('toast-container');
                                                                if (!container) {
                                                                    container       = document.createElement('div');
                                                                    container.id    = 'toast-container';
                                                                    document.body.appendChild(container);
                                                                }
                                                                if (bootstrapLink && window.bootstrap) {
                                                                    const toastEl      = document.createElement('div');
                                                                    toastEl.className  = 'toast';
                                                                    toastEl.setAttribute('role', 'alert');
                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                    const body         = document.createElement('div');
                                                                    body.className     = 'toast-body';
                                                                    body.textContent   = msg;
                                                                    toastEl.appendChild(body);
                                                                    container.appendChild(toastEl);
                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                } else {
                                                                    alert(msg);
                                                                }
                                                                link.setAttribute('data-failed-route', 'true');
                                                            } catch (e) {}
                                                        });
                                                    })();
                                                </script>
                                            @endpush
                                        </td>
                                        <td>{{ $bill->category->name ?? '-' }}</td>
                                        <td>{{ $user?->dateFormat($bill->bill_date) }}</td>
                                        <td>{{ $user?->dateFormat($bill->due_date) }}</td>
                                        @php
                                            $statusClasses = [
                                                0 => 'bg-secondary',
                                                1 => 'bg-warning',
                                                2 => 'bg-danger',
                                                3 => 'bg-info',
                                                4 => 'bg-primary',
                                            ];
                                            $statusLabel = \App\Models\Invoice::$statuses[$bill->status] ?? '';
                                            $badgeClass  = $statusClasses[$bill->status] ?? 'bg-secondary';
                                        @endphp
                                        <td>
                                            <span class="status_badge {{ ViewClassNamesConstants::BDG }} {{ $badgeClass }} p-2 {{ ViewClassNamesConstants::PX3 }} rounded">
                                                {{ __($statusLabel) }}
                                            </span>
                                        </td>
                                        @if(Gate::check('edit bill') || Gate::check('delete bill') || Gate::check('show bill'))
                                            <td class="Action">
                                                <span>
                                                    @can('duplicate bill')
                                                        @php
                                                            $duplicateRoute       = Route::has(ViewsConstants::BIL . '.duplicate')
                                                                ? route(ViewsConstants::BIL . '.duplicate', $bill->id)
                                                                : '#';
                                                            $duplicateBtnId       = 'duplicate-btn-' . $bill->id;
                                                            $duplicateFormId      = 'duplicate-form-' . $bill->id;
                                                            $duplicateGuardMsg    = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::BIL,
                                                                'bill_duplicate_route_unavailable'
                                                            ) ?? 'Bill duplicate route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            {!! Form::open([
                                                                'route'            => $duplicateRoute,
                                                                'method'         => 'get',
                                                                'id'             => $duplicateFormId,
                                                                'data-url'       => $duplicateRoute,
                                                                'data-guard-msg' => $duplicateGuardMsg,
                                                            ]) !!}
                                                                <a
                                                                    id="{{ $duplicateBtnId }}"
                                                                    href="#"
                                                                    class="{{ VC::BT_SM_CT_PR }}"
                                                                    data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                    data-confirm-yes="document.getElementById('{{ $duplicateFormId }}').submit();"
                                                                >
                                                                    <i class="{{ VC::TI_COPY ?? 'ti ti-copy text-white' }}"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const btn = document.getElementById('{{ $duplicateBtnId }}');
                                                                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                    btn.setAttribute('data-listener-active', 'true');
                                                                    btn.addEventListener('click', event => {
                                                                        try {
                                                                            const href = btn.getAttribute('href');
                                                                            const url  = btn.getAttribute('data-url');
                                                                            if ((href && href !== '#') || (url && url !== '#')) return;
                                                                            event.preventDefault();
                                                                            const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                            let container       = document.getElementById('toast-container');
                                                                            if (!container) {
                                                                                container       = document.createElement('div');
                                                                                container.id    = 'toast-container';
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (bootstrapLink && window.bootstrap) {
                                                                                const toastEl      = document.createElement('div');
                                                                                toastEl.className  = 'toast';
                                                                                toastEl.setAttribute('role', 'alert');
                                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                                const body         = document.createElement('div');
                                                                                body.className     = 'toast-body';
                                                                                body.textContent   = msg;
                                                                                toastEl.appendChild(body);
                                                                                container.appendChild(toastEl);
                                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            btn.setAttribute('data-failed-route', 'true');
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    @endcan
                                                    @can('show bill')
                                                        @php
                                                            $showRoute        = Route::has(ViewsConstants::BIL . '.show')
                                                                ? route(ViewsConstants::BIL . '.show', Crypt::encrypt($bill->id))
                                                                : '#';
                                                            $showBtnId        = 'bill-show-btn-' . $bill->id;
                                                            $showGuardMsg     = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::BIL,
                                                                'bill_show_route_unavailable'
                                                            ) ?? 'Bill view route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_INF }}">
                                                            <a
                                                                id="{{ $showBtnId }}"
                                                                href="{{ $showRoute }}"
                                                                data-url="{{ $showRoute }}"
                                                                data-guard-msg="{{ $showGuardMsg }}"
                                                                class="{{ VC::BT_SM_CT }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Show') }}"
                                                            >
                                                                <i class="{{ VC::TI_EYE_WT }}"></i>
                                                            </a>
                                                        </div>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const btn = document.getElementById('{{ $showBtnId }}');
                                                                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                    btn.setAttribute('data-listener-active', 'true');
                                                                    btn.addEventListener('click', event => {
                                                                        try {
                                                                            const href = btn.getAttribute('href');
                                                                            const url  = btn.getAttribute('data-url');
                                                                            if ((href && href !== '#') || (url && url !== '#')) return;
                                                                            event.preventDefault();
                                                                            const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                            let container       = document.getElementById('toast-container');
                                                                            if (!container) {
                                                                                container    = document.createElement('div');
                                                                                container.id = 'toast-container';
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (bootstrapLink && window.bootstrap) {
                                                                                const toastEl      = document.createElement('div');
                                                                                toastEl.className  = 'toast';
                                                                                toastEl.setAttribute('role', 'alert');
                                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                                const body         = document.createElement('div');
                                                                                body.className     = 'toast-body';
                                                                                body.textContent   = msg;
                                                                                toastEl.appendChild(body);
                                                                                container.appendChild(toastEl);
                                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            btn.setAttribute('data-failed-route', 'true');
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    @endcan
                                                    @can('edit bill')
                                                        @php
                                                            $billEditRoute    = Route::has(ViewsConstants::BIL . '.edit')
                                                                ? route(ViewsConstants::BIL . '.edit', Crypt::encrypt($bill->id))
                                                                : '#';
                                                            $billEditBtnId    = 'bill-edit-btn-' . $bill->id;
                                                            $billEditGuardMsg = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::BIL,
                                                                'bill_edit_route_unavailable'
                                                            ) ?? 'Bill edit route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            <a
                                                                id="{{ $billEditBtnId }}"
                                                                href="{{ $billEditRoute }}"
                                                                data-url="{{ $billEditRoute }}"
                                                                data-guard-msg="{{ $billEditGuardMsg }}"
                                                                class="{{ VC::BT_SM_CT }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Edit') }}"
                                                            >
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const btn = document.getElementById('{{ $billEditBtnId }}');
                                                                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                    btn.setAttribute('data-listener-active', 'true');
                                                                    btn.addEventListener('click', event => {
                                                                        try {
                                                                            const href = btn.getAttribute('href');
                                                                            const url  = btn.getAttribute('data-url');
                                                                            if ((href && href !== '#') || (url && url !== '#')) return;
                                                                            event.preventDefault();
                                                                            const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                            let container       = document.getElementById('toast-container');
                                                                            if (!container) {
                                                                                container       = document.createElement('div');
                                                                                container.id    = 'toast-container';
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (bootstrapLink && window.bootstrap) {
                                                                                const toastEl      = document.createElement('div');
                                                                                toastEl.className  = 'toast';
                                                                                toastEl.setAttribute('role', 'alert');
                                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                                const body         = document.createElement('div');
                                                                                body.className     = 'toast-body';
                                                                                body.textContent   = msg;
                                                                                toastEl.appendChild(body);
                                                                                container.appendChild(toastEl);
                                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            btn.setAttribute('data-failed-route', 'true');
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    @endcan
                                                    @can('delete bill')
                                                        @php
                                                            $destroyRoute     = Route::has(ViewsConstants::BIL . '.destroy')
                                                                ? route(ViewsConstants::BIL . '.destroy', $bill->id)
                                                                : '#';
                                                            $destroyBtnId     = 'bill-delete-btn-' . $bill->id;
                                                            $destroyFormId    = 'delete-form-' . $bill->id;
                                                            $destroyGuardMsg  = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::BIL,
                                                                'bill_destroy_route_unavailable'
                                                            ) ?? 'Bill destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                            {!! Form::open([
                                                                'route'            => $destroyRoute,
                                                                'method'         => 'DELETE',
                                                                'id'             => $destroyFormId,
                                                            ]) !!}
                                                                <a
                                                                    id="{{ $destroyBtnId }}"
                                                                    href="#"
                                                                    class="{{ VC::BT_SM_CT_PR }}"
                                                                    data-url="{{ $destroyRoute }}"
                                                                    data-guard-msg="{{ $destroyGuardMsg }}"
                                                                    data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                    data-confirm-yes="document.getElementById('{{ $destroyFormId }}').submit();"
                                                                >
                                                                    <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const btn = document.getElementById('{{ $destroyBtnId }}');
                                                                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                    btn.setAttribute('data-listener-active', 'true');
                                                                    btn.addEventListener('click', event => {
                                                                        try {
                                                                            const href = btn.getAttribute('href');
                                                                            const url  = btn.getAttribute('data-url');
                                                                            if ((href && href !== '#') || (url && url !== '#')) return;
                                                                            event.preventDefault();
                                                                            const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                            let container       = document.getElementById('toast-container');
                                                                            if (!container) {
                                                                                container       = document.createElement('div');
                                                                                container.id    = 'toast-container';
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (bootstrapLink && window.bootstrap) {
                                                                                const toastEl      = document.createElement('div');
                                                                                toastEl.className  = 'toast';
                                                                                toastEl.setAttribute('role', 'alert');
                                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                                const body         = document.createElement('div');
                                                                                body.className     = 'toast-body';
                                                                                body.textContent   = msg;
                                                                                toastEl.appendChild(body);
                                                                                container.appendChild(toastEl);
                                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            btn.setAttribute('data-failed-route', 'true');
                                                                        } catch (e) {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    @endcan
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

