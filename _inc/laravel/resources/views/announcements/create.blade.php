@php
    use App\Config\Constants\{
        PlansConstants,
        ViewClassNamesConstants,
        ViewsConstants,
        StacksConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use Collective\Html\FormFacade as Form;
    $lang = Utility::fetchUserLang();
    $storeRoute = Route::has(ViewsConstants::ANC)
        ? route(ViewsConstants::ANC)
        : '#';
    $formId = 'announcement-create-form';
    $storeMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::ANC,
        'announcement_store_route_unavailable'
    ) ?? 'Announcement create route is unavailable. Please contact technical support or your domain administrator.';
    $aiGenerateRoute = Route::has('generate')
        ? route('generate', ['announcement'])
        : '#';
    $aiGenerateId = 'announcement-ai-generate-link';
    $aiGenerateMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::ANC,
        'announcement_generate_route_unavailable'
    ) ?? 'Generate with AI route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::open([
    'url'              => $storeRoute,
    'method'           => 'post',
    'id'               => $formId,
    'data-url'         => $storeRoute,
    'data-sv-localized'=> 'true',
    'data-guard-msg'   => $storeMsg,
]) }}
    <div class="modal-body">
        @php $plan = Utility::getChatGPTSettings(); @endphp
        @if($plan->chatgpt == 1)
            <div class="{{ ViewClassNamesConstants::DFL_JCE }}">
                <a
                    id="{{ $aiGenerateId }}"
                    href="#"
                    data-url="{{ $aiGenerateRoute }}"
                    data-sv-localized="true"
                    data-guard-msg="{{ $aiGenerateMsg }}"
                    data-size="md"
                    class="{{ ViewClassNamesConstants::BT_SM_PM }} btn-icon btn-sm"
                    data-ajax-popup-over="true"
                    data-bs-placement="top"
                    title="{{ __('Generate content with AI') }}"
                >
                    <i class="fas fa-robot"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif
        <div class="{{ ViewClassNamesConstants::RW }}">
            <div class="{{ ViewClassNamesConstants::CM6 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('title', __('Announcement Title'), ['class'=>ViewClassNamesConstants::FM_LB]) }}
                    {{ Form::text('title', null, ['class'=>ViewClassNamesConstants::FM_CT, 'placeholder'=>__('Enter Announcement Title')]) }}
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::CM6 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('branch_id', __('Branch'), ['class'=>ViewClassNamesConstants::FM_LB]) }}
                    <select name="branch_id" id="branch_id" class="{{ ViewClassNamesConstants::FM_CT }} select">
                        <option value="">{{ __('Select Branch') }}</option>
                        <option value="0">{{ __('All Branch') }}</option>
                        @foreach($branch as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::CM6 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('department_id', __('Department'), ['class'=>ViewClassNamesConstants::FM_LB]) }}
                    <select name="department_id[]" id="department_id" class="{{ ViewClassNamesConstants::FM_CT }} select">
                        <option value="">{{ __('Select Department') }}</option>
                    </select>
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::CM6 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('employee_id', __('Employee'), ['class'=>ViewClassNamesConstants::FM_LB]) }}
                    <select name="employee_id[]" id="employee_id" class="{{ ViewClassNamesConstants::FM_CT }} select">
                        <option value="">{{ __('Select Employee') }}</option>
                    </select>
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::CM6 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('start_date', __('Announcement Start Date'), ['class'=>ViewClassNamesConstants::FM_LB]) }}
                    {{ Form::date('start_date', null, ['class'=>ViewClassNamesConstants::FM_CT]) }}
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::CM6 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('end_date', __('Announcement End Date'), ['class'=>ViewClassNamesConstants::FM_LB]) }}
                    {{ Form::date('end_date', null, ['class'=>ViewClassNamesConstants::FM_CT]) }}
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::C12 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('description', __('Announcement Description'), ['class'=>ViewClassNamesConstants::FM_LB]) }}
                    {{ Form::textarea('description', null, ['class'=>ViewClassNamesConstants::FM_CT, 'placeholder'=>__('Enter Announcement Description')]) }}
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ ViewClassNamesConstants::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ ViewClassNamesConstants::BT_PRM }}">
    </div>
{{ Form::close() }}

