@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Warehouse Report')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Warehouse Report') }}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/reports/warehouses/lang/pdf.js') }}">
    </script>
    <script defer>
        (function () {
            const $ = window.jQuery;
            const qs = (s, r = document) => r.querySelector(s);
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const dataSvLocalized = "data-sv-localized";
            const dataErrGuard = "data-error-guard";
            if (!$) { try { console.error("jQuery unavailable"); } catch (_) {} scheduleInteractiveError(getMsg(document.body, "plugin_unavailable")); return; }
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
                const tid = "np-toast";
                let t = qs("#" + tid, container);
                if (!t) {
                t = document.createElement("div");
                t.id = tid;
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
            function scheduleInteractiveError(message) {
            const host = document.body;
            if (!host || host.getAttribute(dataErrGuard) === "true") { return; }
            host.setAttribute(dataErrGuard, "true");
            const once = () => { try { showErrorNow(message); } finally { host.removeAttribute(dataErrGuard); } };
            document.addEventListener("click", once, { once: true });
            const mo = new MutationObserver((m, o) => { if (!document.body.contains(host)) { document.removeEventListener("click", once); o.disconnect(); } });
            mo.observe(document.documentElement, { childList: true, subtree: true });
            }
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
            const exportPDF = () => {
            const area = document.getElementById("printableArea");
            if (!area) { scheduleInteractiveError(getMsg(document.body, "pdf_unavailable")); return; }
            const name = ($("#filename").val() ?? "").toString().trim() || "export";
            const opt = { margin: 0.3, filename: name, image: { type: "jpeg", quality: 1 }, html2canvas: { scale: 4, dpi: 72, letterRendering: true }, jsPDF: { unit: "in", format: "A2" } };
            try {
                if (typeof window.html2pdf !== "function") { try { console.error("html2pdf unavailable"); } catch (_) {} scheduleInteractiveError(getMsg(area, "plugin_unavailable")); return; }
                window.html2pdf().set(opt).from(area).save();
            } catch (_) { scheduleInteractiveError(getMsg(area, "pdf_unavailable")); }
            };
            window.saveAsPDF = exportPDF;
            const initChart = () => {
            const container = qs("#warehouse_report");
            if (!container) { return; }
            if (typeof window.ApexCharts !== "function") { try { console.error("ApexCharts unavailable"); } catch (_) {} scheduleInteractiveError(getMsg(container, "plugin_unavailable")); return; }
            try {
                const chartBarOptions = {
                series: [{ name: '{{ __("Product") }}', data: {!! json_encode($warehouseProductData) !!} }],
                chart: { height: 300, type: "area", dropShadow: { enabled: true, color: "#000", top: 18, left: 7, blur: 10, opacity: 0.2 }, toolbar: { show: false } },
                dataLabels: { enabled: false },
                stroke: { width: 2, curve: "smooth" },
                title: { text: "", align: "left" },
                xaxis: { categories: {!! json_encode($warehousename) !!}, title: { text: '{{ __("Warehouse") }}' } },
                colors: ["#6fd944"],
                grid: { strokeDashArray: 4 },
                legend: { show: false }
                };
                const arChart = new window.ApexCharts(container, chartBarOptions);
                arChart.render();
            } catch (_) { scheduleInteractiveError(getMsg(container, "chart_unavailable")); }
            };
            const init = () => { initChart(); };
            if (document.readyState === "loading") { document.addEventListener("DOMContentLoaded", init, { once: true }); }
            else { init(); }
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        <button type="button"
                class="{{ VC::BT_SM_PM }}"
                onclick="saveAsPDF()"
                data-bs-toggle="tooltip"
                title="{{ __('Download') }}"
                aria-label="{{ __('Download') }}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </button>
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div id="printableArea">
        <div class="{{ VC::RW }} {{ VC::MT3 }}">
            <div class="{{ VC::CLMS4_12 }}">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Report') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ __('Warehouse Report') }}</h6>
                </div>
            </div>
            <div class="{{ VC::CLMS4_12 }}">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Total Warehouse') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ (int) ($totalWarehouse ?? 0) }}</h6>
                </div>
            </div>
            <div class="{{ VC::CLMS4_12 }}">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Total Product') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ (int) ($totalProduct ?? 0) }}</h6>
                </div>
            </div>
        </div>

        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-header">
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::C6 }}">
                                <h6 class="{{ VC::MB0 }}">{{ __('Warehouse Report') }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="warehouse_report" role="img" aria-label="{{ __('Warehouse Report Chart') }}"></div>
                        <div id="warehouse_report_empty" class="text-center text-muted {{ VC::MT3 }}" hidden>
                            {{ __('No data to display.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
