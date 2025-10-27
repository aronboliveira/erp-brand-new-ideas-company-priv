<?php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
        StacksConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};
    $lang = Utility::fetchUserLang();
?>

<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
    <?php echo e(__('Manage Role')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
<?php $__env->stopPush(); ?>
<?php $__env->startSection(YieldingConstants::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e(Route::has('dashboard') ? route('dashboard') : '#'); ?>"
        <?php echo e(Route::has('dashboard') ? '' : 'aria-disabled="true"'); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item"><?php echo e(__('Role')); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::ADM_ACT_BTN); ?>
    <div class="<?php echo e(VC::FEND); ?>">
        <?php
            $rlCreateBase = ViewsConstants::RL.'.create';
            $rlCreateKebab = Str::kebab($rlCreateBase);
            $rlCreateResolved = Route::has($rlCreateBase) ? $rlCreateBase : (Route::has($rlCreateKebab) ? $rlCreateKebab : null);
            $rlCreateUrl = $rlCreateResolved ? route($rlCreateResolved) : '#';
            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
            $rlCreateGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::RL, 'create_role_route_unavailable') ?? 'Create role route is unavailable. Please contact technical support or your domain administrator.';
        ?>
        <a
            href="<?php echo e($rlCreateUrl); ?>"
            data-size="lg"
            data-url="<?php echo e($rlCreateUrl); ?>"
            data-ajax-popup="true"
            data-bs-toggle="tooltip"
            title="<?php echo e(__('Create New Role')); ?>"
            class="<?php echo e(VC::BT_SM_PM); ?> role-create"
            data-guard-msg="<?php echo e($rlCreateGuardMsg); ?>"
            data-sv-localized="true"
        >
            <i class="<?php echo e(VC::TI_PLS); ?>"></i>
        </a>
        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
            <script src="<?php echo e(asset('assets/js/routes/roles/create.js')); ?>" defer></script>
        <?php $__env->stopPush(); ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
    <div class="<?php echo e(VC::RW); ?>">
        <div class="col-xl-12">
            <div class="<?php echo e(VC::CD); ?>">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="<?php echo e(VC::TB); ?> datatable">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Role')); ?></th>
                                    <th><?php echo e(__('Permissions')); ?></th>
                                    <th width="150"><?php echo e(__('Action')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = (($roles ?? null) instanceof Collection || is_array($roles ?? null)) ? $roles : []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php if((string)data_get($role,'name','') != 'client'): ?>
                                        <tr class="font-style">
                                            <td class="Role"><?php echo e(data_get($role,'name') ?: __('No role name available')); ?></td>
                                            <td class="Permission">
                                                <?php
                                                	$__perms = (is_object($role) && method_exists($role,'permissions')) ? ($role->permissions()->pluck('name') ?? collect()) : collect();
                                                ?>
                                                <?php $__empty_2 = true; $__currentLoopData = $__perms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permissionName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                                    <span class="<?php echo e(VC::BDG); ?> rounded p-2 m-1 px-3 <?php echo e(VC::BG_P); ?>"><?php echo e($permissionName ?: __('No permission name available')); ?></span>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                                    <span class="text-muted"><?php echo e(__('No permissions available')); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="Action">
                                                <span>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::ED_ROLE)): ?>
                                                        <?php
                                                            $rlEditBase = ViewsConstants::RL.'.edit';
                                                            $rlEditKebab = Str::kebab($rlEditBase);
                                                            $rlEditResolved = Route::has($rlEditBase) ? $rlEditBase : (Route::has($rlEditKebab) ? $rlEditKebab : null);
                                                            $roleIdValue = data_get($role, 'id');
                                                            $rlEncryptedId = $roleIdValue ? Crypt::encrypt($roleIdValue) : null;
                                                            $rlEditUrl = ($rlEditResolved && $rlEncryptedId) ? route($rlEditResolved, $rlEncryptedId) : '#';
                                                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                            $rlEditGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::RL, 'edit_role_route_unavailable') ?? 'Edit role route is unavailable. Please contact technical support or your domain administrator.';
                                                            $rlAnchorId = 'role-edit-'.($roleIdValue ? substr(md5((string) $roleIdValue), 0, 8) : 'x');
                                                        ?>
                                                        <div class="<?php echo e(VC::ACT_BTN_INF); ?>">
                                                            <a
                                                                id="<?php echo e($rlAnchorId); ?>"
                                                                href="<?php echo e($rlEditUrl); ?>"
                                                                class="<?php echo e(VC::BT_SM_FL_CT); ?>"
                                                                data-url="<?php echo e($rlEditUrl); ?>"
                                                                data-ajax-popup="true"
                                                                data-size="lg"
                                                                data-bs-toggle="tooltip"
                                                                title="<?php echo e(__('Edit')); ?>"
                                                                data-title="<?php echo e(__('Role Edit')); ?>"
                                                                aria-label="<?php echo e(__('Edit Role')); ?>"
                                                                data-guard-msg="<?php echo e($rlEditGuardMsg); ?>"
                                                                data-sv-localized="true"
                                                            >
                                                                <i class="<?php echo e(VC::TI_PC_WT); ?>"></i>
                                                            </a>
                                                        </div>
                                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                            <script defer>
                                                                (() => {
                                                                    try {
                                                                        const el = document.getElementById('<?php echo e($rlAnchorId); ?>');
                                                                        if (!el) { return; }
                                                                        if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                        el.setAttribute('data-listener-active', 'true');
                                                                        el.addEventListener('click', (e) => {
                                                                            try {
                                                                                const href = el.getAttribute('href') ?? '#';
                                                                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                if (url !== '#' && href !== '#') { return; }
                                                                                e.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? 'Edit role route is unavailable. Please contact technical support or your domain administrator.';
                                                                                const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                                                                let container = document.getElementById('toast-container');
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = 'toast-container';
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (hasBootstrap) {
                                                                                    const toast = document.createElement('div');
                                                                                    toast.className = 'toast';
                                                                                    toast.setAttribute('role', 'alert');
                                                                                    toast.setAttribute('aria-live', 'assertive');
                                                                                    toast.setAttribute('aria-atomic', 'true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toast.appendChild(body);
                                                                                    container.appendChild(toast);
                                                                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            } catch (err) {}
                                                                        });
                                                                    } catch (err) {}
                                                                })();
                                                            </script>
                                                        <?php $__env->stopPush(); ?>
                                                    <?php endif; ?>
                                                    <?php if(strtolower((string)data_get($role,'name','')) !== 'employee'): ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::DEL_ROLE)): ?>
                                                            <div class="<?php echo e(VC::ACT_BTN_DNG_2); ?>">
                                                                <?php echo Form::open(['method' => 'DELETE','route' => [ViewsConstants::RL.'.destroy', data_get($role,'id','0')],'id' => 'delete-form-'.data_get($role,'id','0')]); ?>

                                                                    <?php
                                                                        $rlDestroyBase = ViewsConstants::RL.'.destroy';
                                                                        $rlDestroyKebab = Str::kebab($rlDestroyBase);
                                                                        $rlDestroyResolved = Route::has($rlDestroyBase) ? $rlDestroyBase : (Route::has($rlDestroyKebab) ? $rlDestroyKebab : null);
                                                                        $roleIdValue = data_get($role, 'id');
                                                                        $rlEncryptedId = $roleIdValue ? Crypt::encrypt($roleIdValue) : null;
                                                                        $rlDestroyUrl = ($rlDestroyResolved && $rlEncryptedId) ? route($rlDestroyResolved, $rlEncryptedId) : '#';
                                                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                                        $rlDeleteGuardMsg = Utility::fetchLinkMessage($langValue, ViewsConstants::RL, 'delete_role_route_unavailable') ?? 'Delete role route is unavailable. Please contact technical support or your domain administrator.';
                                                                        $confirmTitle = __(Utility::fetchLinkMessage($langValue, 'generics', 'are_you_sure') ?? 'Are You Sure?');
                                                                        $confirmBody = __(Utility::fetchLinkMessage($langValue, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
                                                                        $formId = 'delete-form-'.($roleIdValue ?? 'x');
                                                                        $anchorId = 'role-delete-btn-'.($roleIdValue ?? 'x');
                                                                    ?>
                                                                    <?php echo Form::open(['method' => 'DELETE', 'url' => $rlDestroyUrl, 'id' => $formId]); ?>

                                                                        <a
                                                                            id="<?php echo e($anchorId); ?>"
                                                                            href="#"
                                                                            class="<?php echo e(VC::BT_SM_CT_PR); ?>"
                                                                            data-bs-toggle="tooltip"
                                                                            title="<?php echo e(__('Delete')); ?>"
                                                                            data-confirm="<?php echo e($confirmTitle); ?>|<?php echo e($confirmBody); ?>"
                                                                            data-confirm-yes="document.getElementById('<?php echo e($formId); ?>').submit();"
                                                                            data-url="<?php echo e($rlDestroyUrl); ?>"
                                                                            data-guard-msg="<?php echo e($rlDeleteGuardMsg); ?>"
                                                                            data-sv-localized="true"
                                                                        >
                                                                            <i class="<?php echo e(VC::TI_TRS_WT); ?>"></i>
                                                                        </a>
                                                                    <?php echo Form::close(); ?>

                                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                        <script defer>
                                                                            (() => {
                                                                                try {
                                                                                    const el = document.getElementById('<?php echo e($anchorId); ?>');
                                                                                    if (!el) { return; }
                                                                                    if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                                                    el.setAttribute('data-listener-active', 'true');
                                                                                    el.addEventListener('click', (e) => {
                                                                                        try {
                                                                                            const href = el.getAttribute('href') ?? '#';
                                                                                            const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                            const form = document.getElementById('<?php echo e($formId); ?>');
                                                                                            const action = form ? (form.getAttribute('action') ?? '#') : '#';
                                                                                            if (url !== '#' && href !== '#' && action !== '#') { return; }
                                                                                            e.preventDefault();
                                                                                            const msg = el.getAttribute('data-guard-msg') ?? 'Delete role route is unavailable. Please contact technical support or your domain administrator.';
                                                                                            const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                                                                            let container = document.getElementById('toast-container');
                                                                                            if (!container) {
                                                                                                container = document.createElement('div');
                                                                                                container.id = 'toast-container';
                                                                                                document.body.appendChild(container);
                                                                                            }
                                                                                            if (hasBootstrap) {
                                                                                                const toast = document.createElement('div');
                                                                                                toast.className = 'toast';
                                                                                                toast.setAttribute('role', 'alert');
                                                                                                toast.setAttribute('aria-live', 'assertive');
                                                                                                toast.setAttribute('aria-atomic', 'true');
                                                                                                const body = document.createElement('div');
                                                                                                body.className = 'toast-body';
                                                                                                body.textContent = msg;
                                                                                                toast.appendChild(body);
                                                                                                container.appendChild(toast);
                                                                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                                            } else {
                                                                                                alert(msg);
                                                                                            }
                                                                                            el.setAttribute('data-failed-route', 'true');
                                                                                            if (form) { form.setAttribute('data-failed-route', 'true'); }
                                                                                        } catch (err) {}
                                                                                    });
                                                                                } catch (err) {}
                                                                            })();
                                                                        </script>
                                                                    <?php $__env->stopPush(); ?>
                                                                    <i class="<?php echo e(VC::TI_TRS_WT); ?>"></i>
                                                                </a>
                                                                <?php echo Form::close(); ?>

                                                            </div>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted"><?php echo e(__('No roles available')); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/roles/index.blade.php ENDPATH**/ ?>