@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        StacksConstants,
        YieldingConstants,
    };
    use App\Models\{Invoice, Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Crypt,Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Bill Summary')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Bill Summary')}}</li>
@endsection
@push('theme-script')
    <script src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/reports/bills/lang/chart.js') }}">
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
            const dataInitGuard = "data-analytics-init-bound";
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
            const initChart = () => {
            const container = qs("#chart-sales");
            if (!container) { return; }
            if (typeof window.ApexCharts !== "function") { try { console.error("ApexCharts unavailable"); } catch (_) {} scheduleInteractiveError(getMsg(container, "plugin_unavailable")); return; }
            try {
                const chartBarOptions = {
                series: [{ name: '{{ __("Bill") }}', data: {!! json_encode($billTotal) !!} }],
                chart: { height: 300, type: "bar", dropShadow: { enabled: true, color: "#000", top: 18, left: 7, blur: 10, opacity: 0.2 }, toolbar: { show: false } },
                dataLabels: { enabled: false },
                stroke: { width: 2, curve: "smooth" },
                title: { text: "", align: "left" },
                xaxis: { categories: {!! json_encode($monthList) !!}, title: { text: '{{ __("Months") }}' } },
                colors: ["#6fd944", "#6fd944"],
                grid: { strokeDashArray: 4 },
                legend: { show: false },
                yaxis: { title: { text: '{{ __("Bill") }}' } }
                };
                const arChart = new window.ApexCharts(container, chartBarOptions);
                arChart.render();
            } catch (_) { scheduleInteractiveError(getMsg(container, "chart_unavailable")); }
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
            const initDataTable = () => {
            const table = $("#report-dataTable");
            if (!table.length) { return; }
            if (!$.fn.DataTable) { try { console.error("DataTables unavailable"); } catch (_) {} scheduleInteractiveError(getMsg(table.get(0), "plugin_unavailable")); return; }
            const filename = ($("#filename").val() ?? "").toString().trim() || "export";
            let useButtons = true;
            if (!$.fn.dataTable || !$.fn.DataTable.Buttons) { useButtons = false; try { console.error("DataTables Buttons unavailable"); } catch (_) {} scheduleInteractiveError(getMsg(table.get(0), "datatable_unavailable")); }
            try {
                table.DataTable(useButtons ? {
                dom: "lBfrtip",
                buttons: [{ extend: "excel", title: filename }, { extend: "pdf", title: filename }, { extend: "csv", title: filename }]
                } : {});
            } catch (_) { scheduleInteractiveError(getMsg(table.get(0), "datatable_unavailable")); }
            };
            const initOnce = () => {
            const root = document.documentElement;
            if (root.getAttribute(dataInitGuard) === "true") { return; }
            root.setAttribute(dataInitGuard, "true");
            initChart();
            $(function () { initDataTable(); });
            };
            if (document.readyState === "loading") { document.addEventListener("DOMContentLoaded", initOnce, { once: true }); }
            else { initOnce(); }
        })();
    </script>
