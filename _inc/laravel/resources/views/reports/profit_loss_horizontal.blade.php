@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
        $authUser = $user?->creatorId() ?? null;
        $creatorUser = User::find($authUser);
    } catch (\Throwable $e) {
        \Log::error('reports/profit_loss_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Profit & Loss') }}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Profit & Loss') }}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script async src="{{ asset('assets/js/routes/reports/profits/horizontal/loss/lang/toggle.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/reports/profits/horizontal/loss/toggle.js') }}"></script>
@endpush
{{-- <div class="{{ VC::FEND }}">
    <a href="#" class="{{ VC::BT_SM_PM }}" onclick="saveAsPDF()"data-bs-toggle="tooltip"
        title="{{ __('Download') }}" data-original-title="{{ __('Download') }}">
        <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
    </a>
</div> --}}
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @php
            try {
                $printBase = VW::RPT.'.profit.loss.print';
                $printKebab = Str::kebab($printBase);
                $printResolved = Route::has($printBase) ? $printBase : (Route::has($printKebab) ? $printKebab : null);
                $actionRoute = $printResolved ? [$printResolved, 'horizontal'] : ['#'];
                $actionUrl = $printResolved ? route($printResolved, 'horizontal') : '#';
                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                $guardMsg = Utility::fetchLinkMessage($langValue, VW::RPT, 'print_profit_loss_route_unavailable') ?? 'Print profit and loss route is unavailable. Please contact technical support or your domain administrator.';
            } catch (\Throwable $e) {
                \Log::error('reports/profit_loss_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        {{ Form::open(['route' => $actionRoute, 'method' => 'POST', 'id' => 'profit-loss-print-horizontal', 'data-url' => $actionUrl, 'data-guard-msg' => $guardMsg, 'data-sv-localized' => 'true']) }}
            <input type="hidden" name="start_date" class="start_date">
            <input type="hidden" name="end_date" class="end_date">
            <button type="submit" class="{{ VC::BT_SM_PM }}" data-bs-toggle="tooltip" title="{{ __('Print') }}" aria-label="{{ __('Print') }}">
                <i class="ti ti-printer"></i>
            </button>
        {{ Form::close() }}
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/profits/horizontal/loss/print.js') }}" defer></script>
        @endpush
    </div>
    <div class="{{ VC::FEND }} me-2">
        @php
            try {
                $exportBase = VW::RPT.'.profit.loss.export';
                $exportKebab = Str::kebab($exportBase);
                $exportResolved = Route::has($exportBase) ? $exportBase : (Route::has($exportKebab) ? $exportKebab : null);
                $actionRoute = $exportResolved ? [$exportResolved] : ['#'];
                $actionUrl = $exportResolved ? route($exportResolved) : '#';
                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                $guardMsg = Utility::fetchLinkMessage($langValue, VW::RPT, 'export_profit_loss_route_unavailable') ?? 'Export profit and loss route is unavailable. Please contact technical support or your domain administrator.';
            } catch (\Throwable $e) {
                \Log::error('reports/profit_loss_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        {{ Form::open(['route' => $actionRoute, 'method' => 'POST', 'id' => 'profit-loss-export', 'data-url' => $actionUrl, 'data-guard-msg' => $guardMsg, 'data-sv-localized' => 'true']) }}
            <input type="hidden" name="start_date" class="start_date">
            <input type="hidden" name="end_date" class="end_date">
            <button type="submit" class="{{ VC::BT_SM_PM }}" data-bs-toggle="tooltip" title="{{ __('Export') }}" aria-label="{{ __('Export') }}">
                <i class="{{ VC::TI_EXP }}"></i>
            </button>
        {{ Form::close() }}
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/profits/horizontal/loss/export.js') }}" defer></script>
        @endpush
    </div>
    <div class="{{ VC::FEND }} me-2">
        <button id="toggle-filter" class="{{ VC::BT_SM_PM }}" type="button" data-bs-toggle="tooltip" title="{{ __('Filter') }}" aria-label="{{ __('Filter') }}">
            <i class="ti ti-filter"></i>
        </button>
    </div>
    <div class="{{ VC::FEND }} me-2">
        @php
            try {
                $profitLossBase = ViewsConstants::RPT.'.profit.loss';
                $profitLossKebab = Str::kebab($profitLossBase);
                $profitLossResolved = Route::has($profitLossBase) ? $profitLossBase : (Route::has($profitLossKebab) ? $profitLossKebab : null);
                $verticalUrl = $profitLossResolved ? route($profitLossResolved, 'vertical') : '#';
                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                $verticalGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::RPT, 'open_profit_loss_vertical_route_unavailable') ?? 'Vertical profit & loss view route is unavailable. Please contact technical support or your domain administrator.';
            } catch (\Throwable $e) {
                \Log::error('reports/profit_loss_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <a id="profit-loss-vertical-view"
        href="{{ $verticalUrl }}"
        class="{{ VC::BT_SM_PM }}"
        data-url="{{ $verticalUrl }}"
        data-guard-msg="{{ base64_encode($verticalGuardMsg) }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ __('Vertical View') }}"
        aria-label="{{ __('Vertical View') }}">
            <i class="ti ti-separator-horizontal"></i>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/profits/horizontal/loss/vertical.js') }}" defer></script>
        @endpush
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }} justify-content-center">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::MT2 }}" id="multiCollapseExample1">
                <div class="{{ VC::CD }}" id="show_filter" style="display:none;">
                    <div class="{{ VC::CD_BD }}">
                        @php
                            try {
                                $profitLossBase = ViewsConstants::RPT.'.profit.loss';
                                $profitLossKebab = Str::kebab($profitLossBase);
                                $profitLossResolved = Route::has($profitLossBase) ? $profitLossBase : (Route::has($profitLossKebab) ? $profitLossKebab : null);
                                $actionRoute = $profitLossResolved ? [$profitLossResolved] : ['#'];
                                $actionUrl = $profitLossResolved ? route($profitLossResolved) : '#';
                                $resetUrl = $profitLossResolved ? route($profitLossResolved, 'horizontal') : '#';
                                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                $applyGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::RPT, 'apply_profit_loss_route_unavailable') ?? 'Apply profit & loss route is unavailable. Please contact technical support or your domain administrator.';
                                $resetGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::RPT, 'reset_profit_loss_route_unavailable') ?? 'Reset profit & loss route is unavailable. Please contact technical support or your domain administrator.';
                            } catch (\Throwable $e) {
                                \Log::error('reports/profit_loss_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        {{ Form::open([
                            'route'             => $actionRoute,
                            'method'            => 'GET',
                            'id'                => 'report_profit_loss_horizontal',
                            'data-url'          => $actionUrl,
                            'data-guard-msg'    => $applyGuardMsg,
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
                                                {{ Form::date('start_date', $filter['startDateRange'], ['class' => 'startDate ' . VC::FM_CT]) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XL3 }}">
                                            <div class="btn-box">
                                                {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                                                {{ Form::date('end_date', $filter['endDateRange'], ['class' => 'endDate ' . VC::FM_CT]) }}
                                            </div>
                                        </div>
                                        <input type="hidden" name="view" value="horizontal">
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT }} mt-4">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::C_AT }}">
                                            <a id="apply-profit-loss-horizontal"
                                            href="#"
                                            class="{{ VC::BT_SM_PM }}"
                                            data-form-id="report_profit_loss_horizontal"
                                            data-guard-msg="{{ base64_encode($applyGuardMsg) }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}"
                                            aria-label="{{ __('Apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>
                                            <a id="reset-profit-loss-horizontal"
                                            href="{{ $resetUrl }}"
                                            class="{{ VC::BT_SM_DG }}"
                                            data-url="{{ $resetUrl }}"
                                            data-guard-msg="{{ base64_encode($resetGuardMsg) }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Reset') }}"
                                            aria-label="{{ __('Reset') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {{ Form::close() }}
                        @push(StacksConstants::ADM_SCR_PG)
                            <script src="{{ asset('assets/js/routes/reports/profits/horizontal/loss/apply.js') }}" defer></script>
                            <script src="{{ asset('assets/js/routes/reports/profits/horizontal/loss/reset.js') }}" defer></script>
                        @endpush
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::RW }} justify-content-center" id="printableArea">
        <div class="{{ VC::CM12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD }}">
                    <div class="account-main-title {{ VC::MB4 }}">
                        <h5>
                            {{ __('Profit & Loss') }}
                            {{ !empty($creatorUser?->name) ? $creatorUser->name : __('User not available') }}
                            {{ __('as of') }}
                            {{ !empty($filter['startDateRange']) ? $filter['startDateRange'] : __('Start date not available') }}
                            {{ __('to') }}
                            {{ !empty($filter['endDateRange']) ? $filter['endDateRange'] : __('End date not available') }}
                        </h5>
                    </div>
                    @php
                        $totalIncome ??= 0;
                        $totalCosts  ??= 0;
                        $grossProfit ??= 0;
