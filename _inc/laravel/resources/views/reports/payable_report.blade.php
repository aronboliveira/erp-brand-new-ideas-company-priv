@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Payable Reports') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Payable Reports') }}</li>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/reports/payables/index/lang/pdf.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/reports/payables/index/pdf.js') }}"></script>
@endpush
    {{-- <div class="float-end me-2">
        {{ Form::open(['route' => ['receivables.export']]) }}
        <input type="hidden" name="start_date" class="start_date">
        <input type="hidden" name="end_date" class="end_date">
        <input type="hidden" name="report" class="report">
        <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Export') }}"
            data-original-title="{{ __('Export') }}"><i class="ti ti-file-export"></i></button>
        {{ Form::close() }}
    </div> --}}
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @php
            $payablesPrintBase         = VW::RPT.'.payables.print';
            $payablesPrintKebab        = Str::kebab($payablesPrintBase);
            $payablesPrintResolved     = Route::has($payablesPrintBase) ? $payablesPrintBase : (Route::has($payablesPrintKebab) ? $payablesPrintKebab : null);
            $payablesPrintUrl          = $payablesPrintResolved ? route($payablesPrintResolved) : '#';
            $payablesPrintFormId       = 'payables-print-form';
            $payablesPrintGuardMsg     = Utility::fetchLinkMessage($lang, VW::RPT, 'payables_print_route_unavailable') ?? 'Payables print route is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        {{ Form::open([
            'method'            => 'POST',
            'url'               => $payablesPrintUrl,
            'id'                => $payablesPrintFormId,
            'data-url'          => $payablesPrintUrl,
            'data-guard-msg'    => $payablesPrintGuardMsg,
            'data-sv-localized' => 'true',
        ]) }}
            <input type="hidden" name="start_date" class="start_date">
            <input type="hidden" name="end_date" class="end_date">
            <input type="hidden" name="report" class="report">
            <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{ __('Print') }}" data-original-title="{{ __('Print') }}"><i class="ti ti-printer"></i></button>
        {{ Form::close() }}
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/payables/index/print.js') }}" defer></script>
        @endpush
    </div>
    <div class="float-end me-2" id="filter">
        <button id="filter" class="{{ VC::BT_SM_PM }}"><i class="ti ti-filter"></i></button>
    </div>
