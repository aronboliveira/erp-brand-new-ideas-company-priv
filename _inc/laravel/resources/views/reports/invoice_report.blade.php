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
    use Illuminate\Support\Facades\{Auth, Crypt, Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(auth: $user);
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
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/reports/invoices/lang/chart.js') }}"></script>
    <script async>
        (function () {
            const $ = window.jQuery;
            const qs = (s, r = document) => r.querySelector(s);
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const dataSvLocalized = "data-sv-localized";
            const dataErrGuard = "data-error-guard";
            const dataChartGuard = "data-invoice-chart-bound";

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
            const once = () => {
                try { showErrorNow(message); } finally { host.removeAttribute(dataErrGuard); }
            };
            document.addEventListener("click", once, { once: true });
            const mo = new MutationObserver((m, o) => {
                if (!document.body.contains(host)) {
                document.removeEventListener("click", once);
                o.disconnect();
                }
            });
            mo.observe(document.documentElement, { childList: true, subtree: true });
            };

            const getMsg = (el, key) => {
            const dataClientLocalizedL = dataClientLocalized;
            const dataGuardMsgL = dataGuardMsg;
            let msg = errFb;
            if (el?.getAttribute(dataSvLocalized) === "true" || el?.getAttribute(dataClientLocalizedL) === "true") {
                msg = el.getAttribute(dataGuardMsgL) || errFb;
            } else {
                let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en")
                .toLowerCase()
                .replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                msg = window.translations?.[lang]?.[key] || el?.getAttribute(dataGuardMsgL) || window.translations?.["en"]?.[key] || errFb;
                if (el && msg !== errFb) {
                el.setAttribute(dataGuardMsgL, msg);
                el.setAttribute(dataClientLocalizedL, "true");
                }
            }
            return msg;
            };

            const renderInvoiceChart = () => {
            const target = qs("#chart-sales");
            if (!target || target.getAttribute(dataChartGuard) === "true") { return; }
            target.setAttribute(dataChartGuard, "true");
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
                const chartBarOptions = {
                series: [{ name: '{{ __("Invoice") }}', data: {!! json_encode($invoiceTotal) !!} }],
                chart: { height: 300, type: "bar", dropShadow: { enabled: true, color: "#000", top: 18, left: 7, blur: 10, opacity: 0.2 }, toolbar: { show: false } },
                dataLabels: { enabled: false },
                stroke: { width: 2, curve: "smooth" },
                title: { text: "", align: "left" },
                xaxis: { categories: {!! json_encode($monthList) !!}, title: { text: '{{ __("Months") }}' } },
                colors: ["#6fd944", "#6fd944"],
                grid: { strokeDashArray: 4 },
                legend: { show: false },
                yaxis: { title: { text: '{{ __("Invoice") }}' } }
                };
                target.innerHTML = "";
                const arChart = new window.ApexCharts(target, chartBarOptions);
                arChart.render();
            } catch (_) {
                scheduleInteractiveError(getMsg(target, "chart_unavailable"));
            }
            };

            const exportPDF = () => {
            const area = document.getElementById("printableArea");
            if (!area) {
                scheduleInteractiveError(getMsg(document.body, "pdf_unavailable"));
                return;
            }
            const name = (($ && $("#filename").val()) ?? "").toString().trim() || "export";
            const opt = { margin: 0.3, filename: name, image: { type: "jpeg", quality: 1 }, html2canvas: { scale: 4, dpi: 72, letterRendering: true }, jsPDF: { unit: "in", format: "A2" } };
            try {
                if (typeof window.html2pdf !== "function") {
                try { 
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("html2pdf unavailable");
                 } catch (_) {}
                scheduleInteractiveError(getMsg(area, "plugin_unavailable"));
                return;
                }
                window.html2pdf().set(opt).from(area).save();
            } catch (_) {
                scheduleInteractiveError(getMsg(area, "pdf_unavailable"));
            }
            };

            const initDataTable = () => {
            const $table = $("#report-dataTable");
            if (!$table.length) { return; }
            if (!$.fn || !$.fn.DataTable) {
                try { 
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("DataTables unavailable");
                } catch (_) {}
                scheduleInteractiveError(getMsg($table.get(0), "plugin_unavailable"));
                return;
            }
            const title = (($ && $("#filename").val()) ?? "").toString().trim() || "export";
            const hasButtons = $.fn.dataTable && $.fn.dataTable.Buttons;
            const opts = hasButtons
                ? { dom: "lBfrtip", buttons: [{ extend: "excel", title }, { extend: "pdf", title }, { extend: "csv", title }] }
                : {};
            if (!hasButtons) { scheduleInteractiveError(getMsg($table.get(0), "datatable_unavailable")); }
            try { $table.DataTable(opts); } catch (_) { scheduleInteractiveError(getMsg($table.get(0), "datatable_unavailable")); }
            };

            const init = () => {
            renderInvoiceChart();
            window.saveAsPDF = exportPDF;
            if ($) { $(function () { initDataTable(); }); }
            };

            if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", init, { once: true });
            } else {
            init();
            }
        })();
    </script>
