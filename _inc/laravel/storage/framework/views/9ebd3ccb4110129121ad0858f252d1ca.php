<?php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants
    };
    use App\Models\{Support, Utility};
    use Illuminate\Support\Facades\{Auth, Crypt, Route, Storage};
    use Illuminate\Support\{Collection, Str};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
?>

<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
<?php $__env->stopPush(); ?>
<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
    <?php echo e(__('Support')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 "><?php echo e(__('Support')); ?></h5>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e(Route::has('dashboard') ? route('dashboard') : '#'); ?>"
        <?php echo e(Route::has('dashboard') ? '' : 'aria-disabled="true"'); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo e(__('Support')); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::ADM_ACT_BTN); ?>
    <?php
        $sptIndexBase = VW::SPT.'.index';
        $sptIndexKebab = Str::kebab($sptIndexBase);
        $sptIndexResolved = Route::has($sptIndexBase) ? $sptIndexBase : (Route::has($sptIndexKebab) ? $sptIndexKebab : null);
        $sptIndexUrl = $sptIndexResolved ? route($sptIndexResolved) : '#';
        $sptCreateBase = VW::SPT.'.create';
        $sptCreateKebab = Str::kebab($sptCreateBase);
        $sptCreateResolved = Route::has($sptCreateBase) ? $sptCreateBase : (Route::has($sptCreateKebab) ? $sptCreateKebab : null);
        $sptCreateUrl = $sptCreateResolved ? route($sptCreateResolved) : '#';
        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
        $listGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'list_support_route_unavailable') ?? 'List support route is unavailable. Please contact technical support or your domain administrator.';
        $createGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'create_support_route_unavailable') ?? 'Create support route is unavailable. Please contact technical support or your domain administrator.';
    ?>
    <div class="<?php echo e(VC::FEND); ?>">
        <a href="<?php echo e($sptIndexUrl); ?>"
           class="<?php echo e(VC::BT_SM_PM); ?> support-list"
           data-url="<?php echo e($sptIndexUrl); ?>"
           data-guard-msg="<?php echo e($listGuardMsg); ?>"
           data-sv-localized="true"
           data-bs-toggle="tooltip"
           title="<?php echo e(__('List View')); ?>">
            <i class="<?php echo e(VC::TI_LT); ?>"></i>
        </a>
        <a href="<?php echo e($sptCreateUrl); ?>"
           data-size="lg"
           data-url="<?php echo e($sptCreateUrl); ?>"
           data-ajax-popup="true"
           data-bs-toggle="tooltip"
           title="<?php echo e(__('Create')); ?>"
           data-title="<?php echo e(__('Create Support')); ?>"
           class="<?php echo e(VC::BT_SM_PM); ?> support-create"
           data-guard-msg="<?php echo e($createGuardMsg); ?>"
           data-sv-localized="true">
            <i class="<?php echo e(VC::TI_PLS); ?>"></i>
        </a>
    </div>
    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
        <script src="<?php echo e(asset('assets/js/routes/supports/list.js')); ?>" defer></script>
        <script src="<?php echo e(asset('assets/js/routes/supports/create.js')); ?>" defer></script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('filter'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
	<div class="<?php echo e(VC::RW); ?>">
		<?php $__empty_1 = true; $__currentLoopData = (($supports ?? null) instanceof Collection || is_array($supports ?? null)) ? $supports : []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $support): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
			<div class="<?php echo e(VC::CM3); ?>">
				<div class="<?php echo e(VC::CD_FL); ?>">
					<div class="card-header">
						<div class="<?php echo e(VC::R_ALC); ?>">
							<div class="<?php echo e(VC::C_AT); ?>">
                                <?php
                                    $avatarFile = data_get($support,'createdBy.avatar');
                                    $avatarSrc = !empty($avatarFile)
                                        ? Storage::url('uploads/avatar/'.$avatarFile)
                                        : Storage::url('uploads/avatar/avatar.png');
                                    $unread = (is_object($support) && method_exists($support,'replyUnread')) ? (int)($support->replyUnread() ?? 0) : 0;
                                ?>
								<a href="#" class="<?php echo e(VC::AV_CC); ?>">
									<img alt="" src="<?php echo e($avatarSrc); ?>">
									<?php if($unread > 0): ?>
										<span class="avatar-child avatar-badge bg-success"></span>
									<?php endif; ?>
								</a>
							</div>
							<div class="col">
								<a href="#!" class="<?php echo e(VC::DBL); ?> <?php echo e(VC::H6); ?> <?php echo e(VC::MB0); ?>"><?php echo e(data_get($support,'createdBy.name') ?: __('No creator name available')); ?></a>
								<small class="<?php echo e(VC::DBL); ?> <?php echo e(VC::TXT_MT); ?>"><?php echo e(data_get($support,'subject') ?: __('No subject available')); ?></small>
							</div>
						</div>
					</div>
					<div class="card-body">
						<div class="<?php echo e(VC::RW); ?>">
							<div class="col text-center">
								<span class="<?php echo e(VC::H6); ?> <?php echo e(VC::MB0); ?>"><?php echo e(data_get($support,'ticket_code') ?: __('No code available')); ?></span>
								<span class="<?php echo e(VC::DBL); ?> <?php echo e(VC::TXSM); ?>"><?php echo e(__('Code')); ?></span>
							</div>
							<div class="col text-center">
								<?php
									$priorityBadgeClasses = [0 => VC::BG_P, 1 => 'badge-info', 2 => 'badge-warning', 3 => 'badge-danger'];
									$prio = data_get($support,'priority');
									$badge = $priorityBadgeClasses[$prio] ?? 'bg-secondary';
									$priorityMap = Support::$priority ?? [];
									$prioLabel = isset($priorityMap[$prio]) ? __($priorityMap[$prio]) : __('No priority available');
								?>
								<span class="<?php echo e(VC::H6); ?> <?php echo e(VC::MB0); ?>">
									<span class="text-capitalize <?php echo e(VC::BDG); ?> <?php echo e($badge); ?> rounded-pill badge-sm"><?php echo e($prioLabel); ?></span>
								</span>
								<span class="<?php echo e(VC::DBL); ?> <?php echo e(VC::TXSM); ?>"><?php echo e(__('Priority')); ?></span>
							</div>
							<div class="col text-center">
                                <?php
                                    $attachment = data_get($support,'attachment');
                                    $attachUrl = !empty($attachment) ? Storage::url('uploads/supports/'.$attachment) : null;
                                ?>
								<span class="<?php echo e(VC::H6); ?> <?php echo e(VC::MB0); ?>">
									<?php if(!empty($attachment)): ?>
										<a href="<?php echo e($attachUrl); ?>" download class="<?php echo e(VC::BT_SM); ?> btn-secondary btn-icon rounded-pill" target="_blank">
											<span class="btn-inner--icon"><i class="<?php echo e(VC::TI_DWN); ?>"></i></span>
										</a>
									<?php else: ?>
										<?php echo e(__('No attachment found')); ?>

									<?php endif; ?>
								</span>
							</div>
						</div>
					</div>
					<div class="card-footer">
						<div class="<?php echo e(VC::R_ALC); ?>">
							<div class="col-6 text-start">
								<span data-toggle="tooltip" data-title="<?php echo e(__('Created Date')); ?>"><?php echo e($user?->dateFormat(data_get($support,'created_at')) ?? __('Failed to get created date')); ?></span>
							</div>
							<div class="col-6 <?php echo e(VC::DFL); ?> <?php echo e(VC::FEND); ?>">
								<div class="<?php echo e(VC::ACT_BTN_WRN); ?>">
									<?php
                                        $sptReplyBase = VW::SPT.'.reply';
                                        $sptReplyKebab = Str::kebab($sptReplyBase);
                                        $sptReplyResolved = Route::has($sptReplyBase) ? $sptReplyBase : (Route::has($sptReplyKebab) ? $sptReplyKebab : null);
                                        $supportId = data_get($support,'id','0');
                                        $sptEncryptedId = $supportId ? Crypt::encrypt($supportId) : null;
                                        $sptReplyUrl = ($sptReplyResolved && $sptEncryptedId) ? route($sptReplyResolved, $sptEncryptedId) : '#';
                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                        $sptReplyGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'reply_support_route_unavailable') ?? 'Reply support route is unavailable. Please contact technical support or your domain administrator.';
                                        $sptReplyAnchorId = 'support-reply-'.$supportId;
                                    ?>
                                    <a id="<?php echo e($sptReplyAnchorId); ?>"
                                    href="<?php echo e($sptReplyUrl); ?>"
                                    data-title="<?php echo e(__('Support Reply')); ?>"
                                    class="<?php echo e(VC::BT_SM_CT); ?>"
                                    data-bs-toggle="tooltip"
                                    title="<?php echo e(__('Reply')); ?>"
                                    data-original-title="<?php echo e(__('Reply')); ?>"
                                    data-url="<?php echo e($sptReplyUrl); ?>"
                                    data-guard-msg="<?php echo e($sptReplyGuardMsg); ?>"
                                    data-sv-localized="true">
                                        <i class="ti ti-corner-up-left <?php echo e(VC::TXT_WT); ?>"></i>
                                    </a>
                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (() => {
                                                try {
                                                    const el = document.getElementById('<?php echo e($sptReplyAnchorId); ?>');
                                                    if (!el) { return; }
                                                    if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                    el.setAttribute('data-listener-active', 'true');
                                                    el.addEventListener('click', (e) => {
                                                        try {
                                                            const href = el.getAttribute('href') ?? '#';
                                                            const url = el.getAttribute('data-url') ?? href ?? '#';
                                                            if (url !== '#' && href !== '#') { return; }
                                                            e.preventDefault();
                                                            const msg = el.getAttribute('data-guard-msg') ?? 'Reply support route is unavailable. Please contact technical support or your domain administrator.';
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
								</div>
								<?php if((($user?->id) ?? null) === data_get($support,'ticket_created')): ?>
									<div class="<?php echo e(VC::ACT_BTN_PRIM); ?>">
										<?php
                                            $sptEditBase = VW::SPT.'.edit';
                                            $sptEditKebab = Str::kebab($sptEditBase);
                                            $sptEditResolved = Route::has($sptEditBase) ? $sptEditBase : (Route::has($sptEditKebab) ? $sptEditKebab : null);
                                            $supportIdRaw = data_get($support,'id');
                                            $supportId = (is_string($supportIdRaw) && Str::isUuid($supportIdRaw)) ? $supportIdRaw : null;
                                            $sptEncryptedId = $supportId ? Crypt::encrypt($supportId) : null;
                                            $sptEditUrl = ($sptEditResolved && $sptEncryptedId) ? route($sptEditResolved, $sptEncryptedId) : '#';
                                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                            $sptEditGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'edit_support_route_unavailable') ?? 'Edit support route is unavailable. Please contact technical support or your domain administrator.';
                                            $sptEditAnchorId = 'support-edit-'.($supportId ? substr(md5($supportId),0,8) : 'x');
                                        ?>
                                        <a
                                            id="<?php echo e($sptEditAnchorId); ?>"
                                            href="<?php echo e($sptEditUrl); ?>"
                                            data-size="lg"
                                            data-url="<?php echo e($sptEditUrl); ?>"
                                            data-ajax-popup="true"
                                            data-title="<?php echo e(__('Edit Support')); ?>"
                                            class="<?php echo e(VC::BT_SM_CT); ?>"
                                            data-bs-toggle="tooltip"
                                            title="<?php echo e(__('Edit')); ?>"
                                            data-original-title="<?php echo e(__('Edit')); ?>"
                                            data-guard-msg="<?php echo e($sptEditGuardMsg); ?>"
                                            data-sv-localized="true"
                                        >
                                            <i class="<?php echo e(VC::TI_PC_WT); ?>"></i>
                                        </a>
                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                            <script defer>
                                                (() => {
                                                    try {
                                                        const el = document.getElementById('<?php echo e($sptEditAnchorId); ?>');
                                                        if (!el) { return; }
                                                        if (el.getAttribute('data-listener-active') === 'true') { return; }
                                                        el.setAttribute('data-listener-active', 'true');
                                                        el.addEventListener('click', (e) => {
                                                            try {
                                                                const href = el.getAttribute('href') ?? '#';
                                                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                if (url !== '#' && href !== '#') { return; }
                                                                e.preventDefault();
                                                                const msg = el.getAttribute('data-guard-msg') ?? 'Edit support route is unavailable. Please contact technical support or your domain administrator.';
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
									</div>
									<div class="<?php echo e(VC::ACT_BTN_DNG_2); ?>">
										<?php
                                            $sptDestroyBase = VW::SPT.'.destroy';
                                            $sptDestroyKebab = Str::kebab($sptDestroyBase);
                                            $sptDestroyResolved = Route::has($sptDestroyBase) ? $sptDestroyBase : (Route::has($sptDestroyKebab) ? $sptDestroyKebab : null);
                                            $supportIdRaw = data_get($support,'id');
                                            $supportId = (is_string($supportIdRaw) && Str::isUuid($supportIdRaw)) ? $supportIdRaw : null;
                                            $sptEncryptedId = $supportId ? Crypt::encrypt($supportId) : null;
                                            $sptDestroyUrl = ($sptDestroyResolved && $sptEncryptedId) ? route($sptDestroyResolved, $sptEncryptedId) : '#';
                                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                            $sptDeleteGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'delete_support_route_unavailable') ?? 'Delete support route is unavailable. Please contact technical support or your domain administrator.';
                                            $confirmTitle = __(Utility::fetchLinkMessage($langValue, 'generics', 'are_you_sure') ?? 'Are You Sure?');
                                            $confirmBody = __(Utility::fetchLinkMessage($langValue, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
                                            $formId = 'support-delete-form-'.Str::uuid();
                                            $anchorId = 'support-delete-btn-'.Str::uuid();
                                        ?>
                                        <?php echo Collective\Html\FormFacade::open(['method' => 'DELETE','url' => $sptDestroyUrl,'id' => $formId]); ?>

                                            <a id="<?php echo e($anchorId); ?>"
                                            href="#!"
                                            class="<?php echo e(VC::BT_SM_CT_PR); ?>"
                                            data-bs-toggle="tooltip"
                                            title="<?php echo e(__('Delete')); ?>"
                                            data-original-title="<?php echo e(__('Delete')); ?>"
                                            data-confirm="<?php echo e($confirmTitle); ?>|<?php echo e($confirmBody); ?>"
                                            data-confirm-yes="document.getElementById('<?php echo e($formId); ?>').submit();"
                                            data-url="<?php echo e($sptDestroyUrl); ?>"
                                            data-guard-msg="<?php echo e($sptDeleteGuardMsg); ?>"
                                            data-sv-localized="true">
                                                <i class="<?php echo e(VC::TI_TRS_WT); ?>"></i>
                                            </a>
                                        <?php echo Collective\Html\FormFacade::close(); ?>

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
                                                                const form = document.getElementById('<?php echo e($formId); ?>');
                                                                const action = form ? (form.getAttribute('action') ?? '#') : '#';
                                                                const href = el.getAttribute('href') ?? '#';
                                                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                if (url !== '#' && href !== '#' && action !== '#') { return; }
                                                                e.preventDefault();
                                                                const msg = el.getAttribute('data-guard-msg') ?? 'Delete support route is unavailable. Please contact technical support or your domain administrator.';
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
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
			<div class="<?php echo e(VC::CM12); ?>"><p class="text-center text-muted"><?php echo e(__('No supports available')); ?></p></div>
		<?php endif; ?>
	</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/supports/grid.blade.php ENDPATH**/ ?>