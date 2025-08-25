@php
    use App\Config\Constants\{
        DatabaseConstants, 
        SettingsConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\{User,Utility};
    use Illuminate\Support\Facades\{Auth,Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $authUser = $user?->creatorId() ?? null;
    $creatorUser = $authUser ? User::find($authUser) : null;
    $settings = Utility::settings();
    $color = !empty($settings[SettingsConstants::THM_CLR]) ? $settings[SettingsConstants::THM_CLR] : 'theme-3';
@endphp
<html lang="{{ $lang ?? str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{ $settings[SettingsConstants::RTL] == 'on' ? 'rtl' : '' }}">
    <head>
        <title>{{ env('APP_NAME') }} - Trial Balance</title>
        @include('fragments.std', [
            'meta_title' => $meta_title,
            'meta_desc' => $meta_desc,
            'meta_vp' => ''
        ])
        @include('fragments.stylesheets', ['settings' => $settings[SettingsConstants::CLR_STG]])
        @if (isset($settings[SettingsConstants::RTL]) && $settings[SettingsConstants::RTL] == 'on')
            <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}" id="main-style-link">
        @endif
    </head>
    @php
        $bodyClass = is_string($color ?? null) ? $color : '';
        $creatorName = data_get($creatorUser ?? null,'name') ?? __('Could not find user name');
        $startDate = data_get($filter ?? [],'startDateRange') ?? __('No start date available');
        $endDate = data_get($filter ?? [],'endDateRange') ?? __('No end date available');
        $accountsSafe = is_iterable($totalAccounts ?? null) ? $totalAccounts : [];
        $fmtNum = function($v) use($creatorUser){ return ($creatorUser && method_exists($creatorUser,'priceFormat')) ? ($creatorUser->priceFormat($v) ?? number_format((float)$v,2)) : number_format((float)$v,2); };
    @endphp
    <body class="{{ $bodyClass }}">
        <div class="{{ VC::RW }} justify-content-center" id="printableArea">
            <div class="col-md-8">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        <div class="account-main-title mb-5">
                            <h5>{{ __('Trial Balance of') . ' ' . $creatorName . ' ' . __('as of') . ' ' . $startDate . ' ' . __('to') . ' ' . $endDate }}</h5>
                        </div>
                        <div class="aacount-title {{ VC::DFL_AIC_JCB }} border-top border-bottom {{ VC::PY2 }}">
                            <h6 class="{{ VC::MB0 }}">{{ __('Account') }}</h6>
                            <h6 class="{{ VC::MB0 }} text-center">{{ __('Account Code') }}</h6>
                            <h6 class="{{ VC::MB0 }} text-end me-5">{{ __('Debit') }}</h6>
                            <h6 class="{{ VC::MB0 }} text-end">{{ __('Credit') }}</h6>
                        </div>
                        @php
                            $totalCredit = 0;
                            $totalDebit = 0;
                        @endphp
                        @foreach ($accountsSafe as $type => $accounts)
                            <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                                <p class="fw-bold ps-2 mb-2">{{ $type }}</p>
                                @foreach ($accounts as $key => $record)
                                    @php
                                        $accId = data_get($record,'id');
                                        $accName = data_get($record,'name') ?? __('No account name available');
                                        $accCode = data_get($record,'code') ?? '-';
                                        $debit = (float)(data_get($record,'totalDebit') ?? 0);
                                        $credit = (float)(data_get($record,'totalCredit') ?? 0);
                                    @endphp
                                    <div class="account-inner {{ VC::DFL_AIC_JCB }}">
                                        @php
                                            $accIdVal           = isset($accId) ? $accId : null;
                                            $ledgerBase         = VW::RPT.'.ledger';
                                            $ledgerKebab        = Str::kebab($ledgerBase);
                                            $ledgerResolved     = Route::has($ledgerBase) ? $ledgerBase : (Route::has($ledgerKebab) ? $ledgerKebab : null);
                                            $ledgerParams       = $accIdVal ? [$accIdVal] : ['#'];
                                            $ledgerRouteUrl     = ($ledgerResolved && $accIdVal) ? route($ledgerResolved, $ledgerParams) : '#';
                                            $ledgerUrl          = ($ledgerRouteUrl !== '#') ? ($ledgerRouteUrl.'?account='.urlencode($accIdVal)) : '#';
                                            $ledgerGuardMsg     = Utility::fetchLinkMessage($lang, VW::RPT, 'ledger_report_unavailable') ?? 'Ledger report route is unavailable. Please contact technical support or your domain administrator.';
                                            $ledgerLinkId       = 'ledger-link-'.($accIdVal ?? 'x');
                                        @endphp
                                        <p class="{{ VC::MB2 }}">
                                            <a href="{{ $ledgerUrl }}"
                                            id="{{ $ledgerLinkId }}"
                                            class="text-primary report-ledger"
                                            data-url="{{ $ledgerUrl }}"
                                            data-guard-msg="{{ $ledgerGuardMsg }}"
                                            data-sv-localized="true">{{ $accName }}</a>
                                        </p>
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
                                                                toast.setAttribute('role','alert');
                                                                toast.setAttribute('aria-live','assertive');
                                                                toast.setAttribute('aria-atomic','true');
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
                                                            a.setAttribute('data-failed-route', 'true');
                                                        } catch (_) {}
                                                    }, { passive: false });
                                                } catch (_) {}
                                            })();
                                        </script>
                                        <p class="mb-2 text-center">{{ $accCode }}</p>
                                        <p class="text-primary mb-2 text-end me-5">{{ $fmtNum($debit) }}</p>
                                        <p class="text-primary mb-2 {{ VC::FEND }} text-end">{{ $fmtNum($credit) }}</p>
                                    </div>
                                    @php
                                        $totalDebit += $debit;
                                        $totalCredit += $credit;
                                    @endphp
                                @endforeach
                            </div>
                        @endforeach
                        @if(!empty($accountsSafe))
                            <div class="aacount-title {{ VC::DFL_AIC_JCB }} border-top border-bottom {{ VC::PY2 }} px-2 pe-0">
                                <h6 class="fw-bold {{ VC::MB0 }}">{{ __('Total') }}</h6>
                                <h6 class="fw-bold {{ VC::MB0 }}">{{ '' }}</h6>
                                <h6 class="fw-bold {{ VC::MB0 }} text-end me-5">{{ $fmtNum($totalDebit) }}</h6>
                                <h6 class="fw-bold {{ VC::MB0 }} text-end">{{ $fmtNum($totalCredit) }}</h6>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <script async src="{{ asset('js/routes/reports/trials/lang/print.js') }}"></script>
        <script defer src="{{ asset('js/routes/reports/trials/print.js') }}"></script>
    </body>
</html>
