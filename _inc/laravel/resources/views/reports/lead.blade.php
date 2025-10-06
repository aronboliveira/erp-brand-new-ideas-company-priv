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
    $lang = Utility::fetchUserLang(auth: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Lead')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Lead Report')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @php
            $downloadGuardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'download_leads_reports_unavailable') ?? 'Download function for leads reports is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a href="#"
        id="download-leads-reports-link"
        class="{{ VC::BT_SM_PM }} download-leads-reports"
        data-func-name="saveAsPDF"
        data-guard-msg="{{ $downloadGuardMsg }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ __('Download') }}"
        data-original-title="{{ __('Download') }}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/leads/download.js') }}" defer></script>
        @endpush
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}" id="printableArea">
        <div class="{{ VC::CS12 }}">
            <input type="hidden" value="{{ __('Lead Report') }}" id="filename">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::CXL3 }}">
                    @php
                        $reportSections = [
                            ['id' => 'general-report',  'label' => __('General Report')],
                            ['id' => 'staff-report',    'label' => __('Staff Report')],
                            ['id' => 'pipeline-report', 'label' => __('Pipelines Report')],
                        ];
                    @endphp
                    <div class="{{ VC::CD_STK }}" style="top:30px">
                        <div class="{{ VC::LG_FLSH }}" id="useradd-sidenav">
                            @forelse($reportSections as $section)
                                <a href="#{{ $section['id'] }}" class="{{ VC::LGI_ACT_NBD }}">
                                    {{ $section['label'] }}
                                    <div class="{{ VC::FEND }}">
                                        <i class="{{ VC::TI_CHV_RT }}"></i>
                                    </div>
                                </a>
                            @empty
                                <div class="px-3 py-2 text-muted">{{ __('No report sections available') }}</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div id="general-report">
                        <div class="{{ VC::CD }}">
                            <div class="card-header">
                                <h5>{{ __('This Week Leads Conversions') }}</h5>
                            </div>
                            <div class="card-body pt-0">
                                <div id="leads-this-week" data-color="primary" data-height="280"></div>
                                @php $hasWeekLeads = !empty($thisWeekLeads ?? []); @endphp
                                @unless($hasWeekLeads)
                                    <div class="text-center text-muted mt-3">{{ __('No weekly lead conversion data available') }}</div>
                                @endunless
                            </div>
                        </div>
                        <div class="{{ VC::CD }}">
                            <div class="card-header">
                                <h5>{{ __('Sources Conversion') }}</h5>
                            </div>
                            <div class="card-body pt-0">
                                <div class="leads-sources-report" id="leads-sources-report" data-color="primary" data-height="280"></div>
                                @php $hasSourceData = !empty($leadSourcesData ?? []); @endphp
                                @unless($hasSourceData)
                                    <div class="text-center text-muted mt-3">{{ __('No lead source data available') }}</div>
                                @endunless
                            </div>
                        </div>
                        <div class="{{ VC::CD }}">
                            <div class="card-header">
                                <div class="{{ VC::RW }}">
                                    <div class="col-9">
                                        <h5>{{ __('Monthly') }}</h5>
                                    </div>
                                    <div class="col-3 {{ VC::FEND }}">
                                        <select name="month" class="{{ VC::FM_CT }} selectpicker" id="selectmonth" data-none-selected-text="{{ __('Nothing selected') }}">
                                            <option value=" ">{{ __('Select Month') }}</option>
                                            <option value="1">{{ __('January') }}</option>
                                            <option value="2">{{ __('February') }}</option>
                                            <option value="3">{{ __('March') }}</option>
                                            <option value="4">{{ __('April') }}</option>
                                            <option value="5">{{ __('May') }}</option>
                                            <option value="6">{{ __('June') }}</option>
                                            <option value="7">{{ __('July') }}</option>
                                            <option value="8">{{ __('August') }}</option>
                                            <option value="9">{{ __('September') }}</option>
                                            <option value="10">{{ __('October') }}</option>
                                            <option value="11">{{ __('November') }}</option>
                                            <option value="12">{{ __('December') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="{{ VC::MT3 }}">
                                    <div id="leads-monthly" data-color="primary" data-height="280"></div>
                                    @php $hasMonthly = !empty($leadsMonthlyData ?? []); @endphp
                                    @unless($hasMonthly)
                                        <div class="text-center text-muted mt-3">{{ __('No monthly lead data available') }}</div>
                                    @endunless
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="staff-report" class="{{ VC::CD }}">
                        <div class="card-header">
                            <h5>{{ __('Staff Report') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="{{ VC::RW }}">
                                <div class="col-md-4">
                                    {{ Form::label('From Date', __('From Date'), ['class' => 'col-form-label']) }}
                                    {{ Form::date('From Date', null, ['class' => VC::FM_CT . ' from_date', 'id' => 'data_picker1', 'placeholder' => __('Select from date')]) }}
                                    <span id="fromDate" class="d-block mt-1" style="color: red;"></span>
                                </div>
                                <div class="col-md-4">
                                    {{ Form::label('To Date', __('To Date'), ['class' => 'col-form-label']) }}
                                    {{ Form::date('To Date', null, ['class' => VC::FM_CT . ' to_date', 'id' => 'data_picker2', 'placeholder' => __('Select to date')]) }}
                                    <span id="toDate" class="d-block mt-1" style="color: red;"></span>
                                </div>
                                <div class="col-md-4" id="filter_type" style="padding-top:38px;">
                                    <button class="{{ VC::BT_PM }} label-margin generate_button" type="button">{{ __('Generate') }}</button>
                                </div>
                            </div>
                            <div id="leads-staff-report" class="{{ VC::MT3 }}" data-color="primary" data-height="280"></div>
                            @php $hasStaffData = !empty($leadsStaffData ?? []); @endphp
                            @unless($hasStaffData)
                                <div class="text-center text-muted mt-3">{{ __('No staff lead data available for the selected period') }}</div>
                            @endunless
                        </div>
                    </div>

                    <div id="pipeline-report" class="{{ VC::CD }}">
                        <div class="card-header">
                            <h5>{{ __('Pipeline Report') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="{{ VC::RW }}">
                                <div id="leads-piplines-report" data-color="primary" data-height="280"></div>
                                @php $hasPipeline = !empty($leadsPipelinesData ?? []); @endphp
                                @unless($hasPipeline)
                                    <div class="text-center text-muted mt-3 w-100">{{ __('No pipeline data available') }}</div>
                                @endunless
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script src="{{asset('assets/js/plugins/apexcharts.min.js')}}"></script>
    <script async src="{{ asset('assets/js/routes/reports/leads/lang/pdf.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/reports/leads/pdf.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/reports/leads/lang/chart.js') }}"></script>
    <script async>
        (function () {
            const $ = window.jQuery;
            const qs = (s, r = document) => r.querySelector(s);
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const dataSvLocalized = "data-sv-localized";
            const dataErrGuard = "data-error-guard";
            const dataListenerGuard = "data-leads-guard";

            if (!$) { 
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("jQuery unavailable");
            } catch (_) {} }

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
            } else {
                alert(message ?? errFb);
            }
            };

            const scheduleInteractiveError = (message) => {
            const host = document.body;
            if (!host || host.getAttribute(dataErrGuard) === "true") { return; }
            host.setAttribute(dataErrGuard, "true");
            const once = () => { try { showErrorNow(message); } finally { host.removeAttribute(dataErrGuard); } };
            document.addEventListener("click", once, { once: true });
            const mo = new MutationObserver((m, o) => { if (!document.body.contains(host)) { document.removeEventListener("click", once); o.disconnect(); } });
            mo.observe(document.documentElement, { childList: true, subtree: true });
            };

            const getMsg = (el, key) => {
            let msg = errFb;
            if (el?.getAttribute(dataSvLocalized) === "true" || el?.getAttribute(dataClientLocalized) === "true") {
                msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
                let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en")
                .toLowerCase()
                .replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                msg = window.translations?.[lang]?.[key] || el?.getAttribute(dataGuardMsg) || window.translations?.["en"]?.[key] || errFb;
                if (el && msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
                }
            }
            return msg;
            };

            const routeOrFail = (url, el) => {
            if (!url || url === "#") {
                scheduleInteractiveError(getMsg(el || document.body, "endpoint_unavailable"));
                return null;
            }
            return url;
            };

            const bindWithObserver = (el, evt, handler, flag) => {
            if (!el || el.getAttribute(flag) === "true") { return; }
            el.setAttribute(flag, "true");
            $(el).on(evt, handler);
            const mo = new MutationObserver((m, o) => {
                if (!document.body.contains(el)) {
                $(el).off(evt, handler);
                o.disconnect();
                }
            });
            mo.observe(document.body, { childList: true, subtree: true });
            };

            const renderBarChart = (selector, options) => {
            const target = qs(selector);
            if (!target) { return; }
            if (typeof window.ApexCharts !== "function") {
                try { 
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("ApexCharts unavailable");
                } catch (_) {}
                scheduleInteractiveError(getMsg(target, "plugin_unavailable"));
                return;
            }
            try {
                target.innerHTML = "";
                const chart = new window.ApexCharts(target, options);
                chart.render();
            } catch (_) {
                scheduleInteractiveError(getMsg(target, "chart_unavailable"));
            }
            };

            const renderPieChart = (selector, options) => {
            const target = qs(selector);
            if (!target) { return; }
            if (typeof window.ApexCharts !== "function") {
                try {
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("ApexCharts unavailable");
                } catch (_) {}
                scheduleInteractiveError(getMsg(target, "plugin_unavailable"));
                return;
            }
            try {
                target.innerHTML = "";
                const chart = new window.ApexCharts(target, options);
                chart.render();
            } catch (_) {
                scheduleInteractiveError(getMsg(target, "chart_unavailable"));
            }
            };

            const onGenerateStaff = function () {
            const from_date = $(".from_date").val();
            const to_date = $(".to_date").val();
            if (from_date === "") { $("#fromDate").text("Please select date"); } else { $("#fromDate").empty(); }
            if (to_date === "") { $("#toDate").text("Please select date"); } else { $("#toDate").empty(); }
            const url = routeOrFail("{{ route(VW::RPT . '.lead') }}", this);
            if (!url) { return; }
            $.ajax({
                url: url,
                type: "get",
                data: { From_Date: from_date, To_Date: to_date, type: "staff_repport", _token: "{{ csrf_token() }}" },
                cache: false,
                success: function (data) {
                $("#leads-staff-report").empty();
                renderBarChart("#leads-staff-report", {
                    series: [{ name: "Lead", data: data?.data ?? [] }],
                    chart: { height: 300, type: "bar", dropShadow: { enabled: true, color: "#000", top: 18, left: 7, blur: 10, opacity: 0.2 }, toolbar: { show: false } },
                    dataLabels: { enabled: false },
                    stroke: { width: 2, curve: "smooth" },
                    title: { text: "", align: "left" },
                    xaxis: { categories: data?.name ?? [] },
                    colors: ["#6fd944", "#6fd944"],
                    grid: { strokeDashArray: 4 },
                    legend: { show: false }
                });
                },
                error: function () { scheduleInteractiveError(getMsg(document.body, "endpoint_unavailable")); }
            });
            };

            const onSelectMonthChange = function () {
            const start_month = $(".selectpicker").val();
            const url = routeOrFail("{{route(VW::RPT . '.lead')}}", this);
            if (!url) { return; }
            $.ajax({
                url: url,
                type: "get",
                data: { start_month: start_month, _token: "{{ csrf_token() }}" },
                cache: false,
                success: function (data) {
                $("#leads-monthly").empty();
                renderBarChart("#leads-monthly", {
                    series: [{ name: "Lead", data: data?.data ?? [] }],
                    chart: { height: 300, type: "bar", dropShadow: { enabled: true, color: "#000", top: 18, left: 7, blur: 10, opacity: 0.2 }, toolbar: { show: false } },
                    dataLabels: { enabled: false },
                    stroke: { width: 2, curve: "smooth" },
                    title: { text: "", align: "left" },
                    xaxis: { categories: data?.name ?? [], title: { text: '{{ __("Lead Per Month") }}' } },
                    colors: ["#6fd944", "#6fd944"],
                    grid: { strokeDashArray: 4 },
                    legend: { show: false }
                });
                },
                error: function () { scheduleInteractiveError(getMsg(document.body, "endpoint_unavailable")); }
            });
            };

            const onLangTabClick = function () {
            const $nav = $(".lang-tab .nav-link");
            const $panes = $(".tab-pane");
            $nav.removeClass("active");
            $panes.removeClass("active");
            $(this).addClass("active");
            const id = $(this).attr("data-href");
            if (!id) { scheduleInteractiveError(getMsg(document.body, "tabs_unavailable")); return; }
            $(id).addClass("active");
            };

            const initBindings = () => {
            document.querySelectorAll(".generate_button").forEach((el) => bindWithObserver(el, "click", onGenerateStaff, dataListenerGuard + "-gen"));
            bindWithObserver(document.getElementById("selectmonth"), "change", onSelectMonthChange, dataListenerGuard + "-month");
            document.querySelectorAll(".lang-tab .nav-link").forEach((el) => bindWithObserver(el, "click", onLangTabClick, dataListenerGuard + "-tab"));
            };

            const initStaticCharts = () => {
            renderPieChart("#leads-this-week", {
                series: {!! json_encode($devicearray['data']) !!},
                chart: { width: 350, type: "pie" },
                colors: ["#35abb6","#ffa21d","#ff3a6e","#6fd943","#5c636a","#181e28","#0288d1"],
                labels: {!! json_encode($devicearray['label']) !!},
                responsive: [{ breakpoint: 480, options: { chart: { width: 200 }, legend: { position: "bottom" } } }]
            });
            renderBarChart("#leads-sources-report", {
                series: [{ name: "Source", data: {!! json_encode($leadsourceeData) !!} }],
                chart: { height: 300, type: "bar", dropShadow: { enabled: true, color: "#000", top: 18, left: 7, blur: 10, opacity: 0.2 }, toolbar: { show: false } },
                dataLabels: { enabled: false },
                stroke: { width: 2, curve: "smooth" },
                title: { text: "", align: "left" },
                xaxis: { categories: {!! json_encode($leadsourceName) !!}, title: { text: '{{ __("Source") }}' } },
                colors: ["#ffa21d", "#ffa21d"],
                grid: { strokeDashArray: 4 },
                legend: { show: false }
            });
            renderBarChart("#leads-monthly", {
                series: [{ name: "Lead", data: {!! json_encode($data) !!} }],
                chart: { height: 300, type: "bar", dropShadow: { enabled: true, color: "#000", top: 18, left: 7, blur: 10, opacity: 0.2 }, toolbar: { show: false } },
                dataLabels: { enabled: false },
                stroke: { width: 2, curve: "smooth" },
                title: { text: "", align: "left" },
                xaxis: { categories: {!! json_encode($labels) !!}, title: { text: '{{ __("Lead Per Month") }}' } },
                colors: ["#6fd944", "#6fd944"],
                grid: { strokeDashArray: 4 },
                legend: { show: false }
            });
            renderBarChart("#leads-staff-report", {
                series: [{ name: "Lead", data: {!! json_encode($leadusereData) !!} }],
                chart: { height: 300, type: "bar", dropShadow: { enabled: true, color: "#000", top: 18, left: 7, blur: 10, opacity: 0.2 }, toolbar: { show: false } },
                dataLabels: { enabled: false },
                stroke: { width: 2, curve: "smooth" },
                title: { text: "", align: "left" },
                xaxis: { categories: {!! json_encode($leaduserName) !!}, title: { text: '{{ __("User") }}' } },
                colors: ["#6fd944", "#6fd944"],
                grid: { strokeDashArray: 4 },
                legend: { show: false }
            });
            renderBarChart("#leads-piplines-report", {
                series: [{ name: "Pipeline", data: {!! json_encode($leadpipelineeData) !!} }],
                chart: { height: 300, type: "bar", dropShadow: { enabled: true, color: "#000", top: 18, left: 7, blur: 10, opacity: 0.2 }, toolbar: { show: false } },
                dataLabels: { enabled: false },
                stroke: { width: 2, curve: "smooth" },
                title: { text: "", align: "left" },
                xaxis: { categories: {!! json_encode($leadpipelineName) !!}, title: { text: '{{ __("Pipelines") }}' } },
                yaxis: { title: { text: '{{ __("Leads") }}' } },
                colors: ["#6fd944", "#6fd944"],
                grid: { strokeDashArray: 4 },
                legend: { show: false }
            });
            };

            const init = () => {
            initBindings();
            initStaticCharts();
            };

            if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", init, { once: true });
            } else {
            init();
            }
        })();
    </script>
@endpush