@endpush
{{--        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
{{--            <i class="ti ti-filter"></i>--}}
{{--        </a>--}}

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @php
            $downloadGuardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'download_invoice_reports_unavailable') ?? 'Download function for invoice reports is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a href="#"
        id="download-invoice-reports-link"
        class="{{ VC::BT_SM_PM }} download-invoice-reports"
        data-func-name="saveAsPDF"
        data-guard-msg="{{ $downloadGuardMsg }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ __('Download') }}"
        data-original-title="{{ __('Download') }}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/invoices/download.js') }}" defer></script>
        @endpush
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        @php
                            $invoiceSummaryBase        = VW::RPT.'.invoice.summary';
                            $invoiceSummaryKebab       = Str::kebab($invoiceSummaryBase);
                            $invoiceSummaryResolved    = Route::has($invoiceSummaryBase) ? $invoiceSummaryBase : (Route::has($invoiceSummaryKebab) ? $invoiceSummaryKebab : null);
                            $invoiceSummaryUrl         = $invoiceSummaryResolved ? route($invoiceSummaryResolved) : '#';
                            $invoiceSummaryGuardMsg    = Utility::fetchLinkMessage($lang, VW::RPT, 'invoice_summary_report_route_unavailable') ?? 'Invoice summary report route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        {{ Form::open([
                            'method'            => 'GET',
                            'url'               => $invoiceSummaryUrl,
                            'id'                => 'report_invoice_summary',
                            'data-url'          => $invoiceSummaryUrl,
                            'data-guard-msg'    => $invoiceSummaryGuardMsg,
                            'data-sv-localized' => 'true',
                        ]) }}
                            <div class="{{ VC::R_ALC_JCE }}">
                                <div class="col-xl-10">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box">
                                                {{ Form::label('start_month', __('Start Month'), ['class'=> VC::FM_LB]) }}
                                                {{ Form::month('start_month', request('start_month', date('Y-m', strtotime('-5 month'))), ['class'=> VC::FM_CT . ' month-btn']) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box">
                                                {{ Form::label('end_month', __('End Month'), ['class'=> VC::FM_LB]) }}
                                                {{ Form::month('end_month', request('end_month', date('Y-m')), ['class'=> VC::FM_CT . ' month-btn']) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box">
                                                {{ Form::label('customer', __('Customer'), ['class'=> VC::FM_LB]) }}
                                                {{ Form::select('customer', $customer ?? [], request('customer',''), ['class' => VC::FM_CT_SL, 'placeholder'=>__('No customers available')]) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box">
                                                {{ Form::label('status', __('Status'), ['class'=> VC::FM_LB]) }}
                                                {{ Form::select('status', [''=>__('Select Status')]+($status ?? []), request('status',''), ['class' => VC::FM_CT_SL]) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                            <a href="#"
                                            class="{{ VC::BT_SM_PM }} apply-invoice-summary"
                                            data-form-id="report_invoice_summary"
                                            data-guard-msg="{{ $invoiceSummaryGuardMsg }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}"
                                            data-original-title="{{ __('apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>
                                            <a href="{{ $invoiceSummaryUrl }}"
                                            class="{{ VC::BT_SM_DG }} reset-invoice-summary"
                                            data-url="{{ $invoiceSummaryUrl }}"
                                            data-guard-msg="{{ $invoiceSummaryGuardMsg }}"
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
                            <script src="{{ asset('assets/js/routes/reports/invoice/summaries/apply.js') }}" defer></script>
                            <script src="{{ asset('assets/js/routes/reports/invoice/summaries/reset.js') }}" defer></script>
                        @endpush
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="printableArea">
        @php
            $fltStatus   = data_get($filter,'status');
            $fltCust     = data_get($filter,'customer');
            $fltStart    = data_get($filter,'startDateRange');
            $fltEnd      = data_get($filter,'endDateRange');

            $statusText  = $fltStatus ?: __('Could not find status');
            $custText    = $fltCust   ?: __('Could not find customer');
            $startText   = $fltStart  ?: __('Could not find start date');
            $endText     = $fltEnd    ?: __('Could not find end date');

            $filename = "{$statusText} " . __('Invoice') . " " . __('Report of') . " {$startText} " . __('to') . " {$endText} " . __('of') . " {$custText}";

            $items = [
                ['label'=> __('Report'),   'value'=> __('Invoice Summary'),                           'when'=> true],
                ['label'=> __('Customer'), 'value'=> $custText,                                       'when'=> $fltCust && $fltCust != __('All')],
                ['label'=> __('Status'),   'value'=> $statusText,                                     'when'=> $fltStatus && $fltStatus != __('All')],
                ['label'=> __('Duration'), 'value'=> "{$startText} " . __('to') . " {$endText}",      'when'=> true],
            ];

            $stats = [
                ['label' => __('Total Invoice'), 'value' => $totalInvoice ?? 0],
                ['label' => __('Total Paid'),    'value' => $totalPaidInvoice ?? 0],
                ['label' => __('Total Due'),     'value' => $totalDueInvoice ?? 0],
            ];
        @endphp
        <input type="hidden" id="filename" value="{{ $filename }}">
        <div class="{{ VC::RW }} {{ VC::MT3 }}">
            @foreach($items as $item)
                @if($item['when'])
                    <div class="col">
                        <div class="{{ VC::CD_POS }}">
                            <h7 class="{{ VC::RPT_TX_GR }}">{{ $item['label'] }} :</h7>
                            <h6 class="{{ VC::RPT_TX_DEF }}">{{ $item['value'] }}</h6>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
        <div class="{{ VC::RW }}">
            @foreach($stats as $stat)
                <div class="col-xl-4 col-md-6 col-lg-4">
                    <div class="{{ VC::CD_POS }}">
                        <h7 class="{{ VC::RPT_TX_GR }}">{{ $stat['label'] }}</h7>
                        <h6 class="{{ VC::RPT_TX_DEF }}">{{ $user?->priceFormat($stat['value']) ?? number_format((float)$stat['value'],2) }}</h6>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}" id="invoice-container">
                <div class="{{ VC::CD }}">
                    <div class="card-header">
                        <div class="{{ VC::DFL_JCB }} w-100">
                            <ul class="{{ VC::NAV_PL_Y3 }}" id="pills-tab" role="tablist">
                                <li class="{{ VC::NV_IT }}">
                                    <a class="{{ VC::NV_LK }} active" id="profile-tab3" data-bs-toggle="pill" href="#summary" role="tab" aria-controls="pills-summary" aria-selected="true">{{__('Summary')}}</a>
                                </li>
                                <li class="{{ VC::NV_IT }}">
                                    <a class="{{ VC::NV_LK }}" id="contact-tab4" data-bs-toggle="pill" href="#invoices" role="tab" aria-controls="pills-invoice" aria-selected="false">{{__('Invoices')}}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::CS12 }}">
                                <div class="tab-content" id="myTabContent2">
                                    <div class="tab-pane fade fade" id="invoices" role="tabpanel" aria-labelledby="profile-tab3">
                                        <table class="{{ VC::TB }} table-flush" id="report-dataTable">
                                            <thead>
                                                <tr>
                                                    <th>{{__('Invoice')}}</th>
                                                    <th>{{__('Date')}}</th>
                                                    <th>{{__('Customer')}}</th>
                                                    <th>{{__('Category')}}</th>
                                                    <th>{{__('Status')}}</th>
                                                    <th>{{__('Paid Amount')}}</th>
                                                    <th>{{__('Due Amount')}}</th>
                                                    <th>{{__('Payment Date')}}</th>
                                                    <th>{{__('Amount')}}</th>
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
                                                @forelse($invoices as $invoice)
                                                    @php
                                                        $status = $invoice->status;
                                                        $badgeClass = $statusClasses[$status] ?? 'bg-secondary';
                                                        $custName = optional($invoice->customer)->name ?? __('No customer available');
                                                        $catName  = optional($invoice->category)->name ?? __('No category available');
                                                        $payDate  = optional($invoice->lastPayments)->date ? ($user?->dateFormat($invoice->lastPayments->date)) : __('No payment date available');
                                                    @endphp
                                                    <tr>
                                                        <td class="Id">
                                                            <a href="{{ route(VW::INV . '.show', Crypt::encrypt($invoice->id)) }}" class="{{ VC::BT_OUTPM }}">
                                                                {{ $user?->invoiceNumberFormat($invoice->invoice_id) ?? __('Could not find invoice number') }}
                                                            </a>
                                                        </td>
                                                        <td>{{ $user?->dateFormat($invoice->send_date) ?? __('Could not find date') }}</td>
                                                        <td>{{ $custName }}</td>
                                                        <td>{{ $catName }}</td>
                                                        <td>
                                                            <span class="{{ VC::BDG }} status_badge {{ $badgeClass }} p-2 px-3 rounded">
                                                                {{ __(\App\Models\Invoice::$statuses[$status] ?? __('Unknown')) }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $user?->priceFormat($invoice->getTotal() - $invoice->getDue()) ?? number_format((float)($invoice->getTotal() - $invoice->getDue()),2) }}</td>
                                                        <td>{{ $user?->priceFormat($invoice->getDue()) ?? number_format((float)$invoice->getDue(),2) }}</td>
                                                        <td>{{ $payDate }}</td>
                                                        <td>{{ $user?->priceFormat($invoice->getTotal()) ?? number_format((float)$invoice->getTotal(),2) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="9" class="text-center text-muted">{{ __('No invoices available for the selected filters') }}</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="tab-pane fade fade show active" id="summary" role="tabpanel" aria-labelledby="profile-tab3">
                                        <div class="{{ VC::CS12 }}">
                                            <div class="scrollbar-inner">
                                                <div id="chart-sales" data-color="primary" data-type="bar" data-height="300"></div>
                                            </div>
                                            @if(empty($invoices) || count($invoices) === 0)
                                                <div class="text-center text-muted mt-3">{{ __('No summary data available for the selected filters') }}</div>
                                            @endif
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
