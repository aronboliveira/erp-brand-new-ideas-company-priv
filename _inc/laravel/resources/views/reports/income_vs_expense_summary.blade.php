@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
    } catch (\Throwable $e) {
        \Log::error('reports/income_vs_expense_summary — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
    $lang ??= 'en';
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Income Vs Expense Summary')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('Income vs Expense Summary')}}</li>
@endsection

@push('theme-script')
    <script src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
@endpush

@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/reports/incomeVsExpenses/summaries/lang/chart.js') }}">
    </script>
    <script async>
        (function () {
            const $ = window.jQuery;
            const qs = (s, r = document) => r.querySelector(s);
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const dataErrGuard = "data-error-guard";
            const dataChartGuard = "data-profit-chart-bound";

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
                t.innerHTML = '<div class="toast-header"><strong class="me-auto">Notice</strong><button type="button" class="{{ VC::BT_CL }}" data-bs-dismiss="toast" aria-label="Close"></button></div><div class="toast-body"></div>';
                container.appendChild(t);
                }
                const body = qs(".toast-body", t);
                if (body) { body.textContent = message ?? errFb; }
                try {
                new window.bootstrap.Toast(t, { autohide: true, delay: 4000 }).show();
                } catch (_) {
                alert(message ?? errFb);
                }
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
            let msg = errFb;
            if (el?.getAttribute("data-sv-localized") === "true" || el?.getAttribute(dataClientLocalized) === "true") {
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

            const renderProfitChart = () => {
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
                series: [{ name: '{{ __("Profit") }}', data: {!! json_encode($profit) !!} }],
                chart: { height: 300, type: "area", dropShadow: { enabled: true, color: "#000", top: 18, left: 7, blur: 10, opacity: 0.2 }, toolbar: { show: false } },
                dataLabels: { enabled: false },
                stroke: { width: 2, curve: "smooth" },
                title: { text: "", align: "left" },
                xaxis: { categories: {!! json_encode($monthList) !!}, title: { text: '{{ __("Months") }}' } },
                colors: ["#ffa21d", "#FF3A6E"],
                grid: { strokeDashArray: 4 },
                legend: { show: false },
                yaxis: { title: { text: '{{ __("Profit") }}' } }
                };
                target.innerHTML = "";
                const arChart = new window.ApexCharts(target, chartBarOptions);
                arChart.render();
            } catch (_) {
                scheduleInteractiveError(getMsg(target, "chart_unavailable"));
            }
            };

            const saveAsPDF = () => {
            const area = document.getElementById("printableArea");
            if (!area) {
                scheduleInteractiveError(getMsg(document.body, "pdf_unavailable"));
                return;
            }
            const name = (($ && $("#filename").val()) ?? "").toString().trim() || "export";
            const opt = {
                margin: 0.3,
                filename: name,
                image: { type: "jpeg", quality: 1 },
                html2canvas: { scale: 4, dpi: 72, letterRendering: true },
                jsPDF: { unit: "in", format: "A2" }
            };
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

            window.saveAsPDF = saveAsPDF;

            const init = () => {
            renderProfitChart();
            };

            if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", init, { once: true });
            } else {
            init();
            }
        })();
    </script>
