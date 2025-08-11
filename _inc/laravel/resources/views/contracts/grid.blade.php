@php
    use App\Config\Constants\{ExtendingLayoutsConstants,StacksConstants,ViewClassNamesConstants as VC,YieldingConstants,ViewsConstants};
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Contract') }}
@endsection
@push(StacksConstants::ADM_SCR_PG)
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"{{ Route::has('dashboard') ? '' : ' aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Contract') }}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @php
            $ctcIndexRoute      = Route::has(ViewsConstants::CTC . '.index')
                ? route(ViewsConstants::CTC . '.index')
                : '#';
            $ctcIndexLinkId     = 'ctc-index-link';
            $ctcIndexGuardMsg   = Utility::fetchLinkMessage(
                $lang,
                ViewsConstants::CTC,
                'contract_index_route_unavailable'
            ) ?? 'List view route for Contracts is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a
            id="{{ $ctcIndexLinkId }}"
            href="{{ $ctcIndexRoute }}"
            data-url="{{ $ctcIndexRoute }}"
            data-guard-msg="{{ $ctcIndexGuardMsg }}"
            data-bs-toggle="tooltip"
            title="{{ __('List View') }}"
            class="{{ VC::BT_SM_PM }}"
        >
            <i class="{{ VC::TI_LT }}"></i>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer>
                (() => {
                    const link = document.getElementById('{{ $ctcIndexLinkId }}');
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
        @if($user?->type == 'company')
            @php
                $contractCreateRoute   = Route::has(ViewsConstants::CTC . '.create')
                    ? route(ViewsConstants::CTC . '.create')
                    : '#';
                $contractCreateBtnId   = 'contract-create-btn';
                $contractCreateGuardMsg = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::CTC,
                    'create_contract_route_unavailable'
                ) ?? 'Create new contract route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a
                id="{{ $contractCreateBtnId }}"
                href="#"
                data-url="{{ $contractCreateRoute }}"
                data-guard-msg="{{ $contractCreateGuardMsg }}"
                data-size="md"
                data-ajax-popup="true"
                data-bs-toggle="tooltip"
                title="{{ __('Create New Contract') }}"
                class="{{ VC::BT_SM_PM }}"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        const btn = document.getElementById('{{ $contractCreateBtnId }}');
                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                        btn.setAttribute('data-listener-active', 'true');
                        btn.addEventListener('click', event => {
                            try {
                                const url = btn.getAttribute('data-url');
                                if (!url || url === '#') {
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
                                    return;
                                }
                            } catch (e) {}
                        });
                    })();
                </script>
            @endpush
        @endif
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        @foreach($contracts as $contract)
            <div class="{{ VC::CM3 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-header">
                        @php
                            $showRoute      = Route::has(ViewsConstants::CTC . '.show')
                                ? route(ViewsConstants::CTC . '.show', $contract->id)
                                : '#';
                            $showLinkId     = 'contract-show-link-' . $contract->id;
                            $showGuardMsg   = Utility::fetchLinkMessage(
                                $lang,
                                ViewsConstants::CTC,
                                'contract_show_route_unavailable'
                            ) ?? 'Contract view route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        <a
                            id="{{ $showLinkId }}"
                            href="{{ $showRoute }}"
                            data-url="{{ $showRoute }}"
                            data-guard-msg="{{ $showGuardMsg }}"
                            class="mb-0"
                        >
                            {{ $contract->subject }}
                        </a>
                        @push(StacksConstants::ADM_SCR_PG)
                            <script defer>
                                (() => {
                                    const link = document.getElementById('{{ $showLinkId }}');
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
                        @if($user?->type == 'company')
                            <div class="card-header-right">
                                <div class="btn-group card-option">
                                    <button type="button" class="{{ VC::BT }} dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="{{ VC::TD_DOTV }}"></i>
                                    </button>
                                    <div class="{{ VC::DRP_MN_EM }}">
                                        @php
                                            $editRoute    = Route::has(ViewsConstants::CTC . '.edit')
                                                ? route(ViewsConstants::CTC . '.edit', $contract->id)
                                                : '#';
                                            $editLinkId   = 'contract-edit-link-' . $contract->id;
                                            $editGuardMsg = Utility::fetchLinkMessage(
                                                $lang,
                                                ViewsConstants::CTC,
                                                'contract_edit_route_unavailable'
                                            ) ?? 'Edit route for Contracts is unavailable. Please contact technical support or your domain administrator.';
                                        @endphp
                                        <a
                                            id="{{ $editLinkId }}"
                                            href="{{ $editRoute }}"
                                            data-url="{{ $editRoute }}"
                                            data-guard-msg="{{ $editGuardMsg }}"
                                            data-size="md"
                                            data-ajax-popup="true"
                                            class="dropdown-item"
                                        >
                                            <i class="{{ VC::TI_PC }}"></i><span>{{ __('Edit') }}</span>
                                        </a>
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (() => {
                                                    const link = document.getElementById('{{ $editLinkId }}');
                                                    if (!link || link.getAttribute('data-event-alias') === 'true') return;
                                                    link.setAttribute('data-event-alias', 'true');
                                                    link.addEventListener('click', event => {
                                                        try {
                                                            const url = link.getAttribute('data-url');
                                                            if (!url || url === '#') {
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
                                                            }
                                                        } catch (e) {}
                                                    });
                                                })();
                                            </script>
                                        @endpush
                                        @php
                                            $destroyRoute     = Route::has(ViewsConstants::CTC . '.destroy')
                                                ? route(ViewsConstants::CTC . '.destroy', $contract->id)
                                                : '#';
                                            $destroyLinkId    = 'contract-destroy-link-' . $contract->id;
                                            $destroyFormId    = 'delete-form-' . $contract->id;
                                            $destroyGuardMsg  = Utility::fetchLinkMessage(
                                                $lang,
                                                ViewsConstants::CTC,
                                                'contract_destroy_route_unavailable'
                                            ) ?? 'Delete route for Contracts is unavailable. Please contact technical support or your domain administrator.';
                                        @endphp
                                        {!! Collective\Html\FormFacade::open([
                                            'method' => 'DELETE',
                                            'route'  => [ViewsConstants::CTC . '.destroy', $contract->id],
                                            'id'     => $destroyFormId,
                                            'data-url'       => $destroyRoute,
                                            'data-guard-msg' => $destroyGuardMsg,
                                        ]) !!}
                                            <a
                                                id="{{ $destroyLinkId }}"
                                                href="#!"
                                                class="dropdown-item bs-pass-para"
                                                data-url="{{ $destroyRoute }}"
                                                data-guard-msg="{{ $destroyGuardMsg }}"
                                                data-event-alias="false"
                                            >
                                                <i class="{{ VC::TI_ARC }}"></i><span>{{ __('Delete') }}</span>
                                            </a>
                                        {!! Collective\Html\FormFacade::close() !!}
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (() => {
                                                    const link = document.getElementById('{{ $destroyLinkId }}');
                                                    if (!link || link.getAttribute('data-event-alias') === 'true') return;
                                                    link.setAttribute('data-event-alias', 'true');
                                                    link.addEventListener('click', event => {
                                                        try {
                                                            const url = link.getAttribute('data-url');
                                                            if (!url || url === '#') {
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
                                                                return;
                                                            }
                                                        } catch (e) {}
                                                    });
                                                })();
                                            </script>
                                        @endpush
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="card-body py-3 flex-grow-1">
                        <p class="{{ VC::TXSM }} {{ VC::MB0 }}">{{ $contract->description }}</p>
                    </div>
                    <div class="card-footer py-0">
                        <ul class="{{ VC::LG_FLSH }}">
                            <li class="{{ VC::LGI }} px-0">
                                <div class="{{ VC::R_ALC }}">
                                    <div class="col-6"><span class="{{ VC::FM_LB }}">{{ __('Contract Type') }}:</span></div>
                                    <div class="col-6 text-end">
                                        <span class="{{ VC::BDG }} bg-secondary p-2 px-3 rounded">{{ $contract->types->name ?? '' }}</span>
                                    </div>
                                </div>
                            </li>
                            <li class="{{ VC::LGI }} px-0">
                                <div class="{{ VC::R_ALC }}">
                                    <div class="col-6"><span class="{{ VC::FM_LB }}">{{ __('Contract Value') }}:</span></div>
                                    <div class="col-6 text-end">
                                        <span class="{{ VC::BDG }} bg-secondary p-2 px-3 rounded">{{ $user?->priceFormat($contract->value) }}</span>
                                    </div>
                                </div>
                            </li>
                            @if($user?->type != 'client')
                                <li class="{{ VC::LGI }} px-0">
                                    <div class="{{ VC::R_ALC }}">
                                        <div class="col-6"><span class="{{ VC::FM_LB }}">{{ __('Client') }}:</span></div>
                                        <div class="col-6 text-end">{{ $contract->clients->name ?? '' }}</div>
                                    </div>
                                </li>
                            @endif
                            <li class="{{ VC::LGI }} px-0">
                                <div class="{{ VC::R_ALC }}">
                                    <div class="col-6">
                                        <small>{{ __('Start Date') }}:</small>
                                        <div class="{{ VC::H6 }} {{ VC::MB0 }}">{{ $user?->dateFormat($contract->start_date) }}</div>
                                    </div>
                                    <div class="col-6">
                                        <small>{{ __('End Date') }}:</small>
                                        <div class="{{ VC::H6 }} {{ VC::MB0 }}">{{ $user?->dateFormat($contract->end_date) }}</div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
