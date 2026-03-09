@php
    $chartIncomeArr ??= [];
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
    } catch (\Throwable $e) {
        \Log::error('reports/income_summary — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
    $lang ??= 'en';
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Income Summary')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('Income Summary')}}</li>
@endsection

@push('theme-script')
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
@endpush

@push(StacksConstants::ADM_SCR_PG)
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/reports/incomes/summaries/lang/chart.js') }}">
    </script>
    <script async>
        (function () {
            const $ = window.jQuery;
            const qs = (s, r = document) => r.querySelector(s);
            const RG = window.RouteGuard || {};
            const getMsg = RG.getMsg || ((k, el) => el?.getAttribute?.('data-guard-msg') || '# ERROR');
            const showErrorNow = RG.showToast || (m => alert(m));
            const dataErrGuard = "data-error-guard";
            const dataChartGuard = "data-income-chart-bound";
            const scheduleInteractiveError = (message) => {
            const host = document.body;
            if (!host || host.getAttribute(dataErrGuard) === "true") { return; }
            host.setAttribute(dataErrGuard, "true");
            const once = () => { try { showErrorNow(message); } finally { host.removeAttribute(dataErrGuard); } };
            document.addEventListener("click", once, { once: true });
            };
            const renderIncomeChart = () => {
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
                series: [{ name: '{{ __("Income") }}', data: {!! json_encode($chartIncomeArr) !!} }],
                chart: { height: 300, type: "area", dropShadow: { enabled: true, color: "#000", top: 18, left: 7, blur: 10, opacity: 0.2 }, toolbar: { show: false } },
                dataLabels: { enabled: false },
                stroke: { width: 2, curve: "smooth" },
                title: { text: "", align: "left" },
                xaxis: { categories: {!! json_encode($monthList) !!}, title: { text: '{{ __("Months") }}' } },
                colors: ["#6fd944", "#6fd944"],
                grid: { strokeDashArray: 4 },
                legend: { show: false },
                yaxis: { title: { text: '{{ __("Income") }}' } }
                };
                target.innerHTML = "";
                const arChart = new window.ApexCharts(target, chartBarOptions);
                arChart.render();
            } catch (_) { scheduleInteractiveError(getMsg(target, "chart_unavailable")); }
            };
            const saveAsPDF = () => {
            const area = document.getElementById("printableArea");
            if (!area) { scheduleInteractiveError(getMsg(document.body, "pdf_unavailable")); return; }
            const name = (($ && $("#filename").val()) ?? "").toString().trim() || "export";
            const opt = { margin: 0.3, filename: name, image: { type: "jpeg", quality: 1 }, html2canvas: { scale: 4, dpi: 72, letterRendering: true }, jsPDF: { unit: "in", format: "A2" } };
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
            const init = () => { renderIncomeChart(); };
            if (document.readyState === "loading") { document.addEventListener("DOMContentLoaded", init, { once: true }); }
            else { init(); }
        })();
    </script>
@endpush

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @php
            $downloadGuardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'download_income_summary_unavailable') ?? 'Download function for income summary report is unavailable. Please contact technical support or your domain administrator.';
