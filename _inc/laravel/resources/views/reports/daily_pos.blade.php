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
        <a href="#" id="download-pdf-link" class="{{ ViewClassNamesConstants::BT_SM_PM }} download-pdf-link" 
        data-sv-localized="true" data-func-name="saveAsPDF" data-guard-msg="{{ Utility::fetchLinkMessage($lang,ViewsConstants::RPT,'download_daily_pos_unavailable') ?? 'Download function for daily POS is unavailable. Please contact technical support or your domain administrator.' }}" data-bs-toggle="tooltip" title="{{ __('Download') }}" data-original-title="{{ __('Download') }}">
            <span class="btn-inner--icon"><i class="{{ ViewClassNamesConstants::TI_DWN }}"></i></span>
        </a>
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <ul class="{{ ViewClassNamesConstants::NAV_PL_Y3 }}" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" href="#daily-chart" role="tab"
               aria-controls="pills-home" aria-selected="true">{{ __('Daily') }}</a>
        </li>
        @php
            $monthlyRouteName = ViewsConstants::RPT.'.monthly.pos';
            $monthlyUrl = Route::has($monthlyRouteName) ? route($monthlyRouteName) : '#';
            $linkId = 'monthly-pos-link';
        @endphp
        <li class="nav-item">
            <a
                href="{{ $monthlyUrl }}"
                id="{{ $linkId }}"
                class="nav-link {{ $linkId }}"
                data-url="{{ $monthlyUrl }}"
                data-sv-localized="true"
                data-guard-msg="{{ Utility::fetchLinkMessage($lang,ViewsConstants::POS,'monthly_pos_unavailable') ?? 'Monthly route is unavailable. Please contact technical support or your domain administrator.' }}"
                data-bs-toggle="pill"
                role="tab"
                aria-controls="pills-profile"
                aria-selected="false"
            >
                {{ __('Monthly') }}
            </a>
        </li>

    </ul>
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" >
                <div class="card">
                    @php
                        $dailyRouteName = ViewsConstants::RPT.'.daily.pos';
                        $dailyRoute = Route::has($dailyRouteName) ? [$dailyRouteName] : ['#'];
                        $dailyUrl = Route::has($dailyRouteName) ? route($dailyRouteName) : '#';
                        $formId = 'daily_pos_report_submit';
                        $applyClass = 'apply-daily-pos-link';
                        $resetClass = 'reset-daily-pos-link';
                    @endphp
                    <div class="card-body">
                        {!! Collective\Html\FormFacade::open(['route'=>$dailyRoute,'method'=>'GET','id'=>$formId]) !!}
                            <div class="{{ ViewClassNamesConstants::R_FLX_ALC_JCE }}">
                                <div class="{{ ViewClassNamesConstants::CL_POS1 }}">
                                    <div class="btn-box">
                                        {{ Collective\Html\FormFacade::label('start_date',__('Start Date'),['class'=>'form-label']) }}
                                        {{ Collective\Html\FormFacade::date('start_date',isset($_GET['start_date'])?$_GET['start_date']:'',['class'=>'form-control month-btn']) }}
                                    </div>
                                </div>
                                <div class="{{ ViewClassNamesConstants::CL_POS2 }}">
                                    <div class="btn-box">
                                        {{ Collective\Html\FormFacade::label('end_date',__('End Date'),['class'=>'form-label']) }}
                                        {{ Collective\Html\FormFacade::date('end_date',isset($_GET['end_date'])?$_GET['end_date']:'',['class'=>'form-control month-btn']) }}
                                    </div>
                                </div>
                                <div class="{{ ViewClassNamesConstants::CL_POS3 }}">
                                    <div class="btn-box">
                                        {{ Collective\Html\FormFacade::label('warehouse',__('Warehouse'),['class'=>'form-label']) }}
                                        {{ Collective\Html\FormFacade::select('warehouse',$warehouse,isset($_GET['warehouse'])?$_GET['warehouse']:'',['class'=>'form-control select']) }}
                                    </div>
                                </div>
                                <div class="{{ ViewClassNamesConstants::CL_POS3 }}">
                                    <div class="btn-box">
                                        {{ Collective\Html\FormFacade::label('customer',__('Customer'),['class'=>'form-label']) }}
                                        {{ Collective\Html\FormFacade::select('customer',$customer,isset($_GET['customer'])?$_GET['customer']:'',['class'=>'form-control select']) }}
                                    </div>
                                </div>
                                <div class="{{ ViewClassNamesConstants::C_AT_FEND }}">
                                    <a href="#" id="{{ $applyClass }}" class="{{ ViewClassNamesConstants::BT_SM_PM }} {{ $applyClass }}" data-url="{{ $dailyUrl }}" data-sv-localized="true" data-guard-msg="{{ Utility::fetchLinkMessage($lang,ViewsConstants::POS,'daily_apply_pos_unavailable') ?? 'Daily POS apply route is unavailable. Please contact technical support or your domain administrator.' }}" onclick="document.getElementById('{{ $formId }}').submit();return false;" data-toggle="tooltip" data-original-title="{{ __('apply') }}">
                                        <span class="btn-inner--icon"><i class="{{ ViewClassNamesConstants::TI_SRC }}"></i></span>
                                    </a>
                                    <a href="{{ $dailyUrl }}" id="{{ $resetClass }}" class="{{ ViewClassNamesConstants::BT_SM_DG }} {{ $resetClass }}" data-url="{{ $dailyUrl }}" data-sv-localized="true" data-guard-msg="{{ Utility::fetchLinkMessage($lang,ViewsConstants::POS,'daily_reset_pos_unavailable') ?? 'Daily POS reset route is unavailable. Please contact technical support or your domain administrator.' }}" data-toggle="tooltip" data-original-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon"><i class="{{ ViewClassNamesConstants::TI_TRS_OFF }}"></i></span>
                                    </a>
                                </div>
                            </div>
                        {!! Collective\Html\FormFacade::close() !!}
                    </div>             
                </div>
            </div>
        </div>
    </div>
    <div id="printableArea">
        <div class="row mt-0">
            <div class="col">
                <input type="hidden" value="{{$filter['warehouse'].' '.__('Daily Pos').' '.'Report of'.' '.$filter['startDate'].' to '.$filter['endDate']}}" id="filename">
                <div class="{{ ViewClassNamesConstants::CD_POS }}">
                    <h7 class="{{ ViewClassNamesConstants::RPT_TX_GR }}">{{__('Report')}} :</h7>
                    <h6 class="{{ ViewClassNamesConstants::RPT_TX_DEF }}">{{__('Daily Pos Report')}}</h6>
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
                    <h6 class="{{ ViewClassNamesConstants::RPT_TX_DEF }}">{{$filter['startDate'].' to '.$filter['endDate']}}</h6>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="setting-tab">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="daily-chart" role="tabpanel">
                                <div class="col-lg-12">
                                    <div class="card-header">
                                        <div class="row">
                                            <div class="col-6">
                                                <h6>{{ __('Daily Report') }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
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