@endpush
{{--        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
{{--            <i class="ti ti-filter"></i>--}}
{{--        </a>--}}
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        <a href="#" class="{{ VC::BT_SM_PM }}" onclick="saveAsPDF()" data-bs-toggle="tooltip" title="{{__('Download')}}" data-original-title="{{__('Download')}}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </a>
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        {{ Form::open(['route' => [VW::RPT.'.bill.summary'], 'method' => 'GET', 'id' => 'report_bill_summary']) }}
                        <div class="{{ VC::R_ALC_JCE }}">
                            <div class="col-xl-10">
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::CXL3 }} {{ VC::CL3 }} {{ VC::CM6 }} {{ VC::CS12 }} {{ VC::C12 }}">
                                        <div class="btn-box">
                                            {{ Form::label('start_month', __('Start Month'), ['class'=> VC::FM_LB]) }}
                                            {{ Form::month('start_month', data_get($_GET ?? [], 'start_month', date('Y-m', strtotime('-5 month'))), ['class' => 'month-btn ' . VC::FM_CT]) }}
                                        </div>
                                    </div>
                                    <div class="{{ VC::CXL3 }} {{ VC::CL3 }} {{ VC::CM6 }} {{ VC::CS12 }} {{ VC::C12 }}">
                                        <div class="btn-box">
                                            {{ Form::label('end_month', __('End Month'), ['class'=> VC::FM_LB]) }}
                                            {{ Form::month('end_month', data_get($_GET ?? [], 'end_month', date('Y-m')), ['class' => 'month-btn ' . VC::FM_CT]) }}
                                        </div>
                                    </div>
                                    <div class="{{ VC::CXL3 }} {{ VC::CL3 }} {{ VC::CM6 }} {{ VC::CS12 }} {{ VC::C12 }}">
                                        <div class="btn-box">
                                            {{ Form::label('vendor', __('Vendor'), ['class'=> VC::FM_LB]) }}
                                            {{ Form::select('vendor', (is_array($vendor ?? null) ? $vendor : []), data_get($_GET ?? [], 'vendor', ''), ['class' => VC::FM_CT_SL]) }}
                                        </div>
                                    </div>
                                    <div class="{{ VC::CXL3 }} {{ VC::CL3 }} {{ VC::CM6 }} {{ VC::CS12 }} {{ VC::C12 }}">
                                        <div class="btn-box">
                                            {{ Form::label('status', __('Status'), ['class'=> VC::FM_LB]) }}
                                            {{ Form::select('status', ['' => __('Select Status')] + (is_array($status ?? null) ? $status : []), data_get($_GET ?? [], 'status', ''), ['class' => VC::FM_CT_SL]) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::C_AT }}">
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                        <a href="#" class="{{ VC::BT_SM_PM }}" onclick="document.getElementById('report_bill_summary').submit(); return false;" data-bs-toggle="tooltip" title="{{ __('Apply') }}" data-original-title="{{ __('apply') }}"><span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span></a>
                                        <a href="{{ route(VW::RPT.'.bill.summary') }}" class="{{ VC::BT_SM_DG }}" data-bs-toggle="tooltip" title="{{ __('Reset') }}" data-original-title="{{ __('Reset') }}"><span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="printableArea">
        <div class="{{ VC::RW }} {{ VC::MT3 }}">
            <div class="{{ VC::CLMS4_12 }}">
                <input type="hidden" value="{{ (data_get($filter ?? [], 'status', __('All')) . ' ' . __('Bill') . ' ' . __('Report of') . ' ' . (data_get($filter ?? [], 'startDateRange') ?? __('No start date available')) . ' ' . __('to') . ' ' . (data_get($filter ?? [], 'endDateRange') ?? __('No end date available')) . ' ' . __('of') . ' ' . (data_get($filter ?? [], 'vendor') ?? __('No vendor available'))) }}" id="filename">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Report') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ __('Bill Summary') }}</h6>
                </div>
            </div>
            @if((data_get($filter ?? [], 'vendor') ?? __('All')) != __('All'))
                <div class="{{ VC::CLMS4_12 }}">
                    <div class="{{ VC::CD_POS }}">
                        <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Vendor') }} :</h7>
                        <h6 class="{{ VC::RPT_TX_DEF }}">{{ data_get($filter ?? [], 'vendor') ?? __('No vendor available') }}</h6>
                    </div>
                </div>
            @endif
            @if((data_get($filter ?? [], 'status') ?? __('All')) != __('All'))
                <div class="{{ VC::CLMS4_12 }}">
                    <div class="{{ VC::CD_POS }}">
                        <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Status') }} :</h7>
                        <h6 class="{{ VC::RPT_TX_DEF }}">{{ data_get($filter ?? [], 'status') ?? __('No status available') }}</h6>
                    </div>
                </div>
            @endif
            <div class="{{ VC::CLMS4_12 }}">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Duration') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ (data_get($filter ?? [], 'startDateRange') ?? __('No start date available')) . ' ' . __('to') . ' ' . (data_get($filter ?? [], 'endDateRange') ?? __('No end date available')) }}</h6>
                </div>
            </div>
        </div>
        <div class="{{ VC::RW }}">
            <div class="{{ VC::CLMS4_12 }}">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Total Bill') }}</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ $user?->priceFormat($totalBill ?? 0) ?? number_format((float)($totalBill ?? 0),2) }}</h6>
                </div>
            </div>
            <div class="{{ VC::CLMS4_12 }}">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Total Paid') }}</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ $user?->priceFormat($totalPaidBill ?? 0) ?? number_format((float)($totalPaidBill ?? 0),2) }}</h6>
                </div>
            </div>
            <div class="{{ VC::CLMS4_12 }}">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Total Due') }}</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ $user?->priceFormat($totalDueBill ?? 0) ?? number_format((float)($totalDueBill ?? 0),2) }}</h6>
                </div>
            </div>
        </div>
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}" id="bill-container">
                <div class="{{ VC::CD }}">
                    <div class="card-header">
                        <div class="{{ VC::DFL_JCB }} w-100">
                            <ul class="{{ VC::NAV_PL }} {{ VC::MB3 }}" id="pills-tab" role="tablist">
                                <li class="{{ VC::NV_IT }}"><a class="{{ VC::NV_LK }} active" id="profile-tab3" data-bs-toggle="pill" href="#summary" role="tab" aria-controls="pills-summary" aria-selected="true">{{ __('Summary') }}</a></li>
                                <li class="{{ VC::NV_IT }}"><a class="{{ VC::NV_LK }}" id="contact-tab4" data-bs-toggle="pill" href="#bills" role="tab" aria-controls="pills-invoice" aria-selected="false">{{ __('Bills') }}</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="{{ VC::RW }}">
                            <div class="{{ VC::CS12 }}">
                                <div class="tab-content" id="myTabContent2">
                                    <div class="tab-pane fade fade" id="bills" role="tabpanel" aria-labelledby="profile-tab3">
                                        <table class="{{ VC::TB }} table-flush" id="report-dataTable">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Bill') }}</th>
                                                    <th>{{ __('Date') }}</th>
                                                    <th>{{ __('Customer') }}</th>
                                                    <th>{{ __('Category') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    <th>{{ __('Paid Amount') }}</th>
                                                    <th>{{ __('Due Amount') }}</th>
                                                    <th>{{ __('Payment Date') }}</th>
                                                    <th>{{ __('Amount') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach (is_iterable($bills ?? null) ? $bills : [] as $bill)
                                                    @php
                                                        $bid = data_get($bill,'id');
                                                        $bstatus = (int) data_get($bill,'status',-1);
                                                    @endphp
                                                    <tr>
                                                        <td class="Id"><a href="{{ route(VW::BIL.'.show', Crypt::encrypt($bid)) }}" class="{{ VC::BT_OUTPM }}">{{ $user?->billNumberFormat(data_get($bill,'bill_id')) ?? __('Failed to get bill number') }}</a></td>
                                                        <td>{{ $user?->dateFormat(data_get($bill,'send_date')) ?? __('Failed to get date') }}</td>
                                                        <td>{{ data_get($bill,'vendor.name') ?? __('No vendor available') }}</td>
                                                        <td>{{ data_get($bill,'category.name') ?? __('No category available') }}</td>
                                                        @php
                                                            $statusClasses = [0=>'bg-primary',1=>'bg-warning',2=>'bg-danger',3=>'bg-info',4=>'bg-success'];
                                                        @endphp
                                                        <td><span class="{{ VC::BDG }} {{ $statusClasses[$bstatus] ?? 'bg-secondary' }} p-2 {{ VC::PX3 }} rounded">{{ __(\App\Models\Bill::$statuses[$bstatus] ?? __('Unknown status')) }}</span></td>
                                                        <td>{{ $user?->priceFormat((data_get($bill,'getTotal') ? $bill->getTotal() : 0) - (data_get($bill,'getDue') ? $bill->getDue() : 0)) ?? __('Failed to get paid amount') }}</td>
                                                        <td>{{ $user?->priceFormat(data_get($bill,'getDue') ? $bill->getDue() : 0) ?? __('Failed to get due amount') }}</td>
                                                        <td>{{ data_get($bill,'lastPayments.date') ? ($user?->dateFormat(data_get($bill,'lastPayments.date')) ?? __('Failed to get payment date')) : '-' }}</td>
                                                        <td>{{ $user?->priceFormat(data_get($bill,'getTotal') ? $bill->getTotal() : 0) ?? __('Failed to get amount') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="tab-pane fade fade show active" id="summary" role="tabpanel" aria-labelledby="profile-tab3">
                                        <div class="scrollbar-inner">
                                            <div id="chart-sales" data-color="primary" data-type="bar" data-height="300"></div>
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

