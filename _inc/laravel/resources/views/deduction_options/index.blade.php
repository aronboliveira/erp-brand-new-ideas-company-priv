@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Config\Constants\ViewsConstants as VW;
    use Illuminate\Support\Facades\Route;

    $list = (is_array($deductionoptions ?? null) || ($deductionoptions ?? null) instanceof \Illuminate\Support\Collection)
        ? $deductionoptions
        : [];
    $langValue = $lang ?? (class_exists(Utility::class) ? Utility::fetchUserLang() : null);
    $dashboardBaseRouteName     = 'dashboard';
    $dashboardKebabRouteName    = Str::kebab($dashboardBaseRouteName);
    $dashboardResolvedRouteName = Route::has($dashboardBaseRouteName)
        ? $dashboardBaseRouteName
        : (Route::has($dashboardKebabRouteName) ? $dashboardKebabRouteName : null);
    $dashboardUrl               = $dashboardResolvedRouteName ? route($dashboardResolvedRouteName) : '#';
    $dashboardGuardMessage      = (class_exists(Utility::class)
        ? Utility::fetchLinkMessage($langValue, 'generics', 'dashboard_unavailable')
        : null) ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';
    $dashboardLinkId            = 'breadcrumb-dashboard-link';
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Deduction Option') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a id="{{ $dashboardLinkId }}"
           href="{{ $dashboardUrl }}"
           data-url="{{ $dashboardUrl }}"
           data-guard-msg="{{ $dashboardGuardMessage }}"
           data-sv-localized="true"
           {{ $dashboardResolvedRouteName ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Deduction Option') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create document type')
            @php
                $deductionOptionCreateBaseRouteName   = VW::DDT_OPT.'.create';
                $deductionOptionCreateKebabRouteName  = Str::kebab($deductionOptionCreateBaseRouteName);
                $deductionOptionCreateResolvedName    = Route::has($deductionOptionCreateBaseRouteName)
                    ? $deductionOptionCreateBaseRouteName
                    : (Route::has($deductionOptionCreateKebabRouteName) ? $deductionOptionCreateKebabRouteName : null);
                $deductionOptionCreateUrl             = $deductionOptionCreateResolvedName ? route($deductionOptionCreateResolvedName) : '#';
                $deductionOptionCreateGuardMessage    = (class_exists(Utility::class)
                    ? Utility::fetchLinkMessage($langValue, VW::DDT_OPT, 'create_deduction_option_route_unavailable')
                    : null) ?? 'Create deduction option route is unavailable. Please contact technical support or your domain administrator.';
                $deductionOptionCreateLinkId          = 'deduction-option-create-link';
            @endphp
            <a id="{{ $deductionOptionCreateLinkId }}"
               href="{{ $deductionOptionCreateUrl }}"
               data-url="{{ $deductionOptionCreateUrl }}"
               data-guard-msg="{{ $deductionOptionCreateGuardMessage }}"
               data-sv-localized="true"
               data-ajax-popup="true"
               data-title="{{ __('Create New Deduction Option') }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               class="{{ VC::BT_SM_PM }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
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
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Deduction Option') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @forelse ($list as $deductionoption)
                                    @php
                                        $dedOptIdValue = (string) ($deductionoption->id ?? '');
                                    @endphp
                                    <tr>
                                        <td>{{ !empty($deductionoption->name) ? $deductionoption->name : __('No name available for Deduction option') }}</td>
                                        <td>
                                            @can('edit deduction option')
                                                @php
                                                    $deductionOptionEditBaseRouteName   = VW::DDT_OPT.'.edit';
                                                    $deductionOptionEditKebabRouteName  = Str::kebab($deductionOptionEditBaseRouteName);
                                                    $deductionOptionEditResolvedName    = Route::has($deductionOptionEditBaseRouteName)
                                                        ? $deductionOptionEditBaseRouteName
                                                        : (Route::has($deductionOptionEditKebabRouteName) ? $deductionOptionEditKebabRouteName : null);
                                                    $deductionOptionEditUrl             = ($deductionOptionEditResolvedName && $dedOptIdValue !== '')
                                                        ? route($deductionOptionEditResolvedName, $dedOptIdValue)
                                                        : '#';
                                                    $deductionOptionEditGuardMessage    = (class_exists(Utility::class)
                                                        ? Utility::fetchLinkMessage($langValue, VW::DDT_OPT, 'edit_deduction_option_route_unavailable')
                                                        : null) ?? 'Edit deduction option route is unavailable. Please contact technical support or your domain administrator.';
                                                    $deductionOptionEditLinkId          = 'deduction-option-edit-link-'.($dedOptIdValue === '' ? 'x' : $dedOptIdValue);
                                                @endphp
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a id="{{ $deductionOptionEditLinkId }}"
                                                       href="{{ $deductionOptionEditUrl }}"
                                                       class="{{ VC::BT_SM_CT }}"
                                                       data-url="{{ $deductionOptionEditUrl }}"
                                                       data-guard-msg="{{ $deductionOptionEditGuardMessage }}"
                                                       data-sv-localized="true"
                                                       data-ajax-popup="true"
                                                       data-title="{{ __('Edit Deduction Option') }}"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ __('Edit') }}">
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan

                                            @can('delete deduction option')
                                                @php
                                                    $deductionOptionDestroyBaseRouteName   = VW::DDT_OPT.'.destroy';
                                                    $deductionOptionDestroyKebabRouteName  = Str::kebab($deductionOptionDestroyBaseRouteName);
                                                    $deductionOptionDestroyResolvedName    = Route::has($deductionOptionDestroyBaseRouteName)
                                                        ? $deductionOptionDestroyBaseRouteName
                                                        : (Route::has($deductionOptionDestroyKebabRouteName) ? $deductionOptionDestroyKebabRouteName : null);
                                                    $deductionOptionDestroyUrl             = ($deductionOptionDestroyResolvedName && $dedOptIdValue !== '')
                                                        ? route($deductionOptionDestroyResolvedName, $dedOptIdValue)
                                                        : '#';
                                                    $deductionOptionDestroyGuardMessage    = (class_exists(Utility::class)
                                                        ? Utility::fetchLinkMessage($langValue, VW::DDT_OPT, 'destroy_deduction_option_route_unavailable')
                                                        : null) ?? 'Delete deduction option route is unavailable. Please contact technical support or your domain administrator.';
                                                    $deductionOptionDeleteFormId           = 'deduction-option-delete-form-'.($dedOptIdValue === '' ? 'x' : $dedOptIdValue);
                                                    $deductionOptionDeleteLinkId           = 'deduction-option-delete-link-'.($dedOptIdValue === '' ? 'x' : $dedOptIdValue);
                                                @endphp
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Collective\Html\FormFacade::open([
                                                        'method'            => 'DELETE',
                                                        'url'               => $deductionOptionDestroyUrl,
                                                        'id'                => $deductionOptionDeleteFormId,
                                                        'data-url'          => $deductionOptionDestroyUrl,
                                                        'data-guard-msg'    => $deductionOptionDestroyGuardMessage,
                                                        'data-sv-localized' => 'true',
                                                    ]) !!}
                                                        <a id="{{ $deductionOptionDeleteLinkId }}"
                                                           href="#"
                                                           class="{{ VC::BT_SM_CT_PR }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('Delete') }}"
                                                           data-confirm="{{ __(class_exists(Utility::class) ? (Utility::fetchLinkMessage($langValue, 'generics', 'are_you_sure') ?? 'Are You Sure?') : 'Are You Sure?') }}|{{ __(class_exists(Utility::class) ? (Utility::fetchLinkMessage($langValue, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') : 'This action can not be undone. Do you want to continue?') }}"
                                                           data-confirm-yes="document.getElementById('{{ $deductionOptionDeleteFormId }}').submit();">
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                </div>
                                            @endcan
                                        </td>
                                    </tr>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                try {
                                                    const guardClick = (el, fallbackMsg) => {
                                                        try {
                                                            if (!el) { return; }
                                                            if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                            el.setAttribute('data-listener-active','true');
                                                            el.addEventListener('click',(e) => {
                                                                try {
                                                                    const href = el.getAttribute('href') ?? '#';
                                                                    const url  = el.getAttribute('data-url') ?? href ?? '#';
                                                                    if (url !== '#' && href !== '#') { return; }
                                                                    e.preventDefault();
                                                                    const msg = el.getAttribute('data-guard-msg') ?? fallbackMsg;
                                                                    const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                                                    let container = document.getElementById('toast-container');
                                                                    if (!container) {
                                                                        container = document.createElement('div');
                                                                        container.id = 'toast-container';
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (hasBootstrap) {
                                                                        const toast = document.createElement('div');
                                                                        toast.className = 'toast';
                                                                        toast.setAttribute('role','alert');
                                                                        toast.setAttribute('aria-live','assertive');
                                                                        toast.setAttribute('aria-atomic','true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toast.appendChild(body);
                                                                        container.appendChild(toast);
                                                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    el.setAttribute('data-failed-route','true');
                                                                } catch (err) {}
                                                            });
                                                        } catch (err) {}
                                                    };

                                                    const guardForm = (fm, fallbackMsg) => {
                                                        try {
                                                            if (!fm) { return; }
                                                            if (fm.getAttribute('data-submit-guarded') === 'true') { return; }
                                                            fm.setAttribute('data-submit-guarded','true');
                                                            fm.addEventListener('submit',(e) => {
                                                                try {
                                                                    const action = fm.getAttribute('action') ?? '#';
                                                                    const url    = fm.getAttribute('data-url') ?? action ?? '#';
                                                                    if (url !== '#' && action !== '#') { return; }
                                                                    e.preventDefault();
                                                                    const msg = fm.getAttribute('data-guard-msg') ?? fallbackMsg;
                                                                    const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                                                    let container = document.getElementById('toast-container');
                                                                    if (!container) {
                                                                        container = document.createElement('div');
                                                                        container.id = 'toast-container';
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (hasBootstrap) {
                                                                        const toast = document.createElement('div');
                                                                        toast.className = 'toast';
                                                                        toast.setAttribute('role','alert');
                                                                        toast.setAttribute('aria-live','assertive');
                                                                        toast.setAttribute('aria-atomic','true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toast.appendChild(body);
                                                                        container.appendChild(toast);
                                                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    fm.setAttribute('data-failed-route','true');
                                                                } catch (err) {}
                                                            });
                                                        } catch (err) {}
                                                    };

                                                    guardClick(
                                                        document.getElementById('{{ $deductionOptionEditLinkId ?? '' }}'),
                                                        'Edit deduction option route is unavailable. Please contact technical support or your domain administrator.'
                                                    );

                                                    guardClick(
                                                        document.getElementById('{{ $deductionOptionDeleteLinkId ?? '' }}'),
                                                        'Delete deduction option route is unavailable. Please contact technical support or your domain administrator.'
                                                    );

                                                    guardForm(
                                                        document.getElementById('{{ $deductionOptionDeleteFormId ?? '' }}'),
                                                        'Delete deduction option route is unavailable. Please contact technical support or your domain administrator.'
                                                    );
                                                } catch (err) {}
                                            })();
                                        </script>
                                    @endpush
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">
                                            {{ __('No deduction options found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            try {
                const el = document.getElementById('{{ $dashboardLinkId }}');
                if (!el) { return; }
                if (el.getAttribute('data-listener-active') === 'true') { return; }
                el.setAttribute('data-listener-active','true');
                el.addEventListener('click',(e) => {
                    try {
                        const href = el.getAttribute('href') ?? '#';
                        const url  = el.getAttribute('data-url') ?? href ?? '#';
                        if (url !== '#' && href !== '#') { return; }
                        e.preventDefault();
                        const msg = el.getAttribute('data-guard-msg') ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';
                        const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                        let container = document.getElementById('toast-container');
                        if (!container) {
                            container = document.createElement('div');
                            container.id = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (hasBootstrap) {
                            const toast = document.createElement('div');
                            toast.className = 'toast';
                            toast.setAttribute('role','alert');
                            toast.setAttribute('aria-live','assertive');
                            toast.setAttribute('aria-atomic','true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;
                            toast.appendChild(body);
                            container.appendChild(toast);
                            bootstrap.Toast.getOrCreateInstance(toast).show();
                        } else {
                            alert(msg);
                        }
                        el.setAttribute('data-failed-route','true');
                    } catch (err) {}
                });

                @can('create document type')
                    const createLink = document.getElementById('{{ $deductionOptionCreateLinkId ?? '' }}');
                    if (createLink && createLink.getAttribute('data-listener-active') !== 'true') {
                        createLink.setAttribute('data-listener-active','true');
                        createLink.addEventListener('click',(e) => {
                            try {
                                const href = createLink.getAttribute('href') ?? '#';
                                const url  = createLink.getAttribute('data-url') ?? href ?? '#';
                                if (url !== '#' && href !== '#') { return; }
                                e.preventDefault();
                                const msg = createLink.getAttribute('data-guard-msg') ?? 'Create deduction option route is unavailable. Please contact technical support or your domain administrator.';
                                const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                let container = document.getElementById('toast-container');
                                if (!container) {
                                    container = document.createElement('div');
                                    container.id = 'toast-container';
                                    document.body.appendChild(container);
                                }
                                if (hasBootstrap) {
                                    const toast = document.createElement('div');
                                    toast.className = 'toast';
                                    toast.setAttribute('role','alert');
                                    toast.setAttribute('aria-live','assertive');
                                    toast.setAttribute('aria-atomic','true');
                                    const body = document.createElement('div');
                                    body.className = 'toast-body';
                                    body.textContent = msg;
                                    toast.appendChild(body);
                                    container.appendChild(toast);
                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                } else {
                                    alert(msg);
                                }
                                createLink.setAttribute('data-failed-route','true');
                            } catch (err) {}
                        });
                    }
                @endcan
            } catch (err) {}
        })();
    </script>
@endpush
