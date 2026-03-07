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
    {{__('Expense Summary')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Expense Summary')}}</li>
@endsection

@push('theme-script')
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/reports/expenses/summaries/lang/chart.js') }}"></script>
    <script async>
        (function () {
            const $ = window.jQuery;
            const qs = (s, r = document) => r.querySelector(s);
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const dataErrGuard = "data-error-guard";
            const dataChartGuard = "data-expense-chart-bound";
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
                t.innerHTML = '<div class="toast-header"><strong class="me-auto">{{ __('Notice') }}</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div><div class="toast-body"></div>';
                container.appendChild(t);
                }
                const body = qs(".toast-body", t);
                if (body) { body.textContent = message ?? errFb; }
                try { new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show(); } catch (_) { alert(message ?? errFb); }
            } else { alert(message ?? errFb); }
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
            if (el?.getAttribute("data-sv-localized") === "true" || el?.getAttribute(dataClientLocalized) === "true") { msg = el.getAttribute(dataGuardMsg) || errFb; }
            else {
                let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en").toLowerCase().replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                msg = window.translations?.[lang]?.[key] || el?.getAttribute(dataGuardMsg) || window.translations?.["en"]?.[key] || errFb;
                if (el && msg !== errFb) { el.setAttribute(dataGuardMsg, msg); el.setAttribute(dataClientLocalized, "true"); }
            }
            return msg;
            };
            const renderExpenseChart = () => {
            const target = qs("#chart-sales");
            if (!target || target.getAttribute(dataChartGuard) === "true") { return; }
            target.setAttribute(dataChartGuard, "true");
            if (typeof window.ApexCharts !== "function") { try { 
                if (
                    window.location.hostname === "localhost" ||
                    window.location.hostname === "127.0.0.1"
                ) console.error("ApexCharts unavailable");
             } catch (_) {} scheduleInteractiveError(getMsg(target, "plugin_unavailable")); return; }
            try {
                const chartBarOptions = {
                series: [{ name: '{{ __("Expense") }}', data: {!! json_encode($chartExpenseArr) !!} }],
                chart: { height: 300, type: "area", dropShadow: { enabled: true, color: "#000", top: 18, left: 7, blur: 10, opacity: 0.2 }, toolbar: { show: false } },
                dataLabels: { enabled: false },
                stroke: { width: 2, curve: "smooth" },
                title: { text: "", align: "left" },
                xaxis: { categories: {!! json_encode($monthList) !!}, title: { text: '{{ __("Months") }}' } },
                colors: ["#6fd944", "#6fd944"],
                grid: { strokeDashArray: 4 },
                legend: { show: false },
                yaxis: { title: { text: '{{ __("Expense") }}' } }
                };
                target.innerHTML = "";
                const arChart = new window.ApexCharts(target, chartBarOptions);
                arChart.render();
            } catch (_) { scheduleInteractiveError(getMsg(target, "chart_unavailable")); }
            };
            const saveAsPDF = () => {
            const area = document.getElementById("printableArea");
            const name = ($ && $("#filename").val() || "").toString().trim() || "export";
            const opt = { margin: 0.3, filename: name, image: { type: "jpeg", quality: 1 }, html2canvas: { scale: 4, dpi: 72, letterRendering: true }, jsPDF: { unit: "in", format: "A2" } };
            if (!area) { scheduleInteractiveError(getMsg(document.body, "pdf_unavailable")); return; }
            try {
                if (typeof window.html2pdf !== "function") { try { 
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("html2pdf unavailable");
                 } catch (_) {} scheduleInteractiveError(getMsg(area, "plugin_unavailable")); return; }
                window.html2pdf().set(opt).from(area).save();
            } catch (_) { scheduleInteractiveError(getMsg(area, "pdf_unavailable")); }
            };
            window.saveAsPDF = saveAsPDF;
            const init = () => { renderExpenseChart(); };
            if (document.readyState === "loading") { document.addEventListener("DOMContentLoaded", init, { once: true }); }
            else { init(); }
        })();
    </script>
