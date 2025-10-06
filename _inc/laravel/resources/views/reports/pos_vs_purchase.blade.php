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
    {{__('POS Vs Purchase')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('POS Vs Purchase')}}</li>
@endsection

@push('theme-script')
    <script src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
@endpush

@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/reports/posVsPurchase/lang/pdf.js') }}"></script>
    <script async>
    (function () {
        const $ = window.jQuery;
        const qs = (s, r = document) => r.querySelector(s);
        const errFb = "# ERROR";
        const dataClientLocalized = "data-client-localized";
        const dataGuardMsg = "data-guard-msg";
        const dataSvLocalized = "data-sv-localized";
        const dataErrGuard = "data-error-guard";
        const dataChartGuard = "data-chart-guard";
        const year = '{{$currentYear}}';
        window.currentYear = year;

        const ensureToastContainer = () => {
        const id = "np-toast-container";
        let c = qs("#" + id);
        if (c) { return c; }
        c = document.createElement("div");
        c.id = id;
        c.setAttribute("aria-live", "polite");
        c.setAttribute("aria-atomic", "true");
        c.style.position = "fixed";
        c.style.top = "1rem";
        c.style.right = "1rem";
        document.body.appendChild(c);
        return c;
        };

        const showErrorNow = (message) => {
        const hasBootstrap = (qs('link[rel="stylesheet"][href*="bootstrap"]') || qs('link[href*="bootstrap"]')) && window.bootstrap && window.bootstrap.Toast;
        if (hasBootstrap) {
            const container = ensureToastContainer();
            let t = qs("#np-toast", container);
            if (!t) {
            t = document.createElement("div");
            t.id = "np-toast";
            t.className = "toast";
            t.setAttribute("role", "alert");
            t.setAttribute("aria-live", "assertive");
            t.setAttribute("aria-atomic", "true");
            t.innerHTML = '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
            container.appendChild(t);
            }
            const body = qs(".toast-body", t);
            if (body) { body.textContent = message ?? errFb; }
            try { new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show(); } catch (_) { alert(message ?? errFb); }
        } else { alert(message ?? errFb); }
        };

        const scheduleInteractiveError = (message, usePointerUp) => {
        const host = document.body;
        if (!host || host.getAttribute(dataErrGuard) === "true") { return; }
        host.setAttribute(dataErrGuard, "true");
        const evt = usePointerUp ? "pointerup" : "click";
        const once = () => { try { showErrorNow(message); } finally { host.removeAttribute(dataErrGuard); } };
        document.addEventListener(evt, once, { once: true });
        const mo = new MutationObserver((m, o) => { if (!document.body.contains(host)) { document.removeEventListener(evt, once); o.disconnect(); } });
        mo.observe(document.documentElement, { childList: true, subtree: true });
        };

        const getMsg = (el, key) => {
        let msg = errFb;
        if (el?.getAttribute(dataSvLocalized) === "true" || el?.getAttribute(dataClientLocalized) === "true") { msg = el.getAttribute(dataGuardMsg) || errFb; }
        else {
            let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en").toLowerCase().replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            const msgKey = key;
            msg = window.translations?.[lang]?.[msgKey] || el?.getAttribute(dataGuardMsg) || window.translations?.["en"]?.[msgKey] || errFb;
            if (el && msg !== errFb) { el.setAttribute(dataGuardMsg, msg); el.setAttribute(dataClientLocalized, "true"); }
        }
        return msg;
        };

        const renderProfitChart = () => {
        const target = qs("#pos-vs-purchase");
        if (!target || target.getAttribute(dataChartGuard) === "true") { return; }
        target.setAttribute(dataChartGuard, "true");
        try {
            if (!window.ApexCharts) { try { 
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("ApexCharts unavailable");
             } catch (_) {} scheduleInteractiveError(getMsg(target, "plugin_unavailable"), false); return; }
            const chartBarOptions = {
            series: [{ name: '{{ __("Profit") }}', data: {!! json_encode($profits) !!} }],
            chart: { height: 300, type: 'area', dropShadow: { enabled: true, color: '#000', top: 18, left: 7, blur: 10, opacity: 0.2 }, toolbar: { show: false } },
            dataLabels: { enabled: false },
            stroke: { width: 2, curve: 'smooth' },
            title: { text: '', align: 'left' },
            xaxis: { categories: {!! json_encode($monthList) !!}, title: { text: '{{ __("Months") }}' } },
            colors: ['#ffa21d', '#FF3A6E'],
            grid: { strokeDashArray: 4 },
            legend: { show: false },
            yaxis: { title: { text: '{{ __("Profit") }}' } }
            };
            const inst = new window.ApexCharts(target, chartBarOptions);
            inst.render().catch(function () { scheduleInteractiveError(getMsg(target, "chart_unavailable"), false); });
            const mo = new MutationObserver((m, o) => { if (!document.body.contains(target)) { try { inst.destroy(); } catch (_) {} o.disconnect(); } });
            mo.observe(document.body, { childList: true, subtree: true });
        } catch (_) { scheduleInteractiveError(getMsg(target, "chart_unavailable"), false); }
        };

        const saveAsPDF = () => {
        const area = document.getElementById("printableArea");
        if (!area) { scheduleInteractiveError(getMsg(document.body, "pdf_unavailable"), true); return; }
        const name = ((window.jQuery && $("#filename").val()) ?? "").toString().trim() || "export";
        const opt = { margin: 0.3, filename: name, image: { type: "jpeg", quality: 1 }, html2canvas: { scale: 4, dpi: 72, letterRendering: true }, jsPDF: { unit: "in", format: "A2" } };
        try {
            if (typeof window.html2pdf !== "function") { try { 
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("html2pdf unavailable");
             } catch (_) {} scheduleInteractiveError(getMsg(area, "plugin_unavailable"), true); return; }
            window.html2pdf().set(opt).from(area).save();
        } catch (_) { scheduleInteractiveError(getMsg(area, "pdf_unavailable"), true); }
        };

        window.saveAsPDF = saveAsPDF;

        const init = () => { renderProfitChart(); };
        if (document.readyState === "loading") { document.addEventListener("DOMContentLoaded", init, { once: true }); } else { init(); }
    })();
    </script>
