@php
    use Illuminate\Support\Facades\Route;
    use App\Models\Utility;
    use App\Config\Constants\{
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC
    };

    $lang                = Utility::fetchUserLang();
    $copyStoreRoute      = Route::has(ViewsConstants::CTC.'.copy.store')
        ? route(ViewsConstants::CTC.'.copy.store', $contract->id)
        : '#';
    $copyFormId          = 'copy-contract-form-' . $contract->id;
    $copyGuardMsg        = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CTC,
        'copy_store_route_unavailable'
    ) ?? 'Contract copy route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{!! Collective\Html\FormFacade::model($contract, [
    'route'            => $copyStoreRoute,
    'method'         => 'POST',
    'id'             => $copyFormId,
    'data-url'       => $copyStoreRoute,
    'data-guard-msg' => $copyGuardMsg,
]) !!}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="form-group col-md-12">
                {{ Collective\Html\FormFacade::label('subject', __('Subject'), ['class' => VC::FM_LB]) }}
                {{ Collective\Html\FormFacade::text('subject', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Collective\Html\FormFacade::label('client', __('Client'), ['class' => VC::FM_LB]) }}
                {{ Collective\Html\FormFacade::select('client', $clients, null, ['class' => VC::FM_CT_SL . ' client_select', 'id' => 'client_select']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Collective\Html\FormFacade::label('project', __('Project'), ['class' => VC::FM_LB]) }}
                <div class="project-div">
                    {{ Collective\Html\FormFacade::select('project', $project, null, ['class' => VC::FM_CT_SL . ' project_select', 'id' => 'project_id', 'name' => 'project_id[]']) }}
                </div>
            </div>
            <div class="form-group col-md-6">
                {{ Collective\Html\FormFacade::label('type', __('Contract Type'), ['class' => VC::FM_LB]) }}
                {{ Collective\Html\FormFacade::select('type', $contractTypes, null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Collective\Html\FormFacade::label('value', __('Contract Value'), ['class' => VC::FM_LB]) }}
                {{ Collective\Html\FormFacade::number('value', null, ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Collective\Html\FormFacade::label('start_date', __('Start Date'), ['class' => VC::FM_LB]) }}
                {{ Collective\Html\FormFacade::date('start_date', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="form-group col-md-6">
                {{ Collective\Html\FormFacade::label('end_date', __('End Date'), ['class' => VC::FM_LB]) }}
                {{ Collective\Html\FormFacade::date('end_date', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
        </div>
        <div class="{{ VC::RW }}">
            <div class="form-group col-md-12">
                {{ Collective\Html\FormFacade::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {!! Collective\Html\FormFacade::textarea('description', null, ['class' => VC::FM_CT, 'rows' => 3]) !!}
            </div>
        </div>
        <div class="modal-footer {{ VC::PX3 }}">
            <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Close') }}</button>
            {{ Collective\Html\FormFacade::submit(__('Copy'), ['class' => VC::BT_PRM]) }}
        </div>
    </div>
{{ Collective\Html\FormFacade::close() }}

    <script>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
        ar: {
            choices_init_failed: 'فشل تهيئة قائمة التحديد المتعدد.',
            project_fetch_failed: 'فشل جلب المشاريع.',
        },
        da: {
            choices_init_failed: 'Kan ikke initialisere multi-select.',
            project_fetch_failed: 'Kunne ikke hente projekter.',
        },
        de: {
            choices_init_failed: 'Initialisierung der Mehrfachauswahl fehlgeschlagen.',
            project_fetch_failed: 'Projekte konnten nicht abgerufen werden.',
        },
        en: {
            choices_init_failed: 'Failed to initialize multi-select.',
            project_fetch_failed: 'Failed to fetch projects.',
        },
        es: {
            choices_init_failed: 'Error al inicializar multi-select.',
            project_fetch_failed: 'Error al obtener proyectos.',
        },
        fr: {
            choices_init_failed: 'Échec de l’initialisation du multi-select.',
            project_fetch_failed: 'Échec de la récupération des projets.',
        },
        he: {
            choices_init_failed: 'לא ניתן לאתחל בחירה מרובה.',
            project_fetch_failed: 'לא ניתן להביא את הפרויקטים.',
        },
        it: {
            choices_init_failed: 'Impossibile inizializzare il multi-select.',
            project_fetch_failed: 'Recupero dei progetti non riuscito.',
        },
        ja: {
            choices_init_failed: 'マルチセレクトの初期化に失敗しました。',
            project_fetch_failed: 'プロジェクトの取得に失敗しました。',
        },
        nl: {
            choices_init_failed: 'Initialisatie van multi-select mislukt.',
            project_fetch_failed: 'Ophalen van projecten mislukt.',
        },
        pl: {
            choices_init_failed: 'Nie można zainicjalizować multi-select.',
            project_fetch_failed: 'Nie udało się pobrać projektów.',
        },
        pt: {
            choices_init_failed: 'Falha ao inicializar multi-select.',
            project_fetch_failed: 'Falha ao buscar projetos.',
        },
        'pt-br': {
            choices_init_failed: 'Falha ao inicializar multi-select.',
            project_fetch_failed: 'Falha ao buscar projetos.',
        },
        ru: {
            choices_init_failed: 'Не удалось инициализировать мультивыбор.',
            project_fetch_failed: 'Не удалось получить проекты.',
        },
        tr: {
            choices_init_failed: 'Çoklu seçim başlatılamadı.',
            project_fetch_failed: 'Projeler alınamadı.',
        },
        zh: {
            choices_init_failed: '初始化多选失败。',
            project_fetch_failed: '获取项目失败。',
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
<script defer src="{{asset('assets/js/plugins/choices.min.js')}}"></script>
<script defer>
    (() => {
    const ERR_FB = '# ERROR';
    const GUARD_MSG = 'data-guard-msg';
    const CLIENT_FLAG = 'data-client-localized';
    const LANG_KEY = 'erp-np-lang';
    let errorMessage = '';
    
    const getLocalizedMessage = (key, el) => {
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
        if (el.dataset.choicesInitialized === 'true') return;
        el.dataset.choicesInitialized = 'true';
        try {
            new Choices(`#${el.id}`, { removeItemButton: true });
        } catch {
            errorMessage = getLocalizedMessage('choices_init_failed', el);
        }
        });
        const clientEl = document.querySelector('#type')
        || document.querySelector('.client_select');
        if (clientEl && clientEl.dataset.listenerAttached !== 'true') {
        clientEl.dataset.listenerAttached = 'true';
    
        const onClientChange = () => {
            try {
            const clientId = clientEl.value ?? '';
            const url = `{{ url('contract/clients/select') }}/${clientId}`;
            if (!url) throw new Error('project_fetch_failed');
    
            $.ajax({
                url,
                type: 'GET',
                dataType: 'json'
            })
            .done(data => {
                const projContainer = document.getElementById('project_id');
                if (!projContainer) return;
                projContainer.innerHTML = '';
                data.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.name;
                projContainer.appendChild(opt);
                });
            })
            .fail(() => {
                throw new Error('project_fetch_failed');
            });
            } catch (e) {
            errorMessage = getLocalizedMessage(e.message, clientEl);
            }
        };
    
        clientEl.addEventListener('change', onClientChange);
        new MutationObserver((muts, obs) => {
            muts.forEach(m => m.removedNodes.forEach(n => {
            if (n === clientEl) {
                clientEl.removeEventListener('change', onClientChange);
                obs.disconnect();
            }
            }));
        }).observe(document.body, { childList:true, subtree:true });
        onClientChange();
        }
    });
    })();
</script>
