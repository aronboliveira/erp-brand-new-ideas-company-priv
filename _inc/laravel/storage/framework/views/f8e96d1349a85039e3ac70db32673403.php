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
    use Illuminate\Support\Facades\{Auth, Gate, Route};
    use Illuminate\Support\Collection;

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
    <div class="<?php echo e(VC::FEND); ?>">
        <?php
            $gridRoute = VW::CTC . '.grid';
            $gridHref  = Route::has($gridRoute) ? route($gridRoute) : '#';
            $gridGuard = Utility::fetchLinkMessage($lang, VW::CTC, 'grid_route_unavailable')
                        ?? 'Grid view route is unavailable. Please contact technical support or your domain administrator.';
        ?>
        <a href="<?php echo e($gridHref); ?>"
           class="<?php echo e(VC::BT_SM_PM); ?>"
           data-sv-localized="true"
           data-guard-msg="<?php echo e($gridGuard); ?>"
           data-bs-toggle="tooltip"
           title="<?php echo e(__('Grid View')); ?>">
            <i class="ti ti-layout-grid"></i>
        </a>
        <?php if($user?->{UsersConstants::COL_TP} == PermissionsConstants::CPN): ?>
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
            <script defer>
                (function () {
                    try {
                        if (!window.svToastOrAlert) {
                            window.svToastOrAlert = function (msg) {
                                try {
                                    var ok = !!(window.bootstrap && window.bootstrap.Toast);
                                    if (!ok) { alert(msg); return; }
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

                        var gridA = document.querySelector('a[href="<?php echo e($gridHref); ?>"][data-guard-msg]');
                        if (gridA && "<?php echo e($gridHref); ?>" === "#") {
                            gridA.addEventListener('click', function (e) {
                                e.preventDefault();
                                window.svToastOrAlert(gridA.getAttribute('data-guard-msg'));
                            });
                        }
                        var createA = document.querySelector('a[data-url="<?php echo e($createHref); ?>"][data-guard-msg]');
                        if (createA && "<?php echo e($createHref); ?>" === "#") {
                            createA.addEventListener('click', function (e) {
                                e.preventDefault();
                                window.svToastOrAlert(createA.getAttribute('data-guard-msg'));
                            });
                        }
                    } catch (_) {}
                })();
            </script>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
    <div class="row">
        <div class="col-xl-12">
            <div class="<?php echo e(VC::CD); ?>">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="<?php echo e(VC::TB); ?> datatable">
                            <thead>
                            <tr>
                                <th scope="col"><?php echo e(__('#')); ?></th>
                                <th scope="col"><?php echo e(__('Subject')); ?></th>
                                <?php if(($user?->{UsersConstants::COL_TP} ?? '') !== PermissionsConstants::CL): ?>
                                    <th scope="col"><?php echo e(__('Client')); ?></th>
                                <?php endif; ?>
                                <th scope="col"><?php echo e(__('Project')); ?></th>
                                <th scope="col"><?php echo e(__('Contract Type')); ?></th>
                                <th scope="col"><?php echo e(__('Contract Value')); ?></th>
                                <th scope="col"><?php echo e(__('Start Date')); ?></th>
                                <th scope="col"><?php echo e(__('End Date')); ?></th>
                                <th scope="col"><?php echo e(__('Action')); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                                $rows = (($contracts ?? null) instanceof Collection || is_array($contracts ?? null)) ? $contracts : [];
                            ?>
                            <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contract): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $cid        = (string) data_get($contract,'id','');
                                    $subject    = (string) (data_get($contract,'subject') ?: __('No subject available'));
                                    $clientName = (string) (data_get($contract,'clients.name') ?: '-');
                                    $project    = (string) (data_get($contract,'projects.project_name') ?: '-');
                                    $typeName   = (string) (data_get($contract,'types.name') ?: __('No type available'));
                                    $valueRaw   = data_get($contract,'value');
                                    $startRaw   = data_get($contract,'start_date');
                                    $endRaw     = data_get($contract,'end_date');
                                    $status     = (string) (data_get($contract,'status') ?: '');
                                    $showRoute  = VW::CTC . '.show';
                                    $showHref   = Route::has($showRoute) && $cid !== '' ? route($showRoute, $cid) : '#';
                                    $showGuard  = Utility::fetchLinkMessage($lang, VW::CTC, 'show_route_unavailable')
                                                 ?? 'Show route is unavailable. Please contact technical support or your domain administrator.';
                                    $deleteGuard   = Utility::fetchLinkMessage($lang, VW::CTC, 'delete_route_unavailable')
                                                   ?? 'Delete route is unavailable. Please contact technical support or your domain administrator.';
                                    $confirmMsg    = __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?')
                                                    .'|'.
                                                    __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?');
                                ?>
                                <tr class="font-style" data-id="<?php echo e($cid); ?>">
                                    <td>
                                        <a href="<?php echo e($showHref); ?>"
                                           class="<?php echo e(VC::BT_OUTPM); ?>"
                                           data-sv-localized="true"
                                           data-guard-msg="<?php echo e($showGuard); ?>">
                                            <?php echo e($user?->contractNumberFormat($cid) ?? ('#'.$cid)); ?>

                                        </a>
                                    </td>
                                    <td><?php echo e($subject); ?></td>
                                    <?php if(($user?->{UsersConstants::COL_TP} ?? '') !== PermissionsConstants::CL): ?>
                                        <td><?php echo e($clientName); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo e($project); ?></td>
                                    <td><?php echo e($typeName); ?></td>
                                    <td>
                                        <?php echo e(is_numeric($valueRaw)
                                            ? ($user?->priceFormat($valueRaw) ?? __('Failed to format value'))
                                            : __('No value available')); ?>

                                    </td>
                                    <td><?php echo e($startRaw ? ($user?->dateFormat($startRaw) ?? __('Failed to format date')) : __('No start date available')); ?></td>
                                    <td><?php echo e($endRaw   ? ($user?->dateFormat($endRaw)   ?? __('Failed to format date')) : __('No end date available')); ?></td>
                                    <td class="action">
                                        <?php if((($user?->{UsersConstants::COL_TP} ?? '') === PermissionsConstants::CPN || ($user?->{UsersConstants::COL_TP} ?? '') === PermissionsConstants::SA) && $status === 'accept'): ?>
                                            <?php
                                                $copyHref   = '#';
                                                $copyGuard  = Utility::fetchLinkMessage($lang, VW::CTC, 'copy_route_unavailable')
                                                            ?? 'Copy route is unavailable. Please contact technical support or your domain administrator.';
                                                if((($user?->{UsersConstants::COL_TP} === PermissionsConstants::CPN || $user->{UsersConstants::COL_TP} === PermissionsConstants::SA) && $status === 'accept')) {
                                                    $copyRoute = VW::CTC . '.copy';
                                                    $copyHref  = (Route::has($copyRoute) && $cid !== '') ? route($copyRoute, $cid) : '#';
                                                }
                                            ?>
                                            <div class="<?php echo e(VC::ACT_BTN_PRIM); ?>">
                                                <a href="#"
                                                   data-size="lg"
                                                   data-url="<?php echo e($copyHref); ?>"
                                                   data-ajax-popup="true"
                                                   data-title="<?php echo e(__('Copy Contract')); ?>"
                                                   class="<?php echo e(VC::BT_SM_FL_CT); ?>"
                                                   data-sv-localized="true"
                                                   data-guard-msg="<?php echo e($copyGuard); ?>"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="<?php echo e(__('Duplicate')); ?>">
                                                    <i class="ti ti-copy <?php echo e(VC::TXT_WT); ?>"></i>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show contract')): ?>
                                            <div class="<?php echo e(VC::ACT_BTN_WRN); ?>">
                                                <a href="<?php echo e($showHref); ?>"
                                                   class="<?php echo e(VC::BT_SM_FL_CT); ?>"
                                                   data-sv-localized="true"
                                                   data-guard-msg="<?php echo e($showGuard); ?>"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-original-title="<?php echo e(__('View')); ?>">
                                                    <span class="<?php echo e(VC::TXT_WT); ?>"><i class="<?php echo e(VC::TI_EYE); ?>"></i></span>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit contract')): ?>
                                            <?php
                                                $editHref   = '#';
                                                $editGuard  = Utility::fetchLinkMessage($lang, VW::CTC, 'edit_route_unavailable')
                                                            ?? 'Edit route is unavailable. Please contact technical support or your domain administrator.';
                                                if(Gate::check('edit contract')) {
                                                    $editRoute = VW::CTC . '.edit';
                                                    $editHref  = (Route::has($editRoute) && $cid !== '') ? route($editRoute, $cid) : '#';
                                                }
                                            ?>
                                            <div class="<?php echo e(VC::ACT_BTN_INF); ?>">
                                                <a href="#"
                                                   class="<?php echo e(VC::BT_SM_FL_CT); ?>"
                                                   data-url="<?php echo e($editHref); ?>"
                                                   data-ajax-popup="true"
                                                   data-size="md"
                                                   data-sv-localized="true"
                                                   data-guard-msg="<?php echo e($editGuard); ?>"
                                                   data-bs-toggle="tooltip"
                                                   title="<?php echo e(__('Edit')); ?>"
                                                   data-title="<?php echo e(__('Edit Contract')); ?>">
                                                    <i class="<?php echo e(VC::TI_PC_WT); ?>"></i>
                                                </a>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete contract')): ?>
                                            <div class="<?php echo e(VC::ACT_BTN_DNG_2); ?>">
                                                <?php
                                                    $destroyFormId = 'delete-form-' . $cid;
                                                    $openParams = ['method' => 'DELETE', 'id' => $destroyFormId, 'data-sv-localized'=>'true', 'data-guard-msg'=>$deleteGuard];
                                                    $destroyRoute = VW::CTC . '.destroy';
                                                    if (Route::has($destroyRoute) && $cid !== '') {
                                                        $openParams['route'] = [$destroyRoute, $cid];
                                                    } else {
                                                        $openParams['url'] = '#';
                                                    }
                                                ?>
                                                <?php echo Form::open($openParams); ?>

                                                    <a href="#"
                                                       class="<?php echo e(VC::BT_SM_CT_PR); ?>"
                                                       data-delete-for="<?php echo e($cid); ?>"
                                                       data-sv-localized="true"
                                                       data-guard-msg="<?php echo e($deleteGuard); ?>"
                                                       data-bs-toggle="tooltip"
                                                       title="<?php echo e(__('Delete')); ?>"
                                                       data-confirm="<?php echo e($confirmMsg); ?>"
                                                       data-confirm-yes="document.getElementById('<?php echo e($destroyFormId); ?>').submit();">
                                                        <i class="<?php echo e(VC::TI_TRS_WT); ?>"></i>
                                                    </a>
                                                <?php echo Form::close(); ?>

                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                    <script defer>
                                        (function () {
                                            try {
                                                if (!window.svToastOrAlert) {
                                                    window.svToastOrAlert = function (msg) {
                                                        try {
                                                            var ok = !!(window.bootstrap && window.bootstrap.Toast);
                                                            if (!ok) { alert(msg); return; }
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

                                                var row = document.querySelector('tr[data-id="<?php echo e($cid); ?>"]');

                                                // show links (both number link and action view)
                                                var showLinks = row ? row.querySelectorAll('a[data-guard-msg][href="<?php echo e($showHref); ?>"]') : [];
                                                if (showLinks && "<?php echo e($showHref); ?>" === "#") {
                                                    for (var i=0;i<showLinks.length;i++){
                                                        (function(a){
                                                            a.addEventListener('click', function (e) {
                                                                e.preventDefault();
                                                                window.svToastOrAlert(a.getAttribute('data-guard-msg'));
                                                            });
                                                        })(showLinks[i]);
                                                    }
                                                }

                                                // copy button
                                                var copyA = row ? row.querySelector('a[data-url="<?php echo e($copyHref); ?>"][data-guard-msg]') : null;
                                                if (copyA && "<?php echo e($copyHref); ?>" === "#") {
                                                    copyA.addEventListener('click', function (e) {
                                                        e.preventDefault();
                                                        window.svToastOrAlert(copyA.getAttribute('data-guard-msg'));
                                                    });
                                                }

                                                // edit button
                                                var editA = row ? row.querySelector('a[data-url="<?php echo e($editHref); ?>"][data-guard-msg]') : null;
                                                if (editA && "<?php echo e($editHref); ?>" === "#") {
                                                    editA.addEventListener('click', function (e) {
                                                        e.preventDefault();
                                                        window.svToastOrAlert(editA.getAttribute('data-guard-msg'));
                                                    });
                                                }

                                                // delete button/form
                                                var delForm = document.getElementById('<?php echo e($destroyFormId); ?>');
                                                if (delForm) {
                                                    var hasAction = (delForm.getAttribute('action') || '').trim() !== '';
                                                    var actionIsHash = (delForm.getAttribute('action') || '#') === '#';
                                                    if (!hasAction || actionIsHash) {
                                                        var delA = row ? row.querySelector('a[data-delete-for="<?php echo e($cid); ?>"][data-guard-msg]') : null;
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
                                <?php $__env->stopPush(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted"><?php echo e(__('No contracts available')); ?></td>
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

<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/contracts/index.blade.php ENDPATH**/ ?>