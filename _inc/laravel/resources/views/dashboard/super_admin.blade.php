@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants
    };
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
            const errFb = '# ERROR';
            const dataClientLocalized = 'data-client-localized';
            const dataGuardMsg = 'data-guard-msg';
            const langSessionKey = 'erp-np-lang';
            const getLocalizedMessage = (msgKey, el) => {
                let msg = errFb;
                if (
                el.getAttribute('data-sv-localized') === 'true' ||
                el.getAttribute(dataClientLocalized) === 'true'
                ) {
                msg = el.getAttribute(dataGuardMsg) ?? errFb;
                } else {
                let lang = (
                    window.sessionStorage.getItem(langSessionKey) ??
                    document.documentElement.lang ??
                    'en'
                )
                    .toLowerCase()
                    .replace(/_/g, '-');
                lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
                msg =
                    window.translations?.[lang]?.[msgKey] ??
                    el.getAttribute(dataGuardMsg) ??
                    window.translations?.['en']?.[msgKey] ??
                    errFb;
                if (msg !== errFb) {
                    el.setAttribute(dataGuardMsg, msg);
                    el.setAttribute(dataClientLocalized, 'true');
                }
                }
                return msg;
            };
            const showError = message => {
                try {
                let container = document.querySelector('#bootstrap-toast-container');
                if (!container) {
                    const hasBs =
                    Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
                        .some(l => /bootstrap/i.test(l.href)) &&
                    window.bootstrap?.Toast;
                    if (hasBs) {
                    container = document.createElement('div');
                    container.id = 'bootstrap-toast-container';
                    container.setAttribute('aria-live', 'polite');
                    container.setAttribute('aria-atomic', 'true');
                    document.body.appendChild(container);
                    }
                }
                if (container && window.bootstrap.Toast) {
                    let toast = container.querySelector('.toast');
                    if (!toast) {
                    toast = document.createElement('div');
                    toast.className = 'toast';
                    toast.setAttribute('role', 'alert');
                    toast.setAttribute('aria-live', 'assertive');
                    toast.setAttribute('aria-atomic', 'true');
                    const body = document.createElement('div');
                    body.className = 'toast-body';
                    toast.appendChild(body);
                    container.appendChild(toast);
                    if (toast.getAttribute('data-click-listener') !== 'true') {
                        toast.addEventListener('click', () => body.textContent = message);
                        toast.setAttribute('data-click-listener', 'true');
                    }
                    }
                    toast.querySelector('.toast-body').textContent = message;
                    new bootstrap.Toast(toast).show();
                } else {
                    alert(message);
                }
                } catch {
                alert(message);
                }
            };
            let errorMessage = '';
            const onErrorPointerUp = () => {
                if (errorMessage) {
                showError(errorMessage);
                errorMessage = '';
                }
            };
            document.addEventListener('pointerup', onErrorPointerUp);
            new MutationObserver((mutations, obs) => {
                for (const m of mutations) {
                for (const n of m.removedNodes) {
                    if (n === document.documentElement) {
                    document.removeEventListener('pointerup', onErrorPointerUp);
                    obs.disconnect();
                    }
                }
                }
            }).observe(document.body, { childList: true, subtree: true });
            
            try {
                const mountEl = document.querySelector('#chart-sales');
                if (!mountEl) throw new Error();
                if (!window.ApexCharts) {
                console.log('ApexCharts library missing');
                return;
                }
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
                const chart = new ApexCharts(mountEl, chartOptions);
                chart.render().catch(() => {
                errorMessage = getLocalizedMessage('chart_render_failed', mountEl);
                });
            } catch {
                errorMessage = getLocalizedMessage('chart_render_failed', document.body);
            }
        })();
    </script>
@endpush
@php
    $admin_payment_setting = Utility::getAdminPaymentSetting();
    $cards=[
        ['bg'=>'bg-primary','icon'=>ViewClassNamesConstants::TI_USRS,'header'=>__('Total Users'),'value'=>$user?->total_user,'sub'=>__('Paid Users'),'subValue'=>$user['total_paid_user']],
        ['bg'=>'bg-warning','icon'=>'ti ti-shopping-cart','header'=>__('Total Orders'),'value'=>$user?->totalOrders,'sub'=>__('Total Order Amount'),'subValue'=>(isset($admin_payment_setting['currency_symbol'])?$admin_payment_setting['currency_symbol']:'$').$user['totalOrders_price']],
        ['bg'=>'bg-info','icon'=>'ti ti-trophy','header'=>__('Total Plans'),'value'=>$user?->total_plan,'sub'=>__('Most Purchase Plan'),'subValue'=>$user['mostPurchasedPlan']]
    ];
@endphp
@section('content')
    <div class="row">
        @php
            $cards=[
                ['bg'=>ViewClassNamesConstants::BG_P,'icon'=>ViewClassNamesConstants::TI_USRS,'header'=>__('Total Users'),'value'=>$user?->total_user,'sub'=>__('Paid Users'),'subValue'=>$user['total_paid_user']],
                ['bg'=>'bg-warning','icon'=>'ti ti-shopping-cart','header'=>__('Total Orders'),'value'=>$user?->totalOrders,'sub'=>__('Total Order Amount'),'subValue'=>($admin_payment_setting['currency_symbol'] ?? '$').$user['totalOrders_price']],
                ['bg'=>ViewClassNamesConstants::BG_TPR,'icon'=>'ti ti-trophy','header'=>__('Total Plans'),'value'=>$user?->total_plan,'sub'=>__('Most Purchase Plan'),'subValue'=>$user['mostPurchasedPlan']]
            ];
        @endphp
        @foreach($cards as $c)
            <div class="col-lg-4 col-md-6">
                <div class="{{ ViewClassNamesConstants::CD }}">
                    <div class="card-body p-3">
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
