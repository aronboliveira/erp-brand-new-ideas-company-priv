@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{Proposal,Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Crypt, Gate, Route};
    use Illuminate\Support\{Collection, Str};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Proposals')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Proposal')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @php
            $proposalExportBaseName     = VW::PPS.'.export';
            $proposalExportKebabName    = Str::kebab($proposalExportBaseName);
            $proposalExportResolvedName = Route::has($proposalExportBaseName)
                ? $proposalExportBaseName
                : (Route::has($proposalExportKebabName) ? $proposalExportKebabName : null);
            $proposalExportUrl          = $proposalExportResolvedName ? route($proposalExportResolvedName) : '#';
            $proposalExportGuardMsg     = Utility::fetchLinkMessage($lang, VW::PPS, 'export_proposal_route_unavailable') ?? 'Export proposal route is unavailable. Please contact technical support or your domain administrator.';
            $proposalExportLinkId       = 'proposal-export-link';
        @endphp
        <a href="{{ $proposalExportUrl }}"
        id="{{ $proposalExportLinkId }}"
        class="{{ VC::BT_SM_PM }}"
        data-url="{{ $proposalExportUrl }}"
        data-guard-msg="{{ $proposalExportGuardMsg }}"
        data-bs-toggle="tooltip"
        title="{{ __('Export') }}">
            <i class="{{ VC::TI_EXP }}"></i>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer>
                (() => {
                    try {
                        const l = document.getElementById('{{ $proposalExportLinkId }}');
                        if (!l || l.getAttribute('data-listener-active') === 'true') return;
                        l.setAttribute('data-listener-active', 'true');
                        l.addEventListener('click', e => {
                            try {
                                const href = l.getAttribute('href') || '#';
                                const url = l.getAttribute('data-url') || href || '#';
                                if (href !== '#' || url !== '#') return;
                                e.preventDefault();
                                const msg = l.getAttribute('data-guard-msg') || 'Export proposal route is unavailable. Please contact technical support or your domain administrator.';
                                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                let container = document.getElementById('toast-container');
                                if (!container) {
                                    container = document.createElement('div');
                                    container.id = 'toast-container';
                                    document.body.appendChild(container);
                                }
                                if (hasBootstrap) {
                                    const toast = document.createElement('div');
                                    toast.className = 'toast';
                                    toast.setAttribute('role', 'alert');
                                    toast.setAttribute('aria-live', 'assertive');
                                    toast.setAttribute('aria-atomic', 'true');
                                    const body = document.createElement('div');
                                    body.className = 'toast-body';
                                    body.textContent = msg;
                                    toast.appendChild(body);
                                    container.appendChild(toast);
                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                } else {
                                    alert(msg);
                                }
                                l.setAttribute('data-failed-route', 'true');
                            } catch (err) {}
                        });
                    } catch (error) {}
                })();
            </script>
        @endpush
        @can('create proposal')
            @php
                $proposalCreateBaseName     = ViewsConstants::PPS.'.create';
                $proposalCreateKebabName    = Str::kebab($proposalCreateBaseName);
                $proposalCreateResolvedName = Route::has($proposalCreateBaseName)
                    ? $proposalCreateBaseName
                    : (Route::has($proposalCreateKebabName) ? $proposalCreateKebabName : null);
                $proposalCreateParam        = 0;
                $proposalCreateUrl          = $proposalCreateResolvedName ? route($proposalCreateResolvedName, $proposalCreateParam) : '#';
                $proposalCreateGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'create_proposal_route_unavailable') ?? 'Create proposal route is unavailable. Please contact technical support or your domain administrator.';
                $proposalCreateLinkId       = 'proposal-create-link';
            @endphp
            <a href="{{ $proposalCreateUrl }}"
            id="{{ $proposalCreateLinkId }}"
            class="{{ VC::BT_SM_PM }}"
            data-url="{{ $proposalCreateUrl }}"
            data-guard-msg="{{ $proposalCreateGuardMsg }}"
            data-bs-toggle="tooltip"
            title="{{ __('Create') }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        try {
                            const l = document.getElementById('{{ $proposalCreateLinkId }}');
                            if (!l || l.getAttribute('data-listener-active') === 'true') return;
                            l.setAttribute('data-listener-active', 'true');
                            l.addEventListener('click', e => {
                                try {
                                    const href = l.getAttribute('href') || '#';
                                    const url  = l.getAttribute('data-url') || href || '#';
                                    if (href !== '#' || url !== '#') return;
                                    e.preventDefault();
                                    const msg = l.getAttribute('data-guard-msg') || 'Create proposal route is unavailable. Please contact technical support or your domain administrator.';
                                    const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                    let container = document.getElementById('toast-container');
                                    if (!container) {
                                        container = document.createElement('div');
                                        container.id = 'toast-container';
                                        document.body.appendChild(container);
                                    }
                                    if (hasBootstrap) {
                                        const toast = document.createElement('div');
                                        toast.className = 'toast';
                                        toast.setAttribute('role', 'alert');
                                        toast.setAttribute('aria-live', 'assertive');
                                        toast.setAttribute('aria-atomic', 'true');
                                        const body = document.createElement('div');
                                        body.className = 'toast-body';
                                        body.textContent = msg;
                                        toast.appendChild(body);
                                        container.appendChild(toast);
                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                    } else {
                                        alert(msg);
                                    }
                                    l.setAttribute('data-failed-route', 'true');
                                } catch (err) {}
                            });
                        } catch (error) {}
                    })();
                </script>
            @endpush
        @endcan
    </div>
