@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        UsersConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth,Route};
    use Illuminate\Support\Collection;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Deals')}} @if($pipeline && $pipeline->name) - {{$pipeline->name}} @else {{ __('No name for pipeline available') }} @endif
@endsection
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}" id="main-style-link">
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('css/summernote/summernote-bs4.js') }}"></script>
    <script defer src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/deals/lang/index.js') }}"></script>
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
            Utility::isFilled($pipelines) ? $pipelines : [__('No pipelines available' ?? [])],
            Utility::isFilled($pipeline ?? []) ? $pipeline->id : '# Unidentified pipeline',
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
    <script defer src="{{ asset('assets/js/routes/deals/pipelines/change.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/deals/list.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/deals/create.js') }}"></script>
@endpush
@section(YieldingConstants::ADM_CTT)
    @php
        $totals = is_array($cnt_deal ?? null) ? $cnt_deal : [];
        $isPriceFormatAvailable = method_exists($user, 'priceFormat');
        $stages = ($pipeline->stages ?? collect());
        $containers = [];
        foreach ($stages as $s) { $containers[] = 'task-list-'.$s->id; }
    @endphp

    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS3 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD }}-body">
                    <div class="{{ VC::RW }} {{ VC::JCB }} {{ VC::ALC }}">
                        <div class="{{ VC::C_AT }} {{ VC::MB3 }} {{ VC::MB_SM0 }}">
                            <small class="{{ VC::TXT_MT }}">{{ __('Total Deals') }}</small>
                            <h4 class="{{ VC::MB0 }}">{{ $totals['total'] ?? 0 }}</h4>
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
                            <h4 class="{{ VC::MB0 }}">{{ $totals['this_month'] ?? 0 }}</h4>
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
                            <h4 class="{{ VC::MB0 }}">{{ $totals['this_week'] ?? 0 }}</h4>
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
                            <h4 class="{{ VC::MB0 }}">{{ $totals['last_30days'] ?? 0 }}</h4>
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
        <div class="row kanban-wrapper horizontal-scroll-cards"
             data-containers='@json($containers)'
             data-plugin="dragula">
            @if (Utility::isFilled($stages) ?? [])
                @php
                    $isPriceFormatAvailable = method_exists($user ?? null, 'priceFormat');
                @endphp
                @foreach($stages as $stage)
                    @php
                        $stageId   = isset($stage->id) ? $stage->id : uniqid('stage_');
                        $stageName = !empty($stage->name) ? $stage->name : __('Untitled Stage');
                        $dealsRaw = method_exists($stage, 'deals') ? ($stage->deals() ?? []) : [];
                        $deals    = Utility::isFilled($dealsRaw ?? [])
                                    ? $dealsRaw
                                    : [];
                    @endphp
                    <div class="{{ VC::C_AT }}">
                        <div class="{{ VC::CD }}">
                            <div class="{{ VC::CD }}-header">
                                <div class="{{ VC::FEND }}">
                                    <span class="{{ VC::BT_SM_PM }} btn-icon count">{{ is_countable($deals) ? count($deals) : 0 }}</span>
                                </div>
                                <h4 class="{{ VC::MB0 }}">{{ $stageName }}</h4>
                            </div>
                            <div class="{{ VC::CD }}-body kanban-box" id="task-list-{{ $stageId }}" data-id="{{ $stageId }}">
                                @if(!empty($deals))
                                    @foreach($deals as $deal)
                                        @php
                                            $dealId      = $deal->id ?? uniqid('deal_');
                                            $dealName    = !empty($deal->name) ? $deal->name : __('No deal name available');
                                            $priceRaw    = isset($deal->price) && is_numeric($deal->price) ? (float)$deal->price : null;
                                            $labelsRaw   = method_exists($deal, 'labels')   ? ($deal->labels()   ?? []) : ($deal->labels   ?? []);
                                            $labels   = Utility::isFilled($labelsRaw ?? [])  ? $labelsRaw   : [];
                                            $productsRaw = method_exists($deal, 'products') ? ($deal->products() ?? []) : ($deal->products ?? []);
                                            $products = Utility::isFilled($productsRaw ?? []) ? $productsRaw : [];
                                            $sourcesRaw  = method_exists($deal, 'sources')  ? ($deal->sources()  ?? []) : ($deal->sources  ?? []);
                                            $sources  = Utility::isFilled($sourcesRaw ?? [])  ? $sourcesRaw  : [];
                                            $dealUsers   = is_array($deal->users ?? null) || ($deal->users ?? null) instanceof \Countable
                                                            ? ($deal->users ?? [])
                                                            : [];
                                            $tasks        = $deal->tasks         ?? [];
                                            $complete     = $deal->completeTasks ?? [];
                                            $tasksCount   = is_countable($tasks)   ? count($tasks)   : 0;
                                            $completeCount= is_countable($complete)? count($complete): 0;
                                            $namespace   = ViewsConstants::DL;
                                            $showRoute   = (!empty($deal->is_active) && !empty($dealId)) ? route("{$namespace}.show", $dealId) : '#';
                                            $showGuardMsg    = Utility::fetchLinkMessage($lang, $namespace, 'deal_show_route_unavailable')    ?? 'Deal show route is unavailable. Please contact technical support or your domain administrator.';
                                            $labelsRoute = !empty($dealId) ? route("{$namespace}.labels", $dealId) : '#';
                                            $labelsGuard     = Utility::fetchLinkMessage($lang, $namespace, 'deals_labels_route_unavailable') ?? 'Deal labels route is unavailable. Please contact technical support or your domain administrator.';
                                        @endphp
                                        <div class="{{ VC::CD }}" data-id="{{ $dealId }}">
                                            <div class="{{ VC::PT3 }} {{ VC::PS3 }}">
                                                @if(!empty($labels))
                                                    @foreach($labels as $label)
                                                        @php
                                                            $lblColor = $label->color ?? 'secondary';
                                                            $lblName  = $label->name  ?? __('Label');
                                                        @endphp
                                                        <div class="badge-xs badge bg-{{ $lblColor }} {{ VC::P4 }} {{ VC::PX3 }} {{ VC::PY2 }}">{{ $lblName }}</div>
                                                    @endforeach
                                                @else
                                                    <div class="badge-xs badge bg-secondary {{ VC::P4 }} {{ VC::PX3 }} {{ VC::PY2 }}">{{ __('No Labels') }}</div>
                                                @endif
                                            </div>
                                            <div class="{{ VC::CD }}-header border-0 pb-0 position-relative">
                                                <h5>
                                                    <a
                                                        id="deal-show-btn-{{ $dealId }}"
                                                        href="{{ $showRoute }}"
                                                        data-url="{{ $showRoute }}"
                                                        data-guard-msg="{{ $showGuardMsg }}"
                                                        class="{{ VC::BT_OUTPM }}"
                                                    >
                                                        {{ $dealName }}
                                                    </a>
                                                    @push(StacksConstants::ADM_SCR_PG)
                                                        <script defer>
                                                            (() => {
                                                                const btn = document.getElementById('deal-show-btn-{{ $dealId }}');
                                                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                btn.setAttribute('data-listener-active', 'true');
                                                                btn.addEventListener('click', e => {
                                                                    try {
                                                                        const url = (btn.getAttribute('data-url') || '#').trim();
                                                                        if (url !== '#') return;
                                                                        e.preventDefault();
                                                                        const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                                        const bs  = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
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
                                                                        } else { alert(msg); }
                                                                        btn.setAttribute('data-failed-route', 'true');
                                                                    } catch {}
                                                                });
                                                            })();
                                                        </script>
                                                    @endpush
                                                </h5>
                                                <div class="{{ VC::CD }}-header-right">
                                                    @if(($user?->{UsersConstants::COL_TP} ?? null) !== PermissionsConstants::CL)
                                                        <div class="btn-group card-option">
                                                            <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown">
                                                                <i class="{{ VC::TD_DOTV }}"></i>
                                                            </button>
                                                            <div class="{{ VC::DRP_MN_EM }}">
                                                                @can('edit deal')
                                                                    @php
                                                                        $editRoute   = !empty($dealId) ? route("{$namespace}.edit",   $dealId) : '#';
                                                                        $editGuard       = Utility::fetchLinkMessage($lang, $namespace, 'deals_edit_route_unavailable')   ?? 'Deal edit route is unavailable. Please contact technical support or your domain administrator.';
                                                                    @endphp
                                                                    <a
                                                                        id="deal-labels-btn-{{ $dealId }}"
                                                                        href="{{ $labelsRoute }}"
                                                                        data-url="{{ $labelsRoute }}"
                                                                        data-guard-msg="{{ $labelsGuard }}"
                                                                        data-size="md"
                                                                        data-ajax-popup="true"
                                                                        class="dropdown-item"
                                                                    >
                                                                        <i class="ti ti-bookmark"></i> <span>{{ __('Labels') }}</span>
                                                                    </a>
                                                                    <a
                                                                        id="deal-edit-btn-{{ $dealId }}"
                                                                        href="{{ $editRoute }}"
                                                                        data-url="{{ $editRoute }}"
                                                                        data-guard-msg="{{ $editGuard }}"
                                                                        data-size="lg"
                                                                        data-ajax-popup="true"
                                                                        class="dropdown-item"
                                                                    >
                                                                        <i class="{{ VC::TI_PC }}"></i> <span>{{ __('Edit') }}</span>
                                                                    </a>
                                                                    @push(StacksConstants::ADM_SCR_PG)
                                                                        <script defer>
                                                                            (() => {
                                                                                const btn = document.getElementById('deal-labels-btn-{{ $dealId }}');
                                                                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                                btn.setAttribute('data-listener-active','true');
                                                                                btn.addEventListener('click', e => {
                                                                                    try {
                                                                                        const url = (btn.getAttribute('data-url') || '#').trim();
                                                                                        if (url !== '#') return;
                                                                                        e.preventDefault();
                                                                                        const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                                                        const bs  = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
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
                                                                                        } else { alert(msg); }
                                                                                        btn.setAttribute('data-failed-route','true');
                                                                                    } catch {}
                                                                                });
                                                                            })();
                                                                        </script>
                                                                        <script defer>
                                                                            (() => {
                                                                                const btn = document.getElementById('deal-edit-btn-{{ $dealId }}');
                                                                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                                btn.setAttribute('data-listener-active','true');
                                                                                btn.addEventListener('click', e => {
                                                                                    try {
                                                                                        const url = (btn.getAttribute('data-url') || '#').trim();
                                                                                        if (url !== '#') return;
                                                                                        e.preventDefault();
                                                                                        const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                                                        const bs  = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
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
                                                                                        } else { alert(msg); }
                                                                                        btn.setAttribute('data-failed-route','true');
                                                                                    } catch {}
                                                                                });
                                                                            })();
                                                                        </script>
                                                                    @endpush
                                                                @endcan
                                                                @can('delete deal')
                                                                    @php
                                                                        $destroyRouteName = ViewsConstants::DL . '.destroy';
                                                                        $destroyUrl  = !empty($dealId) ? route($destroyRouteName, $dealId) : '#';
                                                                        $destroyGuard    = Utility::fetchLinkMessage($lang, $namespace, 'deal_destroy_route_unavailable') ?? 'Delete deal route is unavailable. Please contact technical support or your domain administrator.';
                                                                    @endphp
                                                                    {!! Form::open([
                                                                        'route'  => [$destroyRouteName, $dealId],
                                                                        'method' => 'DELETE',
                                                                        'id'     => 'delete-form-' . $dealId
                                                                    ]) !!}
                                                                        <a
                                                                            id="delete-deal-btn-{{ $dealId }}"
                                                                            href="{{ $destroyUrl }}"
                                                                            data-url="{{ $destroyUrl }}"
                                                                            data-guard-msg="{{ $destroyGuard }}"
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
                                                                                const btn = document.getElementById('delete-deal-btn-{{ $dealId }}');
                                                                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                                btn.setAttribute('data-listener-active', 'true');
                                                                                btn.addEventListener('click', e => {
                                                                                    try {
                                                                                        const url = (btn.getAttribute('data-url') || '#').trim();
                                                                                        if (url !== '#') return;
                                                                                        e.preventDefault();
                                                                                        const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                                                        const bs  = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
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
                                                                                        } else { alert(msg); }
                                                                                        btn.setAttribute('data-failed-route', 'true');
                                                                                    } catch {}
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

                                            <div class="{{ VC::CD }}-body">
                                                <div class="{{ VC::DFL_AIC_JCB }} {{ VC::MB2 }}">
                                                    <ul class="list-inline {{ VC::MB0 }}">
                                                        <li class="list-inline-item {{ VC::DFL_AIC }}" data-bs-toggle="tooltip" title="{{ __('Tasks') }}">
                                                            <i class="f-16 text-primary ti ti-list"></i>
                                                            {{ $tasksCount }}/{{ $completeCount }}
                                                        </li>
                                                    </ul>
                                                    <div class="user-group">
                                                        <i class="text-primary ti ti-report-money"></i>
                                                        {{ $priceRaw !== null ? ($isPriceFormatAvailable ? ($user?->priceFormat($priceRaw)) : $priceRaw) : '-' }}
                                                    </div>
                                                </div>

                                                <div class="{{ VC::DFL_AIC_JCB }}">
                                                    <ul class="list-inline {{ VC::MB0 }}">
                                                        <li class="list-inline-item {{ VC::DFL_AIC }}" data-bs-toggle="tooltip" title="{{ __('Product') }}">
                                                            <i class="f-16 text-primary ti ti-shopping-cart"></i> {{ is_countable($products) ? count($products) : 0 }}
                                                        </li>
                                                        <li class="list-inline-item {{ VC::DFL_AIC }}" data-bs-toggle="tooltip" title="{{ __('Source') }}">
                                                            <i class="f-16 text-primary ti ti-social"></i> {{ is_countable($sources) ? count($sources) : 0 }}
                                                        </li>
                                                    </ul>
                                                    <div class="user-group">
                                                        @if(!empty($dealUsers))
                                                            @foreach($dealUsers as $assignee)
                                                                @php
                                                                    $avatar = !empty($assignee->avatar)
                                                                        ? asset('storage/uploads/avatar/'.$assignee->avatar)
                                                                        : asset('storage/uploads/avatar/avatar.png');
                                                                    $assigneeName = $assignee->name ?? '';
                                                                @endphp
                                                                <img src="{{ $avatar }}" data-bs-toggle="tooltip" title="{{ $assigneeName }}">
                                                            @endforeach
                                                        @else
                                                            <div>{{ __('No deal users available') }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="{{ VC::P4 }} {{ VC::TXCT }} {{ VC::TXT_MT }}">{{ __('No deals in this stage') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="{{ VC::P4 }} {{ VC::TXCT }} {{ VC::TXT_MT }}">{{ __('No stages found') }}</div>
            @endif
        </div>
    </div>
@endsection
