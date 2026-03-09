@php
    try {
} catch (\Throwable $e) {
        \Log::error('dashboard/super_admin — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Dashboard')}}
@endsection
@push('theme-script')
    <script async src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script async>
        (() => {
            const RG = window.RouteGuard || {};
            const getMsg = RG.getMsg || ((k, el) => el?.getAttribute?.('data-guard-msg') || '');
            const showError = RG.showToast || (m => { if (m) console.warn('[Dashboard]', m); });
            let errorMessage = '';
            const onErrorPointerUp = () => {
                if (errorMessage) { showError(errorMessage); errorMessage = ''; }
            };
            document.addEventListener('pointerup', onErrorPointerUp);
            try {
                const mountEl = document.querySelector('#chart-sales');
                if (!mountEl) throw new Error();
                if (!window.ApexCharts) { console.log('ApexCharts library missing'); return; }
                const dataSeries = {!! json_encode($chartData['data']) !!} ?? [];
                const dataLabels = {!! json_encode($chartData['label']) !!} ?? [];
                const chartOptions = {
                    series: [{ name: '{{ __("Income") }}', data: dataSeries }],
                    chart: { height: 300, type: 'area', dropShadow: { enabled: true, color: '#000', top: 18, left: 7, blur: 10, opacity: 0.2 }, toolbar: { show: false } },
                    dataLabels: { enabled: false },
                    stroke: { width: 2, curve: 'smooth' },
                    title: { text: '', align: 'left' },
                    xaxis: { categories: dataLabels, title: { text: '{{ __("Months") }}' } },
                    grid: { strokeDashArray: 4 },
                    legend: { show: false },
                    yaxis: { title: { text: '{{ __("Income") }}' } }
                };
                new ApexCharts(mountEl, chartOptions).render().catch(() => {
                    errorMessage = getMsg('chart_render_failed', mountEl);
                });
            } catch {
                errorMessage = getMsg('chart_render_failed', document.body);
            }
        })();
    </script>
@endpush
@php
    try {
        $admin_payment_setting = Utility::getAdminPaymentSetting();
        $cards=[
            ['bg'=>'bg-primary','icon'=>ViewClassNamesConstants::TI_USRS,'header'=>__('Total Users'),'value'=>$user?->total_user,'sub'=>__('Paid Users'),'subValue'=>$user['total_paid_user']],
            ['bg'=>'bg-warning','icon'=>'ti ti-shopping-cart','header'=>__('Total Orders'),'value'=>$user?->totalOrders,'sub'=>__('Total Order Amount'),'subValue'=>(isset($admin_payment_setting['currency_symbol'])?$admin_payment_setting['currency_symbol']:'$').$user['totalOrders_price']],
            ['bg'=>'bg-info','icon'=>'ti ti-trophy','header'=>__('Total Plans'),'value'=>$user?->total_plan,'sub'=>__('Most Purchase Plan'),'subValue'=>$user['mostPurchasedPlan']]
        ];
    } catch (\Throwable $e) {
        \Log::error('dashboard/super_admin — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@section('content')
    <div class="row">
        @php
            try {
                $cards=[
                    ['bg'=>ViewClassNamesConstants::BG_P,'icon'=>ViewClassNamesConstants::TI_USRS,'header'=>__('Total Users'),'value'=>$user?->total_user,'sub'=>__('Paid Users'),'subValue'=>$user['total_paid_user']],
                    ['bg'=>'bg-warning','icon'=>'ti ti-shopping-cart','header'=>__('Total Orders'),'value'=>$user?->totalOrders,'sub'=>__('Total Order Amount'),'subValue'=>($admin_payment_setting['currency_symbol'] ?? '$').$user['totalOrders_price']],
                    ['bg'=>ViewClassNamesConstants::BG_TPR,'icon'=>'ti ti-trophy','header'=>__('Total Plans'),'value'=>$user?->total_plan,'sub'=>__('Most Purchase Plan'),'subValue'=>$user['mostPurchasedPlan']]
                ];
            } catch (\Throwable $e) {
                \Log::error('dashboard/super_admin — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        @foreach($cards ?? [] as $c)
            <div class="{{ VC::CL4 }} {{ VC::CM6 }}">
                <div class="{{ ViewClassNamesConstants::CD }}">
                    <div class="{{ VC::CD_BD }} p-3">
                        <div class="{{ ViewClassNamesConstants::DFL_AIC_JCB }}">
                            <div class="{{ ViewClassNamesConstants::DFL_AIC }}">
                                <div class="theme-avatar {{ $c['bg'] }}">
                                    <i class="{{ $c['icon'] }}"></i>
                                </div>
                                <div class="{{ ViewClassNamesConstants::MS2 }}">
                                    <h6 class="{{ ViewClassNamesConstants::H6 }}">{{ $c['header'] }}</h6>
                                </div>
                            </div>
                            <div class="{{ ViewClassNamesConstants::FEND }}">
                                <h3>{{ $c['value'] }}</h3>
                            </div>
                            <div class="{{ ViewClassNamesConstants::MS2 }}">
                                <h6>{{ $c['sub'] }} : {{ $c['subValue'] }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="col-xxl-12">
            <h4 class="h4 font-weight-400">{{ __('Recent Order') }}</h4>
            <div class="{{ ViewClassNamesConstants::CD }}">
                <div class="chart">
                    <div id="chart-sales" data-color="primary" data-height="280" class="p-3"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
