@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Cash Flow')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Cash Flow')}}</li>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/reports/cashflow/quarterly/lang/pdf.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/reports/cashflow/quarterly/pdf.js') }}"></script>
@endpush

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @php
            $downloadLabelQc = __('Download');
            $downloadGuardMsgQc = Utility::fetchLinkMessage($lang, VW::RPT, 'download_quarterly_cashflow_report_unavailable') ?? 'Download function for Quarterly Cashflow report is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a href="#"
           class="{{ VC::BT_SM_PM }} download-quarterly-cashflow"
           data-func-name="saveAsPDF"
           data-guard-msg="{{ $downloadGuardMsgQc }}"
           data-sv-localized="true"
           data-bs-toggle="tooltip"
           title="{{ $downloadLabelQc }}"
           aria-label="{{ $downloadLabelQc }}"
           data-original-title="{{ $downloadLabelQc }}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/cashflow/quarterly/download.js') }}" defer></script>
        @endpush
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <ul class="{{ VC::NAV_PL_Y3 }}" id="pills-tab" role="tablist">
        <li class="{{ VC::NV_IT }}">
            @php
                $monthlyCashflowBase = VW::RPT.'.monthly.cashflow';
                $monthlyCashflowKebab = Str::kebab($monthlyCashflowBase);
                $monthlyCashflowResolved = Route::has($monthlyCashflowBase) ? $monthlyCashflowBase : (Route::has($monthlyCashflowKebab) ? $monthlyCashflowKebab : null);
                $monthlyCashflowUrl = $monthlyCashflowResolved ? route($monthlyCashflowResolved) : '#';
                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                $mcGuardMsg = Utility::fetchLinkMessage($langValue, VW::RPT, 'open_monthly_cashflow_route_unavailable') ?? 'Monthly cashflow route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a class="{{ VC::NV_LK }}"
            id="pills-home-tab"
            data-bs-toggle="pill"
            href="{{ $monthlyCashflowUrl }}"
            role="tab"
            aria-controls="pills-home"
            aria-selected="true"
            data-url="{{ $monthlyCashflowUrl }}"
            data-guard-msg="{{ $mcGuardMsg }}"
            data-sv-localized="true">{{ __('Monthly') }}</a>

            @push(StacksConstants::ADM_SCR_PG)
                <script src="{{ asset('assets/js/routes/reports/cashflow/monthly/open.js') }}" defer></script>
            @endpush
        </li>
        <li class="{{ VC::NV_IT }}">
            <a class="{{ VC::NV_LK }} active"
               id="pills-profile-tab"
               data-bs-toggle="pill"
               href="#"
               role="tab"
               aria-controls="pills-profile"
               aria-selected="false">{{ __('Quarterly') }}</a>
        </li>
    </ul>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        @php
                            $qcBase      = VW::RPT.'.quarterly.cashflow';
                            $qcKebab     = Str::kebab($qcBase);
                            $qcResolved  = Route::has($qcBase) ? $qcBase : (Route::has($qcKebab) ? $qcKebab : null);
                            $actionRoute = $qcResolved ? [$qcResolved] : ['#'];
                            $actionUrl   = $qcResolved ? route($qcResolved) : '#';
                            $langValue      = isset($lang) ? $lang : Utility::fetchUserLang();
                            $applyGuardMsg  = Utility::fetchLinkMessage($langValue, VW::RPT, 'apply_quarterly_cashflow_route_unavailable') ?? 'Apply quarterly cashflow route is unavailable. Please contact technical support or your domain administrator.';
                            $resetGuardMsg  = Utility::fetchLinkMessage($langValue, VW::RPT, 'reset_quarterly_cashflow_route_unavailable') ?? 'Reset quarterly cashflow route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        {{ Form::open([
                            'route'             => $actionRoute,
                            'method'            => 'GET',
                            'id'                => 'quarterly_cashflow',
                            'data-url'          => $actionUrl,
                            'data-guard-msg'    => $applyGuardMsg,
                            'data-sv-localized' => 'true',
                        ]) }}
                            <div class="{{ VC::R_FLX_ALC_JCE }}">
                                <div class="col-xl-10">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::CL_XL3 }}"><div class="btn-box"></div></div>
                                        <div class="{{ VC::CL_XL3 }}"><div class="btn-box"></div></div>
                                        <div class="{{ VC::CL_XL3 }}"><div class="btn-box"></div></div>
                                        <div class="{{ VC::CL_XL3 }}">
                                            <div class="btn-box">
                                                {{ Form::label('year', __('Year'), ['class' => VC::FM_LB]) }}
                                                {{ Form::select('year', $yearList ?? [], request('year',''), ['class' => VC::FM_CT_SL]) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::C_AT }}">
                                            <a id="apply-quarterly-cashflow"
                                            href="#"
                                            class="{{ VC::BT_SM_PM }}"
                                            data-form-id="quarterly_cashflow"
                                            data-guard-msg="{{ $applyGuardMsg }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}"
                                            data-original-title="{{ __('apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>

                                            <a id="reset-quarterly-cashflow"
                                            href="{{ $actionUrl }}"
                                            class="{{ VC::BT_SM_DG }}"
                                            data-url="{{ $actionUrl }}"
                                            data-guard-msg="{{ $resetGuardMsg }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Reset') }}"
                                            data-original-title="{{ __('Reset') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {{ Form::close() }}
                        @push(StacksConstants::ADM_SCR_PG)
                            <script src="{{ asset('assets/js/routes/reports/cashflow/quarterly/apply.js') }}" defer></script>
                            <script src="{{ asset('assets/js/routes/reports/cashflow/quarterly/reset.js') }}" defer></script>
                        @endpush
                    </div>
                </div>
            </div>
        </div>
    </div>
    @php
        $startLabel = $filter['startDateRange'] ?? __('No start date available');
        $endLabel   = $filter['endDateRange']   ?? __('No end date available');
        $months     = $month ?? [];
        $colspan    = max(2, count($months) + 1);
    @endphp
    <div id="printableArea">
        <div class="{{ VC::RW }} {{ VC::MT1 }}">
            <div class="col">
                <input type="hidden" value="{{ __('Quarterly Cashflow').' '.__('Report of').' '.$startLabel.' '.__('to').' '.$endLabel }}" id="filename">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Report') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ __('Quarterly Cashflow') }}</h6>
                </div>
            </div>
            <div class="col">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Duration') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ $startLabel.' '.__('to').' '.$endLabel }}</h6>
                </div>
            </div>
        </div>

        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body table-border-style">
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::CS12 }}">
                                <h5 class="pb-3">{{ __('Income') }}</h5>
                                <div class="table-responsive {{ VC::MT3 }} {{ VC::MB3 }}">
                                    <table class="{{ VC::TB }}">
                                        <thead>
                                            <tr>
                                                <th width="25%">{{ __('Category') }}</th>
                                                @forelse($months as $m)
                                                    <th width="15%">{{ $m }}</th>
                                                @empty
                                                    <th>{{ __('No months available') }}</th>
                                                @endforelse
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="{{ $colspan }}" class="font-bold">
                                                    <span>{{ __('Revenue : ') }}</span>
                                                </td>
                                            </tr>

                                            @forelse(($revenueIncomeArray ?? []) as $revenue)
                                                <tr>
                                                    <td>{{ $revenue['category'] ?? __('No revenue category available') }}</td>
                                                    @php $amounts = $revenue['amount'] ?? []; @endphp
                                                    @forelse($amounts as $amount)
                                                        <td width="15%">{{ $user?->priceFormat($amount) }}</td>
                                                    @empty
                                                        <td colspan="{{ max(1, count($months)) }}">{{ __('No revenue amounts available') }}</td>
                                                    @endforelse
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ $colspan }}">{{ __('No revenue data available') }}</td>
                                                </tr>
                                            @endforelse

                                            <tr>
                                                <td colspan="{{ $colspan }}" class="font-bold">
                                                    <span>{{ __('Invoice : ') }}</span>
                                                </td>
                                            </tr>

                                            @forelse(($invoiceIncomeArray ?? []) as $invoice)
                                                <tr>
                                                    <td>{{ $invoice['category'] ?? __('No invoice category available') }}</td>
                                                    @php $amounts = $invoice['amount'] ?? []; @endphp
                                                    @forelse($amounts as $amount)
                                                        <td width="15%">{{ $user?->priceFormat($amount) }}</td>
                                                    @empty
                                                        <td colspan="{{ max(1, count($months)) }}">{{ __('No invoice amounts available') }}</td>
                                                    @endforelse
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ $colspan }}">{{ __('No invoice data available') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::CS12 }}">
                                        <table class="table table-flush {{ VC::BD }}">
                                            <tbody>
                                                <tr>
                                                    <td colspan="{{ $colspan }}" class="font-bold">
                                                        <span>{{ __('Total Income =  Revenue + Invoice ') }}</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="25%" class="text-dark">{{ __('Total Income') }}</td>
                                                    @forelse(($totalIncome ?? []) as $income)
                                                        <td width="15%">{{ $user?->priceFormat($income) }}</td>
                                                    @empty
                                                        <td colspan="{{ max(1, count($months)) }}">{{ __('No total income available') }}</td>
                                                    @endforelse
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="{{ VC::CS12 }}">
                                    <h5>{{ __('Expense') }}</h5>
                                    <div class="table-responsive {{ VC::MT4 }}">
                                        <table class="{{ VC::TB }} mb-0">
                                            <thead>
                                                <tr>
                                                    <th width="25%">{{ __('Category') }}</th>
                                                    @forelse($months as $m)
                                                        <th width="15%">{{ $m }}</th>
                                                    @empty
                                                        <th>{{ __('No months available') }}</th>
                                                    @endforelse
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="{{ $colspan }}" class="font-bold">
                                                        <span>{{ __('Payment : ') }}</span>
                                                    </td>
                                                </tr>

                                                @forelse(($expenseArray ?? []) as $expense)
                                                    <tr>
                                                        <td>{{ $expense['category'] ?? __('No payment category available') }}</td>
                                                        @php $amounts = $expense['amount'] ?? []; @endphp
                                                        @forelse($amounts as $amount)
                                                            <td width="15%">{{ $user?->priceFormat($amount) }}</td>
                                                        @empty
                                                            <td colspan="{{ max(1, count($months)) }}">{{ __('No payment amounts available') }}</td>
                                                        @endforelse
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="{{ $colspan }}">{{ __('No payment data available') }}</td>
                                                    </tr>
                                                @endforelse

                                                <tr>
                                                    <td colspan="{{ $colspan }}" class="font-bold">
                                                        <span>{{ __('Bill : ') }}</span>
                                                    </td>
                                                </tr>

                                                @forelse(($billExpenseArray ?? []) as $bill)
                                                    <tr>
                                                        <td>{{ $bill['category'] ?? __('No bill category available') }}</td>
                                                        @php $amounts = $bill['amount'] ?? []; @endphp
                                                        @forelse($amounts as $amount)
                                                            <td width="15%">{{ $user?->priceFormat($amount) }}</td>
                                                        @empty
                                                            <td colspan="{{ max(1, count($months)) }}">{{ __('No bill amounts available') }}</td>
                                                        @endforelse
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="{{ $colspan }}">{{ __('No bill data available') }}</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::CS12 }}">
                                            <table class="table table-flush {{ VC::BD }}">
                                                <tbody>
                                                    <tr>
                                                        <td colspan="{{ $colspan }}" class="font-bold">
                                                            <span>{{ __('Total Expense =  Payment + Bill ') }}</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-dark">{{ __('Total Expenses') }}</td>
                                                        @forelse(($totalExpense ?? []) as $expense)
                                                            <td width="15%">{{ $user?->priceFormat($expense) }}</td>
                                                        @empty
                                                            <td colspan="{{ max(1, count($months)) }}">{{ __('No total expenses available') }}</td>
                                                        @endforelse
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::CS12 }}">
                                            <table class="table table-flush {{ VC::BD }}">
                                                <thead>
                                                    <tr>
                                                        <th colspan="{{ $colspan }}" class="font-bold">
                                                            <span>{{ __('Net Profit = Total Income - Total Expense ') }}</span>
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td width="25%" class="text-dark">{{ __('Net Profit') }}</td>
                                                        @forelse(($netProfitArray ?? []) as $profit)
                                                            <td width="15%">{{ $user?->priceFormat($profit) }}</td>
                                                        @empty
                                                            <td colspan="{{ max(1, count($months)) }}">{{ __('No net profit available') }}</td>
                                                        @endforelse
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

