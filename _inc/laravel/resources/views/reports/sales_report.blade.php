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
    {{ __('Sales Report') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Sales Report') }}</li>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/reports/sales/index/lang/report.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/reports/sales/index/report.js') }}"></script>
@endpush

@section(YieldingConstants::ADM_ACT_BTN)
    @php
        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
        $printBase        = VW::RPT.'.sales.report.print';
        $printKebab       = Str::kebab($printBase);
        $printResolved    = Route::has($printBase) ? $printBase : (Route::has($printKebab) ? $printKebab : null);
        $printActionRoute = $printResolved ? [$printResolved] : ['#'];
        $printActionUrl   = $printResolved ? route($printResolved) : '#';
        $printGuardMsg    = Utility::fetchLinkMessage($langValue, VW::RPT, 'print_sales_report_route_unavailable') ?? 'Print sales report route is unavailable. Please contact technical support or your domain administrator.';
        $exportBase        = VW::RPT.'.sales.export';
        $exportKebab       = Str::kebab($exportBase);
        $exportResolved    = Route::has($exportBase) ? $exportBase : (Route::has($exportKebab) ? $exportKebab : null);
        $exportActionRoute = $exportResolved ? [$exportResolved] : ['#'];
        $exportActionUrl   = $exportResolved ? route($exportResolved) : '#';
        $exportGuardMsg    = Utility::fetchLinkMessage($langValue, VW::RPT, 'export_sales_report_route_unavailable') ?? 'Export sales report route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <div class="float-end">
        {{ Form::open([
            'route'             => $printActionRoute,
            'id'                => 'sales-report-print',
            'data-url'          => $printActionUrl,
            'data-guard-msg'    => $printGuardMsg,
            'data-sv-localized' => 'true',
        ]) }}
            <input type="hidden" name="start_date" class="start_date">
            <input type="hidden" name="end_date" class="end_date">
            <input type="hidden" name="report" class="report">
            <button type="submit" class="{{ VC::BT_SM_PM }}" data-bs-toggle="tooltip" title="{{ __('Print') }}" data-original-title="{{ __('Print') }}">
                <i class="ti ti-printer"></i>
            </button>
        {{ Form::close() }}
    </div>
    <div class="float-end me-2">
        {{ Form::open([
            'route'             => $exportActionRoute,
            'id'                => 'sales-report-export',
            'data-url'          => $exportActionUrl,
            'data-guard-msg'    => $exportGuardMsg,
            'data-sv-localized' => 'true',
        ]) }}
            <input type="hidden" name="start_date" class="start_date">
            <input type="hidden" name="end_date" class="end_date">
            <input type="hidden" name="report" class="report">
            <button type="submit" class="{{ VC::BT_SM_PM }}" data-bs-toggle="tooltip" title="{{ __('Export') }}" data-original-title="{{ __('Export') }}">
                <i class="{{ VC::TI_EXP }}"></i>
            </button>
        {{ Form::close() }}
    </div>
    <div class="float-end me-2" id="filter">
        <button id="filter" class="{{ VC::BT_SM_PM }}"><i class="ti ti-filter"></i></button>
    </div>
    @push(StacksConstants::ADM_SCRP_PG)
        <script src="{{ asset('assets/js/routes/reports/sales/print.js') }}" defer></script>
        <script src="{{ asset('assets/js/routes/reports/sales/export.js') }}" defer></script>
    @endpush
