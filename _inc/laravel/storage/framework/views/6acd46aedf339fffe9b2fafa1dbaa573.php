<?php

use App\Config\Constants\{
    ExtendingLayoutsConstants,
    StacksConstants,
    YieldingConstants,
    ViewsConstants,
    ViewClassNamesConstants as VC
};
use App\Models\Utility;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

$lang = Utility::fetchUserLang();
?>



<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
<?php echo e(__('Manage Product Stock')); ?>

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
<li class="breadcrumb-item"><?php echo e(__('Product Stock')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_ACT_BTN); ?>
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
                                <th><?php echo e(__('Name')); ?></th>
                                <th><?php echo e(__('Sku')); ?></th>
                                <th><?php echo e(__('Current Quantity')); ?></th>
                                <th><?php echo e(__('Action')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (Utility::isFilled($productServices) ?? []): ?>
                                <?php $__currentLoopData = $productServices;
                                $__env->addLoop($__currentLoopData);
                                foreach ($__currentLoopData as $productService): $__env->incrementLoopIndices();
                                    $loop = $__env->getLastLoop(); ?>
                                    <tr class="font-style">
                                        <td><?php echo e(!empty($productService->name) ? $productService->name : __('No name available')); ?></td>
                                        <td><?php echo e(!empty($productService->sku) ? $productService->sku : __('No SKU available')); ?></td>
                                        <td><?php echo e(!empty($productService->quantity) ? $productService->quantity : __('No quantity available')); ?></td>
                                        <td class="Action">
                                            <div class="<?php echo e(VC::ACT_BTN_INF); ?>">
                                                <?php
                                                $productStockEditRouteNameBase    = ViewsConstants::PRD_STK . '.edit';
                                                $productStockEditKebabName        = Str::kebab($productStockEditRouteNameBase);
                                                $productStockEditResolvedName     = Route::has($productStockEditRouteNameBase)
                                                    ? $productStockEditRouteNameBase
                                                    : (Route::has($productStockEditKebabName) ? $productStockEditKebabName : null);
                                                $productStockEditUrl              = $productStockEditResolvedName
                                                    ? route($productStockEditResolvedName, $productService->id)
                                                    : '#';
                                                $productStockEditGuardMsg         = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::PRD_STK,
                                                    'product_stock_edit_route_unavailable'
                                                ) ?? 'Product stock edit route is unavailable. Please contact technical support or your domain administrator.';
                                                $productStockEditBtnId            = 'product-stock-edit-btn-' . $productService->id;
                                                ?>
                                                <a
                                                    id="<?php echo e($productStockEditBtnId); ?>"
                                                    href="<?php echo e($productStockEditUrl); ?>"
                                                    data-url="<?php echo e($productStockEditUrl); ?>"
                                                    data-guard-msg="<?php echo e($productStockEditGuardMsg); ?>"
                                                    data-size="md"
                                                    class="<?php echo e(VC::BT_SM_FL_CT); ?>"
                                                    data-ajax-popup="true"
                                                    data-size="xl"
                                                    data-bs-toggle="tooltip"
                                                    title="<?php echo e(__('Update Quantity')); ?>">
                                                    <i class="<?php echo e(VC::TI_PLS); ?> <?php echo e(VC::TXT_WT); ?>"></i>
                                                </a>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                <script defer>
                                                    (() => {
                                                        const btn = document.getElementById('<?php echo e($productStockEditBtnId); ?>');
                                                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                        btn.setAttribute('data-listener-active', 'true');
                                                        btn.addEventListener('click', e => {
                                                            try {
                                                                const url = btn.getAttribute('data-url') ?? '#';
                                                                if (url !== '#') return;
                                                                e.preventDefault();
                                                                const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                const hasBootstrap = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
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
                                                                btn.setAttribute('data-failed-route', 'true');
                                                            } catch (err) {}
                                                        });
                                                    })();
                                                </script>
                                                <?php $__env->stopPush(); ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach;
                                $__env->popLoop();
                                $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2"><?php echo e(__('No Product services found')); ?></td>
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

<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/product_stocks/index.blade.php ENDPATH**/ ?>