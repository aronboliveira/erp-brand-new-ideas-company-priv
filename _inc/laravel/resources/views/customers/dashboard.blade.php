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
    <script async src="{{ asset('assets/js/routes/customers/lang/dashboard.js') }}"></script>
    <script async>
        (function () {
        const errFb = "# ERROR";
        const dataClientLocalized = "data-client-localized";
        const dataGuardMsg = "data-guard-msg";
        const dataSvLocalized = "data-sv-localized";
        const dataErrArmed = "data-chart-error-armed";
        const dataChartBound = "data-chart-bound";
        const qs = (s, r = document) => r.querySelector(s);
        const hasBS = () =>
            !!(
            qs('link[rel="stylesheet"][href*="bootstrap"]') ||
            qs('link[href*="bootstrap"]')
            ) && !!(window.bootstrap && window.bootstrap.Toast);
        const ensureToastContainer = () => {
            let c = qs("#np-toast-container");
            if (c) return c;
            c = document.createElement("div");
            c.id = "np-toast-container";
            c.setAttribute("aria-live", "polite");
            c.setAttribute("aria-atomic", "true");
            c.style.position = "fixed";
            c.style.top = "1rem";
            c.style.right = "1rem";
            document.body.appendChild(c);
            return c;
        };
        const showErrorNow = message => {
            if (hasBS()) {
            const container = ensureToastContainer();
            let t = qs("#np-toast", container);
            if (!t) {
                t = document.createElement("div");
                t.id = "np-toast";
                t.className = "toast";
                t.setAttribute("role", "alert");
                t.setAttribute("aria-live", "assertive");
                t.setAttribute("aria-atomic", "true");
                t.innerHTML =
                '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
                container.appendChild(t);
            }
            const body = t.querySelector(".toast-body");
            if (body) body.textContent = message ?? errFb;
            try {
                new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
            } catch (_) {
                alert(message ?? errFb);
            }
            } else {
            alert(message ?? errFb);
            }
        };
        const schedulePointerupError = msg => {
            const host = document.body;
            if (!host || host.getAttribute(dataErrArmed) === "true") return;
            host.setAttribute(dataErrArmed, "true");
            const once = () => {
            try {
                showErrorNow(msg);
            } finally {
                host.removeAttribute(dataErrArmed);
            }
            };
            document.addEventListener("pointerup", once, { once: true });
            const mo = new MutationObserver((m, o) => {
            if (!document.body.contains(host)) {
                document.removeEventListener("pointerup", once);
                o.disconnect();
            }
            });
            mo.observe(document.documentElement, { childList: true, subtree: true });
        };
        const localize = (el, key) => {
            let msg = errFb;
            if (
            el?.getAttribute?.(dataSvLocalized) === "true" ||
            el?.getAttribute?.(dataClientLocalized) === "true"
            ) {
            msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
            let lang = (
                window.sessionStorage.getItem("erp-np-lang") ||
                document.documentElement.lang ||
                "en"
            )
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            const msgKey = key;
            msg =
                window.translations?.[lang]?.[msgKey] ||
                el?.getAttribute?.(dataGuardMsg) ||
                window.translations?.["en"]?.[msgKey] ||
                errFb;
            if (msg !== errFb && el) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
            }
            return msg;
        };
        const renderChart = () => {
            const body = document.body;
            if (body.getAttribute(dataChartBound) === "true") return;
            body.setAttribute(dataChartBound, "true");
            const container = qs("#chart-sales");
            if (!container) {
            schedulePointerupError(localize(body, "container_unavailable"));
            return;
            }
            if (!window.ApexCharts) {
            try {
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("ApexCharts unavailable");
            } catch (_) {}
            schedulePointerupError(localize(body, "plugin_unavailable"));
            return;
            }
            if (container.querySelector(".apexcharts-canvas")) return;
            let options;
            try{options={series:[{name:"{{__('Unpaid')}}",data:{!! json_encode($invoiceChartData['data']['unpaid']) !!}},{name:"{{__('Paid')}}",data:{!! json_encode($invoiceChartData['data']['paid']) !!}},{name:"{{__('Partial Paid')}}",data:{!! json_encode($invoiceChartData['data']['partial']) !!}},{name:"{{__('Due')}}",data:{!! json_encode($invoiceChartData['data']['due']) !!}}],
            chart:{height:350,type:"line",dropShadow:{enabled:true,color:"#000",top:18,left:7,blur:10,opacity:0.2},toolbar:{show:false}},colors:["#FF5630","#36B37E","#00B8D9","#FFAB00"],dataLabels:{enabled:true},stroke:{curve:"smooth"},title:{text:"",align:"left"},grid:{borderColor:"#e7e7e7",row:{colors:["#f3f3f3","transparent"],opacity:0.5}},markers:{size:1},xaxis:{categories:{!! json_encode($invoiceChartData['month']) !!},title:{text:"Month"}},yaxis:{title:{text:"{{__('Amount')}}"}},legend:{position:"top",horizontalAlign:"right",floating:true,offsetY:-25,offsetX:-5}};}catch(_)
            {
            schedulePointerupError(localize(body, "chart_unavailable"));
            return;
            }
            try {
            const chart = new window.ApexCharts(container, options);
            chart.render().catch(function () {
                schedulePointerupError(localize(body, "chart_unavailable"));
            });
            const mo = new MutationObserver(function () {
                if (!document.body.contains(container)) {
                try {
                    chart.destroy();
                } catch (_) {}
                body.removeAttribute(dataChartBound);
                }
            });
            mo.observe(document.documentElement, { childList: true, subtree: true });
            } catch (_) {
            schedulePointerupError(localize(body, "chart_unavailable"));
            }
        };
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", renderChart, { once: true });
        } else {
            renderChart();
        }
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_CTT)
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


