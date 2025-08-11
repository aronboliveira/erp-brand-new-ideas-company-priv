@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\{Crypt, Route};
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Invoice Summary')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Invoice Summary')}}</li>
@endsection

@push('theme-script')
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
@endpush

@push(StacksConstants::ADM_SCR_PG)
    <script>
        (function () {
            var chartBarOptions = {
                series: [
                    {
                        name: '{{ __("Invoice") }}',
                        data:  {!! json_encode($invoiceTotal) !!},

                    },
                ],

                chart: {
                    height: 300,
                    type: 'bar',
                    // type: 'line',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories: {!! json_encode($monthList) !!},
                    title: {
                        text: '{{ __("Months") }}'
                    }
                },
                colors: ['#6fd944', '#6fd944'],

                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                },
                // markers: {
                //     size: 4,
                //     colors: ['#ffa21d', '#FF3A6E'],
                //     opacity: 0.9,
                //     strokeWidth: 2,
                //     hover: {
                //         size: 7,
                //     }
                // },
                yaxis: {
                    title: {
                        text: '{{ __("Invoice") }}'
                    },

                }

            };
            var arChart = new ApexCharts(document.querySelector("#chart-sales"), chartBarOptions);
            arChart.render();
        })();

    </script>
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        var filename = $('#filename').val();

        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {type: 'jpeg', quality: 1},
                html2canvas: {scale: 4, dpi: 72, letterRendering: true},
                jsPDF: {unit: 'in', format: 'A2'}
            };
            html2pdf().set(opt).from(element).save();
        }

        $(document).ready(function () {
            var filename = $('#filename').val();
            $('#report-dataTable').DataTable({
                dom: 'lBfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        title: filename
                    },
                    {
                        extend: 'pdf',
                        title: filename
                    }, {
                        extend: 'csv',
                        title: filename
                    }
                ]
            });
        });
    </script>
