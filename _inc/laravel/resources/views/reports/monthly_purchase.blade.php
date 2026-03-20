@php
    $data ??= [];
    $warehouse ??= $warehouses ?? [];
    $vendor ??= $vendors ?? [];
    try {
$lang = Utility::fetchUserLang();
    } catch (\Throwable $e) {
        \Log::error('reports/monthly_purchase — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Purchase')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Daily Purchase Report') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('download report')
            @php
                $funcName ??= 'saveAsPDF';
                $guardMsg = Utility::fetchLinkMessage($lang, VW::RPT, 'download_monthly_purchase_unavailable') ?? 'Download function for monthly purchases is unavailable. Please contact technical support or your domain administrator.';
@endphp
            <a href="#" id="download-monthly-pos-link" class="{{ VC::BT_SM_PM }} download-monthly-pos-link" data-sv-localized="true" data-func-name="{{ $funcName }}" data-guard-msg="{{ base64_encode($guardMsg) }}" data-bs-toggle="tooltip" title="{{ __('Download') }}" data-original-title="{{ __('Download') }}">
                <span class="btn-inner--icon"><i class="{{ VC::TI_DWN }}"></i></span>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    @php
        try {
            $dailyPurchaseUrl = Route::has(VW::RPT.'.daily.purchase')
                ? route(VW::RPT.'.daily.purchase')
                : '#';
            $dailyPurchaseNavMsg = Utility::fetchLinkMessage(
                $lang,
                VW::RPT, 'daily_purchase_nav_unavailable'
            ) ?? 'Daily purchase navigation is unavailable. Please contact technical support or your domain administrator.';
        } catch (\Throwable $e) {
            \Log::error('reports/monthly_purchase — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp
    <ul class="{{ VC::NAV_PL_Y3 }}" id="pills-tab" role="tablist">
        <li class="{{ VC::NV_IT }}">
            <a
                class="{{ VC::NV_LK }}"
                id="pills-home-tab"
                data-bs-toggle="pill"
                href="{{ $dailyPurchaseUrl }}"
                data-url="{{ $dailyPurchaseUrl }}"
                data-daily-purchase-nav-listener-added="false"
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
    <div class="row">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::MT2 }}" >
                <div class="card">
                    @php
                        try {
                            $monthlyPurchaseUrl = Route::has(VW::RPT.'.monthly.purchase')
                                ? route(VW::RPT.'.monthly.purchase')
                                : '#';
                            $filterUnavailableMsg = Utility::fetchLinkMessage(
                                $lang,
                                VW::RPT, 'filter_report_unavailable'
                            ) ?? 'Filter report route is unavailable. Please contact technical support or your domain administrator.';
                        } catch (\Throwable $e) {
                            \Log::error('reports/monthly_purchase — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp
                    <div class="{{ VC::CD_BD }}">
                        {{ Form::open([
                            'route' => [VW::RPT . '.monthly.purchase'],
                            'method' => 'GET',
                            'id'     => 'monthly_purchase_report_submit',
                        ]) }}
                        <div class="{{ VC::R_FLX_ALC_JCE }}">
                            <div class="{{ VC::CL_XLG4 }}">
                                <div class="btn-box">
                                    {{ Form::label(
                                        'year',
                                        __('Year'),
                                        ['class' => 'form-label']
                                    ) }}
                                    {{ Form::select(
                                        'year',
                                        $yearList,
                                        isset($_GET['year']) ? $_GET['year'] : '',
                                        ['class' => 'form-control select']
                                    ) }}
                                </div>
                            </div>
                            <div class="{{ VC::CL_POS3 }}">
                                <div class="btn-box">
                                    {{ Form::label(
                                        'warehouse',
                                        __('Warehouse'),
                                        ['class' => 'form-label']
                                    ) }}
                                    {{ Form::select(
                                        'warehouse',
                                        $warehouse,
                                        isset($_GET['warehouse']) ? $_GET['warehouse'] : '',
                                        ['class' => 'form-control select']
                                    ) }}
                                </div>
                            </div>
                            <div class="{{ VC::CL_POS3 }}">
                                <div class="btn-box">
                                    {{ Form::label(
                                        'vendor',
                                        __('Vendor'),
                                        ['class' => 'form-label']
                                    ) }}
                                    {{ Form::select(
                                        'vendor',
                                        $vendor,
                                        isset($_GET['vendor']) ? $_GET['vendor'] : '',
                                        ['class' => 'form-control select']
                                    ) }}
                                </div>
                            </div>
                            <div class="{{ VC::C_AT_FEND }}">
                                <a
                                    href="{{ $monthlyPurchaseUrl }}"
                                    data-url="{{ $monthlyPurchaseUrl }}"
                                    data-apply-listener-added="false"
                                    class="{{ VC::BT_SM_PM }}"
                                    data-toggle="tooltip"
                                    data-original-title="{{ __('apply') }}"
                                >
                                    <span class="btn-inner--icon">
                                        <i class="{{ VC::TI_SRC }}"></i>
                                    </span>
                                </a>
                                <a
                                    href="{{ $monthlyPurchaseUrl }}"
                                    data-url="{{ $monthlyPurchaseUrl }}"
                                    data-reset-listener-added="false"
                                    class="{{ VC::BT_SM_DG }}"
                                    data-toggle="tooltip"
                                    data-original-title="{{ __('Reset') }}"
                                >
                                    <span class="btn-inner--icon">
                                        <i class="{{ VC::TI_TRS_OFF }}"></i>
                                    </span>
                                </a>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="printableArea">
        <div class="row mt-0">
            <div class="col">
                <input type="hidden" value="{{$filter['warehouse'].' '.__('Monthly Purchase').' '.'Report of'.' '.$filter['startMonth'].' to '.$filter['endMonth']}}" id="filename">
                <div class="{{ VC::CD_POS }}">
                    <h7 class="{{ VC::RPT_TX_GR }}">{{__('Report')}} :</h7>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{__('Monthly Purchase Report')}}</h6>
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
            @if(!empty($filter['vendor']))
                <div class="col">
                    <div class="{{ VC::CD_POS }}">
                        <h7 class="{{ VC::RPT_TX_GR }}">{{__('Vendor')}} :</h7>
                        <h6 class="{{ VC::RPT_TX_DEF }}">{{$filter['vendor']}}</h6>
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
                                        <div id="monthly-purchase"></div>
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
    <script async src="{{ asset('assets/js/routes/reports/purchases/monthly/lang/pdf.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/reports/monthly/printable.js') }}""></script>
    <script defer src="{{ asset('assets/js/routes/reports/monthly/save.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/reports/monthly/dailyNav.js') }}"></script>
    <script defer>
        (() => {
          if (typeof window.saveAsPDF !== 'function') return;
          const BS_LINK = 'link[href*="bootstrap"]';
          const PRINTABLE_AREA = 'printableArea';
          const FILENAME_INPUT = '#filename';
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

          const saveAsPDF = () => {
            try {
              const printable = document.getElementById(PRINTABLE_AREA);
              if (!printable) {
                showError('no_area');
                return;
              }

              let filename = 'document.pdf';
              if (typeof $ === 'function') {
                filename = $(FILENAME_INPUT).val() || filename;
              } else {
                const input = document.querySelector(FILENAME_INPUT);
                if (input) filename = input.value || filename;
              }

              if (typeof html2pdf !== 'object' || typeof html2pdf().set !== 'function') {
                showError('pdf_fail');
                return;
              }

              html2pdf().set({
                margin: 0.3,
                filename,
                image: { type: 'jpeg', quality: 1 },
                html2canvas: { scale: 4, dpi: 72, letterRendering: true },
                jsPDF: { unit: 'in', format: 'a2' }
              }).from(printable).save();
            } catch (e) {
              showError('pdf_fail');
            }
          };

          window.saveAsPDF = saveAsPDF;
        })();
        (() => {
          const BS_LINK = 'link[href*="bootstrap"]';
          const CHART_ID = '#monthly-purchase';

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
              const chartEl = document.querySelector(CHART_ID);
              if (!chartEl) return;

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
                      if (chartEl.chart) delete chartEl.chart;
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

              if (chartEl.chart) chartEl.chart.destroy();

              chartEl.chart = new ApexCharts(chartEl, chartOptions);
              chartEl.chart.render();
            } catch (e) {
              showError('chart_fail');
            }
          };

          const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
              mutation.removedNodes.forEach(node => {
                if (node === document.querySelector(CHART_ID)) {
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
        (() => {
          const APPLY_CLASS = '{{ VC::BT_SM_PM }}';
          const RESET_CLASS = '{{ VC::BT_SM_DG }}';
          const APPLY_ATTR = 'data-apply-listener';
          const RESET_ATTR = 'data-reset-listener';
          const FORM_ID = 'monthly_purchase_report_submit';

          const showPermissionError = (message) => {
            if (window.bootstrap?.Toast) {
              const toastEl = document.querySelector('.toast');
              if (toastEl) {
                try {
                  const body = toastEl.querySelector('.toast-body');
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

          const handleButtonClick = (btn, isApply) => {
            const url = btn.getAttribute('data-url')
            const href = btn.getAttribute('href');
            if ((!url || url === '#') && (!href || href === '#')) {
              showPermissionError("{{ $filterUnavailableMsg }}");
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
