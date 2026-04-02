@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user:$user);
        $authUser = $user?->creatorId() ?? null;
        $creatorUser = User::find($authUser);
    } catch (\Throwable $e) {
        \Log::error('reports/trial_balance — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Trial Balance') }}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Trial Balance') }}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script async src="{{ asset('assets/js/routes/reports/trials/balance/lang/pdf.js') }}">
    </script>
    <script defer src="{{ asset('assets/js/routes/reports/trials/balance/pdf.js') }}"></script>
@endpush

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        <a href="#" data-save-pdf class="{{ VC::BT_SM_PM }} me-1" data-bs-toggle="tooltip" title="{{ __('Print') }}"
           data-original-title="{{ __('Print') }}"><i class="ti ti-printer"></i></a>
    </div>
    <div class="{{ VC::FEND }} me-2">
        @php
            try {
                $trialExportBase               = VW::RPT.'.trial.balance.export';
                $trialExportKebab              = Str::kebab($trialExportBase);
                $trialExportResolved           = Route::has($trialExportBase) ? $trialExportBase : (Route::has($trialExportKebab) ? $trialExportKebab : null);
                $trialExportUrl                = $trialExportResolved ? route($trialExportResolved) : '#';
                $trialExportGuardMsg           = Utility::fetchLinkMessage($lang, VW::RPT, 'trial_balance_export_report_unavailable') ?? 'Trial balance export route is unavailable. Please contact technical support or your domain administrator.';
                $trialExportFormId             = 'report-trial-balance-export-form';
            } catch (\Throwable $e) {
                \Log::error('reports/trial_balance — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        {{ Form::open([
            'method'            => 'POST',
            'url'               => $trialExportUrl,
            'id'                => $trialExportFormId,
            'data-url'          => $trialExportUrl,
            'data-guard-msg'    => $trialExportGuardMsg,
            'data-sv-localized' => 'true',
        ]) }}
            @push(StacksConstants::ADM_SCR_PG)
                <script src="{{ asset('assets/js/routes/reports/trialBalanceExport.js') }}" defer></script>
            @endpush
            <input type="hidden" name="start_date" class="start_date">
            <input type="hidden" name="end_date" class="end_date">
            <button type="submit" class="{{ VC::BT_SM_PM }}" data-bs-toggle="tooltip" title="{{ __('Export') }}"
                data-original-title="{{ __('Export') }}"><i class="{{ VC::TI_EXP }}"></i></button>
        {{ Form::close() }}
    </div>
    <div class="{{ VC::FEND }} me-2" id="filter">
        <button id="filter" class="{{ VC::BT_SM_PM }}"><i class="ti ti-filter"></i></button>
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    @include('reports.partials._report_styles')
    <div class="{{ VC::RW }} justify-content-center">
        <div class="col-sm-8">
            <div class="{{ VC::MT2 }}" id="multiCollapseExample1">
                <div class="{{ VC::CD }}" id="show_filter" style="display:none;">
                    <div class="{{ VC::CD_BD }}">
                        @php
                            try {
                                $trialBase                    = VW::RPT.'.trial.balance';
                                $trialKebab                   = Str::kebab($trialBase);
                                $trialResolved                = Route::has($trialBase) ? $trialBase : (Route::has($trialKebab) ? $trialKebab : null);
                                $trialUrl                     = $trialResolved ? route($trialResolved) : '#';
                                $trialGuardMsg                = Utility::fetchLinkMessage($lang, VW::RPT, 'trial_balance_report_unavailable') ?? 'Trial balance report route is unavailable. Please contact technical support or your domain administrator.';
                            } catch (\Throwable $e) {
                                \Log::error('reports/trial_balance — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        {{ Form::open([
                            'method'            => 'GET',
                            'url'               => $trialUrl,
                            'id'                => 'report_trial_balance',
                            'data-url'          => $trialUrl,
                            'data-guard-msg'    => $trialGuardMsg,
                            'data-sv-localized' => 'true',
                        ]) }}
                            <div class="{{ VC::R_ALC_JCE }}">
                                <div class="{{ VC::CXL10 }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::CL_XL3 }}"><div class="btn-box"></div></div>
                                        <div class="{{ VC::CL_XL3 }}"><div class="btn-box"></div></div>
                                        <div class="{{ VC::CL_XL3 }}">
                                            <div class="btn-box">
                                                {{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
                                                {{ Form::date('start_date', $filter['startDateRange'] ?? null, ['class' => VC::FM_CT . ' startDate']) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XL3 }}">
                                            <div class="btn-box">
                                                {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                                                {{ Form::date('end_date', $filter['endDateRange'] ?? null, ['class' => VC::FM_CT . ' endDate']) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto {{ VC::MT4 }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::C_AT }}">
                                            <a href="#"
                                            id="trial-balance-apply"
                                            class="{{ VC::BT_SM_PM }}"
                                            data-target-form="report_trial_balance"
                                            data-guard-msg="{{ base64_encode($trialGuardMsg) }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>
                                            <a href="{{ $trialUrl }}"
                                            class="{{ VC::BT_SM_DG }} trial-balance-reset"
                                            data-url="{{ $trialUrl }}"
                                            data-guard-msg="{{ base64_encode($trialGuardMsg) }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Reset') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {{ Form::close() }}
                        @push(StacksConstants::ADM_SCR_PG)
                            <script src="{{ asset('assets/js/routes/reports/trials/balance/index.js') }}" defer></script>
                        @endpush
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::RW }} justify-content-center" id="printableArea">
        <div class="{{ VC::CM8 }}">
            {{-- ── KPI Aggregation Cards ── --}}
            @php
                $kpiDebit  = (float)($totalDebit ?? 0);
                $kpiCredit = (float)($totalCredit ?? 0);
                $fmtKpiDebit  = ($user?->priceFormat($kpiDebit))  ?? number_format($kpiDebit, 2);
                $fmtKpiCredit = ($user?->priceFormat($kpiCredit)) ?? number_format($kpiCredit, 2);
            @endphp
            @include('reports.partials._kpi_cards', ['kpiHeading' => __('Trial Balance Overview'), 'kpis' => [
                ['label' => __('Total Debit'),  'value' => $fmtKpiDebit,  'tone' => 'neutral'],
                ['label' => __('Total Credit'), 'value' => $fmtKpiCredit, 'tone' => 'neutral'],
            ]])

            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD }}">
                    <div class="account-main-title {{ VC::MB5 }}">
                        <h5>{{ __('Trial Balance of') . ' ' . ($creatorUser?->name ?? __('Anonymous')) . ' ' . __('as of') . ' ' . ($filter['startDateRange'] ?? __('Undefined date')) . ' ' . __('to') . ' ' . ($filter['endDateRange'] ?? __('Undefined date')) }}</h5>
                    </div>
                    <div class="aacount-title {{ VC::DFL_AIC_JCB }} border-top border-bottom {{ VC::PY2 }}" role="row" aria-label="{{ __('Trial Balance Column Headers') }}">
                        <h6 class="{{ VC::MB0 }}" role="columnheader">{{ __('Account') }}</h6>
                        <h6 class="{{ VC::MB0 }} text-center" role="columnheader">{{ __('Account Code') }}</h6>
                        <h6 class="{{ VC::MB0 }} text-end me-5" role="columnheader">{{ __('Debit') }}</h6>
                        <h6 class="{{ VC::MB0 }} text-end" role="columnheader">{{ __('Credit') }}</h6>
                    </div>
                    @php
                        $totalDebit  ??= 0.0;
                        $totalCredit ??= 0.0;
