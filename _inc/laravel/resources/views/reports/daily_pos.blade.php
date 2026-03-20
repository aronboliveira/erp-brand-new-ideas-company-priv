@php
    $data ??= [];
    $warehouse ??= $warehouses ?? [];
    $customer ??= $customers ?? [];
    try {
$lang = Utility::fetchUserLang();
    } catch (\Throwable $e) {
        \Log::error('reports/daily_pos — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
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
        <a href="#" id="download-pdf-link" class="{{ VC::BT_SM_PM }} download-pdf-link"
        data-sv-localized="true" data-func-name="saveAsPDF" data-guard-msg="{{ base64_encode(Utility::fetchLinkMessage($lang,VW::RPT,'download_daily_pos_unavailable') ?? 'Download function for daily POS is unavailable. Please contact technical support or your domain administrator.') }}" data-bs-toggle="tooltip" title="{{ __('Download') }}" data-original-title="{{ __('Download') }}">
            <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
        </a>
        @push(StacksConstants::ADM_SCR_PG)
            <script src="{{ asset('assets/js/routes/pos/daily/download.js') }}" defer></script>
        @endpush
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <ul class="{{ VC::NAV_PL_Y3 }}" id="pills-tab" role="tablist">
        <li class="{{ VC::NV_IT }}">
            <a class="{{ VC::NV_LK }} active" id="pills-home-tab" data-bs-toggle="pill" href="#daily-chart" role="tab"
            aria-controls="pills-home" aria-selected="true">{{ __('Daily') }}</a>
        </li>
        @php
            try {
                $monthlyRouteName = VW::RPT.'.monthly.pos';
                $monthlyUrl = Route::has($monthlyRouteName) ? route($monthlyRouteName) : '#';
                $linkId = 'monthly-pos-link';
            } catch (\Throwable $e) {
                \Log::error('reports/daily_pos — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <li class="{{ VC::NV_IT }}">
            <a
                href="{{ $monthlyUrl }}"
                id="{{ $linkId }}"
                class="{{ VC::NV_LK }} {{ $linkId }}"
                data-url="{{ $monthlyUrl }}"
                data-sv-localized="true"
                data-guard-msg="{{ base64_encode(Utility::fetchLinkMessage($lang,VW::POS,'monthly_pos_unavailable') ?? 'Monthly route is unavailable. Please contact technical support or your domain administrator.') }}"
                data-bs-toggle="pill"
                role="tab"
                aria-controls="pills-profile"
                aria-selected="false"
            >
                {{ __('Monthly') }}
            </a>
        </li>
    </ul>
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::MT2 }}">
                <div class="{{ VC::CD }}">
                    @php
                        try {
                            $dailyBase            = VW::RPT.'.daily.pos';
                            $dailyKebab           = Str::kebab($dailyBase);
                            $dailyResolved        = Route::has($dailyBase) ? $dailyBase : (Route::has($dailyKebab) ? $dailyKebab : null);
                            $dailyUrl             = $dailyResolved ? route($dailyResolved) : '#';
                            $formId               = 'daily_pos_report_submit';
                            $applyClass           = 'apply-daily-pos-link';
                            $resetClass           = 'reset-daily-pos-link';
                            $applyGuardMsg        = Utility::fetchLinkMessage($lang, VW::POS, 'daily_apply_pos_route_unavailable') ?? 'Daily POS apply route is unavailable. Please contact technical support or your domain administrator.';
                            $resetGuardMsg        = Utility::fetchLinkMessage($lang, VW::POS, 'daily_reset_pos_route_unavailable') ?? 'Daily POS reset route is unavailable. Please contact technical support or your domain administrator.';
                        } catch (\Throwable $e) {
                            \Log::error('reports/daily_pos — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp
                    <div class="{{ VC::CD_BD }}">
                        {!! Form::open([
                            'method'            => 'GET',
                            'url'               => $dailyUrl,
                            'id'                => $formId,
                            'data-url'          => $dailyUrl,
                            'data-guard-msg'    => $applyGuardMsg,
                            'data-sv-localized' => 'true',
                        ]) !!}
                            <div class="{{ VC::R_FLX_ALC_JCE }}">
                                <div class="{{ VC::CL_POS1 }}">
                                    <div class="btn-box">
                                        {{ Form::label('start_date',__('Start Date'),['class'=> VC::FM_LB]) }}
                                        {{ Form::date('start_date',isset($_GET['start_date'])?$_GET['start_date']:'',[ 'class'=> VC::FM_CT.' month-btn']) }}
                                    </div>
                                </div>
                                <div class="{{ VC::CL_POS2 }}">
                                    <div class="btn-box">
                                        {{ Form::label('end_date',__('End Date'),['class'=> VC::FM_LB]) }}
                                        {{ Form::date('end_date',isset($_GET['end_date'])?$_GET['end_date']:'',[ 'class'=> VC::FM_CT.' month-btn']) }}
                                    </div>
                                </div>
                                <div class="{{ VC::CL_POS3 }}">
                                    <div class="btn-box">
                                        {{ Form::label('warehouse',__('Warehouse'),['class'=> VC::FM_LB]) }}
                                        {{ Form::select('warehouse',$warehouse,isset($_GET['warehouse'])?$_GET['warehouse']:'',[ 'class'=> VC::FM_CT_SL ]) }}
                                    </div>
                                </div>
                                <div class="{{ VC::CL_POS3 }}">
                                    <div class="btn-box">
                                        {{ Form::label('customer',__('Customer'),['class'=> VC::FM_LB]) }}
                                        {{ Form::select('customer',$customer,isset($_GET['customer'])?$_GET['customer']:'',[ 'class'=> VC::FM_CT_SL ]) }}
                                    </div>
                                </div>
                                <div class="{{ VC::C_AT_FEND }}">
                                    <a href="#"
                                    id="{{ $applyClass }}"
                                    class="{{ VC::BT_SM_PM }} {{ $applyClass }}"
                                    data-form-id="{{ $formId }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ base64_encode($applyGuardMsg) }}"
                                    data-bs-toggle="tooltip"
                                    title="{{ __('Apply') }}"
                                    data-original-title="{{ __('apply') }}">
                                        <span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
                                    </a>
                                    <a href="{{ $dailyUrl }}"
                                    id="{{ $resetClass }}"
                                    class="{{ VC::BT_SM_DG }} {{ $resetClass }}"
                                    data-url="{{ $dailyUrl }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ base64_encode($resetGuardMsg) }}"
                                    data-bs-toggle="tooltip"
                                    title="{{ __('Reset') }}"
                                    data-original-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon"><i class="{{ VC::TI_TRS_OFF }}"></i></span>
                                    </a>
                                </div>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="printableArea">
        <div class="{{ VC::RW }} mt-0">
            <div class="col">
                <input type="hidden" value="{{$filter['warehouse'].' '.__('Daily Pos').' '.'Report of'.' '.$filter['startDate'].' to '.$filter['endDate']}}" id="filename">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{__('Report')}} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{__('Daily Pos Report')}}</h6>
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
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{$filter['startDate'].' to '.$filter['endDate']}}</h6>
                </div>
            </div>
        </div>

        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::CD }}">
                    <div class="setting-tab">
                        <div class="tab-content">
                            <div class="{{ VC::TAB_FD_SH }} active" id="daily-chart" role="tabpanel">
                                <div class="{{ VC::CL12 }}">
                                    <div class="{{ VC::CD_HD }}">
                                        <div class="{{ VC::RW }}">
                                            <div class="{{ VC::C6 }}">
                                                <h6>{{ __('Daily Report') }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="{{ VC::CD_BD }}">
                                        <div id="daily-pos"></div>
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
    <script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/pos/daily/download.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/pos/daily/printable.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/reports/monthly/pos.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/reports/pos/daily/apply.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/reports/pos/daily/reset.js') }}"></script>
    <script async>
        (() => {
          const BS_LINK = 'link[href*="bootstrap"]';
          const CHART_CONTAINER = '#daily-pos';
          const translations = {
            ar: { chart_fail: "فشل عرض مخطط نقاط البيع اليومي" },
            da: { chart_fail: "Kunne ikke vise dagligt POS-diagram" },
            de: { chart_fail: "Tägliches POS-Diagramm konnte nicht angezeigt werden" },
            en: { chart_fail: "Failed to render daily POS chart" },
            es: { chart_fail: "Error al mostrar gráfico POS diario" },
            fr: { chart_fail: "Échec de l'affichage du diagramme POS quotidien" },
            he: { chart_fail: "נכשל בהצגת תרשים קופה יומי" },
            it: { chart_fail: "Impossibile visualizzare il grafico POS giornaliero" },
            ja: { chart_fail: "デイリーPOSチャートの表示に失敗しました" },
            nl: { chart_fail: "Dagelijkse POS-grafiek weergeven mislukt" },
            pl: { chart_fail: "Nie udało się wyświetlić dziennego wykresu POS" },
            pt: { chart_fail: "Falha ao exibir gráfico POS diário" },
            "pt-br": { chart_fail: "Falha ao exibir gráfico POS diário" },
            ru: { chart_fail: "Не удалось отобразить ежедневную POS-диаграмму" },
            tr: { chart_fail: "Günlük POS grafiği oluşturulamadı" },
            zh: { chart_fail: "无法渲染每日POS图表" }
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
              toast.setAttribute('role', 'alert');
              toast.setAttribute('aria-live', 'assertive');
              toast.setAttribute('aria-atomic', 'true');
              toast.innerHTML = `<div class="{{ VC::DFL }}"><div class="toast-body">${msg}</div><button type="button" class="{{ VC::BT_CL }} btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div>`;
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
              const categories = JSON.parse(JSON.stringify({!! json_encode($arrDuration) !!})) ?? [];

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
                xaxis: { categories, title: { text: '{{ __("Days") }}' } },
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
@endpush
