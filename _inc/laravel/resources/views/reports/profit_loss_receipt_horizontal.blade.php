{{--  @extends(ExtendingLayoutsConstants::ADM) --}}
@php
    try {
$settings = Utility::settings();
        $color = (!empty($settings[SettingsConstants::THML_CLR])) ? $settings[SettingsConstants::THML_CLR] : 'theme-3';
        $user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
        $authUser = $user?->creatorId() ?? null;
        $creatorUser = User::find($authUser);
    } catch (\Throwable $e) {
        \Log::error('reports/profit_loss_receipt_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
<html lang="{{ $lang ? str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{$settings[SettingsConstants::RTL] == 'on'?'rtl':''}}">
    <head>
        <title>{{env('APP_NAME')}} - Profit & Loss</title>
        @include('fragments.std', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc,
            'meta_vp' => ''
        ])
        @include('fragments.stylesheets', ['settings' => $settings[SettingsConstants::CLR_STG]])
        @if (isset($settings[SettingsConstants::RTL] ) && $settings[SettingsConstants::RTL] == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css')}}" id="main-style-link">
        @endif
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script async src="{{ asset('assets/js/routes/reports/profits/horizontal/loss/receipts/lang/pdf.js') }}"></script>
        <script async src="{{ asset('assets/js/routes/reports/profits/horizontal/loss/receipts/pdf.js') }}"></script>
    </head>
    <body class="{{ $color }}">
        <div class="{{ VC::MT4 }}">
            <div class="{{ VC::RW }} justify-content-center" id="printableArea">
                <div class="{{ VC::CM12 }}">
                    <div class="{{ VC::CD }}">
                        <div class="{{ VC::CD_BD }}">
                            <div class="account-main-title {{ VC::MB5 }}">
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
                                $netProfit   ??= 0;
                                $totalCosts  ??= 0;
                                $grossProfit ??= 0;
                                $hasExpenseRows ??= false;
                                $hasIncomeRows  ??= false;
@endphp
                            <div class="{{ VC::RW }}">
                                <div class="{{ VC::CM6 }}">
                                    <div class="aacount-title {{ VC::DFL_AIC_JCB }} {{ VC::BD }} {{ VC::PY2 }}">
                                        <h5 class="{{ VC::MB0 }} ms-3">{{ __('Expenses') }}</h5>
                                    </div>
                                    <div class="border-start border-end">
                                        @foreach ($chartAccounts as $accounts)
                                            @if (($accounts['Type'] ?? null) == 'Expenses' || ($accounts['Type'] ?? null) == 'Costs of Goods Sold')
                                                @php
 $hasExpenseRows ??= true;
@endphp
                                                <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                                                    <p class="fw-bold {{ VC::MB2 }} ms-3">{{ $accounts['Type'] ?? __('Type not available') }}</p>
                                                    @foreach ($accounts['account'] as $record)
                                                        @php
                                                            try {
                                                                $accId     = $record['account_id']   ?? null;
                                                                $accName   = $record['account_name'] ?? __('Account name not available');
                                                                $accCode   = $record['account_code'] ?? __('Account code not available');
                                                                $rawAmount = $record['netAmount']     ?? 0;
                                                                $netAmount = $rawAmount > 0 ? $rawAmount : -$rawAmount;
                                                            } catch (\Throwable $e) {
                                                                \Log::error('reports/profit_loss_receipt_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <div class="account-inner {{ VC::DFL_AIC_JCB }}">
                                                            @if (!preg_match('/\btotal\b/i', (string) $accName))
                                                                <p class="{{ VC::MB2 }} {{ VC::PS3 }} ms-3">
                                                                    @if(!empty($accId))
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
                                                                                \Log::error('reports/profit_loss_receipt_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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
                                                                <p class="fw-bold {{ VC::MB2 }} ms-3">
                                                                    @if(!empty($accId))
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
                                                                                \Log::error('reports/profit_loss_receipt_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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
                                                            <p class="{{ VC::MB2 }} {{ VC::TXCT }}">{{ $accCode }}</p>
                                                            @if (!preg_match('/\btotal\b/i', (string) $accName))
                                                                <p class="text-primary mb-2 {{ VC::FEND }} text-end {{ VC::ME3 }}">
                                                                    {{ $user?->priceFormat($netAmount) }}
                                                                </p>
                                                            @else
                                                                <p class="mb-2 {{ VC::FEND }} text-end {{ VC::ME3 }} fw-bold text-dark">
                                                                    {{ $user?->priceFormat($netAmount) }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                        @php
                                                            try {
                                                                if ($accName === 'Total Income')
                                                                    $totalIncome = $rawAmount;
                                                                if ($accName === 'Total Costs of Goods Sold')
                                                                    $totalCosts = $netAmount;
                                                                $grossProfit = $totalIncome - $totalCosts;
                                                            } catch (\Throwable $e) {
                                                                \Log::error('reports/profit_loss_receipt_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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
                                    <div class="aacount-title {{ VC::DFL_AIC_JCB }} {{ VC::BD }} {{ VC::PY2 }}">
                                        <h5 class="{{ VC::MB0 }} ms-3">{{ __('Income') }}</h5>
                                    </div>

                                    <div class="border-start border-end">
                                        @foreach ($chartAccounts as $accounts)
                                            @if (($accounts['Type'] ?? null) == 'Income')
                                                @php
 $hasIncomeRows ??= true;
@endphp
                                                <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                                                    <p class="fw-bold {{ VC::MB2 }} ms-3">{{ $accounts['Type'] ?? __('Type not available') }}</p>

                                                    @foreach ($accounts['account'] as $record)
                                                        @php
                                                            try {
                                                                $accId   = $record['account_id']   ?? null;
                                                                $accName = $record['account_name'] ?? __('Account name not available');
                                                                $accCode = $record['account_code'] ?? __('Account code not available');
                                                                $amount  = $record['netAmount']     ?? 0;
                                                            } catch (\Throwable $e) {
                                                                \Log::error('reports/profit_loss_receipt_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp

                                                        <div class="account-inner {{ VC::DFL_AIC_JCB }}">
                                                            @if (!preg_match('/\btotal\b/i', (string) $accName))
                                                                <p class="{{ VC::MB2 }} {{ VC::PS3 }} ms-3">
                                                                    @if(!empty($accId))
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
                                                                                \Log::error('reports/profit_loss_receipt_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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
                                                                <p class="fw-bold {{ VC::MB2 }} ms-3">
                                                                    @if(!empty($accId))
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
                                                                                \Log::error('reports/profit_loss_receipt_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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
                                                            <p class="{{ VC::MB2 }} {{ VC::TXCT }}">{{ $accCode }}</p>
                                                            @if (!preg_match('/\btotal\b/i', (string) $accName))
                                                                <p class="text-primary mb-2 {{ VC::FEND }} text-end {{ VC::ME3 }}">{{ $user?->priceFormat($amount) }}</p>
                                                            @else
                                                                <p class="mb-2 {{ VC::FEND }} text-end {{ VC::ME3 }} fw-bold text-dark">{{ $user?->priceFormat($amount) }}</p>
                                                            @endif
                                                        </div>
                                                        @php
                                                            try {
                                                                if ($accName === 'Total Income')
                                                                    $totalIncome = $amount;
                                                                if ($accName === 'Total Costs of Goods Sold')
                                                                    $totalCosts = $amount > 0 ? $amount : -$amount;
                                                                $grossProfit = $totalIncome - $totalCosts;
                                                            } catch (\Throwable $e) {
                                                                \Log::error('reports/profit_loss_receipt_horizontal — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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
                                        <div class="aacount-title {{ VC::DFL_AIC_JCB }} {{ VC::BD_TOP }} {{ VC::BD_BOT }} {{ VC::PY2 }} px-3">
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
        </div>
    </body>
</html>
