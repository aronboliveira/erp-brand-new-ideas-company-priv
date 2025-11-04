<?php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        SettingsConstants as SC,
        StacksConstants,
        UsersConstants,
        ViewClassNamesConstants as VC,
        ViewsConstants as VW,
        YieldingConstants,
    };
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth,Route};
    use Illuminate\Support\Collection;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
?>

<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
    <?php echo e(__('Manage Deals')); ?> <?php if($pipeline && $pipeline->name): ?> - <?php echo e($pipeline->name); ?> <?php else: ?> <?php echo e(__('No name for pipeline available')); ?> <?php endif; ?>
<?php $__env->stopSection(); ?>
<?php $__env->startPush(StacksConstants::ADM_CSS); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/summernote/summernote-bs4.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/dragula.min.css')); ?>" id="main-style-link">
<?php $__env->stopPush(); ?>
<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
    <script src="<?php echo e(asset('css/summernote/summernote-bs4.js')); ?>"></script>
    <script defer src="<?php echo e(asset('assets/js/plugins/dragula.min.js')); ?>"></script>
    <script async src="<?php echo e(asset('assets/js/routes/deals/lang/index.js')); ?>"></script>
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
                        const pipeline_id = '<?php echo e($pipeline->id); ?>';
                        $(source).parent().find('.count').text(source.children.length);
                        $(target).parent().find('.count').text(target.children.length);
                        $.ajax({
                        url: '<?php echo e(route(VW::DL.".order")); ?>',
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
<?php $__env->stopPush(); ?>
<?php $__env->startSection(YieldingConstants::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e(Route::has('dashboard') ? route('dashboard') : '#'); ?>"
        <?php echo e(Route::has('dashboard') ? '' : 'aria-disabled="true"'); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item"><?php echo e(__('Deal')); ?></li>
<?php $__env->stopSection(); ?>
<?php
    $ns = VW::DL;
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
?>
<?php $__env->startSection(YieldingConstants::ADM_ACT_BTN); ?>
    <div class="float-end">
        <?php if($hasChange): ?>
            <?php echo e(Form::open([
                'route'          => $changeName,
                'id'             => 'change-pipeline-form',
                'class'          => VC::BT_SM,
                'data-guard-msg' => $changeGuardMsg
            ])); ?>

        <?php else: ?>
            <?php echo e(Form::open([
                'url'            => '#',
                'id'             => 'change-pipeline-form',
                'class'          => VC::BT_SM,
                'data-guard-msg' => $changeGuardMsg
            ])); ?>

        <?php endif; ?>
        <?php echo e(Form::select(
            'default_pipeline_id',
            Utility::isFilled($pipelines) ? $pipelines : [__('No pipelines available' ?? [])],
            Utility::isFilled($pipeline ?? []) ? $pipeline->id : '# Unidentified pipeline',
            [
                'class' => VC::FM_CT . ' select me-4',
                'id'    => 'default_pipeline_id'
            ]
        )); ?>

        <?php echo e(Form::close()); ?>

        <a
            id="deal-list-btn"
            href="<?php echo e($hasList ? route($listName) : '#'); ?>"
            data-url="<?php echo e($hasList ? route($listName) : '#'); ?>"
            data-guard-msg="<?php echo e($listGuardMsg); ?>"
            data-size="lg"
            data-bs-toggle="tooltip"
            title="<?php echo e(__('List View')); ?>"
            class="<?php echo e(VC::BT_SM_PM); ?>"
        >
            <i class="<?php echo e(VC::TI_LT); ?>"></i>
        </a>

        <a
            id="deal-create-btn"
            href="<?php echo e($hasCreate ? route($createName) : '#'); ?>"
            data-url="<?php echo e($hasCreate ? route($createName) : '#'); ?>"
            data-guard-msg="<?php echo e($createGuardMsg); ?>"
            data-size="lg"
            data-ajax-popup="true"
            data-bs-toggle="tooltip"
            title="<?php echo e(__('Create New Deal')); ?>"
            data-title="<?php echo e(__('Create Deal')); ?>"
            class="<?php echo e(VC::BT_SM_PM); ?>"
        >
            <i class="<?php echo e(VC::TI_PLS); ?>"></i>
        </a>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
    <script defer src="<?php echo e(asset('assets/js/routes/deals/pipelines/change.js')); ?>"></script>
    <script defer src="<?php echo e(asset('assets/js/routes/deals/list.js')); ?>"></script>
    <script defer src="<?php echo e(asset('assets/js/routes/deals/create.js')); ?>"></script>
<?php $__env->stopPush(); ?>
<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
    <?php
        if (is_array($cntDeal ?? null)) {
            if (isset($cntDeal['total']))
                $totals = $cntDeal;
            else
                $totals = ['total' => $cntDeal[0] . ' (' . ($cntDeal[1] ?? '') . ')'];
        } else {
            $currencySymbol = $settings[SC::CR_SB] ?? '';
            $position = $settings[SC::CR_SB_P] ?? '';
            $amount = '99999999999999999999999';
            if ($position === 'pre')
                $total = $currencySymbol . $amount;
            elseif ($position === 'pos')
                $total = $amount . ' ' . $currencySymbol;
            else
                $total = $amount;
            $totals = [...$totals, 'total' => $total];
        }
        $isPriceFormatAvailable = method_exists($user, 'priceFormat');
        $stages = ($pipeline->stages ?? collect());
        $containers = [];
        foreach ($stages as $s) { $containers[] = 'task-list-'.$s->id; }
    ?>
    <div class="<?php echo e(VC::RW); ?>">
        <div class="<?php echo e(VC::CS3); ?>">
            <div class="<?php echo e(VC::CD); ?>">
                <div class="<?php echo e(VC::CD); ?>-body">
                    <div class="<?php echo e(VC::RW); ?> <?php echo e(VC::JCB); ?> <?php echo e(VC::ALC); ?>">
                        <div class="<?php echo e(VC::C_AT); ?> <?php echo e(VC::MB3); ?> <?php echo e(VC::MB0); ?>">
                            <small class="<?php echo e(VC::TXT_MT); ?>"><?php echo e(__('Total Deals')); ?></small>
                            <h4 class="<?php echo e(VC::MB0); ?>"><?php echo e(!empty($totals['total']) ? $totals['total'] : "#NULL"); ?></h4>
                        </div>
                        <div class="<?php echo e(VC::C_AT); ?>">
                            <div class="theme-avatar bg-info">
                                <i class="ti ti-layers-difference"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="<?php echo e(VC::CS3); ?>">
            <div class="<?php echo e(VC::CD); ?>">
                <div class="<?php echo e(VC::CD); ?>-body">
                    <div class="<?php echo e(VC::RW); ?> <?php echo e(VC::JCB); ?> <?php echo e(VC::ALC); ?>">
                        <div class="<?php echo e(VC::C_AT); ?> <?php echo e(VC::MB3); ?> <?php echo e(VC::MB0); ?>">
                            <small class="<?php echo e(VC::TXT_MT); ?>"><?php echo e(__('This Month Total Deals')); ?></small>
                            <h4 class="<?php echo e(VC::MB0); ?>"><?php echo e(!empty($totals['this_month']) ? $totals['this_month'] : "#NULL"); ?></h4>
                        </div>
                        <div class="<?php echo e(VC::C_AT); ?>">
                            <div class="theme-avatar bg-primary">
                                <i class="ti ti-layers-difference"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="<?php echo e(VC::CS3); ?>">
            <div class="<?php echo e(VC::CD); ?>">
                <div class="<?php echo e(VC::CD); ?>-body">
                    <div class="<?php echo e(VC::RW); ?> <?php echo e(VC::JCB); ?> <?php echo e(VC::ALC); ?>">
                        <div class="<?php echo e(VC::C_AT); ?> <?php echo e(VC::MB3); ?> <?php echo e(VC::MB0); ?>">
                            <small class="<?php echo e(VC::TXT_MT); ?>"><?php echo e(__('This Week Total Deals')); ?></small>
                            <h4 class="<?php echo e(VC::MB0); ?>"><?php echo e(!empty($totals['this_week']) ? $totals['this_week'] : "#NULL"); ?></h4>
                        </div>
                        <div class="<?php echo e(VC::C_AT); ?>">
                            <div class="theme-avatar bg-warning">
                                <i class="ti ti-layers-difference"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="<?php echo e(VC::CS3); ?>">
            <div class="<?php echo e(VC::CD); ?>">
                <div class="<?php echo e(VC::CD); ?>-body">
                    <div class="<?php echo e(VC::RW); ?> <?php echo e(VC::JCB); ?> <?php echo e(VC::ALC); ?>">
                        <div class="<?php echo e(VC::C_AT); ?> <?php echo e(VC::MB3); ?> <?php echo e(VC::MB0); ?>">
                            <small class="<?php echo e(VC::TXT_MT); ?>"><?php echo e(__('Last 30 Days Total Deals')); ?></small>
                            <h4 class="<?php echo e(VC::MB0); ?>"><?php echo e(!empty($totals['last_30days']) ? $totals['last_30days'] : "#NULL"); ?></h4>
                        </div>
                        <div class="<?php echo e(VC::C_AT); ?>">
                            <div class="theme-avatar bg-danger">
                                <i class="ti ti-layers-difference"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="<?php echo e(VC::RW); ?>">
        <div class="row kanban-wrapper horizontal-scroll-cards"
             data-containers='<?php echo json_encode($containers, 15, 512) ?>'
             data-plugin="dragula">
            <?php if(Utility::isFilled($stages) ?? []): ?>
                <?php
                    $isPriceFormatAvailable = method_exists($user ?? null, 'priceFormat');
                ?>
                <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $stageId   = isset($stage->id) ? $stage->id : uniqid('stage_');
                        $stageName = !empty($stage->name) ? $stage->name : __('Untitled Stage');
                        $dealsRaw = method_exists($stage, 'deals') ? ($stage->deals() ?? []) : [];
                        $deals    = Utility::isFilled($dealsRaw ?? [])
                                    ? $dealsRaw
                                    : [];
                    ?>
                    <div class="<?php echo e(VC::C_AT); ?>">
                        <div class="<?php echo e(VC::CD); ?>">
                            <div class="<?php echo e(VC::CD); ?>-header">
                                <div class="<?php echo e(VC::FEND); ?>">
                                    <span class="<?php echo e(VC::BT_SM_PM); ?> btn-icon count"><?php echo e(is_countable($deals) ? count($deals) : 0); ?></span>
                                </div>
                                <h4 class="<?php echo e(VC::MB0); ?>"><?php echo e($stageName); ?></h4>
                            </div>
                            <div class="<?php echo e(VC::CD); ?>-body kanban-box" id="task-list-<?php echo e($stageId); ?>" data-id="<?php echo e($stageId); ?>">
                                <?php if(!empty($deals)): ?>
                                    <?php $__currentLoopData = $deals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
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
                                            $namespace   = VW::DL;
                                            $showRoute   = (!empty($deal->is_active) && !empty($dealId)) ? route("{$namespace}.show", $dealId) : '#';
                                            $showGuardMsg    = Utility::fetchLinkMessage($lang, $namespace, 'deal_show_route_unavailable')    ?? 'Deal show route is unavailable. Please contact technical support or your domain administrator.';
                                            $labelsRoute = !empty($dealId) ? route("{$namespace}.labels", $dealId) : '#';
                                            $labelsGuard     = Utility::fetchLinkMessage($lang, $namespace, 'deals_labels_route_unavailable') ?? 'Deal labels route is unavailable. Please contact technical support or your domain administrator.';
                                        ?>
                                        <div class="<?php echo e(VC::CD); ?>" data-id="<?php echo e($dealId); ?>">
                                            <div class="<?php echo e(VC::PT3); ?> <?php echo e(VC::PS3); ?>">
                                                <?php if(!empty($labels)): ?>
                                                    <?php $__currentLoopData = $labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $lblColor = $label->color ?? 'secondary';
                                                            $lblName  = $label->name  ?? __('Label');
                                                        ?>
                                                        <div class="badge-xs badge bg-<?php echo e($lblColor); ?> <?php echo e(VC::P4); ?> <?php echo e(VC::PX3); ?> <?php echo e(VC::PY2); ?>"><?php echo e($lblName); ?></div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php else: ?>
                                                    <div class="badge-xs badge bg-secondary <?php echo e(VC::P4); ?> <?php echo e(VC::PX3); ?> <?php echo e(VC::PY2); ?>"><?php echo e(__('No Labels')); ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="<?php echo e(VC::CD); ?>-header border-0 pb-0 position-relative">
                                                <h5>
                                                    <a
                                                        id="deal-show-btn-<?php echo e($dealId); ?>"
                                                        href="<?php echo e($showRoute); ?>"
                                                        data-url="<?php echo e($showRoute); ?>"
                                                        data-guard-msg="<?php echo e($showGuardMsg); ?>"
                                                        class="<?php echo e(VC::BT_OUTPM); ?>"
                                                    >
                                                        <?php echo e($dealName); ?>

                                                    </a>
                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                        <script defer>
                                                            (() => {
                                                                const btn = document.getElementById('deal-show-btn-<?php echo e($dealId); ?>');
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
                                                    <?php $__env->stopPush(); ?>
                                                </h5>
                                                <div class="<?php echo e(VC::CD); ?>-header-right">
                                                    <?php if(($user?->{UsersConstants::COL_TP} ?? null) !== PermissionsConstants::CL): ?>
                                                        <div class="btn-group card-option">
                                                            <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown">
                                                                <i class="<?php echo e(VC::TD_DOTV); ?>"></i>
                                                            </button>
                                                            <div class="<?php echo e(VC::DRP_MN_EM); ?>">
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit deal')): ?>
                                                                    <?php
                                                                        $editRoute   = !empty($dealId) ? route("{$namespace}.edit",   $dealId) : '#';
                                                                        $editGuard       = Utility::fetchLinkMessage($lang, $namespace, 'deals_edit_route_unavailable')   ?? 'Deal edit route is unavailable. Please contact technical support or your domain administrator.';
                                                                    ?>
                                                                    <a
                                                                        id="deal-labels-btn-<?php echo e($dealId); ?>"
                                                                        href="<?php echo e($labelsRoute); ?>"
                                                                        data-url="<?php echo e($labelsRoute); ?>"
                                                                        data-guard-msg="<?php echo e($labelsGuard); ?>"
                                                                        data-size="md"
                                                                        data-ajax-popup="true"
                                                                        class="dropdown-item"
                                                                    >
                                                                        <i class="ti ti-bookmark"></i> <span><?php echo e(__('Labels')); ?></span>
                                                                    </a>
                                                                    <a
                                                                        id="deal-edit-btn-<?php echo e($dealId); ?>"
                                                                        href="<?php echo e($editRoute); ?>"
                                                                        data-url="<?php echo e($editRoute); ?>"
                                                                        data-guard-msg="<?php echo e($editGuard); ?>"
                                                                        data-size="lg"
                                                                        data-ajax-popup="true"
                                                                        class="dropdown-item"
                                                                    >
                                                                        <i class="<?php echo e(VC::TI_PC); ?>"></i> <span><?php echo e(__('Edit')); ?></span>
                                                                    </a>
                                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                        <script defer>
                                                                            (() => {
                                                                                const btn = document.getElementById('deal-labels-btn-<?php echo e($dealId); ?>');
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
                                                                                const btn = document.getElementById('deal-edit-btn-<?php echo e($dealId); ?>');
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
                                                                    <?php $__env->stopPush(); ?>
                                                                <?php endif; ?>
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete deal')): ?>
                                                                    <?php
                                                                        $destroyRouteName = VW::DL . '.destroy';
                                                                        $destroyUrl  = !empty($dealId) ? route($destroyRouteName, $dealId) : '#';
                                                                        $destroyGuard    = Utility::fetchLinkMessage($lang, $namespace, 'deal_destroy_route_unavailable') ?? 'Delete deal route is unavailable. Please contact technical support or your domain administrator.';
                                                                    ?>
                                                                    <?php echo Form::open([
                                                                        'route'  => [$destroyRouteName, $dealId],
                                                                        'method' => 'DELETE',
                                                                        'id'     => 'delete-form-' . $dealId
                                                                    ]); ?>

                                                                        <a
                                                                            id="delete-deal-btn-<?php echo e($dealId); ?>"
                                                                            href="<?php echo e($destroyUrl); ?>"
                                                                            data-url="<?php echo e($destroyUrl); ?>"
                                                                            data-guard-msg="<?php echo e($destroyGuard); ?>"
                                                                            class="dropdown-item bs-pass-para"
                                                                            data-bs-toggle="tooltip"
                                                                            title="<?php echo e(__('Delete')); ?>"
                                                                        >
                                                                            <i class="ti ti-archive"></i>
                                                                            <span><?php echo e(__('Delete')); ?></span>
                                                                        </a>
                                                                    <?php echo Form::close(); ?>

                                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                        <script defer>
                                                                            (() => {
                                                                                const btn = document.getElementById('delete-deal-btn-<?php echo e($dealId); ?>');
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
                                                                    <?php $__env->stopPush(); ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="<?php echo e(VC::CD); ?>-body">
                                                <div class="<?php echo e(VC::DFL_AIC_JCB); ?> <?php echo e(VC::MB2); ?>">
                                                    <ul class="list-inline <?php echo e(VC::MB0); ?>">
                                                        <li class="list-inline-item <?php echo e(VC::DFL_AIC); ?>" data-bs-toggle="tooltip" title="<?php echo e(__('Tasks')); ?>">
                                                            <i class="f-16 text-primary ti ti-list"></i>
                                                            <?php echo e($tasksCount); ?>/<?php echo e($completeCount); ?>

                                                        </li>
                                                    </ul>
                                                    <div class="user-group">
                                                        <i class="text-primary ti ti-report-money"></i>
                                                        <?php echo e($priceRaw !== null ? ($isPriceFormatAvailable ? ($user?->priceFormat($priceRaw)) : $priceRaw) : '-'); ?>

                                                    </div>
                                                </div>

                                                <div class="<?php echo e(VC::DFL_AIC_JCB); ?>">
                                                    <ul class="list-inline <?php echo e(VC::MB0); ?>">
                                                        <li class="list-inline-item <?php echo e(VC::DFL_AIC); ?>" data-bs-toggle="tooltip" title="<?php echo e(__('Product')); ?>">
                                                            <i class="f-16 text-primary ti ti-shopping-cart"></i> <?php echo e(is_countable($products) ? count($products) : 0); ?>

                                                        </li>
                                                        <li class="list-inline-item <?php echo e(VC::DFL_AIC); ?>" data-bs-toggle="tooltip" title="<?php echo e(__('Source')); ?>">
                                                            <i class="f-16 text-primary ti ti-social"></i> <?php echo e(is_countable($sources) ? count($sources) : 0); ?>

                                                        </li>
                                                    </ul>
                                                    <div class="user-group">
                                                        <?php if(Utility::isFilled($dealUsers)): ?>
                                                            <?php $__currentLoopData = $dealUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <?php
                                                                    $avatar = !empty($assignee->avatar)
                                                                        ? asset('storage/uploads/avatar/'.$assignee->avatar)
                                                                        : asset('storage/uploads/avatar/avatar.png');
                                                                    $assigneeName = $assignee->name ?? '';
                                                                ?>
                                                                <img src="<?php echo e($avatar); ?>" data-bs-toggle="tooltip" title="<?php echo e($assigneeName); ?>">
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        <?php else: ?>
                                                            <div><?php echo e(__('No deal users available')); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <div class="<?php echo e(VC::P4); ?> <?php echo e(VC::TXCT); ?> <?php echo e(VC::TXT_MT); ?>"><?php echo e(__('No deals in this stage')); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <div class="<?php echo e(VC::P4); ?> <?php echo e(VC::TXCT); ?> <?php echo e(VC::TXT_MT); ?>"><?php echo e(__('No stages found')); ?></div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/deals/index.blade.php ENDPATH**/ ?>