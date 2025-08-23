@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{User,Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $authUser = $user?->creatorId() ?? null;
    $creatorUser = $authUser ? User::find($authUser) : null;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Balance Sheet') }}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Balance Sheet') }}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script async src="{{ asset('js/routes/reports/balances/horizontal/index/lang/pdf.js') }}"></script>
    <script defer src="{{ asset('js/routes/reports/balances/horizontal/index/pdf.js') }}"></script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @php
            $printBase                 = ViewsConstants::RPT.'.balance.sheet.print';
            $printKebab                = Str::kebab($printBase);
            $printResolved             = Route::has($printBase) ? $printBase : (Route::has($printKebab) ? $printKebab : null);
            $orientation               = 'horizontal';
            $printParams               = $orientation ? [$orientation] : ['#'];
            $balanceSheetPrintUrl      = $printResolved ? route($printResolved, $printParams) : '#';
            $balanceSheetPrintGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::RPT, 'print_balance_sheet_unavailable') ?? 'Print balance sheet route is unavailable. Please contact technical support or your domain administrator.';
            $balanceSheetPrintFormId   = 'balance-sheet-print-form';
        @endphp
        {{ Form::open([
            'method'              => 'POST',
            'url'                 => $balanceSheetPrintUrl,
            'id'                  => $balanceSheetPrintFormId,
            'data-url'            => $balanceSheetPrintUrl,
            'data-guard-msg'      => $balanceSheetPrintGuardMsg,
            'data-sv-localized'   => 'true',
        ]) }}
        @push(StacksConstants::ADM_SCRP_PG)
            <script src="{{ asset('assets/js/routes/reports/balances/horizontal/index/print.js') }}" defer></script>
        @endpush
    </div>
    @php
        $exportBase                       = ViewsConstants::RPT.'.balance.sheet.export';
        $exportKebab                      = Str::kebab($exportBase);
        $exportResolved                   = Route::has($exportBase) ? $exportBase : (Route::has($exportKebab) ? $exportKebab : null);
        $balanceSheetExportUrl            = $exportResolved ? route($exportResolved) : '#';
        $balanceSheetExportGuardMsg       = Utility::fetchLinkMessage($lang, ViewsConstants::RPT, 'export_balance_sheet_unavailable') ?? 'Export balance sheet route is unavailable. Please contact technical support or your domain administrator.';
        $balanceSheetExportFormId         = 'balance-sheet-export-form';
        $verticalBase                     = VW::RPT.'.balance.sheet';
        $verticalKebab                    = Str::kebab($verticalBase);
        $verticalResolved                 = Route::has($verticalBase) ? $verticalBase : (Route::has($verticalKebab) ? $verticalKebab : null);
        $verticalParam                    = 'vertical';
        $verticalParams                   = [$verticalParam ?: '#'];
        $balanceSheetVerticalUrl          = $verticalResolved ? route($verticalResolved, $verticalParams) : '#';
        $balanceSheetVerticalGuardMsg     = Utility::fetchLinkMessage($lang, VW::RPT, 'view_balance_sheet_unavailable') ?? 'View balance sheet route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <div class="{{ VC::FEND }} me-2">
        {{ Form::open([
            'method'              => 'POST',
            'url'                 => $balanceSheetExportUrl,
            'id'                  => $balanceSheetExportFormId,
            'data-url'            => $balanceSheetExportUrl,
            'data-guard-msg'      => $balanceSheetExportGuardMsg,
            'data-sv-localized'   => 'true',
        ]) }}
            <input type="hidden" name="start_date" class="start_date">
            <input type="hidden" name="end_date" class="end_date">
            <button type="submit" class="{{ VC::BT_SM_PM }}" data-bs-toggle="tooltip" title="{{ __('Export') }}" data-original-title="{{ __('Export') }}">
                <i class="{{ VC::TI_EXP }}"></i>
            </button>
        {{ Form::close() }}
    </div>
    <div class="{{ VC::FEND }} me-2" id="filter">
        <button id="filter" class="{{ VC::BT_SM_PM }}"><i class="ti ti-filter"></i></button>
    </div>
    <div class="{{ VC::FEND }} me-2">
        <a href="{{ $balanceSheetVerticalUrl }}"
        class="{{ VC::BT_SM_PM }} balance-sheet-vertical"
        data-bs-toggle="tooltip"
        title="{{ __('Vertical View') }}"
        data-original-title="{{ __('Vertical View') }}"
        data-url="{{ $balanceSheetVerticalUrl }}"
        data-guard-msg="{{ $balanceSheetVerticalGuardMsg }}"
        data-sv-localized="true">
            <i class="ti ti-separator-horizontal"></i>
        </a>
    </div>
    @push(StacksConstants::ADM_SCRP_PG)
        <script src="{{ asset('assets/js/routes/reports/balances/horizontal/index/export.js') }}" defer></script>
        <script src="{{ asset('assets/js/routes/reports/balances/horizontal/index/filter.js') }}" defer></script>
        <script src="{{ asset('assets/js/routes/reports/balances/horizontal/index/vertical.js') }}" defer></script>
    @endpush
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::MT4 }}">
        <div class="{{ VC::RW }} justify-content-center">
            <div class="{{ VC::CM12 }}">
                <div class="mt-2" id="multiCollapseExample1">
                    <div class="{{ VC::CD }}" id="show_filter" style="display:none;">
                        <div class="card-body">
                            @php
                                $balanceSheetBase     = VW::RPT.'.balance.sheet';
                                $balanceSheetKebab    = Str::kebab($balanceSheetBase);
                                $balanceSheetResolved = Route::has($balanceSheetBase) ? $balanceSheetBase : (Route::has($balanceSheetKebab) ? $balanceSheetKebab : null);
                                $balanceSheetUrl      = $balanceSheetResolved ? route($balanceSheetResolved) : '#';
                                $balanceSheetGuardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'view_balance_sheet_unavailable') ?? 'View balance sheet route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            {{ Form::open([
                                'route'             => [$balanceSheetResolved ?: '#'],
                                'method'            => 'GET',
                                'url'               => $balanceSheetUrl,
                                'id'                => 'report_bill_summary',
                                'data-url'          => $balanceSheetUrl,
                                'data-guard-msg'    => $balanceSheetGuardMsg,
                                'data-sv-localized' => 'true',
                            ]) }}
                                <div class="{{ VC::R_ALC_JCE }}">
                                    <div class="col-xl-10">
                                        <div class="{{ VC::RW }}">
                                            <div class="{{ VC::CL_XLG4 }}"><div class="btn-box"></div></div>
                                            <div class="{{ VC::CL_XLG4 }}"><div class="btn-box"></div></div>
                                            <div class="{{ VC::CL_XLG4 }}">
                                                <div class="btn-box">
                                                    {{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
                                                    {{ Form::date('start_date', data_get($filter ?? [], 'startDateRange'), ['class' => 'startDate ' . VC::FM_CT]) }}
                                                </div>
                                            </div>
                                            <div class="{{ VC::CL_XLG4 }}">
                                                <div class="btn-box">
                                                    {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                                                    {{ Form::date('end_date', data_get($filter ?? [], 'endDateRange'), ['class' => 'endDate ' . VC::FM_CT]) }}
                                                </div>
                                            </div>
                                            <input type="hidden" name="view" value="horizontal">
                                        </div>
                                    </div>
                                    <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                        <div class="{{ VC::RW }}">
                                            <div class="{{ VC::C_AT }}">
                                                <button type="submit" class="{{ VC::BT_SM_PM }}" data-bs-toggle="tooltip" title="{{ __('Apply') }}" data-original-title="{{ __('apply') }}">
                                                    <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                                </button>
                                                <a href="{{ $balanceSheetUrl }}"
                                                class="{{ VC::BT_SM_DG }} balance-sheet-reset"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('Reset') }}"
                                                data-original-title="{{ __('Reset') }}"
                                                data-url="{{ $balanceSheetUrl }}"
                                                data-guard-msg="{{ $balanceSheetGuardMsg }}"
                                                data-sv-localized="true">
                                                    <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {{ Form::close() }}
                            @push(StacksConstants::ADM_SCRP_PG)
                                <script src="{{ asset('assets/js/routes/reports/balances/horizontal/index/index.js') }}" defer></script>
                                <script src="{{ asset('assets/js/routes/reports/balances/horizontal/index/reset.js') }}" defer></script>
                            @endpush
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VC::RW }} justify-content-center" id="printableArea">
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        <div class="account-main-title mb-5">
                            <h5>{{ __('Balance Sheet of') . ' ' . (data_get($user ?? null,'name') ?? __('Could not find user name')) . ' ' . __('as of') . ' ' . (data_get($filter ?? [],'startDateRange') ?? __('No start date available')) . ' ' . __('to') . ' ' . (data_get($filter ?? [],'endDateRange') ?? __('No end date available')) }}</h5>
                        </div>
                        @php
                            $charts = is_iterable($chartAccounts ?? null) ? $chartAccounts : [];
                            $fmt = function($v) use($user){ return ($user && method_exists($user,'priceFormat')) ? ($user->priceFormat($v) ?? number_format((float)$v,2)) : number_format((float)$v,2); };
                            $totalAmount = 0;
                        @endphp
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::CM6 }}">
                                <div class="aacount-title {{ VC::DFL_AIC_JCB }} {{ VC::BD }} {{ VC::PY2 }}"><h5 class="{{ VC::MB0 }} ms-3">{{ __('Liabilities & Equity') }}</h5></div>
                                <div class="border-start border-end">
                                    @foreach ($charts as $type => $accounts)
                                        @if (!empty($accounts) && $type != 'Assets')
                                            <div class="account-main-inner {{ VC::PY2 }}">
                                                <p class="fw-bold ps-2 mb-2">{{ $type }}</p>
                                                @php $sectionTotal = 0; @endphp
                                                @foreach ($accounts as $account)
                                                    @php
                                                        $subType = data_get($account,'subType') ?: '';
                                                        $accList = data_get($account,'account',[]);
                                                        $last = (is_array($accList) && !empty($accList)) ? end($accList) : null;
                                                        $lastName = data_get($last,'account_name') ?? 0;
                                                        $lastNet = (float) data_get($last,'netAmount',0);
                                                    @endphp
                                                    <div class="border-bottom {{ VC::PY2 }}">
                                                        <p class="fw-bold ps-4 mb-2">{{ $subType }}</p>
                                                        @foreach ($accList as $k => $record)
                                                            @php $name = (string)(data_get($record,'account_name') ?? ''); $isTotal = preg_match('/\btotal\b/i',$name) === 1; @endphp
                                                            @if ($k < count($accList) - 1)
                                                                @if (!$isTotal)
                                                                    <div class="account-inner {{ VC::DFL_AIC_JCB }} ps-5">
                                                                        @php
                                                                            $accountId      = data_get($record, 'account_id');
                                                                            $ledgerBase     = VW::RPT.'.ledger';
                                                                            $ledgerKebab    = Str::kebab($ledgerBase);
                                                                            $ledgerResolved = Route::has($ledgerBase) ? $ledgerBase : (Route::has($ledgerKebab) ? $ledgerKebab : null);
                                                                            $ledgerUrl      = ($ledgerResolved && $accountId) ? route($ledgerResolved, [$accountId]) : '#';
                                                                            $ledgerHref     = ($ledgerUrl !== '#' && $accountId) ? ($ledgerUrl.'?account='.$accountId) : '#';
                                                                            $ledgerGuardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'view_ledger_unavailable') ?? 'View ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                            $ledgerLinkId   = 'ledger-view-'.($accountId ?? 'x');
                                                                        @endphp
                                                                        <p class="mb-2">
                                                                            <a href="{{ $ledgerHref }}"
                                                                            id="{{ $ledgerLinkId }}"
                                                                            class="text-primary ledger-view"
                                                                            data-url="{{ $ledgerHref }}"
                                                                            data-guard-msg="{{ $ledgerGuardMsg }}"
                                                                            data-sv-localized="true">
                                                                                {{ $name ?: __('No account name available') }}
                                                                            </a>
                                                                        </p>
                                                                        @push(StacksConstants::ADM_SCRP_PG)
                                                                            <script>
                                                                                (() => {
                                                                                    try {
                                                                                        const l = document.getElementById('{{ $ledgerLinkId }}');
                                                                                        if (!l) return;
                                                                                        const flag = 'data-click-listener';
                                                                                        if (l.hasAttribute(flag) && l.getAttribute(flag) === 'true') return;
                                                                                        l.setAttribute(flag, 'true');
                                                                                        l.addEventListener('click', function(e) {
                                                                                            try {
                                                                                                const href = l.getAttribute('href') || '#';
                                                                                                const url  = l.getAttribute('data-url') || href || '#';
                                                                                                if (href !== '#' || url !== '#') return;
                                                                                                e.preventDefault();
                                                                                                const msg = l.getAttribute('data-guard-msg') || 'View ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                                                const linkEl = document.querySelector('link[href*="bootstrap"]');
                                                                                                const hasBootstrapToast = (typeof window !== 'undefined' && window.bootstrap && typeof window.bootstrap.Toast === 'function');
                                                                                                let container = document.getElementById('toast-container');
                                                                                                if (!container) {
                                                                                                    container = document.createElement('div');
                                                                                                    container.id = 'toast-container';
                                                                                                    container.className = 'position-fixed top-0 end-0 p-3';
                                                                                                    document.body.appendChild(container);
                                                                                                }
                                                                                                if (linkEl && hasBootstrapToast) {
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
                                                                                                    const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                                                                                                    toast.addEventListener('hidden.bs.toast', function() { try { toast.remove(); } catch (_) {} });
                                                                                                    inst.show();
                                                                                                } else {
                                                                                                    alert(msg);
                                                                                                }
                                                                                                l.setAttribute('data-failed-route', 'true');
                                                                                            } catch (_) {}
                                                                                        }, { passive: false });
                                                                                    } catch (_) {}
                                                                                })();
                                                                            </script>
                                                                        @endpush
                                                                        <p class="mb-2 text-center">{{ data_get($record,'account_code') ?? '-' }}</p>
                                                                        <p class="text-primary mb-2 {{ VC::FEND }} text-end {{ VC::ME3 }}">{{ $fmt(data_get($record,'netAmount',0)) }}</p>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        @endforeach
                                                        <div class="account-inner {{ VC::DFL_AIC_JCB }} ps-4">
                                                            <p class="fw-bold mb-2">{{ $lastName }}</p>
                                                            <p class="fw-bold mb-2 text-end {{ VC::ME3 }}">{{ $fmt($lastNet) }}</p>
                                                        </div>
                                                    </div>
                                                    @php $sectionTotal += $lastNet; @endphp
                                                @endforeach
                                                <div class="aacount-title {{ VC::DFL_AIC_JCB }} border-top border-bottom {{ VC::PY2 }} px-2 pe-0">
                                                    <h6 class="fw-bold {{ VC::MB0 }}">{{ __('Total for') . ' ' . $type }}</h6>
                                                    <h6 class="fw-bold {{ VC::MB0 }} text-end {{ VC::ME3 }}">{{ $fmt($sectionTotal) }}</h6>
                                                </div>
                                                @php $totalAmount += $sectionTotal; @endphp
                                            </div>
                                        @endif
                                    @endforeach
                                    @if ($totalAmount != 0)
                                        <div class="{{ VC::DFL_AIC_JCB }} border-bottom {{ VC::PY2 }} px-0">
                                            <h6 class="fw-bold {{ VC::MB0 }} ms-2">{{ __('Total for Liabilities & Equity') }}</h6>
                                            <h6 class="fw-bold {{ VC::MB0 }} text-end {{ VC::ME3 }}">{{ $fmt($totalAmount) }}</h6>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @php $assetsTotal = 0; @endphp
                            <div class="{{ VC::CM6 }}">
                                <div class="aacount-title {{ VC::DFL_AIC_JCB }} {{ VC::BD }} {{ VC::PY2 }}"><h5 class="{{ VC::MB0 }} ms-3">{{ __('Assets') }}</h5></div>
                                <div class="border-start border-end">
                                    @foreach ($charts as $type => $accounts)
                                        @if (!empty($accounts) && $type == 'Assets')
                                            <div class="account-main-inner {{ VC::PY2 }}">
                                                <p class="fw-bold ps-2 mb-2">{{ $type }}</p>
                                                @foreach ($accounts as $account)
                                                    @php
                                                        $subType = data_get($account,'subType') ?: '';
                                                        $accList = data_get($account,'account',[]);
                                                        $last = (is_array($accList) && !empty($accList)) ? end($accList) : null;
                                                        $lastName = data_get($last,'account_name') ?? 0;
                                                        $lastNet = (float) data_get($last,'netAmount',0);
                                                    @endphp
                                                    <div class="border-bottom {{ VC::PY2 }}">
                                                        <p class="fw-bold ps-4 mb-2">{{ $subType }}</p>
                                                        @foreach ($accList as $k => $record)
                                                            @php $name = (string)(data_get($record,'account_name') ?? ''); $isTotal = preg_match('/\btotal\b/i',$name) === 1; @endphp
                                                            @if ($k < count($accList) - 1)
                                                                @if (!$isTotal)
                                                                    <div class="account-inner {{ VC::DFL_AIC_JCB }} ps-5">
                                                                        @php
                                                                            $accountId      = data_get($record, 'account_id');
                                                                            $ledgerBase     = VW::RPT.'.ledger';
                                                                            $ledgerKebab    = Str::kebab($ledgerBase);
                                                                            $ledgerResolved = Route::has($ledgerBase) ? $ledgerBase : (Route::has($ledgerKebab) ? $ledgerKebab : null);
                                                                            $ledgerUrl      = ($ledgerResolved && $accountId) ? route($ledgerResolved, [$accountId]) : '#';
                                                                            $ledgerHref     = ($ledgerUrl !== '#' && $accountId) ? ($ledgerUrl.'?account='.$accountId) : '#';
                                                                            $ledgerGuardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'view_ledger_unavailable') ?? 'View ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                            $ledgerLinkId   = 'ledger-view-'.($accountId ?? 'x');
                                                                        @endphp
                                                                        <p class="mb-2">
                                                                            <a href="{{ $ledgerHref }}"
                                                                            id="{{ $ledgerLinkId }}"
                                                                            class="text-primary ledger-view"
                                                                            data-url="{{ $ledgerHref }}"
                                                                            data-guard-msg="{{ $ledgerGuardMsg }}"
                                                                            data-sv-localized="true">
                                                                                {{ $name ?: __('No account name available') }}
                                                                            </a>
                                                                        </p>
                                                                        @push(StacksConstants::ADM_SCRP_PG)
                                                                            <script>
                                                                                (() => {
                                                                                    try {
                                                                                        const l = document.getElementById('{{ $ledgerLinkId }}');
                                                                                        if (!l) return;
                                                                                        const flag = 'data-click-listener';
                                                                                        if (l.hasAttribute(flag) && l.getAttribute(flag) === 'true') return;
                                                                                        l.setAttribute(flag, 'true');
                                                                                        l.addEventListener('click', function(e) {
                                                                                            try {
                                                                                                const href = l.getAttribute('href') || '#';
                                                                                                const url  = l.getAttribute('data-url') || href || '#';
                                                                                                if (href !== '#' || url !== '#') return;
                                                                                                e.preventDefault();
                                                                                                const msg = l.getAttribute('data-guard-msg') || 'View ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                                                const linkEl = document.querySelector('link[href*="bootstrap"]');
                                                                                                const hasBootstrapToast = (typeof window !== 'undefined' && window.bootstrap && typeof window.bootstrap.Toast === 'function');
                                                                                                let container = document.getElementById('toast-container');
                                                                                                if (!container) {
                                                                                                    container = document.createElement('div');
                                                                                                    container.id = 'toast-container';
                                                                                                    container.className = 'position-fixed top-0 end-0 p-3';
                                                                                                    document.body.appendChild(container);
                                                                                                }
                                                                                                if (linkEl && hasBootstrapToast) {
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
                                                                                                    const inst = window.bootstrap.Toast.getOrCreateInstance(toast);
                                                                                                    toast.addEventListener('hidden.bs.toast', function() { try { toast.remove(); } catch (_) {} });
                                                                                                    inst.show();
                                                                                                } else {
                                                                                                    alert(msg);
                                                                                                }
                                                                                                l.setAttribute('data-failed-route', 'true');
                                                                                            } catch (_) {}
                                                                                        }, { passive: false });
                                                                                    } catch (_) {}
                                                                                })();
                                                                            </script>
                                                                        @endpush
                                                                        <p class="mb-2 text-center">{{ data_get($record,'account_code') ?? '-' }}</p>
                                                                        <p class="text-primary mb-2 {{ VC::FEND }} text-end {{ VC::ME3 }}">{{ $fmt(data_get($record,'netAmount',0)) }}</p>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        @endforeach
                                                        <div class="account-inner {{ VC::DFL_AIC_JCB }} ps-4">
                                                            <p class="fw-bold mb-2">{{ $lastName }}</p>
                                                            <p class="fw-bold mb-2 text-end {{ VC::ME3 }}">{{ $fmt($lastNet) }}</p>
                                                        </div>
                                                    </div>
                                                    @php $assetsTotal += $lastNet; @endphp
                                                @endforeach
                                            </div>
                                        @endif
                                    @endforeach
                                    @if ($assetsTotal != 0)
                                        <div class="{{ VC::DFL_AIC_JCB }} border-bottom {{ VC::PY2 }} px-0">
                                            <h6 class="fw-bold {{ VC::MB0 }} ms-2">{{ __('Total for Assets') }}</h6>
                                            <h6 class="fw-bold {{ VC::MB0 }} text-end {{ VC::ME3 }}">{{ $fmt($assetsTotal) }}</h6>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