@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const ids = ['{{ $aiGenerateId }}', '{{ $formId }}'];
            const flagAttr = 'data-listener-active';
            ids.forEach(id => {
                const el = document.getElementById(id);
                if (!el || el.getAttribute(flagAttr) === 'true') return;
                el.setAttribute(flagAttr, 'true');
                if (el.tagName === 'FORM') {
                    el.addEventListener('submit', event => {
                        try {
                            const url    = el.getAttribute('data-url');
                            const action = el.getAttribute('action');
                            if ((!action || action === '#') && (!url || url === '#')) {
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
                            }
                        } catch {}
                    });
                } else {
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
                }
                const observer = new MutationObserver(() => {
                    if (!document.getElementById(id)) observer.disconnect();
                });
                observer.observe(document.body, { childList: true, subtree: true });
            });
        })();
    </script>
@endpush

<script>
  window.translations = {
    ar: {
      announcement_department_fetch_failed: 'فشل جلب أقسام الإعلان.',
      announcement_employee_fetch_failed: 'فشل جلب قائمة الموظفين للإعلان.'
    },
    da: {
      announcement_department_fetch_failed: 'Kunne ikke hente afdelinger til annoncen.',
      announcement_employee_fetch_failed: 'Kunne ikke hente medarbejderlisten til annoncen.'
    },
    de: {
      announcement_department_fetch_failed: 'Fehler beim Laden der Abteilungen für die Ankündigung.',
      announcement_employee_fetch_failed: 'Fehler beim Laden der Mitarbeiterliste für die Ankündigung.'
    },
    en: {
      announcement_department_fetch_failed: 'Failed to fetch announcement departments.',
      announcement_employee_fetch_failed: 'Failed to fetch announcement employee list.'
    },
    es: {
      announcement_department_fetch_failed: 'Error al obtener los departamentos del anuncio.',
      announcement_employee_fetch_failed: 'Error al obtener la lista de empleados del anuncio.'
    },
    fr: {
      announcement_department_fetch_failed: 'Échec de la récupération des départements de l’annonce.',
      announcement_employee_fetch_failed: 'Échec de la récupération de la liste des employés de l’annonce.'
    },
    he: {
      announcement_department_fetch_failed: 'לא ניתן להביא את המחלקות של ההודעה.',
      announcement_employee_fetch_failed: 'לא ניתן להביא את רשימת העובדים של ההודעה.'
    },
    it: {
      announcement_department_fetch_failed: 'Impossibile recuperare i reparti per l’avviso.',
      announcement_employee_fetch_failed: 'Impossibile recuperare l’elenco dei dipendenti per l’avviso.'
    },
    ja: {
      announcement_department_fetch_failed: 'お知らせの部署データの取得に失敗しました。',
      announcement_employee_fetch_failed: 'お知らせの従業員リストの取得に失敗しました。'
    },
    nl: {
      announcement_department_fetch_failed: 'Kan aankondigingsafdelingen niet ophalen.',
      announcement_employee_fetch_failed: 'Kan aankondigingsmedewerkerslijst niet ophalen.'
    },
    pl: {
      announcement_department_fetch_failed: 'Nie udało się pobrać działów ogłoszenia.',
      announcement_employee_fetch_failed: 'Nie udało się pobrać listy pracowników ogłoszenia.'
    },
    pt: {
      announcement_department_fetch_failed: 'Falha ao buscar departamentos do anúncio.',
      announcement_employee_fetch_failed: 'Falha ao buscar lista de funcionários do anúncio.'
    },
    'pt-br': {
      announcement_department_fetch_failed: 'Falha ao buscar departamentos do anúncio.',
      announcement_employee_fetch_failed: 'Falha ao buscar lista de funcionários do anúncio.'
    },
    ru: {
      announcement_department_fetch_failed: 'Не удалось получить отделы объявления.',
      announcement_employee_fetch_failed: 'Не удалось получить список сотрудников объявления.'
    },
    tr: {
      announcement_department_fetch_failed: 'Duyuru bölümleri alınamadı.',
      announcement_employee_fetch_failed: 'Duyuru çalışan listesi alınamadı.'
    },
    zh: {
      announcement_department_fetch_failed: '获取公告部门失败。',
      announcement_employee_fetch_failed: '获取公告员工列表失败。'
    }
  };