@endphp
                    <div class="{{ VC::RW }}">
                        <div class="{{ VC::CM6 }}">
                            <div class="aacount-title {{ VC::DFL_AIC_JCB }} border py-2">
                                <h5 class="{{ VC::MB0 }} ms-3">{{ __('Expenses') }}</h5>
                            </div>
                            <div class="border-start border-end">
                                @php
 $hasExpenseRows ??= false;
@endphp
                                @foreach ($chartAccounts as $accounts)
                                    @if (isset($accounts['Type']) && ($accounts['Type'] === 'Expenses' || $accounts['Type'] === 'Costs of Goods Sold'))
                                        @php
 $hasExpenseRows ??= true;
@endphp
                                        <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                                            <p class="fw-bold {{ VC::MB1 }} ms-3">{{ $accounts['Type'] }}</p>
                                            @foreach ($accounts['account'] as $record)
                                                @php
                                                    try {
                                                        $accName   = $record['account_name'] ?? __('Account name not available');
                                                        $accCode   = $record['account_code'] ?? __('Account code not available');
                                                        $accId     = $record['account_id'] ?? null;
                                                        $rawNet    = $record['netAmount'] ?? 0;
                                                        $netAmount = $rawNet > 0 ? $rawNet : -$rawNet;
                                                    } catch (\Throwable $e) {
                                                        \Log::error('reports/profit_loss_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <div class="account-inner {{ VC::DFL_AIC_JCB }}">
                                                    @if (!preg_match('/\btotal\b/i', (string)$accName))
                                                        <p class="{{ VC::MB1 }} ps-3 ms-3">
                                                            @if(!empty($accId))
                                                                @php
                                                                    try {
                                                                        $ledgerBase = ViewsConstants::RPT.'.ledger';
                                                                        $ledgerKebab = Str::kebab($ledgerBase);
                                                                        $ledgerResolved = Route::has($ledgerBase) ? $ledgerBase : (Route::has($ledgerKebab) ? $ledgerKebab : null);
                                                                        $accIdValue = isset($accId) ? $accId : null;
                                                                        $ledgerUrl = ($ledgerResolved && $accIdValue !== null) ? route($ledgerResolved, $accIdValue) : '#';
                                                                        $ledgerHref = ($ledgerUrl !== '#' && $accIdValue !== null) ? ($ledgerUrl.'?account='.$accIdValue) : '#';
                                                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                        $ledgerGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::RPT, 'view_ledger_unavailable') ?? 'Ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                        $anchorId = 'ledger-open-'.($accIdValue ?? 'x');
                                                                        $accLabel = $accName ?? __('No account name available');
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('reports/profit_loss_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                                <a id="{{ $anchorId }}"
                                                                href="{{ $ledgerHref }}"
                                                                class="{{ VC::TX_PM }}"
                                                                data-url="{{ $ledgerHref }}"
                                                                data-guard-msg="{{ base64_encode($ledgerGuardMsg) }}"
                                                                data-sv-localized="true">
                                                                    {{ $accLabel }}
                                                                </a>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            try {
                                                                                const el = document.getElementById('{{ $anchorId }}');
                                                                                if (!el) { return; }
                                                                                if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                                el.setAttribute('data-listener-active', 'true');
                                                                                el.addEventListener('click', (e) => {
                                                                                    try {
                                                                                        const href = el.getAttribute('href') ?? '#';
                                                                                        const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                        if (url !== '#' && href !== '#') { return; }
                                                                                        e.preventDefault();
                                                                                        const msg = el.getAttribute('data-guard-msg') ?? 'Ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                        el.setAttribute('data-failed-route', 'true');
                                                                                    } catch (err) {}
                                                                                });
                                                                            } catch (err) {}
                                                                        })();
                                                                    </script>
                                                                @endpush
                                                            @else
                                                                {{ $accName }}
                                                            @endif
                                                        </p>
                                                    @else
                                                        <p class="fw-bold {{ VC::MB1 }} ms-3">
                                                            @if(!empty($accId))
                                                                @php
                                                                    try {
                                                                        $ledgerBase = ViewsConstants::RPT.'.ledger';
                                                                        $ledgerKebab = Str::kebab($ledgerBase);
                                                                        $ledgerResolved = Route::has($ledgerBase) ? $ledgerBase : (Route::has($ledgerKebab) ? $ledgerKebab : null);
                                                                        $accIdValue = isset($accId) ? $accId : null;
                                                                        $ledgerUrl = ($ledgerResolved && $accIdValue !== null) ? route($ledgerResolved, $accIdValue) : '#';
                                                                        $ledgerHref = ($ledgerUrl !== '#' && $accIdValue !== null) ? ($ledgerUrl.'?account='.$accIdValue) : '#';
                                                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                        $ledgerGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::RPT, 'view_ledger_unavailable') ?? 'Ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                        $anchorId = 'ledger-open-'.($accIdValue ?? 'x');
                                                                        $accLabel = $accName ?? __('No account name available');
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('reports/profit_loss_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                                <a id="{{ $anchorId }}"
                                                                href="{{ $ledgerHref }}"
                                                                class="{{ VC::TX_DK }}"
                                                                data-url="{{ $ledgerHref }}"
                                                                data-guard-msg="{{ base64_encode($ledgerGuardMsg) }}"
                                                                data-sv-localized="true">
                                                                    {{ $accLabel }}
                                                                </a>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            try {
                                                                                const el = document.getElementById('{{ $anchorId }}');
                                                                                if (!el) { return; }
                                                                                if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                                el.setAttribute('data-listener-active', 'true');
                                                                                el.addEventListener('click', (e) => {
                                                                                    try {
                                                                                        const href = el.getAttribute('href') ?? '#';
                                                                                        const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                        if (url !== '#' && href !== '#') { return; }
                                                                                        e.preventDefault();
                                                                                        const msg = el.getAttribute('data-guard-msg') ?? 'Ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                        el.setAttribute('data-failed-route', 'true');
                                                                                    } catch (err) {}
                                                                                });
                                                                            } catch (err) {}
                                                                        })();
                                                                    </script>
                                                                @endpush
                                                            @else
                                                                {{ $accName }}
                                                            @endif
                                                        </p>
                                                    @endif
                                                    <p class="{{ VC::MB1 }} text-center">{{ $accCode }}</p>
                                                    @if (!preg_match('/\btotal\b/i', (string)$accName))
                                                        <p class="text-primary {{ VC::MB1 }} float-end text-end me-3">{{ $user?->priceFormat($netAmount) }}</p>
                                                    @else
                                                        <p class="{{ VC::MB1 }} float-end text-end me-3 fw-bold text-dark">{{ $user?->priceFormat($netAmount) }}</p>
                                                    @endif
                                                </div>
                                                @php
                                                    try {
                                                        if ($accName === 'Total Income')
                                                            $totalIncome = $rawNet ?? 0;
                                                        if ($accName === 'Total Costs of Goods Sold')
                                                            $totalCosts = $netAmount ?? 0;
                                                        $grossProfit = $totalIncome - $totalCosts;
                                                    } catch (\Throwable $e) {
                                                        \Log::error('reports/profit_loss_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                            @endforeach
                                        </div>
                                    @endif
                                @endforeach
                                @if(!$hasExpenseRows)
                                    <div class="p-3">{{ __('No expense data available for this period') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="{{ VC::CM6 }}">
                            <div class="aacount-title {{ VC::DFL_AIC_JCB }} border py-2">
                                <h5 class="{{ VC::MB0 }} ms-3">{{ __('Income') }}</h5>
                            </div>
                            <div class="border-start border-end">
                                @php
 $hasIncomeRows ??= false;
@endphp
                                @foreach ($chartAccounts as $accounts)
                                    @if (isset($accounts['Type']) && $accounts['Type'] === 'Income')
                                        @php
 $hasIncomeRows ??= true;
@endphp
                                        <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                                            <p class="fw-bold {{ VC::MB1 }} ms-3">{{ $accounts['Type'] }}</p>
                                            @foreach ($accounts['account'] as $record)
                                                @php
                                                    try {
                                                        $accName = $record['account_name'] ?? __('Account name not available');
                                                        $accCode = $record['account_code'] ?? __('Account code not available');
                                                        $accId   = $record['account_id'] ?? null;
                                                        $amount  = $record['netAmount'] ?? 0;
                                                    } catch (\Throwable $e) {
                                                        \Log::error('reports/profit_loss_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <div class="account-inner {{ VC::DFL_AIC_JCB }}">
                                                    @if (!preg_match('/\btotal\b/i', (string)$accName))
                                                        <p class="{{ VC::MB1 }} ps-3 ms-3">
                                                            @if(!empty($accId))
                                                                @php
                                                                    try {
                                                                        $ledgerBase = ViewsConstants::RPT.'.ledger';
                                                                        $ledgerKebab = Str::kebab($ledgerBase);
                                                                        $ledgerResolved = Route::has($ledgerBase) ? $ledgerBase : (Route::has($ledgerKebab) ? $ledgerKebab : null);
                                                                        $accIdValue = isset($accId) ? $accId : null;
                                                                        $ledgerUrl = ($ledgerResolved && $accIdValue !== null) ? route($ledgerResolved, $accIdValue) : '#';
                                                                        $ledgerHref = ($ledgerUrl !== '#' && $accIdValue !== null) ? ($ledgerUrl.'?account='.$accIdValue) : '#';
                                                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                        $ledgerGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::RPT, 'view_ledger_unavailable') ?? 'Ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                        $anchorId = 'ledger-open-'.($accIdValue ?? 'x');
                                                                        $accLabel = $accName ?? __('No account name available');
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('reports/profit_loss_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                                <a id="{{ $anchorId }}"
                                                                href="{{ $ledgerHref }}"
                                                                class="{{ VC::TX_PM }}"
                                                                data-url="{{ $ledgerHref }}"
                                                                data-guard-msg="{{ base64_encode($ledgerGuardMsg) }}"
                                                                data-sv-localized="true">
                                                                    {{ $accLabel }}
                                                                </a>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            try {
                                                                                const el = document.getElementById('{{ $anchorId }}');
                                                                                if (!el) { return; }
                                                                                if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                                el.setAttribute('data-listener-active', 'true');
                                                                                el.addEventListener('click', (e) => {
                                                                                    try {
                                                                                        const href = el.getAttribute('href') ?? '#';
                                                                                        const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                        if (url !== '#' && href !== '#') { return; }
                                                                                        e.preventDefault();
                                                                                        const msg = el.getAttribute('data-guard-msg') ?? 'Ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                        el.setAttribute('data-failed-route', 'true');
                                                                                    } catch (err) {}
                                                                                });
                                                                            } catch (err) {}
                                                                        })();
                                                                    </script>
                                                                @endpush
                                                            @else
                                                                {{ $accName }}
                                                            @endif
                                                        </p>
                                                    @else
                                                        <p class="fw-bold {{ VC::MB1 }} ms-3">
                                                            @if(!empty($accId))
                                                                @php
                                                                    try {
                                                                        $ledgerBase = ViewsConstants::RPT.'.ledger';
                                                                        $ledgerKebab = Str::kebab($ledgerBase);
                                                                        $ledgerResolved = Route::has($ledgerBase) ? $ledgerBase : (Route::has($ledgerKebab) ? $ledgerKebab : null);
                                                                        $accIdValue = isset($accId) ? $accId : null;
                                                                        $ledgerUrl = ($ledgerResolved && $accIdValue !== null) ? route($ledgerResolved, $accIdValue) : '#';
                                                                        $ledgerHref = ($ledgerUrl !== '#' && $accIdValue !== null) ? ($ledgerUrl.'?account='.$accIdValue) : '#';
                                                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                        $ledgerGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::RPT, 'view_ledger_unavailable') ?? 'Ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                        $anchorId = 'ledger-open-'.($accIdValue ?? 'x');
                                                                        $accLabel = $accName ?? __('No account name available');
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('reports/profit_loss_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                                <a id="{{ $anchorId }}"
                                                                href="{{ $ledgerHref }}"
                                                                class="{{ VC::TX_DK }}"
                                                                data-url="{{ $ledgerHref }}"
                                                                data-guard-msg="{{ base64_encode($ledgerGuardMsg) }}"
                                                                data-sv-localized="true">
                                                                    {{ $accLabel }}
                                                                </a>
                                                                @push(StacksConstants::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            try {
                                                                                const el = document.getElementById('{{ $anchorId }}');
                                                                                if (!el) { return; }
                                                                                if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                                el.setAttribute('data-listener-active', 'true');
                                                                                el.addEventListener('click', (e) => {
                                                                                    try {
                                                                                        const href = el.getAttribute('href') ?? '#';
                                                                                        const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                        if (url !== '#' && href !== '#') { return; }
                                                                                        e.preventDefault();
                                                                                        const msg = el.getAttribute('data-guard-msg') ?? 'Ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                                        el.setAttribute('data-failed-route', 'true');
                                                                                    } catch (err) {}
                                                                                });
                                                                            } catch (err) {}
                                                                        })();
                                                                    </script>
                                                                @endpush
                                                            @else
                                                                {{ $accName }}
                                                            @endif
                                                        </p>
                                                    @endif
                                                    <p class="{{ VC::MB1 }} text-center">{{ $accCode }}</p>
                                                    @if (!preg_match('/\btotal\b/i', (string)$accName))
                                                        <p class="text-primary {{ VC::MB1 }} float-end text-end me-3">{{ $user?->priceFormat($amount) }}</p>
                                                    @else
                                                        <p class="{{ VC::MB1 }} float-end text-end me-3 fw-bold text-dark">{{ $user?->priceFormat($amount) }}</p>
                                                    @endif
                                                </div>
                                                @php
                                                    try {
                                                        if ($accName === 'Total Income')
                                                            $totalIncome = $amount ?? 0;
                                                        if ($accName === 'Total Costs of Goods Sold')
                                                            $totalCosts = ($amount ?? 0) > 0 ? $amount : -($amount ?? 0);
                                                        $grossProfit = $totalIncome - $totalCosts;
                                                    } catch (\Throwable $e) {
                                                        \Log::error('reports/profit_loss_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                            @endforeach
                                        </div>
                                    @endif
                                @endforeach
                                @if(!$hasIncomeRows)
                                    <div class="p-3">{{ __('No income data available for this period') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @php
                        $summaryAvailable = ($totalIncome !== 0 || $totalCosts !== 0);
@endphp
                    @if($summaryAvailable)
                        <div class="{{ VC::RW }} mt-3">
                            <div class="{{ VC::CM12 }}">
                                <div class="aacount-title {{ VC::DFL_AIC_JCB }} border-top border-bottom py-2 px-3">
                                    <h6 class="{{ VC::MB0 }}">{{ __('Gross Profit') }}</h6>
                                    <h6 class="{{ VC::MB0 }}">{{ $user?->priceFormat($grossProfit) }}</h6>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="{{ VC::MT3 }} {{ VC::PX3 }}">{{ __('No profit and loss summary available for this period') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
