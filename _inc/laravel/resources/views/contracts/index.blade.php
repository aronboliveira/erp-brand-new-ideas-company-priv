@php
    use App\Config\Constants\{ExtendingLayoutsConstants,StacksConstants,ViewClassNamesConstants as VC,YieldingConstants,ViewsConstants};
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth,Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
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
            $gridRoute          = Route::has(ViewsConstants::CTC . '.grid')
                ? route(ViewsConstants::CTC . '.grid')
                : '#';
            $gridLinkId         = 'contract-grid-view-link';
            $gridGuardMsg       = Utility::fetchLinkMessage(
                $lang,
                ViewsConstants::CTC,
                'contract_grid_route_unavailable'
            ) ?? 'Grid view route for Contracts is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a
            id="{{ $gridLinkId }}"
            href="{{ $gridRoute }}"
            data-url="{{ $gridRoute }}"
            data-guard-msg="{{ $gridGuardMsg }}"
            data-bs-toggle="tooltip"
            title="{{ __('Grid View') }}"
            class="{{ VC::BT_SM_PM }}"
        >
            <i class="ti ti-layout-grid"></i>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer>
                (() => {
                    const link = document.getElementById('{{ $gridLinkId }}');
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
        <div class="col-xl-12">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th scope="col">{{ __('#') }}</th>
                                    <th scope="col">{{ __('Subject') }}</th>
                                    @if($user?->type != 'client')
                                        <th scope="col">{{ __('Client') }}</th>
                                    @endif
                                    <th scope="col">{{ __('Project') }}</th>
                                    <th scope="col">{{ __('Contract Type') }}</th>
                                    <th scope="col">{{ __('Contract Value') }}</th>
                                    <th scope="col">{{ __('Start Date') }}</th>
                                    <th scope="col">{{ __('End Date') }}</th>
                                    <th scope="col">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contracts as $contract)
                                    <tr class="font-style">
                                        <td>
                                            @php
                                                $contractShowRoute      = Route::has(ViewsConstants::CTC . '.show')
                                                    ? route(ViewsConstants::CTC . '.show', $contract->id)
                                                    : '#';
                                                $contractShowLinkId     = 'contract-show-link-' . $contract->id;
                                                $contractShowGuardMsg   = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::CTC,
                                                    'contract_show_route_unavailable'
                                                ) ?? 'Contract view route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <a
                                                id="{{ $contractShowLinkId }}"
                                                href="{{ $contractShowRoute }}"
                                                class="{{ VC::BT_OUTPM }}"
                                                data-url="{{ $contractShowRoute }}"
                                                data-guard-msg="{{ $contractShowGuardMsg }}"
                                            >
                                                {{ $user?->contractNumberFormat($contract->id) }}
                                            </a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        const link = document.getElementById('{{ $contractShowLinkId }}');
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
                                        <td>{{ $contract->subject }}</td>
                                        @if($user?->type != 'client')
                                            <td>{{ $contract->clients->name ?? '-' }}</td>
                                        @endif
                                        <td>{{ $contract->projects->project_name ?? '-' }}</td>
                                        <td>{{ $contract->types->name ?? '' }}</td>
                                        <td>{{ $user?->priceFormat($contract->value) }}</td>
                                        <td>{{ $user?->dateFormat($contract->start_date) }}</td>
                                        <td>{{ $user?->dateFormat($contract->end_date) }}</td>
                                        <td class="action">
                                            @if($user?->type == 'company' && $contract->status == 'accept')
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    @php
                                                        $copyRoute       = Route::has(ViewsConstants::CTC . '.copy')
                                                            ? route(ViewsConstants::CTC . '.copy', $contract->id)
                                                            : '#';
                                                        $copyBtnId       = 'contract-copy-btn-' . $contract->id;
                                                        $copyGuardMsg    = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::CTC,
                                                            'contract_copy_route_unavailable'
                                                        ) ?? 'Copy Contract route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <div class="{{ VC::ACT_BTN_PRIM }}">
                                                        <a
                                                            id="{{ $copyBtnId }}"
                                                            href="#"
                                                            data-size="lg"
                                                            data-url="{{ $copyRoute }}"
                                                            data-ajax-popup="true"
                                                            data-title="{{ __('Copy Contract') }}"
                                                            data-guard-msg="{{ $copyGuardMsg }}"
                                                            class="{{ VC::BT_SM_FL_CT }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Duplicate') }}"
                                                        >
                                                            <i class="ti ti-copy"></i>
                                                        </a>
                                                    </div>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                const btn = document.getElementById('{{ $copyBtnId }}');
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
                                                </div>
                                            @endif
                                            @can('show contract')
                                                <div class="{{ VC::ACT_BTN_WRN }}">
                                                    <a
                                                        id="{{ $contractShowLinkId }}"
                                                        href="{{ $contractShowRoute }}"
                                                        class="{{ VC::BT_SM_FL_CT }}"
                                                        data-url="{{ $contractShowRoute }}"
                                                        data-guard-msg="{{ $contractShowGuardMsg }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('View') }}"
                                                    >
                                                        <i class="{{ VC::TI_EYE_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('edit contract')
                                                @php
                                                    $contractEditRoute       = Route::has(ViewsConstants::CTC . '.edit')
                                                        ? route(ViewsConstants::CTC . '.edit', $contract->id)
                                                        : '#';
                                                    $contractEditLinkId      = 'contract-edit-link-' . $contract->id;
                                                    $contractEditGuardMsg    = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::CTC,
                                                        'contract_edit_route_unavailable'
                                                    ) ?? 'Contract edit route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <div class="{{ VC::ACT_BTN_INF }}">
                                                    <a
                                                        id="{{ $contractEditLinkId }}"
                                                        href="{{ $contractEditRoute }}"
                                                        class="{{ VC::BT_SM_FL_CT }}"
                                                        data-url="{{ $contractEditRoute }}"
                                                        data-guard-msg="{{ $contractEditGuardMsg }}"
                                                        data-ajax-popup="true"
                                                        data-size="md"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        data-title="{{ __('Edit Contract') }}"
                                                    >
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script defer>
                                                        (() => {
                                                            const link = document.getElementById('{{ $contractEditLinkId }}');
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
                                            @endcan
                                            @can('delete contract')
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    @php
                                                        $contractDestroyRoute   = Route::has(ViewsConstants::CTC.'.destroy')
                                                            ? route(ViewsConstants::CTC.'.destroy', $contract->id)
                                                            : '#';
                                                        $destroyFormId          = 'contract-destroy-form-'.$contract->id;
                                                        $destroyBtnId           = 'contract-destroy-btn-'.$contract->id;
                                                        $contractDestroyMsg     = Utility::fetchLinkMessage(
                                                            $lang,
                                                            ViewsConstants::CTC,
                                                            'contract_destroy_route_unavailable'
                                                        ) ?? 'Contract destroy route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    {!! Collective\Html\FormFacade::open([
                                                        'url'            => $contractDestroyRoute,
                                                        'method'         => 'DELETE',
                                                        'id'             => $destroyFormId,
                                                        'data-url'       => $contractDestroyRoute,
                                                        'data-guard-msg' => $contractDestroyMsg,
                                                    ]) !!}
                                                        <a
                                                            id="{{ $destroyBtnId }}"
                                                            href="#"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-url="{{ $contractDestroyRoute }}"
                                                            data-guard-msg="{{ $contractDestroyMsg }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                        >
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                const btn = document.getElementById('{{ $destroyBtnId }}');
                                                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                btn.setAttribute('data-listener-active', 'true');
                                                                btn.addEventListener('click', event => {
                                                                    try {
                                                                        const url = btn.getAttribute('data-url');
                                                                        if (url && url !== '#') return;
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
                                                </div>
                                            @endcan
                                        </td>
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