@endsection
@push(StacksConstants::ADM_CSS)
@endpush
@push(StacksConstants::ADM_SCR_PG)
@endpush
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        @php
                            $proposalIndexBaseName     = 'proposal.index';
                            $proposalIndexKebabName    = Str::kebab($proposalIndexBaseName);
                            $proposalIndexResolvedName = Route::has($proposalIndexBaseName)
                                ? $proposalIndexBaseName
                                : (Route::has($proposalIndexKebabName) ? $proposalIndexKebabName : null);
                            $proposalIndexUrl          = $proposalIndexResolvedName ? route($proposalIndexResolvedName) : '#';
                            $proposalIndexGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'proposal_index_route_unavailable') ?? 'Index proposal route is unavailable. Please contact technical support or your domain administrator.';
                            $proposalIndexFormId       = 'frm_submit';
                        @endphp
                        {!! Form::open([
                            'url'            => $proposalIndexUrl,
                            'method'         => 'get',
                            'accept-charset' => 'UTF-8',
                            'id'             => $proposalIndexFormId,
                            'data-url'       => $proposalIndexUrl,
                            'data-guard-msg' => $proposalIndexGuardMsg
                        ]) !!}
                            @push(StacksConstants::ADM_SCR_PG)
                                <script defer>
                                    (() => {
                                        try {
                                            const f = document.getElementById('{{ $proposalIndexFormId }}');
                                            if (!f || f.getAttribute('data-listener-active') === 'true') return;
                                            f.setAttribute('data-listener-active', 'true');
                                            f.addEventListener('submit', e => {
                                                try {
                                                    const url = f.getAttribute('data-url') || '#';
                                                    const action = f.getAttribute('action') || '#';
                                                    if (url !== '#' || action !== '#') return;
                                                    e.preventDefault();
                                                    const msg = f.getAttribute('data-guard-msg') || 'Index proposal route is unavailable. Please contact technical support or your domain administrator.';
                                                    const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (hasBootstrap) {
                                                        const toast = document.createElement('div');
                                                        toast.className = 'toast';
                                                        toast.setAttribute('role', 'alert');
                                                        toast.setAttribute('aria-live', 'assertive');
                                                        toast.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toast.appendChild(body);
                                                        container.appendChild(toast);
                                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    f.setAttribute('data-failed-route', 'true');
                                                } catch (err) {}
                                            });
                                        } catch (error) {}
                                    })();
                                </script>
                            @endpush
                            @php
                                $issueDateRaw = data_get(request()->query(), 'issue_date');
                                $issueDateVal = old('issue_date', $issueDateRaw) ?? null;
                                $selectedStatus = data_get(request()->query(), 'status') ?? '';
                                $statusList = (isset($status) && !empty($status) && (is_array($status) || $status instanceof Collection)) ? (is_array($status) ? $status : $status->toArray()) : [];
                                $labelDate = __('Date') ?: __('Failed to get date label');
                                $labelStatus = __('Status') ?: __('Failed to get status label');
                                $applyTitle = __('apply') ?: __('Failed to get action label');
                                $resetTitle = __('Reset') ?: __('Failed to get reset label');
                                $selectStatusText = __('Select Status') ?: __('No status list available');
                                $issueDatePlaceholder = $issueDateVal ?? __('No date available');
                            @endphp
                            <div class="{{ VC::DFL }} {{ VC::ALC }} {{ VC::JCE }}">
                                <div class="col-xl-3 col-lg-3 {{ VC::CM6 }} {{ VC::CS12 }} col-12 me-2">
                                    <div class="btn-box">
                                        {{ Form::label('issue_date', $labelDate, ['class' => VC::FM_LB]) }}
                                        {{ Form::text('issue_date', $issueDateVal, ['class' => VC::FM_CT.' month-btn', 'id' => 'pc-daterangepicker-1', 'placeholder' => $issueDatePlaceholder]) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 {{ VC::CM6 }} {{ VC::CS12 }} col-12">
                                    <div class="btn-box">
                                        {{ Form::label('status', $labelStatus, ['class' => VC::FM_LB]) }}
                                        {{ Form::select('status', ['' => $selectStatusText] + (array) $statusList, $selectedStatus, ['class' => VC::FM_CT_SL.' select']) }}
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT }} {{ VC::FEND }} {{ VC::MS2 }} {{ VC::MT4 }}">
                                    <a href="#" class="{{ VC::BT_SM_PM }}" onclick="document.getElementById('frm_submit').submit(); return false;" data-bs-toggle="tooltip" data-original-title="{{ $applyTitle }}">
                                        <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                    </a>
                                    @php
                                        $productServiceIndexBaseName     = ViewsConstants::PRD_SV.'.index';
                                        $productServiceIndexKebabName    = Str::kebab($productServiceIndexBaseName);
                                        $productServiceIndexResolvedName = Route::has($productServiceIndexBaseName)
                                            ? $productServiceIndexBaseName
                                            : (Route::has($productServiceIndexKebabName) ? $productServiceIndexKebabName : null);
                                        $productServiceIndexUrl          = $productServiceIndexResolvedName ? route($productServiceIndexResolvedName) : '#';
                                        $productServiceIndexGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRD_SV, 'product_service_index_route_unavailable') ?? 'Index product service route is unavailable. Please contact technical support or your domain administrator.';
                                        $productServiceIndexLinkId       = 'product-service-index-reset-link';
                                        $resetTitleText                  = isset($resetTitle) ? $resetTitle : __('Export');
                                    @endphp
                                    <a href="{{ $productServiceIndexUrl }}"
                                    id="{{ $productServiceIndexLinkId }}"
                                    class="{{ VC::BT_SM_DG }}"
                                    data-url="{{ $productServiceIndexUrl }}"
                                    data-guard-msg="{{ $productServiceIndexGuardMsg }}"
                                    data-bs-toggle="tooltip"
                                    title="{{ $resetTitleText }}">
                                        <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                    </a>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                try {
                                                    const l = document.getElementById('{{ $productServiceIndexLinkId }}');
                                                    if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                    l.setAttribute('data-listener-active', 'true');
                                                    l.addEventListener('click', e => {
                                                        try {
                                                            const href = l.getAttribute('href') || '#';
                                                            const url  = l.getAttribute('data-url') || href || '#';
                                                            if (href !== '#' || url !== '#') return;
                                                            e.preventDefault();
                                                            const msg = l.getAttribute('data-guard-msg') || 'Index product service route is unavailable. Please contact technical support or your domain administrator.';
                                                            const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
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
                                                            l.setAttribute('data-failed-route', 'true');
                                                        } catch (err) {}
                                                    });
                                                } catch (error) {}
                                            })();
                                        </script>
                                    @endpush
                                </div>
                            </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @php
        $statusLabels = (isset(Proposal::$statuses) && is_array(Proposal::$statuses)) ? Proposal::$statuses : [];
        $badgeByStatus = [0=>'bg-primary',1=>'bg-info',2=>'bg-success',3=>'bg-warning',4=>'bg-danger'];
    @endphp
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Proposal') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Issue Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if(Gate::check('edit proposal') || Gate::check('delete proposal') || Gate::check('show proposal'))
                                        <th width="10%">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ((isset($proposals) && (is_array($proposals) || $proposals instanceof Collection)) ? $proposals : [] as $proposal)
                                    @php
                                        $itemValid = isset($proposal) && !empty($proposal) && (is_array($proposal) || is_object($proposal));
                                        $pid = $itemValid ? data_get($proposal,'id') : null;
                                        $statusVal = $itemValid ? data_get($proposal,'status') : null;
                                        $validSt = isset($statusVal) && is_numeric($statusVal) && $statusVal >= 0 && $statusVal <= 4;
                                        $badge = $validSt ? ($badgeByStatus[(int)$statusVal] ?? 'bg-secondary') : 'bg-secondary';
                                        $stLabel = $validSt ? __($statusLabels[(int)$statusVal] ?? __('Unknown status')) : __('Unknown status');
                                        $isConvert = (int)(data_get($proposal,'is_convert') ?? 0);
                                        $convertedId = data_get($proposal,'converted_invoice_id');
                                    @endphp
                                    <tr class="font-style">
                                        <td class="Id">
                                            @php
                                                $proposalShowBaseName     = ViewsConstants::PPS.'.show';
                                                $proposalShowKebabName    = Str::kebab($proposalShowBaseName);
                                                $proposalShowResolvedName = Route::has($proposalShowBaseName)
                                                    ? $proposalShowBaseName
                                                    : (Route::has($proposalShowKebabName) ? $proposalShowKebabName : null);
                                                $proposalIdValue          = isset($pid) && !empty($pid) ? $pid : null;
                                                $encryptedProposalId      = $proposalIdValue ? Crypt::encrypt($proposalIdValue) : null;
                                                $proposalShowUrl          = ($proposalShowResolvedName && $encryptedProposalId) ? route($proposalShowResolvedName, $encryptedProposalId) : '#';
                                                $proposalShowGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'show_proposal_route_unavailable') ?? 'Show proposal route is unavailable. Please contact technical support or your domain administrator.';
                                                $proposalShowLinkId       = 'proposal-show-link-'.($proposalIdValue ?? 'x');
                                                $proposalNumberText       = ($user && method_exists($user,'proposalNumberFormat')) ? ($user->proposalNumberFormat(data_get($proposal,'proposal_id')) ?? __('Failed to get proposal number')) : __('Failed to get proposal number');
                                            @endphp
                                            <a href="{{ $proposalShowUrl }}"
                                            id="{{ $proposalShowLinkId }}"
                                            class="{{ VC::BT_OUTPM }}"
                                            data-url="{{ $proposalShowUrl }}"
                                            data-guard-msg="{{ $proposalShowGuardMsg }}">
                                                {{ $proposalNumberText }}
                                            </a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        try {
                                                            const l = document.getElementById('{{ $proposalShowLinkId }}');
                                                            if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                            l.setAttribute('data-listener-active', 'true');
                                                            l.addEventListener('click', e => {
                                                                try {
                                                                    const href = l.getAttribute('href') || '#';
                                                                    const url  = l.getAttribute('data-url') || href || '#';
                                                                    if (href !== '#' || url !== '#') return;
                                                                    e.preventDefault();
                                                                    const msg = l.getAttribute('data-guard-msg') || 'Show proposal route is unavailable. Please contact technical support or your domain administrator.';
                                                                    const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
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
                                                                    l.setAttribute('data-failed-route', 'true');
                                                                } catch (err) {}
                                                            });
                                                        } catch (error) {}
                                                    })();
                                                </script>
                                            @endpush
                                        </td>
                                        <td>{{ data_get($proposal,'category.name') ?? __('No category available') }}</td>
                                        <td>{{ ($user && method_exists($user,'dateFormat') && data_get($proposal,'issue_date')) ? ($user->dateFormat(data_get($proposal,'issue_date')) ?? __('No issue date available')) : __('No issue date available') }}</td>
                                        <td><span class="status_badge badge {{ $badge }} p-2 px-3 rounded">{{ $stLabel }}</span></td>
                                        @if(Gate::check('edit proposal') || Gate::check('delete proposal') || Gate::check('show proposal'))
                                            <td class="Action">
                                                @if($isConvert === 0 && $pid)
                                                    @can('convert invoice')
                                                        <div class="{{ VC::ACT_BTN_WRN }} {{ VC::MS2 }}">
                                                            @php
                                                                $proposalConvertBaseName     = ViewsConstants::PPS.'.convert';
                                                                $proposalConvertKebabName    = Str::kebab($proposalConvertBaseName);
                                                                $proposalConvertResolvedName = Route::has($proposalConvertBaseName)
                                                                    ? $proposalConvertBaseName
                                                                    : (Route::has($proposalConvertKebabName) ? $proposalConvertKebabName : null);
                                                                $proposalIdValue             = isset($pid) && !empty($pid) ? $pid : null;
                                                                $proposalConvertRouteArray   = ($proposalConvertResolvedName && $proposalIdValue) ? [$proposalConvertResolvedName, $proposalIdValue] : ['#'];
                                                                $proposalConvertUrl          = ($proposalConvertResolvedName && $proposalIdValue) ? route($proposalConvertResolvedName, $proposalIdValue) : '#';
                                                                $proposalConvertGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'convert_proposal_to_invoice_route_unavailable') ?? 'Convert proposal to invoice route is unavailable. Please contact technical support or your domain administrator.';
                                                                $proposalConvertFormId       = 'proposal-form-'.($proposalIdValue ?? 'x');
                                                                $proposalConvertLinkId       = 'proposal-convert-link-'.($proposalIdValue ?? 'x');
                                                                $proposalConvertTitle        = __('Convert Invoice');
                                                                $proposalConvertOriginal     = __('Convert to Invoice');
                                                                $proposalConvertConfirmMsg   = __('Do you want to confirm converting to invoice? Press Yes to continue or Cancel to go back');
                                                            @endphp
                                                            {!! Form::open([
                                                                'route'          => $proposalConvertRouteArray,
                                                                'method'         => 'get',
                                                                'accept-charset' => 'UTF-8',
                                                                'id'             => $proposalConvertFormId,
                                                                'data-url'       => $proposalConvertUrl,
                                                                'data-guard-msg' => $proposalConvertGuardMsg
                                                            ]) !!}
                                                                <a href="#"
                                                                id="{{ $proposalConvertLinkId }}"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ $proposalConvertTitle }}"
                                                                data-original-title="{{ $proposalConvertOriginal }}"
                                                                data-form-id="{{ $proposalConvertFormId }}"
                                                                data-url="{{ $proposalConvertUrl }}"
                                                                data-guard-msg="{{ $proposalConvertGuardMsg }}"
                                                                data-confirm="{{ $proposalConvertConfirmMsg }}">
                                                                    <i class="ti ti-exchange {{ VC::TXT_WT }}"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (() => {
                                                                        try {
                                                                            const l = document.getElementById('{{ $proposalConvertLinkId }}');
                                                                            if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                            l.setAttribute('data-listener-active', 'true');
                                                                            l.addEventListener('click', (e) => {
                                                                                try {
                                                                                    e.preventDefault();
                                                                                    const formId = l.getAttribute('data-form-id') || '';
                                                                                    const f = formId ? document.getElementById(formId) : null;
                                                                                    if (!f) return;
                                                                                    const url = l.getAttribute('data-url') || f.getAttribute('data-url') || '#';
                                                                                    const action = f.getAttribute('action') || '#';
                                                                                    if (url === '#' && action === '#') {
                                                                                        const msg = l.getAttribute('data-guard-msg') || f.getAttribute('data-guard-msg') || 'Convert proposal to invoice route is unavailable. Please contact technical support or your domain administrator.';
                                                                                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
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
                                                                                        l.setAttribute('data-failed-route', 'true');
                                                                                        f.setAttribute('data-failed-route', 'true');
                                                                                        return;
                                                                                    }
                                                                                    const confirmMsg = l.getAttribute('data-confirm') || 'Do you want to confirm converting to invoice? Press Yes to continue or Cancel to go back';
                                                                                    if (window.confirm(confirmMsg)) {
                                                                                        f.submit();
                                                                                    }
                                                                                } catch (err) {}
                                                                            });
                                                                        } catch (error) {}
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        </div>
                                                    @endcan
                                                @elseif($isConvert !== 0 && $convertedId)
                                                    @can('show invoice')
                                                        <div class="{{ VC::ACT_BTN_WRN }} {{ VC::MS2 }}">
                                                            @php
                                                                $invoiceShowBaseName     = ViewsConstants::INV.'.show';
                                                                $invoiceShowKebabName    = Str::kebab($invoiceShowBaseName);
                                                                $invoiceShowResolvedName = Route::has($invoiceShowBaseName)
                                                                    ? $invoiceShowBaseName
                                                                    : (Route::has($invoiceShowKebabName) ? $invoiceShowKebabName : null);
                                                                $convertedIdValue        = isset($convertedId) && !empty($convertedId) ? $convertedId : null;
                                                                $encryptedConvertedId    = $convertedIdValue ? Crypt::encrypt($convertedIdValue) : null;
                                                                $invoiceShowUrl          = ($invoiceShowResolvedName && $encryptedConvertedId) ? route($invoiceShowResolvedName, $encryptedConvertedId) : '#';
                                                                $invoiceShowGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::INV, 'invoice_show_route_unavailable') ?? 'Show invoice route is unavailable. Please contact technical support or your domain administrator.';
                                                                $invoiceShowLinkId       = 'invoice-show-link-'.($convertedIdValue ?? 'x');
                                                                $invoiceShowTitle        = __('Already convert to Invoice');
                                                            @endphp
                                                            <a href="{{ $invoiceShowUrl }}"
                                                            id="{{ $invoiceShowLinkId }}"
                                                            class="{{ VC::BT_SM_CT }}"
                                                            data-url="{{ $invoiceShowUrl }}"
                                                            data-guard-msg="{{ $invoiceShowGuardMsg }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ $invoiceShowTitle }}">
                                                                <i class="{{ VC::TI_FL }} {{ VC::TXT_WT }}"></i>
                                                            </a>
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (() => {
                                                                        try {
                                                                            const l = document.getElementById('{{ $invoiceShowLinkId }}');
                                                                            if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                            l.setAttribute('data-listener-active', 'true');
                                                                            l.addEventListener('click', e => {
                                                                                try {
                                                                                    const href = l.getAttribute('href') || '#';
                                                                                    const url  = l.getAttribute('data-url') || href || '#';
                                                                                    if (href !== '#' || url !== '#') return;
                                                                                    e.preventDefault();
                                                                                    const msg = l.getAttribute('data-guard-msg') || 'Show invoice route is unavailable. Please contact technical support or your domain administrator.';
                                                                                    const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
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
                                                                                    l.setAttribute('data-failed-route', 'true');
                                                                                } catch (err) {}
                                                                            });
                                                                        } catch (error) {}
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        </div>
                                                    @endcan
                                                @endif
                                                @can('duplicate proposal')
                                                    @if($pid)
                                                        <div class="action-btn bg-success ms-2">
                                                            @php
                                                                $proposalDuplicateBaseName     = ViewsConstants::PPS.'.duplicate';
                                                                $proposalDuplicateKebabName    = Str::kebab($proposalDuplicateBaseName);
                                                                $proposalDuplicateResolvedName = Route::has($proposalDuplicateBaseName)
                                                                    ? $proposalDuplicateBaseName
                                                                    : (Route::has($proposalDuplicateKebabName) ? $proposalDuplicateKebabName : null);
                                                                $proposalIdValue               = isset($pid) && !empty($pid) ? $pid : null;
                                                                $proposalDuplicateRouteArray   = ($proposalDuplicateResolvedName && $proposalIdValue) ? [$proposalDuplicateResolvedName, $proposalIdValue] : ['#'];
                                                                $proposalDuplicateUrl          = ($proposalDuplicateResolvedName && $proposalIdValue) ? route($proposalDuplicateResolvedName, $proposalIdValue) : '#';
                                                                $proposalDuplicateGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'duplicate_proposal_route_unavailable') ?? 'Duplicate proposal route is unavailable. Please contact technical support or your domain administrator.';
                                                                $proposalDuplicateFormId       = 'duplicate-form-'.($proposalIdValue ?? 'x');
                                                                $proposalDuplicateLinkId       = 'proposal-duplicate-link-'.($proposalIdValue ?? 'x');
                                                                $proposalDuplicateTitle        = __('Duplicate');
                                                                $proposalDuplicateConfirm      = __('Do you want to confirm duplicating this proposal ? Press Yes to continue or Cancel to go back');
                                                            @endphp
                                                            {!! Form::open([
                                                                'route'          => $proposalDuplicateRouteArray,
                                                                'method'         => 'get',
                                                                'accept-charset' => 'UTF-8',
                                                                'id'             => $proposalDuplicateFormId,
                                                                'data-url'       => $proposalDuplicateUrl,
                                                                'data-guard-msg' => $proposalDuplicateGuardMsg
                                                            ]) !!}
                                                                <a href="#"
                                                                id="{{ $proposalDuplicateLinkId }}"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ $proposalDuplicateTitle }}"
                                                                data-form-id="{{ $proposalDuplicateFormId }}"
                                                                data-url="{{ $proposalDuplicateUrl }}"
                                                                data-guard-msg="{{ $proposalDuplicateGuardMsg }}"
                                                                data-confirm="{{ $proposalDuplicateConfirm }}">
                                                                    <i class="ti ti-copy {{ VC::TXT_WT }}"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (() => {
                                                                        try {
                                                                            const l = document.getElementById('{{ $proposalDuplicateLinkId }}');
                                                                            if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                            l.setAttribute('data-listener-active', 'true');
                                                                            l.addEventListener('click', e => {
                                                                                try {
                                                                                    e.preventDefault();
                                                                                    const formId = l.getAttribute('data-form-id') || '';
                                                                                    const f = formId ? document.getElementById(formId) : null;
                                                                                    if (!f) return;
                                                                                    const url = l.getAttribute('data-url') || f.getAttribute('data-url') || '#';
                                                                                    const action = f.getAttribute('action') || '#';
                                                                                    if (url === '#' && action === '#') {
                                                                                        const msg = l.getAttribute('data-guard-msg') || f.getAttribute('data-guard-msg') || 'Duplicate proposal route is unavailable. Please contact technical support or your domain administrator.';
                                                                                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                                        let container = document.getElementById('toast-container');
                                                                                        if (!container) {
                                                                                            container = document.createElement('div');
                                                                                            container.id = 'toast-container';
                                                                                            document.body.appendChild(container);
                                                                                        }
                                                                                        if (hasBootstrap) {
                                                                                            const toast = document.createElement('div');
                                                                                            toast.className = 'toast';
                                                                                            toast.setAttribute('role', 'alert');
                                                                                            toast.setAttribute('aria-live', 'assertive');
                                                                                            toast.setAttribute('aria-atomic', 'true');
                                                                                            const body = document.createElement('div');
                                                                                            body.className = 'toast-body';
                                                                                            body.textContent = msg;
                                                                                            toast.appendChild(body);
                                                                                            container.appendChild(toast);
                                                                                            bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                        } else {
                                                                                            alert(msg);
                                                                                        }
                                                                                        l.setAttribute('data-failed-route', 'true');
                                                                                        f.setAttribute('data-failed-route', 'true');
                                                                                        return;
                                                                                    }
                                                                                    const confirmMsg = l.getAttribute('data-confirm') || 'Do you want to confirm duplicating this proposal ? Press Yes to continue or Cancel to go back';
                                                                                    if (window.confirm(confirmMsg)) {
                                                                                        f.submit();
                                                                                    }
                                                                                } catch (err) {}
                                                                            });
                                                                        } catch (error) {}
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        </div>
                                                    @endif
                                                @endcan
                                                @can('show proposal')
                                                    @if($pid)
                                                        <div class="{{ VC::ACT_BTN_INF }} {{ VC::MS2 }}">
                                                            @php
                                                                $proposalShowBaseName     = ViewsConstants::PPS.'.show';
                                                                $proposalShowKebabName    = Str::kebab($proposalShowBaseName);
                                                                $proposalShowResolvedName = Route::has($proposalShowBaseName)
                                                                    ? $proposalShowBaseName
                                                                    : (Route::has($proposalShowKebabName) ? $proposalShowKebabName : null);
                                                                $proposalIdValue          = isset($pid) && !empty($pid) ? $pid : null;
                                                                $encryptedProposalId      = $proposalIdValue ? Crypt::encrypt($proposalIdValue) : null;
                                                                $proposalShowUrl          = ($proposalShowResolvedName && $encryptedProposalId) ? route($proposalShowResolvedName, $encryptedProposalId) : '#';
                                                                $proposalShowGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'show_proposal_route_unavailable') ?? 'Show proposal route is unavailable. Please contact technical support or your domain administrator.';
                                                                $proposalShowLinkId       = 'proposal-show-link-'.($proposalIdValue ?? 'x');
                                                                $proposalShowTitle        = __('Show');
                                                            @endphp
                                                            <a href="{{ $proposalShowUrl }}"
                                                            id="{{ $proposalShowLinkId }}"
                                                            class="{{ VC::BT_SM_CT }}"
                                                            data-url="{{ $proposalShowUrl }}"
                                                            data-guard-msg="{{ $proposalShowGuardMsg }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ $proposalShowTitle }}">
                                                                <i class="{{ VC::TI_EYE_WT }}"></i>
                                                            </a>
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (() => {
                                                                        try {
                                                                            const l = document.getElementById('{{ $proposalShowLinkId }}');
                                                                            if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                            l.setAttribute('data-listener-active', 'true');
                                                                            l.addEventListener('click', e => {
                                                                                try {
                                                                                    const href = l.getAttribute('href') || '#';
                                                                                    const url = l.getAttribute('data-url') || href || '#';
                                                                                    if (href !== '#' || url !== '#') return;
                                                                                    e.preventDefault();
                                                                                    const msg = l.getAttribute('data-guard-msg') || 'Show proposal route is unavailable. Please contact technical support or your domain administrator.';
                                                                                    const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                                    let container = document.getElementById('toast-container');
                                                                                    if (!container) {
                                                                                        container = document.createElement('div');
                                                                                        container.id = 'toast-container';
                                                                                        document.body.appendChild(container);
                                                                                    }
                                                                                    if (hasBootstrap) {
                                                                                        const toast = document.createElement('div');
                                                                                        toast.className = 'toast';
                                                                                        toast.setAttribute('role', 'alert');
                                                                                        toast.setAttribute('aria-live', 'assertive');
                                                                                        toast.setAttribute('aria-atomic', 'true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
                                                                                        toast.appendChild(body);
                                                                                        container.appendChild(toast);
                                                                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                    } else {
                                                                                        alert(msg);
                                                                                    }
                                                                                    l.setAttribute('data-failed-route', 'true');
                                                                                } catch (err) {}
                                                                            });
                                                                        } catch (error) {}
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        </div>
                                                    @endif
                                                @endcan
                                                @can('edit proposal')
                                                    @if($pid)
                                                        <div class="{{ VC::ACT_BTN_PRIM }} {{ VC::MS2 }}">
                                                            @php
                                                                $proposalEditBaseName     = ViewsConstants::PPS.'.edit';
                                                                $proposalEditKebabName    = Str::kebab($proposalEditBaseName);
                                                                $proposalEditResolvedName = Route::has($proposalEditBaseName)
                                                                    ? $proposalEditBaseName
                                                                    : (Route::has($proposalEditKebabName) ? $proposalEditKebabName : null);
                                                                $proposalIdValue          = isset($pid) && !empty($pid) ? $pid : null;
                                                                $encryptedProposalId      = $proposalIdValue ? Crypt::encrypt($proposalIdValue) : null;
                                                                $proposalEditUrl          = ($proposalEditResolvedName && $encryptedProposalId) ? route($proposalEditResolvedName, $encryptedProposalId) : '#';
                                                                $proposalEditGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'edit_proposal_route_unavailable') ?? 'Edit proposal route is unavailable. Please contact technical support or your domain administrator.';
                                                                $proposalEditLinkId       = 'proposal-edit-link-'.($proposalIdValue ?? 'x');
                                                                $proposalEditTitle        = __('Edit');
                                                            @endphp
                                                            <a href="{{ $proposalEditUrl }}"
                                                            id="{{ $proposalEditLinkId }}"
                                                            class="{{ VC::BT_SM_CT }}"
                                                            data-url="{{ $proposalEditUrl }}"
                                                            data-guard-msg="{{ $proposalEditGuardMsg }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ $proposalEditTitle }}">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (() => {
                                                                        try {
                                                                            const l = document.getElementById('{{ $proposalEditLinkId }}');
                                                                            if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                            l.setAttribute('data-listener-active', 'true');
                                                                            l.addEventListener('click', e => {
                                                                                try {
                                                                                    const href = l.getAttribute('href') || '#';
                                                                                    const url  = l.getAttribute('data-url') || href || '#';
                                                                                    if (href !== '#' || url !== '#') return;
                                                                                    e.preventDefault();
                                                                                    const msg = l.getAttribute('data-guard-msg') || 'Edit proposal route is unavailable. Please contact technical support or your domain administrator.';
                                                                                    const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                                    let container = document.getElementById('toast-container');
                                                                                    if (!container) {
                                                                                        container = document.createElement('div');
                                                                                        container.id = 'toast-container';
                                                                                        document.body.appendChild(container);
                                                                                    }
                                                                                    if (hasBootstrap) {
                                                                                        const toast = document.createElement('div');
                                                                                        toast.className = 'toast';
                                                                                        toast.setAttribute('role', 'alert');
                                                                                        toast.setAttribute('aria-live', 'assertive');
                                                                                        toast.setAttribute('aria-atomic', 'true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
                                                                                        toast.appendChild(body);
                                                                                        container.appendChild(toast);
                                                                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                    } else {
                                                                                        alert(msg);
                                                                                    }
                                                                                    l.setAttribute('data-failed-route', 'true');
                                                                                } catch (err) {}
                                                                            });
                                                                        } catch (error) {}
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        </div>
                                                    @endif
                                                @endcan
                                                @can('delete proposal')
                                                    @if($pid)
                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                            @php
                                                                $proposalDestroyBaseName     = ViewsConstants::PPS.'.destroy';
                                                                $proposalDestroyKebabName    = Str::kebab($proposalDestroyBaseName);
                                                                $proposalDestroyResolvedName = Route::has($proposalDestroyBaseName)
                                                                    ? $proposalDestroyBaseName
                                                                    : (Route::has($proposalDestroyKebabName) ? $proposalDestroyKebabName : null);
                                                                $proposalIdValue             = isset($pid) && !empty($pid) ? $pid : null;
                                                                $proposalDestroyRouteArray   = ($proposalDestroyResolvedName && $proposalIdValue) ? [$proposalDestroyResolvedName, $proposalIdValue] : ['#'];
                                                                $proposalDestroyUrl          = ($proposalDestroyResolvedName && $proposalIdValue) ? route($proposalDestroyResolvedName, $proposalIdValue) : '#';
                                                                $proposalDestroyGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'delete_proposal_route_unavailable') ?? 'Delete proposal route is unavailable. Please contact technical support or your domain administrator.';
                                                                $proposalDestroyFormId       = 'delete-form-'.($proposalIdValue ?? 'x');
                                                                $proposalDestroyLinkId       = 'proposal-destroy-link-'.($proposalIdValue ?? 'x');
                                                                $proposalDestroyTitle        = __('Delete');
                                                                $confirmTitle                = __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?');
                                                                $confirmBody                 = __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
                                                                $confirmCombined             = $confirmTitle.'|'.$confirmBody;
                                                            @endphp
                                                            {!! Form::open([
                                                                'route'          => $proposalDestroyRouteArray,
                                                                'method'         => 'delete',
                                                                'accept-charset' => 'UTF-8',
                                                                'id'             => $proposalDestroyFormId,
                                                                'data-url'       => $proposalDestroyUrl,
                                                                'data-guard-msg' => $proposalDestroyGuardMsg
                                                            ]) !!}
                                                                @csrf
                                                                <a href="#"
                                                                id="{{ $proposalDestroyLinkId }}"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ $proposalDestroyTitle }}"
                                                                data-form-id="{{ $proposalDestroyFormId }}"
                                                                data-url="{{ $proposalDestroyUrl }}"
                                                                data-guard-msg="{{ $proposalDestroyGuardMsg }}"
                                                                data-confirm="{{ $confirmCombined }}">
                                                                    <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (() => {
                                                                        try {
                                                                            const l = document.getElementById('{{ $proposalDestroyLinkId }}');
                                                                            if (!l || l.getAttribute('data-listener-active') === 'true') return;
                                                                            l.setAttribute('data-listener-active', 'true');
                                                                            l.addEventListener('click', (e) => {
                                                                                try {
                                                                                    e.preventDefault();
                                                                                    const formId = l.getAttribute('data-form-id') || '';
                                                                                    const f = formId ? document.getElementById(formId) : null;
                                                                                    if (!f) return;
                                                                                    const url = l.getAttribute('data-url') || f.getAttribute('data-url') || '#';
                                                                                    const action = f.getAttribute('action') || '#';
                                                                                    if (url === '#' && action === '#') {
                                                                                        const msg = l.getAttribute('data-guard-msg') || f.getAttribute('data-guard-msg') || 'Delete proposal route is unavailable. Please contact technical support or your domain administrator.';
                                                                                        const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
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
                                                                                        l.setAttribute('data-failed-route', 'true');
                                                                                        f.setAttribute('data-failed-route', 'true');
                                                                                        return;
                                                                                    }
                                                                                    const confirmRaw = l.getAttribute('data-confirm') || '';
                                                                                    const confirmMsg = confirmRaw ? confirmRaw : 'Are You Sure?|This action can not be undone. Do you want to continue?';
                                                                                    const parts = confirmMsg.split('|');
                                                                                    const finalMsg = parts.length > 1 ? parts[0] + '\n\n' + parts.slice(1).join(' ') : confirmMsg;
                                                                                    if (window.confirm(finalMsg)) {
                                                                                        f.submit();
                                                                                    }
                                                                                } catch (err) {}
                                                                            });
                                                                        } catch (error) {}
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        </div>
                                                    @endif
                                                @endcan
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">{{ __('No proposals available') }}</td>
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
