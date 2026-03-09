@php
    try {
} catch (\Throwable $e) {
        \Log::error('vendors/dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Dashboard')}}
@endsection
{{--{{dd($billChartData['data'])}}--}}
@if(!empty($billChartData['data']))
@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('js/routes/vendors/lang/chart.js') }}"></script>
    <script async>
        (function () {
            const $ = window.jQuery;
            const dataBound = "data-chart-bound";
            const qs = (s, r = document) => r.querySelector(s);
            const RG = window.RouteGuard || {};
            const getMsg = RG.getMsg || ((k, el) => el?.getAttribute?.('data-guard-msg') || '# ERROR');
            const showError = RG.showToast || (m => alert(m));
            const scheduleClickError = (msg) => { const host = document.body; if (!host || host.getAttribute('data-chart-error') === "true") return; host.setAttribute('data-chart-error', "true"); const once = () => { try { showError(msg); } finally { host.removeAttribute('data-chart-error'); } }; document.addEventListener("click", once, { once: true }); };
            const init = () => {
            if (!$ || !$.fn) { try { console.error("jQuery unavailable"); } catch (_) {} }
            const el = qs("#chart-sales");
            if (!el) return;
            if (el.getAttribute(dataBound) === "true") return;
            el.setAttribute(dataBound, "true");
            const opts = {
                series: [
                { name: "{{__('Unpaid')}}", data: {!! json_encode($billChartData['data']['unpaid']) !!} },
                { name: "{{__('Paid')}}", data: {!! json_encode($billChartData['data']['paid']) !!} },
                { name: "{{__('Partial Paid')}}", data: {!! json_encode($billChartData['data']['partial']) !!} },
                { name: "{{__('Due')}}", data: {!! json_encode($billChartData['data']['due']) !!} }
                ],
                chart: { height: 350, type: "line", dropShadow: { enabled: true, color: "#000", top: 18, left: 7, blur: 10, opacity: 0.2 }, toolbar: { show: false } },
                colors: ["#FF5630", "#36B37E", "#00B8D9", "#FFAB00"],
                dataLabels: { enabled: true },
                stroke: { curve: "smooth" },
                title: { text: "", align: "left" },
                grid: { borderColor: "#e7e7e7", row: { colors: ["#f3f3f3", "transparent"], opacity: 0.5 } },
                markers: { size: 1 },
                xaxis: { categories: {!! json_encode($billChartData['month']) !!}, title: { text: "Month" } },
                yaxis: { title: { text: "{{__('Amount')}}" } },
                legend: { position: "top", horizontalAlign: "right", floating: true, offsetY: -25, offsetX: -5 }
            };
            if (typeof window.ApexCharts !== "function") { try { console.error("ApexCharts unavailable"); } catch (_) {} scheduleClickError(getMsg(el, "plugin_unavailable")); return; }
            try { const chart = new window.ApexCharts(el, opts); chart.render(); } catch (_) { scheduleClickError(getMsg(el, "chart_unavailable")); }
            const mo = new MutationObserver((m, o) => { if (!document.body.contains(el)) { o.disconnect(); } });
            mo.observe(document.body, { childList: true, subtree: true });
            };
            if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init, { once: true }); else init();
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="{{ VC::CM12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_HD }}">
                    <div class="row">
                        @php
                            try {
                                $pd = (array) data_get($billChartData ?? [], 'progressData', []);
                                $decRaw = Utility::getValByName('decimal_number');
                                $dec = is_numeric($decRaw) ? (int) $decRaw : 0;
                                $unpaidPr = (float) ($pd['unpaidPr'] ?? 0);
                                $paidPr = (float) ($pd['paidPr'] ?? 0);
                                $partialPr = (float) ($pd['partialPr'] ?? 0);
                                $duePr = (float) ($pd['duePr'] ?? 0);
                                $totalBill = (int) ($pd['totalBill'] ?? 0);
                                $totalUnpaidBill = (int) ($pd['totalUnpaidBill'] ?? 0);
                                $totalPaidBill = (int) ($pd['totalPaidBill'] ?? 0);
                                $totalPartialBill = (int) ($pd['totalPartialBill'] ?? 0);
                                $totalDueBill = (int) ($pd['totalDueBill'] ?? 0);
                            } catch (\Throwable $e) {
                                \Log::error('vendors/dashboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        <div class="col">
                            <div class="{{ VC::LG_FLSH }}">
                                <a href="#" class="{{ VC::LGI_ACT }}">
                                    <div class="{{ VC::DFL_AIC_JCB }}">
                                        <div class="flex-fill {{ VC::TX_LM }}">
                                            <h6 class="{{ VC::PG_SM_BL }}">{{ number_format($unpaidPr, $dec, '.', '') . '%' }}</h6>
                                            <div class="{{ VC::PG_XS }}">
                                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $unpaidPr }}%;" aria-valuenow="{{ $unpaidPr }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="{{ VC::DFL_SPC_TXT }} text-end">
                                                <div><span class="font-weight-bold {{ VC::TX_DNG }}">{{ __('Unpaid') }}</span></div>
                                                <div>{{ $totalBill . '/' . $totalUnpaidBill }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col">
                            <div class="{{ VC::LG_FLSH }}">
                                <a href="#" class="{{ VC::LGI_ACT }}">
                                    <div class="{{ VC::DFL_AIC_JCB }}">
                                        <div class="flex-fill {{ VC::TX_LM }}">
                                            <h6 class="{{ VC::PG_SM_BL }}">{{ number_format($paidPr, $dec, '.', '') . ' %' }}</h6>
                                            <div class="{{ VC::PG_XS }}">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $paidPr }}%;" aria-valuenow="{{ $paidPr }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="{{ VC::DFL_SPC_TXT }} text-end">
                                                <div><span class="font-weight-bold text-success">{{ __('Paid') }}</span></div>
                                                <div>{{ $totalBill . '/' . $totalPaidBill }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col">
                            <div class="{{ VC::LG_FLSH }}">
                                <a href="#" class="{{ VC::LGI_ACT }}">
                                    <div class="{{ VC::DFL_AIC_JCB }}">
                                        <div class="flex-fill {{ VC::TX_LM }}">
                                            <h6 class="{{ VC::PG_SM_BL }}">{{ number_format($partialPr, $dec, '.', '') . '%' }}</h6>
                                            <div class="{{ VC::PG_XS }}">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $partialPr }}%;" aria-valuenow="{{ $partialPr }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="{{ VC::DFL_SPC_TXT }} text-end">
                                                <div><span class="font-weight-bold text-info">{{ __('Partial Paid') }}</span></div>
                                                <div>{{ $totalBill . '/' . $totalPartialBill }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col">
                            <div class="{{ VC::LG_FLSH }}">
                                <a href="#" class="{{ VC::LGI_ACT }}">
                                    <div class="{{ VC::DFL_AIC_JCB }}">
                                        <div class="flex-fill {{ VC::TX_LM }}">
                                            <h6 class="{{ VC::PG_SM_BL }}">{{ number_format($duePr, $dec, '.', '') . '%' }}</h6>
                                            <div class="{{ VC::PG_XS }}">
                                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $duePr }}%;" aria-valuenow="{{ $duePr }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="{{ VC::DFL_SPC_TXT }} text-end">
                                                <div><span class="font-weight-bold text-warning">{{ __('Due') }}</span></div>
                                                <div>{{ $totalBill . '/' . $totalDueBill }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="{{ VC::CD_BD }}">
                    <h6>{{ __('Current year') . ' - ' . date('Y') }}</h6>
                    <div class="scrollbar-inner">
                        <div id="chart-sales" height="300"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@else
    <div class="{{ VC::ALT_WRN }}">{{ __('No data available for the chart.') }}</div>
@endif