@endpush
{{--        <a class="{{ VC::BT_SM_PM }}" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
{{--            <i class="ti ti-filter"></i>--}}
{{--        </a>--}}
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @php
            $downloadGuardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'download_income_vs_expense_summary_unavailable') ?? 'Download function for income vs expense summary is unavailable. Please contact technical support or your domain administrator.';
@endphp
        <a href="#"
        id="download-income-vs-expense-summary-link"
        class="{{ VC::BT_SM_PM }} download-income-vs-expense-summary"
        data-func-name="saveAsPDF"
        data-guard-msg="{{ base64_encode($downloadGuardMsg) }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ __('Download') }}"
        data-original-title="{{ __('Download') }}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/incomeVsExpenses/summaries/download.js') }}" defer></script>
        @endpush
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    @include('reports.partials._report_styles')
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::MT2 }}" id="multiCollapseExample1">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD }}">
                        @php
                            try {
                                $lang                              = Utility::fetchUserLang();
                                $ivsBase                           = VW::RPT.'.income.vs.expense.summary';
                                $ivsKebab                          = Str::kebab($ivsBase);
                                $ivsResolved                       = Route::has($ivsBase) ? $ivsBase : (Route::has($ivsKebab) ? $ivsKebab : null);
                                $ivsUrl                            = $ivsResolved ? route($ivsResolved) : '#';
                                $ivsGuardMsg                       = Utility::fetchLinkMessage($lang, VW::RPT, 'income_vs_expense_summary_report_route_unavailable') ?? 'Income vs expense summary report route is unavailable. Please contact technical support or your domain administrator.';
                            } catch (\Throwable $e) {
                                \Log::error('reports/income_vs_expense_summary — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        {{ Form::open([
                            'method'            => 'GET',
                            'url'               => $ivsUrl,
                            'id'                => 'income_vs_expense_summary',
                            'data-url'          => $ivsUrl,
                            'data-guard-msg'    => $ivsGuardMsg,
                            'data-sv-localized' => 'true',
                        ]) }}
                            <div class="{{ VC::R_ALC_JCE }}">
                                <div class="{{ VC::CXL10 }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box">
                                                {{ Form::label('year', __('Year'),['class'=> VC::FM_LB])}}
                                                {{ Form::select('year', $yearList ?? [], request('year',''), ['class' => VC::FM_CT_SL, 'placeholder' => __('No years available')]) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box">
                                                {{ Form::label('category', __('Category'),['class'=> VC::FM_LB])}}
                                                {{ Form::select('category', $category ?? [], request('category',''), ['class' => VC::FM_CT_SL, 'placeholder' => __('No categories available')]) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box">
                                                {{ Form::label('customer', __('Customer'),['class'=> VC::FM_LB])}}
                                                {{ Form::select('customer', $customer ?? [], request('customer',''), ['class' => VC::FM_CT_SL, 'placeholder' => __('No customers available')]) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box">
                                                {{ Form::label('vendor', __('Vendor'),['class'=> VC::FM_LB])}}
                                                {{ Form::select('vendor', $vendor ?? [], request('vendor',''), ['class' => VC::FM_CT_SL, 'placeholder' => __('No vendors available')]) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                            <a href="#"
                                            class="{{ VC::BT_SM_PM }} apply-income-vs-expense-summary"
                                            data-form-id="income_vs_expense_summary"
                                            data-guard-msg="{{ base64_encode($ivsGuardMsg) }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}"
                                            data-original-title="{{ __('apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>
                                            <a href="{{ $ivsUrl }}"
                                            class="{{ VC::BT_SM_DG }} reset-income-vs-expense-summary"
                                            data-url="{{ $ivsUrl }}"
                                            data-guard-msg="{{ base64_encode($ivsGuardMsg) }}"
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
                            <script src="{{ asset('assets/js/routes/reports/incomeVsExpense/summaries/apply.js') }}" defer></script>
                            <script src="{{ asset('assets/js/routes/reports/incomeVsExpense/summaries/reset.js') }}" defer></script>
                        @endpush
                    </div>
                </div>
            </div>
        </div>
    </div>
    @php
        try {
            $catLabel     = data_get($filter,'category');
            $catText      = $catLabel ? $catLabel : __('No category available');
            $custLabel    = data_get($filter,'customer');
            $custText     = $custLabel ? $custLabel : __('No customer available');
            $vendLabel    = data_get($filter,'vendor');
            $vendText     = $vendLabel ? $vendLabel : __('No vendor available');
            $startRange   = data_get($filter,'startDateRange');
            $endRange     = data_get($filter,'endDateRange');
            $startText    = $startRange ? $startRange : __('Could not find start date');
            $endText      = $endRange ? $endRange : __('Could not find end date');

            $months       = is_array($monthList ?? null) ? $monthList : [];
            $colspan      = max(2, count($months) + 1);

            $revTotals    = is_array($revenueIncomeTotal ?? null) ? $revenueIncomeTotal : [];
            $invTotals    = is_array($invoiceIncomeTotal ?? null) ? $invoiceIncomeTotal : [];
            $payTotals    = is_array($paymentExpenseTotal ?? null) ? $paymentExpenseTotal : [];
            $billTotals   = is_array($billExpenseTotal ?? null) ? $billExpenseTotal : [];
            $profitTotals = is_array($profit ?? null) ? $profit : [];

            $hasIncome    = !empty($revTotals) || !empty($invTotals);
            $hasExpense   = !empty($payTotals) || !empty($billTotals);
            $hasProfit    = !empty($profitTotals);
        } catch (\Throwable $e) {
            \Log::error('reports/income_vs_expense_summary — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp
    <div id="printableArea">
        {{-- ── KPI Aggregation Cards ── --}}
        @php
            $totalRevSum = is_array($revTotals) ? array_sum($revTotals) : 0;
            $totalInvSum = is_array($invTotals) ? array_sum($invTotals) : 0;
            $totalPaySum = is_array($payTotals) ? array_sum($payTotals) : 0;
            $totalBilSum = is_array($billTotals) ? array_sum($billTotals) : 0;
            $totalPftSum = is_array($profitTotals) ? array_sum($profitTotals) : 0;
            $totalIncSum = $totalRevSum + $totalInvSum;
            $totalExpSum = $totalPaySum + $totalBilSum;
            $fmtTotInc = ($user?->priceFormat($totalIncSum)) ?? number_format((float)$totalIncSum, 2);
            $fmtTotExp = ($user?->priceFormat($totalExpSum)) ?? number_format((float)$totalExpSum, 2);
            $fmtTotPft = ($user?->priceFormat($totalPftSum)) ?? number_format((float)$totalPftSum, 2);
            $profitTone = $totalPftSum >= 0 ? 'positive' : 'negative';
        @endphp
        @include('reports.partials._kpi_cards', ['kpiHeading' => __('Income vs Expense Overview'), 'kpis' => [
            ['label' => __('Total Income'),  'value' => $fmtTotInc, 'tone' => 'positive'],
            ['label' => __('Total Expense'), 'value' => $fmtTotExp, 'tone' => 'negative'],
            ['label' => __('Net Profit'),    'value' => $fmtTotPft, 'tone' => $profitTone],
        ]])

        <div class="{{ VC::RW }} {{ VC::MT3 }}">
            <div class="col">
                <input type="hidden" value="{{ $catText.' '.__('Income Vs Expense Summary').' '.__('Report of').' '.$startText.' '.__('to').' '.$endText }}" id="filename">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{__('Report')}} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{__('Income Vs Expense Summary')}}</h6>
                </div>
            </div>
            @if($catLabel && $catLabel != __('All'))
                <div class="col">
                    <div class="{{ VC::CD_POS }}">
                        <h7 class="{{ VC::RPT_TX_GR }}">{{__('Category')}} :</h7>
                        <h6 class="{{ VC::RPT_TX_DEF }}">{{ $catText }}</h6>
                    </div>
                </div>
            @endif
            @if($custLabel && $custLabel != __('All'))
                <div class="col">
                    <div class="{{ VC::CD_POS }}">
                        <h7 class="{{ VC::RPT_TX_GR }}">{{__('Customer')}} :</h7>
                        <h6 class="{{ VC::RPT_TX_DEF }}">{{ $custText }}</h6>
                    </div>
                </div>
            @endif
            @if($vendLabel && $vendLabel != __('All'))
                <div class="col">
                    <div class="{{ VC::CD_POS }}">
                        <h7 class="{{ VC::RPT_TX_GR }}">{{__('Vendor')}} :</h7>
                        <h6 class="{{ VC::RPT_TX_DEF }}">{{ $vendText }}</h6>
                    </div>
                </div>
            @endif
            <div class="col">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{__('Duration')}} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ $startText.' '.__('to').' '.$endText }}</h6>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}" id="chart-container">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD }}">
                    <div class="scrollbar-inner">
                        <div id="chart-sales" data-color="primary" data-height="300"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} rpt-table" role="table" aria-label="{{ __('Income vs Expense Summary') }}">
                            <caption class="sr-only">{{ __('Income versus expense comparison by month') }}</caption>
                            <thead>
                                <tr>
                                    <th scope="col">{{__('Type')}}</th>
                                    @if(count($months))
                                        @foreach($months as $m)
                                            <th scope="col">{{ $m }}</th>
                                        @endforeach
                                    @else
                                        <th>{{ __('No months available') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="rpt-section-header">
                                    <td colspan="{{ $colspan }}" class="{{ VC::TX_DK }}"><span>{{__('Income : ')}}</span></td>
                                </tr>
                                @if($hasIncome)
                                    <tr>
                                        <td>{{ __('Revenue') }}</td>
                                        @if(count($months))
                                            @foreach($months as $i => $m)
                                                @php
 $val = $revTotals[$i] ?? 0;
@endphp
                                                <td>{{ ($user?->priceFormat($val)) ?? number_format((float)$val, 2) }}</td>
                                            @endforeach
                                        @else
                                            <td>{{ __('No revenue data available') }}</td>
                                        @endif
                                    </tr>
                                    <tr>
                                        <td>{{ __('Invoice') }}</td>
                                        @if(count($months))
                                            @foreach($months as $i => $m)
                                                @php
 $val = $invTotals[$i] ?? 0;
@endphp
                                                <td>{{ ($user?->priceFormat($val)) ?? number_format((float)$val, 2) }}</td>
                                            @endforeach
                                        @else
                                            <td>{{ __('No invoice data available') }}</td>
                                        @endif
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="{{ $colspan }}" class="{{ VC::TXCT_MT }}">{{ __('No income data available for the selected filters') }}</td>
                                    </tr>
                                @endif

                                <tr class="rpt-section-header">
                                    <td colspan="{{ $colspan }}" class="{{ VC::TX_DK }}"><span>{{__('Expense : ')}}</span></td>
                                </tr>
                                @if($hasExpense)
                                    <tr>
                                        <td>{{ __('Payment') }}</td>
                                        @if(count($months))
                                            @foreach($months as $i => $m)
                                                @php
 $val = $payTotals[$i] ?? 0;
@endphp
                                                <td>{{ ($user?->priceFormat($val)) ?? number_format((float)$val, 2) }}</td>
                                            @endforeach
                                        @else
                                            <td>{{ __('No payment data available') }}</td>
                                        @endif
                                    </tr>
                                    <tr>
                                        <td>{{ __('Bill') }}</td>
                                        @if(count($months))
                                            @foreach($months as $i => $m)
                                                @php
 $val = $billTotals[$i] ?? 0;
@endphp
                                                <td>{{ ($user?->priceFormat($val)) ?? number_format((float)$val, 2) }}</td>
                                            @endforeach
                                        @else
                                            <td>{{ __('No bill data available') }}</td>
                                        @endif
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="{{ $colspan }}" class="{{ VC::TXCT_MT }}">{{ __('No expense data available for the selected filters') }}</td>
                                    </tr>
                                @endif

                                <tr class="rpt-section-header">
                                    <td colspan="{{ $colspan }}" class="{{ VC::TX_DK }}"><span>{{__('Profit = Income - Expense ')}}</span></td>
                                </tr>
                                @if($hasProfit)
                                    <tr>
                                        <td><h6>{{ __('Profit') }}</h6></td>
                                        @if(count($months))
                                            @foreach($months as $i => $m)
                                                @php
 $val = $profitTotals[$i] ?? 0;
@endphp
                                                <td>{{ ($user?->priceFormat($val)) ?? number_format((float)$val, 2) }}</td>
                                            @endforeach
                                        @else
                                            <td>{{ __('No profit data available') }}</td>
                                        @endif
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="{{ $colspan }}" class="{{ VC::TXCT_MT }}">{{ __('No profit data available for the selected filters') }}</td>
                                    </tr>
                                @endif

                                @if(!$hasIncome && !$hasExpense && !$hasProfit)
                                    <tr>
                                        <td colspan="{{ $colspan }}" class="{{ VC::TXCT_MT }}">{{ __('No data available for the selected filters') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
