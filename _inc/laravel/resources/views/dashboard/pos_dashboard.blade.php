@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        YieldingConstants
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Dashboard')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
        <script>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
        ar: {
            traffic_chart_unavailable: 'فشل عرض مخطط المرور.'
        },
        da: {
            traffic_chart_unavailable: 'Kunne ikke vise trafikdiagram.'
        },
        de: {
            traffic_chart_unavailable: 'Fehler beim Anzeigen des Verkehrscharts.'
        },
        en: {
            traffic_chart_unavailable: 'Failed to render traffic chart.'
        },
        es: {
            traffic_chart_unavailable: 'Error al mostrar el gráfico de tráfico.'
        },
        fr: {
            traffic_chart_unavailable: 'Échec de l’affichage du graphique de trafic.'
        },
        he: {
            traffic_chart_unavailable: 'הצגת תרשים התנועה נכשלה.'
        },
        it: {
            traffic_chart_unavailable: 'Visualizzazione del grafico del traffico non riuscita.'
        },
        ja: {
            traffic_chart_unavailable: 'トラフィックチャートの表示に失敗しました。'
        },
        nl: {
            traffic_chart_unavailable: 'Kan verkeersgrafiek niet weergeven.'
        },
        pl: {
            traffic_chart_unavailable: 'Nie udało się wyświetlić wykresu ruchu.'
        },
        pt: {
            traffic_chart_unavailable: 'Falha ao exibir o gráfico de tráfego.'
        },
        'pt-br': {
            traffic_chart_unavailable: 'Falha ao exibir o gráfico de tráfego.'
        },
        ru: {
            traffic_chart_unavailable: 'Не удалось отобразить график трафика.'
        },
        tr: {
            traffic_chart_unavailable: 'Trafik grafiği oluşturulamadı.'
        },
        zh: {
            traffic_chart_unavailable: '呈现流量图失败。'
        }
        };
Object.keys(t).forEach(
  k =>
    (window.translations[k] = {
      ...(window.translations[k] || {}),
      ...t[k],
    })
);
     
          })();
    </script>
    <script async>
        (() => {
        const ERR_FB = '# ERROR';
        const DS_CLIENT = 'data-client-localized';
        const DS_GUARD  = 'data-guard-msg';
        const LANG_KEY  = 'erp-np-lang';
        let errorMessage = '';
        
        const getLocalizedMessage = (key, el) => {
            let msg = ERR_FB;
            if (el.getAttribute(DS_CLIENT) === 'true') {
            msg = el.getAttribute(DS_GUARD) || msg;
            } else {
            let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
                .toLowerCase().replace(/_/g, '-');
            lang = lang === 'pt-br' ? lang : lang.slice(0,2);
            msg = translations?.[lang]?.[key]
                ?? el.getAttribute(DS_GUARD)
                ?? translations?.['en']?.[key]
                ?? msg;
            if (msg !== ERR_FB) {
                el.setAttribute(DS_GUARD, msg);
                el.setAttribute(DS_CLIENT, 'true');
            }
            }
            return msg;
        };
        
        const showError = message => {
            try {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                document.body.appendChild(container);
            }
            const hasBs = !!document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
            if (hasBs) {
                const toast = document.createElement('div');
                toast.className = 'toast';
                toast.setAttribute('role','alert');
                toast.setAttribute('aria-live','assertive');
                toast.setAttribute('aria-atomic','true');
                const body = document.createElement('div');
                body.className = 'toast-body';
                body.textContent = message;
                toast.appendChild(body);
                container.appendChild(toast);
                bootstrap.Toast.getOrCreateInstance(toast).show();
            } else {
                alert(message);
            }
            } catch {
            alert(message);
            }
        };
        
        const onErrorPointerUp = () => {
            if (errorMessage) {
            showError(errorMessage);
            errorMessage = '';
            }
        };
        document.addEventListener('pointerup', onErrorPointerUp);
        new MutationObserver((muts, obs) => {
            muts.forEach(m => Array.from(m.removedNodes).forEach(n => {
            if (n === document.documentElement) {
                document.removeEventListener('pointerup', onErrorPointerUp);
                obs.disconnect();
            }
            }));
        }).observe(document.body, { childList:true, subtree:true });
        
        window.addEventListener('DOMContentLoaded', () => {
            try {
            const chartEl = document.querySelector('#traffic-chart');
            if (!chartEl) throw new Error('traffic_chart_unavailable');
        
            const opts = {
                chart: {
                height: 350,
                type: 'area',
                toolbar: { show: false }
                },
                dataLabels: { enabled: false },
                stroke: { width: 2, curve: 'smooth' },
                series: [
                {
                    name: '{{ __("Purchase") }}',
                    data: {!! json_encode($purchasesArray['value']) !!}
                },
                {
                    name: '{{ __("POS") }}',
                    data: {!! json_encode($posesArray['value']) !!}
                }
                ],
                xaxis: {
                categories: {!! json_encode($purchasesArray['label']) !!},
                title: { text: '{{ __("Days") }}' }
                },
                colors: ['#ff3a6e', '#6fd943'],
                grid: { strokeDashArray: 4 },
                legend: { show: false },
                yaxis: { title: { text: '{{ __("Amount") }}' } }
            };
        
            new ApexCharts(chartEl, opts).render();
            } catch (e) {
            errorMessage = getLocalizedMessage(e.message, document.querySelector('#traffic-chart') || document.body);
            }
        });
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('POS')}}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-6 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avatar bg-primary">
                                    <i class="ti ti-hand-finger"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('POS Of This Month') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{$pos_data['monthlyPosAmount']}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avatar bg-warning">
                                    <i class="ti ti-chart-pie"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('POS Amount') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{$pos_data['totalPosAmount']}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avatar bg-info">
                                    <i class="ti ti-report-money"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Purchase Of This Month') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{$pos_data['monthlyPurchaseAmount']}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avatar bg-info">
                                    <i class="ti ti-chart-bar"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __(' Purchase Amount') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{$pos_data['totalPurchaseAmount']}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6">
                            <h5>{{ __('Purchase Vs POS Report') }}</h5>
                        </div>
                        <div class="col-6 text-end">
                            <h6>{{ __('Last 10 Days') }}</h6>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="traffic-chart"></div>
                </div>
            </div>
        </div>

    </div>
@endsection
