@php
    use App\Config\Constants\{ViewsConstants, ViewClassNamesConstants as VC};
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    $lang = Utility::fetchUserLang();
    $plan = Utility::getChatGPTSettings();
@endphp

{!! Form::model(
        $contract,
        [
            'route'  => [ViewsConstants::CTC . '.update', $contract->id],
            'method' => 'PUT',
        ]
    ) !!}
    <div class="modal-body">
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="{{ VC::FEND }}">
                @php
                    $generateRoute    = Route::has('generate')
                        ? route('generate', ['contract'])
                        : '#';
                    $generateBtnId    = 'generate-contract-btn';
                    $generateGuardMsg = Utility::fetchLinkMessage(
                        $lang,
                        ViewsConstants::CTC,
                        'contract_generate_route_unavailable'
                    ) ?? 'Generate with AI route is unavailable. Please contact technical support or your domain administrator.';
                @endphp
                <a
                    id="{{ $generateBtnId }}"
                    href="#"
                    data-size="md"
                    class="{{ VC::BT_SM_PM }} btn-icon"
                    data-ajax-popup-over="true"
                    data-url="{{ $generateRoute }}"
                    data-guard-msg="{{ $generateGuardMsg }}"
                    data-bs-placement="top"
                    data-title="{{ __('Generate content with AI') }}"
                >
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
                @push(StacksConstants::ADM_SCR_PG)
                    <script defer>
                        (() => {
                            const btn = document.getElementById('{{ $generateBtnId }}');
                            if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                            btn.setAttribute('data-listener-active', 'true');
                            btn.addEventListener('click', event => {
                                try {
                                    const url = btn.getAttribute('data-url');
                                    if (!url || url === '#') {
                                        event.preventDefault();
                                        const msg           = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                        let container       = document.getElementById('toast-container');
                                        if (!container) {
                                            container       = document.createElement('div');
                                            container.id    = 'toast-container';
                                            document.body.appendChild(container);
                                        }
                                        if (bootstrapLink && window.bootstrap) {
                                            const toastEl      = document.createElement('div');
                                            toastEl.className  = 'toast';
                                            toastEl.setAttribute('role', 'alert');
                                            toastEl.setAttribute('aria-live', 'assertive');
                                            toastEl.setAttribute('aria-atomic', 'true');
                                            const body         = document.createElement('div');
                                            body.className     = 'toast-body';
                                            body.textContent   = msg;
                                            toastEl.appendChild(body);
                                            container.appendChild(toastEl);
                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                        } else {
                                            alert(msg);
                                        }
                                        btn.setAttribute('data-failed-route', 'true');
                                    }
                                } catch (e) {}
                            });
                        })();
                    </script>
                @endpush
            </div>
        @endif
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('subject', __('Subject'), ['class' => VC::FM_LB]) }}
                {{ Form::text('subject', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('client_name', __('Client'), ['class' => VC::FM_LB]) }}
                {{ Form::select(
                    'client_name',
                    $clients,
                    null,
                    [
                        'class' => VC::FM_CT_SL . ' client_select',
                        'id'    => 'client_select',
                    ]
                ) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('project', __('Project'), ['class' => VC::FM_LB]) }}
                <div class="project-div">
                    {{ Form::select(
                        'project',
                        $project,
                        null,
                        [
                            'class' => VC::FM_CT . ' project_select',
                            'id'    => 'project_id',
                            'name'  => 'project_id',
                        ]
                    ) }}
                </div>
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('type', __('Contract Type'), ['class' => VC::FM_LB]) }}
                {{ Form::select(
                    'type',
                    $contractTypes,
                    null,
                    [
                        'class'       => VC::FM_CT,
                        'data-toggle' => 'select',
                        'required'    => 'required',
                    ]
                ) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('value', __('Contract Value'), ['class' => VC::FM_LB]) }}
                {{ Form::number(
                    'value',
                    null,
                    [
                        'class'    => VC::FM_CT,
                        'required' => 'required',
                        'stage'    => '0.01',
                    ]
                ) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date(
                    'start_date',
                    null,
                    [
                        'class'    => VC::FM_CT,
                        'required' => 'required',
                    ]
                ) }}
            </div>
            <div class="{{ VC::FM_G }} {{ VC::CM6 }}">
                {{ Form::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                {{ Form::date(
                    'end_date',
                    null,
                    [
                        'class'    => VC::FM_CT,
                        'required' => 'required',
                    ]
                ) }}
            </div>
        </div>
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {!! Form::textarea(
                    'description',
                    null,
                    [
                        'class' => VC::FM_CT,
                        'rows'  => '3',
                    ]
                ) !!}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input
            type="button"
            value="{{ __('Cancel') }}"
            class="{{ VC::BT_LG }}"
            data-bs-dismiss="modal"
        >
        <input
            type="submit"
            value="{{ __('Update') }}"
            class="{{ VC::BT_PRM }}"
        >
    </div>
{{Form::close()}}

<script>
    window.translations = {
      ar: {
        choices_init_failed: 'فشل تهيئة قائمة التحديد المتعدد.',
        project_fetch_failed: 'فشل جلب المشاريع.'
      },
      da: {
        choices_init_failed: 'Kan ikke initialisere multi-select.',
        project_fetch_failed: 'Kunne ikke hente projekter.'
      },
      de: {
        choices_init_failed: 'Initialisierung der Mehrfachauswahl fehlgeschlagen.',
        project_fetch_failed: 'Projekte konnten nicht abgerufen werden.'
      },
      en: {
        choices_init_failed: 'Failed to initialize multi-select.',
        project_fetch_failed: 'Failed to fetch projects.'
      },
      es: {
        choices_init_failed: 'Error al inicializar multi-select.',
        project_fetch_failed: 'Error al obtener proyectos.'
      },
      fr: {
        choices_init_failed: 'Échec de l’initialisation du multi-select.',
        project_fetch_failed: 'Échec de la récupération des projets.'
      },
      he: {
        choices_init_failed: 'לא ניתן לאתחל בחירה מרובה.',
        project_fetch_failed: 'לא ניתן להביא את הפרויקטים.'
      },
      it: {
        choices_init_failed: 'Impossibile inizializzare il multi-select.',
        project_fetch_failed: 'Recupero dei progetti non riuscito.'
      },
      ja: {
        choices_init_failed: 'マルチセレクトの初期化に失敗しました。',
        project_fetch_failed: 'プロジェクトの取得に失敗しました。'
      },
      nl: {
        choices_init_failed: 'Initialisatie van multi-select mislukt.',
        project_fetch_failed: 'Ophalen van projecten mislukt.'
      },
      pl: {
        choices_init_failed: 'Nie można zainicjalizować multi-select.',
        project_fetch_failed: 'Nie udało się pobrać projektów.'
      },
      pt: {
        choices_init_failed: 'Falha ao inicializar multi-select.',
        project_fetch_failed: 'Falha ao buscar projetos.'
      },
      'pt-br': {
        choices_init_failed: 'Falha ao inicializar multi-select.',
        project_fetch_failed: 'Falha ao buscar projetos.'
      },
      ru: {
        choices_init_failed: 'Не удалось инициализировать мультивыбор.',
        project_fetch_failed: 'Не удалось получить проекты.'
      },
      tr: {
        choices_init_failed: 'Çoklu seçim başlatılamadı.',
        project_fetch_failed: 'Projeler alınamadı.'
      },
      zh: {
        choices_init_failed: '初始化多选失败。',
        project_fetch_failed: '获取项目失败。'
      }
    };
</script>
<script defer src="{{asset('assets/js/plugins/choices.min.js')}}"></script>
<script defer>
    (() => {
      const ERR_FB = '# ERROR';
      const GUARD_MSG = 'data-guard-msg';
      const CLIENT_FLAG = 'data-client-localized';
      const LANG_KEY = 'erp-np-lang';
    
      function getMsg(key, el) {
        let msg = ERR_FB;
        if (el.getAttribute(CLIENT_FLAG) === 'true') {
          msg = el.getAttribute(GUARD_MSG) || msg;
        } else {
          let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
            .toLowerCase().replace(/_/g, '-');
          lang = lang === 'pt-br' ? lang : lang.slice(0,2);
          msg = translations?.[lang]?.[key] ||
                el.getAttribute(GUARD_MSG) ||
                translations?.['en']?.[key] ||
                msg;
          if (msg !== ERR_FB) {
            el.setAttribute(GUARD_MSG, msg);
            el.setAttribute(CLIENT_FLAG, 'true');
          }
        }
        return msg;
      }
    
      function showError(message) {
        try {
          let container = document.getElementById('toast-container');
          if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
          }
          const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
          if (bs) {
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
      }
      let errorMessage = '';
      const onPointerUp = () => {
        if (errorMessage) {
          showError(errorMessage);
          errorMessage = '';
        }
      };
      document.addEventListener('pointerup', onPointerUp);
      new MutationObserver((muts, obs) => {
        muts.forEach(m => m.removedNodes.forEach(n => {
          if (n === document.documentElement) {
            document.removeEventListener('pointerup', onPointerUp);
            obs.disconnect();
          }
        }));
      }).observe(document.body, { childList:true, subtree:true });
      document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.multi-select').forEach(el => {
          if (el.dataset.listenerAttached === 'true') return;
          el.dataset.listenerAttached = 'true';
          try {
            new Choices(`#${el.id}`, { removeItemButton: true });
          } catch {
            errorMessage = getMsg('choices_init_failed', el);
          }
        });
        const obs1 = new MutationObserver((m, obs) => {
          m.forEach(mut => mut.removedNodes.forEach(node => {
            if (node.matches && node.matches('.multi-select')) {
              obs.disconnect();
            }
          }));
        });
        obs1.observe(document.body, { childList:true, subtree:true });
        document.querySelectorAll('.client_select').forEach(el => {
          if (el.dataset.listenerAttached === 'true') return;
          el.dataset.listenerAttached = 'true';
          const onChange = async () => {
            try {
              const clientId = el.value ?? '';
              const url = `{{ url('contract/clients/select') }}/${clientId}`;
              if (!url) throw new Error('project_fetch_failed');
              const data = await $.ajax({ url, type: 'GET', dataType: 'json' });
              const proj = document.getElementById('project_id');
              if (!proj) return;
              proj.innerHTML = '';
              data.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.name;
                proj.appendChild(opt);
              });
            } catch (e) {
              errorMessage = getMsg(e.message, el);
            }
          };
          el.addEventListener('change', onChange);
          const obs2 = new MutationObserver((m, obs) => {
            m.forEach(mut => mut.removedNodes.forEach(node => {
              if (node === el) {
                el.removeEventListener('change', onChange);
                obs.disconnect();
              }
            }));
          });
          obs2.observe(document.body, { childList:true, subtree:true });
          onChange();
        });
      });
    })();
</script>