@endphp
                    @foreach ((is_iterable($totalAccounts ?? null) ? $totalAccounts : []) as $type => $accounts)
                        <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                            <p class="fw-bold ps-2 {{ VC::MB2 }}">{{ $type }}</p>
                            @foreach ($accounts as $record)
                                @php
                                    try {
                                        $accId    = data_get($record, 'id');
                                        $accName  = (string) data_get($record, 'name', __('Anonymous'));
                                        $accCode  = (string) data_get($record, 'code', __('Failed to get Code'));
                                        $debit    = (float) data_get($record, 'totalDebit', 0);
                                        $credit   = (float) data_get($record, 'totalCredit', 0);
                                        $totalDebit  += $debit;
                                        $totalCredit += $credit;
                                    } catch (\Throwable $e) {
                                        \Log::error('reports/trial_balance — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                    }
@endphp
                                <div class="account-inner {{ VC::DFL_AIC_JCB }}">
                                    @php
                                        try {
                                            $accIdVal           = isset($accId) ? $accId : null;
                                            $ledgerBase         = VW::RPT.'.ledger';
                                            $ledgerKebab        = Str::kebab($ledgerBase);
                                            $ledgerResolved     = Route::has($ledgerBase) ? $ledgerBase : (Route::has($ledgerKebab) ? $ledgerKebab : null);
                                            $ledgerParams       = $accIdVal ? [$accIdVal] : ['#'];
                                            $ledgerRouteUrl     = ($ledgerResolved && $accIdVal) ? route($ledgerResolved, $ledgerParams) : '#';
                                            $ledgerUrl          = ($ledgerRouteUrl !== '#') ? ($ledgerRouteUrl.'?account='.urlencode($accIdVal)) : '#';
                                            $ledgerGuardMsg     = Utility::fetchLinkMessage($lang, VW::RPT, 'ledger_report_unavailable') ?? 'Ledger report route is unavailable. Please contact technical support or your domain administrator.';
                                            $ledgerLinkId       = 'ledger-link-'.($accIdVal ?? 'x');
                                        } catch (\Throwable $e) {
                                            \Log::error('reports/trial_balance — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <p class="{{ VC::MB2 }}">
                                        <a href="{{ $ledgerUrl }}"
                                        id="{{ $ledgerLinkId }}"
                                        class="{{ VC::TX_PM }} report-ledger"
                                        data-url="{{ $ledgerUrl }}"
                                        data-guard-msg="{{ base64_encode($ledgerGuardMsg) }}"
                                        data-sv-localized="true">{{ $accName }}</a>
                                    </p>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                try {
                                                    const a = document.getElementById('{{ $ledgerLinkId }}');
                                                    if (!a) return;
                                                    const flag = 'data-click-listener';
                                                    if (a.hasAttribute(flag) && a.getAttribute(flag) === 'true') return;
                                                    a.setAttribute(flag, 'true');
                                                    a.addEventListener('click', function(e) {
                                                        try {
                                                            const href = a.getAttribute('href') || '#';
                                                            const url  = a.getAttribute('data-url') || href || '#';
                                                            if (href !== '#' || url !== '#') return;
                                                            e.preventDefault();
                                                            const msg = a.getAttribute('data-guard-msg') || 'Ledger report route is unavailable. Please contact technical support or your domain administrator.';
                                                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                            a.setAttribute('data-failed-route', 'true');
                                                        } catch (_) {}
                                                    }, { passive: false });
                                                } catch (_) {}
                                            })();
                                        </script>
                                    @endpush
                                    <p class="{{ VC::MB2 }} text-center">{{ $accCode }}</p>
                                    <p class="text-primary {{ VC::MB2 }} text-end me-5">{{ $user?->priceFormat($debit) }}</p>
                                    <p class="text-primary {{ VC::MB2 }} {{ VC::FEND }} text-end">{{ $user?->priceFormat($credit) }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endforeach

                    @if (!empty($totalAccounts))
                        <div class="aacount-title {{ VC::DFL_AIC_JCB }} border-top border-bottom {{ VC::PY2 }} px-2 pe-0">
                            <h6 class="fw-bold {{ VC::MB0 }}">{{ __('Total') }}</h6>
                            <h6 class="fw-bold {{ VC::MB0 }}"></h6>
                            <h6 class="fw-bold {{ VC::MB0 }} text-end me-5">{{ $user?->priceFormat($totalDebit) }}</h6>
                            <h6 class="fw-bold {{ VC::MB0 }} text-end">{{ $user?->priceFormat($totalCredit) }}</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/reports/trials/balance/lang/date.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/reports/trials/balance/date.js') }}"></script>
@endpush
