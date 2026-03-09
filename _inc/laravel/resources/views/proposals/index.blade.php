@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user:$user);
    } catch (\Throwable $e) {
        \Log::error('proposals/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Proposals')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('Proposal')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @php
            try {
                $proposalExportBaseName     = VW::PPS.'.export';
                $proposalExportKebabName    = Str::kebab($proposalExportBaseName);
                $proposalExportSingular     = Str::singular(VW::PPS).'.export';
                $proposalExportResolvedName = Route::has($proposalExportBaseName)
                    ? $proposalExportBaseName
                    : (Route::has($proposalExportKebabName)
                        ? $proposalExportKebabName
                        : (Route::has($proposalExportSingular) ? $proposalExportSingular : null));
                $proposalExportUrl          = $proposalExportResolvedName ? route($proposalExportResolvedName) : '#';
                $proposalExportGuardMsg     = Utility::fetchLinkMessage($lang, VW::PPS, 'export_proposal_route_unavailable') ?? 'Export proposal route is unavailable. Please contact technical support or your domain administrator.';
                $proposalExportLinkId       = 'proposal-export-link';
            } catch (\Throwable $e) {
                \Log::error('proposals/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <a href="{{ $proposalExportUrl }}"
        id="{{ $proposalExportLinkId }}"
        class="{{ VC::BT_SM_PM }}"
        data-url="{{ $proposalExportUrl }}"
        data-guard-msg="{{ base64_encode($proposalExportGuardMsg) }}"
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
                                (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                l.setAttribute('data-failed-route', 'true');
                            } catch (err) {}
                        });
                    } catch (error) {}
                })();
            </script>
        @endpush
        @can('create proposal')
            @php
                try {
                    $proposalCreateBaseName     = ViewsConstants::PPS.'.create';
                    $proposalCreateKebabName    = Str::kebab($proposalCreateBaseName);
                    $proposalCreateSingular     = Str::singular(ViewsConstants::PPS).'.create';
                    $proposalCreateResolvedName = Route::has($proposalCreateBaseName)
                        ? $proposalCreateBaseName
                        : (Route::has($proposalCreateKebabName)
                            ? $proposalCreateKebabName
                            : (Route::has($proposalCreateSingular) ? $proposalCreateSingular : null));
                    $proposalCreateParam        = 0;
                    $proposalCreateUrl          = $proposalCreateResolvedName ? route($proposalCreateResolvedName, $proposalCreateParam) : '#';
                    $proposalCreateGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'create_proposal_route_unavailable') ?? 'Create proposal route is unavailable. Please contact technical support or your domain administrator.';
                    $proposalCreateLinkId       = 'proposal-create-link';
                } catch (\Throwable $e) {
                    \Log::error('proposals/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a href="{{ $proposalCreateUrl }}"
            id="{{ $proposalCreateLinkId }}"
            class="{{ VC::BT_SM_PM }}"
            data-url="{{ $proposalCreateUrl }}"
            data-guard-msg="{{ base64_encode($proposalCreateGuardMsg) }}"
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
                                    (window.RouteGuard?.showToast || (m => alert(m)))(msg);
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
            <div class="{{ VC::MT2 }}" id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD }}">
                        @php
                            try {
                                $proposalIndexBaseName     = ViewsConstants::PPS.'.index';
                                $proposalIndexKebabName    = Str::kebab($proposalIndexBaseName);
                                $proposalIndexSingular     = Str::singular(ViewsConstants::PPS).'.index';
                                $proposalIndexResolvedName = Route::has($proposalIndexBaseName)
                                    ? $proposalIndexBaseName
                                    : (Route::has($proposalIndexKebabName)
                                        ? $proposalIndexKebabName
                                        : (Route::has($proposalIndexSingular) ? $proposalIndexSingular : null));
                                $proposalIndexRouteArray   = $proposalIndexResolvedName ? [$proposalIndexResolvedName] : null;
                                $proposalIndexUrl          = $proposalIndexResolvedName ? route($proposalIndexResolvedName) : '#';
                                $proposalIndexGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'proposal_index_route_unavailable') ?? 'Index proposal route is unavailable. Please contact technical support or your domain administrator.';
                                $proposalIndexFormId       = 'frm_submit';
                            } catch (\Throwable $e) {
                                \Log::error('proposals/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        {!! Form::open(array_filter([
                            'route'          => $proposalIndexRouteArray,
                            'url'            => $proposalIndexRouteArray ? null : '#',
                            'method'         => 'get',
                            'accept-charset' => 'UTF-8',
                            'id'             => $proposalIndexFormId ?? 'frm_submit',
                            'data-url'       => $proposalIndexUrl ?? '#',
                            'data-guard-msg' => $proposalIndexGuardMsg ?? ''
                        ], fn($v) => $v !== null)) !!}
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
                                                    (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                    f.setAttribute('data-failed-route', 'true');
                                                } catch (err) {}
                                            });
                                        } catch (error) {}
                                    })();
                                </script>
                            @endpush
                            @php
                                try {
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
                                } catch (\Throwable $e) {
                                    \Log::error('proposals/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                }
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
                                        try {
                                            $productServiceIndexBaseName     = ViewsConstants::PRD_SV.'.index';
                                            $productServiceIndexKebabName    = Str::kebab($productServiceIndexBaseName);
                                            $productServiceIndexResolvedName = Route::has($productServiceIndexBaseName)
                                                ? $productServiceIndexBaseName
                                                : (Route::has($productServiceIndexKebabName) ? $productServiceIndexKebabName : null);
                                            $productServiceIndexUrl          = $productServiceIndexResolvedName ? route($productServiceIndexResolvedName) : '#';
                                            $productServiceIndexGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PRD_SV, 'product_service_index_route_unavailable') ?? 'Index product service route is unavailable. Please contact technical support or your domain administrator.';
                                            $productServiceIndexLinkId       = 'product-service-index-reset-link';
                                            $resetTitleText                  = isset($resetTitle) ? $resetTitle : __('Export');
                                        } catch (\Throwable $e) {
                                            \Log::error('proposals/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <a href="{{ $productServiceIndexUrl }}"
                                    id="{{ $productServiceIndexLinkId }}"
                                    class="{{ VC::BT_SM_DG }}"
                                    data-url="{{ $productServiceIndexUrl }}"
                                    data-guard-msg="{{ base64_encode($productServiceIndexGuardMsg) }}"
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
                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
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
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
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
                                        try {
                                            $itemValid = isset($proposal) && !empty($proposal) && (is_array($proposal) || is_object($proposal));
                                            $pid = $itemValid ? data_get($proposal,'id') : null;
                                            $statusVal = $itemValid ? data_get($proposal,'status') : null;
                                            $validSt = isset($statusVal) && is_numeric($statusVal) && $statusVal >= 0 && $statusVal <= 4;
                                            $badge = $validSt ? ($badgeByStatus[(int)$statusVal] ?? 'bg-secondary') : 'bg-secondary';
                                            $stLabel = $validSt ? __($statusLabels[(int)$statusVal] ?? __('Unknown status')) : __('Unknown status');
                                            $isConvert = (int)(data_get($proposal,'is_convert') ?? 0);
                                            $convertedId = data_get($proposal,'converted_invoice_id');
                                        } catch (\Throwable $e) {
                                            \Log::error('proposals/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <tr class="font-style">
                                        <td class="Id">
                                            @php
                                                try {
                                                    $proposalShowBaseName     = ViewsConstants::PPS.'.show';
                                                    $proposalShowKebabName    = Str::kebab($proposalShowBaseName);
                                                    $proposalShowSingular     = Str::singular(ViewsConstants::PPS).'.show';
                                                    $proposalShowResolvedName = Route::has($proposalShowBaseName)
                                                        ? $proposalShowBaseName
                                                        : (Route::has($proposalShowKebabName)
                                                            ? $proposalShowKebabName
                                                            : (Route::has($proposalShowSingular) ? $proposalShowSingular : null));
                                                    $proposalIdValue          = isset($pid) && !empty($pid) ? $pid : null;
                                                    $encryptedProposalId      = $proposalIdValue ? Crypt::encrypt($proposalIdValue) : null;
                                                    $proposalShowUrl          = ($proposalShowResolvedName && $encryptedProposalId) ? route($proposalShowResolvedName, $encryptedProposalId) : '#';
                                                    $proposalShowGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'show_proposal_route_unavailable') ?? 'Show proposal route is unavailable. Please contact technical support or your domain administrator.';
                                                    $proposalShowLinkId       = 'proposal-show-link-'.($proposalIdValue ?? 'x');
                                                    $proposalNumberText       = ($user && method_exists($user,'proposalNumberFormat')) ? ($user->proposalNumberFormat(data_get($proposal,'proposal_id')) ?? __('Failed to get proposal number')) : __('Failed to get proposal number');
                                                } catch (\Throwable $e) {
                                                    \Log::error('proposals/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <a href="{{ $proposalShowUrl }}"
                                            id="{{ $proposalShowLinkId }}"
                                            class="{{ VC::BT_OUTPM }}"
                                            data-url="{{ $proposalShowUrl }}"
                                            data-guard-msg="{{ base64_encode($proposalShowGuardMsg) }}">
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
                                                                    (window.RouteGuard?.showToast || (m => alert(m)))(msg);
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
                                        <td><span class="status_badge badge {{ $badge }} p-2 {{ VC::PX3 }} rounded">{{ $stLabel }}</span></td>
                                        @if(Gate::check('edit proposal') || Gate::check('delete proposal') || Gate::check('show proposal'))
                                            <td class="Action">
                                                @if($isConvert === 0 && $pid)
                                                    @can('convert invoice')
                                                        <div class="{{ VC::ACT_BTN_WRN }} {{ VC::MS2 }}">
                                                            @php
                                                                try {
                                                                    $proposalConvertBaseName     = ViewsConstants::PPS.'.convert';
                                                                    $proposalConvertKebabName    = Str::kebab($proposalConvertBaseName);
                                                                    $proposalConvertSingular     = Str::singular(ViewsConstants::PPS).'.convert';
                                                                    $proposalConvertResolvedName = Route::has($proposalConvertBaseName)
                                                                        ? $proposalConvertBaseName
                                                                        : (Route::has($proposalConvertKebabName)
                                                                            ? $proposalConvertKebabName
                                                                            : (Route::has($proposalConvertSingular) ? $proposalConvertSingular : null));
                                                                    $proposalIdValue             = isset($pid) && !empty($pid) ? $pid : null;
                                                                    $proposalConvertRouteArray   = ($proposalConvertResolvedName && $proposalIdValue) ? [$proposalConvertResolvedName, $proposalIdValue] : null;
                                                                    $proposalConvertUrl          = ($proposalConvertResolvedName && $proposalIdValue) ? route($proposalConvertResolvedName, $proposalIdValue) : '#';
                                                                    $proposalConvertGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'convert_proposal_to_invoice_route_unavailable') ?? 'Convert proposal to invoice route is unavailable. Please contact technical support or your domain administrator.';
                                                                    $proposalConvertFormId       = 'proposal-form-'.($proposalIdValue ?? 'x');
                                                                    $proposalConvertLinkId       = 'proposal-convert-link-'.($proposalIdValue ?? 'x');
                                                                    $proposalConvertTitle        = __('Convert Invoice');
                                                                    $proposalConvertOriginal     = __('Convert to Invoice');
                                                                    $proposalConvertConfirmMsg   = __('Do you want to confirm converting to invoice? Press Yes to continue or Cancel to go back');
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('proposals/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp
                                                            {!! Form::open(array_filter([
                                                                'route'          => $proposalConvertRouteArray ?? null,
                                                                'url'            => ($proposalConvertRouteArray ?? null) ? null : '#',
                                                                'method'         => 'get',
                                                                'accept-charset' => 'UTF-8',
                                                                'id'             => $proposalConvertFormId ?? 'proposal-form-x',
                                                                'data-url'       => $proposalConvertUrl ?? '#',
                                                                'data-guard-msg' => $proposalConvertGuardMsg ?? ''
                                                            ], fn($v) => $v !== null)) !!}
                                                                <a href="#"
                                                                id="{{ $proposalConvertLinkId }}"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ $proposalConvertTitle }}"
                                                                data-original-title="{{ $proposalConvertOriginal }}"
                                                                data-form-id="{{ $proposalConvertFormId }}"
                                                                data-url="{{ $proposalConvertUrl }}"
                                                                data-guard-msg="{{ base64_encode($proposalConvertGuardMsg) }}"
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
                                                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
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
                                                                try {
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
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('proposals/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp
                                                            <a href="{{ $invoiceShowUrl }}"
                                                            id="{{ $invoiceShowLinkId }}"
                                                            class="{{ VC::BT_SM_CT }}"
                                                            data-url="{{ $invoiceShowUrl }}"
                                                            data-guard-msg="{{ base64_encode($invoiceShowGuardMsg) }}"
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
                                                                                    (window.RouteGuard?.showToast || (m => alert(m)))(msg);
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
                                                        <div class="{{ VC::ACT_BTN }} bg-success {{ VC::MS2 }}">
                                                            @php
                                                                try {
                                                                    $proposalDuplicateBaseName     = ViewsConstants::PPS.'.duplicate';
                                                                    $proposalDuplicateKebabName    = Str::kebab($proposalDuplicateBaseName);
                                                                    $proposalDuplicateSingular     = Str::singular(ViewsConstants::PPS).'.duplicate';
                                                                    $proposalDuplicateResolvedName = Route::has($proposalDuplicateBaseName)
                                                                        ? $proposalDuplicateBaseName
                                                                        : (Route::has($proposalDuplicateKebabName)
                                                                            ? $proposalDuplicateKebabName
                                                                            : (Route::has($proposalDuplicateSingular) ? $proposalDuplicateSingular : null));
                                                                    $proposalIdValue               = isset($pid) && !empty($pid) ? $pid : null;
                                                                    $proposalDuplicateRouteArray   = ($proposalDuplicateResolvedName && $proposalIdValue) ? [$proposalDuplicateResolvedName, $proposalIdValue] : null;
                                                                    $proposalDuplicateUrl          = ($proposalDuplicateResolvedName && $proposalIdValue) ? route($proposalDuplicateResolvedName, $proposalIdValue) : '#';
                                                                    $proposalDuplicateGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'duplicate_proposal_route_unavailable') ?? 'Duplicate proposal route is unavailable. Please contact technical support or your domain administrator.';
                                                                    $proposalDuplicateFormId       = 'duplicate-form-'.($proposalIdValue ?? 'x');
                                                                    $proposalDuplicateLinkId       = 'proposal-duplicate-link-'.($proposalIdValue ?? 'x');
                                                                    $proposalDuplicateTitle        = __('Duplicate');
                                                                    $proposalDuplicateConfirm      = __('Do you want to confirm duplicating this proposal ? Press Yes to continue or Cancel to go back');
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('proposals/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp
                                                            {!! Form::open(array_filter([
                                                                'route'          => $proposalDuplicateRouteArray ?? null,
                                                                'url'            => ($proposalDuplicateRouteArray ?? null) ? null : '#',
                                                                'method'         => 'get',
                                                                'accept-charset' => 'UTF-8',
                                                                'id'             => $proposalDuplicateFormId ?? 'proposal-dup-x',
                                                                'data-url'       => $proposalDuplicateUrl ?? '#',
                                                                'data-guard-msg' => $proposalDuplicateGuardMsg ?? ''
                                                            ], fn($v) => $v !== null)) !!}
                                                                <a href="#"
                                                                id="{{ $proposalDuplicateLinkId }}"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ $proposalDuplicateTitle }}"
                                                                data-form-id="{{ $proposalDuplicateFormId }}"
                                                                data-url="{{ $proposalDuplicateUrl }}"
                                                                data-guard-msg="{{ base64_encode($proposalDuplicateGuardMsg) }}"
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
                                                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
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
                                                                try {
                                                                    $proposalShowBaseName     = ViewsConstants::PPS.'.show';
                                                                    $proposalShowKebabName    = Str::kebab($proposalShowBaseName);
                                                                    $proposalShowSingular     = Str::singular(ViewsConstants::PPS).'.show';
                                                                    $proposalShowResolvedName = Route::has($proposalShowBaseName)
                                                                        ? $proposalShowBaseName
                                                                        : (Route::has($proposalShowKebabName)
                                                                            ? $proposalShowKebabName
                                                                            : (Route::has($proposalShowSingular) ? $proposalShowSingular : null));
                                                                    $proposalIdValue          = isset($pid) && !empty($pid) ? $pid : null;
                                                                    $encryptedProposalId      = $proposalIdValue ? Crypt::encrypt($proposalIdValue) : null;
                                                                    $proposalShowUrl          = ($proposalShowResolvedName && $encryptedProposalId) ? route($proposalShowResolvedName, $encryptedProposalId) : '#';
                                                                    $proposalShowGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'show_proposal_route_unavailable') ?? 'Show proposal route is unavailable. Please contact technical support or your domain administrator.';
                                                                    $proposalShowLinkId       = 'proposal-show-link-'.($proposalIdValue ?? 'x');
                                                                    $proposalShowTitle        = __('Show');
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('proposals/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp
                                                            <a href="{{ $proposalShowUrl }}"
                                                            id="{{ $proposalShowLinkId }}"
                                                            class="{{ VC::BT_SM_CT }}"
                                                            data-url="{{ $proposalShowUrl }}"
                                                            data-guard-msg="{{ base64_encode($proposalShowGuardMsg) }}"
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
                                                                                    (window.RouteGuard?.showToast || (m => alert(m)))(msg);
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
                                                                try {
                                                                    $proposalEditBaseName     = ViewsConstants::PPS.'.edit';
                                                                    $proposalEditKebabName    = Str::kebab($proposalEditBaseName);
                                                                    $proposalEditSingular     = Str::singular(ViewsConstants::PPS).'.edit';
                                                                    $proposalEditResolvedName = Route::has($proposalEditBaseName)
                                                                        ? $proposalEditBaseName
                                                                        : (Route::has($proposalEditKebabName)
                                                                            ? $proposalEditKebabName
                                                                            : (Route::has($proposalEditSingular) ? $proposalEditSingular : null));
                                                                    $proposalIdValue          = isset($pid) && !empty($pid) ? $pid : null;
                                                                    $encryptedProposalId      = $proposalIdValue ? Crypt::encrypt($proposalIdValue) : null;
                                                                    $proposalEditUrl          = ($proposalEditResolvedName && $encryptedProposalId) ? route($proposalEditResolvedName, $encryptedProposalId) : '#';
                                                                    $proposalEditGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'edit_proposal_route_unavailable') ?? 'Edit proposal route is unavailable. Please contact technical support or your domain administrator.';
                                                                    $proposalEditLinkId       = 'proposal-edit-link-'.($proposalIdValue ?? 'x');
                                                                    $proposalEditTitle        = __('Edit');
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('proposals/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp
                                                            <a href="{{ $proposalEditUrl }}"
                                                            id="{{ $proposalEditLinkId }}"
                                                            class="{{ VC::BT_SM_CT }}"
                                                            data-url="{{ $proposalEditUrl }}"
                                                            data-guard-msg="{{ base64_encode($proposalEditGuardMsg) }}"
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
                                                                                    (window.RouteGuard?.showToast || (m => alert(m)))(msg);
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
                                                                try {
                                                                    $proposalDestroyBaseName     = ViewsConstants::PPS.'.destroy';
                                                                    $proposalDestroyKebabName    = Str::kebab($proposalDestroyBaseName);
                                                                    $proposalDestroySingular     = Str::singular(ViewsConstants::PPS).'.destroy';
                                                                    $proposalDestroyResolvedName = Route::has($proposalDestroyBaseName)
                                                                        ? $proposalDestroyBaseName
                                                                        : (Route::has($proposalDestroyKebabName)
                                                                            ? $proposalDestroyKebabName
                                                                            : (Route::has($proposalDestroySingular) ? $proposalDestroySingular : null));
                                                                    $proposalIdValue             = isset($pid) && !empty($pid) ? $pid : null;
                                                                    $proposalDestroyRouteArray   = ($proposalDestroyResolvedName && $proposalIdValue) ? [$proposalDestroyResolvedName, $proposalIdValue] : null;
                                                                    $proposalDestroyUrl          = ($proposalDestroyResolvedName && $proposalIdValue) ? route($proposalDestroyResolvedName, $proposalIdValue) : '#';
                                                                    $proposalDestroyGuardMsg     = Utility::fetchLinkMessage($lang, ViewsConstants::PPS, 'delete_proposal_route_unavailable') ?? 'Delete proposal route is unavailable. Please contact technical support or your domain administrator.';
                                                                    $proposalDestroyFormId       = 'delete-form-'.($proposalIdValue ?? 'x');
                                                                    $proposalDestroyLinkId       = 'proposal-destroy-link-'.($proposalIdValue ?? 'x');
                                                                    $proposalDestroyTitle        = __('Delete');
                                                                    $confirmTitle                = __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?');
                                                                    $confirmBody                 = __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
                                                                    $confirmCombined             = $confirmTitle.'|'.$confirmBody;
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('proposals/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp
                                                            {!! Form::open(array_filter([
                                                                'route'          => $proposalDestroyRouteArray ?? null,
                                                                'url'            => ($proposalDestroyRouteArray ?? null) ? null : '#',
                                                                'method'         => 'DELETE',
                                                                'accept-charset' => 'UTF-8',
                                                                'id'             => $proposalDestroyFormId ?? 'proposal-del-x',
                                                                'data-url'       => $proposalDestroyUrl ?? '#',
                                                                'data-guard-msg' => $proposalDestroyGuardMsg ?? ''
                                                            ], fn($v) => $v !== null)) !!}
                                                                @csrf
                                                                <a href="#"
                                                                id="{{ $proposalDestroyLinkId }}"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ $proposalDestroyTitle }}"
                                                                data-form-id="{{ $proposalDestroyFormId }}"
                                                                data-url="{{ $proposalDestroyUrl }}"
                                                                data-guard-msg="{{ base64_encode($proposalDestroyGuardMsg) }}"
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
                                                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
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