@endsection
    {{-- <div class="float-end me-2">
        <a href="{{ route(VW::RPT . '.balance.sheet', 'vertical') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
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
                                $payablesBase           = VW::RPT.'.payables';
                                $payablesKebab          = Str::kebab($payablesBase);
                                $payablesResolved       = Route::has($payablesBase) ? $payablesBase : (Route::has($payablesKebab) ? $payablesKebab : null);
                                $payablesUrl            = $payablesResolved ? route($payablesResolved) : '#';
                                $payablesFormId         = 'report_payable_summary';
                                $applyGuardMsg          = Utility::fetchLinkMessage($lang, VW::RPT, 'apply_payables_route_unavailable') ?? 'Payables apply route is unavailable. Please contact technical support or your domain administrator.';
                                $resetGuardMsg          = Utility::fetchLinkMessage($lang, VW::RPT, 'reset_payables_route_unavailable') ?? 'Payables reset route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            {{ Form::open([
                                'method'            => 'GET',
                                'url'               => $payablesUrl,
                                'id'                => $payablesFormId,
                                'data-url'          => $payablesUrl,
                                'data-guard-msg'    => $applyGuardMsg,
                                'data-sv-localized' => 'true',
                            ]) }}
                                <div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::JCE }}">
                                    <div class="col-xl-10">
                                        <div class="{{ VC::RW }}">
                                            <div class="{{ VC::CL_XLG4 }}">
                                                <div class="btn-box"></div>
                                            </div>
                                            <div class="{{ VC::CL_XLG4 }}">
                                                <div class="btn-box"></div>
                                            </div>
                                            <div class="{{ VC::CL_XLG4 }}">
                                                <div class="btn-box">
                                                    {{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
                                                    {{ Form::date('start_date', $filter['startDateRange'], ['class' => 'startDate ' . VC::FM_CT]) }}
                                                </div>
                                            </div>
                                            <div class="{{ VC::CL_XLG4 }}">
                                                <div class="btn-box">
                                                    {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                                                    {{ Form::date('end_date', $filter['endDateRange'], ['class' => 'endDate ' . VC::FM_CT]) }}
                                                </div>
                                            </div>
                                            <input type="hidden" name="report" class="report">
                                        </div>
                                    </div>
                                    <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                        <div class="{{ VC::RW }}">
                                            <div class="{{ VC::C_AT }}">
                                                <a href="#"
                                                class="{{ VC::BT_SM_PM }} apply-payables"
                                                data-form-id="{{ $payablesFormId }}"
                                                data-guard-msg="{{ $applyGuardMsg }}"
                                                data-sv-localized="true"
                                                data-bs-toggle="tooltip"
                                                title="{{ __('Apply') }}"
                                                data-original-title="{{ __('apply') }}">
                                                    <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                                </a>
                                                <a href="{{ $payablesUrl }}"
                                                class="{{ VC::BT_SM_DG }} reset-payables"
                                                data-url="{{ $payablesUrl }}"
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
                                <script src="{{ asset('assets/js/routes/reports/payables/index/apply.js') }}" defer></script>
                                <script src="{{ asset('assets/js/routes/reports/payables/index/reset.js') }}" defer></script>
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
                    <div class="{{ VC::DFL }} {{ VC::JCB }} w-100">
                        <ul class="{{ VC::NAV_PL }} {{ VC::MB3 }}" id="pills-tab" role="tablist">
                            <li class="{{ VC::NV_IT }}">
                                <a class="{{ VC::NV_LK }} active" id="payable-tab1" data-bs-toggle="pill" href="#vendor_balance"
                                   role="tab" aria-controls="pills-vendor-balance"
                                   aria-selected="true">{{ __('Vendor Balance') }}</a>
                            </li>
                            <li class="{{ VC::NV_IT }}">
                                <a class="{{ VC::NV_LK }}" id="payable-tab2" data-bs-toggle="pill" href="#payable_summary"
                                   role="tab" aria-controls="pills-payable-summary"
                                   aria-selected="false">{{ __('Payable Summary') }}</a>
                            </li>
                            <li class="{{ VC::NV_IT }}">
                                <a class="{{ VC::NV_LK }}" id="payable-tab3" data-bs-toggle="pill" href="#payable_details"
                                   role="tab" aria-controls="pills-payable-details"
                                   aria-selected="false">{{ __('Payable Details') }}</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card-body">
                    <div class="{{ VC::RW }}">
                        <div class="{{ VC::CS12 }}">
                            <div class="tab-content" id="myTabContent2">
                                <div class="tab-pane fade fade show active" id="vendor_balance" role="tabpanel"
                                     aria-labelledby="payable-tab1">
                                    <table class="{{ VC::TB }} table-flush" id="report-dataTable">
                                        <thead>
                                            <tr>
                                                <th width="33%"> {{ __('Vendor Name') }}</th>
                                                <th width="33%"> {{ __('Billed Amount') }}</th>
                                                <th width="33%"> {{ __('Available Debit') }}</th>
                                                <th class="text-end"> {{ __('Closing Balance') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $mergedArray = [];
                                                foreach ($payableVendors as $item) {
                                                    $name = $item['name'];

                                                    if (!isset($mergedArray[$name])) {
                                                        $mergedArray[$name] = [
                                                            'name' => $name,
                                                            'price' => 0.0,
                                                            'pay_price' => 0.0,
                                                            'total_tax' => 0.0,
                                                            'debit_price' => 0.0,
                                                        ];
                                                    }

                                                    $mergedArray[$name]['price'] += floatval($item['price']);
                                                    if ($item['pay_price'] !== null) {
                                                        $mergedArray[$name]['pay_price'] += floatval($item['pay_price']);
                                                    }
                                                    $mergedArray[$name]['total_tax'] += floatval($item['total_tax']);
                                                    $mergedArray[$name]['debit_price'] += floatval($item['debit_price']);
                                                }
                                                $resultArray = array_values($mergedArray);
                                                $total = 0;
                                            @endphp
                                            @foreach ($resultArray as $receivableCustomer)
                                                <tr>
                                                    @php
                                                        $customerBalance = $receivableCustomer['price'] + $receivableCustomer['total_tax'] - $receivableCustomer['pay_price'];
                                                        $balance = $customerBalance - $receivableCustomer['debit_price'];
                                                        $total += $balance;
                                                    @endphp
                                                    <td> {{ $receivableCustomer['name'] }}</td>
                                                    <td> {{ $user?->priceFormat($customerBalance) }} </td>
                                                    <td> {{ !empty($receivableCustomer['debit_price']) ? $user?->priceFormat($receivableCustomer['debit_price']) : $user?->priceFormat(0) }}
                                                    </td>
                                                    <td class="text-end"> {{ $user?->priceFormat($balance) }} </td>
                                                </tr>
                                            @endforeach
                                            @if ($payableVendors != [])
                                                <tr>
                                                    <th>{{ __('Total') }}</th>
                                                    <td></td>
                                                    <td></td>
                                                    <th class="text-end">{{ $user?->priceFormat($total) }}</th>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                <div class="tab-pane fade fade show" id="payable_summary" role="tabpanel"
                                     aria-labelledby="payable-tab2">
                                    <table class="{{ VC::TB }} table-flush" id="report-dataTable">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Vendor Name') }}</th>
                                                <th>{{ __('Date') }}</th>
                                                <th>{{ __('Transaction') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                <th>{{ __('Transaction Type') }}</th>
                                                <th>{{ __('Total') }}</th>
                                                <th>{{ __('Balance') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $total = 0;
                                                $totalAmount = 0;
                                                function compare($a, $b)
                                                {
                                                    return strtotime($b['bill_date']) - strtotime($a['bill_date']);
                                                }
                                                usort($payableSummaries, 'compare');
                                            @endphp
                                            @foreach ($payableSummaries as $payableSummary)
                                                <tr>
                                                    @php
                                                        if ($payableSummary['bill']) {
                                                            $payableBalance = $payableSummary['price'] + $payableSummary['total_tax'];
                                                        } else {
                                                            $payableBalance = -$payableSummary['price'];
                                                        }
                                                        $pay_price = ($payableSummary['pay_price'] != null) ? $payableSummary['pay_price'] : 0;
                                                        $balance = $payableBalance - $pay_price;
                                                        $total += $balance;
                                                        $totalAmount += $payableBalance;
                                                    @endphp
                                                    <td> {{ $payableSummary['name'] }}</td>
                                                    <td> {{ $payableSummary['bill_date'] }}</td>
                                                    @if ($payableSummary['bill'])
                                                        @if ($payableSummary['type'] == 'Bill')
                                                            <td> {{ $user?->billNumberFormat($payableSummary['bill']) }}</td>
                                                        @elseif($payableSummary['type'] == 'Expense')
                                                            <td> {{ $user?->expenseNumberFormat($payableSummary['bill']) }}</td>
                                                        @endif
                                                    @else
                                                        <td>{{ __('Debit Note') }}</td>
                                                    @endif
                                                    </td>
                                                    @php
                                                        $statusClasses = [
                                                            0 => 'bg-secondary',
                                                            1 => 'bg-warning',
                                                            2 => 'bg-danger',
                                                            3 => 'bg-info',
                                                            4 => 'bg-primary'
                                                        ];
                                                        $bgClass = $statusClasses[$payableSummary['status']] ?? null;
                                                    @endphp
                                                    <td>
                                                        @if($bgClass)
                                                            <span class="status_badge {{ VC::BDG }} {{ $bgClass }} p-2 px-3 rounded">
                                                                {{ __(Invoice::$statuses[$payableSummary['status']]) }}
                                                            </span>
                                                        @else
                                                            <span class="p-2 px-3">-</span>
                                                        @endif
                                                    </td>
                                                    @if ($payableSummary['bill'])
                                                        <td> {{ $payableSummary['type'] }}
                                                    @else
                                                        <td>{{ __('Debit Note') }}</td>
                                                    @endif
                                                    <td> {{ $user?->priceFormat($payableBalance) }} </td>
                                                    <td> {{ $user?->priceFormat($balance) }} </td>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @if ($payableSummaries != [])
                                                <tr>
                                                    <th>{{ __('Total') }}</th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th>{{ $user?->priceFormat($totalAmount) }}</th>
                                                    <th>{{ $user?->priceFormat($total) }}</th>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                <div class="tab-pane fade fade show" id="payable_details" role="tabpanel"
                                     aria-labelledby="payable-tab3">
                                    <table class="{{ VC::TB }} table-flush" id="report-dataTable">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Vendor Name') }}</th>
                                                <th>{{ __('Date') }}</th>
                                                <th>{{ __('Transaction') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                <th>{{ __('Transaction Type') }}</th>
                                                <th>{{ __('Item Name') }}</th>
                                                <th>{{ __('Quantity Ordered') }}</th>
                                                <th>{{ __('Item Price') }}</th>
                                                <th>{{ __('Total') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $total = 0;
                                                $totalQuantity = 0;
                                                function compares($a, $b)
                                                {
                                                    return strtotime($b['bill_date']) - strtotime($a['bill_date']);
                                                }
                                                usort($payableDetails, 'compares');
                                            @endphp
                                            @foreach ($payableDetails as $payableDetail)
                                                <tr>
                                                    @php
                                                        if ($payableDetail['bill']) {
                                                            $receivableBalance = $payableDetail['price'];
                                                        } else {
                                                            $receivableBalance = -$payableDetail['price'];
                                                        }
                                                        if ($payableDetail['bill']) {
                                                            $quantity = $payableDetail['quantity'];
                                                        }
                                                        else {
                                                            $quantity = 0;
                                                        }

                                                        if ($payableDetail['bill']) {
                                                            $itemTotal = $receivableBalance * $payableDetail['quantity'];
                                                        } else {
                                                            $itemTotal = -$payableDetail['price'];
                                                        }
                                                        $total += $itemTotal;
                                                        $totalQuantity += $quantity;
                                                    @endphp
                                                    <td> {{ $payableDetail['name'] }}</td>
                                                    <td> {{ $payableDetail['bill_date'] }}</td>
                                                    @if ($payableDetail['bill'])
                                                        @if ($payableDetail['type'] == 'Bill')
                                                            <td> {{ $user?->billNumberFormat($payableDetail['bill']) }}</td>
                                                        @elseif($payableDetail['type'] == 'Expense')
                                                            <td> {{ $user?->expenseNumberFormat($payableDetail['bill']) }}</td>
                                                        @endif
                                                    @else
                                                        <td>{{ __('Debit Note') }}</td>
                                                    @endif
                                                    </td>
                                                    @php
                                                        $statusClasses = [
                                                            0 => 'bg-secondary',
                                                            1 => 'bg-warning',
                                                            2 => 'bg-danger',
                                                            3 => 'bg-info',
                                                            4 => 'bg-primary'
                                                        ];
                                                        $bgClass = $statusClasses[$payableDetail['status']] ?? null;
                                                    @endphp
                                                    <td>
                                                        @if($bgClass)
                                                            <span class="status_badge {{ VC::BDG }} {{ $bgClass }} p-2 px-3 rounded">
                                                                {{ __(Invoice::$statuses[$payableDetail['status']]) }}
                                                            </span>
                                                        @else
                                                            <span class="p-2 px-3">-</span>
                                                        @endif
                                                    </td>
                                                    @if ($payableDetail['bill'])
                                                        <td> {{ $payableDetail['type'] }}
                                                    @else
                                                        <td>{{ __('Debit Note') }}</td>
                                                    @endif
                                                    <td>{{ $payableDetail['product_name'] }}</td>
                                                    <td> {{ $quantity }}</td>
                                                    <td>{{ $user?->priceFormat($receivableBalance) }}</td>
                                                    <td>{{ $user?->priceFormat($itemTotal) }}</td>
                                                </tr>
                                            @endforeach
                                            @if ($payableDetails != [])
                                                <tr>
                                                    <th>{{ __('Total') }}</th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th>{{ $totalQuantity }}</th>
                                                    <th></th>
                                                    <th>{{ $user?->priceFormat($total) }}</th>
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
@endsection