</script>
<script defer>
    (() => {
      const errFb = '# ERROR';
      const dataClientLocalized = 'data-client-localized';
      const dataGuardMsg = 'data-guard-msg';
      const langSessionKey = 'erp-np-lang';
    
      const getLocalizedMessage = (msgKey, el) => {
        let msg = errFb;
        if (
          el.getAttribute('data-sv-localized') === 'true' ||
          el.getAttribute(dataClientLocalized) === 'true'
        ) {
          msg = el.getAttribute(dataGuardMsg) ?? errFb;
        } else {
          let lang = (
            window.sessionStorage.getItem(langSessionKey) ??
            document.documentElement.lang ??
            'en'
          )
            .toLowerCase()
            .replace(/_/g, '-');
          lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
          msg =
            window.translations?.[lang]?.[msgKey] ??
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
            const hasBs =
              Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
                .some(l => /bootstrap/i.test(l.href)) &&
              window.bootstrap?.Toast;
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
      new MutationObserver((muts, obs) => {
        for (const m of muts) {
          for (const n of m.removedNodes) {
            if (n === document.documentElement) {
              document.removeEventListener('pointerup', onErrorPointerUp);
              obs.disconnect();
            }
          }
        }
      }).observe(document.body, { childList: true, subtree: true });
    
      document.addEventListener('DOMContentLoaded', () => {
        const branchEl = document.getElementById('branch_id');
        if (branchEl) {
          const obsB = new MutationObserver((muts, obs) => {
            for (const m of muts) {
              for (const n of m.removedNodes) {
                if (n === branchEl) {
                  branchEl.removeEventListener('change', onBranchChange);
                  obs.disconnect();
                }
              }
            }
          });
          obsB.observe(document.body, { childList: true, subtree: true });
    
          branchEl.addEventListener('change', onBranchChange);
          branchEl.addEventListener('pointerup', onErrorPointerUp);
          getDepartment(branchEl.value ?? '');
        }
    
        const deptEl = document.getElementById('department_id');
        if (deptEl) {
          const obsD = new MutationObserver((muts, obs) => {
            for (const m of muts) {
              for (const n of m.removedNodes) {
                if (n === deptEl) {
                  deptEl.removeEventListener('change', onDeptChange);
                  obs.disconnect();
                }
              }
            }
          });
          obsD.observe(document.body, { childList: true, subtree: true });
    
          deptEl.addEventListener('change', onDeptChange);
          deptEl.addEventListener('pointerup', onErrorPointerUp);
        }
      });
    
      function onBranchChange() {
        getDepartment(this.value ?? '');
      }
    
      function onDeptChange() {
        getEmployee(this.value ?? '');
      }
    
      function getDepartment(bid) {
        try {
          $.ajax({
            url: '{{ route("announcements.getdepartment") }}',
            type: 'POST',
            dataType: 'json',
            data: {
              branch_id: bid,
              _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
            }
          })
          .done(data => {
            const deptEl = document.getElementById('department_id');
            if (!deptEl) return;
            deptEl.innerHTML = '';
            const opt0 = document.createElement('option');
            opt0.value = '';
            opt0.textContent = '{{ __("Select Department") }}';
            deptEl.appendChild(opt0);
            const optAll = document.createElement('option');
            optAll.value = '0';
            optAll.textContent = '{{ __("All Department") }}';
            deptEl.appendChild(optAll);
            Object.entries(data).forEach(([key, val]) => {
              const o = document.createElement('option');
              o.value = key;
              o.textContent = val;
              deptEl.appendChild(o);
            });
          })
          .fail(() => {
            errorMessage = getLocalizedMessage('announcement_department_fetch_failed', document.getElementById('branch_id') || document.body);
          });
        } catch {
          errorMessage = getLocalizedMessage('announcement_department_fetch_failed', document.getElementById('branch_id') || document.body);
        }
      }
    
      function getEmployee(did) {
        try {
          $.ajax({
            url: '{{ route("announcements.getemployee") }}',
            type: 'POST',
            dataType: 'json',
            data: {
              department_id: did,
              _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
            }
          })
          .done(data => {
            const empEl = document.getElementById('employee_id');
            if (!empEl) return;
            empEl.innerHTML = '';
            const opt0 = document.createElement('option');
            opt0.value = '';
            opt0.textContent = '{{ __("Select Employee") }}';
            empEl.appendChild(opt0);
            const optAll = document.createElement('option');
            optAll.value = '0';
            optAll.textContent = '{{ __("All Employee") }}';
            empEl.appendChild(optAll);
            Object.entries(data).forEach(([key, val]) => {
              const o = document.createElement('option');
              o.value = key;
              o.textContent = val;
              empEl.appendChild(o);
            });
          })
          .fail(() => {
            errorMessage = getLocalizedMessage('announcement_employee_fetch_failed', document.getElementById('department_id') || document.body);
          });
        } catch {
          errorMessage = getLocalizedMessage('announcement_employee_fetch_failed', document.getElementById('department_id') || document.body);
        }
      }
    })();
</script>
    