@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth,Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $authUser = $user?->creatorId() ?? null;
    $creatorUser = User::find($authUser);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Trial Balance') }}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Trial Balance') }}</li>
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
        <a href="#" onclick="saveAsPDF()" class="{{ VC::BT_SM_PM }} me-1" data-bs-toggle="tooltip" title="{{ __('Print') }}"
           data-original-title="{{ __('Print') }}"><i class="ti ti-printer"></i></a>
    </div>
    <div class="{{ VC::FEND }} me-2">
        @php
            $trialExportBase               = VW::RPT.'.trial.balance.export';
            $trialExportKebab              = Str::kebab($trialExportBase);
            $trialExportResolved           = Route::has($trialExportBase) ? $trialExportBase : (Route::has($trialExportKebab) ? $trialExportKebab : null);
            $trialExportUrl                = $trialExportResolved ? route($trialExportResolved) : '#';
            $trialExportGuardMsg           = Utility::fetchLinkMessage($lang, VW::RPT, 'trial_balance_export_report_unavailable') ?? 'Trial balance export route is unavailable. Please contact technical support or your domain administrator.';
            $trialExportFormId             = 'report-trial-balance-export-form';
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
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }} justify-content-center">
        <div class="col-sm-8">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="{{ VC::CD }}" id="show_filter" style="display:none;">
                    <div class="card-body">
                        @php
                            $trialBase                    = VW::RPT.'.trial.balance';
                            $trialKebab                   = Str::kebab($trialBase);
                            $trialResolved                = Route::has($trialBase) ? $trialBase : (Route::has($trialKebab) ? $trialKebab : null);
                            $trialUrl                     = $trialResolved ? route($trialResolved) : '#';
                            $trialGuardMsg                = Utility::fetchLinkMessage($lang, VW::RPT, 'trial_balance_report_unavailable') ?? 'Trial balance report route is unavailable. Please contact technical support or your domain administrator.';
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
                                <div class="col-xl-10">
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
                                        <div class="col-auto">
                                            <a href="#"
                                            id="trial-balance-apply"
                                            class="{{ VC::BT_SM_PM }}"
                                            data-target-form="report_trial_balance"
                                            data-guard-msg="{{ $trialGuardMsg }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>
                                            <a href="{{ $trialUrl }}"
                                            class="{{ VC::BT_SM_DG }} trial-balance-reset"
                                            data-url="{{ $trialUrl }}"
                                            data-guard-msg="{{ $trialGuardMsg }}"
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
        <div class="col-md-8">
            <div class="{{ VC::CD }}">
                <div class="card-body">
                    <div class="account-main-title {{ VC::MB5 }}">
                        <h5>{{ __('Trial Balance of') . ' ' . ($creatorUser?->name ?? __('Anonymous')) . ' ' . __('as of') . ' ' . ($filter['startDateRange'] ?? __('Undefined date')) . ' ' . __('to') . ' ' . ($filter['endDateRange'] ?? __('Undefined date')) }}</h5>
                    </div>
                    <div class="aacount-title {{ VC::DFL_AIC_JCB }} border-top border-bottom {{ VC::PY2 }}">
                        <h6 class="{{ VC::MB0 }}">{{ __('Account') }}</h6>
                        <h6 class="{{ VC::MB0 }} text-center">{{ __('Account Code') }}</h6>
                        <h6 class="{{ VC::MB0 }} text-end me-5">{{ __('Debit') }}</h6>
                        <h6 class="{{ VC::MB0 }} text-end">{{ __('Credit') }}</h6>
                    </div>
                    @php
                        $totalDebit  = 0.0;
                        $totalCredit = 0.0;
                    @endphp
                    @foreach ((is_iterable($totalAccounts ?? null) ? $totalAccounts : []) as $type => $accounts)
                        <div class="account-main-inner border-bottom {{ VC::PY2 }}">
                            <p class="fw-bold ps-2 {{ VC::MB2 }}">{{ $type }}</p>
                            @foreach ($accounts as $record)
                                @php
                                    $accId    = data_get($record, 'id');
                                    $accName  = (string) data_get($record, 'name', __('Anonymous'));
                                    $accCode  = (string) data_get($record, 'code', __('Failed to get Code'));
                                    $debit    = (float) data_get($record, 'totalDebit', 0);
                                    $credit   = (float) data_get($record, 'totalCredit', 0);
                                    $totalDebit  += $debit;
                                    $totalCredit += $credit;
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

@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/reports/trials/balance/lang/date.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/reports/trials/balance/date.js') }}"></script>
@endpush
