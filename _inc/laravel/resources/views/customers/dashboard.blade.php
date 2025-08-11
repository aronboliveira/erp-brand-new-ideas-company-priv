@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Dashboard')}}
@endsection
{{--{{dd($invoiceChartData['data'])}}--}}
@push(StacksConstants::ADM_SCR_PG)
    <script>
        var options = {
            series: [
                {
                    name: "{{__('Unpaid')}}",
                    data: {!! json_encode($invoiceChartData['data']['unpaid']) !!}
                }, {
                    name: "{{__('Paid')}}",
                    data: {!! json_encode($invoiceChartData['data']['paid']) !!}
                }, {
                    name: "{{__('Partial Paid')}}",
                    data: {!! json_encode($invoiceChartData['data']['partial']) !!}
                }, {
                    name: "{{__('Due')}}",
                    data: {!! json_encode($invoiceChartData['data']['due']) !!}
                },

            ],
            chart: {
                height: 350,
                type: 'line',
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
            colors: ['#FF5630', '#36B37E', '#00B8D9', '#FFAB00'],
            dataLabels: {
                enabled: true,
            },
            stroke: {
                curve: 'smooth'
            },
            title: {
                text: '',
                align: 'left'
            },
            grid: {
                borderColor: '#e7e7e7',
                row: {
                    colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
                    opacity: 0.5
                },
            },
            markers: {
                size: 1
            },
            xaxis: {
                categories: {!! json_encode($invoiceChartData['month']) !!},
                title: {
                    text: 'Month'
                }
            },
            yaxis: {
                title: {
                    text: '{{__('Amount')}}'
                },

            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                floating: true,
                offsetY: -25,
                offsetX: -5
            }
        };
        var chart = new ApexCharts(document.querySelector("#chart-sales"), options);
        chart.render();
    </script>
@endpush
@section('content')
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-header">
                    <div class="{{ VC::RW }}">
                        @php
                            $widgets = [
                                [
                                    'percent' => $invoiceChartData['progressData']['unpaidPr'],
                                    'color'   => 'bg-danger',
                                    'label'   => __('Unpaid'),
                                    'bar'     => 'text-danger',
                                    'ratio'   => $invoiceChartData['progressData']['totalInvoice'] . '/' .
                                                $invoiceChartData['progressData']['totalUnpaidInvoice'],
                                ],
                                [
                                    'percent' => $invoiceChartData['progressData']['paidPr'],
                                    'color'   => 'bg-primary',
                                    'label'   => __('Paid'),
                                    'bar'     => 'text-success',
                                    'ratio'   => $invoiceChartData['progressData']['totalInvoice'] . '/' .
                                                $invoiceChartData['progressData']['totalPaidInvoice'],
                                ],
                                [
                                    'percent' => $invoiceChartData['progressData']['partialPr'],
                                    'color'   => 'bg-info',
                                    'label'   => __('Partial Paid'),
                                    'bar'     => 'text-info',
                                    'ratio'   => $invoiceChartData['progressData']['totalInvoice'] . '/' .
                                                $invoiceChartData['progressData']['totalPartialInvoice'],
                                ],
                                [
                                    'percent' => $invoiceChartData['progressData']['duePr'],
                                    'color'   => 'bg-warning',
                                    'label'   => __('Due'),
                                    'bar'     => 'text-warning',
                                    'ratio'   => $invoiceChartData['progressData']['totalInvoice'] . '/' .
                                                $invoiceChartData['progressData']['totalDueInvoice'],
                                ],
                            ];
                        @endphp

                        @foreach($widgets as $w)
                            <div class="col">
                                <div class="{{ VC::LG_FLSH }}">
                                    <a href="#" class="{{ VC::LGI_ACT }}">
                                        <div class="{{ VC::DFL_AIC_JCB }}">
                                            <div class="flex-fill {{ VC::TX_LM }}">
                                                <h6 class="{{ VC::PG_SM_BL }}">
                                                    {{ number_format($w['percent'], Utility::getValByName('decimal_number'), '.', '') . ' %' }}
                                                </h6>

                                                <div class="{{ VC::PG_XS }}">
                                                    <div class="progress-bar {{ $w['color'] }}"
                                                        role="progressbar"
                                                        style="width: {{ $w['percent'] }}%;"
                                                        aria-valuenow="{{ $w['percent'] }}" aria-valuemin="0"
                                                        aria-valuemax="100">
                                                    </div>
                                                </div>

                                                <div class="{{ VC::DFL_SPC_TXT }}">
                                                    <div>
                                                        <span class="font-weight-bold {{ $w['bar'] }}">{{ $w['label'] }}</span>
                                                    </div>
                                                    <div>{{ $w['ratio'] }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card-body">
                    <h6>{{ __('Current year') . ' - ' . date('Y') }}</h6>
                    <div class="scrollbar-inner">
                        <div id="chart-sales" height="300"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


