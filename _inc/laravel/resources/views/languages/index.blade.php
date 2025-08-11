@php
    use App\Config\Constants\{
        DatabaseConstants,
        ExtendingLayoutsConstants,
        SettingsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_CSS)
@endpush
@push(StacksConstants::ADM_SCR_PG)
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Language')}}
@endsection
@section(YieldingConstants::ADM_PG_TTL)
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Language')}}</h5>
    </div>
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Language')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
        const errFb = '# ERROR';
        const dataClientLocalized = 'data-client-localized';
        const dataGuardMsg = 'data-guard-msg';
        const defaultLangSessionKey = 'erp-np-lang';
        const getLocalizedMessage = (msgKey, el) => {
            let msg = errFb;
            if (el.getAttribute('data-sv-localized') === 'true' || el.getAttribute(dataClientLocalized) === 'true') {
            msg = el.getAttribute(dataGuardMsg) ?? errFb;
            } else {
            let lang = (window.sessionStorage.getItem(defaultLangSessionKey) ?? document.documentElement.lang ?? 'en')
                .toLowerCase()
                .replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
            msg = window.translations?.[lang]?.[msgKey] ??
                    el.getAttribute(dataGuardMsg) ??
                    window.translations?.['en']?.[msgKey] ??
                    errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, 'true');
            }
            }
            return msg;
        };
        const el = document.querySelector('#disable_lang');
        if (!el || el.getAttribute('data-listener-attached') === 'true') return;
        const observer = new MutationObserver((mutations, obs) => {
            for (const m of mutations) {
            for (const node of m.removedNodes) {
                if (node === el) {
                el.removeEventListener('pointerup', handler);
                obs.disconnect();
                }
            }
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
        el.setAttribute('data-listener-attached', 'true');
        el.addEventListener('pointerup', handler);
        function handler() {
            try {
            const isChecked = el.checked ?? false;
            const mode = isChecked ? 'on' : 'off';
            const url = el.getAttribute('data-url');
            const href = el.form?.action ?? el.getAttribute('href');
            if ((!url || url === '#') && (!href || href === '#')) {
                showError(getLocalizedMessage('disable_lang_unavailable', el));
                return;
            }
            const requestUrl = url || href;
            const token = window.csrfToken ?? document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            if (!token) console.log('CSRF token missing');
            $.ajax({
                type: 'POST',
                url: requestUrl,
                dataType: 'json',
                data: { _token: token, mode, lang: el.getAttribute('data-lang') ?? '' }
            })
            .done(data => show_toastr('success', data.message, 'success'))
            .fail(() => showError(getLocalizedMessage('disable_lang_failed', el)));
            } catch {
            showError(getLocalizedMessage('disable_lang_failed', el));
            }
        }
        function showError(message) {
            try {
            let container = document.querySelector('#bootstrap-toast-container');
            if (!container) {
                const hasBootstrap = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
                .some(l => /bootstrap/i.test(l.href)) && window.bootstrap?.Toast;
                if (hasBootstrap) {
                container = document.createElement('div');
                container.id = 'bootstrap-toast-container';
                container.setAttribute('aria-live', 'polite');
                container.setAttribute('aria-atomic', 'true');
                document.body.appendChild(container);
                }
            }
            if (container && window.bootstrap.Toast) {
                let toast = container.querySelector('.toast');
                if (!toast) {
                toast = document.createElement('div');
                toast.className = 'toast';
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');
                const body = document.createElement('div');
                body.className = 'toast-body';
                toast.appendChild(body);
                container.appendChild(toast);
                if (toast.getAttribute('data-click-listener') !== 'true') {
                    toast.addEventListener('click', () => body.textContent = message);
                    toast.setAttribute('data-click-listener', 'true');
                }
                }
                toast.querySelector('.toast-body').textContent = message;
                new bootstrap.Toast(toast).show();
            } else {
                alert(message);
            }
            el.setAttribute('data-failed-route', 'true');
            } catch {
            alert(message);
            }
        }
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @if(
            $currentLang !== 
            (!empty($settings[SettingsConstants::DEF_LNG]) 
                ? $settings[SettingsConstants::DEF_LNG] 
                : DatabaseConstants::DEFAULT_LANG)
        )
            <div class="action-btn pb-0">
                <div class="form-check form-switch custom-switch-v1 mb-0">
                    <input type="hidden" name="disable_lang" value="off">
                    <input 
                        type="checkbox" 
                        class="form-check-input input-primary" 
                        name="disable_lang" 
                        data-bs-placement="top" 
                        title="{{ __('Enable/Disable') }}" 
                        id="disable_lang" 
                        data-bs-toggle="tooltip" 
                        {{ !in_array($currentLang, $disabledLang) ? 'checked' : '' }}
                    >
                    <label class="form-check-label" for="disable_lang"></label>
                </div>
            </div>
            @php
                $langDestroyRoute = Route::has('languages.destroy')
                    ? route('languages.destroy', $currentLang)
                    : '#';
                $linkId = 'lang-destroy-link';
                $langDestroyMsg = Utility::fetchLinkMessage(
                    app()->getLocale(),
                    ViewsConstants::LNG,
                    'language_destroy_route_unavailable'
                ) ?? 'Language delete route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG }}">
                {!! Collective\Html\FormFacade::open([
                    'method' => 'DELETE',
                    'url'    => $langDestroyRoute,
                    'id'     => 'langDestroyForm'
                ]) !!}
                    <a
                        id="{{ $linkId }}"
                        href="{{ $langDestroyRoute }}"
                        data-url="{{ $langDestroyRoute }}"
                        data-sv-localized="true"
                        data-guard-msg="{{ $langDestroyMsg }}"
                        class="{{ ViewClassNamesConstants::BT_SM_DG }} btn-icon bs-pass-para"
                        data-bs-toggle="tooltip"
                        title="{{ __('Delete') }}"
                    >
                        <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
                    </a>
                {!! Collective\Html\FormFacade::close() !!}
            </div>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        const listenerAttr = 'data-lang-destroy-listener-active';
                        const el = document.getElementById('{{ $linkId }}');
                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                        el.setAttribute(listenerAttr, 'true');
        
                        const url  = el.getAttribute('data-url');
                        const href = el.href;
                        const msg  = el.getAttribute('data-guard-msg') ?? '# ERROR';
        
                        if ((!url || url === '#') && (!href || href === '#')) {
                            el.addEventListener('click', event => {
                                event.preventDefault();
                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                let container = document.getElementById('toast-container');
                                if (!container) {
                                    container = document.createElement('div');
                                    container.id = 'toast-container';
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
                                    body.textContent = msg;
                                    toastEl.appendChild(body);
                                    container.appendChild(toastEl);
                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                } else {
                                    alert(msg);
                                }
                                el.setAttribute('data-failed-route', 'true');
                            });
                            return;
                        }
        
                        el.addEventListener('click', event => {
                            event.preventDefault();
                            const form = el.closest('form');
                            if (!form) return;
                            form.submit();
                        });
        
                        const observer = new MutationObserver(() => {
                            if (!document.getElementById('{{ $linkId }}')) observer.disconnect();
                        });
                        observer.observe(document.body, { childList: true, subtree: true });
                    })();
                </script>
            @endpush
        @endif
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-xl-2 col-md-3">
            <div class="{{ ViewClassNamesConstants::CD_STK }}" style="top:30px">
                <div class="{{ ViewClassNamesConstants::LG_FLSH }}" id="useradd-sidenav">
                    @foreach ($languages as $code => $langName)
                            @php
                                $manageRoute = Route::has('languages.manage')
                                    ? route('languages.manage', ['lang' => $code])
                                    : '#';
                                $linkId = 'language-manage-' . $code . '-link';
                                $message = Utility::fetchLinkMessage(
                                    app()->getLocale(),
                                    ViewsConstants::LNG,
                                    'language_manage_route_unavailable'
                                ) ?? 'Manage Language route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <a
                                id="{{ $linkId }}"
                                class="{{ ViewClassNamesConstants::LGI_ACT_NBD }} {{ $currentLang === $code ? 'active' : '' }}"
                                href="{{ $manageRoute }}"
                                data-url="{{ $manageRoute }}"
                                data-sv-localized="true"
                                data-guard-msg="{{ $message }}"
                            >
                                {{ ucfirst($langName) }}
                                <div class="float-end">
                                    <i class="{{ ViewClassNamesConstants::TI_CHV_RT }}"></i>
                                </div>
                            </a>
                    @endforeach
                    @push(StacksConstants::ADM_SCR_PG)
                        <script defer>
                            (() => {
                                const setupGuard = (id) => {
                                    const listenerAttr = `data-${id}-listener-active`;
                                    const el = document.getElementById(id);
                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                    el.setAttribute(listenerAttr, 'true');
                                    el.addEventListener('click', event => {
                                        try {
                                            const url = el.getAttribute('data-url');
                                            const href = el.href;
                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                event.preventDefault();
                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                let container = document.getElementById('toast-container');
                                                if (!container) {
                                                    container = document.createElement('div');
                                                    container.id = 'toast-container';
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
                                                    body.textContent = msg;
                                                    toastEl.appendChild(body);
                                                    container.appendChild(toastEl);
                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                } else {
                                                    alert(msg);
                                                }
                                                el.setAttribute('data-failed-route', 'true');
                                            }
                                        } catch {}
                                    });
                                    const observer = new MutationObserver(() => {
                                        if (!document.getElementById(id)) observer.disconnect();
                                    });
                                    observer.observe(document.body, { childList: true, subtree: true });
                                };
                    
                                @foreach ($languages as $code => $langName)
                                    setupGuard('language-manage-{{ $code }}-link');
                                @endforeach
                            })();
                        </script>
                    @endpush                
                </div>
            </div>
        </div>
        <div class="col-xl-10 col-md-9">
            <div class="p-3 card">
                <ul class="nav nav-pills nav-fill" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-user-tab-1" data-bs-toggle="pill" data-bs-target="#pills-user-1" type="button">
                            {{__('Labels')}}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-user-tab-2" data-bs-toggle="pill" data-bs-target="#pills-user-2" type="button">
                            {{__('Messages')}}
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card card-fluid">
                <div class="card-body" style="position: relative;">
                    <div class="tab-content no-padding" id="myTab2Content">
                        <div class="tab-pane fade show active" id="lang1" role="tabpanel" aria-labelledby="home-tab4">
                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="pills-user-1" role="tabpanel" aria-labelledby="pills-user-tab-1">
                                    @php
                                        $storeDataRoute = Route::has('languages.store.data')
                                            ? route('languages.store.data', [$currentLang])
                                            : '#';
                                        $formId = 'langStoreForm';
                                        $storeDataMsg = Utility::fetchLinkMessage(
                                            app()->getLocale(),
                                            ViewsConstants::LNG,
                                            'languages_store_data_route_unavailable'
                                        ) ?? 'Language store data route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <form
                                        method="post"
                                        action="{{ $storeDataRoute }}"
                                        id="{{ $formId }}"
                                        data-action="{{ $storeDataRoute }}"
                                        data-url="{{ $storeDataRoute }}"
                                        data-sv-localized="true"
                                        data-guard-msg="{{ $storeDataMsg }}"
                                    >
                                        @csrf
                                        <div class="row">
                                            @foreach($arrLabel as $label => $value)
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">{{ $label }}</label>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            name="label[{{ $label }}]"
                                                            value="{{ $value }}"
                                                        >
                                                    </div>
                                                </div>
                                            @endforeach
                                            <div class="col-lg-12 text-end">
                                                <button class="{{ ViewClassNamesConstants::BT_PRM }}" type="submit">
                                                    {{ __('Save Changes') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const listenerAttr = 'data-lang-store-listener-active';
                                                const form = document.getElementById('{{ $formId }}');
                                                if (!form || form.getAttribute(listenerAttr) === 'true') return;
                                                form.setAttribute(listenerAttr, 'true');
                                                form.addEventListener('submit', event => {
                                                    try {
                                                        const action     = form.getAttribute('action');
                                                        const dataAction = form.getAttribute('data-action');
                                                        if ((!action || action === '#') && (!dataAction || dataAction === '#')) {
                                                            event.preventDefault();
                                                            const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            let container       = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container       = document.createElement('div');
                                                                container.id    = 'toast-container';
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
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                        }
                                                    } catch (error) {}
                                                });
                                                const observer = new MutationObserver(() => {
                                                    if (!document.getElementById('{{ $formId }}')) observer.disconnect();
                                                });
                                                observer.observe(document.body, { childList: true, subtree: true });
                                            })();
                                        </script>
                                    @endpush
                                </div>
                                <div class="tab-pane fade" id="pills-user-2" role="tabpanel" aria-labelledby="pills-user-tab-2">
                                    @php
                                        $storeDataRoute = Route::has('languages.store.data')
                                            ? route('languages.store.data', [$currentLang])
                                            : '#';
                                        $formId = 'languages-store-data-form';
                                        $storeDataMsg = Utility::fetchLinkMessage(
                                            app()->getLocale(),
                                            ViewsConstants::LNG,
                                            'languages_store_data_route_unavailable'
                                        ) ?? 'Language store data route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <form
                                        method="post"
                                        action="{{ $storeDataRoute }}"
                                        id="{{ $formId }}"
                                        data-url="{{ $storeDataRoute }}"
                                        data-sv-localized="true"
                                        data-guard-msg="{{ $storeDataMsg }}"
                                    >
                                        @csrf
                                        <div class="row">
                                            @foreach($arrMessage as $fileName => $fileValue)
                                                <div class="col-lg-12">
                                                    <h5>{{ucfirst($fileName)}}</h5>
                                                </div>
                                                @foreach($fileValue as $label => $value)
                                                    @if(is_array($value))
                                                        @foreach($value as $label2 => $value2)
                                                            @if(is_array($value2))
                                                                @foreach($value2 as $label3 => $value3)
                                                                    @if(is_array($value3))
                                                                        @foreach($value3 as $label4 => $value4)
                                                                            @if(is_array($value4))
                                                                                @foreach($value4 as $label5 => $value5)
                                                                                    <div class="col-md-6">
                                                                                        <div class="form-group">
                                                                                            <label>{{$fileName}}.{{$label}}.{{$label2}}.{{$label3}}.{{$label4}}.{{$label5}}</label>
                                                                                            <input type="text" class="form-control" name="message[{{$fileName}}][{{$label}}][{{$label2}}][{{$label3}}][{{$label4}}][{{$label5}}]" value="{{$value5}}">
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            @else
                                                                                <div class="col-lg-6">
                                                                                    <div class="form-group">
                                                                                        <label>{{$fileName}}.{{$label}}.{{$label2}}.{{$label3}}.{{$label4}}</label>
                                                                                        <input type="text" class="form-control" name="message[{{$fileName}}][{{$label}}][{{$label2}}][{{$label3}}][{{$label4}}]" value="{{$value4}}">
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        @endforeach
                                                                    @else
                                                                        <div class="col-lg-6">
                                                                            <div class="form-group">
                                                                                <label>{{$fileName}}.{{$label}}.{{$label2}}.{{$label3}}</label>
                                                                                <input type="text" class="form-control" name="message[{{$fileName}}][{{$label}}][{{$label2}}][{{$label3}}]" value="{{$value3}}">
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            @else
                                                                <div class="col-lg-6">
                                                                    <div class="form-group">
                                                                        <label>{{$fileName}}.{{$label}}.{{$label2}}</label>
                                                                        <input type="text" class="form-control" name="message[{{$fileName}}][{{$label}}][{{$label2}}]" value="{{$value2}}">
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <div class="col-lg-6">
                                                            <div class="form-group">
                                                                <label>{{$fileName}}.{{$label}}</label>
                                                                <input type="text" class="form-control" name="message[{{$fileName}}][{{$label}}]" value="{{$value}}">
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endforeach
                                        </div>
                                        <div class="col-lg-12 text-end">
                                            <button class="{{ ViewClassNamesConstants::BT_PRM }}" type="submit">{{ __('Save Changes')}}</button>
                                        </div>
                                    </form>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const listenerAttr = 'data-languages-store-listener-active';
                                                const form = document.getElementById('{{ $formId }}');
                                                if (!form || form.getAttribute(listenerAttr) === 'true') return;
                                                form.setAttribute(listenerAttr, 'true');

                                                form.addEventListener('submit', event => {
                                                    try {
                                                        const actionUrl = form.getAttribute('action');
                                                        const dataUrl = form.getAttribute('data-url');
                                                        if ((!actionUrl || actionUrl === '#') && (!dataUrl || dataUrl === '#')) {
                                                            event.preventDefault();
                                                            const msg = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = 'toast-container';
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
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                        }
                                                    } catch {}
                                                });

                                                const observer = new MutationObserver(() => {
                                                    if (!document.getElementById('{{ $formId }}')) observer.disconnect();
                                                });
                                                observer.observe(document.body, { childList: true, subtree: true });
                                            })();
                                        </script>
                                    @endpush
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

