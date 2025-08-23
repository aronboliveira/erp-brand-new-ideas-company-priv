@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants, StacksConstants};
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use Collective\Html\FormFacade as Form;

    $lang = Utility::fetchUserLang();
    $updateRoute = Route::has(ViewsConstants::APR.'.update')
        ? route(ViewsConstants::APR.'.update', $appraisal->id)
        : '#';
    $formId = 'appraisal-update-form';
    $updateMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::APR,
        'appraisal_update_route_unavailable'
    ) ?? 'Appraisal update route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::model($appraisal, [
    'route'             => [ViewsConstants::APR.'.update', $appraisal->id],
    'method'            => 'PUT',
    'id'                => $formId,
    'data-url'          => $updateRoute,
    'data-sv-localized' => 'true',
    'data-guard-msg'    => $updateMsg,
]) }}
    <div class="modal-body">
        <div class="{{ ViewClassNamesConstants::RW }}">
            <div class="{{ ViewClassNamesConstants::C12 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('branch',__('Branch*'),['class'=>ViewClassNamesConstants::FM_LB]) }}
                    <select name="branch" id="branch" required class="{{ ViewClassNamesConstants::FM_CT_SL }}">
                        @foreach($brances as $value)
                            <option value="{{ $value->id }}" @if($appraisal->branch==$value->id) selected @endif>{{ $value->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::CM6 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('employees',__('Employee*'),['class'=>ViewClassNamesConstants::FM_LB]) }}
                    <div class="employee_div">
                        <select name="employee" id="employee" required class="{{ ViewClassNamesConstants::FM_CT_SL }}"></select>
                    </div>
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::CM6 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('appraisal_date',__('Select Month*'),['class'=>ViewClassNamesConstants::FM_LB]) }}
                    {{ Form::text('appraisal_date',null,['class'=>ViewClassNamesConstants::FM_CT_SL.' d_filter','required']) }}
                </div>
            </div>
            <div class="{{ ViewClassNamesConstants::C12 }}">
                <div class="{{ ViewClassNamesConstants::FM_G }}">
                    {{ Form::label('remark',__('Remarks'),['class'=>ViewClassNamesConstants::FM_LB]) }}
                    {{ Form::textarea('remark',null,['class'=>ViewClassNamesConstants::FM_CT,'rows'=>3]) }}
                </div>
            </div>
        </div>
        <div class="{{ ViewClassNamesConstants::RW }}" id="stares"></div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ ViewClassNamesConstants::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ ViewClassNamesConstants::BT_PRM }}">
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
      emp_by_star1_unavailable: 'فشل جلب بيانات النجوم للتقييم.',
      employee_fetch_unavailable: 'فشل جلب قائمة الموظفين.'
    },
    da: {
      emp_by_star_unavailable: 'Kunne ikke hente stjernedata for medarbejderen.',
      emp_by_star1_unavailable: 'Kunne ikke hente stjernedata for evalueringen.',
      employee_fetch_unavailable: 'Kunne ikke hente medarbejderlisten.'
    },
    de: {
      emp_by_star_unavailable: 'Fehler beim Laden der Stern-Daten für den Mitarbeiter.',
      emp_by_star1_unavailable: 'Fehler beim Laden der Stern-Daten für die Bewertung.',
      employee_fetch_unavailable: 'Fehler beim Abrufen der Mitarbeiterliste.'
    },
    en: {
      emp_by_star_unavailable: 'Failed to load star data for the employee.',
      emp_by_star1_unavailable: 'Failed to load star data for the appraisal.',
      employee_fetch_unavailable: 'Failed to fetch employee list.'
    },
    es: {
      emp_by_star_unavailable: 'Error al cargar los datos de estrellas para el empleado.',
      emp_by_star1_unavailable: 'Error al cargar los datos de estrellas para la evaluación.',
      employee_fetch_unavailable: 'Error al obtener la lista de empleados.'
    },
    fr: {
      emp_by_star_unavailable: 'Échec du chargement des données d’étoiles pour l’employé.',
      emp_by_star1_unavailable: 'Échec du chargement des données d’étoiles pour l’évaluation.',
      employee_fetch_unavailable: 'Échec de la récupération de la liste des employés.'
    },
    he: {
      emp_by_star_unavailable: 'לא ניתן לטעון נתוני כוכבים עבור העובד.',
      emp_by_star1_unavailable: 'לא ניתן לטעון נתוני כוכבים עבור ההערכה.',
      employee_fetch_unavailable: 'לא ניתן להביא את רשימת העובדים.'
    },
    it: {
      emp_by_star_unavailable: 'Impossibile caricare i dati delle stelle per il dipendente.',
      emp_by_star1_unavailable: 'Impossibile caricare i dati delle stelle per la valutazione.',
      employee_fetch_unavailable: 'Impossibile recuperare l’elenco dei dipendenti.'
    },
    ja: {
      emp_by_star_unavailable: '従業員のスター データの読み込みに失敗しました。',
      emp_by_star1_unavailable: '評価のスター データの読み込みに失敗しました。',
      employee_fetch_unavailable: '従業員リストの取得に失敗しました。'
    },
    nl: {
      emp_by_star_unavailable: 'Kan stergegevens voor de medewerker niet laden.',
      emp_by_star1_unavailable: 'Kan stergegevens voor de beoordeling niet laden.',
      employee_fetch_unavailable: 'Kan werknemerslijst niet ophalen.'
    },
    pl: {
      emp_by_star_unavailable: 'Nie udało się załadować danych gwiazdek pracownika.',
      emp_by_star1_unavailable: 'Nie udało się załadować danych gwiazdek dla oceny.',
      employee_fetch_unavailable: 'Nie udało się pobrać listy pracowników.'
    },
    pt: {
      emp_by_star_unavailable: 'Falha ao carregar dados de estrelas do funcionário.',
      emp_by_star1_unavailable: 'Falha ao carregar dados de estrelas da avaliação.',
      employee_fetch_unavailable: 'Falha ao buscar a lista de funcionários.'
    },
    'pt-br': {
      emp_by_star_unavailable: 'Falha ao carregar dados de estrelas do funcionário.',
      emp_by_star1_unavailable: 'Falha ao carregar dados de estrelas da avaliação.',
      employee_fetch_unavailable: 'Falha ao buscar a lista de funcionários.'
    },
    ru: {
      emp_by_star_unavailable: 'Не удалось загрузить данные звезд для сотрудника.',
      emp_by_star1_unavailable: 'Не удалось загрузить данные звезд для оценки.',
      employee_fetch_unavailable: 'Не удалось получить список сотрудников.'
    },
    tr: {
      emp_by_star_unavailable: 'Çalışan için yıldız verileri yüklenemedi.',
      emp_by_star1_unavailable: 'Değerlendirme için yıldız verileri yüklenemedi.',
      employee_fetch_unavailable: 'Çalışan listesi alınamadı.'
    },
    zh: {
      emp_by_star_unavailable: '无法加载该员工的星级数据。',
      emp_by_star1_unavailable: '无法加载该评估的星级数据。',
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
        const branchEl = document.getElementById('branch');
        const empEl    = document.getElementById('employee');
        const stEl     = document.getElementById('stares');
        const branchIds   = '{{ $appraisal->branch }}';
        const employeeId  = '{{ $appraisal->employee }}';
        const appraisalId = '{{ $appraisal->id }}';
    
        if (branchEl && branchEl.dataset.listenerAttached !== 'true') {
          branchEl.dataset.listenerAttached = 'true';
          branchEl.addEventListener('change', onBranchChange);
          new MutationObserver((ms, obs) => {
            ms.forEach(m => m.removedNodes.forEach(n => {
              if (n === branchEl) {
                branchEl.removeEventListener('change', onBranchChange);
                obs.disconnect();
              }
            }));
          }).observe(document.body, { childList: true, subtree: true });
        }
    
        if (empEl && empEl.dataset.listenerAttached !== 'true') {
          empEl.dataset.listenerAttached = 'true';
          empEl.addEventListener('change', onEmployeeChange);
          new MutationObserver((ms, obs) => {
            ms.forEach(m => m.removedNodes.forEach(n => {
              if (n === empEl) {
                empEl.removeEventListener('change', onEmployeeChange);
                obs.disconnect();
              }
            }));
          }).observe(document.body, { childList: true, subtree: true });
        }
    
        // initial load
        onBranchChange.call({ value: branchIds });
        loadStars(employeeId, appraisalId);
      });
    
      function onBranchChange() {
        const branchId = this.value ?? '';
        try {
          const url = '{{ route("getemployee") }}';
          if (!url) throw 0;
          $.ajax({
            url,
            type: 'POST',
            dataType: 'json',
            data: {
              branch_id: branchId,
              _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
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
            errorMessage = getLocalizedMessage('employee_fetch_unavailable', document.getElementById('branch') || document.body);
          });
        } catch {
          errorMessage = getLocalizedMessage('employee_fetch_unavailable', document.getElementById('branch') || document.body);
        }
      }
    
      function onEmployeeChange() {
        const empId = this.value ?? '';
        loadStars(empId);
      }
    
      function loadStars(empId, appId = null) {
        try {
          const routeName = appId ? '{{ route("empByStar1") }}' : '{{ route("empByStar") }}';
          const data = { employee: empId, _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '' };
          if (appId) data.appraisal = appId;
          $.ajax({
            url: routeName,
            type: 'POST',
            dataType: 'json',
            data
          })
          .done(resp => {
            const stEl = document.getElementById('stares');
            if (stEl) stEl.innerHTML = resp.html ?? '';
          })
          .fail(() => {
            errorMessage = getLocalizedMessage(appId ? 'emp_by_star1_unavailable' : 'emp_by_star_unavailable', document.getElementById('employee') || document.body);
          });
        } catch {
          errorMessage = getLocalizedMessage(appId ? 'emp_by_star1_unavailable' : 'emp_by_star_unavailable', document.getElementById('employee') || document.body);
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