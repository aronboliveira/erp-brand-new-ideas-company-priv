@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Model\Utility;
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Appraisal')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Appraisal')}}</li>
@endsection
@push(StacksConstants::ADM_CSS)
    <style>
        @import url({{ asset('css/font-awesome.css') }});
    </style>
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script>
      window.translations = {
        ar: {
          emp_by_star_route_unavailable: 'مسار empByStar غير متوفر.',
          emp_by_star_unavailable: 'فشل جلب بيانات النجوم للموظف.',
          getemployee_route_unavailable: 'مسار getemployee غير متوفر.',
          employee_fetch_unavailable: 'فشل جلب قائمة الموظفين.'
        },
        da: {
          emp_by_star_route_unavailable: 'EmpByStar-ruten er ikke tilgængelig.',
          emp_by_star_unavailable: 'Kunne ikke hente stjernedata for medarbejderen.',
          getemployee_route_unavailable: 'GetEmployee-ruten er ikke tilgængelig.',
          employee_fetch_unavailable: 'Kunne ikke hente medarbejderlisten.'
        },
        de: {
          emp_by_star_route_unavailable: 'Route "empByStar" ist nicht verfügbar.',
          emp_by_star_unavailable: 'Fehler beim Laden der Stern-Daten für den Mitarbeiter.',
          getemployee_route_unavailable: 'Route "getemployee" ist nicht verfügbar.',
          employee_fetch_unavailable: 'Fehler beim Abrufen der Mitarbeiterliste.'
        },
        en: {
          emp_by_star_route_unavailable: 'The empByStar route is unavailable.',
          emp_by_star_unavailable: 'Failed to load star data for the employee.',
          getemployee_route_unavailable: 'The getemployee route is unavailable.',
          employee_fetch_unavailable: 'Failed to fetch employee list.'
        },
        es: {
          emp_by_star_route_unavailable: 'La ruta empByStar no está disponible.',
          emp_by_star_unavailable: 'Error al cargar los datos de estrellas para el empleado.',
          getemployee_route_unavailable: 'La ruta getemployee no está disponible.',
          employee_fetch_unavailable: 'Error al obtener la lista de empleados.'
        },
        fr: {
          emp_by_star_route_unavailable: 'La route empByStar n’est pas disponible.',
          emp_by_star_unavailable: 'Échec du chargement des données d’étoiles pour l’employé.',
          getemployee_route_unavailable: 'La route getemployee n’est pas disponible.',
          employee_fetch_unavailable: 'Échec de la récupération de la liste des employés.'
        },
        he: {
          emp_by_star_route_unavailable: 'נתיב empByStar אינו זמין.',
          emp_by_star_unavailable: 'לא ניתן לטעון נתוני כוכבים עבור העובד.',
          getemployee_route_unavailable: 'נתיב getemployee אינו זמין.',
          employee_fetch_unavailable: 'לא ניתן להביא את רשימת העובדים.'
        },
        it: {
          emp_by_star_route_unavailable: 'Il percorso empByStar non è disponibile.',
          emp_by_star_unavailable: 'Impossibile caricare i dati delle stelle per il dipendente.',
          getemployee_route_unavailable: 'Il percorso getemployee non è disponibile.',
          employee_fetch_unavailable: 'Impossibile recuperare l’elenco dei dipendenti.'
        },
        ja: {
          emp_by_star_route_unavailable: 'empByStar ルートが利用できません。',
          emp_by_star_unavailable: '従業員のスター データの読み込みに失敗しました。',
          getemployee_route_unavailable: 'getemployee ルートが利用できません。',
          employee_fetch_unavailable: '従業員リストの取得に失敗しました。'
        },
        nl: {
          emp_by_star_route_unavailable: 'De empByStar-route is niet beschikbaar.',
          emp_by_star_unavailable: 'Kan stergegevens voor de medewerker niet laden.',
          getemployee_route_unavailable: 'De getemployee-route is niet beschikbaar.',
          employee_fetch_unavailable: 'Kan werknemerslijst niet ophalen.'
        },
        pl: {
          emp_by_star_route_unavailable: 'Trasa empByStar jest niedostępna.',
          emp_by_star_unavailable: 'Nie udało się załadować danych gwiazdek pracownika.',
          getemployee_route_unavailable: 'Trasa getemployee jest niedostępna.',
          employee_fetch_unavailable: 'Nie udało się pobrać listy pracowników.'
        },
        pt: {
          emp_by_star_route_unavailable: 'A rota empByStar não está disponível.',
          emp_by_star_unavailable: 'Falha ao carregar dados de estrelas do funcionário.',
          getemployee_route_unavailable: 'A rota getemployee não está disponível.',
          employee_fetch_unavailable: 'Falha ao buscar a lista de funcionários.'
        },
        'pt-br': {
          emp_by_star_route_unavailable: 'A rota empByStar não está disponível.',
          emp_by_star_unavailable: 'Falha ao carregar dados de estrelas do funcionário.',
          getemployee_route_unavailable: 'A rota getemployee não está disponível.',
          employee_fetch_unavailable: 'Falha ao buscar a lista de funcionários.'
        },
        ru: {
          emp_by_star_route_unavailable: 'Маршрут empByStar недоступен.',
          emp_by_star_unavailable: 'Не удалось загрузить данные звезд для сотрудника.',
          getemployee_route_unavailable: 'Маршрут getemployee недоступен.',
          employee_fetch_unavailable: 'Не удалось получить список сотрудников.'
        },
        tr: {
          emp_by_star_route_unavailable: 'empByStar rotası kullanılamıyor.',
          emp_by_star_unavailable: 'Çalışan için yıldız verileri yüklenemedi.',
          getemployee_route_unavailable: 'getemployee rotası kullanılamıyor.',
          employee_fetch_unavailable: 'Çalışan listesi alınamadı.'
        },
        zh: {
          emp_by_star_route_unavailable: 'empByStar 路由不可用。',
          emp_by_star_unavailable: '无法加载该员工的星级数据。',
          getemployee_route_unavailable: 'getemployee 路由不可用。',
          employee_fetch_unavailable: '无法获取员工列表。'
        }
      };
    </script>
    <script defer src="{{ asset('js/bootstrap-toggle.js') }}"></script>
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
          let errorMessage = '';
          const onErrorPointerUp = () => {
            if (errorMessage) {
              showError(errorMessage);
              errorMessage = '';
            }
          };
          document.addEventListener('pointerup', onErrorPointerUp);
          new MutationObserver((ms, obs) => {
            ms.forEach(m => m.removedNodes.forEach(n => {
              if (n === document.documentElement) {
                document.removeEventListener('pointerup', onErrorPointerUp);
                obs.disconnect();
              }
            }));
          }).observe(document.body, { childList: true, subtree: true });
          document.addEventListener('DOMContentLoaded', () => {
            const empEl = document.getElementById('employee');
            if (empEl && empEl.dataset.listenerAttached !== 'true') {
              empEl.dataset.listenerAttached = 'true';
              const obs1 = new MutationObserver((ms, obs) => {
                ms.forEach(m => m.removedNodes.forEach(n => {
                  if (n === empEl) {
                    empEl.removeEventListener('change', onEmployeeChange);
                    obs.disconnect();
                  }
                }));
              });
              obs1.observe(document.body, { childList: true, subtree: true });
              empEl.addEventListener('change', onEmployeeChange);
            }
            const branchEl = document.getElementById('branch');
            if (branchEl && branchEl.dataset.listenerAttached !== 'true') {
              branchEl.dataset.listenerAttached = 'true';
              const obs2 = new MutationObserver((ms, obs) => {
                ms.forEach(m => m.removedNodes.forEach(n => {
                  if (n === branchEl) {
                    branchEl.removeEventListener('change', onBranchChange);
                    obs.disconnect();
                  }
                }));
              });
              obs2.observe(document.body, { childList: true, subtree: true });
              branchEl.addEventListener('change', onBranchChange);
            }
            if (empEl) onEmployeeChange.call(empEl);
          });
          function onEmployeeChange() {
            loadStars(this.value);
          }
          function onBranchChange() {
            loadEmployees(this.value);
          }
          function loadStars(empId) {
            try {
              const url = '{{ route("empByStar") }}';
              if (!url || url === '#') {
                errorMessage = getLocalizedMessage('emp_by_star_route_unavailable', document.getElementById('employee') || document.body);
                return;
              }
              $.ajax({
                url,
                type: 'POST',
                dataType: 'json',
                data: {
                  employee: empId ?? '',
                  _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
                }
              })
              .done(data => {
                const stEl = document.getElementById('stares');
                if (stEl) stEl.innerHTML = data.html ?? '';
              })
              .fail(() => {
                errorMessage = getLocalizedMessage('emp_by_star_unavailable', document.getElementById('employee') || document.body);
              });
            } catch {
              errorMessage = getLocalizedMessage('emp_by_star_unavailable', document.getElementById('employee') || document.body);
            }
          }
          function loadEmployees(branchId) {
            try {
              const url = '{{ route("getemployee") }}';
              if (!url || url === '#') {
                errorMessage = getLocalizedMessage('getemployee_route_unavailable', document.getElementById('branch') || document.body);
                return;
              }
              $.ajax({
                url,
                type: 'POST',
                dataType: 'json',
                data: {
                  branch_id: branchId ?? '',
                  _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
                }
              })
              .done(data => {
                const empEl = document.getElementById('employee');
                if (!empEl) return;
                empEl.innerHTML = '<option value="">{{ __("Select Employee") }}</option>';
                (data.employee || []).forEach(val => {
                  const o = document.createElement('option');
                  o.value = val.id;
                  o.textContent = val.name;
                  empEl.appendChild(o);
                });
              })
              .fail(() => {
                errorMessage = getLocalizedMessage('employee_fetch_unavailable', document.getElementById('branch') || document.body);
              });
            } catch {
              errorMessage = getLocalizedMessage('employee_fetch_unavailable', document.getElementById('branch') || document.body);
            }
          }
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ ViewClassNamesConstants::FEND }}">
        @can('create appraisal')
            @php
                use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants, StacksConstants};
                use App\Models\Utility;
                use Illuminate\Support\Facades\Route;
                use Illuminate\Support\Str;

                $lang = Utility::fetchUserLang();
                $createAppraisalRoute = Route::has(ViewsConstants::APR.'.create')
                    ? route(ViewsConstants::APR.'.create')
                    : Route::has(Str::kebab(ViewsConstants::APR.'.create'))
                        ? route(Str::kebab(ViewsConstants::APR.'.create'))
                        : '#';
                $createAppraisalId = 'appraisal-create-link';
                $createAppraisalMsg = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::APR,
                    'appraisal_create_route_unavailable'
                ) ?? 'Create Appraisal route is unavailable. Please contact technical support or your domain administrator.';
            @endphp
            <a
                id="{{ $createAppraisalId }}"
                href="#"
                data-size="lg"
                data-url="{{ $createAppraisalRoute }}"
                data-sv-localized="true"
                data-guard-msg="{{ $createAppraisalMsg }}"
                data-ajax-popup="true"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="{{ ViewClassNamesConstants::BT_SM_PM }}"
            >
                <i class="{{ ViewClassNamesConstants::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        const el = document.getElementById('{{ $createAppraisalId }}');
                        const flagAttr = 'data-listener-active';
                        if (!el || el.getAttribute(flagAttr) === 'true') return;
                        el.setAttribute(flagAttr, 'true');
                        el.addEventListener('click', event => {
                            try {
                                const url = el.getAttribute('data-url');
                                const href = el.href;
                                if ((!url || url === '#') && (!href || href === '#')) {
                                    event.preventDefault();
                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                    let container = document.getElementById('toast-container');
                                    if (!container) {
                                        container = document.createElement('div');
                                        container.id = 'toast-container';
                                        document.body.appendChild(container);
                                    }
                                    if (bootstrapLink && window.bootstrap) {
                                        const toastEl = document.createElement('div');
                                        toastEl.className = 'toast';
                                        toastEl.setAttribute('role', 'alert');
                                        toastEl.setAttribute('aria-live', 'assertive');
                                        toastEl.setAttribute('aria-atomic', 'true');
                                        const body = document.createElement('div');
                                        body.className = 'toast-body';
                                        body.textContent = msg;
                                        toastEl.appendChild(body);
                                        container.appendChild(toastEl);
                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                    } else {
                                        alert(msg);
                                    }
                                    el.setAttribute('data-failed-route', 'true');
                                }
                            } catch {}
                        });
                        const observer = new MutationObserver(() => {
                            if (!document.getElementById('{{ $createAppraisalId }}')) observer.disconnect();
                        });
                        observer.observe(document.body, { childList: true, subtree: true });
                    })();
                </script>
            @endpush
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ ViewClassNamesConstants::RW }}">
        <div class="{{ ViewClassNamesConstants::C12 }}">
            <div class="{{ ViewClassNamesConstants::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ ViewClassNamesConstants::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Department') }}</th>
                                    <th>{{ __('Designation') }}</th>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Target Rating') }}</th>
                                    <th>{{ __('Overall Rating') }}</th>
                                    <th>{{ __('Appraisal Date') }}</th>
                                    @if(Gate::check('edit appraisal')||Gate::check('delete appraisal')||Gate::check('show appraisal'))
                                        <th width="200px">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @foreach($appraisals as $appraisal)
                                    @php
                                        $designation = $appraisal->employees->designation->id ?? 0;
                                        $targetRating = Utility::getTargetrating($designation,$competencyCount);
                                        $ratingData = json_decode($appraisal->rating,true) ?? [];
                                        $overallrating = $ratingData ? array_sum($ratingData)/count($ratingData) : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $appraisal->branches->name ?? '' }}</td>
                                        <td>{{ $appraisal->employees->department->name ?? '' }}</td>
                                        <td>{{ $appraisal->employees->designation->name ?? '' }}</td>
                                        <td>{{ $appraisal->employees->name ?? '' }}</td>
                                        <td>
                                            @for($i=1;$i<=5;$i++)
                                                @if($targetRating < $i)
                                                    @if(is_float($targetRating) && round($targetRating)==$i)
                                                        <i class="text-warning fas fa-star-half-alt"></i>
                                                    @else
                                                        <i class="fas fa-star"></i>
                                                    @endif
                                                @else
                                                    <i class="text-warning fas fa-star"></i>
                                                @endif
                                            @endfor
                                            <span class="theme-text-color">({{ number_format($targetRating,1) }})</span>
                                        </td>
                                        <td>
                                            @for($i=1;$i<=5;$i++)
                                                @if($overallrating < $i)
                                                    @if(is_float($overallrating) && round($overallrating)==$i)
                                                        <i class="text-warning fas fa-star-half-alt"></i>
                                                    @else
                                                        <i class="fas fa-star"></i>
                                                    @endif
                                                @else
                                                    <i class="text-warning fas fa-star"></i>
                                                @endif
                                            @endfor
                                            <span class="theme-text-color">({{ number_format($overallrating,1) }})</span>
                                        </td>
                                        <td>{{ $appraisal->appraisal_date }}</td>
                                        @if(Gate::check('edit appraisal')||Gate::check('delete appraisal')||Gate::check('show appraisal'))
                                          <td>
                                            @can('show appraisal')
                                                @php
                                                    $lang = Utility::fetchUserLang();
                                                    $showRoute = Route::has(ViewsConstants::APR.'.show')
                                                        ? route(ViewsConstants::APR.'.show', $appraisal->id)
                                                        : '#';
                                                    $showId = 'appraisal-show-' . $appraisal->id . '-link';
                                                    $showMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::APR,
                                                        'appraisal_show_route_unavailable'
                                                    ) ?? 'Appraisal show route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_INF }}">
                                                    <a
                                                        id="{{ $showId }}"
                                                        href="#"
                                                        data-url="{{ $showRoute }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ $showMsg }}"
                                                        data-size="lg"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Appraisal Detail') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('View') }}"
                                                        class="{{ ViewClassNamesConstants::BT_SM_MX3 }} {{ ViewClassNamesConstants::AL_IT_CT }}"
                                                    >
                                                        <i class="{{ ViewClassNamesConstants::TI_EYE_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('edit appraisal')
                                                @php
                                                    $editRoute = Route::has(ViewsConstants::APR.'.edit')
                                                        ? route(ViewsConstants::APR.'.edit', $appraisal->id)
                                                        : '#';
                                                    $editId = 'appraisal-edit-' . $appraisal->id . '-link';
                                                    $editMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::APR,
                                                        'appraisal_edit_route_unavailable'
                                                    ) ?? 'Appraisal edit route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_PRIM }}">
                                                    <a
                                                        id="{{ $editId }}"
                                                        href="#"
                                                        data-url="{{ $editRoute }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ $editMsg }}"
                                                        data-size="lg"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Appraisal') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        class="{{ ViewClassNamesConstants::BT_SM_MX3 }} {{ ViewClassNamesConstants::AL_IT_CT }}"
                                                    >
                                                        <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete appraisal')
                                                @php
                                                    $deleteRoute = Route::has(ViewsConstants::APR.'.destroy')
                                                        ? route(ViewsConstants::APR.'.destroy', $appraisal->id)
                                                        : '#';
                                                    $deleteId = 'appraisal-delete-' . $appraisal->id . '-link';
                                                    $deleteMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::APR,
                                                        'appraisal_destroy_route_unavailable'
                                                    ) ?? 'Appraisal delete route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                    {!! Collective\Html\FormFacade::open([
                                                        'method' => 'DELETE',
                                                        'route'  => [ViewsConstants::APR.'.destroy', $appraisal->id],
                                                        'id'     => 'delete-form-'.$appraisal->id
                                                    ]) !!}
                                                        <a
                                                            id="{{ $deleteId }}"
                                                            href="#"
                                                            class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"
                                                            data-url="{{ $deleteRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $deleteMsg }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                            data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone.') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{$appraisal->id}}').submit();"
                                                        >
                                                            <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                </div>
                                            @endcan
                                          </td>
                                          @push(StacksConstants::ADM_SCR_PG)
                                              <script defer>
                                                  (() => {
                                                      const ids = [
                                                          '{{ $showId ?? '' }}',
                                                          '{{ $editId ?? '' }}',
                                                          '{{ $deleteId ?? '' }}'
                                                      ].filter(Boolean);
                                                      const flagAttr = 'data-listener-active';
                                                      ids.forEach(id => {
                                                          const el = document.getElementById(id);
                                                          if (!el || el.getAttribute(flagAttr) === 'true') return;
                                                          el.setAttribute(flagAttr, 'true');
                                                          el.addEventListener('click', event => {
                                                              try {
                                                                  const url = el.getAttribute('data-url');
                                                                  const href = el.href;
                                                                  if ((!url || url === '#') && (!href || href === '#')) {
                                                                      event.preventDefault();
                                                                      const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                      const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                      let container = document.getElementById('toast-container');
                                                                      if (!container) {
                                                                          container = document.createElement('div');
                                                                          container.id = 'toast-container';
                                                                          document.body.appendChild(container);
                                                                      }
                                                                      if (bootstrapLink && window.bootstrap) {
                                                                          const toastEl = document.createElement('div');
                                                                          toastEl.className = 'toast';
                                                                          toastEl.setAttribute('role', 'alert');
                                                                          toastEl.setAttribute('aria-live', 'assertive');
                                                                          toastEl.setAttribute('aria-atomic', 'true');
                                                                          const body = document.createElement('div');
                                                                          body.className = 'toast-body';
                                                                          body.textContent = msg;
                                                                          toastEl.appendChild(body);
                                                                          container.appendChild(toastEl);
                                                                          bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                      } else {
                                                                          alert(msg);
                                                                      }
                                                                      el.setAttribute('data-failed-route', 'true');
                                                                  }
                                                              } catch {}
                                                          });
                                                          const observer = new MutationObserver(() => {
                                                              if (!document.getElementById(id)) observer.disconnect();
                                                          });
                                                          observer.observe(document.body, { childList: true, subtree: true });
                                                      });
                                                  })();
                                              </script>
                                          @endpush
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