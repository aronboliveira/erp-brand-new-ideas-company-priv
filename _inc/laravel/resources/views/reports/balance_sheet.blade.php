@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user:$user);
        $authUser = $user?->creatorId() ?? null;
        $creatorUser = $authUser ? User::find($authUser) : null;
    } catch (\Throwable $e) {
        \Log::error('reports/balance_sheet — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Balance Sheet') }}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Balance Sheet') }}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script async src="{{ asset('assets/js/routes/reports/balances/index/lang/pdf.js') }}">
    </script>
    <script defer src="{{ asset('assets/js/routes/reports/balances/index/pdf.js') }}">
    </script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @php
            try {
                $printBase                 = ViewsConstants::RPT.'.balance.sheet.print';
                $printKebab                = Str::kebab($printBase);
                $printResolved             = Route::has($printBase) ? $printBase : (Route::has($printKebab) ? $printKebab : null);
                $orientation               = 'horizontal';
                $printParams               = $orientation ? [$orientation] : ['#'];
                $balanceSheetPrintUrl      = $printResolved ? route($printResolved, $printParams) : '#';
                $balanceSheetPrintGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::RPT, 'print_balance_sheet_unavailable') ?? 'Print balance sheet route is unavailable. Please contact technical support or your domain administrator.';
                $balanceSheetPrintFormId   = 'balance-sheet-print-form';
            } catch (\Throwable $e) {
                \Log::error('reports/balance_sheet — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        {{ Form::open([
            'method'              => 'POST',
            'url'                 => $balanceSheetPrintUrl,
            'id'                  => $balanceSheetPrintFormId,
            'data-url'            => $balanceSheetPrintUrl,
            'data-guard-msg'      => $balanceSheetPrintGuardMsg,
            'data-sv-localized'   => 'true',
        ]) }}
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/balances/index/print.js') }}" defer></script>
        @endpush
    </div>
    @php
        try {
            $exportBase                     = VW::RPT.'.balance.sheet.export';
            $exportKebab                    = Str::kebab($exportBase);
            $exportResolved                 = Route::has($exportBase) ? $exportBase : (Route::has($exportKebab) ? $exportKebab : null);
            $balanceSheetExportUrl          = $exportResolved ? route($exportResolved) : '#';
            $balanceSheetExportGuardMsg     = Utility::fetchLinkMessage($lang, VW::RPT, 'export_balance_sheet_unavailable') ?? 'Export balance sheet route is unavailable. Please contact technical support or your domain administrator.';
            $balanceSheetExportFormId       = 'balance-sheet-export-form';
            $horizontalBase                 = VW::RPT.'.balance.sheet';
            $horizontalKebab                = Str::kebab($horizontalBase);
            $horizontalResolved             = Route::has($horizontalBase) ? $horizontalBase : (Route::has($horizontalKebab) ? $horizontalKebab : null);
            $horizontalParam                = 'horizontal';
            $horizontalParams               = [$horizontalParam ?: '#'];
            $balanceSheetHorizontalUrl      = $horizontalResolved ? route($horizontalResolved, $horizontalParams) : '#';
            $balanceSheetHorizontalGuardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'view_balance_sheet_horizontal_unavailable') ?? 'Horizontal view for balance sheet route is unavailable. Please contact technical support or your domain administrator.';
        } catch (\Throwable $e) {
            \Log::error('reports/balance_sheet — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
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
        <a href="{{ $balanceSheetHorizontalUrl }}"
        class="{{ VC::BT_SM_PM }} balance-sheet-horizontal"
        data-bs-toggle="tooltip"
        title="{{ __('Horizontal View') }}"
        data-original-title="{{ __('Horizontal View') }}"
        data-url="{{ $balanceSheetHorizontalUrl }}"
        data-guard-msg="{{ base64_encode($balanceSheetHorizontalGuardMsg) }}"
        data-sv-localized="true">
            <i class="ti ti-separator-vertical"></i>
        </a>
    </div>
    @push(StacksConstants::ADM_SCR_PG)
        <script src="{{ asset('assets/js/routes/reports/balances/index/export.js') }}" defer></script>
        <script src="{{ asset('assets/js/routes/reports/balances/index/filter.js') }}" defer></script>
        <script src="{{ asset('assets/js/routes/reports/balances/index/horizontal.js') }}" defer></script>
    @endpush
@endsection
@section(YieldingConstants::ADM_CTT)
    @include('reports.partials._report_styles')
    <div class="{{ VC::MT4 }}">
        <div class="{{ VC::RW }} justify-content-center">
            <div class="{{ VC::CM8 }}">
                <div class="{{ VC::MT2 }}" id="multiCollapseExample1">
                    <div class="{{ VC::CD }}" id="show_filter" style="display:none;">
                        <div class="{{ VC::CD_BD }}">
                            @php
                                try {
                                    $balanceSheetBase            = VW::RPT.'.balance.sheet';
                                    $balanceSheetKebab           = Str::kebab($balanceSheetBase);
                                    $balanceSheetResolved        = Route::has($balanceSheetBase) ? $balanceSheetBase : (Route::has($balanceSheetKebab) ? $balanceSheetKebab : null);
                                    $balanceSheetUrl             = $balanceSheetResolved ? route($balanceSheetResolved) : '#';
                                    $balanceSheetGuardMsg        = Utility::fetchLinkMessage($lang, VW::RPT, 'view_balance_sheet_unavailable') ?? 'View balance sheet route is unavailable. Please contact technical support or your domain administrator.';
                                } catch (\Throwable $e) {
                                    \Log::error('reports/balance_sheet — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                }
@endphp
                            {{ Form::open([
                                'method'              => 'GET',
                                'url'                 => $balanceSheetUrl,
                                'id'                  => 'report_bill_summary',
                                'data-url'            => $balanceSheetUrl,
                                'data-guard-msg'      => $balanceSheetGuardMsg,
                                'data-sv-localized'   => 'true',
                            ]) }}
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script src="{{ asset('assets/js/routes/reports/balances/index/index.js') }}" defer></script>
                                @endpush
                                <div class="{{ VC::R_ALC_JCE }}">
                                    <div class="{{ VC::CXL10 }}">
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
                                        </div>
                                    </div>
                                    <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                        <div class="{{ VC::RW }}">
                                            <div class="{{ VC::C_AT }}">
                                                <a href="#" class="{{ VC::BT_SM_PM }}" data-submit-form="report_bill_summary" data-bs-toggle="tooltip" title="{{ __('Apply') }}" data-original-title="{{ __('apply') }}"><span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span></a>
                                                <a href="{{ $balanceSheetUrl }}"
                                                class="{{ VC::BT_SM_DG }} balance-sheet-reset"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('Reset') }}"
                                                data-original-title="{{ __('Reset') }}"
                                                data-url="{{ $balanceSheetUrl }}"
                                                data-guard-msg="{{ base64_encode($balanceSheetGuardMsg) }}"
                                                data-sv-localized="true">
                                                    <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                                </a>
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script src="{{ asset('assets/js/routes/reports/balances/index/reset.js') }}" defer></script>
                                                @endpush
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VC::RW }} justify-content-center" id="printableArea">
            <div class="{{ VC::CM8 }}">
                {{-- ── KPI Aggregation Cards ── --}}
                @php
                    $bsAssets = 0; $bsLiabEquity = 0;
                    foreach (($charts ?? []) as $bsType => $bsAccounts) {
                        $secTot = 0;
                        foreach ($bsAccounts as $bsAcct) {
                            $bsAccList = data_get($bsAcct,'account',[]);
                            $bsLast = (is_array($bsAccList) && !empty($bsAccList)) ? end($bsAccList) : null;
                            $secTot += (float) data_get($bsLast,'netAmount',0);
                        }
                        if ($bsType === 'Assets') { $bsAssets = $secTot; }
                        else { $bsLiabEquity += $secTot; }
                    }
                    $fmtBsAssets   = ($user?->priceFormat($bsAssets))     ?? number_format((float)$bsAssets, 2);
                    $fmtBsLiabEq  = ($user?->priceFormat($bsLiabEquity)) ?? number_format((float)$bsLiabEquity, 2);
                @endphp
                @include('reports.partials._kpi_cards', ['kpiHeading' => __('Balance Sheet Overview'), 'kpis' => [
                    ['label' => __('Total Assets'),               'value' => $fmtBsAssets,  'tone' => 'neutral'],
                    ['label' => __('Total Liabilities & Equity'), 'value' => $fmtBsLiabEq, 'tone' => 'neutral'],
                ]])

                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD }}">
                        <div class="account-main-title {{ VC::MB5 }}">
                            <h5>{{ __('Balance Sheet of') . ' ' . (data_get($creatorUser ?? null, 'name') ?? __('Could not find user name')) . ' ' . __('as of') . ' ' . (data_get($filter ?? [], 'startDateRange') ?? __('No start date available')) . ' ' . __('to') . ' ' . (data_get($filter ?? [], 'endDateRange') ?? __('No end date available')) }}</h5>
                        </div>
                        <div class="aacount-title {{ VC::DFL_AIC_JCB }} border-top border-bottom {{ VC::PY2 }}" role="row" aria-label="{{ __('Balance Sheet Column Headers') }}">
                            <h6 class="{{ VC::MB0 }}" role="columnheader">{{ __('Account') }}</h6>
                            <h6 class="{{ VC::MB0 }} text-center" role="columnheader">{{ __('Account Code') }}</h6>
                            <h6 class="{{ VC::MB0 }} text-end" role="columnheader">{{ __('Total') }}</h6>
                        </div>
                        @php
                            try {
                                $user ??= Auth::user();
                                $creatorUser ??= ($user ? \App\Models\User::find($user->creatorId()) : null);
                                $charts = is_iterable($chartAccounts ?? null) ? $chartAccounts : [];
                                $fmt = function($v) use ($user) { $u = $user; return ($u && method_exists($u,'priceFormat')) ? ($u->priceFormat($v) ?? number_format((float)$v,2)) : number_format((float)$v,2); };
                                $totalAmount = 0;
                            } catch (\Throwable $e) {
                                \Log::error('reports/balance_sheet — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        @foreach ($charts as $type => $accounts)
                            @if (!empty($accounts))
                                <div class="account-main-inner {{ VC::PY2 }}">
                                    @if ($type === 'Liabilities')
                                        <p class="fw-bold {{ VC::MB3 }}">{{ __('Liabilities & Equity') }}</p>
                                    @endif
                                    <p class="fw-bold ps-2 {{ VC::MB2 }}">{{ $type }}</p>
                                    @php
                                        $sectionTotal ??= 0;
@endphp
                                    @foreach ($accounts as $account)
                                        @php
                                            try {
                                                $subType = data_get($account,'subType') ?: '';
                                                $accList = data_get($account,'account',[]);
                                                $last = (is_array($accList) && !empty($accList)) ? end($accList) : null;
                                                $lastName = data_get($last,'account_name') ?? 0;
                                                $lastNet  = (float) data_get($last,'netAmount',0);
                                            } catch (\Throwable $e) {
                                                \Log::error('reports/balance_sheet — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        <div class="border-bottom {{ VC::PY2 }}">
                                            <p class="fw-bold ps-4 {{ VC::MB2 }}">{{ $subType }}</p>
                                            @foreach ($accList as $k => $record)
                                                @php
                                                    $name = (string)(data_get($record,'account_name') ?? '');
                                                    $isTotal = preg_match('/\btotal\b/i',$name) === 1;
@endphp
                                                @if ($k < count($accList) - 1)
                                                    @if (!$isTotal)
                                                        <div class="account-inner {{ VC::DFL_AIC_JCB }} ps-5">
                                                            @php
                                                                try {
                                                                    $accountId      = data_get($record, 'account_id');
                                                                    $ledgerBase     = VW::RPT.'.ledger';
                                                                    $ledgerKebab    = Str::kebab($ledgerBase);
                                                                    $ledgerResolved = Route::has($ledgerBase) ? $ledgerBase : (Route::has($ledgerKebab) ? $ledgerKebab : null);
                                                                    $ledgerUrl      = ($ledgerResolved && $accountId) ? route($ledgerResolved, [$accountId]) : '#';
                                                                    $ledgerHref     = ($ledgerUrl !== '#' && $accountId) ? ($ledgerUrl.'?account='.$accountId) : '#';
                                                                    $ledgerGuardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'view_ledger_unavailable') ?? 'View ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                    $ledgerLinkId   = 'ledger-view-'.($accountId ?? 'x');
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('reports/balance_sheet — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp
                                                            <p class="{{ VC::MB2 }}">
                                                                <a href="{{ $ledgerHref }}" id="{{ $ledgerLinkId }}" class="{{ VC::TX_PM }} ledger-view" data-url="{{ $ledgerHref }}" data-guard-msg="{{ base64_encode($ledgerGuardMsg) }}" data-sv-localized="true">
                                                                    {{ $name !== '' ? $name : __('No account name available') }}
                                                                </a>
                                                            </p>
                                                            @push(StacksConstants::ADM_SCR_PG)
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
                                                                                    const RG = window.RouteGuard || {};
                                                                                    (RG.showToast || (m => alert(m)))(msg);
                                                                                    l.setAttribute('data-failed-route', 'true');
                                                                                } catch (_) {}
                                                                            }, { passive: false });
                                                                        } catch (_) {}
                                                                    })();
                                                                </script>
                                                            @endpush
                                                            <p class="{{ VC::MB2 }} {{ VC::TXCT }}">{{ data_get($record,'account_code') ?? '-' }}</p>
                                                            <p class="text-primary mb-2 {{ VC::FEND }} text-end">{{ $fmt(data_get($record,'netAmount',0)) }}</p>
                                                        </div>
                                                    @endif
                                                @endif
                                            @endforeach
                                            <div class="account-inner {{ VC::DFL_AIC_JCB }} ps-4">
                                                <p class="fw-bold {{ VC::MB2 }}">{{ $lastName }}</p>
                                                <p class="fw-bold {{ VC::MB2 }} {{ VC::TX_END }}">{{ $fmt($lastNet) }}</p>
                                            </div>
                                        </div>
                                        @php
                                            $sectionTotal += $lastNet;
@endphp
                                    @endforeach
                                    <div class="aacount-title {{ VC::DFL_AIC_JCB }} border-top border-bottom {{ VC::PY2 }} px-2 pe-0">
                                        <h6 class="fw-bold {{ VC::MB0 }}">{{ __('Total for') . ' ' . $type }}</h6>
                                        <h6 class="fw-bold {{ VC::MB0 }} text-end">{{ $fmt($sectionTotal) }}</h6>
                                    </div>
                                    @php
                                        if ($type !== 'Assets') { $totalAmount += $sectionTotal; }
@endphp
                                </div>
                            @endif
                        @endforeach
                        @if($totalAmount !== 0)
                            <div class="aacount-title {{ VC::DFL_AIC_JCB }} border-bottom {{ VC::PY2 }} px-0">
                                <h6 class="fw-bold {{ VC::MB0 }}">{{ __('Total for Liabilities & Equity') }}</h6>
                                <h6 class="fw-bold {{ VC::MB0 }} text-end">{{ $fmt($totalAmount) }}</h6>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