@endpush

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @php
            $downloadLabelPvp = __('Download');
            $downloadGuardMsgPvp = Utility::fetchLinkMessage(
                $lang,
                VW::RPT,
                'download_pos_vs_purchase_report_unavailable'
            ) ?? 'Download function for POS vs Purchase report is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a href="#"
        class="{{ VC::BT_SM_PM }} download-pos-vs-purchase"
        data-func-name="saveAsPDF"
        data-guard-msg="{{ $downloadGuardMsgPvp }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ $downloadLabelPvp }}"
        aria-label="{{ $downloadLabelPvp }}"
        data-original-title="{{ $downloadLabelPvp }}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/posVsPurchase/download.js') }}" defer></script>
        @endpush
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        @php
                            $routeNameBase = VW::RPT.'.pos.vs.purchase';
                            $routeNameKebab = Str::kebab($routeNameBase);
                            $routeResolved = Route::has($routeNameBase) ? $routeNameBase : (Route::has($routeNameKebab) ? $routeNameKebab : null);
                            $actionRoute = $routeResolved ? [$routeResolved] : ['#'];
                            $actionUrl = $routeResolved ? route($routeResolved) : '#';
                            $guardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'pos_vs_purchase_route_unavailable') ?? 'POS vs Purchase route is unavailable. Please contact technical support or your domain administrator.';
                            $formId = 'pos_vs_purchase';
                            $applyId = 'apply-pos-vs-purchase-'.uniqid();
                            $resetId = 'reset-pos-vs-purchase-'.uniqid();
                        @endphp
                        {{ Form::open(['route' => $actionRoute,'method' => 'GET','id' => $formId,'data-url' => $actionUrl,'data-sv-localized' => 'true','data-guard-msg' => $guardMsg]) }}
                            <div class="{{ VC::R_ALC_JCE }}">
                                <div class="col-xl-10">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::CL_XL3 }}"><div class="btn-box"></div></div>
                                        <div class="{{ VC::CL_XL3 }}"><div class="btn-box"></div></div>
                                        <div class="{{ VC::CL_XL3 }}"><div class="btn-box"></div></div>
                                        <div class="{{ VC::CL_XL3 }}">
                                            <div class="btn-box">
                                                {{ Form::label('year', __('Year'), ['class' => 'form-label']) }}
                                                {{ Form::select('year', $yearList ?? [], request('year',''), ['class' => "{{ VC::FM_CT_SL }}", 'placeholder' => __('Select Year')]) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::C_AT }}">
                                            <a id="{{ $applyId }}"
                                            href="#"
                                            class="{{ VC::BT_SM_PM }} apply-pos-vs-purchase"
                                            data-form-id="{{ $formId }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ $guardMsg }}"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}"
                                            data-original-title="{{ __('apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>
                                            <a id="{{ $resetId }}"
                                            href="{{ $actionUrl }}"
                                            class="{{ VC::BT_SM_DG }} reset-pos-vs-purchase"
                                            data-url="{{ $actionUrl }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ $guardMsg }}"
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
                            <script defer>
                                (() => {
                                    try {
                                        const form = document.getElementById('{{ $formId }}');
                                        if (!form) { return; }
                                        const apply = document.getElementById('{{ $applyId }}');
                                        const reset = document.getElementById('{{ $resetId }}');
                                        if (apply && apply.getAttribute('data-listener-active') !== 'true') {
                                            apply.setAttribute('data-listener-active', 'true');
                                            apply.addEventListener('click', (e) => {
                                                try {
                                                    const url = form.getAttribute('data-url') ?? '#';
                                                    if (url === '#') {
                                                        e.preventDefault();
                                                        const msg = apply.getAttribute('data-guard-msg') ?? 'POS vs Purchase route is unavailable. Please contact technical support or your domain administrator.';
                                                        const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                                        let container = document.getElementById('toast-container');
                                                        if (!container) {
                                                            container = document.createElement('div');
                                                            container.id = 'toast-container';
                                                            document.body.appendChild(container);
                                                        }
                                                        if (hasBootstrap) {
                                                            const toast = document.createElement('div');
                                                            toast.className = 'toast';
                                                            toast.setAttribute('role', 'alert');
                                                            toast.setAttribute('aria-live', 'assertive');
                                                            toast.setAttribute('aria-atomic', 'true');
                                                            const body = document.createElement('div');
                                                            body.className = 'toast-body';
                                                            body.textContent = msg;
                                                            toast.appendChild(body);
                                                            container.appendChild(toast);
                                                            bootstrap.Toast.getOrCreateInstance(toast).show();
                                                        } else {
                                                            alert(msg);
                                                        }
                                                        apply.setAttribute('data-failed-route', 'true');
                                                        return;
                                                    }
                                                    e.preventDefault();
                                                    form.submit();
                                                } catch (err) {}
                                            });
                                        }
                                        if (reset && reset.getAttribute('data-listener-active') !== 'true') {
                                            reset.setAttribute('data-listener-active', 'true');
                                            reset.addEventListener('click', (e) => {
                                                try {
                                                    const href = reset.getAttribute('href') ?? '#';
                                                    const url = reset.getAttribute('data-url') ?? href ?? '#';
                                                    if (url !== '#' && href !== '#') { return; }
                                                    e.preventDefault();
                                                    const msg = reset.getAttribute('data-guard-msg') ?? 'POS vs Purchase route is unavailable. Please contact technical support or your domain administrator.';
                                                    const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (hasBootstrap) {
                                                        const toast = document.createElement('div');
                                                        toast.className = 'toast';
                                                        toast.setAttribute('role', 'alert');
                                                        toast.setAttribute('aria-live', 'assertive');
                                                        toast.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toast.appendChild(body);
                                                        container.appendChild(toast);
                                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    reset.setAttribute('data-failed-route', 'true');
                                                } catch (err) {}
                                            });
                                        }
                                    } catch (err) {}
                                })();
                            </script>
                        @endpush
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="printableArea">
        @php
            $start = data_get($filter ?? [], 'startDateRange');
            $end   = data_get($filter ?? [], 'endDateRange');
            $startLabel = $start ?: __('No start date available');
            $endLabel   = $end   ?: __('No end date available');
        @endphp
        <div class="{{ VC::RW }} mt-0">
            <div class="col">
                <input type="hidden" value="{{ __('POS Vs Purchase') . ' ' . __('Report of') . ' ' . $startLabel . ' ' . __('to') . ' ' . $endLabel }}" id="filename">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Report') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ __('POS Vs Purchase') }}</h6>
                </div>
            </div>
            <div class="col">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Duration') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ $startLabel . ' ' . __('to') . ' ' . $endLabel }}</h6>
                </div>
            </div>
        </div>

        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}" id="chart-container">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        <div class="scrollbar-inner">
                            <div id="pos-vs-purchase" data-color="primary" data-height="300"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $months = $monthList ?? [];
            $posArr = $posTotal ?? [];
            $purArr = $purchaseTotal ?? [];
            $profitsArr = $profits ?? [];
        @endphp
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    @if(empty($months))
                        <div class="p-3">{{ __('Could not find months for the selected period') }}</div>
                    @else
                        <div class="table-responsive">
                            <table class="{{ VC::TB }}">
                                <thead>
                                    <tr>
                                        <th>{{ __('Type') }}</th>
                                        @foreach($months as $month)
                                            <th>{{ $month }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ __('POS') }}</td>
                                        @foreach($months as $i => $m)
                                            @php $val = $posArr[$i] ?? 0; @endphp
                                            <td>{{ $user?->priceFormat($val) }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>{{ __('Purchase') }}</td>
                                        @foreach($months as $i => $m)
                                            @php $val = $purArr[$i] ?? 0; @endphp
                                            <td>{{ $user?->priceFormat($val) }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td colspan="{{ 1 + count($months) }}" class="text-dark">
                                            <span>{{ __('Profit = POS - Purchase') }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><h6>{{ __('Profit') }}</h6></td>
                                        @foreach($months as $i => $m)
                                            @php
                                                $raw = $profitsArr[$i] ?? 0;
                                                $num = is_numeric($raw) ? $raw : (float)preg_replace('/[^\d.\-]/','', (string)$raw);
                                            @endphp
                                            <td>{{ $user?->priceFormat($num) }}</td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

