<?php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        PlansConstants as PL,
        StacksConstants as ST,
        UsersConstants as UC,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YD
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Collection;

    $user = Auth::user();
    $lang = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();
    $canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);

    $dashUrl   = Route::has('dashboard') ? route('dashboard') : '#';
    $dashGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') : 'Dashboard route is unavailable. Please contact technical support or your domain administrator.') ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

    $items = [];
    if (is_array($plan_requests ?? null) && count($plan_requests)) {
        $items = $plan_requests;
    } elseif (($plan_requests ?? null) instanceof Collection && $plan_requests->isNotEmpty()) {
        $items = $plan_requests;
    }
?>


<?php $__env->startSection(YD::ADM_PG_TTL); ?>
    <?php echo e(__('Plan-Request')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection(YD::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e($dashUrl); ?>"
           data-url="<?php echo e($dashUrl); ?>"
           data-sv-localized="true"
           data-guard-msg="<?php echo e($dashGuard); ?>"
           <?php echo e($dashUrl !== '#' ? '' : 'aria-disabled=true'); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item"><?php echo e(__('Plan Request')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('title'); ?>
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0"><?php echo e(__('Plan Request')); ?></h5>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YD::ADM_CTT); ?>
    <div class="<?php echo e(VC::RW); ?>">
        <div class="<?php echo e(VC::C12); ?>">
            <div class="<?php echo e(VC::CD); ?>">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="<?php echo e(VC::TB); ?> header datatable" width="100%">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Name')); ?></th>
                                    <th><?php echo e(__('Plan Name')); ?></th>
                                    <th><?php echo e(__('Total Users')); ?></th>
                                    <th><?php echo e(__('Total Customers')); ?></th>
                                    <th><?php echo e(__('Total Vendors')); ?></th>
                                    <th><?php echo e(__('Total Clients')); ?></th>
                                    <th><?php echo e(__('Duration')); ?></th>
                                    <th><?php echo e(__('Date')); ?></th>
                                    <th><?php echo e(__('Action')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prequest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                        $uidName   = data_get($prequest, 'user.'.UC::COL_NM, '-');
                                        $planName  = data_get($prequest, 'plan.'.PL::COL_NM, __('No name available'));
                                        $maxUsers  = data_get($prequest, 'plan.'.PL::COL_MAX_U, __('Failed to get the number of max users'));
                                        $maxCust   = data_get($prequest, 'plan.'.PL::COL_MAX_CR, __('Failed to get the number of max customers'));
                                        $maxVend   = data_get($prequest, 'plan.'.PL::COL_MAX_V, __('Failed to get the number of max vendors'));
                                        $maxClient = data_get($prequest, 'plan.'.PL::COL_MAX_CL, __('Failed to get the number of max clients'));

                                        $durRaw    = data_get($prequest, PL::COL_DUR, null);
                                        $duration  = $durRaw === 'year' ? __('Yearly') : ($durRaw === 'month' ? __('Monthly') : __('Lifetime'));
                                        $dateStr   = \App\Models\Utility::getDateFormated($prequest->created_at, true);

                                        $approveUrl = Route::has(VW::PLN_RQ.'.request.response') ? route(VW::PLN_RQ.'.request.response', [$prequest->id, 1]) : '#';
                                        $rejectUrl  = Route::has(VW::PLN_RQ.'.request.response') ? route(VW::PLN_RQ.'.request.response', [$prequest->id, 0]) : '#';

                                        $approveGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PLN_RQ, 'approve_plan_request_unavailable') : 'Approve Plan Request route is unavailable. Please contact technical support or your domain administrator.') ?? __('Approve Plan Request route is unavailable. Please contact technical support or your domain administrator.');
                                        $rejectGuard  = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PLN_RQ, 'reject_plan_request_unavailable')  : 'Reject Plan Request route is unavailable. Please contact technical support or your domain administrator.')  ?? __('Reject Plan Request route is unavailable. Please contact technical support or your domain administrator.');
                                    ?>
                                    <tr>
                                        <td><div class="font-style"><?php echo e($uidName); ?></div></td>
                                        <td><div class="font-style"><?php echo e($planName); ?></div></td>
                                        <td><?php echo e($maxUsers); ?></td>
                                        <td><?php echo e($maxCust); ?></td>
                                        <td><?php echo e($maxVend); ?></td>
                                        <td><?php echo e($maxClient); ?></td>
                                        <td><div class="font-style"><?php echo e($duration); ?></div></td>
                                        <td><?php echo e($dateStr); ?></td>
                                        <td>
                                            <div class="<?php echo e(VC::DFL_IL_VC); ?>">
                                                <a href="<?php echo e($approveUrl); ?>"
                                                   class="<?php echo e(VC::BT_SM); ?> btn-success <?php echo e(VC::MX3); ?> <?php echo e(VC::AL_IT_CT); ?>"
                                                   data-url="<?php echo e($approveUrl); ?>"
                                                   data-sv-localized="true"
                                                   data-guard-msg="<?php echo e($approveGuard); ?>"
                                                   data-bs-toggle="tooltip"
                                                   title="<?php echo e(__('Approve')); ?>">
                                                    <i class="ti ti-check <?php echo e(VC::TXT_WT); ?>"></i>
                                                </a>
                                                <a href="<?php echo e($rejectUrl); ?>"
                                                   class="<?php echo e(VC::BT_SM); ?> btn-danger <?php echo e(VC::MX3); ?> <?php echo e(VC::AL_IT_CT); ?>"
                                                   data-url="<?php echo e($rejectUrl); ?>"
                                                   data-sv-localized="true"
                                                   data-guard-msg="<?php echo e($rejectGuard); ?>"
                                                   data-bs-toggle="tooltip"
                                                   title="<?php echo e(__('Reject')); ?>">
                                                    <i class="ti ti-x <?php echo e(VC::TXT_WT); ?>"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted"><?php echo e(__('No Manually Plan Request Found.')); ?></td>
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

<?php $__env->startPush(ST::ADM_SCR_PG); ?>
    <script defer src="<?php echo e(asset('assets/js/routes/plans/requests/index.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make(EL::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/plan_requests/index.blade.php ENDPATH**/ ?>