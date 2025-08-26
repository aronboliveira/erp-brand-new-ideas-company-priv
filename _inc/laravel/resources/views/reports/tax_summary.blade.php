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
    {{__('Tax Summary')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Tax Summary')}}</li>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/reports/taxes/summaries/lang/pdf.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/reports/taxes/summaries/pdf.js') }}"></script>
@endpush

{{--        <a class="{{ VC::BT_SM_PM }}" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
{{--            <i class="ti ti-filter"></i>--}}
{{--        </a>--}}
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @php
            $downloadLabelTax = __('Download');
            $downloadGuardMsgTax = Utility::fetchLinkMessage($lang, VW::RPT, 'download_tax_reports_unavailable') ?? 'Download function for Tax Reports is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a href="#"
        class="{{ VC::BT_SM_PM }} download-tax-reports"
        data-func-name="saveAsPDF"
        data-guard-msg="{{ $downloadGuardMsgTax }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ $downloadLabelTax }}"
        aria-label="{{ $downloadLabelTax }}"
        data-original-title="{{ $downloadLabelTax }}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </a>
        @push(StacksConstants::ADM_SCRP_PG)
            <script src="{{ asset('assets/js/routes/reports/taxes/download.js') }}" defer></script>
        @endpush
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        @php
                            $taxSummaryBase = ViewsConstants::RPT.'.tax.summary';
                            $taxSummaryKebab = Str::kebab($taxSummaryBase);
                            $taxSummaryResolved = Route::has($taxSummaryBase) ? $taxSummaryBase : (Route::has($taxSummaryKebab) ? $taxSummaryKebab : null);
                            $actionRoute = $taxSummaryResolved ? [$taxSummaryResolved] : ['#'];
                            $actionUrl = $taxSummaryResolved ? route($taxSummaryResolved) : '#';
                            $resetUrl = $actionUrl;
                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                            $applyGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::RPT, 'apply_tax_summary_route_unavailable') ?? 'Apply tax summary route is unavailable. Please contact technical support or your domain administrator.';
                            $resetGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::RPT, 'reset_tax_summary_route_unavailable') ?? 'Reset tax summary route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        {{ Form::open(['route' => $actionRoute, 'method' => 'GET', 'id' => 'report_tax_summary', 'data-url' => $actionUrl, 'data-guard-msg' => $applyGuardMsg, 'data-sv-localized' => 'true']) }}
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
                                            <a id="apply-tax-summary"
                                            href="#"
                                            class="{{ VC::BT_SM_PM }}"
                                            data-form-id="report_tax_summary"
                                            data-guard-msg="{{ $applyGuardMsg }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}"
                                            data-original-title="{{ __('apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>
                                            <a id="reset-tax-summary"
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
                        @push(StacksConstants::ADM_SCRP_PG)
                            <script src="{{ asset('assets/js/routes/reports/taxes/summaries/apply.js') }}" defer></script>
                            <script src="{{ asset('assets/js/routes/reports/taxes/summaries/reset.js') }}" defer></script>
                        @endpush
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="printableArea">
        <div class="{{ VC::RW }} {{ VC::MT3 }}">
            <div class="col">
                <input type="hidden"
                       id="filename"
                       value="{{ __('Tax Summary') . ' ' . __('Report of') . ' ' . $filter['startDateRange'] . ' ' . __('to') . ' ' . $filter['endDateRange'] }}">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Report') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ __('Tax Summary') }}</h6>
                </div>
            </div>
            <div class="col">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Duration') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ $filter['startDateRange'] . ' ' . __('to') . ' ' . $filter['endDateRange'] }}</h6>
                </div>
            </div>
        </div>
        @php
            $colCount      = count($monthList);
            $incomeTotals  = array_fill(0, $colCount, 0.0);
            $expenseTotals = array_fill(0, $colCount, 0.0);
            foreach ($incomes as $prices)
                foreach (array_values($prices) as $i => $price)
                    if ($i < $colCount) $incomeTotals[$i] += (float) $price;
            foreach ($expenses as $prices)
                foreach (array_values($prices) as $i => $price)
                    if ($i < $colCount) $expenseTotals[$i] += (float) $price;
            $netTotals = [];
            for ($i = 0; $i < $colCount; $i++)
                $netTotals[$i] = $incomeTotals[$i] - $expenseTotals[$i];
            $noIncome  = empty($incomes);
            $noExpense = empty($expenses);
        @endphp
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body table-border-style">
                        <div class="{{ VC::CS12 }}">
                            <h5>{{ __('Income') }}</h5>
                            <div class="table-responsive {{ VC::MT3 }} {{ VC::MB3 }}">
                                <table class="{{ VC::TB }}">
                                    <thead>
                                    <tr>
                                        <th>{{ __('Tax') }}</th>
                                        @foreach($monthList as $month)
                                            <th class="text-end">{{ $month }}</th>
                                        @endforeach
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($incomes as $taxName => $prices)
                                        <tr>
                                            <td>{{ $taxName }}</td>
                                            @foreach($prices as $price)
                                                <td class="text-end">{{ $user?->priceFormat($price) ?? __('Failed to retrieve user data.') }}</td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 1 + $colCount }}" class="text-center">{{ __('Income tax not found') }}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                    @unless($noIncome)
                                        <tfoot>
                                            <tr>
                                                <th class="text-end">{{ __('Total Income Tax') }}</th>
                                                @foreach($incomeTotals as $sum)
                                                    <th class="text-end">{{ $user?->priceFormat($sum) ?? __('Failed to retrieve user data.') }}</th>
                                                @endforeach
                                            </tr>
                                        </tfoot>
                                    @endunless
                                </table>
                            </div>
                        </div>
                        <div class="{{ VC::CS12 }}">
                            <h5>{{ __('Expense') }}</h5>
                            <div class="table-responsive {{ VC::MT4 }}">
                                <table class="{{ VC::TB }}">
                                    <thead>
                                    <tr>
                                        <th>{{ __('Tax') }}</th>
                                        @foreach($monthList as $month)
                                            <th class="text-end">{{ $month }}</th>
                                        @endforeach
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($expenses as $taxName => $prices)
                                        <tr>
                                            <td>{{ $taxName }}</td>
                                            @foreach($prices as $price)
                                                <td class="text-end">{{ $user?->priceFormat($price) ?? __('Failed to retrieve user data.') }}</td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 1 + $colCount }}" class="text-center">{{ __('Expense tax not found') }}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                    @unless($noExpense)
                                        <tfoot>
                                            <tr>
                                                <th class="text-end">{{ __('Total Expense Tax') }}</th>
                                                @foreach($expenseTotals as $sum)
                                                    <th class="text-end">{{ $user?->priceFormat($sum) ?? __('Failed to retrieve user data.') }}</th>
                                                @endforeach
                                            </tr>
                                        </tfoot>
                                    @endunless
                                </table>
                            </div>
                        </div>
                        @if(!$noIncome || !$noExpense)
                            <div class="{{ VC::CS12 }}">
                                <h5>{{ __('Net Tax (Income − Expense)') }}</h5>
                                <div class="table-responsive {{ VC::MT3 }}">
                                    <table class="{{ VC::TB }}">
                                        <thead>
                                        <tr>
                                            <th>{{ __('Metric') }}</th>
                                            @foreach($monthList as $month)
                                                <th class="text-end">{{ $month }}</th>
                                            @endforeach
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{{ __('Net') }}</td>
                                                @foreach($netTotals as $sum)
                                                    <td class="text-end">{{ $user?->priceFormat($sum) ?? __('Failed to retrieve user data.') }}</td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


