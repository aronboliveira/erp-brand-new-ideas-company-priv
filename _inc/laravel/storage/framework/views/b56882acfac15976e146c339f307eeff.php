<?php
    use Illuminate\Support\Facades\{Route, Storage};
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        ViewsConstants,
        PermissionsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
        StacksConstants
    };
    $lang = Utility::fetchUserLang();
    $createName    = ViewsConstants::CLT . '.create';
    $createRoute   = Route::has($createName)
        ? route($createName)
        : (Route::has(Str::kebab($createName))
            ? route(Str::kebab($createName))
            : '#');
    $createGuardMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::CLT,
        'client_create_route_unavailable'
    ) ?? 'Create Client route is unavailable. Please contact technical support or your domain administrator.';
?>


<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
    <?php echo e(__('Manage Client')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e(Route::has('dashboard') ? route('dashboard') : '#'); ?>"
           <?php echo e(Route::has('dashboard') ? '' : 'aria-disabled="true"'); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item"><?php echo e(__('Client')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_ACT_BTN); ?>
    <div class="<?php echo e(VC::FEND); ?>">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::CR_CLT)): ?>
            <a
                href="#"
                id="createClientBtn"
                data-url="<?php echo e($createRoute); ?>"
                data-guard-msg="<?php echo e($createGuardMsg); ?>"
                data-listener-alias="create-client"
                data-size="md"
                data-ajax-popup="true"
                data-bs-toggle="tooltip"
                title="<?php echo e(__('Create')); ?>"
                class="<?php echo e(VC::BT_SM_PM); ?>"
            >
                <i class="<?php echo e(VC::TI_PLS); ?>"></i>
            </a>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
    <div class="<?php echo e(VC::RW); ?>">
        <div class="col-xxl-12">
            <div class="<?php echo e(VC::RW); ?>">
                <?php
                    $clientList = ((is_array($clients ?? null) && count($clients ?? [])) || (($clients ?? null) instanceof Collection && ($clients)->isNotEmpty())) ? $clients : [];
                ?>
                <?php $__empty_1 = true; $__currentLoopData = $clientList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $cid = data_get($client,'id');
                        $resetName = 'clients.reset';
                        $resetUrl = ($cid && Route::has($resetName)) ? route($resetName, Crypt::encrypt($cid)) : '#';
                        $resetGuard = Utility::fetchLinkMessage($lang, ViewsConstants::CLT, 'client_reset_route_unavailable') ?? __('No reset route available');
                        $avatar = data_get($client,'avatar');
                        $avatarSrc = $avatar ? asset(Storage::url('uploads/avatar/'.$avatar)) : asset(Storage::url('uploads/avatar/avatar.png'));
                        $dealRel = data_get($client,'clientDeals') ?? null;
                        $dealCount = ((is_array($dealRel) && count($dealRel)) || ($dealRel instanceof Collection && $dealRel->isNotEmpty())) ? (is_array($dealRel) ? count($dealRel) : $dealRel->count()) : 0;
                        $projRel = data_get($client,'clientProjects') ?? null;
                        $projCount = ((is_array($projRel) && count($projRel)) || ($projRel instanceof Collection && $projRel->isNotEmpty())) ? (is_array($projRel) ? count($projRel) : $projRel->count()) : 0;
                    ?>
                    <div class="<?php echo e(VC::CM3); ?>">
                        <div class="<?php echo e(VC::CD); ?> text-center">
                            <div class="card-header border-0 pb-0">
                                <div class="card-header-right">
                                    <div class="btn-group card-option">
                                        <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="<?php echo e(VC::TI_DRP); ?>"></i></button>
                                        <div class="<?php echo e(VC::DRP_MN_EM); ?>">
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit client')): ?>
                                                <?php
                                                    $editName = ViewsConstants::CLT . '.edit';
                                                    $editUrl = ($cid && Route::has($editName)) ? route($editName, $cid) : (( $cid && Route::has(Str::kebab($editName)) ) ? route(Str::kebab($editName), $cid) : '#');
                                                    $editGuard = Utility::fetchLinkMessage($lang, ViewsConstants::CLT, 'client_edit_route_unavailable') ?? __('No edit route available');
                                                ?>
                                                <a href="#" class="dropdown-item edit-icon" id="editClientBtn_<?php echo e($cid); ?>" data-url="<?php echo e($editUrl); ?>" data-guard-msg="<?php echo e($editGuard); ?>" data-listener-alias="edit-client" data-size="md" data-ajax-popup="true"><i class="<?php echo e(VC::TI_PC); ?>"></i><span><?php echo e(__('Edit')); ?></span></a>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete client')): ?>
                                                <?php
                                                    $destroyName = ViewsConstants::CLT . '.destroy';
                                                    $destroyUrl = ($cid && Route::has($destroyName)) ? route($destroyName, $cid) : (( $cid && Route::has(Str::kebab($destroyName)) ) ? route(Str::kebab($destroyName), $cid) : '#');
                                                    $destroyGuard = Utility::fetchLinkMessage($lang, ViewsConstants::CLT, 'client_destroy_route_unavailable') ?? __('No delete route available');
                                                    $deleteFormId = 'delete-form-' . $cid;
                                                ?>
                                                <?php echo Form::open([
                                                    'method'         => 'DELETE',
                                                    'url'            => $destroyUrl,
                                                    'id'             => $deleteFormId,
                                                    'data-url'       => $destroyUrl,
                                                    'data-guard-msg' => $destroyGuard,
                                                ]); ?>

                                                <a href="#" class="dropdown-item delete-icon" data-listener-alias="delete-client" data-confirm="<?php echo e(__(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?')); ?>|<?php echo e(__(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?')); ?>" data-confirm-yes="document.getElementById('<?php echo e($deleteFormId); ?>').submit();"><i class="<?php echo e(VC::TI_TRS); ?>"></i><span><?php echo e(((int) (data_get($client,'delete_status') ?? 1)) != 0 ? __('Delete') : __('Restore')); ?></span></a>
                                                <?php echo Form::close(); ?>

                                            <?php endif; ?>
                                            <?php
                                                $resetBtnId = 'resetClientBtn_' . $cid;
                                            ?>
                                            <a href="#" class="dropdown-item reset-icon" id="<?php echo e($resetBtnId); ?>" data-url="<?php echo e($resetUrl); ?>" data-guard-msg="<?php echo e($resetGuard); ?>" data-listener-alias="reset-client" data-ajax-popup="true"><i class="<?php echo e(VC::TI_ADJ); ?>"></i><span><?php echo e(__('Reset Password')); ?></span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="<?php echo e(VC::CD_MT); ?> full-card">
                                <div class="card-avatar"><img src="<?php echo e($avatarSrc); ?>" class="img-user wid-80 rounded-circle"></div>
                                <h4 class="mt-2 text-primary"><?php echo e(data_get($client,'name') ?: __('No client name available')); ?></h4>
                                <div class="<?php echo e(VC::DFL_AIC_JCB); ?>">
                                    <div class="me-4 text-primary"><?php echo e(data_get($client,'email') ?: __('No email available')); ?></div>
                                </div>
                                <div class="mt-2 h6" data-bs-toggle="tooltip" title="<?php echo e(__('Last Login')); ?>"><?php echo e(data_get($client,'last_login_at') ?: __('No last login available')); ?></div>
                            </div>
                            <div class="card-footer p-3">
                                <div class="<?php echo e(VC::DFL_JCB); ?>">
                                    <div>
                                        <h6 class="mb-0"><?php echo e($dealCount); ?></h6>
                                        <p class="text-muted text-sm mb-0"><?php echo e(__('Deals')); ?></p>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?php echo e($projCount); ?></h6>
                                        <p class="text-muted text-sm mb-0"><?php echo e(__('Projects')); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="<?php echo e(VC::CM12); ?>"><div class="<?php echo e(VC::CD); ?>"><div class="card-body"><p class="<?php echo e(VC::TXCT); ?>"><?php echo e(__('No clients available')); ?></p></div></div></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
    <script defer src="<?php echo e(asset('assets/js/routes/clients/index.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/clients/index.blade.php ENDPATH**/ ?>