@endpush
{{--            <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
{{--                <i class="ti ti-filter"></i>--}}
{{--            </a>--}}
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @php
            $downloadGuardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'download_expenses_summary_unavailable') ?? 'Download function for expenses summaries report is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a href="#"
        id="download-expenses-summary-link"
        class="{{ VC::BT_SM_PM }} download-expenses-summary"
        data-func-name="saveAsPDF"
        data-guard-msg="{{ $downloadGuardMsg }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ __('Download') }}"
        data-original-title="{{ __('Download') }}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/expenses/summaries/download.js') }}" defer></script>
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
                            $expenseSummaryBase       = ViewsConstants::RPT.'.expense.summary';
                            $expenseSummaryKebab      = Str::kebab($expenseSummaryBase);
                            $expenseSummaryResolved   = Route::has($expenseSummaryBase) ? $expenseSummaryBase : (Route::has($expenseSummaryKebab) ? $expenseSummaryKebab : null);
                            $expenseSummaryUrl        = $expenseSummaryResolved ? route($expenseSummaryResolved) : '#';
                            $expenseSummaryGuardMsg   = Utility::fetchLinkMessage($lang, ViewsConstants::RPT, 'expense_summary_report_unavailable') ?? 'Expense summary report route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        {{ Form::open([
                            'method'            => 'GET',
                            'url'               => $expenseSummaryUrl,
                            'id'                => 'report_expense_summary',
                            'data-url'          => $expenseSummaryUrl,
                            'data-guard-msg'    => $expenseSummaryGuardMsg,
                            'data-sv-localized' => 'true',
                        ]) }}
                            <div class="{{ VC::R_ALC_JCE }}">
                                <div class="col-xl-10">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box"></div>
                                        </div>
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box">
                                                {{ Form::label('year', __('Year'), ['class'=> VC::FM_LB]) }}
                                                {{ Form::select('year', $yearList ?? [], request('year', ''), ['class' => VC::FM_CT_SL]) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box">
                                                {{ Form::label('category', __('Category'), ['class'=> VC::FM_LB]) }}
                                                {{ Form::select('category', $category ?? [], request('category',''), ['class' => VC::FM_CT_SL]) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box">
                                                {{ Form::label('vendor', __('Vendor'), ['class'=> VC::FM_LB]) }}
                                                {{ Form::select('vendor', $vendor ?? [], request('vendor',''), ['class' => VC::FM_CT_SL]) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::C_AT }}">
                                            <a href="#"
                                            class="{{ VC::BT_SM_PM }} apply-expense-summary"
                                            data-form-id="report_expense_summary"
                                            data-guard-msg="{{ $expenseSummaryGuardMsg }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}"
                                            data-original-title="{{ __('apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>
                                            <a href="{{ $expenseSummaryUrl }}"
                                            class="{{ VC::BT_SM_DG }} reset-expense-summary"
                                            data-url="{{ $expenseSummaryUrl }}"
                                            data-guard-msg="{{ $expenseSummaryGuardMsg }}"
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
                            <script src="{{ asset('assets/js/routes/reports/expenses/summaries/apply.js') }}" defer></script>
                            <script src="{{ asset('assets/js/routes/reports/expenses/summaries/reset.js') }}" defer></script>
                        @endpush
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="printableArea">
        <div class="{{ VC::RW }} {{ VC::MT3 }}">
            <div class="col">
                <input type="hidden" value="{{ (data_get($filter,'category',__('All'))).' '.__('Expense Summary').' '.__('Report of').' '.(data_get($filter,'startDateRange',__('No starting data available'))).' '.__('to').' '.(data_get($filter,'endDateRange',__('No ending data available'))) }}" id="filename">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Report') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ __('Expense Summary') }}</h6>
                </div>
            </div>
            @if(data_get($filter,'category') && data_get($filter,'category') !== __('All'))
                <div class="col">
                    <div class="{{ VC::CD_POS }}">
                        <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Category') }} :</h7>
                        <h6 class="{{ VC::RPT_TX_DEF }}">{{ data_get($filter,'category',__('No category available')) }}</h6>
                    </div>
                </div>
            @endif
            @if(data_get($filter,'vendor') && data_get($filter,'vendor') !== __('All'))
                <div class="col">
                    <div class="{{ VC::CD_POS }}">
                        <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Vendor') }} :</h7>
                        <h6 class="{{ VC::RPT_TX_DEF }}">{{ data_get($filter,'vendor',__('No vendor available')) }}</h6>
                    </div>
                </div>
            @endif
            <div class="col">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Duration') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ (data_get($filter,'startDateRange',__('No starting data available'))).' '.__('to').' '.(data_get($filter,'endDateRange',__('No ending data available'))) }}</h6>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}" id="chart-container">
            <div class="{{ VC::CD }}">
                <div class="card-body">
                    <div class="scrollbar-inner">
                        <div id="chart-sales" data-color="primary" data-height="300"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        @php
                            $months = $monthList ?? [];
                            $colspan = (count($months) + 1);
                            $hasPaymentRows = !empty($expenseArr);
                            $hasBillRows = !empty($billArray);
                            $hasTotal = !empty($chartExpenseArr);
                        @endphp
                        <table class="{{ VC::TB }}">
                            <thead>
                            <tr>
                                <th>{{ __('Category') }}</th>
                                @forelse($months as $month)
                                    <th>{{ $month }}</th>
                                @empty
                                    <th>{{ __('No Months') }}</th>
                                @endforelse
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td colspan="{{ $colspan }}" class="text-dark"><span>{{ __('Payment :') }}</span></td>
                            </tr>
                            @forelse($expenseArr ?? [] as $expense)
                                <tr>
                                    <td>{{ data_get($expense,'category',__('No category available')) }}</td>
                                    @php $rowData = data_get($expense,'data',[]); @endphp
                                    @if(!empty($months))
                                        @foreach($months as $idx => $m)
                                            <td>{{ $user?->priceFormat($rowData[$idx] ?? 0) }}</td>
                                        @endforeach
                                    @else
                                        <td>{{ $user?->priceFormat(0) }}</td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $colspan }}" class="text-center text-muted">{{ __('No payments found for the selected filters') }}</td>
                                </tr>
                            @endforelse

                            <tr>
                                <td colspan="{{ $colspan }}" class="text-dark"><span>{{ __('Bill :') }}</span></td>
                            </tr>
                            @forelse($billArray ?? [] as $bill)
                                <tr>
                                    <td>{{ data_get($bill,'category',__('No category available')) }}</td>
                                    @php $rowData = data_get($bill,'data',[]); @endphp
                                    @if(!empty($months))
                                        @foreach($months as $idx => $m)
                                            <td>{{ $user?->priceFormat($rowData[$idx] ?? 0) }}</td>
                                        @endforeach
                                    @else
                                        <td>{{ $user?->priceFormat(0) }}</td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $colspan }}" class="text-center text-muted">{{ __('No bills found for the selected filters') }}</td>
                                </tr>
                            @endforelse

                            <tr>
                                <td colspan="{{ $colspan }}" class="text-dark"><span>{{ __('Expense = Payment + Bill :') }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-dark"><h6>{{ __('Total') }}</h6></td>
                                @if(!empty($months))
                                    @foreach($months as $idx => $m)
                                        <td>{{ $user?->priceFormat(($chartExpenseArr[$idx] ?? 0)) }}</td>
                                    @endforeach
                                @else
                                    <td>{{ $user?->priceFormat(0) }}</td>
                                @endif
                            </tr>
                            </tbody>
                        </table>
                        @if(!$hasPaymentRows && !$hasBillRows && !$hasTotal)
                            <div class="text-center text-muted py-3">{{ __('No data available for the selected filters') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

