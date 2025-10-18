<?php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        LangsConstants,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YD,
        PermissionsConstants as PERM,
        UsersConstants as UC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Collection;

    $user = Auth::user();
    $hasFetchMsg = is_callable([Utility::class, 'fetchLinkMessage']);
    $hasGetFile = is_callable([Utility::class, 'getFile']);
    $hasGetAdminPay = is_callable([Utility::class, 'getAdminPaymentSetting']);
    $hasUserDate = $user && method_exists($user, 'dateFormat');
    $hasUserPrice = $user && method_exists($user, 'priceFormat');
    $lang = is_callable([Utility::class, 'fetchUserLang']) ? Utility::fetchUserLang(user: $user) : app()->getLocale();

    $dashUrl = Route::has('dashboard') ? route('dashboard') : '#';
    $dashGuard = ($hasFetchMsg ? Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') : null) ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

    $admin_payment_setting = $hasGetAdminPay ? Utility::getAdminPaymentSetting() : [];
    $currencySymbol = $admin_payment_setting['currency_symbol'] ?? '$';

    $isSuperAdmin = strtolower((string)($user->{UC::COL_TP} ?? '')) === strtolower((string) PERM::SA);

    $ordersList = [];
    if (is_array($orders ?? null) && count($orders ?? []) > 0) {
        $ordersList = $orders;
    } elseif (($orders ?? null) instanceof Collection && $orders->isNotEmpty()) {
        $ordersList = $orders;
    }

    $fmtDate = function ($v, $fb) use ($hasUserDate, $user) {
        if ($v && $hasUserDate) {
            try { return $user->dateFormat($v) ?? $fb; } catch (\Throwable $e) { return $fb; }
        }
        if ($v instanceof \Carbon\Carbon) {
            try { return $v->format('d M Y'); } catch (\Throwable $e) { return $fb; }
        }
        return $fb;
    };
    $fmtPrice = function ($v) use ($hasUserPrice, $user, $currencySymbol) {
        if ($hasUserPrice) {
            try { return $user->priceFormat($v) ?? __('Failed to format amount.'); } catch (\Throwable $e) { return __('Failed to format amount.'); }
        }
        if (is_numeric($v)) return $currencySymbol . number_format((float)$v, 2);
        return __('Amount not available.');
    };

    $filesBase = $hasGetFile ? Utility::getFile('uploads/order') : 'uploads/order';
?>



<?php $__env->startSection(YD::ADM_PG_TTL); ?>
    <?php echo e(__('Orders')); ?>

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
    <li class="breadcrumb-item"><?php echo e(__('Order')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YD::ADM_CTT); ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th><?php echo e(__('Order Id')); ?></th>
                                <th><?php echo e(__('Name')); ?></th>
                                <th><?php echo e(__('Plan Name')); ?></th>
                                <th><?php echo e(__('Price')); ?></th>
                                <th><?php echo e(__('Status')); ?></th>
                                <th><?php echo e(__('Payment Type')); ?></th>
                                <th><?php echo e(__('Date')); ?></th>
                                <th><?php echo e(__('Coupon')); ?></th>
                                <th><?php echo e(__('Invoice')); ?></th>
                                <?php if($isSuperAdmin): ?>
                                    <th><?php echo e(__('Action')); ?></th>
                                <?php endif; ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $ordersList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $oid = $order->order_id ?? __('Order ID not available.');
                                    $uname = $order->user_name ?? __('Customer name not available.');
                                    $pname = $order->plan_name ?? __('Plan name not available.');
                                    $price = $fmtPrice($order->price ?? null);
                                    $pstatus = (string)($order->payment_status ?? '');
                                    $pstatusLower = strtolower($pstatus);
                                    $ptype = (string)($order->payment_type ?? '');
                                    $ptypeLower = strtolower($ptype);
                                    $created = $fmtDate($order->created_at ?? null, __('Failed to format date.'));
                                    $couponCode = data_get($order, 'totalCouponUsed.couponDetail.code') ?? '-';

                                    $statusBadge = ['class' => 'bg-danger', 'text' => ucfirst($pstatusLower ?: __('unknown'))];
                                    if (in_array($pstatusLower, ['success', 'approved'])) $statusBadge = ['class' => 'bg-primary', 'text' => ucfirst($pstatusLower)];
                                    elseif ($pstatusLower === 'succeeded') $statusBadge = ['class' => 'bg-primary', 'text' => __('Success')];
                                    elseif ($pstatusLower === 'pending') $statusBadge = ['class' => 'bg-warning', 'text' => __('Pending')];
                                ?>
                                <tr>
                                    <td><?php echo e($oid); ?></td>
                                    <td><?php echo e($uname); ?></td>
                                    <td><?php echo e($pname); ?></td>
                                    <td><?php echo e($price); ?></td>
                                    <td>
                                        <span class="status_badge badge <?php echo e($statusBadge['class']); ?> p-2 px-3 rounded"><?php echo e($statusBadge['text']); ?></span>
                                    </td>
                                    <td><?php echo e($ptype !== '' ? $ptype : __('Payment type not available.')); ?></td>
                                    <td><?php echo e($created); ?></td>
                                    <td class="text-center"><?php echo e($couponCode); ?></td>
                                    <td class="Id">
                                        <?php
                                            $receipt = (string)($order->receipt ?? '');
                                        ?>
                                        <?php if($ptypeLower === 'manually'): ?>
                                            <p><?php echo e(__('Manually plan upgraded by Super Admin')); ?></p>
                                        <?php elseif($receipt !== '' && strtolower($receipt) === 'free coupon'): ?>
                                            <p><?php echo e(__('Used 100 % discount coupon code.')); ?></p>
                                        <?php elseif($ptypeLower === 'stripe' && $receipt !== ''): ?>
                                            <a href="<?php echo e($receipt); ?>" target="_blank">
                                                <i class="ti ti-file-invoice"></i> <?php echo e(__('Receipt')); ?>

                                            </a>
                                        <?php elseif($ptypeLower === 'bank transfer' && $receipt !== ''): ?>
                                            <a href="<?php echo e(rtrim($filesBase, '/').'/'.$receipt); ?>" target="_blank">
                                                <i class="ti ti-file-invoice"></i> <?php echo e(__('Receipt')); ?>

                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <?php if($isSuperAdmin): ?>
                                        <td class="Action">
                                            <?php
                                                $showAction = ($ptypeLower === 'bank transfer' && $pstatusLower === 'pending');
                                            ?>
                                            <?php if($showAction): ?>
                                                <?php
                                                    $actionUrl = Route::has(VW::OD.'.action') ? route(VW::OD.'.action', $order->id) : '#';
                                                    $actionGuard = ($hasFetchMsg ? Utility::fetchLinkMessage($lang, VW::OD, 'payment_status_unavailable') : null) ?? __('Ordering of payment status route is unavailable. Please contact technical support or your domain administrator.');
                                                ?>
                                                <span>
                                                    <div class="action-btn bg-warning">
                                                        <a
                                                            href="<?php echo e($actionUrl); ?>"
                                                            class="<?php echo e(VC::BT_SM_CT); ?>"
                                                            data-url="<?php echo e($actionUrl); ?>"
                                                            data-sv-localized="true"
                                                            data-guard-msg="<?php echo e($actionGuard); ?>"
                                                            data-size="lg"
                                                            data-ajax-popup="true"
                                                            data-title="<?php echo e(__('Payment Status')); ?>"
                                                            data-bs-toggle="tooltip"
                                                            title="<?php echo e(__('Payment Status')); ?>">
                                                            <i class="<?php echo e(VC::TI_CRT_WT); ?>"></i>
                                                        </a>
                                                    </div>
                                                </span>
                                            <?php endif; ?>
                                            <?php
                                                $deleteUrl = Route::has(VW::OD.'.destroy') ? route(VW::OD.'.destroy', $order->id) : '#';
                                                $deleteGuard = ($hasFetchMsg ? Utility::fetchLinkMessage($lang, VW::OD, 'delete_order_unavailable') : 'Delete order route is unavailable. Please contact technical support or your domain administrator.') ?? __('Delete order route is unavailable. Please contact technical support or your domain administrator.');
                                                $formId = 'delete-order-form-'.$order->id;
                                            ?>
                                            <span>
                                                <div class="<?php echo e(VC::ACT_BTN_DNG_2); ?>">
                                                    <?php echo Form::open([
                                                        'method' => 'DELETE',
                                                        'url'    => $deleteUrl,
                                                        'id'     => $formId,
                                                        'data-url' => $deleteUrl,
                                                        'data-sv-localized' => 'true',
                                                        'data-guard-msg' => $deleteGuard
                                                    ]); ?>

                                                        <a
                                                            href="<?php echo e($deleteUrl); ?>"
                                                            class="<?php echo e(VC::TRS_PARA); ?>"
                                                            data-url="<?php echo e($deleteUrl); ?>"
                                                            data-sv-localized="true"
                                                            data-guard-msg="<?php echo e($deleteGuard); ?>"
                                                            data-bs-toggle="tooltip"
                                                            title="<?php echo e(__('Delete')); ?>"
                                                            data-confirm="<?php echo e(__(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?')); ?>|<?php echo e(__(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?')); ?>"
                                                            data-confirm-yes="document.getElementById('<?php echo e($formId); ?>').submit();">
                                                            <i class="<?php echo e(VC::TI_TRS_WT); ?>"></i>
                                                        </a>
                                                    <?php echo Form::close(); ?>

                                                </div>
                                            </span>
                                        </td>
                                        <?php $__env->startPush(ST::ADM_SCR_PG); ?>
                                            <script defer src="<?php echo e(asset('assets/js/routes/orders/action.js')); ?>"></script>
                                            <script defer src="<?php echo e(asset('assets/js/routes/orders/delete.js')); ?>"></script>
                                        <?php $__env->stopPush(); ?>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="<?php echo e($isSuperAdmin ? 10 : 9); ?>" class="text-center text-muted"><?php echo e(__('No orders found.')); ?></td>
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

<?php echo $__env->make(EL::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/orders/index.blade.php ENDPATH**/ ?>