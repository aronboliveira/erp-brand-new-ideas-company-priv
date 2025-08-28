@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants, StacksConstants};
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use Collective\Html\FormFacade as Form;

    $lang = Utility::fetchUserLang();
    $storeRoute = Route::has(ViewsConstants::APR)
        ? route(ViewsConstants::APR)
        : '#';
    $formId = 'appraisal-store-form';
    $storeMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::APR,
        'appraisal_store_route_unavailable'
    ) ?? 'Appraisal store route is unavailable. Please contact technical support or your domain administrator.';
@endphp
{{ Form::open(['url'=> $storeRoute,'method'=>'post']) }}
    <div class="modal-body">
        <div class="{{ ViewClassNamesConstants::RW }}">
            <div class="{{ ViewClassNamesConstants::C12 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('branch',__('Branch*'),['class'=>ViewClassNamesConstants::FM_LB]) }}
                    <select name="branch" id="branch" required class="{{ ViewClassNamesConstants::FM_CT_SL }}">
                        <option selected disabled value="0">{{ __('Select Branch') }}</option>
                        @foreach($brances as $value)
                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::CM6 }} {{ ViewClassNamesConstants::MT4 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('employee',__('Employee*'),['class'=>ViewClassNamesConstants::FM_LB]) }}
                    <div class="employee_div">
                        <select name="employee" id="employee" required class="{{ ViewClassNamesConstants::FM_CT_SL }}"></select>
                    </div>
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::CM6 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('appraisal_date',__('Select Month*'),['class'=>ViewClassNamesConstants::FM_LB]) }}
                    {{ Form::month('appraisal_date','',['class'=>ViewClassNamesConstants::FM_CT,'autocomplete'=>'off','required']) }}
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::C12 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('remark',__('Remarks'),['class'=>ViewClassNamesConstants::FM_LB]) }}
                    {{ Form::textarea('remark',null,['class'=>ViewClassNamesConstants::FM_CT,'rows'=>3,'placeholder'=>'Enter remark']) }}
                </div>
            </div>
        </div>
        <div class="{{ ViewClassNamesConstants::RW }}" id="stares"></div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ ViewClassNamesConstants::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ ViewClassNamesConstants::BT_PRM }}">
    </div>
{{ Form::close() }}
    <script>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
    ar: {
      emp_by_star_unavailable: 'فشل جلب بيانات النجوم للموظف.',
      employee_fetch_unavailable: 'فشل جلب قائمة الموظفين.'
    },
    da: {
      emp_by_star_unavailable: 'Kunne ikke hente stjernedata for medarbejderen.',
      employee_fetch_unavailable: 'Kunne ikke hente medarbejderlisten.'
    },
    de: {
      emp_by_star_unavailable: 'Fehler beim Laden der Stern-Daten für den Mitarbeiter.',
      employee_fetch_unavailable: 'Fehler beim Abrufen der Mitarbeiterliste.'
    },
    en: {
      emp_by_star_unavailable: 'Failed to load star data for the employee.',
      employee_fetch_unavailable: 'Failed to fetch employee list.'
    },
    es: {
      emp_by_star_unavailable: 'Error al cargar los datos de estrellas para el empleado.',
      employee_fetch_unavailable: 'Error al obtener la lista de empleados.'
    },
    fr: {
      emp_by_star_unavailable: 'Échec du chargement des données d’étoiles pour l’employé.',
      employee_fetch_unavailable: 'Échec de la récupération de la liste des employés.'
    },
    he: {
      emp_by_star_unavailable: 'לא ניתן לטעון נתוני כוכבים עבור העובד.',
      employee_fetch_unavailable: 'לא ניתן להביא את רשימת העובדים.'
    },
    it: {
      emp_by_star_unavailable: 'Impossibile caricare i dati delle stelle per il dipendente.',
      employee_fetch_unavailable: 'Impossibile recuperare l’elenco dei dipendenti.'
    },
    ja: {
      emp_by_star_unavailable: '従業員のスター データの読み込みに失敗しました。',
      employee_fetch_unavailable: '従業員リストの取得に失敗しました。'
    },
    nl: {
      emp_by_star_unavailable: 'Kan stergegevens voor de medewerker niet laden.',
      employee_fetch_unavailable: 'Kan werknemerslijst niet ophalen.'
    },
    pl: {
      emp_by_star_unavailable: 'Nie udało się załadować danych gwiazdek pracownika.',
      employee_fetch_unavailable: 'Nie udało się pobrać listy pracowników.'
    },
    pt: {
      emp_by_star_unavailable: 'Falha ao carregar dados de estrelas do funcionário.',
      employee_fetch_unavailable: 'Falha ao buscar a lista de funcionários.'
    },
    'pt-br': {
      emp_by_star_unavailable: 'Falha ao carregar dados de estrelas do funcionário.',
      employee_fetch_unavailable: 'Falha ao buscar a lista de funcionários.'
    },
    ru: {
      emp_by_star_unavailable: 'Не удалось загрузить данные звезд для сотрудника.',
      employee_fetch_unavailable: 'Не удалось получить список сотрудников.'
    },
    tr: {
      emp_by_star_unavailable: 'Çalışan için yıldız verileri yüklenemedi.',
      employee_fetch_unavailable: 'Çalışan listesi alınamadı.'
    },
    zh: {
      emp_by_star_unavailable: '无法加载该员工的星级数据。',
      employee_fetch_unavailable: '无法获取员工列表。'
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
                toast.addEventListener('click', () => (body.textContent = message));
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
        for (const m of ms) {
          for (const n of m.removedNodes) {
            if (n === document.documentElement) {
              document.removeEventListener('pointerup', onErrorPointerUp);
              obs.disconnect();
            }
          }
        }
      }).observe(document.body, { childList: true, subtree: true });
      document.addEventListener('DOMContentLoaded', () => {
        const empEl = document.getElementById('employee');
        if (empEl && empEl.dataset.listenerAttached !== 'true') {
          empEl.dataset.listenerAttached = 'true';
          new MutationObserver((ms, obs) => {
            for (const m of ms) {
              for (const n of m.removedNodes) {
                if (n === empEl) {
                  empEl.removeEventListener('change', onEmployeeChange);
                  obs.disconnect();
                }
              }
            }
          }).observe(document.body, { childList: true, subtree: true });
          empEl.addEventListener('change', onEmployeeChange);
        }
        const branchEl = document.getElementById('branch');
        if (branchEl && branchEl.dataset.listenerAttached !== 'true') {
          branchEl.dataset.listenerAttached = 'true';
          new MutationObserver((ms, obs) => {
            for (const m of ms) {
              for (const n of m.removedNodes) {
                if (n === branchEl) {
                  branchEl.removeEventListener('change', onBranchChange);
                  obs.disconnect();
                }
              }
            }
          }).observe(document.body, { childList: true, subtree: true });
          branchEl.addEventListener('change', onBranchChange);
        }
      });
      function onEmployeeChange() {
        const el = this;
        try {
          const empId = el.value ?? '';
          $.ajax({
            url: '{{ route("empByStar") }}',
            type: 'POST',
            dataType: 'json',
            data: {
              employee: empId,
              _token:
                document
                  .querySelector('meta[name="csrf-token"]')
                  ?.getAttribute('content') ?? ''
            }
          })
            .done(data => {
              const target = document.getElementById('stares');
              if (target) target.innerHTML = data.html ?? '';
            })
            .fail(() => {
              errorMessage = getLocalizedMessage('emp_by_star_unavailable', el);
            });
        } catch {
          errorMessage = getLocalizedMessage('emp_by_star_unavailable', el);
        }
      }
      function onBranchChange() {
        const el = this;
        try {
          const branchId = el.value ?? '';
          $.ajax({
            url: '{{ route("getemployee") }}',
            type: 'POST',
            dataType: 'json',
            data: {
              branch_id: branchId,
              _token:
                document
                  .querySelector('meta[name="csrf-token"]')
                  ?.getAttribute('content') ?? ''
            }
          })
            .done(data => {
              const empEl = document.getElementById('employee');
              if (!empEl) return;
              empEl.innerHTML = '<option value="">{{ __("Select Employee") }}</option>';
              (data.employee || []).forEach(({ id, name }) => {
                const o = document.createElement('option');
                o.value = id;
                o.textContent = name;
                empEl.appendChild(o);
              });
            })
            .fail(() => {
              errorMessage = getLocalizedMessage('employee_fetch_unavailable', el);
            });
        } catch {
          errorMessage = getLocalizedMessage('employee_fetch_unavailable', el);
        }
      }
    })();
</script>
<script defer>
    (() => {
        const form = document.getElementById('{{ $formId }}');
        if (!form || form.getAttribute('data-listener-active') === 'true') return;
        form.setAttribute('data-listener-active', 'true');
        form.addEventListener('submit', event => {
            try {
                const action = form.getAttribute('action');
                const url    = form.getAttribute('data-url');
                if ((!action || action === '#') && (!url || url === '#')) {
                    event.preventDefault();
                    const msg = form.getAttribute('data-guard-msg') ?? '# ERROR';
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
        const observer = new MutationObserver(() => {
            if (!document.getElementById('{{ $formId }}')) observer.disconnect();
        });
        observer.observe(document.body, { childList: true, subtree: true });
    })();
</script>





