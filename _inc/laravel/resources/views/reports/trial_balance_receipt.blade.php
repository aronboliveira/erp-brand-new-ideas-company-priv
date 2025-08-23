@php
    use App\Config\Constants\{
        DatabaseConstants, 
        SettingsConstants,
        ViewsConstants,
    };
    use App\Models\{User,Utility};
    use Illuminate\Support\Facades\{Auth,Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $authUser = $user?->creatorId() ?? null;
    $creatorUser = $authUser ? User::find($authUser) : null;
    $settings = Utility::settings();
    $color = !empty($settings[SettingsConstants::THM_CLR]) ? $settings[SettingsConstants::THM_CLR] : 'theme-3';
@endphp
<html lang="{{ str_replace('_', '-', is_string(app()->getLocale()) ? app()->getLocale() : DatabaseConstants::DEFAULT_LANG) }}" dir="{{ $settings[SettingsConstants::RTL] == 'on' ? 'rtl' : '' }}">
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
                                        <p class="mb-2"><a href="{{ route(VW::RPT.'.ledger', $accId) }}?account={{ $accId }}" class="text-primary">{{ $accName }}</a></p>
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
