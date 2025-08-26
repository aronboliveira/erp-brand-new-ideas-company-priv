{{-- @extends(ExtendingLayoutsConstants::ADM) --}}
@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{User, Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
    $authUser = $user?->creatorId() ?? null;
    $creatorUser = User::find($authUser);
    $settings = Utility::settings();
    $color = (!empty($settings[SettingsConstants::THML_CLR])) ? $settings[SettingsConstants::THML_CLR] : 'theme-3';
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
        <div class="mt-4">
        <div class="{{ VC::RW }} justify-content-center" id="printableArea">
            <div class="col-md-8">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        <div class="account-main-title mb-5">
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
                            $totalIncome    = 0;
                            $totalCosts     = 0;
                            $totalExpenses  = 0;
                            $grossProfit    = 0;
                            $netProfit      = 0;
                            $printedIncome  = false;
                            $printedCOGS    = false;
                            $printedExpense = false;
                            $hasAccounts    = !empty($chartAccounts) && is_iterable($chartAccounts);
                        @endphp
                        @if(!$hasAccounts)
                            <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                                <p class="fw-bold mb-2">{{ __('No account data available') }}</p>
                            </div>
                        @endif
                        @if($hasAccounts)
                            @foreach ($chartAccounts as $accounts)
                                @if (($accounts['Type'] ?? null) === 'Income')
                                    @php $printedIncome = true; @endphp
                                    <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                                        <p class="fw-bold mb-2">{{ __('Income') }}</p>
                                        @forelse ($accounts['account'] ?? [] as $record)
                                            @php
                                                $accId   = $record['account_id']  ?? null;
                                                $accName = $record['account_name'] ?? __('No account name available');
                                                $accCode = $record['account_code'] ?? __('No account code available');
                                                $amount  = is_numeric($record['netAmount'] ?? null) ? $record['netAmount'] : 0;
                                            @endphp

                                            <div class="account-inner {{ VC::DFL_AIC_JCB }}">
                                                @if (!preg_match('/\btotal\b/i', $accName) && $accId)
                                                    <p class="mb-2 ps-3">
                                                        @php
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
                                                        @endphp
                                                        <a id="{{ $anchorId }}"
                                                        href="{{ $ledgerHref }}"
                                                        class="text-primary"
                                                        data-url="{{ $ledgerHref }}"
                                                        data-guard-msg="{{ $ledgerGuardMsg }}"
                                                        data-sv-localized="true">
                                                            {{ $accLabel }}
                                                        </a>
                                                        @push(StacksConstants::ADM_SCRP_PG)
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
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            } catch (err) {}
                                                                        });
                                                                    } catch (err) {}
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </p>
                                                @else
                                                    <p class="fw-bold mb-2">
                                                        @if($accId)
                                                            @php
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
                                                            @endphp
                                                            <a id="{{ $anchorId }}"
                                                            href="{{ $ledgerHref }}"
                                                            class="text-dark"
                                                            data-url="{{ $ledgerHref }}"
                                                            data-guard-msg="{{ $ledgerGuardMsg }}"
                                                            data-sv-localized="true">
                                                                {{ $accLabel }}
                                                            </a>
                                                            @push(StacksConstants::ADM_SCRP_PG)
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
                                                                                    el.setAttribute('data-failed-route', 'true');
                                                                                } catch (err) {}
                                                                            });
                                                                        } catch (err) {}
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        @else
                                                            <span class="text-dark">{{ $accName }}</span>
                                                        @endif
                                                    </p>
                                                @endif
                                                <p class="mb-2 text-center">{{ $accCode }}</p>
                                                <p class="text-primary mb-2 {{ VC::FEND }} text-end">
                                                    {{ $user?->priceFormat($amount) }}
                                                </p>
                                            </div>
                                            @php
                                                if (strcasecmp($accName, 'Total Income') === 0)
                                                    $totalIncome = $amount;
                                            @endphp
                                        @empty
                                            <p class="mb-2 ps-3">{{ __('No income accounts available') }}</p>
                                        @endforelse
                                    </div>
                                @endif
                            @endforeach

                            @if(!$printedIncome)
                                <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                                    <p class="fw-bold mb-2">{{ __('Income') }}</p>
                                    <p class="mb-2 ps-3">{{ __('No income accounts available') }}</p>
                                </div>
                            @endif

                            @foreach ($chartAccounts as $accounts)
                                @if (($accounts['Type'] ?? null) === 'Costs of Goods Sold')
                                    @php $printedCOGS = true; @endphp
                                    <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                                        <p class="fw-bold mb-2">{{ __('Costs of Goods Sold') }}</p>

                                        @forelse ($accounts['account'] ?? [] as $record)
                                            @php
                                                $accId   = $record['account_id']  ?? null;
                                                $accName = $record['account_name'] ?? __('No account name available');
                                                $accCode = $record['account_code'] ?? __('No account code available');
                                                $rawAmt  = is_numeric($record['netAmount'] ?? null) ? $record['netAmount'] : 0;
                                                $amount  = $rawAmt >= 0 ? $rawAmt : -$rawAmt;
                                            @endphp

                                            <div class="account-inner {{ VC::DFL_AIC_JCB }}">
                                                @if (!preg_match('/\btotal\b/i', $accName) && $accId)
                                                    <p class="mb-2 ps-3">
                                                        @php
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
                                                        @endphp
                                                        <a id="{{ $anchorId }}"
                                                        href="{{ $ledgerHref }}"
                                                        class="text-primary"
                                                        data-url="{{ $ledgerHref }}"
                                                        data-guard-msg="{{ $ledgerGuardMsg }}"
                                                        data-sv-localized="true">
                                                            {{ $accLabel }}
                                                        </a>
                                                        @push(StacksConstants::ADM_SCRP_PG)
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
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            } catch (err) {}
                                                                        });
                                                                    } catch (err) {}
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </p>
                                                @else
                                                    <p class="fw-bold mb-2">
                                                        @if($accId)
                                                            @php
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
                                                            @endphp
                                                            <a id="{{ $anchorId }}"
                                                            href="{{ $ledgerHref }}"
                                                            class="text-dark"
                                                            data-url="{{ $ledgerHref }}"
                                                            data-guard-msg="{{ $ledgerGuardMsg }}"
                                                            data-sv-localized="true">
                                                                {{ $accLabel }}
                                                            </a>
                                                            @push(StacksConstants::ADM_SCRP_PG)
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
                                                                                    el.setAttribute('data-failed-route', 'true');
                                                                                } catch (err) {}
                                                                            });
                                                                        } catch (err) {}
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        @else
                                                            <span class="text-dark">{{ $accName }}</span>
                                                        @endif
                                                    </p>
                                                @endif
                                                <p class="mb-2 text-center">{{ $accCode }}</p>
                                                <p class="text-primary mb-2 {{ VC::FEND }} text-end">
                                                    {{ $user?->priceFormat($amount) }}
                                                </p>
                                            </div>
                                            @php
                                                if (strcasecmp($accName, 'Total Costs of Goods Sold') === 0)
                                                    $totalCosts = $amount;
                                            @endphp
                                        @empty
                                            <p class="mb-2 ps-3">{{ __('No costs of goods sold accounts available') }}</p>
                                        @endforelse
                                    </div>
                                @endif
                            @endforeach
                            @php $grossProfit = $totalIncome - $totalCosts; @endphp
                            <div class="account-inner {{ VC::DFL_AIC_JCB }} border-bottom">
                                <p></p>
                                <p class="fw-bold mb-2 text-center">{{ __('Gross Profit') }}</p>
                                <p class="text-primary mb-2 {{ VC::FEND }} text-end">
                                    {{ $user?->priceFormat($grossProfit) }}
                                </p>
                            </div>
                            @foreach ($chartAccounts as $accounts)
                                @if (($accounts['Type'] ?? null) === 'Expenses')
                                    @php $printedExpense = true; @endphp
                                    <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                                        <p class="fw-bold mb-2">{{ __('Expenses') }}</p>
                                        @forelse ($accounts['account'] ?? [] as $record)
                                            @php
                                                $accId   = $record['account_id']  ?? null;
                                                $accName = $record['account_name'] ?? __('No account name available');
                                                $accCode = $record['account_code'] ?? __('No account code available');
                                                $rawAmt  = is_numeric($record['netAmount'] ?? null) ? $record['netAmount'] : 0;
                                                $amount  = $rawAmt >= 0 ? $rawAmt : -$rawAmt;
                                            @endphp
                                            <div class="account-inner {{ VC::DFL_AIC_JCB }}">
                                                @if (!preg_match('/\btotal\b/i', $accName) && $accId)
                                                    <p class="mb-2 ps-3">
                                                        @php
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
                                                        @endphp
                                                        <a id="{{ $anchorId }}"
                                                        href="{{ $ledgerHref }}"
                                                        class="text-primary"
                                                        data-url="{{ $ledgerHref }}"
                                                        data-guard-msg="{{ $ledgerGuardMsg }}"
                                                        data-sv-localized="true">
                                                            {{ $accLabel }}
                                                        </a>
                                                        @push(StacksConstants::ADM_SCRP_PG)
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
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            } catch (err) {}
                                                                        });
                                                                    } catch (err) {}
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </p>
                                                @else
                                                    <p class="fw-bold mb-2">
                                                        @if($accId)
                                                            @php
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
                                                            @endphp
                                                            <a id="{{ $anchorId }}"
                                                            href="{{ $ledgerHref }}"
                                                            class="text-dark"
                                                            data-url="{{ $ledgerHref }}"
                                                            data-guard-msg="{{ $ledgerGuardMsg }}"
                                                            data-sv-localized="true">
                                                                {{ $accLabel }}
                                                            </a>
                                                            @push(StacksConstants::ADM_SCRP_PG)
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
                                                                                    el.setAttribute('data-failed-route', 'true');
                                                                                } catch (err) {}
                                                                            });
                                                                        } catch (err) {}
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        @else
                                                            <span class="text-dark">{{ $accName }}</span>
                                                        @endif
                                                    </p>
                                                @endif
                                                <p class="mb-2 text-center">{{ $accCode }}</p>
                                                <p class="text-primary mb-2 {{ VC::FEND }} text-end">
                                                    {{ $user?->priceFormat($amount) }}
                                                </p>
                                            </div>
                                            @php
                                                if (strcasecmp($accName, 'Total Expenses') === 0)
                                                    $totalExpenses = $amount;
                                            @endphp
                                        @empty
                                            <p class="mb-2 ps-3">{{ __('No expenses accounts available') }}</p>
                                        @endforelse
                                    </div>
                                @endif
                            @endforeach
                            @php $netProfit = $grossProfit - $totalExpenses; @endphp
                            <div class="account-inner {{ VC::DFL_AIC_JCB }} border-bottom">
                                <p></p>
                                <p class="fw-bold mb-2 text-center">{{ __('Net Profit/Loss') }}</p>
                                <p class="text-primary mb-2 {{ VC::FEND }} text-end">
                                    {{ $user?->priceFormat($netProfit) }}
                                </p>
                            </div>
                            @if(!$printedCOGS && $printedIncome)
                                <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                                    <p class="mb-2 ps-3">{{ __('No costs of goods sold accounts available') }}</p>
                                </div>
                            @endif
                            @if(!$printedExpense)
                                <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                                    <p class="mb-2 ps-3">{{ __('No expenses accounts available') }}</p>
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

