{{-- @extends(ExtendingLayoutsConstants::ADM) --}}
@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
        $authUser = $user?->creatorId() ?? null;
        $creatorUser = User::find($authUser);
        $settings = Utility::settings();
        $color = (!empty($settings[SettingsConstants::THML_CLR])) ? $settings[SettingsConstants::THML_CLR] : 'theme-3';
    } catch (\Throwable $e) {
        \Log::error('reports/profit_loss_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<html lang="{{ $lang ? str_replace('_', '-', is_string(app()->getLocale()) ? (app()->getLocale() : DatabaseConstants::DEFAULT_LANG) : '') }}" dir="{{$settings[SettingsConstants::RTL] == 'on'?'rtl':''}}">
    <head>
        <title>{{env('APP_NAME')}} - Profit & Loss</title>
        @include('fragments.std', [
        'meta_title' => $meta_title,
        'meta_desc' => $meta_desc,
        'meta_vp' => ''
        ])
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        @include('fragments.stylesheets', ['settings' => $settings[SettingsConstants::CLR_STG]])
        @if (isset($settings[SettingsConstants::RTL] ) && $settings[SettingsConstants::RTL] == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css')}}" id="main-style-link">
        @endif
    </head>
    <body class="{{ $color }}">
        <div class="{{ VC::MT4 }}">
        <div class="{{ VC::RW }} justify-content-center" id="printableArea">
            <div class="{{ VC::CM8 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD }}">
                        <div class="account-main-title {{ VC::MB5 }}">
                            <h5>
                                {{
                                    __('Profit & Loss of') . ' ' .
                                    ($creatorUser?->name ?? __('No creator name available')) .
                                    ' ' . __('as of') . ' ' .
                                    ($filter['startDateRange'] ?? __('No start date available')) . ' ' . __('to') . ' ' .
                                    ($filter['endDateRange'] ?? __('No end date available'))
                                }}
                            </h5>
                        </div>

                        <div class="aacount-title {{ VC::DFL_AIC_JCB }} border-top border-bottom {{ VC::PY2 }}">
                            <h6 class="{{ VC::MB0 }}">{{ __('Account') }}</h6>
                            <h6 class="{{ VC::MB0 }} text-center">{{ __('Account Code') }}</h6>
                            <h6 class="{{ VC::MB0 }} text-end">{{ __('Total') }}</h6>
                        </div>
                        @php
                            $totalIncome    ??= 0;
                            $totalCosts     ??= 0;
                            $totalExpenses  ??= 0;
                            $grossProfit    ??= 0;
                            $netProfit      ??= 0;
                            $printedIncome  ??= false;
                            $printedCOGS    ??= false;
                            $printedExpense ??= false;
                            try {
                                $hasAccounts    = !empty($chartAccounts) && is_iterable($chartAccounts);
                            } catch (\Throwable $e) {
                                \Log::error('reports/profit_loss_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        @if(!$hasAccounts)
                            <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                                <p class="fw-bold {{ VC::MB2 }}">{{ __('No account data available') }}</p>
                            </div>
                        @endif
                        @if($hasAccounts)
                            @foreach ($chartAccounts as $accounts)
                                @if (($accounts['Type'] ?? null) === 'Income')
                                    @php
 $printedIncome ??= true;
@endphp
                                    <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                                        <p class="fw-bold {{ VC::MB2 }}">{{ __('Income') }}</p>
                                        @forelse ($accounts['account'] ?? [] as $record)
                                            @php
                                                try {
                                                    $accId   = $record['account_id']  ?? null;
                                                    $accName = $record['account_name'] ?? __('No account name available');
                                                    $accCode = $record['account_code'] ?? __('No account code available');
                                                    $amount  = is_numeric($record['netAmount'] ?? null) ? $record['netAmount'] : 0;
                                                } catch (\Throwable $e) {
                                                    \Log::error('reports/profit_loss_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp

                                            <div class="account-inner {{ VC::DFL_AIC_JCB }}">
                                                @if (!preg_match('/\btotal\b/i', $accName) && $accId)
                                                    <p class="{{ VC::MB2 }} {{ VC::PS3 }}">
                                                        @php
                                                            try {
                                                                $ledgerBase = VW::RPT.'.ledger';
                                                                $ledgerKebab = Str::kebab($ledgerBase);
                                                                $ledgerResolved = Route::has($ledgerBase) ? $ledgerBase : (Route::has($ledgerKebab) ? $ledgerKebab : null);
                                                                $accIdValue = isset($accId) ? $accId : null;
                                                                $ledgerUrl = ($ledgerResolved && $accIdValue !== null) ? route($ledgerResolved, $accIdValue) : '#';
                                                                $ledgerHref = ($ledgerUrl !== '#' && $accIdValue !== null) ? ($ledgerUrl.'?account='.$accIdValue) : '#';
                                                                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                $ledgerGuardMsg = Utility::fetchLinkMessage($langValue, VW::RPT, 'view_ledger_unavailable') ?? 'Ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                $anchorId = 'ledger-open-'.($accIdValue ?? 'x');
                                                                $accLabel = $accName ?? __('No account name available');
                                                            } catch (\Throwable $e) {
                                                                \Log::error('reports/profit_loss_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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
                                                    </p>
                                                @else
                                                    <p class="fw-bold {{ VC::MB2 }}">
                                                        @if($accId)
                                                            @php
                                                                try {
                                                                    $ledgerBase = VW::RPT.'.ledger';
                                                                    $ledgerKebab = Str::kebab($ledgerBase);
                                                                    $ledgerResolved = Route::has($ledgerBase) ? $ledgerBase : (Route::has($ledgerKebab) ? $ledgerKebab : null);
                                                                    $accIdValue = isset($accId) ? $accId : null;
                                                                    $ledgerUrl = ($ledgerResolved && $accIdValue !== null) ? route($ledgerResolved, $accIdValue) : '#';
                                                                    $ledgerHref = ($ledgerUrl !== '#' && $accIdValue !== null) ? ($ledgerUrl.'?account='.$accIdValue) : '#';
                                                                    $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                    $ledgerGuardMsg = Utility::fetchLinkMessage($langValue, VW::RPT, 'view_ledger_unavailable') ?? 'Ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                    $anchorId = 'ledger-open-'.($accIdValue ?? 'x');
                                                                    $accLabel = $accName ?? __('No account name available');
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('reports/profit_loss_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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
                                                            <span class="{{ VC::TX_DK }}">{{ $accName }}</span>
                                                        @endif
                                                    </p>
                                                @endif
                                                <p class="{{ VC::MB2 }} {{ VC::TXCT }}">{{ $accCode }}</p>
                                                <p class="text-primary mb-2 {{ VC::FEND }} text-end">
                                                    {{ $user?->priceFormat($amount) }}
                                                </p>
                                            </div>
                                            @php
                                                if (strcasecmp($accName, 'Total Income') === 0)
                                                    $totalIncome = $amount;
@endphp
                                        @empty
                                            <p class="{{ VC::MB2 }} {{ VC::PS3 }}">{{ __('No income accounts available') }}</p>
                                        @endforelse
                                    </div>
                                @endif
                            @endforeach

                            @if(!$printedIncome)
                                <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                                    <p class="fw-bold {{ VC::MB2 }}">{{ __('Income') }}</p>
                                    <p class="{{ VC::MB2 }} {{ VC::PS3 }}">{{ __('No income accounts available') }}</p>
                                </div>
                            @endif

                            @foreach ($chartAccounts as $accounts)
                                @if (($accounts['Type'] ?? null) === 'Costs of Goods Sold')
                                    @php
 $printedCOGS ??= true;
@endphp
                                    <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                                        <p class="fw-bold {{ VC::MB2 }}">{{ __('Costs of Goods Sold') }}</p>

                                        @forelse ($accounts['account'] ?? [] as $record)
                                            @php
                                                try {
                                                    $accId   = $record['account_id']  ?? null;
                                                    $accName = $record['account_name'] ?? __('No account name available');
                                                    $accCode = $record['account_code'] ?? __('No account code available');
                                                    $rawAmt  = is_numeric($record['netAmount'] ?? null) ? $record['netAmount'] : 0;
                                                    $amount  = $rawAmt >= 0 ? $rawAmt : -$rawAmt;
                                                } catch (\Throwable $e) {
                                                    \Log::error('reports/profit_loss_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp

                                            <div class="account-inner {{ VC::DFL_AIC_JCB }}">
                                                @if (!preg_match('/\btotal\b/i', $accName) && $accId)
                                                    <p class="{{ VC::MB2 }} {{ VC::PS3 }}">
                                                        @php
                                                            try {
                                                                $ledgerBase = VW::RPT.'.ledger';
                                                                $ledgerKebab = Str::kebab($ledgerBase);
                                                                $ledgerResolved = Route::has($ledgerBase) ? $ledgerBase : (Route::has($ledgerKebab) ? $ledgerKebab : null);
                                                                $accIdValue = isset($accId) ? $accId : null;
                                                                $ledgerUrl = ($ledgerResolved && $accIdValue !== null) ? route($ledgerResolved, $accIdValue) : '#';
                                                                $ledgerHref = ($ledgerUrl !== '#' && $accIdValue !== null) ? ($ledgerUrl.'?account='.$accIdValue) : '#';
                                                                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                $ledgerGuardMsg = Utility::fetchLinkMessage($langValue, VW::RPT, 'view_ledger_unavailable') ?? 'Ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                $anchorId = 'ledger-open-'.($accIdValue ?? 'x');
                                                                $accLabel = $accName ?? __('No account name available');
                                                            } catch (\Throwable $e) {
                                                                \Log::error('reports/profit_loss_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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
                                                    </p>
                                                @else
                                                    <p class="fw-bold {{ VC::MB2 }}">
                                                        @if($accId)
                                                            @php
                                                                try {
                                                                    $ledgerBase = VW::RPT.'.ledger';
                                                                    $ledgerKebab = Str::kebab($ledgerBase);
                                                                    $ledgerResolved = Route::has($ledgerBase) ? $ledgerBase : (Route::has($ledgerKebab) ? $ledgerKebab : null);
                                                                    $accIdValue = isset($accId) ? $accId : null;
                                                                    $ledgerUrl = ($ledgerResolved && $accIdValue !== null) ? route($ledgerResolved, $accIdValue) : '#';
                                                                    $ledgerHref = ($ledgerUrl !== '#' && $accIdValue !== null) ? ($ledgerUrl.'?account='.$accIdValue) : '#';
                                                                    $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                    $ledgerGuardMsg = Utility::fetchLinkMessage($langValue, VW::RPT, 'view_ledger_unavailable') ?? 'Ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                    $anchorId = 'ledger-open-'.($accIdValue ?? 'x');
                                                                    $accLabel = $accName ?? __('No account name available');
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('reports/profit_loss_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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
                                                            <span class="{{ VC::TX_DK }}">{{ $accName }}</span>
                                                        @endif
                                                    </p>
                                                @endif
                                                <p class="{{ VC::MB2 }} {{ VC::TXCT }}">{{ $accCode }}</p>
                                                <p class="text-primary mb-2 {{ VC::FEND }} text-end">
                                                    {{ $user?->priceFormat($amount) }}
                                                </p>
                                            </div>
                                            @php
                                                if (strcasecmp($accName, 'Total Costs of Goods Sold') === 0)
                                                    $totalCosts = $amount;
@endphp
                                        @empty
                                            <p class="{{ VC::MB2 }} {{ VC::PS3 }}">{{ __('No costs of goods sold accounts available') }}</p>
                                        @endforelse
                                    </div>
                                @endif
                            @endforeach
                            @php
 $grossProfit = $totalIncome - $totalCosts;
@endphp
                            <div class="account-inner {{ VC::DFL_AIC_JCB }} border-bottom">
                                <p></p>
                                <p class="fw-bold {{ VC::MB2 }} {{ VC::TXCT }}">{{ __('Gross Profit') }}</p>
                                <p class="text-primary mb-2 {{ VC::FEND }} text-end">
                                    {{ $user?->priceFormat($grossProfit) }}
                                </p>
                            </div>
                            @foreach ($chartAccounts as $accounts)
                                @if (($accounts['Type'] ?? null) === 'Expenses')
                                    @php
 $printedExpense ??= true;
@endphp
                                    <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                                        <p class="fw-bold {{ VC::MB2 }}">{{ __('Expenses') }}</p>
                                        @forelse ($accounts['account'] ?? [] as $record)
                                            @php
                                                try {
                                                    $accId   = $record['account_id']  ?? null;
                                                    $accName = $record['account_name'] ?? __('No account name available');
                                                    $accCode = $record['account_code'] ?? __('No account code available');
                                                    $rawAmt  = is_numeric($record['netAmount'] ?? null) ? $record['netAmount'] : 0;
                                                    $amount  = $rawAmt >= 0 ? $rawAmt : -$rawAmt;
                                                } catch (\Throwable $e) {
                                                    \Log::error('reports/profit_loss_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <div class="account-inner {{ VC::DFL_AIC_JCB }}">
                                                @if (!preg_match('/\btotal\b/i', $accName) && $accId)
                                                    <p class="{{ VC::MB2 }} {{ VC::PS3 }}">
                                                        @php
                                                            try {
                                                                $ledgerBase = VW::RPT.'.ledger';
                                                                $ledgerKebab = Str::kebab($ledgerBase);
                                                                $ledgerResolved = Route::has($ledgerBase) ? $ledgerBase : (Route::has($ledgerKebab) ? $ledgerKebab : null);
                                                                $accIdValue = isset($accId) ? $accId : null;
                                                                $ledgerUrl = ($ledgerResolved && $accIdValue !== null) ? route($ledgerResolved, $accIdValue) : '#';
                                                                $ledgerHref = ($ledgerUrl !== '#' && $accIdValue !== null) ? ($ledgerUrl.'?account='.$accIdValue) : '#';
                                                                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                $ledgerGuardMsg = Utility::fetchLinkMessage($langValue, VW::RPT, 'view_ledger_unavailable') ?? 'Ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                $anchorId = 'ledger-open-'.($accIdValue ?? 'x');
                                                                $accLabel = $accName ?? __('No account name available');
                                                            } catch (\Throwable $e) {
                                                                \Log::error('reports/profit_loss_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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
                                                    </p>
                                                @else
                                                    <p class="fw-bold {{ VC::MB2 }}">
                                                        @if($accId)
                                                            @php
                                                                try {
                                                                    $ledgerBase = VW::RPT.'.ledger';
                                                                    $ledgerKebab = Str::kebab($ledgerBase);
                                                                    $ledgerResolved = Route::has($ledgerBase) ? $ledgerBase : (Route::has($ledgerKebab) ? $ledgerKebab : null);
                                                                    $accIdValue = isset($accId) ? $accId : null;
                                                                    $ledgerUrl = ($ledgerResolved && $accIdValue !== null) ? route($ledgerResolved, $accIdValue) : '#';
                                                                    $ledgerHref = ($ledgerUrl !== '#' && $accIdValue !== null) ? ($ledgerUrl.'?account='.$accIdValue) : '#';
                                                                    $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                    $ledgerGuardMsg = Utility::fetchLinkMessage($langValue, VW::RPT, 'view_ledger_unavailable') ?? 'Ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                    $anchorId = 'ledger-open-'.($accIdValue ?? 'x');
                                                                    $accLabel = $accName ?? __('No account name available');
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('reports/profit_loss_receipt — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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
                                                            <span class="{{ VC::TX_DK }}">{{ $accName }}</span>
                                                        @endif
                                                    </p>
                                                @endif
                                                <p class="{{ VC::MB2 }} {{ VC::TXCT }}">{{ $accCode }}</p>
                                                <p class="text-primary mb-2 {{ VC::FEND }} text-end">
                                                    {{ $user?->priceFormat($amount) }}
                                                </p>
                                            </div>
                                            @php
                                                if (strcasecmp($accName, 'Total Expenses') === 0)
                                                    $totalExpenses = $amount;
@endphp
                                        @empty
                                            <p class="{{ VC::MB2 }} {{ VC::PS3 }}">{{ __('No expenses accounts available') }}</p>
                                        @endforelse
                                    </div>
                                @endif
                            @endforeach
                            @php
 $netProfit = $grossProfit - $totalExpenses;
@endphp
                            <div class="account-inner {{ VC::DFL_AIC_JCB }} border-bottom">
                                <p></p>
                                <p class="fw-bold {{ VC::MB2 }} {{ VC::TXCT }}">{{ __('Net Profit/Loss') }}</p>
                                <p class="text-primary mb-2 {{ VC::FEND }} text-end">
                                    {{ $user?->priceFormat($netProfit) }}
                                </p>
                            </div>
                            @if(!$printedCOGS && $printedIncome)
                                <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                                    <p class="{{ VC::MB2 }} {{ VC::PS3 }}">{{ __('No costs of goods sold accounts available') }}</p>
                                </div>
                            @endif
                            @if(!$printedExpense)
                                <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                                    <p class="{{ VC::MB2 }} {{ VC::PS3 }}">{{ __('No expenses accounts available') }}</p>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script async src="{{ asset('assets/routes/reports/profits/index/loss/receipts/lang/pdf.js') }}"></script>
        <script defer src="{{ asset('assets/routes/reports/profits/index/loss/receipts/pdf.js') }}"></script>
    </body>
</html>
