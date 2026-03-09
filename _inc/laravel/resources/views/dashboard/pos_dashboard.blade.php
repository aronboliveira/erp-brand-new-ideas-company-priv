@php
    try {
} catch (\Throwable $e) {
        \Log::error('dashboard/pos_dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Dashboard')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/dashboards/pos/lang/chart.js') }}"></script>
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
            window.addEventListener('DOMContentLoaded', () => {
                try {
                    const chartEl = document.querySelector('#traffic-chart');
                    if (!chartEl) throw new Error('traffic_chart_unavailable');
                    const opts = {
                        chart: { height: 350, type: 'area', toolbar: { show: false } },
                        dataLabels: { enabled: false },
                        stroke: { width: 2, curve: 'smooth' },
                        series: [
                            { name: '{{ __("Purchase") }}', data: {!! json_encode($purchasesArray['value']) !!} },
                            { name: '{{ __("POS") }}', data: {!! json_encode($posesArray['value']) !!} }
                        ],
                        xaxis: { categories: {!! json_encode($purchasesArray['label']) !!}, title: { text: '{{ __("Days") }}' } },
                        colors: ['#ff3a6e', '#6fd943'],
                        grid: { strokeDashArray: 4 },
                        legend: { show: false },
                        yaxis: { title: { text: '{{ __("Amount") }}' } }
                    };
                    new ApexCharts(chartEl, opts).render();
                } catch (e) {
                    errorMessage = getMsg(e.message, document.querySelector('#traffic-chart') || document.body);
                }
            });
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('POS')}}</li>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CL6 }} {{ VC::CM12 }} dashboard-card">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD }}">
                    <div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::JCB }}">
                        <div class="{{ VC::C_AT }} {{ VC::MB3 }} mb-sm-0">
                            <div class="{{ VC::DFL_AIC }}">
                                <div class="theme-avatar {{ VC::BG_P }}">
                                    <i class="ti ti-hand-finger"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="{{ VC::TXT_MT }}">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('POS Of This Month') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="{{ VC::C_AT }} text-end">
                            <h4 class="m-0">{{ $pos_data['monthlyPosAmount'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="{{ VC::CL6 }} {{ VC::CM12 }} dashboard-card">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD }}">
                    <div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::JCB }}">
                        <div class="{{ VC::C_AT }} {{ VC::MB3 }} mb-sm-0">
                            <div class="{{ VC::DFL_AIC }}">
                                <div class="theme-avatar bg-warning">
                                    <i class="ti ti-chart-pie"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="{{ VC::TXT_MT }}">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('POS Amount') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="{{ VC::C_AT }} text-end">
                            <h4 class="m-0">{{ $pos_data['totalPosAmount'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="{{ VC::CL6 }} {{ VC::CM12 }} dashboard-card">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD }}">
                    <div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::JCB }}">
                        <div class="{{ VC::C_AT }} {{ VC::MB3 }} mb-sm-0">
                            <div class="{{ VC::DFL_AIC }}">
                                <div class="theme-avatar bg-info">
                                    <i class="{{ VC::TI_RPT_MN }}"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="{{ VC::TXT_MT }}">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Purchase Of This Month') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="{{ VC::C_AT }} text-end">
                            <h4 class="m-0">{{ $pos_data['monthlyPurchaseAmount'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="{{ VC::CL6 }} {{ VC::CM12 }} dashboard-card">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD }}">
                    <div class="{{ VC::RW }} {{ VC::ALC }} {{ VC::JCB }}">
                        <div class="{{ VC::C_AT }} {{ VC::MB3 }} mb-sm-0">
                            <div class="{{ VC::DFL_AIC }}">
                                <div class="theme-avatar bg-info">
                                    <i class="ti ti-chart-bar"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="{{ VC::TXT_MT }}">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __(' Purchase Amount') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="{{ VC::C_AT }} text-end">
                            <h4 class="m-0">{{ $pos_data['totalPurchaseAmount'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_HD }}">
                    <div class="{{ VC::RW }}">
                        <div class="{{ VC::C6 }}">
                            <h5>{{ __('Purchase Vs POS Report') }}</h5>
                        </div>
                        <div class="{{ VC::C6 }} {{ VC::TX_END }}">
                            <h6>{{ __('Last 10 Days') }}</h6>
                        </div>
                    </div>
                </div>
                <div class="{{ VC::CD_BD }}">
                    <div id="traffic-chart"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