@endpush

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        {{--        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
        {{--            <i class="ti ti-filter"></i>--}}
        {{--        </a>--}}
        <a href="#" class="btn btn-sm btn-primary" onclick="saveAsPDF()"data-bs-toggle="tooltip" title="{{__('Download')}}" data-original-title="{{__('Download')}}">
            <span class="btn-inner--icon"><i class="ti ti-download"></i></span>
        </a>
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                    {{ Collective\Html\FormFacade::open(array('route' => array(ViewsConstants::RPT . '.invoice.summary'),'method' => 'GET','id'=>'report_invoice_summary')) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('start_month', __('Start Month'),['class'=>'form-label']) }}
                                                {{--                                            {{ Collective\Html\FormFacade::month('start_month',isset($_GET['start_month'])?$_GET['start_month']:'', array('class' => 'month-btn form-control')) }}--}}
                                            {{Collective\Html\FormFacade::month('start_month',isset($_GET['start_month'])?$_GET['start_month']:date('Y-m', strtotime("-5 month")),array('class'=>'month-btn form-control'))}}

                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('end_month', __('End Month'),['class'=>'form-label']) }}
                                            {{--                                            {{ Collective\Html\FormFacade::month('end_month',isset($_GET['end_month'])?$_GET['end_month']:'', array('class' => 'month-btn form-control')) }}--}}
                                            {{Collective\Html\FormFacade::month('end_month',isset($_GET['end_month'])?$_GET['end_month']:date('Y-m'),array('class'=>'month-btn form-control'))}}

                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        {{ Collective\Html\FormFacade::label('customer', __('Customer'),['class'=>'form-label']) }}

                                        {{ Collective\Html\FormFacade::select('customer',$customer,isset($_GET['customer'])?$_GET['customer']:'', array('class' => 'form-control select')) }}

                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                        {{ Collective\Html\FormFacade::label('status', __('Status'),['class'=>'form-label']) }}

                                        {{ Collective\Html\FormFacade::select('status', [''=>'Select Status']+$status,isset($_GET['status'])?$_GET['status']:'', array('class' => 'form-control select')) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_invoice_summary').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route(ViewsConstants::RPT . '.invoice.summary')}}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Collective\Html\FormFacade::close() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div id="printableArea">
        @php
            $filename = "{$filter['status']} " 
                . __('Invoice') 
                . " Report of {$filter['startDateRange']} to {$filter['endDateRange']} of {$filter['customer']}";
        
            $items = [
                ['label'=> __('Report'),   'value'=> __('Invoice Summary'),                               'when'=> true],
                ['label'=> __('Customer'), 'value'=> $filter['customer'],                                'when'=> $filter['customer'] != __('All')],
                ['label'=> __('Status'),   'value'=> $filter['status'],                                  'when'=> $filter['status']   != __('All')],
                ['label'=> __('Duration'), 'value'=> "{$filter['startDateRange']} to {$filter['endDateRange']}", 'when'=> true],
            ];
        @endphp
        <input type="hidden" id="filename" value="{{ $filename }}">
        <div class="{{ ViewClassNamesConstants::RW }} mt-3">
            @foreach($items as $item)
                @if($item['when'])
                    <div class="col">
                        <div class="{{ ViewClassNamesConstants::CD }} p-4 mb-4">
                            <h7 class="report-text gray-text mb-0">{{ $item['label'] }} :</h7>
                            <h6 class="report-text mb-0">{{ $item['value'] }}</h6>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
        @php
            $stats = [
                ['label' => __('Total Invoice'), 'value' => $totalInvoice],
                ['label' => __('Total Paid'),    'value' => $totalPaidInvoice],
                ['label' => __('Total Due'),     'value' => $totalDueInvoice],
            ];
        @endphp
        <div class="{{ ViewClassNamesConstants::RW }}">
            @foreach($stats as $stat)
                <div class="col-xl-4 col-md-6 col-lg-4">
                    <div class="{{ ViewClassNamesConstants::CD }} p-4 mb-4">
                        <h7 class="report-text gray-text mb-0">{{ $stat['label'] }}</h7>
                        <h6 class="report-text mb-0">{{ Auth::user()->priceFormat($stat['value']) }}</h6>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="row">
            <div class="col-12" id="invoice-container">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between w-100">
                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="profile-tab3" data-bs-toggle="pill" href="#summary" role="tab" aria-controls="pills-summary" aria-selected="true">{{__('Summary')}}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="contact-tab4" data-bs-toggle="pill" href="#invoices" role="tab" aria-controls="pills-invoice" aria-selected="false">{{__('Invoices')}}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="tab-content" id="myTabContent2">
                                    <div class="tab-pane fade fade" id="invoices" role="tabpanel" aria-labelledby="profile-tab3">
                                        <table class="table table-flush" id="report-dataTable">
                                            <thead>
                                            <tr>
                                                <th> {{__('Invoice')}}</th>
                                                <th> {{__('Date')}}</th>
                                                <th> {{__('Customer')}}</th>
                                                <th> {{__('Category')}}</th>
                                                <th> {{__('Status')}}</th>
                                                <th> {{__('	Paid Amount')}}</th>
                                                <th> {{__('Due Amount')}}</th>
                                                <th> {{__('Payment Date')}}</th>
                                                <th> {{__('Amount')}}</th>
                                            </tr>
                                            </thead>
                                            @php
                                                $statusClasses = [
                                                    0 => 'bg-primary',
                                                    1 => 'bg-warning',
                                                    2 => 'bg-danger',
                                                    3 => 'bg-info',
                                                    4 => 'bg-success',
                                                ];
                                            @endphp
                                            <tbody>
                                                @foreach($invoices as $invoice)
                                                    @php
                                                        $status = $invoice->status;
                                                        $badgeClass = $statusClasses[$status] ?? 'bg-secondary';
                                                    @endphp
                                                    <tr>
                                                        <td class="Id">
                                                            <a href="{{ route(ViewsConstants::INV . '.show', Crypt::encrypt($invoice->id)) }}"
                                                            class="btn btn-outline-primary">
                                                                {{ Auth::user()->invoiceNumberFormat($invoice->invoice_id) }}
                                                            </a>
                                                        </td>
                                                        <td>{{ Auth::user()->dateFormat($invoice->send_date) }}</td>
                                                        <td>{{ optional($invoice->customer)->name ?? '-' }}</td>
                                                        <td>{{ optional($invoice->category)->name ?? '-' }}</td>
                                                        <td>
                                                            <span class="badge status_badge {{ $badgeClass }} p-2 px-3 rounded">
                                                                {{ __(\App\Models\Invoice::$statuses[$status]) }}
                                                            </span>
                                                        </td>
                                                        <td>{{ Auth::user()->priceFormat($invoice->getTotal() - $invoice->getDue()) }}</td>
                                                        <td>{{ Auth::user()->priceFormat($invoice->getDue()) }}</td>
                                                        <td>{{ optional($invoice->lastPayments)->date ? Auth::user()->dateFormat($invoice->lastPayments->date) : '' }}</td>
                                                        <td>{{ Auth::user()->priceFormat($invoice->getTotal()) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="tab-pane fade fade show active" id="summary" role="tabpanel" aria-labelledby="profile-tab3">
                                        <div class="col-sm-12">
                                            <div class="scrollbar-inner">
                                                <div id="chart-sales" data-color="primary" data-type="bar" data-height="300" ></div>
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
    </div>
@endsection
