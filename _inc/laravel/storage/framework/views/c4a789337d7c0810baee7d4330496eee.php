<?php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        UsersConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Gate, Route, URL};
    use Illuminate\Support\{Collection, Str};

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
?>



<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
    <?php echo e(__('Manage Contract')); ?>

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
    <li class="breadcrumb-item"><?php echo e(__('Contract')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_ACT_BTN); ?>
    <div class="float-end">
        <?php
            $contractsIndexBaseRouteName = VW::CTC.'.index';
            $contractsIndexKebabRouteName = Str::kebab($contractsIndexBaseRouteName);
            $contractsIndexResolvedRouteName = Route::has($contractsIndexBaseRouteName)
                ? $contractsIndexBaseRouteName
                : (Route::has($contractsIndexKebabRouteName) ? $contractsIndexKebabRouteName : null);
            $contractsIndexUrl = $contractsIndexResolvedRouteName ? route($contractsIndexResolvedRouteName) : '#';
            $contractsLangValue = isset($lang) ? $lang : Utility::fetchUserLang();
            $contractsIndexGuardMessage = Utility::fetchLinkMessage($contractsLangValue, VW::CTC, 'index_route_unavailable')
                ?? 'Contracts index route is unavailable. Please contact technical support or your domain administrator.';
            $contractsIndexLinkId = 'contracts-index-list-link';
        ?>
        <a id="<?php echo e($contractsIndexLinkId); ?>"
        href="<?php echo e($contractsIndexUrl); ?>"
        class="<?php echo e(VC::BT_SM_PM); ?>"
        data-sv-localized="true"
        data-url="<?php echo e($contractsIndexUrl); ?>"
        data-guard-msg="<?php echo e($contractsIndexGuardMessage); ?>"
        data-bs-toggle="tooltip"
        title="<?php echo e(__('List View')); ?>">
            <i class="ti ti-list"></i>
        </a>
        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
            <script defer src="<?php echo e(asset('assets/js/routes/contracts/index.js')); ?>"></script>
        <?php $__env->stopPush(); ?>
        <?php if($user?->{UsersConstants::COL_TP} == PermissionsConstants::CPN || $user?->{UsersConstants::COL_TP} == PermissionsConstants::SA): ?>
            <?php
                $createRoute = VW::CTC . '.create';
                $createHref  = Route::has($createRoute) ? route($createRoute) : '#';
                $createGuard = Utility::fetchLinkMessage($lang, VW::CTC, 'create_route_unavailable')
                                ?? 'Create route is unavailable. Please contact technical support or your domain administrator.';
            ?>
            <a href="#"
               data-size="md"
               data-url="<?php echo e($createHref); ?>"
               data-ajax-popup="true"
               data-sv-localized="true"
               data-guard-msg="<?php echo e($createGuard); ?>"
               data-bs-toggle="tooltip"
               title="<?php echo e(__('Create New Contract')); ?>"
               class="<?php echo e(VC::BT_SM_PM); ?>">
                <i class="<?php echo e(VC::TI_PLS); ?>"></i>
            </a>
            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                <script defer>
                    (function () {
                        try {
                            if (!window.svToastOrAlert) {
                                window.svToastOrAlert = function (msg) {
                                    try {
                                        var hasBootstrap = !!(window.bootstrap && window.bootstrap.Toast);
                                        if (!hasBootstrap) { alert(msg); return; }
                                        var t = document.getElementById('route-guard-toast');
                                        if (!t) {
                                            t = document.createElement('div');
                                            t.id = 'route-guard-toast';
                                            t.className = 'toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3';
                                            t.setAttribute('role','alert');
                                            t.setAttribute('aria-live','assertive');
                                            t.setAttribute('aria-atomic','true');
                                            t.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
                                            document.body.appendChild(t);
                                        }
                                        var body = t.querySelector('.toast-body');
                                        if (body) body.textContent = msg;
                                        new window.bootstrap.Toast(t, { delay: 4000 }).show();
                                    } catch (e) { alert(msg); }
                                };
                            }

                            var idxA = document.querySelector('a.btn.btn-sm.btn-primary[href="<?php echo e($idxHref); ?>"]');
                            if (idxA && "<?php echo e($idxHref); ?>" === "#") {
                                idxA.addEventListener('click', function (e) {
                                    e.preventDefault();
                                    window.svToastOrAlert(idxA.getAttribute('data-guard-msg'));
                                });
                            }

                            var createA = document.querySelector('a[data-url="<?php echo e($createHref); ?>"]');
                            if (createA && "<?php echo e($createHref); ?>" === "#") {
                                createA.addEventListener('click', function (e) {
                                    e.preventDefault();
                                    window.svToastOrAlert(createA.getAttribute('data-guard-msg'));
                                });
                            }
                        } catch (_) {}
                    })();
                </script>
            <?php $__env->stopPush(); ?>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
    <div class="row">
        <?php
            $list = Utility::isFilled($contracts ?? []) ? $contracts : [];
        ?>
        <?php $__empty_1 = true; $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contract): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $cid = isset($contract->id) ? (string)$contract->id : '';
                $subject = (isset($contract->subject) && $contract->subject !== '') ? (string)$contract->subject : __('No subject available');
                $desc = (isset($contract->description) && $contract->description !== '') ? (string)$contract->description : __('No description available');
                $typeName = (string)(data_get($contract,'types.name') ?: __('No type available'));
                $clientName = (string)(data_get($contract,'clients.name') ?: __('No client available'));
                $valueRaw = isset($contract->value) ? $contract->value : null;
                $startRaw = isset($contract->start_date) ? $contract->start_date : null;
                $endRaw = isset($contract->end_date) ? $contract->end_date : null;
                $showRoute   = VW::CTC . '.show';
                $showHref    = Route::has($showRoute) && $cid !== '' ? route($showRoute, $cid) : '#';
                $showGuard   = Utility::fetchLinkMessage($lang, VW::CTC, 'show_route_unavailable')
                               ?? 'Show route is unavailable. Please contact technical support or your domain administrator.';
            ?>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <a href="<?php echo e($showHref); ?>"
                           class="mb-0"
                           data-sv-localized="true"
                           data-guard-msg="<?php echo e($showGuard); ?>">
                            <?php echo e($subject); ?>

                        </a>
                        <?php if($user?->{UsersConstants::COL_TP} == PermissionsConstants::CPN || $user?->{UsersConstants::COL_TP} == PermissionsConstants::SA): ?>
                            <?php
                                $editRoute   = VW::CTC . '.edit';
                                $editHref    = (Route::has($editRoute) && $cid !== '') ? route($editRoute, $cid) : '#';
                                $editGuard   = Utility::fetchLinkMessage($lang, VW::CTC, 'edit_route_unavailable')
                                               ?? 'Edit route is unavailable. Please contact technical support or your domain administrator.';
                                $destroyRoute = VW::CTC . '.destroy';
                                $deleteGuard  = Utility::fetchLinkMessage($lang, VW::CTC, 'delete_route_unavailable')
                                                ?? 'Delete route is unavailable. Please contact technical support or your domain administrator.';
                                $confirmMsg   = __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?')
                                                .'|'.
                                                __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
                                $deleteFormId = 'delete-form-' . $cid;
                                $openParams   = ['method' => 'DELETE', 'id' => $deleteFormId, 'data-sv-localized' => 'true', 'data-guard-msg' => $deleteGuard];
                                if (Route::has($destroyRoute) && $cid !== '') {
                                    $openParams['route'] = [$destroyRoute, $cid];
                                } else {
                                    $openParams['url'] = '#';
                                }
                            ?>
                            <div class="card-header-right">
                                <div class="btn-group card-option">
                                    <button type="button" class="btn dropdown-toggle"
                                            data-bs-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <div class="<?php echo e(VC::DRP_MN_EM); ?>">
                                        <a href="#!"
                                           data-size="md"
                                           data-url="<?php echo e($editHref); ?>"
                                           data-ajax-popup="true"
                                           class="dropdown-item"
                                           data-sv-localized="true"
                                           data-guard-msg="<?php echo e($editGuard); ?>"
                                           data-bs-original-title="<?php echo e(__('Edit User')); ?>">
                                            <i class="ti ti-pencil"></i>
                                            <span><?php echo e(__('Edit')); ?></span>
                                        </a>

                                        <?php echo Form::open($openParams); ?>

                                            <a href="#!"
                                               class="dropdown-item bs-pass-para"
                                               data-sv-localized="true"
                                               data-guard-msg="<?php echo e($deleteGuard); ?>"
                                               data-confirm="<?php echo e($confirmMsg); ?>"
                                               data-confirm-yes="document.getElementById('<?php echo e($deleteFormId); ?>').submit();">
                                                <i class="ti ti-archive"></i>
                                                <span><?php echo e(__('Delete')); ?></span>
                                            </a>
                                        <?php echo Form::close(); ?>

                                    </div>
                                </div>
                            </div>
                            <script defer>
                                (function () {
                                    try {
                                        var root = document.currentScript && document.currentScript.parentElement ? document.currentScript.parentElement : document;
                                        if (!window.svToastOrAlert) {
                                            window.svToastOrAlert = function (msg) {
                                                try {
                                                    var hasBootstrap = !!(window.bootstrap && window.bootstrap.Toast);
                                                    if (!hasBootstrap) { alert(msg); return; }
                                                    var t = document.getElementById('route-guard-toast');
                                                    if (!t) {
                                                        t = document.createElement('div');
                                                        t.id = 'route-guard-toast';
                                                        t.className = 'toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3';
                                                        t.setAttribute('role','alert');
                                                        t.setAttribute('aria-live','assertive');
                                                        t.setAttribute('aria-atomic','true');
                                                        t.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
                                                        document.body.appendChild(t);
                                                    }
                                                    var body = t.querySelector('.toast-body');
                                                    if (body) body.textContent = msg;
                                                    new window.bootstrap.Toast(t, { delay: 4000 }).show();
                                                } catch (e) { alert(msg); }
                                            };
                                        }

                                        var box = document.querySelector('.card a.mb-0[href="<?php echo e($showHref); ?>"]');
                                        if (box && "<?php echo e($showHref); ?>" === "#") {
                                            box.addEventListener('click', function (e) {
                                                e.preventDefault();
                                                window.svToastOrAlert(box.getAttribute('data-guard-msg'));
                                            });
                                        }

                                        var editA = document.querySelector('a.dropdown-item[data-url="<?php echo e($editHref); ?>"]');
                                        if (editA && "<?php echo e($editHref); ?>" === "#") {
                                            editA.addEventListener('click', function (e) {
                                                e.preventDefault();
                                                window.svToastOrAlert(editA.getAttribute('data-guard-msg'));
                                            });
                                        }

                                        var delForm = document.getElementById('<?php echo e($deleteFormId); ?>');
                                        if (delForm) {
                                            var hasAction = (delForm.getAttribute('action') || '').trim() !== '';
                                            var actionIsHash = (delForm.getAttribute('action') || '#') === '#';
                                            if (!hasAction || actionIsHash) {
                                                var delA = delForm.parentElement && delForm.parentElement.querySelector('a.dropdown-item.bs-pass-para');
                                                if (delA) {
                                                    delA.addEventListener('click', function (e) {
                                                        e.preventDefault();
                                                        window.svToastOrAlert(delA.getAttribute('data-guard-msg'));
                                                    });
                                                }
                                            }
                                        }
                                    } catch (_) {}
                                })();
                            </script>
                        <?php endif; ?>
                    </div>
                    <div class="card-body py-3 flex-grow-1">
                        <p class="text-sm mb-0"><?php echo e($desc); ?></p>
                    </div>
                    <div class="card-footer py-0">
                        <ul class="<?php echo e(VC::LG_FLSH); ?>">
                            <li class="list-group-item px-0">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <span class="form-label"><?php echo e(__('Contract Type')); ?>:</span>
                                    </div>
                                    <div class="col-6 text-end">
                                        <span class="badge bg-secondary p-2 px-3 rounded"><?php echo e($typeName); ?></span>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item px-0">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <span class="form-label"><?php echo e(__('Contract Value')); ?>:</span>
                                    </div>
                                    <div class="col-6 text-end">
                                        <span class="badge bg-secondary p-2 px-3 rounded">
                                            <?php echo e(is_numeric($valueRaw) && method_exists($user, 'priceFormat') ? ($user?->priceFormat($valueRaw) ?? __('Failed to format value')) : __('No value available')); ?>

                                        </span>
                                    </div>
                                </div>
                            </li>

                            <?php if($user?->{UsersConstants::COL_TP} != PermissionsConstants::CL): ?>
                                <li class="list-group-item px-0">
                                    <div class="row align-items-center">
                                        <div class="col-6">
                                            <span class="form-label"><?php echo e(__('Client')); ?>:</span>
                                        </div>
                                        <div class="col-6 text-end">
                                            <?php echo e($clientName); ?>

                                        </div>
                                    </div>
                                </li>
                            <?php endif; ?>

                            <li class="list-group-item px-0">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <small><?php echo e(__('Start Date')); ?>:</small>
                                        <div class="h6 mb-0">
                                            <?php echo e($startRaw && method_exists($user, 'dateFormat') ? ($user?->dateFormat($startRaw) ?? __('Failed to format date')) : __('No start date available')); ?>

                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <small><?php echo e(__('End Date')); ?>:</small>
                                        <div class="h6 mb-0">
                                            <?php echo e($endRaw && method_exists($user, 'dateFormat') ? ($user?->dateFormat($endRaw) ?? __('Failed to format date')) : __('No end date available')); ?>

                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center text-muted">
                        <?php echo e(__('No contracts available')); ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/contracts/grid.blade.php ENDPATH**/ ?>