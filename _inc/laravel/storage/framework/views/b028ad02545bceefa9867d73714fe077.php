<?php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants as PC,
        StacksConstants,
        UsersConstants as UC,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Crypt, Gate, Route, Storage};
    use Illuminate\Support\{Collection, Str};

    $profile = Utility::getFile('uploads/avatar');
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);

    $dashBase  = 'dashboard';
    $dashKebab = Str::kebab($dashBase);
    $dashName  = Route::has($dashBase) ? $dashBase : (Route::has($dashKebab) ? $dashKebab : null);
    $dashUrl   = $dashName ? route($dashName) : '#';
?>



<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
    <?php echo e(__('Manage User')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e($dashUrl); ?>" <?php echo e($dashUrl === '#' ? 'aria-disabled=true' : ''); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item"><?php echo e(__('User')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_ACT_BTN); ?>
    <div class="<?php echo e(VC::FEND); ?>">
        <?php if($user?->{UC::COL_TP} == PC::CPN || strtolower((string)($user?->{UC::COL_TP} ?? '')) == PC::HR || $user?->{UC::COL_TP} == PC::SA): ?>
            <?php
                $logsBase  = VW::USR . '.log';
                $logsKebab = Str::kebab($logsBase);
                $logsName  = Route::has($logsBase) ? $logsBase : (Route::has($logsKebab) ? $logsKebab : null);
                $logsUrl   = $logsName ? route($logsName, []) : '#';
                $logsGuard = Utility::fetchLinkMessage($lang, VW::USR, 'view_user_log_unavailable') ?? 'User logs route is unavailable. Please contact technical support or your domain administrator.';
                $logsId    = 'users-log-link';
            ?>
            <a id="<?php echo e($logsId); ?>"
               href="<?php echo e($logsUrl); ?>"
               data-url="<?php echo e($logsUrl); ?>"
               data-guard-msg="<?php echo e($logsGuard); ?>"
               class="<?php echo e(VC::BT_SM); ?> <?php echo e(Request::segment(1) === VW::USR ? 'active' : ''); ?> <?php echo e(VC::BT_PRM); ?>"
               data-bs-toggle="tooltip"
               data-bs-placement="top"
               title="<?php echo e(__('User Logs History')); ?>">
                <i class="ti ti-user-check"></i>
            </a>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PC::CR_USER)): ?>
            <?php
                $createBase  = VW::USR . '.create';
                $createKebab = Str::kebab($createBase);
                $createName  = Route::has($createBase) ? $createBase : (Route::has($createKebab) ? $createKebab : null);
                $createUrl   = $createName ? route($createName, []) : '#';
                $createGuard = Utility::fetchLinkMessage($lang, VW::USR, 'create_user_unavailable') ?? 'Create user route is unavailable. Please contact technical support or your domain administrator.';
                $createId    = 'user-create-link';
            ?>
            <a id="<?php echo e($createId); ?>"
               href="<?php echo e($createUrl); ?>"
               data-url="<?php echo e($createUrl); ?>"
               data-ajax-popup="true"
               data-bs-toggle="tooltip"
               title="<?php echo e(__('Create')); ?>"
               data-guard-msg="<?php echo e($createGuard); ?>"
               class="<?php echo e(VC::BT_SM_PM); ?>">
                <i class="<?php echo e(VC::TI_PLS); ?>"></i>
            </a>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
    <div class="<?php echo e(VC::RW); ?>">
        <div class="col-xxl-12">
            <div class="<?php echo e(VC::RW); ?>">
                <?php $__empty_1 = true; $__currentLoopData = (($users ?? null) instanceof Collection || is_array($users ?? null)) ? $users : []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $uid = (string) data_get($usr, 'id', '');
                        $isActive = (int) (data_get($usr, UC::COL_IA) ?? 0) === 1;
                    ?>

                    <div class="<?php echo e(VC::CM3); ?> <?php echo e(VC::MB4); ?>">
                        <div class="<?php echo e(VC::CD); ?>">
                            <div class="card-header border-0 <?php echo e(VC::MB0); ?>">
                                <div class="<?php echo e(VC::DFL_AIC_JCB); ?>">
                                    <h6 class="<?php echo e(VC::MB0); ?>">
                                        <div class="<?php echo e(VC::BDG); ?> <?php echo e(VC::BG_P); ?> p-2 px-3 rounded">
                                            <?php echo e(data_get($usr,'type') ? ucfirst((string) data_get($usr,'type')) : __('No user type available')); ?>

                                        </div>
                                    </h6>
                                </div>

                                <?php if(Gate::check(PC::ED_USER) || Gate::check(PC::DEL_USER)): ?>
                                    <div class="card-header-right">
                                        <div class="btn-group card-option">
                                            <?php if($isActive): ?>
                                                <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <div class="<?php echo e(VC::DRP_MN_EM); ?>">
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PC::ED_USER)): ?>
                                                        <?php
                                                            $editBase  = VW::USR . '.edit';
                                                            $editName  = Route::has($editBase) ? $editBase : (Route::has(Str::kebab($editBase)) ? Str::kebab($editBase) : null);
                                                            $editUrl   = ($editName && $uid) ? route($editName, [$uid]) : '#';
                                                            $editGuard = Utility::fetchLinkMessage($lang, VW::USR, 'edit_user_route_unavailable') ?? 'Edit user route is unavailable. Please contact technical support or your domain administrator.';
                                                            $editId    = 'user-edit-link-' . $uid;
                                                        ?>
                                                        <a id="<?php echo e($editId); ?>"
                                                           href="<?php echo e($editUrl); ?>"
                                                           data-url="<?php echo e($editUrl); ?>"
                                                           data-size="lg"
                                                           data-ajax-popup="true"
                                                           class="dropdown-item"
                                                           title="<?php echo e(__('Edit User')); ?>"
                                                           data-guard-msg="<?php echo e($editGuard); ?>">
                                                            <i class="<?php echo e(VC::TI_PC); ?>"></i><span><?php echo e(__('Edit')); ?></span>
                                                        </a>

                                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                            <script defer>
                                                                (() => {
                                                                    try {
                                                                        const a = document.getElementById(<?php echo json_encode($editId, 15, 512) ?>);
                                                                        if (!a || a.getAttribute('data-listener-active') === 'true') return;
                                                                        a.setAttribute('data-listener-active', 'true');
                                                                        const url = a.getAttribute('data-url') ?? '#';
                                                                        if ((a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') a.setAttribute('href', url);
                                                                        a.addEventListener('click', (e) => {
                                                                            const href = a.getAttribute('href') ?? '#';
                                                                            if (href && href !== '#') return;
                                                                            e.preventDefault();
                                                                            const msg = a.getAttribute('data-guard-msg') ?? 'Edit user route is unavailable. Please contact technical support or your domain administrator.';
                                                                            let c = document.getElementById('toast-container');
                                                                            if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                                            const hasBS = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
                                                                            if (hasBS) {
                                                                                const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                                                                                const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
                                                                                t.appendChild(b); c.appendChild(t);
                                                                                try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                                                                            } else { alert(msg); }
                                                                            a.setAttribute('data-failed-route','true');
                                                                        });
                                                                    } catch {}
                                                                })();
                                                            </script>
                                                        <?php $__env->stopPush(); ?>
                                                    <?php endif; ?>

                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PC::DEL_USER)): ?>
                                                        <?php
                                                            $delBase   = VW::USR . '.destroy';
                                                            $delName   = Route::has($delBase) ? $delBase : (Route::has(Str::kebab($delBase)) ? Str::kebab($delBase) : null);
                                                            $delUrl    = ($delName && $uid) ? route($delName, [$uid]) : '#';
                                                            $delGuard  = Utility::fetchLinkMessage($lang, VW::USR, 'delete_user_route_unavailable') ?? 'Delete user route is unavailable. Please contact technical support or your domain administrator.';
                                                            $delFormId = 'user-delete-form-' . $uid;
                                                        ?>
                                                        <?php echo Form::open([
                                                            'method'               => 'DELETE',
                                                            'url'                  => $delUrl,
                                                            'id'                   => $delFormId,
                                                            'data-resolved-action' => $delUrl,
                                                            'data-guard-msg'       => $delGuard,
                                                            'data-sv-localized'    => 'true',
                                                        ]); ?>

                                                            <a href="#!"
                                                               class="dropdown-item bs-pass-para"
                                                               data-confirm="<?php echo e(__(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?')); ?>|<?php echo e(__(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?')); ?>"
                                                               data-confirm-yes="document.getElementById('<?php echo e($delFormId); ?>').submit();">
                                                                <i class="<?php echo e(VC::TI_ARC); ?>"></i>
                                                                <span>
                                                                    <?php if((int) (data_get($usr,'delete_status') ?? 0) !== 0): ?>
                                                                        <?php echo e(__('Delete')); ?>

                                                                    <?php else: ?>
                                                                        <?php echo e(__('Restore')); ?>

                                                                    <?php endif; ?>
                                                                </span>
                                                            </a>
                                                        <?php echo Form::close(); ?>


                                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                            <script defer>
                                                                (() => {
                                                                    try {
                                                                        const f = document.getElementById(<?php echo json_encode($delFormId, 15, 512) ?>);
                                                                        if (!f || f.getAttribute('data-listener-active') === 'true') return;
                                                                        f.setAttribute('data-listener-active', 'true');
                                                                        const resolved = f.getAttribute('data-resolved-action') || '#';
                                                                        if ((f.getAttribute('action') === '#' || !f.getAttribute('action')) && resolved !== '#') f.setAttribute('action', resolved);
                                                                        f.addEventListener('submit', (e) => {
                                                                            const action = f.getAttribute('action') || '#';
                                                                            if (action && action !== '#') return;
                                                                            e.preventDefault();
                                                                            const msg = f.getAttribute('data-guard-msg') || 'Delete user route is unavailable. Please contact technical support or your domain administrator.';
                                                                            let c = document.getElementById('toast-container');
                                                                            if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                                            const hasBS = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
                                                                            if (hasBS) {
                                                                                const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                                                                                const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
                                                                                t.appendChild(b); c.appendChild(t);
                                                                                try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                                                                            } else { alert(msg); }
                                                                            f.setAttribute('data-failed-route','true');
                                                                        });
                                                                    } catch {}
                                                                })();
                                                            </script>
                                                        <?php $__env->stopPush(); ?>
                                                    <?php endif; ?>

                                                    <?php
                                                        $resetBase  = VW::USR . '.reset';
                                                        $resetName  = Route::has($resetBase) ? $resetBase : (Route::has(Str::kebab($resetBase)) ? Str::kebab($resetBase) : null);
                                                        $encId      = $uid !== '' ? Crypt::encrypt($uid) : '';
                                                        $resetUrl   = ($resetName && $encId) ? route($resetName, [$encId]) : '#';
                                                        $resetGuard = Utility::fetchLinkMessage($lang, VW::USR, 'reset_user_password_route_unavailable') ?? 'Reset password route is unavailable. Please contact technical support or your domain administrator.';
                                                        $resetId    = 'user-reset-link-' . $uid;
                                                    ?>
                                                    <a id="<?php echo e($resetId); ?>"
                                                       href="<?php echo e($resetUrl); ?>"
                                                       data-url="<?php echo e($resetUrl); ?>"
                                                       data-ajax-popup="true"
                                                       data-size="md"
                                                       class="dropdown-item"
                                                       title="<?php echo e(__('Reset Password')); ?>"
                                                       data-guard-msg="<?php echo e($resetGuard); ?>">
                                                        <i class="ti ti-adjustments"></i><span><?php echo e(__('Reset Password')); ?></span>
                                                    </a>

                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                        <script defer>
                                                            (() => {
                                                                try {
                                                                    const a = document.getElementById(<?php echo json_encode($resetId, 15, 512) ?>);
                                                                    if (!a || a.getAttribute('data-listener-active') === 'true') return;
                                                                    a.setAttribute('data-listener-active', 'true');
                                                                    const url = a.getAttribute('data-url') ?? '#';
                                                                    if ((a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') a.setAttribute('href', url);
                                                                    a.addEventListener('click', (e) => {
                                                                        const href = a.getAttribute('href') ?? '#';
                                                                        if (href && href !== '#') return;
                                                                        e.preventDefault();
                                                                        const msg = a.getAttribute('data-guard-msg') ?? 'Reset password route is unavailable. Please contact technical support or your domain administrator.';
                                                                        let c = document.getElementById('toast-container');
                                                                        if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                                        const hasBS = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
                                                                        if (hasBS) {
                                                                            const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                                                                            const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
                                                                            t.appendChild(b); c.appendChild(t);
                                                                            try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                                                                        } else { alert(msg); }
                                                                        a.setAttribute('data-failed-route','true');
                                                                    });
                                                                } catch {}
                                                            })();
                                                        </script>
                                                    <?php $__env->stopPush(); ?>
                                                </div>
                                            <?php else: ?>
                                                <a href="#" class="action-item text-lg"><i class="ti ti-lock"></i></a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-body full-card text-center">
                                <div class="img-fluid <?php echo e(VC::AV_CC); ?> card-avatar">
                                    <img src="<?php echo e(data_get($usr,'avatar') ? asset(Storage::url('uploads/avatar/'.data_get($usr,'avatar'))) : asset(Storage::url('uploads/avatar/avatar.png'))); ?>" class="img-user wid-80 round-img <?php echo e(VC::AV_CC); ?>">
                                </div>
                                <h4 class="<?php echo e(VC::MT3); ?> text-primary"><?php echo e(data_get($usr,'name') ?: __('No name available')); ?></h4>
                                <?php if((int) (data_get($usr,'delete_status') ?? 0) === 0): ?>
                                    <h5 class="office-time <?php echo e(VC::MB0); ?>"><?php echo e(__('Soft Deleted')); ?></h5>
                                <?php endif; ?>
                                <small class="text-primary"><?php echo e(data_get($usr,'email') ?: __('No email available')); ?></small>
                                <p></p>
                                <div class="text-center" data-bs-toggle="tooltip" title="<?php echo e(__('Last Login')); ?>">
                                    <?php echo e(data_get($usr,'last_login_at') ?: __('No last login available')); ?>

                                </div>

                                <?php if((string) (data_get($usr,UC::COL_TP) ?? '') === PC::SA): ?>
                                    <?php
                                        $upgBase   = VW::PLN . '.upgrade';
                                        $upgName   = Route::has($upgBase) ? $upgBase : (Route::has(Str::kebab($upgBase)) ? Str::kebab($upgBase) : null);
                                        $upgUrl    = ($upgName && $uid) ? route($upgName, [$uid]) : '#';
                                        $upgGuard  = Utility::fetchLinkMessage($lang, VW::PLN, 'upgrade_plan_route_unavailable') ?? 'Upgrade plan route is unavailable. Please contact technical support or your domain administrator.';
                                        $upgId     = 'plan-upgrade-link-' . $uid;
                                    ?>

                                    <div class="<?php echo e(VC::MT4); ?>">
                                        <div class="<?php echo e(VC::R_FLX_ALC_JCE); ?>">
                                            <div class="col-6 text-center">
                                                <span class="d-block font-bold <?php echo e(VC::MB0); ?>"><?php echo e(data_get($usr,'currentPlan.name') ?: __('No plan name available')); ?></span>
                                            </div>
                                            <div class="col-6 text-center">
                                                <a id="<?php echo e($upgId); ?>"
                                                   href="<?php echo e($upgUrl); ?>"
                                                   data-url="<?php echo e($upgUrl); ?>"
                                                   data-size="lg"
                                                   data-ajax-popup="true"
                                                   class="<?php echo e(VC::BT_OUTPM); ?>"
                                                   data-guard-msg="<?php echo e($upgGuard); ?>">
                                                    <?php echo e(__('Upgrade Plan')); ?>

                                                </a>
                                            </div>
                                            <div class="<?php echo e(VC::C12); ?>"><hr class="<?php echo e(VC::MY3); ?>"></div>
                                            <div class="<?php echo e(VC::C12); ?> text-center">
                                                <span class="text-dark <?php echo e(VC::TXS); ?>">
                                                    <?php echo e(__('Plan Expired : ')); ?>

                                                    <?php echo e(data_get($usr,'plan_expire_date') ? ($usr?->dateFormat(data_get($usr,'plan_expire_date')) ?? __('Failed to format date')) : __('Lifetime')); ?>

                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script>
                                            (() => {
                                                try {
                                                    const a = document.getElementById(<?php echo json_encode($upgId, 15, 512) ?>);
                                                    if (!a || a.getAttribute('data-listener-active') === 'true') return;
                                                    a.setAttribute('data-listener-active', 'true');
                                                    const url = a.getAttribute('data-url') ?? '#';
                                                    if ((a.getAttribute('href') === '#' || !a.getAttribute('href')) && url !== '#') a.setAttribute('href', url);
                                                    a.addEventListener('click', (e) => {
                                                        const href = a.getAttribute('href') ?? '#';
                                                        if (href && href !== '#') return;
                                                        e.preventDefault();
                                                        const msg = a.getAttribute('data-guard-msg') ?? 'Upgrade plan route is unavailable. Please contact technical support or your domain administrator.';
                                                        let c = document.getElementById('toast-container');
                                                        if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                        const hasBS = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
                                                        if (hasBS) {
                                                            const t = document.createElement('div'); t.className = 'toast'; t.setAttribute('role','alert'); t.setAttribute('aria-live','assertive'); t.setAttribute('aria-atomic','true');
                                                            const b = document.createElement('div'); b.className = 'toast-body'; b.textContent = msg;
                                                            t.appendChild(b); c.appendChild(t);
                                                            try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                                                        } else { alert(msg); }
                                                        a.setAttribute('data-failed-route','true');
                                                    });
                                                } catch {}
                                            })();
                                        </script>
                                    <?php $__env->stopPush(); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="<?php echo e(VC::CM12); ?>"><p class="text-center text-muted"><?php echo e(__('No users available')); ?></p></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
    <script defer src="<?php echo e(asset('assets/js/routes/users/index.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/users/index.blade.php ENDPATH**/ ?>