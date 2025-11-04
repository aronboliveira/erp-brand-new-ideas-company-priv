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
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    /** @var \App\Models\User|null $user */
    $user = Auth::user();

    $lang = Utility::fetchUserLang(user: $user);

    $ns = VW::DL;

    $kanbanName  = "{$ns}.index";
    $createName  = "{$ns}.create";
    $showName    = "{$ns}.show";
    $editName    = "{$ns}.edit";
    $destroyName = "{$ns}.destroy";

    $hasKanban  = Route::has($kanbanName);
    $hasCreate  = Route::has($createName);
    $hasShow    = Route::has($showName);
    $hasEdit    = Route::has($editName);
    $hasDelete  = Route::has($destroyName);

    $kanbanGuard  = Utility::fetchLinkMessage($lang, $ns, 'deals_index_route_unavailable')
        ?? 'Deals Kanban route is unavailable. Please contact technical support or your domain administrator.';
    $createGuard  = Utility::fetchLinkMessage($lang, $ns, 'deals_create_route_unavailable')
        ?? 'Deal create route is unavailable. Please contact technical support or your domain administrator.';
    $showGuard    = Utility::fetchLinkMessage($lang, $ns, 'deal_show_route_unavailable')
        ?? 'Deal show route is unavailable. Please contact technical support or your domain administrator.';
    $editGuard    = Utility::fetchLinkMessage($lang, $ns, 'deals_edit_route_unavailable')
        ?? 'Deal edit route is unavailable. Please contact technical support or your domain administrator.';
    $deleteGuard  = Utility::fetchLinkMessage($lang, $ns, 'deal_destroy_route_unavailable')
        ?? 'Delete deal route is unavailable. Please contact technical support or your domain administrator.';

    $cntRaw = $cntDeal ?? null;
    if (Utility::isFilled($cntRaw))
        $totals = $cntRaw;
    else {
        $currencySymbol = $settings[SC::CR_SB] ?? '';
        $position       = $settings[SC::CR_SB_P] ?? '';
        $amount         = '—';
        $display = match ($position) {
            'pre' => $currencySymbol . $amount,
            'pos' => $amount . ($currencySymbol ? ' ' . $currencySymbol : ''),
            default => $amount,
        };
        $totals = [
            'total'       => $display,
            'this_month'  => $display,
            'this_week'   => $display,
            'last_30days' => $display,
        ];
    }
    $dealsList = (isset($deals) && (is_array($deals) || $deals instanceof \Illuminate\Support\Collection))
        ? $deals
        : [];
    $isPriceFormatAvailable = method_exists($user, 'priceFormat');
?>



