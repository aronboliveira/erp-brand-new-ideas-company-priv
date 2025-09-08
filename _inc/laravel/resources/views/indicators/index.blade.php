@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Indicator')}}
@endsection
@push(StacksConstants::ADM_CSS)
    <style>
        @import url({{ asset('css/font-awesome.css') }});
    </style>
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('js/bootstrap-toggle.js') }}"></script>
        <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
        ar: {
          toggle_init_failed:               'فشل تهيئة التبديل.',
          star_toggle_failed:               'فشل تبديل النجمة.',
          designation_failed:               'فشل جلب الوظيفة.',
          designation_fetch_failed:         'فشل جلب بيانات الوظيفة.',
          invoice_fetch_unavailable:        'بيانات الفاتورة غير متوفرة.'
        },
        da: {
          toggle_init_failed:               'Kunne ikke initialisere toggle.',
          star_toggle_failed:               'Kunne ikke skifte stjerne.',
          designation_failed:               'Kunne ikke hente betegnelse.',
          designation_fetch_failed:         'Kunne ikke hente betegnelsesdata.',
          invoice_fetch_unavailable:        'Fakturadata ikke tilgængelige.'
        },
        de: {
          toggle_init_failed:               'Initialisierung des Schalters fehlgeschlagen.',
          star_toggle_failed:               'Sterneinstellung fehlgeschlagen.',
          designation_failed:               'Abrufen der Bezeichnung fehlgeschlagen.',
          designation_fetch_failed:         'Abrufen der Bezeichnungsdaten fehlgeschlagen.',
          invoice_fetch_unavailable:        'Rechnungsdaten nicht verfügbar.'
        },
        en: {
          toggle_init_failed:               'Failed to initialize toggle.',
          star_toggle_failed:               'Failed to toggle star.',
          designation_failed:               'Failed to fetch designation.',
          designation_fetch_failed:         'Failed to fetch designation data.',
          invoice_fetch_unavailable:        'Invoice data unavailable.'
        },
        es: {
          toggle_init_failed:               'No se pudo inicializar el interruptor.',
          star_toggle_failed:               'No se pudo alternar la estrella.',
          designation_failed:               'No se pudo obtener la designación.',
          designation_fetch_failed:         'No se pudieron obtener los datos de la designación.',
          invoice_fetch_unavailable:        'Datos de factura no disponibles.'
        },
        fr: {
          toggle_init_failed:               'Échec de l’initialisation du commutateur.',
          star_toggle_failed:               'Échec du changement d’état de l’étoile.',
          designation_failed:               'Échec de la récupération de la désignation.',
          designation_fetch_failed:         'Échec de la récupération des données de désignation.',
          invoice_fetch_unavailable:        'Données de facture indisponibles.'
        },
        he: {
          toggle_init_failed:               'נכשל בהפעלת הפקד.',
          star_toggle_failed:               'נכשל שינוי הכוכב.',
          designation_failed:               'נכשל בקבלת התפקיד.',
          designation_fetch_failed:         'נכשל בקבלת נתוני התפקיד.',
          invoice_fetch_unavailable:        'נתוני החשבונית אינם זמינים.'
        },
        it: {
          toggle_init_failed:               'Impossibile inizializzare l’interruttore.',
          star_toggle_failed:               'Impossibile attivare la stella.',
          designation_failed:               'Impossibile ottenere la designazione.',
          designation_fetch_failed:         'Impossibile ottenere i dati della designazione.',
          invoice_fetch_unavailable:        'Dati della fattura non disponibili.'
        },
        ja: {
          toggle_init_failed:               'トグルの初期化に失敗しました。',
          star_toggle_failed:               '星の切り替えに失敗しました。',
          designation_failed:               '役職の取得に失敗しました。',
          designation_fetch_failed:         '役職データの取得に失敗しました。',
          invoice_fetch_unavailable:        '請求書データが利用できません。'
        },
        nl: {
          toggle_init_failed:               'Kan schakelaar niet initialiseren.',
          star_toggle_failed:               'Kan ster niet omzetten.',
          designation_failed:               'Kan functie niet ophalen.',
          designation_fetch_failed:         'Kan functiedata niet ophalen.',
          invoice_fetch_unavailable:        'Factuurgegevens niet beschikbaar.'
        },
        pl: {
          toggle_init_failed:               'Nie można zainicjować przełącznika.',
          star_toggle_failed:               'Nie udało się przełączyć gwiazdki.',
          designation_failed:               'Nie udało się pobrać nazwy stanowiska.',
          designation_fetch_failed:         'Nie udało się pobrać danych stanowiska.',
          invoice_fetch_unavailable:        'Dane faktury niedostępne.'
        },
        pt: {
          toggle_init_failed:               'Falha ao inicializar o alternador.',
          star_toggle_failed:               'Falha ao alternar estrela.',
          designation_failed:               'Falha ao obter designação.',
          designation_fetch_failed:         'Falha ao obter dados de designação.',
          invoice_fetch_unavailable:        'Dados da fatura indisponíveis.'
        },
        'pt-br': {
          toggle_init_failed:               'Falha ao inicializar o alternador.',
          star_toggle_failed:               'Falha ao alternar estrela.',
          designation_failed:               'Falha ao obter designação.',
          designation_fetch_failed:         'Falha ao obter dados de designação.',
          invoice_fetch_unavailable:        'Dados da fatura indisponíveis.'
        },
        ru: {
          toggle_init_failed:               'Не удалось инициализировать переключатель.',
          star_toggle_failed:               'Не удалось переключить звезду.',
          designation_failed:               'Не удалось получить должность.',
          designation_fetch_failed:         'Не удалось получить данные должности.',
          invoice_fetch_unavailable:        'Данные счета недоступны.'
        },
        tr: {
          toggle_init_failed:               'Anahtar başlatılamadı.',
          star_toggle_failed:               'Yıldız anahtarı başarısız.',
          designation_failed:               'Unvan alınamadı.',
          designation_fetch_failed:         'Unvan verileri alınamadı.',
          invoice_fetch_unavailable:        'Fatura verileri kullanılamıyor.'
        },
        zh: {
          toggle_init_failed:               '初始化切换失败。',
          star_toggle_failed:               '切换星标失败。',
          designation_failed:               '获取职称失败。',
          designation_fetch_failed:         '获取职称数据失败。',
          invoice_fetch_unavailable:        '发票数据不可用。'
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
    <script defer>
        (() => {
          const errFb = '# ERROR';
          const dataClientLocalized = 'data-client-localized';
          const dataGuardMsg = 'data-guard-msg';
          const langSessionKey = 'erp-np-lang';
          const getLocalizedMessage = (msgKey, el) => {
            let msg = errFb;
            if (el.getAttribute('data-sv-localized') === 'true' || el.getAttribute(dataClientLocalized) === 'true') {
              msg = el.getAttribute(dataGuardMsg) ?? errFb;
            } else {
              let lang = (window.sessionStorage.getItem(langSessionKey) ?? document.documentElement.lang ?? 'en')
                .toLowerCase()
                .replace(/_/g, '-');
              lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
              msg = window.translations?.[lang]?.[msgKey] ??
                    el.getAttribute(dataGuardMsg) ??
                    window.translations?.['en']?.[msgKey] ??
                    errFb;
              if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLocalized, 'true');
              }
            }
            return msg;
          };
          const showError = message => {
            try {
              let container = document.querySelector('#bootstrap-toast-container');
              if (!container) {
                const hasBs = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
                  .some(l => /bootstrap/i.test(l.href)) && window.bootstrap?.Toast;
                if (hasBs) {
                  container = document.createElement('div');
                  container.id = 'bootstrap-toast-container';
                  container.setAttribute('aria-live', 'polite');
                  container.setAttribute('aria-atomic', 'true');
                  document.body.appendChild(container);
                }
              }
              if (container && window.bootstrap.Toast) {
                let toast = container.querySelector('.toast');
                if (!toast) {
                  toast = document.createElement('div');
                  toast.className = 'toast';
                  toast.setAttribute('role', 'alert');
                  toast.setAttribute('aria-live', 'assertive');
                  toast.setAttribute('aria-atomic', 'true');
                  const body = document.createElement('div');
                  body.className = 'toast-body';
                  toast.appendChild(body);
                  container.appendChild(toast);
                  if (toast.getAttribute('data-click-listener') !== 'true') {
                    toast.addEventListener('click', () => body.textContent = message);
                    toast.setAttribute('data-click-listener', 'true');
                  }
                }
                toast.querySelector('.toast-body').textContent = message;
                new bootstrap.Toast(toast).show();
              } else {
                alert(message);
              }
            } catch {
              alert(message);
            }
          };
        
          document.querySelectorAll('.toggleswitch').forEach(el => {
            try {
              if (el.getAttribute('data-listener-attached') !== 'true' && $(el).bootstrapToggle) {
                el.setAttribute('data-listener-attached', 'true');
                $(el).bootstrapToggle();
              }
            } catch {
              showError(getLocalizedMessage('toggle_init_failed', el));
            }
          });
        
          document.querySelectorAll("fieldset[id^='demo'] .stars").forEach(el => {
            if (el.getAttribute('data-listener-attached') === 'true') return;
            el.setAttribute('data-listener-attached', 'true');
            const obs = new MutationObserver((mutations, ob) => {
              for (const m of mutations) {
                for (const node of m.removedNodes) {
                  if (node === el) {
                    el.removeEventListener('click', onStarClick);
                    ob.disconnect();
                  }
                }
              }
            });
            obs.observe(document.body, { childList: true, subtree: true });
            el.addEventListener('click', onStarClick);
          });
          function onStarClick(evt) {
            try {
              evt.currentTarget.checked = true;
            } catch {
              showError(getLocalizedMessage('star_toggle_failed', evt.currentTarget));
            }
          }
        
          const deptIdEl = document.querySelector('#department_id');
          if (deptIdEl) {
            try {
              getDesignation(deptIdEl.value);
            } catch {
              showError(getLocalizedMessage('designation_failed', deptIdEl));
            }
          }
        
          const deptSelect = document.querySelector("select[name=department]");
          if (deptSelect && deptSelect.getAttribute('data-listener-attached') !== 'true') {
            deptSelect.setAttribute('data-listener-attached', 'true');
            const obs2 = new MutationObserver((mutations, ob) => {
              for (const m of mutations) {
                for (const node of m.removedNodes) {
                  if (node === deptSelect) {
                    deptSelect.removeEventListener('change', onDeptChange);
                    ob.disconnect();
                  }
                }
              }
            });
            obs2.observe(document.body, { childList: true, subtree: true });
            deptSelect.addEventListener('change', onDeptChange);
          }
          function onDeptChange() {
            try {
              getDesignation(deptSelect.value);
            } catch {
              showError(getLocalizedMessage('designation_failed', deptSelect));
            }
          }
        
          function getDesignation(did) {
            try {
              const url = '{{ route("employees.json") }}';
              if (!url) throw new Error();
              $.ajax({
                url,
                type: 'POST',
                dataType: 'json',
                data: {
                  department_id: did,
                  _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
                }
              })
              .done(data => {
                const desEl = document.querySelector('#designation_id');
                if (!desEl) return;
                desEl.innerHTML = '';
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = '{{ __("Select Designation") }}';
                desEl.appendChild(placeholder);
                Object.entries(data).forEach(([key, value]) => {
                  const opt = document.createElement('option');
                  opt.value = key;
                  opt.textContent = value;
                  desEl.appendChild(opt);
                });
              })
              .fail(() => {
                showError(getLocalizedMessage('designation_fetch_failed', deptSelect));
              });
            } catch {
              showError(getLocalizedMessage('designation_fetch_failed', deptSelect));
            }
          }
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
    <li class="breadcrumb-item">{{__('Indicator')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
    @can('create indicator')
       <a href="#" data-size="lg" data-url="{{ route(ViewsConstants::IND.'.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create New Indicator')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
            <div class="card-body table-border-style">
                    <div class="table-responsive">
                    <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{__('Branch')}}</th>
                                <th>{{__('Department')}}</th>
                                <th>{{__('Designation')}}</th>
                                <th>{{__('Overall Rating')}}</th>
                                <th>{{__('Added By')}}</th>
                                <th>{{__('Created At')}}</th>
                                @if( Gate::check('edit indicator') ||Gate::check('delete indicator') ||Gate::check('show indicator'))
                                    <th width="200px">{{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody class="font-style">
                            @foreach ($indicators as $indicator)

                                @php
                                    if(!empty($indicator->rating)){
                                        $rating = json_decode($indicator->rating,true);
                                        if(!empty($rating)){
                                            $starsum = array_sum($rating);
                                            $overallrating = $starsum/count($rating);
                                        }else{
                                                $overallrating = 0;
                                        }

                                    }
                                    else{
                                        $overallrating = 0;
                                    }
                                @endphp
                                <tr>
                                    <td>{{ !empty($indicator->branches)?$indicator->branches->name:'' }}</td>
                                    <td>{{ !empty($indicator->departments)?$indicator->departments->name:'' }}</td>
                                    <td>{{ !empty($indicator->designations)?$indicator->designations->name:'' }}</td>
                                    <td>

                                        @for($i=1; $i<=5; $i++)
                                            @if($overallrating < $i)
                                                @if(is_float($overallrating) && (round($overallrating) == $i))
                                                    <i class="text-warning fas fa-star-half-alt"></i>
                                                @else
                                                    <i class="fas fa-star"></i>
                                                @endif
                                            @else
                                                <i class="text-warning fas fa-star"></i>
                                            @endif
                                        @endfor
                                        <span class="theme-text-color">({{number_format($overallrating,1)}})</span>
                                    </td>

                                    <td>{{ !empty($indicator->user)?$indicator->user->name:'' }}</td>
                                    <td>{{ $user?->dateFormat($indicator->created_at) }}</td>
                                    @if( Gate::check('edit indicator') ||Gate::check('delete indicator') || Gate::check('show indicator'))
                                        <td>
                                            @can('show indicator')
                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" data-url="{{ route(ViewsConstants::IND.'.show',$indicator->id) }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Indicator Detail')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('View')}}" data-original-title="{{__('View Detail')}}">
                                                    <i class="ti ti-eye text-white"></i></a>
                                            </div>
                                            @endcan
                                            @can('edit indicator')
                                            <div class="action-btn bg-primary ms-2">
                                                <a href="#" data-url="{{ route(ViewsConstants::IND.'.edit',$indicator->id) }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Indicator')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i></a>
                                            </div>
                                                @endcan
                                            @can('delete indicator')
                                            <div class="action-btn bg-danger ms-2">
                                            {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::IND.'.destroy', $indicator->id],'id'=>'delete-form-'.$indicator->id]) !!}
                                                <a href="#" class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{$indicator->id}}').submit();">
                                                <i class="ti ti-trash text-white"></i></a>
                                                {!! Collective\Html\FormFacade::close() !!}
                                                </div>
                                            @endcan
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
