<?php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{Support, Utility};
    use Illuminate\Support\Facades\{Auth, Crypt, Route, Storage};
    $lang = Utility::fetchUserLang();
?>

<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
<?php $__env->stopPush(); ?>
<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
    <?php echo e(__('Support')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('title'); ?>
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
    <li class="breadcrumb-item"><?php echo e(__('Support')); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::ADM_ACT_BTN); ?>
    <?php
        $sptGridBase = VW::SPT.'.grid';
        $sptGridKebab = Str::kebab($sptGridBase);
        $sptGridResolved = Route::has($sptGridBase) ? $sptGridBase : (Route::has($sptGridKebab) ? $sptGridKebab : null);
        $sptGridUrl = $sptGridResolved ? route($sptGridResolved) : '#';
        $sptCreateBase = VW::SPT.'.create';
        $sptCreateKebab = Str::kebab($sptCreateBase);
        $sptCreateResolved = Route::has($sptCreateBase) ? $sptCreateBase : (Route::has($sptCreateKebab) ? $sptCreateKebab : null);
        $sptCreateUrl = $sptCreateResolved ? route($sptCreateResolved) : '#';
        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
        $gridGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'grid_support_route_unavailable') ?? 'Grid support route is unavailable. Please contact technical support or your domain administrator.';
        $createGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'create_support_route_unavailable') ?? 'Create support route is unavailable. Please contact technical support or your domain administrator.';
    ?>
    <div class="<?php echo e(VC::FEND); ?>">
        <a href="<?php echo e($sptGridUrl); ?>"
           class="<?php echo e(VC::BT_SM_PM); ?> support-grid"
           data-url="<?php echo e($sptGridUrl); ?>"
           data-guard-msg="<?php echo e($gridGuardMsg); ?>"
           data-sv-localized="true"
           data-bs-toggle="tooltip"
           title="<?php echo e(__('Grid View')); ?>">
            <i class="ti ti-layout-grid <?php echo e(VC::TXT_WT); ?>"></i>
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
        <script src="<?php echo e(asset('assets/js/routes/supports/grid.js')); ?>" defer></script>
        <script src="<?php echo e(asset('assets/js/routes/supports/create.js')); ?>" defer></script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
	<div class="<?php echo e(VC::RW); ?>">
		<div class="<?php echo e(VC::CL3); ?> <?php echo e(VC::CM6); ?>">
			<div class="<?php echo e(VC::CD); ?>">
				<div class="card-body">
					<div class="<?php echo e(VC::RW); ?> <?php echo e(VC::ALC); ?> <?php echo e(VC::JCB); ?>">
						<div class="col-auto mb-3 mb-sm-0">
							<div class="<?php echo e(VC::DFL_AIC); ?>">
								<div class="theme-avatar <?php echo e(VC::BG_P); ?>"><i class="ti ti-cast"></i></div>
								<div class="ms-3"><small class="<?php echo e(VC::TXT_MT); ?>"><?php echo e(__('Total')); ?></small><h6 class="m-0"><?php echo e(__('Ticket')); ?></h6></div>
							</div>
						</div>
						<div class="col-auto text-end"><h3 class="m-0"><?php echo e((int)($countTicket ?? 0)); ?></h3></div>
					</div>
				</div>
			</div>
		</div>
		<div class="<?php echo e(VC::CL3); ?> <?php echo e(VC::CM6); ?>">
			<div class="<?php echo e(VC::CD); ?>">
				<div class="card-body">
					<div class="<?php echo e(VC::RW); ?> <?php echo e(VC::ALC); ?> <?php echo e(VC::JCB); ?>">
						<div class="col-auto mb-3 mb-sm-0">
							<div class="<?php echo e(VC::DFL_AIC); ?>">
								<div class="theme-avatar bg-info"><i class="ti ti-cast"></i></div>
								<div class="ms-3"><small class="<?php echo e(VC::TXT_MT); ?>"><?php echo e(__('Open')); ?></small><h6 class="m-0"><?php echo e(__('Ticket')); ?></h6></div>
							</div>
						</div>
						<div class="col-auto text-end"><h3 class="m-0"><?php echo e((int)($countOpenTicket ?? 0)); ?></h3></div>
					</div>
				</div>
			</div>
		</div>
		<div class="<?php echo e(VC::CL3); ?> <?php echo e(VC::CM6); ?>">
			<div class="<?php echo e(VC::CD); ?>">
				<div class="card-body">
					<div class="<?php echo e(VC::RW); ?> <?php echo e(VC::ALC); ?> <?php echo e(VC::JCB); ?>">
						<div class="col-auto mb-3 mb-sm-0">
							<div class="<?php echo e(VC::DFL_AIC); ?>">
								<div class="theme-avatar bg-warning"><i class="ti ti-cast"></i></div>
								<div class="ms-3"><small class="<?php echo e(VC::TXT_MT); ?>"><?php echo e(__('On Hold')); ?></small><h6 class="m-0"><?php echo e(__('Ticket')); ?></h6></div>
							</div>
						</div>
						<div class="col-auto text-end"><h3 class="m-0"><?php echo e((int)($countonholdTicket ?? 0)); ?></h3></div>
					</div>
				</div>
			</div>
		</div>
		<div class="<?php echo e(VC::CL3); ?> <?php echo e(VC::CM6); ?>">
			<div class="<?php echo e(VC::CD); ?>">
				<div class="card-body">
					<div class="<?php echo e(VC::RW); ?> <?php echo e(VC::ALC); ?> <?php echo e(VC::JCB); ?>">
						<div class="col-auto mb-3 mb-sm-0">
							<div class="<?php echo e(VC::DFL_AIC); ?>">
								<div class="theme-avatar bg-danger"><i class="ti ti-cast"></i></div>
								<div class="ms-3"><small class="<?php echo e(VC::TXT_MT); ?>"><?php echo e(__('Close')); ?></small><h6 class="m-0"><?php echo e(__('Ticket')); ?></h6></div>
							</div>
						</div>
						<div class="col-auto text-end"><h3 class="m-0"><?php echo e((int)($countCloseTicket ?? 0)); ?></h3></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="<?php echo e(VC::RW); ?>">
		<div class="<?php echo e(VC::CM12); ?>">
			<div class="<?php echo e(VC::CD); ?>">
				<div class="card-body table-border-style">
					<div class="table-responsive">
						<table class="<?php echo e(VC::TB); ?> datatable">
							<thead>
								<tr>
									<th scope="col"><?php echo e(__('Created By')); ?></th>
									<th scope="col"><?php echo e(__('Ticket')); ?></th>
									<th scope="col"><?php echo e(__('Code')); ?></th>
									<th scope="col"><?php echo e(__('Attachment')); ?></th>
									<th scope="col"><?php echo e(__('Assign User')); ?></th>
									<th scope="col"><?php echo e(__('Status')); ?></th>
									<th scope="col"><?php echo e(__('Created At')); ?></th>
									<th scope="col"><?php echo e(__('Action')); ?></th>
								</tr>
							</thead>
							<tbody class="list">
								<?php
									$supportpath = \App\Models\Utility::getFile('uploads/supports') ?? '';
								?>
								<?php $__empty_1 = true; $__currentLoopData = (($supports ?? null) instanceof \Illuminate\Support\Collection || is_array($supports ?? null)) ? $supports : []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $support): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
									<tr>
										<td scope="row">
											<div class="<?php echo e(VC::MD_AIC); ?>">
												<div>
													<div class="avatar-parent-child">
														<?php
															$avatar = data_get($support,'createdBy.avatar');
															$avatarSrc = !empty($avatar) ? asset(Storage::url('uploads/avatar')).'/'.$avatar : asset(Storage::url('uploads/avatar')).'/avatar.png';
															$unread = (is_object($support) && method_exists($support,'replyUnread')) ? (int)($support->replyUnread() ?? 0) : 0;
														?>
														<img alt="" class="<?php echo e(VC::AV_CC_SM); ?> me-1" src="<?php echo e($avatarSrc); ?>">
														<?php if($unread > 0): ?>
															<span class="avatar-child avatar-badge bg-success"></span>
														<?php endif; ?>
													</div>
												</div>
												<div class="media-body"><?php echo e(data_get($support,'createdBy.name') ?: __('No creator name available')); ?></div>
											</div>
										</td>
										<td scope="row">
											<div class="<?php echo e(VC::MD_AIC); ?>">
												<div class="media-body">
													<?php
                                                        $sptReplyBase = VW::SPT.'.reply';
                                                        $sptReplyKebab = Str::kebab($sptReplyBase);
                                                        $sptReplyResolved = Route::has($sptReplyBase) ? $sptReplyBase : (Route::has($sptReplyKebab) ? $sptReplyKebab : null);
                                                        $supportIdRaw = data_get($support,'id');
                                                        $supportId = (is_string($supportIdRaw) && Str::isUuid($supportIdRaw)) ? $supportIdRaw : null;
                                                        $sptEncryptedId = $supportId ? Crypt::encrypt($supportId) : null;
                                                        $sptReplyUrl = ($sptReplyResolved && $sptEncryptedId) ? route($sptReplyResolved, $sptEncryptedId) : '#';
                                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                        $sptReplyGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'reply_support_route_unavailable') ?? 'Reply support route is unavailable. Please contact technical support or your domain administrator.';
                                                        $sptReplyAnchorId = 'support-reply-'.Str::uuid();
                                                    ?>
                                                    <a id="<?php echo e($sptReplyAnchorId); ?>"
                                                    href="<?php echo e($sptReplyUrl); ?>"
                                                    class="name <?php echo e(VC::H6); ?> <?php echo e(VC::MB0); ?> <?php echo e(VC::TXSM); ?>"
                                                    data-url="<?php echo e($sptReplyUrl); ?>"
                                                    data-guard-msg="<?php echo e($sptReplyGuardMsg); ?>"
                                                    data-sv-localized="true"
                                                    data-bs-toggle="tooltip"
                                                    title="<?php echo e(__('Reply')); ?>">
                                                        <?php echo e(data_get($support,'subject') ?: __('No subject available')); ?>

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
                                                    <br/>
													<?php
														$priorityBadgeClasses = [0 => VC::BG_P, 1 => 'bg-info', 2 => 'bg-warning', 3 => 'bg-danger'];
														$prio = data_get($support,'priority');
														$prioClass = $priorityBadgeClasses[$prio] ?? 'bg-secondary';
														$priorityMap = Support::$priority ?? [];
														$prioLabel = isset($priorityMap[$prio]) ? __($priorityMap[$prio]) : __('No priority available');
													?>
													<span data-toggle="tooltip" data-title="<?php echo e(__('Priority')); ?>" class="text-capitalize badge <?php echo e($prioClass); ?> p-2 px-3 rounded"><?php echo e($prioLabel); ?></span>
												</div>
											</div>
										</td>
										<td><?php echo e(data_get($support,'ticket_code') ?: __('No code available')); ?></td>
										<td>
											<?php
												$attachment = data_get($support,'attachment');
												$fileUrl = !empty($attachment) && !empty($supportpath) ? $supportpath.'/'.$attachment : '';
											?>
											<?php if(!empty($fileUrl)): ?>
												<a class="<?php echo e(VC::ACT_BTN_PRIM); ?> <?php echo e(VC::BT_SM_CT); ?>" href="<?php echo e($fileUrl); ?>" download data-bs-toggle="tooltip" title="<?php echo e(__('Download')); ?>" target="_blank">
													<i class="<?php echo e(VC::TI_DWN); ?> <?php echo e(VC::TXT_WT); ?>"></i>
												</a>
												<a href="<?php echo e($fileUrl); ?>" class="action-btn bg-secondary ms-2 <?php echo e(VC::BT_SM_CT); ?>">
													<span class="btn-inner--icon"><i class="ti ti-crosshair <?php echo e(VC::TXT_WT); ?>"></i></span>
												</a>
											<?php else: ?>
												-
											<?php endif; ?>
										</td>
										<td><?php echo e(data_get($support,'assignUser.name') ?: __('No user name found')); ?></td>
										<td>
											<?php
												$status = (string) data_get($support,'status','');
												$statusMap = Support::$status ?? [];
												$statusLabel = isset($statusMap[$status]) ? __($statusMap[$status]) : __('No status available');
												$statusClass = $status === 'Open' ? 'bg-success' : ($status === 'Close' ? 'bg-danger' : ($status === 'On Hold' ? 'bg-warning' : 'bg-secondary'));
											?>
											<span class="status_badge text-capitalize badge <?php echo e($statusClass); ?> p-2 px-3 rounded"><?php echo e($statusLabel); ?></span>
										</td>
										<td><?php echo e($user?->dateFormat(data_get($support,'created_at')) ?? __('Failed to get created date')); ?></td>
										<td class="Action">
											<span>
												<div class="<?php echo e(VC::ACT_BTN_WRN); ?> me-2">
													<?php
                                                        $sptReplyBase = VW::SPT.'.reply';
                                                        $sptReplyKebab = Str::kebab($sptReplyBase);
                                                        $sptReplyResolved = Route::has($sptReplyBase) ? $sptReplyBase : (Route::has($sptReplyKebab) ? $sptReplyKebab : null);
                                                        $supportIdRaw = data_get($support,'id');
                                                        $supportId = (is_string($supportIdRaw) && Str::isUuid($supportIdRaw)) ? $supportIdRaw : null;
                                                        $sptEncryptedId = $supportId ? Crypt::encrypt($supportId) : null;
                                                        $sptReplyUrl = ($sptReplyResolved && $sptEncryptedId) ? route($sptReplyResolved, $sptEncryptedId) : '#';
                                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                        $sptReplyGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'reply_support_route_unavailable') ?? 'Reply support route is unavailable. Please contact technical support or your domain administrator.';
                                                        $sptReplyAnchorId = 'support-reply-'.($supportId ? substr(md5($supportId),0,8) : 'x');
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
												<?php if((($user?->type) ?? '') === 'company' || (($user?->id) ?? null) === data_get($support,'ticket_created')): ?>
													<div class="<?php echo e(VC::ACT_BTN_PRIM); ?> me-2">
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
                                                                                const href = el.getAttribute('href') ?? '#!';
                                                                                const url = el.getAttribute('data-url') ?? href ?? '#';
                                                                                if (url !== '#' && href !== '#!' && action !== '#') { return; }
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
											</span>
										</td>
									</tr>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
									<tr><td colspan="8" class="text-center text-muted"><?php echo e(__('No supports available')); ?></td></tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/supports/index.blade.php ENDPATH**/ ?>