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
    <script async src="{{ asset('assets/js/routes/reports/profits/index/loss/summaries/lang/pdf.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/reports/profits/index/loss/summaries/pdf.js') }}"></script>
@endpush

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @php
            $downloadLabelPl = __('Download');
            $downloadGuardMsgPl = Utility::fetchLinkMessage($lang, VW::RPT, 'download_profit_loss_report_unavailable') ?? 'Download function for Profit & Loss report is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a href="#"
           class="{{ VC::BT_SM_PM }} download-profit-loss"
           data-func-name="saveAsPDF"
           data-guard-msg="{{ $downloadGuardMsgPl }}"
           data-sv-localized="true"
           data-bs-toggle="tooltip"
           title="{{ $downloadLabelPl }}"
           aria-label="{{ $downloadLabelPl }}"
           data-original-title="{{ $downloadLabelPl }}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/profits/index/loss/summaries/download.js') }}" defer></script>
        @endpush
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <ul class="{{ VC::NAV_PL_Y3 }}" id="pills-tab" role="tablist">
        <li class="{{ VC::NV_IT }}">
            <a class="{{ VC::NV_LK }} active"
               id="pills-home-tab"
               data-bs-toggle="pill"
               href="#daily-chart"
               role="tab"
               aria-controls="pills-home"
               aria-selected="true">{{ __('Monthly') }}</a>
        </li>
        <li class="{{ VC::NV_IT }}">
            @php
                $monthlyPurchaseBase = VW::RPT.'.monthly.purchase';
                $monthlyPurchaseKebab = Str::kebab($monthlyPurchaseBase);
                $monthlyPurchaseResolved = Route::has($monthlyPurchaseBase) ? $monthlyPurchaseBase : (Route::has($monthlyPurchaseKebab) ? $monthlyPurchaseKebab : null);
                $monthlyPurchaseUrl = $monthlyPurchaseResolved ? route($monthlyPurchaseResolved) : '#';
                $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                $guardMsg = Utility::fetchLinkMessage($langValue, VW::RPT, 'open_monthly_purchase_route_unavailable') ?? 'Monthly purchase route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a class="{{ VC::NV_LK }}"
            id="pills-profile-tab"
            data-bs-toggle="pill"
            href="{{ $monthlyPurchaseUrl }}"
            role="tab"
            aria-controls="pills-profile"
            aria-selected="false"
            data-url="{{ $monthlyPurchaseUrl }}"
            data-guard-msg="{{ $guardMsg }}"
            data-sv-localized="true">{{ __('Quarterly') }}</a>
            @push(StacksConstants::ADM_SCR_PG)
                <script src="{{ asset('assets/js/routes/reports/purchases/monthly/open.js') }}" defer></script>
            @endpush
        </li>
    </ul>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        @php
                            $plsBase = VW::RPT.'.profit.loss.summary';
                            $plsKebab = Str::kebab($plsBase);
                            $plsResolved = Route::has($plsBase) ? $plsBase : (Route::has($plsKebab) ? $plsKebab : null);
                            $actionRoute = $plsResolved ? [$plsResolved] : ['#'];
                            $actionUrl = $plsResolved ? route($plsResolved) : '#';
                            $resetUrl = $actionUrl;
                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                            $applyGuardMsg = Utility::fetchLinkMessage($langValue, VW::RPT, 'apply_profit_loss_summary_route_unavailable') ?? 'Apply profit & loss summary route is unavailable. Please contact technical support or your domain administrator.';
                            $resetGuardMsg = Utility::fetchLinkMessage($langValue, VW::RPT, 'reset_profit_loss_summary_route_unavailable') ?? 'Reset profit & loss summary route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        {{ Form::open([
                            'route'             => $actionRoute,
                            'method'            => 'GET',
                            'id'                => 'report_profit_loss_summary',
                            'data-url'          => $actionUrl,
                            'data-guard-msg'    => $applyGuardMsg,
                            'data-sv-localized' => 'true',
                        ]) }}
                            <div class="{{ VC::R_ALC_JCE }}">
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

                                <div class="{{ VC::C_AT }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                            <a id="apply-profit-loss-summary"
                                            href="#"
                                            class="{{ VC::BT_SM_PM }}"
                                            data-form-id="report_profit_loss_summary"
                                            data-guard-msg="{{ $applyGuardMsg }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}"
                                            data-original-title="{{ __('apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>

                                            <a id="reset-profit-loss-summary"
                                            href="{{ $resetUrl }}"
                                            class="{{ VC::BT_SM_DG }}"
                                            data-url="{{ $resetUrl }}"
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
                            <script src="{{ asset('assets/js/routes/reports/profits/index/loss/summaries/apply.js') }}" defer></script>
                            <script src="{{ asset('assets/js/routes/reports/profits/index/loss/summaries/reset.js') }}" defer></script>
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
        <div class="{{ VC::RW }} {{ VC::MT3 }}">
            <div class="col">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Report') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ __('Profit & Loss Summary') }}</h6>
                    <input type="hidden" id="filename"
                           value="{{ __('Profit & Loss Summary') . ' ' . __('Report of') . ' ' . $startLabel . ' ' . __('to') . ' ' . $endLabel }}">
                </div>
            </div>
            <div class="col">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Duration') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ $startLabel . ' ' . __('to') . ' ' . $endLabel }}</h6>
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

                                <div class="table-responsive mt-3 mb-3">
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
                                            <td colspan="{{ $colspan }}" class="text-dark"><span>{{ __('Revenue : ') }}</span></td>
                                        </tr>
                                        @forelse($revenueIncomeArray ?? [] as $revenue)
                                            <tr>
                                                <td>{{ $revenue['category'] ?? __('No revenue category available') }}</td>
                                                @php $amounts = $revenue['amount'] ?? []; @endphp
                                                @forelse($amounts as $amount)
                                                    <td width="15%">{{ $user?->priceFormat($amount) }}</td>
                                                @empty
                                                    <td colspan="{{ max(1,count($months)) }}">{{ __('No revenue amounts available') }}</td>
                                                @endforelse
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $colspan }}">{{ __('No revenue data available') }}</td>
                                            </tr>
                                        @endforelse
                                        <tr>
                                            <td colspan="{{ $colspan }}" class="text-dark"><span>{{ __('Invoice : ') }}</span></td>
                                        </tr>
                                        @forelse($invoiceIncomeArray ?? [] as $invoice)
                                            <tr>
                                                <td>{{ $invoice['category'] ?? __('No invoice category available') }}</td>
                                                @php $amounts = $invoice['amount'] ?? []; @endphp
                                                @forelse($amounts as $amount)
                                                    <td width="15%">{{ $user?->priceFormat($amount) }}</td>
                                                @empty
                                                    <td colspan="{{ max(1,count($months)) }}">{{ __('No invoice amounts available') }}</td>
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
                                <div class="table-responsive mt-1 mb-4">
                                    <table class="table table-flush border">
                                        <tbody>
                                        <tr>
                                            <td colspan="{{ $colspan }}" class="text-dark">
                                                <span>{{ __('Total Income =  Revenue + Invoice ') }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td width="25%" class="text-dark">{{ __('Total Income') }}</td>
                                            @forelse(($totalIncome ?? []) as $income)
                                                <td width="15%">{{ $user?->priceFormat($income) }}</td>
                                            @empty
                                                <td colspan="{{ max(1,count($months)) }}">{{ __('No total income available') }}</td>
                                            @endforelse
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="{{ VC::CS12 }}">
                                    <h5>{{ __('Expense') }}</h5>
                                    <div class="table-responsive mt-4">
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
                                                <td colspan="{{ $colspan }}" class="text-dark"><span>{{ __('Payment : ') }}</span></td>
                                            </tr>
                                            @forelse($expenseArray ?? [] as $expense)
                                                <tr>
                                                    <td>{{ $expense['category'] ?? __('No payment category available') }}</td>
                                                    @php $amounts = $expense['amount'] ?? []; @endphp
                                                    @forelse($amounts as $amount)
                                                        <td width="15%">{{ $user?->priceFormat($amount) }}</td>
                                                    @empty
                                                        <td colspan="{{ max(1,count($months)) }}">{{ __('No payment amounts available') }}</td>
                                                    @endforelse
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ $colspan }}">{{ __('No payment data available') }}</td>
                                                </tr>
                                            @endforelse
                                            <tr>
                                                <td colspan="{{ $colspan }}" class="text-dark"><span>{{ __('Bill : ') }}</span></td>
                                            </tr>
                                            @forelse($billExpenseArray ?? [] as $bill)
                                                <tr>
                                                    <td>{{ $bill['category'] ?? __('No bill category available') }}</td>
                                                    @php $amounts = $bill['amount'] ?? []; @endphp
                                                    @forelse($amounts as $amount)
                                                        <td width="15%">{{ $user?->priceFormat($amount) }}</td>
                                                    @empty
                                                        <td colspan="{{ max(1,count($months)) }}">{{ __('No bill amounts available') }}</td>
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
                                    <div class="table-responsive mt-3">
                                        <table class="table table-flush border">
                                            <tbody>
                                            <tr>
                                                <td colspan="{{ $colspan }}" class="text-dark">
                                                    <span>{{ __('Total Expense =  Payment + Bill ') }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-dark">{{ __('Total Expenses') }}</td>
                                                @forelse(($totalExpense ?? []) as $expense)
                                                    <td width="15%">{{ $user?->priceFormat($expense) }}</td>
                                                @empty
                                                    <td colspan="{{ max(1,count($months)) }}">{{ __('No total expenses available') }}</td>
                                                @endforelse
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="table-responsive mt-3">
                                        <table class="table table-flush border">
                                            <tbody>
                                            <tr>
                                                <td colspan="{{ $colspan }}" class="text-dark">
                                                    <span>{{ __('Net Profit = Total Income - Total Expense ') }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="25%" class="text-dark">{{ __('Net Profit') }}</td>
                                                @forelse(($netProfitArray ?? []) as $profit)
                                                    <td width="15%">{{ $user?->priceFormat($profit) }}</td>
                                                @empty
                                                    <td colspan="{{ max(1,count($months)) }}">{{ __('No net profit available') }}</td>
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
@endsection