@endsection
    {{-- <div class="float-end me-2">
        <a href="{{ route(VW::RPT . '.balance.sheet', 'vertical') }}" class="{{ VC::BT_SM_PM }}" data-bs-toggle="tooltip"
            title="{{ __('Vertical View') }}" data-original-title="{{ __('Vertical View') }}"><i
                class="ti ti-separator-horizontal"></i></a>
    </div> --}}
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::MT4 }}">
        <div class="{{ VC::RW }} justify-content-center">
            <div class="{{ VC::CM12 }}">
                <div class="mt-2" id="multiCollapseExample1">
                    <div class="{{ VC::CD }}" id="show_filter" style="display:none;">
                        <div class="card-body">
                            @php
                                $salesBase        = VW::RPT.'.sales';
                                $salesKebab       = Str::kebab($salesBase);
                                $salesResolved    = Route::has($salesBase) ? $salesBase : (Route::has($salesKebab) ? $salesKebab : null);
                                $actionRoute      = $salesResolved ? [$salesResolved] : ['#'];
                                $actionUrl        = $salesResolved ? route($salesResolved) : '#';
                                $langValue        = isset($lang) ? $lang : Utility::fetchUserLang();
                                $applyGuardMsg    = Utility::fetchLinkMessage($langValue, VW::RPT, 'apply_sales_route_unavailable') ?? 'Apply sales route is unavailable. Please contact technical support or your domain administrator.';
                                $resetGuardMsg    = Utility::fetchLinkMessage($langValue, VW::RPT, 'reset_sales_route_unavailable') ?? 'Reset sales route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            {{ Form::open(['route'=>$actionRoute,'method'=>'GET','id'=>'report_bill_summary','data-url'=>$actionUrl,'data-guard-msg'=>$applyGuardMsg,'data-sv-localized'=>'true']) }}
                                <div class="{{ VC::R_ALC_JCE }}">
                                    <div class="col-xl-10">
                                        <div class="{{ VC::RW }}">
                                            <div class="{{ VC::CL_XL3 }}"><div class="btn-box"></div></div>
                                            <div class="{{ VC::CL_XL3 }}"><div class="btn-box"></div></div>
                                            <div class="{{ VC::CL_XL3 }}">
                                                <div class="btn-box">
                                                    {{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
                                                    {{ Form::date('start_date', $filter['startDateRange'], ['class' => 'startDate ' . VC::FM_CT]) }}
                                                </div>
                                            </div>
                                            <div class="{{ VC::CL_XL3 }}">
                                                <div class="btn-box">
                                                    {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                                                    {{ Form::date('end_date', $filter['endDateRange'], ['class' => 'endDate ' . VC::FM_CT]) }}
                                                </div>
                                            </div>
                                            <input type="hidden" name="view" value="horizontal">
                                        </div>
                                    </div>
                                    <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                        <div class="{{ VC::RW }}">
                                            <div class="{{ VC::C_AT }}">
                                                <a id="apply-sales-index"
                                                href="#"
                                                class="{{ VC::BT_SM_PM }}"
                                                data-form-id="report_bill_summary"
                                                data-guard-msg="{{ $applyGuardMsg }}"
                                                data-sv-localized="true"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('Apply') }}"
                                                data-original-title="{{ __('apply') }}">
                                                    <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                                </a>
                                                <a id="reset-sales-index"
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
                            @push(StacksConstants::ADM_SCRP_PG)
                                <script src="{{ asset('assets/js/routes/reports/sales/apply.js') }}" defer></script>
                                <script src="{{ asset('assets/js/routes/reports/sales/reset.js') }}" defer></script>
                            @endpush
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}" id="invoice-container">
            <div class="{{ VC::CD }}">
                <div class="card-header">
                    <div class="{{ VC::DFL_JCB }} w-100">
                        <ul class="{{ VC::NAV_PL }} {{ VC::MB3 }}" id="pills-tab" role="tablist">
                            <li class="{{ VC::NV_IT }}">
                                <a class="{{ VC::NV_LK }} active"
                                   id="tab-items"
                                   data-bs-toggle="pill"
                                   href="#pane-items"
                                   role="tab"
                                   aria-controls="pane-items"
                                   aria-selected="true">{{ __('Sales by Item') }}</a>
                            </li>
                            <li class="{{ VC::NV_IT }}">
                                <a class="{{ VC::NV_LK }}"
                                   id="tab-customers"
                                   data-bs-toggle="pill"
                                   href="#pane-customers"
                                   role="tab"
                                   aria-controls="pane-customers"
                                   aria-selected="false">{{ __('Sales by Customer') }}</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card-body">
                    <div class="{{ VC::RW }}">
                        <div class="{{ VC::CS12 }}">
                            <div class="tab-content" id="myTabContent2">
                                <div class="tab-pane fade show active"
                                     id="pane-items"
                                     role="tabpanel"
                                     aria-labelledby="tab-items">
                                    @php $totQty = 0; $totAmt = 0.0; @endphp
                                    <table class="{{ VC::TB }} table-flush" id="report-items-table">
                                        <thead>
                                            <tr>
                                                <th width="33%">{{ __('Invoice Item') }}</th>
                                                <th width="33%" class="text-end">{{ __('Quantity Sold') }}</th>
                                                <th width="33%" class="text-end">{{ __('Amount') }}</th>
                                                <th class="text-end">{{ __('Average Price') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($invoiceItems as $row)
                                                @php
                                                    $qty = (float)($row['quantity'] ?? 0);
                                                    $amt = (float)($row['price'] ?? 0);
                                                    $avg = isset($row['avg_price']) ? (float)$row['avg_price'] : ($qty > 0 ? $amt / $qty : 0);
                                                    $totQty += $qty; $totAmt += $amt;
                                                @endphp
                                                <tr>
                                                    <td>{{ $row['name'] }}</td>
                                                    <td class="text-end">{{ $qty }}</td>
                                                    <td class="text-end">{{ $user?->priceFormat($amt) }}</td>
                                                    <td class="text-end">{{ $user?->priceFormat($avg) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">{{ __('No data found') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>

                                        @if(!empty($invoiceItems))
                                            <tfoot>
                                                <tr>
                                                    <th>{{ __('Total') }}</th>
                                                    <th class="text-end">{{ $totQty }}</th>
                                                    <th class="text-end">{{ $user?->priceFormat($totAmt) }}</th>
                                                    <th class="text-end">{{ $user?->priceFormat($totQty > 0 ? $totAmt / $totQty : 0) }}</th>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>

                                <div class="tab-pane fade"
                                     id="pane-customers"
                                     role="tabpanel"
                                     aria-labelledby="tab-customers">
                                    @php $totCount = 0; $totSales = 0.0; $totWithTax = 0.0; @endphp
                                    <table class="{{ VC::TB }} table-flush" id="report-customers-table">
                                        <thead>
                                            <tr>
                                                <th width="33%">{{ __('Customer Name') }}</th>
                                                <th width="33%" class="text-end">{{ __('Invoice Count') }}</th>
                                                <th width="33%" class="text-end">{{ __('Sales') }}</th>
                                                <th class="text-end">{{ __('Sales With Tax') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($invoiceCustomers as $row)
                                                @php
                                                    $count = (int)($row['invoice_count'] ?? 0);
                                                    $amt   = (float)($row['price'] ?? 0);
                                                    $tax   = (float)($row['total_tax'] ?? 0);
                                                    $totCount += $count; $totSales += $amt; $totWithTax += ($amt + $tax);
                                                @endphp
                                                <tr>
                                                    <td>{{ $row['name'] }}</td>
                                                    <td class="text-end">{{ $count }}</td>
                                                    <td class="text-end">{{ $user?->priceFormat($amt) }}</td>
                                                    <td class="text-end">{{ $user?->priceFormat($amt + $tax) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">{{ __('No data found') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>

                                        @if(!empty($invoiceCustomers))
                                            <tfoot>
                                                <tr>
                                                    <th>{{ __('Total') }}</th>
                                                    <th class="text-end">{{ $totCount }}</th>
                                                    <th class="text-end">{{ $user?->priceFormat($totSales) }}</th>
                                                    <th class="text-end">{{ $user?->priceFormat($totWithTax) }}</th>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