@endphp
        <a href="#"
        id="download-income-summary-link"
        class="{{ VC::BT_SM_PM }} download-income-summary"
        data-func-name="saveAsPDF"
        data-guard-msg="{{ base64_encode($downloadGuardMsg) }}"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="{{ __('Download') }}"
        data-original-title="{{ __('Download') }}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/reports/incomeSummary/download.js') }}" defer></script>
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
                                $incomeSummaryBase     = ViewsConstants::RPT.'.income.summary';
                                $incomeSummaryKebab    = Str::kebab($incomeSummaryBase);
                                $incomeSummaryResolved = Route::has($incomeSummaryBase) ? $incomeSummaryBase : (Route::has($incomeSummaryKebab) ? $incomeSummaryKebab : null);
                                $incomeSummaryUrl      = $incomeSummaryResolved ? route($incomeSummaryResolved) : '#';
                                $incomeSummaryGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::RPT, 'income_summary_report_unavailable') ?? 'Income summary report route is unavailable. Please contact technical support or your domain administrator.';
                            } catch (\Throwable $e) {
                                \Log::error('reports/income_summary — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        {{ Form::open([
                            'method'            => 'GET',
                            'url'               => $incomeSummaryUrl,
                            'id'                => 'report_income_summary',
                            'data-url'          => $incomeSummaryUrl,
                            'data-guard-msg'    => $incomeSummaryGuardMsg,
                            'data-sv-localized' => 'true',
                        ]) }}
                            <div class="{{ VC::R_ALC_JCE }}">
                                <div class="{{ VC::CXL10 }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box"></div>
                                        </div>
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box">
                                                {{ Form::label('year', __('Year'), ['class'=> VC::FM_LB]) }}
                                                {{ Form::select('year', $yearList ?? [], request('year',''), ['class' => VC::FM_CT_SL, 'placeholder' => __('No years available')]) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box">
                                                {{ Form::label('category', __('Category'), ['class'=> VC::FM_LB]) }}
                                                {{ Form::select('category', $category ?? [], request('category',''), ['class' => VC::FM_CT_SL, 'placeholder' => __('No categories available')]) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::CL_XLG4 }}">
                                            <div class="btn-box">
                                                {{ Form::label('customer', __('Customer'), ['class'=> VC::FM_LB]) }}
                                                {{ Form::select('customer', $customer ?? [], request('customer',''), ['class' => VC::FM_CT_SL, 'placeholder' => __('No customers available')]) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT }} {{ VC::MT4 }}">
                                    <div class="{{ VC::RW }}">
                                        <div class="{{ VC::C_AT }}">
                                            <a href="#"
                                            class="{{ VC::BT_SM_PM }} apply-income-summary"
                                            data-form-id="report_income_summary"
                                            data-guard-msg="{{ base64_encode($incomeSummaryGuardMsg) }}"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="{{ __('Apply') }}"
                                            data-original-title="{{ __('apply') }}">
                                                <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                            </a>
                                            <a href="{{ $incomeSummaryUrl }}"
                                            class="{{ VC::BT_SM_DG }} reset-income-summary"
                                            data-url="{{ $incomeSummaryUrl }}"
                                            data-guard-msg="{{ base64_encode($incomeSummaryGuardMsg) }}"
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
                            <script src="{{ asset('assets/js/routes/reports/incomeSummary/apply.js') }}" defer></script>
                            <script src="{{ asset('assets/js/routes/reports/incomeSummary/reset.js') }}" defer></script>
                        @endpush
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        try {
            $categoryLabel   = data_get($filter, 'category');
            $categoryText    = $categoryLabel ? $categoryLabel : __('No category available');
            $customerLabel   = data_get($filter, 'customer');
            $customerText    = $customerLabel ? $customerLabel : __('No customer available');
            $startRange      = data_get($filter, 'startDateRange');
            $endRange        = data_get($filter, 'endDateRange');
            $startText       = $startRange ? $startRange : __('Could not find start date');
            $endText         = $endRange ? $endRange : __('Could not find end date');
            $months          = is_array($monthList ?? null) ? $monthList : [];
            $colspan         = max(2, count($months) + 1);
            $hasRevenueRows  = !empty($incomeArr);
            $hasInvoiceRows  = !empty($invoiceArray);
            $hasTotals       = !empty($chartIncomeArr);
        } catch (\Throwable $e) {
            \Log::error('reports/income_summary — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp

    <div id="printableArea">
        {{-- ── KPI Aggregation Cards ── --}}
        @php
            $grandTotalIncome = is_array($chartIncomeArr) ? array_sum($chartIncomeArr) : 0;
            $grandTotalRevenue = 0;
            $grandTotalInvoice = 0;
            foreach (($incomeArr ?? []) as $_inc) {
                $grandTotalRevenue += array_sum($_inc['data'] ?? []);
            }
            foreach (($invoiceArray ?? []) as $_inv) {
                $grandTotalInvoice += array_sum($_inv['data'] ?? []);
            }
            $fmtIncome  = ($user?->priceFormat($grandTotalIncome))  ?? number_format((float)$grandTotalIncome, 2);
            $fmtRevenue = ($user?->priceFormat($grandTotalRevenue)) ?? number_format((float)$grandTotalRevenue, 2);
            $fmtInvoice = ($user?->priceFormat($grandTotalInvoice)) ?? number_format((float)$grandTotalInvoice, 2);
        @endphp
        @include('reports.partials._kpi_cards', ['kpiHeading' => __('Income Overview'), 'kpis' => [
            ['label' => __('Total Revenue'),       'value' => $fmtRevenue, 'tone' => 'positive'],
            ['label' => __('Total Invoice Income'), 'value' => $fmtInvoice, 'tone' => 'neutral'],
            ['label' => __('Grand Total Income'),   'value' => $fmtIncome,  'tone' => 'positive'],
        ]])

        <div class="{{ VC::RW }} {{ VC::MT3 }}">
            <div class="col">
                <input type="hidden" value="{{ $categoryText.' '.__('Income Summary').' '.__('Report of').' '.$startText.' '.__('to').' '.$endText }}" id="filename">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Report') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ __('Income Summary') }}</h6>
                </div>
            </div>
            @if($categoryLabel && $categoryLabel != __('All'))
                <div class="col">
                    <div class="{{ VC::CD_POS }}">
                        <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Category') }} :</h7>
                        <h6 class="{{ VC::RPT_TX_DEF }}">{{ $categoryText }}</h6>
                    </div>
                </div>
            @endif
            @if($customerLabel && $customerLabel != __('All'))
                <div class="col">
                    <div class="{{ VC::CD_POS }}">
                        <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Customer') }} :</h7>
                        <h6 class="{{ VC::RPT_TX_DEF }}">{{ $customerText }}</h6>
                    </div>
                </div>
            @endif
            <div class="col">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{ __('Duration') }} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ $startText.' '.__('to').' '.$endText }}</h6>
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
                            <table class="{{ VC::TB }} rpt-table" role="table" aria-label="{{ __('Income Summary') }}">
                                <caption class="sr-only">{{ __('Income summary report broken down by category and month') }}</caption>
                                <thead>
                                <tr>
                                    <th scope="col">{{ __('Category') }}</th>
                                    @if(count($months))
                                        @foreach($months as $month)
                                            <th scope="col">{{ $month }}</th>
                                        @endforeach
                                    @else
                                        <th scope="col">{{ __('No months available') }}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                <tr class="rpt-section-header">
                                    <td colspan="{{ $colspan }}" class="{{ VC::TX_DK }}"><span>{{ __('Revenue :') }}</span></td>
                                </tr>
                                @if($hasRevenueRows)
                                    @foreach($incomeArr as $income)
                                        @php
 $row = $income['data'] ?? [];
@endphp
                                        <tr>
                                            <td>{{ $income['category'] ?? __('No category available') }}</td>
                                            @if(count($months))
                                                @foreach($months as $idx => $m)
                                                    @php
 $val = $row[$idx] ?? 0;
@endphp
                                                    <td>{{ ($user?->priceFormat($val)) ?? number_format((float)$val, 2) }}</td>
                                                @endforeach
                                            @else
                                                <td>{{ __('No revenue data available') }}</td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="{{ $colspan }}" class="{{ VC::TXCT_MT }}">{{ __('No revenue data available') }}</td>
                                    </tr>
                                @endif

                                <tr class="rpt-section-header">
                                    <td colspan="{{ $colspan }}" class="{{ VC::TX_DK }}"><span>{{ __('Invoice :') }}</span></td>
                                </tr>
                                @if($hasInvoiceRows)
                                    @foreach($invoiceArray as $invoice)
                                        @php
 $row = $invoice['data'] ?? [];
@endphp
                                        <tr>
                                            <td>{{ $invoice['category'] ?? __('No category available') }}</td>
                                            @if(count($months))
                                                @foreach($months as $idx => $m)
                                                    @php
 $val = $row[$idx] ?? 0;
@endphp
                                                    <td>{{ ($user?->priceFormat($val)) ?? number_format((float)$val, 2) }}</td>
                                                @endforeach
                                            @else
                                                <td>{{ __('No invoice data available') }}</td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="{{ $colspan }}" class="{{ VC::TXCT_MT }}">{{ __('No invoice data available') }}</td>
                                    </tr>
                                @endif

                                <tr class="rpt-section-header">
                                    <td colspan="{{ $colspan }}" class="{{ VC::TX_DK }}"><span>{{ __('Income = Revenue + Invoice :') }}</span></td>
                                </tr>
                                @if(count($months))
                                    <tr class="rpt-total-row">
                                        <td class="{{ VC::TX_DK }}"><h6>{{ __('Total') }}</h6></td>
                                        @foreach($months as $idx => $m)
                                            @php
 $val = $chartIncomeArr[$idx] ?? 0;
@endphp
                                            <td>{{ ($user?->priceFormat($val)) ?? number_format((float)$val, 2) }}</td>
                                        @endforeach
                                    </tr>
                                @else
                                    <tr>
                                        <td class="{{ VC::TX_DK }}"><h6>{{ __('Total') }}</h6></td>
                                        <td>{{ __('Could not compute totals because no months are available') }}</td>
                                    </tr>
                                @endif

                                @if(!$hasRevenueRows && !$hasInvoiceRows && !$hasTotals)
                                    <tr>
                                        <td colspan="{{ $colspan }}" class="{{ VC::TXCT_MT }}">{{ __('No income data available for the selected filters') }}</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
