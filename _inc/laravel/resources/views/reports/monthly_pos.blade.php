@php
    $data ??= [];
    $warehouse ??= $warehouses ?? [];
    $customer ??= $customers ?? [];
    try {
$lang = Utility::fetchUserLang();
    } catch (\Throwable $e) {
        \Log::error('reports/monthly_pos — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Pos')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Daily Pos Report') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('download report')
            @php
                $funcName  ??= 'saveAsPDF';
                $guardMsg  = Utility::fetchLinkMessage($lang, VW::RPT, 'download_montly_pos_unavailable') ?? 'Download function for monthly POS is unavailable. Please contact technical support or your domain administrator.';
@endphp
            <a href="#" id="download-report-link" class="{{ VC::BT_SM_PM }} download-report-link" data-func-name="{{ $funcName }}" data-sv-localized="true" data-guard-msg="{{ base64_encode($guardMsg) }}" data-bs-toggle="tooltip" title="{{ __('Download') }}" data-original-title="{{ __('Download') }}">
                <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <ul class="{{ VC::NAV_PL_Y3 }}" id="pills-tab" role="tablist">
        <li class="{{ VC::NV_IT }}">
            <a
                class="{{ VC::NV_LK }}"
                id="pills-home-tab"
                data-bs-toggle="pill"
                href="{{ Route::has(VW::RPT . '.daily.pos') ? route(VW::RPT . '.daily.pos') : '#' }}"
                data-url="{{ Route::has(VW::RPT . '.daily.pos') ? route(VW::RPT . '.daily.pos') : '#' }}"
                role="tab"
                aria-controls="pills-home"
                aria-selected="true"
            >
                {{ __('Daily') }}
            </a>
        </li>
        <li class="{{ VC::NV_IT }}">
            <a
                class="{{ VC::NV_LK }} active"
                id="pills-profile-tab"
                data-bs-toggle="pill"
                href="#monthly-chart"
                role="tab"
                aria-controls="pills-profile"
                aria-selected="false"
            >
                {{ __('Monthly') }}
            </a>
        </li>
    </ul>
    @php
        $flagAttr ??= 'data-dailyPos-listener-added';
        $guardAttr ??= 'data-url';
        $urlAttr ??= 'data-url';
        try {
            $message = Utility::fetchLinkMessage($lang, VW::RPT, 'daily_pos_unavailable')
                ?? 'Daily pos route is unavailable. Please contact technical support or your domain administrator.';
        } catch (\Throwable $e) {
            \Log::error('reports/monthly_pos — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp
    <div class="row">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::MT2 }}" >
                <div class="card">
                    <div class="{{ VC::CD_BD }}">
                        {{ Form::open([
                            'url' => Route::has(VW::RPT . '.monthly.pos') ? route(VW::RPT . '.monthly.pos') : '#',
                            'method' => 'GET',
                            'id' => 'monthly_pos_report_submit',
                            'data-url' => Route::has(VW::RPT . '.monthly.pos') ? route(VW::RPT . '.monthly.pos') : '#'
                        ]) }}
                        <div class="{{ VC::R_FLX_ALC_JCE }}">
                            <div class="{{ VC::CL_XLG4 }}">
                                <div class="btn-box">
                                    {{ Form::label('year', __('Year'), ['class' => 'form-label']) }}
                                    {{ Form::select('year', $yearList, isset($_GET['year']) ? $_GET['year'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="{{ VC::CL_POS3 }}">
                                <div class="btn-box">
                                    {{ Form::label('warehouse', __('Warehouse'), ['class' => 'form-label']) }}
                                    {{ Form::select('warehouse', $warehouse, isset($_GET['warehouse']) ? $_GET['warehouse'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="{{ VC::CL_POS3 }}">
                                <div class="btn-box">
                                    {{ Form::label('customer', __('Customer'), ['class' => 'form-label']) }}
                                    {{ Form::select('customer', $customer, isset($_GET['customer']) ? $_GET['customer'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="{{ VC::C_AT_FEND }}">
                                <a
                                    id="monthly-pos-apply"
                                    href="#"
                                    class="{{ VC::BT_SM_PM }}"
                                    data-toggle="tooltip"
                                    data-original-title="{{ __('apply') }}"
                                >
                                    <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                </a>
                                <a
                                    id="monthly-pos-reset"
                                    href="{{ Route::has(VW::RPT . '.monthly.pos') ? route(VW::RPT . '.monthly.pos') : '#' }}"
                                    data-url="{{ Route::has(VW::RPT . '.monthly.pos') ? route(VW::RPT . '.monthly.pos') : '#' }}"
                                    class="{{ VC::BT_SM_DG }}"
                                    data-toggle="tooltip"
                                    data-original-title="{{ __('Reset') }}"
                                >
                                    <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                </a>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                    @php
                        $flagApply ??= 'data-monthlyPosApply-listener-added';
                        $flagReset ??= 'data-monthlyPosReset-listener-added';
                        $guardAttr ??= 'data-url';
                        $urlAttr ??= 'data-url';
                        try {
                            $message = Utility::fetchLinkMessage($lang, VW::RPT, 'monthly_pos_unavailable')
                                ?? 'Monthly pos route is unavailable. Please contact technical support or your domain administrator.';
                        } catch (\Throwable $e) {
                            \Log::error('reports/monthly_pos — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp
                </div>
            </div>
        </div>
    </div>
    <div id="printableArea">
        <div class="row mt-0">
            <div class="col">
                <input type="hidden" value="{{$filter['warehouse'].' '.__('Monthly Pos').' '.'Report of'.' '.$filter['startMonth'].' to '.$filter['endMonth']}}" id="filename">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{__('Report')}} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{__('Monthly Pos Report')}}</h6>
                </div>
            </div>
            @if(!empty($filter['warehouse']))
                <div class="col">
                    <div class="{{ VC::CD_POS }}">
                        <h7 class="{{ VC::RPT_TX_GR }}">{{__('Warehouse')}} :</h7>
                        <h6 class="{{ VC::RPT_TX_DEF }}">{{$filter['warehouse']}}</h6>
                    </div>
                </div>
            @endif
            @if(!empty($filter['customer']))
                <div class="col">
                    <div class="{{ VC::CD_POS }}">
                        <h7 class="{{ VC::RPT_TX_GR }}">{{__('Customer')}} :</h7>
                        <h6 class="{{ VC::RPT_TX_DEF }}">{{$filter['customer']}}</h6>
                    </div>
                </div>
            @endif
            <div class="col">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{__('Duration')}} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{$filter['startMonth'].' to '.$filter['endMonth']}}</h6>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="{{ VC::C12 }}">
                <div class="card">
                    <div class="setting-tab">
                        <div class="tab-content">
                            <div class="{{ VC::TAB_FD_SH }} active" id="monthly-chart" role="tabpanel">
                                <div class="{{ VC::CL12 }}">
                                    <div class="{{ VC::CD_HD }}">
                                        <div class="row">
                                            <div class="{{ VC::C6 }}">
                                                <h6>{{ __('Monthly Report') }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CD_BD }}">
                                        <div id="monthly-pos"></div>
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

@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
    <script async type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/pos/monthly/printable.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/pos/monthly/download.js') }}"></script>
    <script defer>
        (() => {
          const BS_LINK = 'link[href*="bootstrap"]';
          const CHART_CONTAINER = '#monthly-pos';
          const translations = {
            ar: { chart_fail: "فشل عرض مخطط نقاط البيع الشهري" },
            da: { chart_fail: "Kunne ikke vise månedligt POS-diagram" },
            de: { chart_fail: "Monatliches POS-Diagramm konnte nicht angezeigt werden" },
            en: { chart_fail: "Failed to render monthly POS chart" },
            es: { chart_fail: "Error al mostrar gráfico POS mensual" },
            fr: { chart_fail: "Échec de l'affichage du diagramme POS mensuel" },
            he: { chart_fail: "נכשל בהצגת תרשים קופה חודשי" },
            it: { chart_fail: "Impossibile visualizzare il grafico POS mensile" },
            ja: { chart_fail: "月次POSチャートの表示に失敗しました" },
            nl: { chart_fail: "Maandelijkse POS-grafiek weergeven mislukt" },
            pl: { chart_fail: "Nie udało się wyświetlić miesięcznego wykresu POS" },
            pt: { chart_fail: "Falha ao exibir gráfico POS mensal" },
            "pt-br": { chart_fail: "Falha ao exibir gráfico POS mensal" },
            ru: { chart_fail: "Не удалось отобразить ежемесячную POS-диаграмму" },
            tr: { chart_fail: "Aylık POS grafiği oluşturulamadı" },
            zh: { chart_fail: "无法渲染月度POS图表" }
          };

          const RG = window.RouteGuard || {};
          const showError = (key) => {
            const msg = RG.getMsg ? RG.getMsg(key, document.body) : (window.translations?.[((sessionStorage.getItem('erp-np-lang') || document.documentElement.lang || 'en').toLowerCase().replace(/_/g,'-').slice(0,2))]?.[key] || '# ERROR');
            (RG.showToast || (m => alert(m)))(msg);
          };

          const renderChart = () => {
            try {
              const chartContainer = document.querySelector(CHART_CONTAINER);
              if (!chartContainer) return;

              const data = JSON.parse(JSON.stringify({!! json_encode($data) !!})) ?? [];
              const categories = JSON.parse(JSON.stringify({!! json_encode($monthList) !!})) ?? [];

              if (!data.length || !categories.length) {
                showError('chart_fail');
                return;
              }

              if (typeof ApexCharts === 'undefined') {
                showError('chart_fail');
                return;
              }

              const chartOptions = {
                series: [{ name: '{{ __("Pos") }}', data }],
                chart: {
                  height: 300,
                  type: 'area',
                  dropShadow: {
                    enabled: true,
                    color: '#000',
                    top: 18,
                    left: 7,
                    blur: 10,
                    opacity: 0.2
                  },
                  toolbar: { show: false },
                  events: {
                    destroyed: () => {
                      if (chartContainer.chart) delete chartContainer.chart;
                    }
                  }
                },
                dataLabels: { enabled: false },
                stroke: { width: 2, curve: 'smooth' },
                title: { text: '' },
                xaxis: { categories, title: { text: '{{ __("Months") }}' } },
                colors: ['#6fd944'],
                grid: { strokeDashArray: 4 },
                legend: { show: false },
                yaxis: { title: { text: '{{ __("Amount") }}' } }
              };

              if (chartContainer.chart) chartContainer.chart.destroy();

              chartContainer.chart = new ApexCharts(chartContainer, chartOptions);
              chartContainer.chart.render();
            } catch (e) {
              showError('chart_fail');
            }
          };

          const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
              mutation.removedNodes.forEach(node => {
                if (node === document.querySelector(CHART_CONTAINER)) {
                  if (node.chart) {
                    node.chart.destroy();
                    delete node.chart;
                  }
                }
              });
            });
          });

          observer.observe(document.body, { childList: true, subtree: true });

          if (document.readyState !== 'loading') {
            renderChart();
          } else {
            document.addEventListener('DOMContentLoaded', renderChart);
          }
        })();
    </script>
    <script defer>
        (() => {
        const FLAG_ATTR = @json($flagAttr);
        const GUARD_ATTR = @json($guardAttr);
        const URL_ATTR = @json($urlAttr);
        const TAB_ID = "pills-home-tab";

        const showPermissionError = (message) => {
            if (window.bootstrap?.Toast) {
            const toastEl = document.querySelector(".toast");
            if (toastEl) {
                try {
                const body = toastEl.querySelector(".toast-body");
                if (body) body.textContent = message;
                new bootstrap.Toast(toastEl, {autohide: true, delay: 5000}).show();
                } catch {}
            } else {
                alert(message);
            }
            } else {
            alert(message);
            }
        };

        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
            mutation.removedNodes.forEach(node => {
                if (node.id === TAB_ID && node.eventHandler) {
                node.removeEventListener("click", node.eventHandler);
                delete node.eventHandler;
                }
            });
            });
        });

        try {
            const el = document.getElementById(TAB_ID);
            if (!el) return;

            if (el.getAttribute(FLAG_ATTR) === "true") return;
            el.setAttribute(FLAG_ATTR, "true");

            observer.observe(document.body, { childList: true, subtree: true });

            const dataUrl = el.getAttribute(GUARD_ATTR) ?? el.getAttribute(URL_ATTR);
            const hrefAttr = el.getAttribute("href");
            const url = dataUrl ?? hrefAttr;

            if (dataUrl === "#" && hrefAttr === "#") {
            showPermissionError(@json($message));
            return;
            }

            const handler = (e) => {
            e.preventDefault();
            window.location.href = url;
            };
            el.eventHandler = handler;
            el.addEventListener("click", handler);
        } catch {}
        })();
    </script>
    <script defer>
        (() => {
        const FLAG_ATTR_APPLY = @json($flagApply);
        const FLAG_ATTR_RESET = @json($flagReset);
        const GUARD_ATTR = @json($guardAttr);
        const URL_ATTR = @json($urlAttr);
        const APPLY_ID = "monthly-pos-apply";
        const RESET_ID = "monthly-pos-reset";
        const FORM_ID = "monthly_pos_report_submit";

        const showPermissionError = (message) => {
            if (window.bootstrap?.Toast) {
            const toastEl = document.querySelector(".toast");
            if (toastEl) {
                try {
                const body = toastEl.querySelector(".toast-body");
                if (body) body.textContent = message;
                new bootstrap.Toast(toastEl, {autohide: true, delay: 5000}).show();
                } catch {}
            } else {
                alert(message);
            }
            } else {
            alert(message);
            }
        };

        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
            mutation.removedNodes.forEach(node => {
                if (node.id === APPLY_ID && node.applyHandler) {
                node.removeEventListener("click", node.applyHandler);
                delete node.applyHandler;
                }
                if (node.id === RESET_ID && node.resetHandler) {
                node.removeEventListener("click", node.resetHandler);
                delete node.resetHandler;
                }
            });
            });
        });

        try {
            observer.observe(document.body, { childList: true, subtree: true });

            const applyEl = document.getElementById(APPLY_ID);
            if (applyEl && applyEl.getAttribute(FLAG_ATTR_APPLY) !== "true") {
            applyEl.setAttribute(FLAG_ATTR_APPLY, "true");

            const formEl = document.getElementById(FORM_ID);
            if (!formEl) return;

            const dataUrl = formEl.getAttribute(GUARD_ATTR) ?? formEl.getAttribute(URL_ATTR);
            const actionAttr = formEl.getAttribute("action");
            const url = dataUrl ?? actionAttr;

            if (dataUrl === "#" && actionAttr === "#") {
                showPermissionError(@json($message));
            } else {
                const handler = (e) => {
                e.preventDefault();
                const params = new URLSearchParams(new FormData(formEl)).toString();
                window.location.href = url + (params ? `?${params}` : "");
                };
                applyEl.applyHandler = handler;
                applyEl.addEventListener("click", handler);
            }
            }

            const resetEl = document.getElementById(RESET_ID);
            if (resetEl && resetEl.getAttribute(FLAG_ATTR_RESET) !== "true") {
            resetEl.setAttribute(FLAG_ATTR_RESET, "true");

            const dataUrl = resetEl.getAttribute(GUARD_ATTR) ?? resetEl.getAttribute(URL_ATTR);
            const hrefAttr = resetEl.getAttribute("href");
            const url = dataUrl ?? hrefAttr;

            if (dataUrl === "#" && hrefAttr === "#") {
                showPermissionError(@json($message));
            } else {
                const handler = (e) => {
                e.preventDefault();
                window.location.href = url;
                };
                resetEl.resetHandler = handler;
                resetEl.addEventListener("click", handler);
            }
            }
        } catch {}
        })();
    </script>
@endpush
