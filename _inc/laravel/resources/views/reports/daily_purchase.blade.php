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
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Purchase')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Daily Purchase Report') }}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can('download')
            @php
                $downloadGuardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'download_daily_purchase_unavailable') ?? 'Download function for daily purchases is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a href="#"
            id="download-pdf-link"
            class="{{ VW::BT_SM_PM }} download-daily-purchase"
            data-func-name="saveAsPDF"
            data-guard-msg="{{ $downloadGuardMsg }}"
            data-sv-localized="true"
            data-bs-toggle="tooltip"
            title="{{ __('Download') }}"
            data-original-title="{{ __('Download') }}">
                <span class="btn-inner--icon"><i class="{{ VW::TI_DWN }}"></i></span>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script src="{{ asset('assets/js/routes/reports/purchases/daily/download.js') }}" defer></script>
            @endpush
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    @php
        $monthlyPurchaseUrl = Route::has(VW::RPT . '.monthly.purchase') ? route(VW::RPT . '.monthly.purchase') : '#';
    @endphp
    <ul class="{{ VW::NAV_PL_Y3 }}" id="pills-tab" role="tablist">
        <li class="nav-item">
            <a
                class="nav-link active"
                id="pills-home-tab"
                data-bs-toggle="pill"
                href="#daily-chart"
                role="tab"
                aria-controls="pills-home"
                aria-selected="true"
            >
                {{ __('Daily') }}
            </a>
        </li>
        <li class="nav-item">
            <a
                class="nav-link"
                id="pills-profile-tab"
                data-bs-toggle="pill"
                href="{{ $monthlyPurchaseUrl }}"
                data-url="{{ $monthlyPurchaseUrl }}"
                role="tab"
                aria-controls="pills-profile"
                aria-selected="false"
            >
                {{ __('Monthly') }}
            </a>
        </li>
    </ul>
    @php
        $flagAttrName = 'data-monthlyPurchase-listener-added';
        $guardAttrName = 'data-url';
        $urlAttrName = 'data-url';
        $message = Utility::fetchLinkMessage($lang, VW::RPT, 'monthly_purchase_unavailable')
        ?? 'Monthly purchase route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" >
                <div class="card">
                    @php
                        $dailyPurchaseBase    = VW::RPT.'.daily.purchase';
                        $dailyPurchaseKebab   = Str::kebab($dailyPurchaseBase);
                        $dailyPurchaseResolved= Route::has($dailyPurchaseBase) ? $dailyPurchaseBase : (Route::has($dailyPurchaseKebab) ? $dailyPurchaseKebab : null);
                        $dailyPurchaseUrl     = $dailyPurchaseResolved ? route($dailyPurchaseResolved) : '#';
                        $formId               = 'daily_purchase_report_submit';
                        $applyGuardMsg        = Utility::fetchLinkMessage($lang, VW::RPT, 'daily_apply_purchase_route_unavailable') ?? 'Daily purchase apply route is unavailable. Please contact technical support or your domain administrator.';
                        $resetGuardMsg        = Utility::fetchLinkMessage($lang, VW::RPT, 'daily_reset_purchase_route_unavailable') ?? 'Daily purchase reset route is unavailable. Please contact technical support or your domain administrator.';
                    @endphp
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="mt-2">
                                <div class="card">
                                    <div class="card-body">
                                        {{ Form::open([
                                            'method'            => 'GET',
                                            'url'               => $dailyPurchaseUrl,
                                            'id'                => $formId,
                                            'data-url'          => $dailyPurchaseUrl,
                                            'data-guard-msg'    => $applyGuardMsg,
                                            'data-sv-localized' => 'true',
                                        ]) }}
                                            <div class="{{ VW::R_FLX_ALC_JCE }}">
                                                <div class="{{ VW::CL_POS1 }}">
                                                    <div class="btn-box">
                                                        {{ Form::label('start_date', __('Start Date'), ['class'=>'form-label']) }}
                                                        {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : '', ['class' => 'form-control month-btn']) }}
                                                    </div>
                                                </div>
                                                <div class="{{ VW::CL_POS2 }}">
                                                    <div class="btn-box">
                                                        {{ Form::label('end_date', __('End Date'), ['class'=>'form-label']) }}
                                                        {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : '', ['class' => 'form-control month-btn']) }}
                                                    </div>
                                                </div>
                                                <div class="{{ VW::CL_POS3 }}">
                                                    <div class="btn-box">
                                                        {{ Form::label('warehouse', __('Warehouse'), ['class'=>'form-label']) }}
                                                        {{ Form::select('warehouse', $warehouse, isset($_GET['warehouse']) ? $_GET['warehouse'] : '', ['class' => 'form-control select']) }}
                                                    </div>
                                                </div>
                                                <div class="{{ VW::CL_POS3 }}">
                                                    <div class="btn-box">
                                                        {{ Form::label('vendor', __('Vendor'), ['class'=>'form-label']) }}
                                                        {{ Form::select('vendor', $vendor, isset($_GET['vendor']) ? $_GET['vendor'] : '', ['class' => 'form-control select']) }}
                                                    </div>
                                                </div>
                                                <div class="{{ VW::C_AT_FEND }}">
                                                    <a href="#"
                                                    class="{{ VW::BT_SM_PM }} apply-daily-purchase-link"
                                                    data-form-id="{{ $formId }}"
                                                    data-guard-msg="{{ $applyGuardMsg }}"
                                                    data-sv-localized="true"
                                                    data-bs-toggle="tooltip"
                                                    data-original-title="{{ __('apply') }}"
                                                    title="{{ __('Apply') }}">
                                                        <span class="btn-inner--icon"><i class="{{ VW::TI_SRC }}"></i></span>
                                                    </a>
                                                    <a href="{{ $dailyPurchaseUrl }}"
                                                    class="{{ VW::BT_SM_DG }} reset-daily-purchase-link"
                                                    data-url="{{ $dailyPurchaseUrl }}"
                                                    data-guard-msg="{{ $resetGuardMsg }}"
                                                    data-sv-localized="true"
                                                    data-bs-toggle="tooltip"
                                                    data-original-title="{{ __('Reset') }}"
                                                    title="{{ __('Reset') }}">
                                                        <span class="btn-inner--icon"><i class="{{ VW::TI_TRS_OFF }}"></i></span>
                                                    </a>
                                                </div>
                                            </div>
                                        {{ Form::close() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @push(StacksConstants::ADM_SCR_PG)
                        <script src="{{ asset('assets/js/routes/reports/purchases/daily/apply.js') }}" defer></script>
                        <script src="{{ asset('assets/js/routes/reports/purchases/daily/reset.js') }}" defer></script>
                    @endpush
                </div>
            </div>
        </div>
    </div>
    <div id="printableArea">
        <div class="row mt-0">
            <div class="col">
                <input type="hidden" value="{{$filter['warehouse'].' '.__('Daily Purchase').' '.'Report of'.' '.$filter['startDate'].' to '.$filter['endDate']}}" id="filename">
                <div class="{{ VW::CD_POS }}">
                    <h7 class="{{ VW::RPT_TX_GR }}">{{__('Report')}} :</h7>
                    <h6 class="{{ VW::CD_POS }}">{{__('Daily Purchase Report')}}</h6>
                </div>
            </div>
            @if(!empty($filter['warehouse']))

                <div class="col">
                    <div class="{{ VW::CD_POS }}">
                        <h7 class="{{ VW::RPT_TX_GR }}">{{__('Warehouse')}} :</h7>
                        <h6 class="{{ VW::CD_POS }}">{{$filter['warehouse']}}</h6>
                    </div>
                </div>
            @endif
            @if(!empty($filter['vendor']))
                <div class="col">
                    <div class="{{ VW::CD_POS }}">
                        <h7 class="{{ VW::RPT_TX_GR }}">{{__('Vendor')}} :</h7>
                        <h6 class="{{ VW::CD_POS }}">{{$filter['vendor']}}</h6>
                    </div>
                </div>
            @endif
            <div class="col">
                <div class="{{ VW::CD_POS }}">
                    <h7 class="{{ VW::RPT_TX_GR }}">{{__('Duration')}} :</h7>
                    <h6 class="{{ VW::CD_POS }}">{{$filter['startDate'].' to '.$filter['endDate']}}</h6>
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
                                        <div id="daily-purchase"></div>
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
    <script defer src="{{ asset('assets/js/routes/reports/daily/printable.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/reports/daily/download.js') }}"></script>
    <script defer>
        (() => {
          const BS_LINK = 'link[href*="bootstrap"]';
          const CHART_CONTAINER = '#daily-purchase';
          const translations = {
            ar: { chart_fail: "فشل عرض مخطط المشتريات اليومي" },
            da: { chart_fail: "Kunne ikke vise dagligt indkøbsdiagram" },
            de: { chart_fail: "Tägliches Einkaufsdiagramm konnte nicht angezeigt werden" },
            en: { chart_fail: "Failed to render daily purchase chart" },
            es: { chart_fail: "Error al mostrar gráfico de compras diario" },
            fr: { chart_fail: "Échec de l'affichage du diagramme d'achat quotidien" },
            he: { chart_fail: "נכשל בהצגת תרשים רכישות יומי" },
            it: { chart_fail: "Impossibile visualizzare il grafico degli acquisti giornaliero" },
            ja: { chart_fail: "デイリー購入チャートの表示に失敗しました" },
            nl: { chart_fail: "Dagelijkse inkoopgrafiek weergeven mislukt" },
            pl: { chart_fail: "Nie udało się wyświetlić dziennego wykresu zakupów" },
            pt: { chart_fail: "Falha ao exibir gráfico de compras diário" },
            "pt-br": { chart_fail: "Falha ao exibir gráfico de compras diário" },
            ru: { chart_fail: "Не удалось отобразить ежедневную диаграмму покупок" },
            tr: { chart_fail: "Günlük satın alma grafiği oluşturulamadı" },
            zh: { chart_fail: "无法渲染每日采购图表" }
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
              toast.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div>`;
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
                series: [{ name: '{{ __("Purchase") }}', data }],
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
    <script defer>
        (() => {
            const translations = {
                ar: { no_permission: "ليس لديك إذن لعرض هذه الصفحة" },
                da: { no_permission: "Du har ikke tilladelse til at se denne side" },
                de: { no_permission: "Sie haben keine Berechtigung, diese Seite anzuzeigen" },
                en: { no_permission: "You don't have permission to view this page" },
                es: { no_permission: "No tienes permiso para ver esta página" },
                fr: { no_permission: "Vous n'êtes pas autorisé à voir cette page" },
                he: { no_permission: "אין לך הרשאה לצפות בדף זה" },
                it: { no_permission: "Non sei autorizzato a visualizzare questa pagina" },
                ja: { no_permission: "このページを表示する権限がありません" },
                nl: { no_permission: "U bent niet gemachtigd om deze pagina te bekijken" },
                pl: { no_permission: "Nie masz uprawnień do przeglądania tej strony" },
                pt: { no_permission: "Você não tem permissão para ver esta página" },
                "pt-br": { no_permission: "Você não tem permissão para ver esta página" },
                ru: { no_permission: "У вас нет разрешения на просмотр этой страницы" },
                tr: { no_permission: "Bu sayfayı görüntüleme izniniz yok" },
                zh: { no_permission: "您没有权限查看此页面" }
            };
            
            const flagAttr = @json($flagAttrName);
            const guardAttr = @json($guardAttrName);
            const urlAttr = @json($urlAttrName);
            
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
                if (window.bootstrap?.Toast) {
                const container = getToastContainer();
                const toast = document.createElement('div');
                toast.className = 'toast align-items-center text-bg-danger border-0';
                toast.setAttribute('role', 'alert');
                toast.setAttribute('aria-live', 'assertive');
                toast.setAttribute('aria-atomic', 'true');
                toast.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div>`;
                container.append(toast);
                new bootstrap.Toast(toast, {autohide: true, delay: 5000}).show();
                } else {
                alert(msg);
                }
            };
            
            const handleClick = (e, url) => {
                e.preventDefault();
                window.location.href = url;
            };
            
            const observer = new MutationObserver(mutations => {
                mutations.forEach(mutation => {
                mutation.removedNodes.forEach(node => {
                    if (node === el && node.eventHandler) {
                    node.removeEventListener("click", node.eventHandler);
                    delete node.eventHandler;
                    }
                });
                });
            });
            
            try {
                const el = document.getElementById("pills-profile-tab");
                if (!el) return;
                
                if (el.getAttribute(flagAttr) === "true") return;
                el.setAttribute(flagAttr, "true");
                
                observer.observe(document.body, { childList: true, subtree: true });
                
                const dataUrl = el.getAttribute(guardAttr) ?? el.getAttribute(urlAttr);
                const hrefAttr = el.getAttribute("href");
                const url = dataUrl ?? hrefAttr;
                
                if (dataUrl === "#" && hrefAttr === "#") {
                showError('no_permission');
                return;
                }
                
                const clickHandler = (e) => handleClick(e, url);
                el.eventHandler = clickHandler;
                el.addEventListener("click", clickHandler);
            } catch (error) {
            }
        })();
    </script>
    <script defer>
        (() => {
        const translations = {
            ar: { filter_unavailable: "مرشح غير متوفر" },
            da: { filter_unavailable: "Filter ikke tilgængelig" },
            de: { filter_unavailable: "Filter nicht verfügbar" },
            en: { filter_unavailable: "Filter unavailable" },
            es: { filter_unavailable: "Filtro no disponible" },
            fr: { filter_unavailable: "Filtre indisponible" },
            he: { filter_unavailable: "מסנן לא זמין" },
            it: { filter_unavailable: "Filtro non disponibile" },
            ja: { filter_unavailable: "フィルター利用不可" },
            nl: { filter_unavailable: "Filter niet beschikbaar" },
            pl: { filter_unavailable: "Filtr niedostępny" },
            pt: { filter_unavailable: "Filtro indisponível" },
            "pt-br": { filter_unavailable: "Filtro indisponível" },
            ru: { filter_unavailable: "Фильтр недоступен" },
            tr: { filter_unavailable: "Filtre kullanılamıyor" },
            zh: { filter_unavailable: "筛选器不可用" }
        };
        
        const APPLY_CLASS = '{{ VW::BT_SM_PM }}';
        const RESET_CLASS = '{{ VW::BT_SM_DG }}';
        const APPLY_ATTR = 'data-apply-listener';
        const RESET_ATTR = 'data-reset-listener';
        const FORM_ID = 'daily_purchase_report_submit';
        
        let toastContainer = null;
        const getToastContainer = () => {
            if (!toastContainer) {
            toastContainer = document.querySelector('.toast-container') || document.createElement('div');
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            if (!toastContainer.isConnected) document.body.append(toastContainer);
            }
            return toastContainer;
        };
        
        const showError = (message) => {
            let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en")
                .toLowerCase()
                .replace(/_/g, "-");
            lang === "pt-br" ? lang : lang.slice(0, 2);
            const msg = translations[lang]?.filter_unavailable || message;
            
            if (window.bootstrap?.Toast) {
            const container = getToastContainer();
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-bg-danger border-0';
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            toast.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div>`;
            container.append(toast);
            new bootstrap.Toast(toast, {autohide: true, delay: 5000}).show();
            } else {
            alert(msg);
            }
        };
        
        const handleButtonClick = (btn, isApply) => {
            const url = btn.getAttribute('data-url')
            const href = btn.getAttribute('href');
            if ((!url || url === '#') && (!href || href === '#')) {
                showError("{{ $filterUnavailableMsg }}");
                btn.setAttribute('data-failed-route', 'true');
                return;
            }
            
            if (isApply) {
            const form = document.getElementById(FORM_ID);
            if (form) form.submit();
            } else {
            window.location.href = url;
            }
        };
        
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
            mutation.removedNodes.forEach(node => {
                if (node.nodeType === 1) {
                if (node.getAttribute(APPLY_ATTR)) {
                    node.removeEventListener('click', node.applyHandler);
                    delete node.applyHandler;
                }
                if (node.getAttribute(RESET_ATTR)) {
                    node.removeEventListener('click', node.resetHandler);
                    delete node.resetHandler;
                }
                }
            });
            });
        });
        
        try {
            observer.observe(document.body, { childList: true, subtree: true });
        
            const applyBtn = document.querySelector(`a.${APPLY_CLASS}`);
            if (applyBtn && !applyBtn.getAttribute(APPLY_ATTR)) {
            applyBtn.setAttribute(APPLY_ATTR, 'true');
            const handler = (e) => {
                e.preventDefault();
                handleButtonClick(applyBtn, true);
            };
            applyBtn.applyHandler = handler;
            applyBtn.addEventListener('click', handler);
            }
        
            const resetBtn = document.querySelector(`a.${RESET_CLASS}`);
            if (resetBtn && !resetBtn.getAttribute(RESET_ATTR)) {
            resetBtn.setAttribute(RESET_ATTR, 'true');
            const handler = (e) => {
                e.preventDefault();
                handleButtonClick(resetBtn, false);
            };
            resetBtn.resetHandler = handler;
            resetBtn.addEventListener('click', handler);
            }
        } catch (error) {
        }
        })();
    </script>   
@endpush