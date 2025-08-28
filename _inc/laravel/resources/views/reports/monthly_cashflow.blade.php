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
    $lang = Utility::fetchUserLang(auth: $user);
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
    <script async src="{{ asset('assets/js/routes/reports/cashflow/monthly/lang/pdf.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/reports/cashflow/monthly/pdf.js') }}"></script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @php
            $downloadGuardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'download_monthly_cashflow_unavailable') ?? 'Download function for monthly cashflow reports is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a href="#"
        id="download-monthly-cashflow-link"
        class="{{ VC::BT_SM_PM }} download-monthly-cashflow"
        data-func-name="saveAsPDF"
        data-guard-msg="{{ $downloadGuardMsg }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ __('Download') }}"
        data-original-title="{{ __('Download') }}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/cashflow/monthly/download.js') }}" defer></script>
        @endpush
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <ul class="{{ VC::NAV_PL_Y3 }}" id="pills-tab" role="tablist">
        <li class="{{ VC::NV_IT }}">
            <a class="{{ VC::NV_LK }} active" id="pills-home-tab" data-bs-toggle="pill" href="#daily-chart" role="tab" aria-controls="pills-home" aria-selected="true">{{ __('Monthly') }}</a>
        </li>
        <li class="{{ VC::NV_IT }}">
            @php
                $quarterlyCashflowBase      = VW::RPT.'.quarterly.cashflow';
                $quarterlyCashflowKebab     = Str::kebab($quarterlyCashflowBase);
                $quarterlyCashflowResolved  = Route::has($quarterlyCashflowBase)
                    ? $quarterlyCashflowBase
                    : (Route::has($quarterlyCashflowKebab) ? $quarterlyCashflowKebab : null);
                $quarterlyCashflowUrl       = $quarterlyCashflowResolved ? route($quarterlyCashflowResolved) : '#';
                $quarterlyCashflowGuardMsg  = Utility::fetchLinkMessage($lang, VW::RPT, 'quarterly_cashflow_report_route_unavailable') ?? 'Quarterly cashflow report route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a class="{{ VC::NV_LK }} quarterly-cashflow-link"
            id="pills-profile-tab"
            data-bs-toggle="pill"
            href="{{ $quarterlyCashflowUrl }}"
            role="tab"
            aria-controls="pills-profile"
            aria-selected="false"
            data-url="{{ $quarterlyCashflowUrl }}"
            data-guard-msg="{{ $quarterlyCashflowGuardMsg }}"
            data-sv-localized="true">
                {{ __('Quarterly') }}
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script src="{{ asset('assets/js/routes/reports/cashflow/quarterly/open.js') }}" defer></script>
            @endpush
        </li>
    </ul>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        @php
                            $monthlyCashflowBase          = VW::RPT.'.monthly.cashflow';
                            $monthlyCashflowKebab         = Str::kebab($monthlyCashflowBase);
                            $monthlyCashflowResolved      = Route::has($monthlyCashflowBase) ? $monthlyCashflowBase : (Route::has($monthlyCashflowKebab) ? $monthlyCashflowKebab : null);
                            $monthlyCashflowUrl           = $monthlyCashflowResolved ? route($monthlyCashflowResolved) : '#';
                            $monthlyCashflowFormId        = 'monthly_cashflow';
                            $monthlyApplyGuardMsg         = Utility::fetchLinkMessage($lang, VW::RPT, 'monthly_apply_cashflow_unavailable') ?? 'Monthly cashflow apply route is unavailable. Please contact technical support or your domain administrator.';
                            $monthlyResetGuardMsg         = Utility::fetchLinkMessage($lang, VW::RPT, 'monthly_reset_cashflow_unavailable') ?? 'Monthly cashflow reset route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        {{ Form::open([
                            'method'            => 'GET',
                            'url'               => $monthlyCashflowUrl,
                            'id'                => $monthlyCashflowFormId,
                            'data-url'          => $monthlyCashflowUrl,
                            'data-guard-msg'    => $monthlyApplyGuardMsg,
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
                                                {{ Form::select('year', (array)($yearList ?? []), request('year',''), ['class' => VC::FM_CT_SL, 'placeholder'=>__('Select a year')]) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                            <a href="#"
                                            class="{{ VC::BT_SM_PM }} apply-monthly-cashflow"
                                            data-form-id="{{ $monthlyCashflowFormId }}"
                                            data-guard-msg="{{ $monthlyApplyGuardMsg }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}"
                                            data-original-title="{{ __('apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>
                                            <a href="{{ $monthlyCashflowUrl }}"
                                            class="{{ VC::BT_SM_DG }} reset-monthly-cashflow"
                                            data-url="{{ $monthlyCashflowUrl }}"
                                            data-guard-msg="{{ $monthlyResetGuardMsg }}"
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
                            <script src="{{ asset('assets/js/routes/reports/cashflow/monthly/apply.js') }}" defer></script>
                            <script src="{{ asset('assets/js/routes/reports/cashflow/monthly/reset.js') }}" defer></script>
                        @endpush
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="printableArea">
        <div class="{{ VC::RW }} {{ VC::MT1 }}">
            <div class="col">
                <input type="hidden" value="{{ __('Monthly Cashflow').' '.__('Report of').' '.(data_get($filter,'startDateRange') ?? __('No start date available')).' '.__('to').' '.(data_get($filter,'endDateRange') ?? __('No end date available')) }}" id="filename">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Report') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ __('Monthly Cashflow') }}</h6>
                </div>
            </div>
            <div class="col">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Duration') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ (data_get($filter,'startDateRange') ?? __('No start date available')).' '.__('to').' '.(data_get($filter,'endDateRange') ?? __('No end date available')) }}</h6>
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
                                                <th width="20%">{{ __('Category') }}</th>
                                                @forelse(($monthList ?? []) as $month)
                                                    <th>{{ $month }}</th>
                                                @empty
                                                    <th>{{ __('No months available') }}</th>
                                                @endforelse
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="{{ max((isset($monthList) ? count($monthList) : 0),1) + 1 }}" class="font-bold"><span>{{ __('Revenue : ') }}</span></td>
                                            </tr>
                                            @forelse(($incomeArr ?? []) as $income)
                                                <tr>
                                                    <td>{{ data_get($income,'category',__('No category available')) }}</td>
                                                    @php $row = (array) data_get($income,'data',[]); @endphp
                                                    @if(empty($row))
                                                        <td colspan="{{ max((isset($monthList) ? count($monthList) : 0),1) }}">{{ __('No revenue data available') }}</td>
                                                    @else
                                                        @foreach($row as $data)
                                                            <td>{{ $user?->priceFormat($data) ?? number_format((float) $data,2) }}</td>
                                                        @endforeach
                                                    @endif
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ max((isset($monthList) ? count($monthList) : 0),1) + 1 }}">{{ __('No revenue rows available') }}</td>
                                                </tr>
                                            @endforelse
                                            <tr>
                                                <td colspan="{{ max((isset($monthList) ? count($monthList) : 0),1) + 1 }}" class="font-bold"><span>{{ __('Invoice : ') }}</span></td>
                                            </tr>
                                            @forelse(($invoiceArray ?? []) as $invoice)
                                                <tr>
                                                    <td>{{ data_get($invoice,'category',__('No category available')) }}</td>
                                                    @php $row = (array) data_get($invoice,'data',[]); @endphp
                                                    @if(empty($row))
                                                        <td colspan="{{ max((isset($monthList) ? count($monthList) : 0),1) }}">{{ __('No invoice data available') }}</td>
                                                    @else
                                                        @foreach($row as $data)
                                                            <td>{{ $user?->priceFormat($data) ?? number_format((float) $data,2) }}</td>
                                                        @endforeach
                                                    @endif
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="{{ max((isset($monthList) ? count($monthList) : 0),1) + 1 }}">{{ __('No invoice rows available') }}</td>
                                                </tr>
                                            @endforelse
                                            <tr>
                                                <td colspan="{{ max((isset($monthList) ? count($monthList) : 0),1) + 1 }}" class="font-bold"><span>{{ __('Total Income =  Revenue + Invoice ') }}</span></td>
                                            </tr>
                                            @php $totCols = max((isset($monthList) ? count($monthList) : 0),1); @endphp
                                            @if(!empty($chartIncomeArr))
                                                <tr>
                                                    <td width="20%" class="text-dark">{{ __('Total Income') }}</td>
                                                    @foreach($chartIncomeArr as $income)
                                                        <td>{{ $user?->priceFormat($income) ?? number_format((float) $income,2) }}</td>
                                                    @endforeach
                                                </tr>
                                            @else
                                                <tr>
                                                    <td width="20%" class="text-dark">{{ __('Total Income') }}</td>
                                                    <td colspan="{{ $totCols }}">{{ __('No total income data available') }}</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                <div class="{{ VC::CS12 }}">
                                    <h5>{{ __('Expense') }}</h5>
                                    <div class="table-responsive mt-4">
                                        <table class="{{ VC::TB }} mb-0">
                                            <thead>
                                                <tr>
                                                    <th width="20%">{{ __('Category') }}</th>
                                                    @forelse(($monthList ?? []) as $month)
                                                        <th>{{ $month }}</th>
                                                    @empty
                                                        <th>{{ __('No months available') }}</th>
                                                    @endforelse
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="{{ max((isset($monthList) ? count($monthList) : 0),1) + 1 }}" class="font-bold"><span>{{ __('Payment : ') }}</span></td>
                                                </tr>
                                                @forelse(($expenseArr ?? []) as $expense)
                                                    <tr>
                                                        <td>{{ data_get($expense,'category',__('No category available')) }}</td>
                                                        @php $row = (array) data_get($expense,'data',[]); @endphp
                                                        @if(empty($row))
                                                            <td colspan="{{ max((isset($monthList) ? count($monthList) : 0),1) }}">{{ __('No payment data available') }}</td>
                                                        @else
                                                            @foreach($row as $data)
                                                                <td>{{ $user?->priceFormat($data) ?? number_format((float) $data,2) }}</td>
                                                            @endforeach
                                                        @endif
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="{{ max((isset($monthList) ? count($monthList) : 0),1) + 1 }}">{{ __('No payment rows available') }}</td>
                                                    </tr>
                                                @endforelse

                                                <tr>
                                                    <td colspan="{{ max((isset($monthList) ? count($monthList) : 0),1) + 1 }}" class="font-bold"><span>{{ __('Bill : ') }}</span></td>
                                                </tr>
                                                @forelse(($billArray ?? []) as $bill)
                                                    <tr>
                                                        <td>{{ data_get($bill,'category',__('No category available')) }}</td>
                                                        @php $row = (array) data_get($bill,'data',[]); @endphp
                                                        @if(empty($row))
                                                            <td colspan="{{ max((isset($monthList) ? count($monthList) : 0),1) }}">{{ __('No bill data available') }}</td>
                                                        @else
                                                            @foreach($row as $data)
                                                                <td>{{ $user?->priceFormat($data) ?? number_format((float) $data,2) }}</td>
                                                            @endforeach
                                                        @endif
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="{{ max((isset($monthList) ? count($monthList) : 0),1) + 1 }}">{{ __('No bill rows available') }}</td>
                                                    </tr>
                                                @endforelse

                                                <tr>
                                                    <td colspan="{{ max((isset($monthList) ? count($monthList) : 0),1) + 1 }}" class="font-bold"><span>{{ __('Total Expense =  Payment + Bill ') }}</span></td>
                                                </tr>
                                                @if(!empty($chartExpenseArr))
                                                    <tr>
                                                        <td width="20%" class="text-dark">{{ __('Total Expenses') }}</td>
                                                        @foreach($chartExpenseArr as $expense)
                                                            <td>{{ $user?->priceFormat($expense) ?? number_format((float) $expense,2) }}</td>
                                                        @endforeach
                                                    </tr>
                                                @else
                                                    <tr>
                                                        <td width="20%" class="text-dark">{{ __('Total Expenses') }}</td>
                                                        <td colspan="{{ $totCols }}">{{ __('No total expense data available') }}</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="{{ VC::CS12 }}">
                                    <div class="table-responsive mt-1">
                                        <table class="{{ VC::TB }} mb-0">
                                            <thead>
                                                <tr>
                                                    <th colspan="{{ max((isset($monthList) ? count($monthList) : 0),1) + 1 }}" class="font-bold">
                                                        <span>{{ __('Net Profit = Total Income - Total Expense') }}</span>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(!empty($netProfitArray))
                                                    <tr>
                                                        <td width="20%" class="text-dark">{{ __('Net Profit') }}</td>
                                                        @foreach($netProfitArray as $profit)
                                                            <td>{{ $user?->priceFormat($profit) ?? number_format((float) $profit,2) }}</td>
                                                        @endforeach
                                                    </tr>
                                                @else
                                                    <tr>
                                                        <td width="20%" class="text-dark">{{ __('Net Profit') }}</td>
                                                        <td colspan="{{ $totCols }}">{{ __('No net profit data available') }}</td>
                                                    </tr>
                                                @endif
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


