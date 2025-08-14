@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;

    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Pos')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Daily Pos Report') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can('download report')
            @php
                $funcName  = 'saveAsPDF';
                $guardMsg  = Utility::fetchLinkMessage($lang, ViewsConstants::RPT, 'download_montly_pos_unavailable') ?? 'Download function for monthly POS is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a href="#" id="download-report-link" class="{{ ViewClassNamesConstants::BT_SM_PM }} download-report-link" data-func-name="{{ $funcName }}" data-sv-localized="true" data-guard-msg="{{ $guardMsg }}" data-bs-toggle="tooltip" title="{{ __('Download') }}" data-original-title="{{ __('Download') }}">
                <span class="btn-inner--icon"><i class="{{ ViewClassNamesConstants::TI_DWN }}"></i></span>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <ul class="{{ ViewClassNamesConstants::NAV_PL_Y3 }}" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a
                class="nav-link"
                id="pills-home-tab"
                data-bs-toggle="pill"
                href="{{ Route::has(ViewsConstants::RPT . '.daily.pos') ? route(ViewsConstants::RPT . '.daily.pos') : '#' }}"
                data-url="{{ Route::has(ViewsConstants::RPT . '.daily.pos') ? route(ViewsConstants::RPT . '.daily.pos') : '#' }}"
                role="tab"
                aria-controls="pills-home"
                aria-selected="true"
            >
                {{ __('Daily') }}
            </a>
        </li>
        <li class="nav-item">
            <a
                class="nav-link active"
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
        $flagAttr = 'data-dailyPos-listener-added';
        $guardAttr = 'data-url';
        $urlAttr = 'data-url';
        $message = Utility::fetchLinkMessage($lang, ViewsConstants::RPT, 'daily_pos_unavailable')
            ?? 'Daily pos route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" >
                <div class="card">
                    <div class="card-body">
                        {{ Collective\Html\FormFacade::open([
                            'url' => Route::has(ViewsConstants::RPT . '.monthly.pos') ? route(ViewsConstants::RPT . '.monthly.pos') : '#',
                            'method' => 'GET',
                            'id' => 'monthly_pos_report_submit',
                            'data-url' => Route::has(ViewsConstants::RPT . '.monthly.pos') ? route(ViewsConstants::RPT . '.monthly.pos') : '#'
                        ]) }}
                        <div class="{{ ViewClassNamesConstants::R_FLX_ALC_JCE }}">
                            <div class="{{ ViewClassNamesConstants::CL_XLG4 }}">
                                <div class="btn-box">
                                    {{ Collective\Html\FormFacade::label('year', __('Year'), ['class' => 'form-label']) }}
                                    {{ Collective\Html\FormFacade::select('year', $yearList, isset($_GET['year']) ? $_GET['year'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="{{ ViewClassNamesConstants::CL_POS3 }}">
                                <div class="btn-box">
                                    {{ Collective\Html\FormFacade::label('warehouse', __('Warehouse'), ['class' => 'form-label']) }}
                                    {{ Collective\Html\FormFacade::select('warehouse', $warehouse, isset($_GET['warehouse']) ? $_GET['warehouse'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="{{ ViewClassNamesConstants::CL_POS3 }}">
                                <div class="btn-box">
                                    {{ Collective\Html\FormFacade::label('customer', __('Customer'), ['class' => 'form-label']) }}
                                    {{ Collective\Html\FormFacade::select('customer', $customer, isset($_GET['customer']) ? $_GET['customer'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="{{ ViewClassNamesConstants::C_AT_FEND }}">
                                <a
                                    id="monthly-pos-apply"
                                    href="#"
                                    class="{{ ViewClassNamesConstants::BT_SM_PM }}"
                                    data-toggle="tooltip"
                                    data-original-title="{{ __('apply') }}"
                                >
                                    <span class="btn-inner--icon"><i class="{{ ViewClassNamesConstants::TI_SRC }}"></i></span>
                                </a>
                                <a
                                    id="monthly-pos-reset"
                                    href="{{ Route::has(ViewsConstants::RPT . '.monthly.pos') ? route(ViewsConstants::RPT . '.monthly.pos') : '#' }}"
                                    data-url="{{ Route::has(ViewsConstants::RPT . '.monthly.pos') ? route(ViewsConstants::RPT . '.monthly.pos') : '#' }}"
                                    class="{{ ViewClassNamesConstants::BT_SM_DG }}"
                                    data-toggle="tooltip"
                                    data-original-title="{{ __('Reset') }}"
                                >
                                    <span class="btn-inner--icon"><i class="{{ ViewClassNamesConstants::TI_TRS_OFF }}"></i></span>
                                </a>
                            </div>
                        </div>
                        {{ Collective\Html\FormFacade::close() }}
                    </div>
                    @php
                        $flagApply = 'data-monthlyPosApply-listener-added';
                        $flagReset = 'data-monthlyPosReset-listener-added';
                        $guardAttr = 'data-url';
                        $urlAttr = 'data-url';
                        $message = Utility::fetchLinkMessage($lang, ViewsConstants::RPT, 'monthly_pos_unavailable') 
                            ?? 'Monthly pos route is unavailable. Please contact technical support or your domain administrator.';
                    @endphp              
                </div>
            </div>
        </div>
    </div>
    <div id="printableArea">
        <div class="row mt-0">
            <div class="col">
                <input type="hidden" value="{{$filter['warehouse'].' '.__('Monthly Pos').' '.'Report of'.' '.$filter['startMonth'].' to '.$filter['endMonth']}}" id="filename">
                <div class="{{ ViewClassNamesConstants::CD_POS }}">
                    <h7 class="{{ ViewClassNamesConstants::RPT_TX_GR }}">{{__('Report')}} :</h7>
                    <h6 class="{{ ViewClassNamesConstants::RPT_TX_DEF }}">{{__('Monthly Pos Report')}}</h6>
                </div>
            </div>
            @if(!empty($filter['warehouse']))
                <div class="col">
                    <div class="{{ ViewClassNamesConstants::CD_POS }}">
                        <h7 class="{{ ViewClassNamesConstants::RPT_TX_GR }}">{{__('Warehouse')}} :</h7>
                        <h6 class="{{ ViewClassNamesConstants::RPT_TX_DEF }}">{{$filter['warehouse']}}</h6>
                    </div>
                </div>
            @endif
            @if(!empty($filter['customer']))
                <div class="col">
                    <div class="{{ ViewClassNamesConstants::CD_POS }}">
                        <h7 class="{{ ViewClassNamesConstants::RPT_TX_GR }}">{{__('Customer')}} :</h7>
                        <h6 class="{{ ViewClassNamesConstants::RPT_TX_DEF }}">{{$filter['customer']}}</h6>
                    </div>
                </div>
            @endif
            <div class="col">
                <div class="{{ ViewClassNamesConstants::CD_POS }}">
                    <h7 class="{{ ViewClassNamesConstants::RPT_TX_GR }}">{{__('Duration')}} :</h7>
                    <h6 class="{{ ViewClassNamesConstants::RPT_TX_DEF }}">{{$filter['startMonth'].' to '.$filter['endMonth']}}</h6>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="setting-tab">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="monthly-chart" role="tabpanel">
                                <div class="col-lg-12">
                                    <div class="card-header">
                                        <div class="row">
                                            <div class="col-6">
                                                <h6>{{ __('Monthly Report') }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
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
        
          let toastContainer = null;
          const getToastContainer = () => {
            if (!toastContainer) {
              toastContainer = document.querySelector('.toast-container') || document.createElement('div');
              toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
              if (!toastContainer.isConnected) document.body.append(toastContainer);
            }
            return toastContainer;
          };
        
          const showError = (key) => {
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            let msg = errFb;
            if (el.getAttribute("data-sv-localized") === "true" || el.getAttribute(dataClientLocalized) === "true")
            msg = el.getAttribute(dataGuardMsg) || errFb;
            else {
            let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en")
                .toLowerCase()
                .replace(/_/g, "-");
            lang = lang === "pt-br" ? lang : lang.slice(0, 2);
            const msgKey = key;
            msg =
                window.translations?.[lang]?.[msgKey] ||
                el.getAttribute(dataGuardMsg) ||
                window.translations?.["en"]?.[msgKey] ||
                errFb;
            if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, "true");
            }
            }
            const hasBootstrap = document.querySelector(BS_LINK) && window.bootstrap?.Toast;
            
            if (hasBootstrap) {
              const container = getToastContainer();
              const toast = document.createElement('div');
              toast.className = 'toast align-items-center text-bg-danger border-0';
              toast.setAttribute('role','alert');
              toast.setAttribute('aria-live','assertive');
              toast.setAttribute('aria-atomic','true');
              toast.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
              container.append(toast);
              new bootstrap.Toast(toast, {autohide: true, delay: 5000}).show();
            } else {
              alert(msg);
            }
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