<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
    <?php echo e(__('Manage Deals')); ?> <?php if(!empty($pipeline?->name)): ?> - <?php echo e($pipeline->name); ?> <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush(StacksConstants::ADM_CSS); ?>
    <link rel="stylesheet" href="<?php echo e(asset('css/summernote/summernote-bs4.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
    <script src="<?php echo e(asset('css/summernote/summernote-bs4.js')); ?>"></script>
    <script defer>
        (() => {
            const ERR_FB = '# ERROR';
            const FL_CLIENT = 'data-client-localized';
            const FL_GUARD  = 'data-guard-msg';
            const LANG_KEY  = 'erp-np-lang';
            let errorMessage = '';

            const getMsg = (key, el) => {
                let msg = ERR_FB;
                try {
                    if (el.getAttribute(FL_CLIENT) === 'true') {
                        msg = el.getAttribute(FL_GUARD) || msg;
                    } else {
                        let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
                          .toLowerCase().replace(/_/g,'-');
                        lang = lang === 'pt-br' ? lang : lang.slice(0,2);
                        msg = (window.translations?.[lang]?.[key])
                          ?? el.getAttribute(FL_GUARD)
                          ?? window.translations?.['en']?.[key]
                          ?? msg;
                        if (msg !== ERR_FB) {
                            el.setAttribute(FL_GUARD, msg);
                            el.setAttribute(FL_CLIENT, 'true');
                        }
                    }
                } catch {}
                return msg;
            };

            const ensureToastContainer = () => {
                let c = document.getElementById('toast-container');
                if (!c) {
                    c = document.createElement('div');
                    c.id = 'toast-container';
                    document.body.appendChild(c);
                }
                return c;
            };

            const showError = message => {
                try {
                    const c = ensureToastContainer();
                    const hasBootstrap = !!document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
                    if (hasBootstrap) {
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

            const guardedClick = (btn) => {
                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                btn.setAttribute('data-listener-active', 'true');
                btn.addEventListener('click', e => {
                    try {
                        const url = (btn.getAttribute('data-url') || '#').trim();
                        if (url !== '#') return;
                        e.preventDefault();
                        const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                        const hasBootstrap = !!document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                        ensureToastContainer();
                        if (hasBootstrap) {
                            const toast = document.createElement('div');
                            toast.className = 'toast';
                            toast.setAttribute('role','alert');
                            toast.setAttribute('aria-live','assertive');
                            toast.setAttribute('aria-atomic','true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;
                            toast.appendChild(body);
                            document.getElementById('toast-container').appendChild(toast);
                            bootstrap.Toast.getOrCreateInstance(toast).show();
                        } else {
                            alert(msg);
                        }
                        btn.setAttribute('data-failed-route', 'true');
                    } catch {}
                });
            };

            document.addEventListener('DOMContentLoaded', () => {
                try {
                    document.querySelectorAll('[data-url][data-guard-msg]').forEach(guardedClick);
                } catch {}
            });

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
        })();
    </script>
    <script defer src="<?php echo e(asset('assets/js/routes/deals/list.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection(YieldingConstants::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e(Route::has('dashboard') ? route('dashboard') : '#'); ?>"
           <?php echo e(Route::has('dashboard') ? '' : 'aria-disabled=true'); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item"><?php echo e(__('Deal')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_ACT_BTN); ?>
    <div class="float-end">
        <?php
            $kanbanUrl = $hasKanban ? route($kanbanName) : '#';
            $createUrl = $hasCreate ? route($createName) : '#';
        ?>
        <a
            id="deals-kanban-btn"
            href="<?php echo e($kanbanUrl); ?>"
            data-url="<?php echo e($kanbanUrl); ?>"
            data-guard-msg="<?php echo e($kanbanGuard); ?>"
            data-bs-toggle="tooltip"
            title="<?php echo e(__('Kanban View')); ?>"
            class="<?php echo e(VC::BT_SM_PM); ?>"
        >
            <i class="ti ti-layout-grid"></i>
        </a>

        <a
            id="deals-create-btn"
            href="<?php echo e($createUrl); ?>"
            data-url="<?php echo e($createUrl); ?>"
            data-guard-msg="<?php echo e($createGuard); ?>"
            data-size="lg"
            data-ajax-popup="true"
            data-bs-toggle="tooltip"
            title="<?php echo e(__('Create New Deal')); ?>"
            class="<?php echo e(VC::BT_SM_PM); ?>"
        >
            <i class="<?php echo e(VC::TI_PLS); ?>"></i>
        </a>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
    <?php if(Utility::isFilled($pipeline)): ?>
        <div class="<?php echo e(VC::RW); ?>">
            <div class="<?php echo e(VC::CS3); ?>">
                <div class="<?php echo e(VC::CD); ?>">
                    <div class="<?php echo e(VC::CD); ?>-body">
                        <div class="<?php echo e(VC::RW); ?> <?php echo e(VC::JCB); ?> <?php echo e(VC::ALC); ?>">
                            <div class="<?php echo e(VC::C_AT); ?> <?php echo e(VC::MB3); ?> <?php echo e(VC::MB0); ?>">
                                <small class="<?php echo e(VC::TXT_MT); ?>"><?php echo e(__('Total Deals')); ?></small>
                                <h4 class="<?php echo e(VC::MB0); ?>"><?php echo e($totals['total'] ?? __('No total available')); ?></h4>
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
                                <h4 class="<?php echo e(VC::MB0); ?>"><?php echo e($totals['this_month'] ?? __('No totals for this month')); ?></h4>
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
                                <h4 class="<?php echo e(VC::MB0); ?>"><?php echo e($totals['this_week'] ?? __('No totals for week')); ?></h4>
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
                                <h4 class="<?php echo e(VC::MB0); ?>"><?php echo e($totals['last_30days'] ?? __('No totals for last 30 days')); ?></h4>
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
            <div class="col-xl-12">
                <div class="<?php echo e(VC::CD); ?>">
                    <div class="<?php echo e(VC::CD); ?>-body table-border-style">
                        <div class="table-responsive">
                            <table class="table datatable">
                                <thead>
                                <tr>
                                    <th><?php echo e(__('Name')); ?></th>
                                    <th><?php echo e(__('Price')); ?></th>
                                    <th><?php echo e(__('Stage')); ?></th>
                                    <th><?php echo e(__('Tasks')); ?></th>
                                    <th><?php echo e(__('Users')); ?></th>
                                    <th width="300"><?php echo e(__('Action')); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if( (is_countable($dealsList) ? count($dealsList) : 0) > 0 ): ?>
                                    <?php $__currentLoopData = $dealsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $dealId      = $deal->id ?? null;
                                            $dealName    = $deal->name ?? __('No deal name available');
                                            $priceRaw    = isset($deal->price) && is_numeric($deal->price) ? (float)$deal->price : null;
                                            $stageName   = $deal->stage->name ?? __('—');
                                            $tasksCount  = is_countable($deal->tasks ?? []) ? count($deal->tasks) : 0;
                                            $doneCount   = is_countable($deal->complete_tasks ?? []) ? count($deal->complete_tasks) : 0;
                                            $viewUrl     = ($hasShow && !empty($deal->is_active) && $dealId) ? route($showName, $dealId) : '#';
                                            $editUrl     = ($hasEdit && $dealId) ? route($editName, $dealId) : '#';
                                            $deleteUrl   = ($hasDelete && $dealId) ? route($destroyName, $dealId) : '#';
                                        ?>
                                        <tr>
                                            <td><?php echo e($dealName); ?></td>
                                            <td>
                                                <?php echo e($priceRaw !== null ? ($isPriceFormatAvailable ? ($user?->priceFormat($priceRaw)) : $priceRaw) : '—'); ?>

                                            </td>
                                            <td><?php echo e($stageName); ?></td>
                                            <td><?php echo e($tasksCount); ?>/<?php echo e($doneCount); ?></td>
                                            <td>
                                                <?php $dealUsers = $deal->users ?? []; ?>
                                                <?php if(Utility::isFilled($dealUsers)): ?>
                                                    <?php $__currentLoopData = $dealUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $avatar = !empty($assignee->avatar)
                                                                ? asset('storage/uploads/avatar/'.$assignee->avatar)
                                                                : asset('storage/uploads/avatar/avatar.png');
                                                            $assigneeName = $assignee->name ?? '';
                                                        ?>
                                                        <a href="#" class="btn btn-sm p-0 rounded-circle" tabindex="-1" aria-label="<?php echo e($assigneeName); ?>">
                                                            <img alt="avatar" data-bs-toggle="tooltip" title="<?php echo e($assigneeName); ?>"
                                                                 src="<?php echo e($avatar); ?>" class="rounded-circle" width="25" height="25">
                                                        </a>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php else: ?>
                                                    <span class="<?php echo e(VC::TXT_MT); ?>"><?php echo e(__('No deal users available')); ?></span>
                                                <?php endif; ?>
                                            </td>

                                            <?php if(($user?->{UsersConstants::COL_TP} ?? null) !== PermissionsConstants::CL): ?>
                                                <td class="Action">
                                                    <span class="<?php echo e(VC::DFL_AIC); ?>">
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view deal')): ?>
                                                            <?php if(!empty($deal->is_active)): ?>
                                                                <div class="action-btn bg-warning ms-2">
                                                                    <a
                                                                        id="deal-view-btn-<?php echo e($dealId); ?>"
                                                                        href="<?php echo e($viewUrl); ?>"
                                                                        data-url="<?php echo e($viewUrl); ?>"
                                                                        data-guard-msg="<?php echo e($showGuard); ?>"
                                                                        class="<?php echo e(VC::BT_SM_FL_CT); ?>"
                                                                        data-size="xl"
                                                                        data-bs-toggle="tooltip"
                                                                        title="<?php echo e(__('View')); ?>"
                                                                        data-title="<?php echo e(__('Lead Detail')); ?>"
                                                                    >
                                                                        <i class="<?php echo e(VC::TI_EYE_WT); ?>"></i>
                                                                    </a>
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php endif; ?>

                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit deal')): ?>
                                                            <div class="action-btn bg-info ms-2">
                                                                <a
                                                                    id="deal-edit-btn-<?php echo e($dealId); ?>"
                                                                    href="<?php echo e($editUrl); ?>"
                                                                    data-url="<?php echo e($editUrl); ?>"
                                                                    data-guard-msg="<?php echo e($editGuard); ?>"
                                                                    class="<?php echo e(VC::BT_SM_FL_CT); ?>"
                                                                    data-ajax-popup="true"
                                                                    data-size="xl"
                                                                    data-bs-toggle="tooltip"
                                                                    title="<?php echo e(__('Edit')); ?>"
                                                                    data-title="<?php echo e(__('Lead Edit')); ?>"
                                                                >
                                                                    <i class="<?php echo e(VC::TI_PC_WT); ?>"></i>
                                                                </a>
                                                            </div>
                                                        <?php endif; ?>

                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete deal')): ?>
                                                            <div class="action-btn bg-danger ms-2">
                                                                <?php echo Form::open([
                                                                    'route'  => [$destroyName, $dealId],
                                                                    'method' => 'DELETE',
                                                                    'id'     => 'delete-form-' . $dealId
                                                                ]); ?>

                                                                    <a
                                                                        id="deal-delete-btn-<?php echo e($dealId); ?>"
                                                                        href="<?php echo e($deleteUrl); ?>"
                                                                        data-url="<?php echo e($deleteUrl); ?>"
                                                                        data-guard-msg="<?php echo e($deleteGuard); ?>"
                                                                        class="<?php echo e(VC::BT_SM_FL_CT); ?> bs-pass-para"
                                                                        data-bs-toggle="tooltip"
                                                                        title="<?php echo e(__('Delete')); ?>"
                                                                    >
                                                                        <i class="<?php echo e(VC::TI_TRS_WT); ?>"></i>
                                                                    </a>
                                                                <?php echo Form::close(); ?>

                                                            </div>
                                                        <?php endif; ?>
                                                    </span>
                                                </td>
                                            <?php endif; ?>
                                        </tr>

                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                            <script defer>
                                                (() => {
                                                    ['deal-view-btn-<?php echo e($dealId); ?>','deal-edit-btn-<?php echo e($dealId); ?>','deal-delete-btn-<?php echo e($dealId); ?>']
                                                        .map(id => document.getElementById(id))
                                                        .filter(Boolean)
                                                        .forEach(btn => {
                                                            if (btn.getAttribute('data-listener-active') === 'true') return;
                                                            btn.setAttribute('data-listener-active','true');
                                                            btn.addEventListener('click', e => {
                                                                try {
                                                                    const url = (btn.getAttribute('data-url') || '#').trim();
                                                                    if (url !== '#') return;
                                                                    e.preventDefault();
                                                                    const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                                    const hasBootstrap = !!document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                    let c = document.getElementById('toast-container');
                                                                    if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                                    if (hasBootstrap) {
                                                                        const toast = document.createElement('div');
                                                                        toast.className = 'toast';
                                                                        toast.setAttribute('role','alert');
                                                                        toast.setAttribute('aria-live','assertive');
                                                                        toast.setAttribute('aria-atomic','true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toast.appendChild(body);
                                                                        c.appendChild(toast);
                                                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                    } else { alert(msg); }
                                                                    btn.setAttribute('data-failed-route','true');
                                                                } catch {}
                                                            });
                                                        });
                                                })();
                                            </script>
                                        <?php $__env->stopPush(); ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="<?php echo e(VC::TXCT); ?> <?php echo e(VC::TXT_MT); ?>"><?php echo e(__('No data available in table')); ?></td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="<?php echo e(VC::P4); ?> <?php echo e(VC::TXCT); ?> <?php echo e(VC::TXT_MT); ?>"><?php echo e(__('No pipeline available')); ?></div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/deals/list.blade.php ENDPATH**/ ?>