@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth,Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Deals')}} @if($pipeline) - {{$pipeline->name}} @endif
@endsection
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}" id="main-style-link">
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('css/summernote/summernote-bs4.js') }}"></script>
    <script defer src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>
        <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
          ar: {
            deals_order_failed: 'فشل ترتيب الصفقات.',
            pipeline_change_failed: 'فشل تغيير مسار العملية.'
          },
          da: {
            deals_order_failed: 'Kunne ikke sortere handler.',
            pipeline_change_failed: 'Kunne ikke ændre pipeline.'
          },
          de: {
            deals_order_failed: 'Reihenfolge der Deals konnte nicht gespeichert werden.',
            pipeline_change_failed: 'Pipeline konnte nicht gewechselt werden.'
          },
          en: {
            deals_order_failed: 'Failed to reorderViewsConstants::DL. .',
            pipeline_change_failed: 'Failed to change pipeline.'
          },
          es: {
            deals_order_failed: 'Error al reordenar las ofertas.',
            pipeline_change_failed: 'Error al cambiar el pipeline.'
          },
          fr: {
            deals_order_failed: 'Échec du réordonnancement des transactions.',
            pipeline_change_failed: 'Échec du changement de pipeline.'
          },
          he: {
            deals_order_failed: 'עדכון סדר העסקאות נכשל.',
            pipeline_change_failed: 'שינוי הצנרת נכשל.'
          },
          it: {
            deals_order_failed: 'Ripristino ordine offerte non riuscito.',
            pipeline_change_failed: 'Impossibile cambiare pipeline.'
          },
          ja: {
            deals_order_failed: '取引の並び替えに失敗しました。',
            pipeline_change_failed: 'パイプラインの変更に失敗しました。'
          },
          nl: {
            deals_order_failed: 'Kon deals niet opnieuw ordenen.',
            pipeline_change_failed: 'Kon pipeline niet wijzigen.'
          },
          pl: {
            deals_order_failed: 'Nie udało się zmienić kolejności transakcji.',
            pipeline_change_failed: 'Nie udało się zmienić pipeline.'
          },
          pt: {
            deals_order_failed: 'Falha ao reordenar negócios.',
            pipeline_change_failed: 'Falha ao alterar pipeline.'
          },
          'pt-br': {
            deals_order_failed: 'Falha ao reordenar negócios.',
            pipeline_change_failed: 'Falha ao alterar pipeline.'
          },
          ru: {
            deals_order_failed: 'Не удалось изменить порядок сделок.',
            pipeline_change_failed: 'Не удалось сменить воронку.'
          },
          tr: {
            deals_order_failed: 'Anlaşmaların sıralaması yapılamadı.',
            pipeline_change_failed: 'Pipeline değiştirilemedi.'
          },
          zh: {
            deals_order_failed: '重新排序交易失败。',
            pipeline_change_failed: '更改管道失败。'
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
        const ERR_FB = '# ERROR';
        const FL_CLIENT = 'data-client-localized';
        const FL_GUARD  = 'data-guard-msg';
        const LANG_KEY  = 'erp-np-lang';
        let errorMessage = '';
        
        const getMsg = (key, el) => {
            let msg = ERR_FB;
            if (el.getAttribute(FL_CLIENT) === 'true') {
            msg = el.getAttribute(FL_GUARD) || msg;
            } else {
            let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
                .toLowerCase().replace(/_/g,'-');
            lang = lang === 'pt-br' ? lang : lang.slice(0,2);
            msg = translations?.[lang]?.[key]
                ?? el.getAttribute(FL_GUARD)
                ?? translations?.['en']?.[key]
                ?? msg;
            if (msg !== ERR_FB) {
                el.setAttribute(FL_GUARD, msg);
                el.setAttribute(FL_CLIENT, 'true');
            }
            }
            return msg;
        };
        
        const showError = message => {
            try {
            let c = document.getElementById('toast-container');
            if (!c) {
                c = document.createElement('div');
                c.id = 'toast-container';
                document.body.appendChild(c);
            }
            const bs = !!document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
            if (bs) {
                const t = document.createElement('div');
                t.className = 'toast';
                t.setAttribute('role','alert');
                t.setAttribute('aria-live','assertive');
                t.setAttribute('aria-atomic','true');
                const b = document.createElement('div');
                b.className = 'toast-body';
                b.textContent = message;
                t.appendChild(b);
                c.appendChild(t);
                bootstrap.Toast.getOrCreateInstance(t).show();
            } else {
                alert(message);
            }
            } catch {
            alert(message);
            }
        };
        
        const onUp = () => {
            if (errorMessage) {
            showError(errorMessage);
            errorMessage = '';
            }
        };
        document.addEventListener('pointerup', onUp);
        new MutationObserver((m, obs) => {
            m.forEach(mut => Array.from(mut.removedNodes).forEach(n => {
            if (n === document.documentElement) {
                document.removeEventListener('pointerup', onUp);
                obs.disconnect();
            }
            }));
        }).observe(document.body,{ childList:true, subtree:true });
        
        document.addEventListener('DOMContentLoaded', () => {
            try {
            $('[data-plugin="dragula"]').each(function() {
                const $el = $(this);
                const containers = $el.data('containers');
                const els = containers
                ? containers.map(id => document.getElementById(id)).filter(Boolean)
                : [this];
                const handle = $el.data('handleclass');
                const drake = handle
                ? dragula(els, { moves: (el, s, handleEl) => handleEl.classList.contains(handle) })
                : dragula(els);
                drake.on('drop', (el, target, source) => {
                try {
                    const order = Array.from(target.children).map((d,i) => d.getAttribute('data-id'));
                    const id = el.getAttribute('data-id');
                    const old_status = source.dataset.status;
                    const new_status = target.dataset.status;
                    const stage_id = target.getAttribute('data-id');
                    const pipeline_id = '{{ $pipeline->id }}';
                    $(source).parent().find('.count').text(source.children.length);
                    $(target).parent().find('.count').text(target.children.length);
                    $.ajax({
                    url: '{{ route(ViewsConstants::DL.".order") }}',
                    type: 'POST',
                    data: { deal_id:id, stage_id, order, new_status, old_status, pipeline_id,
                            _token: $('meta[name="csrf-token"]').attr('content') },
                    })
                    .fail(() => { throw new Error('deals_order_failed'); });
                } catch (e) {
                    errorMessage = getMsg(e.message, document.body);
                }
                });
            });
            } catch {
            errorMessage = getMsg('deals_order_failed', document.body);
            }
            const pipelineSelect = document.getElementById('default_pipeline_id');
            if (pipelineSelect) {
            pipelineSelect.addEventListener('change', () => {
                try {
                document.getElementById('change-pipeline').submit();
                } catch {
                errorMessage = getMsg('pipeline_change_failed', pipelineSelect);
                }
            });
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
    <li class="breadcrumb-item">{{__('Deal')}}</li>
@endsection
@php
    $ns = ViewsConstants::DL;
    $changeName = "{$ns}.change.pipeline";
    $hasChange = Route::has($changeName);
    $changeGuardMsg = Utility::fetchLinkMessage(
        $lang,
        $ns,
        'change_pipeline_deal_route_unavailable'
    ) ?? 'Deal change pipeline route is unavailable. Please contact technical support or your domain administrator.';
    $listName = "{$ns}.list";
    $hasList = Route::has($listName);
    $listGuardMsg = Utility::fetchLinkMessage(
        $lang,
        $ns,
        'deals_list_route_unavailable'
    ) ?? 'Deal list route is unavailable. Please contact technical support or your domain administrator.';
    $createName = "{$ns}.create";
    $hasCreate = Route::has($createName);
    $createGuardMsg = Utility::fetchLinkMessage(
        $lang,
        $ns,
        'deals_create_route_unavailable'
    ) ?? 'Deal create route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @if($hasChange)
            {{ Form::open([
                'route'          => $changeName,
                'id'             => 'change-pipeline-form',
                'class'          => VC::BT_SM,
                'data-guard-msg' => $changeGuardMsg
            ]) }}
        @else
            {{ Form::open([
                'url'            => '#',
                'id'             => 'change-pipeline-form',
                'class'          => VC::BT_SM,
                'data-guard-msg' => $changeGuardMsg
            ]) }}
        @endif

        {{ Form::select(
            'default_pipeline_id',
            $pipelines,
            $pipeline->id,
            [
                'class' => VC::FM_CT . ' select me-4',
                'id'    => 'default_pipeline_id'
            ]
        ) }}
        {{ Form::close() }}

        <a
            id="deal-list-btn"
            href="{{ $hasList ? route($listName) : '#' }}"
            data-url="{{ $hasList ? route($listName) : '#' }}"
            data-guard-msg="{{ $listGuardMsg }}"
            data-size="lg"
            data-bs-toggle="tooltip"
            title="{{ __('List View') }}"
            class="{{ VC::BT_SM_PM }}"
        >
            <i class="{{ VC::TI_LT }}"></i>
        </a>

        <a
            id="deal-create-btn"
            href="{{ $hasCreate ? route($createName) : '#' }}"
            data-url="{{ $hasCreate ? route($createName) : '#' }}"
            data-guard-msg="{{ $createGuardMsg }}"
            data-size="lg"
            data-ajax-popup="true"
            data-bs-toggle="tooltip"
            title="{{ __('Create New Deal') }}"
            data-title="{{ __('Create Deal') }}"
            class="{{ VC::BT_SM_PM }}"
        >
            <i class="{{ VC::TI_PLS }}"></i>
        </a>
    </div>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const form = document.getElementById('change-pipeline-form');
            if (!form || form.getAttribute('data-listener-active') === 'true') return;
            form.setAttribute('data-listener-active', 'true');
            form.addEventListener('submit', e => {
                try {
                    const action = form.getAttribute('action') ?? '#';
                    if (action !== '#') return;
                    e.preventDefault();
                    const msg = form.getAttribute('data-guard-msg') ?? '# ERROR';
                    const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                    let container = document.getElementById('toast-container');
                    if (!container) {
                        container = document.createElement('div');
                        container.id = 'toast-container';
                        document.body.appendChild(container);
                    }
                    if (bs) {
                        const toast = document.createElement('div');
                        toast.className = 'toast';
                        toast.setAttribute('role','alert');
                        toast.setAttribute('aria-live','assertive');
                        toast.setAttribute('aria-atomic','true');
                        const body = document.createElement('div');
                        body.className = 'toast-body';
                        body.textContent = msg;
                        toast.appendChild(body);
                        container.appendChild(toast);
                        bootstrap.Toast.getOrCreateInstance(toast).show();
                    } else {
                        alert(msg);
                    }
                    form.setAttribute('data-failed-route', 'true');
                } catch {}
            });
        })();
    </script>
    <script defer>
        (() => {
            const btn = document.getElementById('deal-list-btn');
            if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
            btn.setAttribute('data-listener-active', 'true');
            btn.addEventListener('click', e => {
                try {
                    const url = btn.getAttribute('data-url') ?? '#';
                    if (url !== '#') return;
                    e.preventDefault();
                    const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                    const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                    let container = document.getElementById('toast-container');
                    if (!container) {
                        container = document.createElement('div');
                        container.id = 'toast-container';
                        document.body.appendChild(container);
                    }
                    if (bs) {
                        const toast = document.createElement('div');
                        toast.className = 'toast';
                        toast.setAttribute('role','alert');
                        toast.setAttribute('aria-live','assertive');
                        toast.setAttribute('aria-atomic','true');
                        const body = document.createElement('div');
                        body.className = 'toast-body';
                        body.textContent = msg;
                        toast.appendChild(body);
                        container.appendChild(toast);
                        bootstrap.Toast.getOrCreateInstance(toast).show();
                    } else {
                        alert(msg);
                    }
                    btn.setAttribute('data-failed-route', 'true');
                } catch {}
            });
        })();
    </script>
    <script defer>
        (() => {
            const btn = document.getElementById('deal-create-btn');
            if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
            btn.setAttribute('data-listener-active', 'true');
            btn.addEventListener('click', e => {
                try {
                    const url = btn.getAttribute('data-url') ?? '#';
                    if (url !== '#') return;
                    e.preventDefault();
                    const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                    const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                    let container = document.getElementById('toast-container');
                    if (!container) {
                        container = document.createElement('div');
                        container.id = 'toast-container';
                        document.body.appendChild(container);
                    }
                    if (bs) {
                        const toast = document.createElement('div');
                        toast.className = 'toast';
                        toast.setAttribute('role','alert');
                        toast.setAttribute('aria-live','assertive');
                        toast.setAttribute('aria-atomic','true');
                        const body = document.createElement('div');
                        body.className = 'toast-body';
                        body.textContent = msg;
                        toast.appendChild(body);
                        container.appendChild(toast);
                        bootstrap.Toast.getOrCreateInstance(toast).show();
                    } else {
                        alert(msg);
                    }
                    btn.setAttribute('data-failed-route', 'true');
                } catch {}
            });
        })();
    </script>
@endpush
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS3 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD }}-body">
                    <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }}">
                        <div class="{{ VC::C_AT }} {{ VC::MB3 }} {{ VC::MB_SM0 }}">
                            <small class="{{ VC::TXT_MT }}">{{ __('Total Deals') }}</small>
                            <h4 class="{{ VC::MB0 }}">{{ $cnt_deal['total'] }}</h4>
                        </div>
                        <div class="{{ VC::C_AT }}">
                            <div class="theme-avatar bg-info">
                                <i class="ti ti-layers-difference"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VC::CS3 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD }}-body">
                    <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }}">
                        <div class="{{ VC::C_AT }} {{ VC::MB3 }} {{ VC::MB_SM0 }}">
                            <small class="{{ VC::TXT_MT }}">{{ __('This Month Total Deals') }}</small>
                            <h4 class="{{ VC::MB0 }}">{{ $cnt_deal['this_month'] }}</h4>
                        </div>
                        <div class="{{ VC::C_AT }}">
                            <div class="theme-avatar bg-primary">
                                <i class="ti ti-layers-difference"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VC::CS3 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD }}-body">
                    <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }}">
                        <div class="{{ VC::C_AT }} {{ VC::MB3 }} {{ VC::MB_SM0 }}">
                            <small class="{{ VC::TXT_MT }}">{{ __('This Week Total Deals') }}</small>
                            <h4 class="{{ VC::MB0 }}">{{ $cnt_deal['this_week'] }}</h4>
                        </div>
                        <div class="{{ VC::C_AT }}">
                            <div class="theme-avatar bg-warning">
                                <i class="ti ti-layers-difference"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VC::CS3 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD }}-body">
                    <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }}">
                        <div class="{{ VC::C_AT }} {{ VC::MB3 }} {{ VC::MB_SM0 }}">
                            <small class="{{ VC::TXT_MT }}">{{ __('Last 30 Days Total Deals') }}</small>
                            <h4 class="{{ VC::MB0 }}">{{ $cnt_deal['last_30days'] }}</h4>
                        </div>
                        <div class="{{ VC::C_AT }}">
                            <div class="theme-avatar bg-danger">
                                <i class="ti ti-layers-difference"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="{{ VC::RW }}">
        @php
            $stages = $pipeline->stages;
            $json = [];
            foreach ($stages as $stage){
                $json[] = 'task-list-'.$stage->id;
            }
        @endphp
        <div class="row kanban-wrapper horizontal-scroll-cards" data-containers='{!! json_encode($json) !!}' data-plugin="dragula">
            @foreach($stages as $stage)
                @php($deals = $stage->deals())
                <div class="{{ VC::C_AT }}">
                    <div class="{{ VC::CD }}">
                        <div class="{{ VC::CD }}-header">
                            <div class="{{ VC::FEND }}">
                                <span class="{{ VC::BT_SM_PM }} btn-icon count">
                                    {{ count($deals) }}
                                </span>
                            </div>
                            <h4 class="{{ VC::MB0 }}">{{ $stage->name }}</h4>
                        </div>
                        <div class="{{ VC::CD }}-body kanban-box" id="task-list-{{ $stage->id }}" data-id="{{ $stage->id }}">
                            @foreach($deals as $deal)
                                <div class="{{ VC::CD }}" data-id="{{ $deal->id }}">
                                    <div class="{{ VC::PT3 }} {{ VC::PS3 }}">
                                        @foreach($deal->labels() as $label)
                                            <div class="badge-xs badge bg-{{ $label->color }} {{ VC::P4 }} {{ VC::PX3 }} {{ VC::PY2 }}">{{ $label->name }}</div>
                                        @endforeach
                                    </div>
                                    <div class="{{ VC::CD }}-header border-0 pb-0 position-relative">
                                        <h5>
                                            @php
                                                $namespace    = ViewsConstants::DL;
                                                $routeName    = "{$namespace}.show";
                                                $showRoute    = Route::has($routeName) && $deal->is_active
                                                    ? route($routeName, $deal->id)
                                                    : '#';
                                                $showGuardMsg = Utility::fetchLinkMessage(
                                                    $lang,
                                                    $namespace,
                                                    'deal_show_route_unavailable'
                                                ) ?? 'Deal show route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <a
                                                id="deal-show-btn-{{ $deal->id }}"
                                                href="{{ $showRoute }}"
                                                data-url="{{ $showRoute }}"
                                                data-guard-msg="{{ $showGuardMsg }}"
                                                class="{{ VC::BT_OUTPM }}"
                                            >
                                                {{ $deal->name }}
                                            </a>
                                            @push(StacksConstants::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        const btn = document.getElementById('deal-show-btn-{{ $deal->id }}');
                                                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                        btn.setAttribute('data-listener-active', 'true');
                                                        btn.addEventListener('click', e => {
                                                            try {
                                                                const url = btn.getAttribute('data-url') ?? '#';
                                                                if (url !== '#') return;
                                                                e.preventDefault();
                                                                const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                let container = document.getElementById('toast-container');
                                                                if (!container) {
                                                                    container = document.createElement('div');
                                                                    container.id = 'toast-container';
                                                                    document.body.appendChild(container);
                                                                }
                                                                if (bs) {
                                                                    const toast = document.createElement('div');
                                                                    toast.className = 'toast';
                                                                    toast.setAttribute('role','alert');
                                                                    toast.setAttribute('aria-live','assertive');
                                                                    toast.setAttribute('aria-atomic','true');
                                                                    const body = document.createElement('div');
                                                                    body.className = 'toast-body';
                                                                    body.textContent = msg;
                                                                    toast.appendChild(body);
                                                                    container.appendChild(toast);
                                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                } else {
                                                                    alert(msg);
                                                                }
                                                                btn.setAttribute('data-failed-route', 'true');
                                                            } catch {}
                                                        });
                                                    })();
                                                </script>
                                            @endpush
                                        </h5>
                                        <div class="{{ VC::CD }}-header-right">
                                            @if($user?->type != 'client')
                                                <div class="btn-group card-option">
                                                    <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown">
                                                        <i class="{{ VC::TD_DOTV }}"></i>
                                                    </button>
                                                    <div class="{{ VC::DRP_MN_EM }}">
                                                        @can('edit deal')
                                                            @php
                                                                $ns                = ViewsConstants::DL;
                                                                $labelsRouteName   = "{$ns}.labels";
                                                                $labelsRoute       = Route::has($labelsRouteName)
                                                                    ? route($labelsRouteName, $deal->id)
                                                                    : '#';
                                                                $labelsGuardMsg    = Utility::fetchLinkMessage($lang, $ns, 'deals_labels_route_unavailable')
                                                                    ?? 'Deal labels route is unavailable. Please contact technical support or your domain administrator.';
                                                                $editRouteName     = "{$ns}.edit";
                                                                $editRoute         = Route::has($editRouteName)
                                                                    ? route($editRouteName, $deal->id)
                                                                    : '#';
                                                                $editGuardMsg      = Utility::fetchLinkMessage($lang, $ns, 'deals_edit_route_unavailable')
                                                                    ?? 'Deal edit route is unavailable. Please contact technical support or your domain administrator.';
                                                            @endphp
                                                            <a
                                                                id="deal-labels-btn-{{ $deal->id }}"
                                                                href="{{ $labelsRoute }}"
                                                                data-url="{{ $labelsRoute }}"
                                                                data-guard-msg="{{ $labelsGuardMsg }}"
                                                                data-size="md"
                                                                data-ajax-popup="true"
                                                                class="dropdown-item"
                                                            >
                                                                <i class="ti ti-bookmark"></i> <span>{{ __('Labels') }}</span>
                                                            </a>
                                                            <a
                                                                id="deal-edit-btn-{{ $deal->id }}"
                                                                href="{{ $editRoute }}"
                                                                data-url="{{ $editRoute }}"
                                                                data-guard-msg="{{ $editGuardMsg }}"
                                                                data-size="lg"
                                                                data-ajax-popup="true"
                                                                class="dropdown-item"
                                                            >
                                                                <i class="{{ VC::TI_PC }}"></i> <span>{{ __('Edit') }}</span>
                                                            </a>
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (() => {
                                                                        const btn = document.getElementById('deal-labels-btn-{{ $deal->id }}');
                                                                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                        btn.setAttribute('data-listener-active','true');
                                                                        btn.addEventListener('click', e => {
                                                                            try {
                                                                                const url = btn.getAttribute('data-url') ?? '#';
                                                                                if (url !== '#') return;
                                                                                e.preventDefault();
                                                                                const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                                let container = document.getElementById('toast-container');
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = 'toast-container';
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bs) {
                                                                                    const toast = document.createElement('div');
                                                                                    toast.className = 'toast';
                                                                                    toast.setAttribute('role','alert');
                                                                                    toast.setAttribute('aria-live','assertive');
                                                                                    toast.setAttribute('aria-atomic','true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toast.appendChild(body);
                                                                                    container.appendChild(toast);
                                                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                btn.setAttribute('data-failed-route','true');
                                                                            } catch {}
                                                                        });
                                                                    })();
                                                                </script>
                                                                <script defer>
                                                                    (() => {
                                                                        const btn = document.getElementById('deal-edit-btn-{{ $deal->id }}');
                                                                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                        btn.setAttribute('data-listener-active','true');
                                                                        btn.addEventListener('click', e => {
                                                                            try {
                                                                                const url = btn.getAttribute('data-url') ?? '#';
                                                                                if (url !== '#') return;
                                                                                e.preventDefault();
                                                                                const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                                let container = document.getElementById('toast-container');
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = 'toast-container';
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bs) {
                                                                                    const toast = document.createElement('div');
                                                                                    toast.className = 'toast';
                                                                                    toast.setAttribute('role','alert');
                                                                                    toast.setAttribute('aria-live','assertive');
                                                                                    toast.setAttribute('aria-atomic','true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toast.appendChild(body);
                                                                                    container.appendChild(toast);
                                                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                btn.setAttribute('data-failed-route','true');
                                                                            } catch {}
                                                                        });
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        @endcan
                                                        @can('delete deal')
                                                            @php
                                                                $routeKey           = ViewsConstants::DL . '.destroy';
                                                                $kebabRouteKey      = Str::kebab($routeKey);
                                                                $hasRoute           = Route::has($routeKey);
                                                                $hasKebab           = Route::has($kebabRouteKey);
                                                                $destroyRouteName   = $hasRoute
                                                                    ? $routeKey
                                                                    : ($hasKebab ? $kebabRouteKey : null);
                                                                $destroyRouteArray  = $destroyRouteName
                                                                    ? [$destroyRouteName, $deal->id]
                                                                    : ['#'];
                                                                $destroyRouteUrl    = $destroyRouteName
                                                                    ? route($destroyRouteName, $deal->id)
                                                                    : '#';
                                                                $destroyGuardMsg    = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::DL,
                                                                    'deal_destroy_route_unavailable'
                                                                ) ?? 'Delete deal route is unavailable. Please contact technical support or your domain administrator.';
                                                            @endphp
                                                            {!! Form::open([
                                                                'route'  => $destroyRouteArray,
                                                                'method' => 'DELETE',
                                                                'id'     => 'delete-form-' . $deal->id
                                                            ]) !!}
                                                                <a
                                                                    id="delete-deal-btn-{{ $deal->id }}"
                                                                    href="{{ $destroyRouteUrl }}"
                                                                    data-url="{{ $destroyRouteUrl }}"
                                                                    data-guard-msg="{{ $destroyGuardMsg }}"
                                                                    class="dropdown-item bs-pass-para"
                                                                    data-bs-toggle="tooltip"
                                                                    title="{{ __('Delete') }}"
                                                                >
                                                                    <i class="ti ti-archive"></i>
                                                                    <span>{{ __('Delete') }}</span>
                                                                </a>
                                                            {!! Form::close() !!}
                                                            @push(StacksConstants::ADM_SCR_PG)
                                                                <script defer>
                                                                    (() => {
                                                                        const btn = document.getElementById('delete-deal-btn-{{ $deal->id }}');
                                                                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                        btn.setAttribute('data-listener-active', 'true');
                                                                        btn.addEventListener('click', e => {
                                                                            try {
                                                                                const url = btn.getAttribute('data-url') || '#';
                                                                                if (url !== '#') return;
                                                                                e.preventDefault();
                                                                                const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                                                const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                                let container = document.getElementById('toast-container');
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = 'toast-container';
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bs) {
                                                                                    const toast = document.createElement('div');
                                                                                    toast.className = 'toast';
                                                                                    toast.setAttribute('role','alert');
                                                                                    toast.setAttribute('aria-live','assertive');
                                                                                    toast.setAttribute('aria-atomic','true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toast.appendChild(body);
                                                                                    container.appendChild(toast);
                                                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                btn.setAttribute('data-failed-route', 'true');
                                                                            } catch (error) {}
                                                                        });
                                                                    })();
                                                                </script>
                                                            @endpush
                                                        @endcan
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    @php($products = $deal->products())
                                    @php($sources  = $deal->sources())
                                    <div class="{{ VC::CD }}-body">
                                        <div class="{{ VC::DFL_AIC_JCB }} {{ VC::MB2 }}">
                                            <ul class="list-inline {{ VC::MB0 }}">
                                                <li class="list-inline-item {{ VC::DFL_AIC }}" data-bs-toggle="tooltip" title="{{ __('Tasks') }}">
                                                    <i class="f-16 text-primary ti ti-list"></i> {{ count($deal->tasks) }}/{{ count($deal->completeTasks) }}
                                                </li>
                                            </ul>
                                            <div class="user-group">
                                                <i class="text-primary ti ti-report-money"></i> {{ $user?->priceFormat($deal->price) }}
                                            </div>
                                        </div>
                                        <div class="{{ VC::DFL_AIC_JCB }}">
                                            <ul class="list-inline {{ VC::MB0 }}">
                                                <li class="list-inline-item {{ VC::DFL_AIC }}" data-bs-toggle="tooltip" title="{{ __('Product') }}">
                                                    <i class="f-16 text-primary ti ti-shopping-cart"></i> {{ count($products) }}
                                                </li>
                                                <li class="list-inline-item {{ VC::DFL_AIC }}" data-bs-toggle="tooltip" title="{{ __('Source') }}">
                                                    <i class="f-16 text-primary ti ti-social"></i> {{ count($sources) }}
                                                </li>
                                            </ul>
                                            <div class="user-group">
                                                @foreach($deal->users as $user)
                                                    <img src="@if($user->avatar) {{ asset('storage/uploads/avatar/'.$user->avatar) }} @else {{ asset('storage/uploads/avatar/avatar.png') }} @endif"
                                                         data-bs-toggle="tooltip" title="{{ $user->name }}">
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
