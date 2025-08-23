{{-- @extends(ExtendingLayoutsConstants::ADM) --}}
@php
    use App\Config\Constants\{
        DatabaseConstants, 
        SettingsConstants,
        ViewsConstants,
    };
    use App\Models\{User, Utility};
    use Illuminate\Support\Facades\{Auth,Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $authUser = $user?->creatorId() ?? null;
    $creatorUser = $authUser ? User::find($authUser) : null;
    $settings = Utility::settings();
    $color = (!empty($settings[SettingsConstants::THM_CLR])) ? $settings[SettingsConstants::THM_CLR] : 'theme-3';
@endphp
<html lang="{{ $lang ?? str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{$settings[SettingsConstants::RTL] == 'on'?'rtl':''}}">
    <head>
        <title>{{env('APP_NAME')}} - Balance Sheet</title>
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
        <script async src="{{ asset('assets/js/routes/reports/balances/receipts/lang/pdf.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/reports/balances/receipts/pdf.js') }}"></script>
    </head>
    @php
        $bodyClass = is_string($color ?? null) ? $color : '';
        $creatorName = data_get($creatorUser ?? null,'name') ?? __('Could not find user name');
        $startDate = data_get($filter ?? [],'startDateRange') ?? __('No start date available');
        $endDate = data_get($filter ?? [],'endDateRange') ?? __('No end date available');
        $charts = is_iterable($chartAccounts ?? null) ? $chartAccounts : [];
        $fmtNum = function($v){ return is_numeric($v) ? number_format((float)$v,2,'.',',') : ($v ?? '0'); };
    @endphp
    <body class="{{ $bodyClass }}">
        <div class="{{ VC::MT4 }}">
            <div class="{{ VC::RW }} justify-content-center" id="printableArea">
                <div class="col-md-8">
                    <div class="{{ VC::CD }}">
                        <div class="card-body">
                            <div class="account-main-title mb-5">
                                <h5>{{ __('Balance Sheet of') . ' ' . $creatorName . ' ' . __('as of') . ' ' . $startDate . ' ' . __('to') . ' ' . $endDate }}</h5>
                            </div>
                            <div class="aacount-title {{ VC::DFL_AIC_JCB }} border-top border-bottom {{ VC::PY2 }}">
                                <h6 class="{{ VC::MB0 }}">{{ __('Account') }}</h6>
                                <h6 class="{{ VC::MB0 }} text-center">{{ __('Account Code') }}</h6>
                                <h6 class="{{ VC::MB0 }} text-end">{{ __('Total') }}</h6>
                            </div>
                            @php $liabEqTotal = 0; @endphp
                            @foreach ($charts as $type => $accounts)
                                @if (!empty($accounts))
                                    <div class="account-main-inner {{ VC::PY2 }}">
                                        @if ($type === 'Liabilities')
                                            <p class="fw-bold mb-3">{{ __('Liabilities & Equity') }}</p>
                                        @endif
                                        <p class="fw-bold ps-2 mb-2">{{ $type }}</p>
                                        @php $sectionTotal = 0; @endphp
                                        @foreach ($accounts as $account)
                                            @php
                                                $subType = data_get($account,'subType') ?: '';
                                                $accListRaw = data_get($account,'account',[]);
                                                $accList = is_array($accListRaw) ? $accListRaw : (is_iterable($accListRaw) ? iterator_to_array($accListRaw) : []);
                                                $accCopy = $accList;
                                                $last = !empty($accCopy) ? end($accCopy) : null;
                                                $lastName = data_get($last,'account_name') ?? 0;
                                                $lastNet = (float)(data_get($last,'netAmount',0));
                                            @endphp
                                            <div class="border-bottom {{ VC::PY2 }}">
                                                <p class="fw-bold ps-4 mb-2">{{ $subType }}</p>
                                                @foreach ($accList as $k => $record)
                                                    @php $name = (string)(data_get($record,'account_name') ?? ''); $isTotal = preg_match('/\btotal\b/i',$name) === 1; @endphp
                                                    @if ($k < max(count($accList) - 1,0))
                                                        @if (!$isTotal)
                                                            @php
                                                                $accountId       = data_get($record, 'account_id');
                                                                $ledgerBase      = VW::RPT.'.ledger';
                                                                $ledgerKebab     = Str::kebab($ledgerBase);
                                                                $ledgerResolved  = Route::has($ledgerBase) ? $ledgerBase : (Route::has($ledgerKebab) ? $ledgerKebab : null);
                                                                $ledgerParams    = $accountId ? [$accountId] : ['#'];
                                                                $ledgerUrl       = ($ledgerResolved && $accountId) ? route($ledgerResolved, $ledgerParams) : '#';
                                                                $ledgerHref      = ($ledgerUrl !== '#' && $accountId) ? ($ledgerUrl.'?account='.$accountId) : '#';
                                                                $ledgerGuardMsg  = Utility::fetchLinkMessage($lang, VW::RPT, 'view_ledger_unavailable') ?? 'View ledger route is unavailable. Please contact technical support or your domain administrator.';
                                                                $ledgerLinkId    = 'ledger-view-'.($accountId ?? 'x');
                                                            @endphp
                                                            <div class="account-inner {{ VC::DFL_AIC_JCB }} ps-5">
                                                                <p class="mb-2">
                                                                    <a href="{{ $ledgerHref }}" id="{{ $ledgerLinkId }}" class="text-primary ledger-view" data-url="{{ $ledgerHref }}" data-guard-msg="{{ $ledgerGuardMsg }}" data-sv-localized="true">
                                                                        {{ $name ?: __('No account name available') }}
                                                                    </a>
                                                                </p>
                                                                <p class="mb-2 text-center">{{ data_get($record,'account_code') ?? '-' }}</p>
                                                                <p class="text-primary mb-2 {{ VC::FEND }} text-end">{{ $fmtNum(data_get($record,'netAmount',0)) }}</p>
                                                            </div>
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
                                                        @endif
                                                    @endif
                                                @endforeach
                                                <div class="account-inner {{ VC::DFL_AIC_JCB }} ps-4">
                                                    <p class="fw-bold mb-2">{{ $lastName }}</p>
                                                    <p class="fw-bold mb-2 text-end">{{ $fmtNum($lastNet) }}</p>
                                                </div>
                                            </div>
                                            @php $sectionTotal += $lastNet; @endphp
                                        @endforeach
                                        <div class="aacount-title {{ VC::DFL_AIC_JCB }} border-top border-bottom {{ VC::PY2 }} px-2 pe-0">
                                            <h6 class="fw-bold {{ VC::MB0 }}">{{ __('Total for') . ' ' . $type }}</h6>
                                            <h6 class="fw-bold {{ VC::MB0 }} text-end">{{ $fmtNum($sectionTotal) }}</h6>
                                        </div>
                                        @php if ($type !== 'Assets') { $liabEqTotal += $sectionTotal; } @endphp
                                    </div>
                                @endif
                            @endforeach
                            @if($liabEqTotal !== 0)
                                <div class="aacount-title {{ VC::DFL_AIC_JCB }} border-bottom {{ VC::PY2 }} px-0">
                                    <h6 class="fw-bold {{ VC::MB0 }}">{{ __('Total for Liabilities & Equity') }}</h6>
                                    <h6 class="fw-bold {{ VC::MB0 }} text-end">{{ $fmtNum($liabEqTotal) }}</h6>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>

