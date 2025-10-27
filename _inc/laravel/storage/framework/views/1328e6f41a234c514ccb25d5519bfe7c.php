<?php

use App\Config\Constants\{
    ExtendingLayoutsConstants as EL,
    StacksConstants as ST,
    ViewsConstants as VW,
    ViewClassNamesConstants as VC,
    YieldingConstants as YD
};
use App\Models\Utility;
use Collective\Html\FormFacade as Form;
use Illuminate\Support\Facades\{Auth, Route};
use Illuminate\Support\{Collection, Str};

$user        = Auth::user();
$lang        = is_callable([Utility::class, 'fetchUserLang']) ? Utility::fetchUserLang(user: $user) : app()->getLocale();
$canFetchMsg = is_callable([Utility::class, 'fetchLinkMessage']);
$canPriceFormat = is_callable([$user, 'priceFormat']);

$dashUrl   = Route::has('dashboard') ? route('dashboard') : '#';
$dashGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') : 'Dashboard route is unavailable. Please contact technical support or your domain administrator.') ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

$importUrl   = Route::has(VW::PRD_SV . '.file.import') ? route(VW::PRD_SV . '.file.import') : '#';
$importGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRD_SV, 'import_product_services_unavailable') : 'Import Product & Services route is unavailable. Please contact technical support or your domain administrator.') ?? __('Import Product & Services route is unavailable. Please contact technical support or your domain administrator.');

$exportUrl   = Route::has(VW::PRD_SV . '.export') ? route(VW::PRD_SV . '.export') : '#';
$exportGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRD_SV, 'export_product_services_unavailable') : 'Export Product & Services route is unavailable. Please contact technical support or your domain administrator.') ?? __('Export Product & Services route is unavailable. Please contact technical support or your domain administrator.');

$createUrl   = Route::has(VW::PRD_SV . '.create') ? route(VW::PRD_SV . '.create') : '#';
$createGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRD_SV, 'create_product_service_unavailable') : 'Create Product Service route is unavailable. Please contact technical support or your domain administrator.') ?? __('Create Product Service route is unavailable. Please contact technical support or your domain administrator.');

$items = [];
if (is_array($productServices ?? null) && count($productServices)) {
    $items = $productServices;
} elseif (($productServices ?? null) instanceof Collection && $productServices->isNotEmpty()) {
    $items = $productServices;
}
?>



<?php $__env->startSection(YD::ADM_PG_TTL); ?>
<?php echo e(__('Manage Product & Services')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush(ST::ADM_SCR_PG); ?>
<?php $__env->stopPush(); ?>

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
<li class="breadcrumb-item"><?php echo e(__('Product & Services')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YD::ADM_ACT_BTN); ?>
<div class="<?php echo e(VC::FEND); ?>">
    <a href="<?php echo e($importUrl); ?>"
        data-size="md"
        data-bs-toggle="tooltip"
        title="<?php echo e(__('Import')); ?>"
        data-url="<?php echo e($importUrl); ?>"
        data-ajax-popup="true"
        data-title="<?php echo e(__('Import product CSV file')); ?>"
        data-sv-localized="true"
        data-guard-msg="<?php echo e($importGuard); ?>"
        class="<?php echo e(VC::BT_SM_PM); ?>">
        <i class="<?php echo e(VC::TI_IMP); ?>"></i>
    </a>

    <a href="<?php echo e($exportUrl); ?>"
        data-bs-toggle="tooltip"
        title="<?php echo e(__('Export')); ?>"
        data-url="<?php echo e($exportUrl); ?>"
        data-sv-localized="true"
        data-guard-msg="<?php echo e($exportGuard); ?>"
        class="<?php echo e(VC::BT_SM_PM); ?>">
        <i class="<?php echo e(VC::TI_EXP); ?>"></i>
    </a>

    <a href="<?php echo e($createUrl); ?>"
        data-size="lg"
        data-url="<?php echo e($createUrl); ?>"
        data-ajax-popup="true"
        data-bs-toggle="tooltip"
        title="<?php echo e(__('Create New Product')); ?>"
        data-title="<?php echo e(__('Create New Product')); ?>"
        data-sv-localized="true"
        data-guard-msg="<?php echo e($createGuard); ?>"
        class="<?php echo e(VC::BT_SM_PM); ?>">
        <i class="<?php echo e(VC::TI_PLS); ?>"></i>
    </a>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YD::ADM_CTT); ?>
<div class="<?php echo e(VC::RW); ?>">
    <div class="<?php echo e(VC::C12); ?>">
        <div class="<?php echo e(VC::CD); ?>">
            <div class="card-body">
                <?php
                $psIndexBase             = VW::PRD_SV . '.index';
                $psIndexKebab            = Str::kebab($psIndexBase);
                $psIndexResolved         = Route::has($psIndexBase) ? $psIndexBase : (Route::has($psIndexKebab) ? $psIndexKebab : null);
                $psIndexUrl              = $psIndexResolved ? route($psIndexResolved) : '#';
                $psFormId                = 'product-service-filter-form';
                $psIndexGuardMsg         = Utility::fetchLinkMessage($lang, VW::PRD_SV, 'product_services_index_route_unavailable') ?? 'Product & Service index route is unavailable. Please contact technical support or your domain administrator.';
                $categoryIsList          = (is_array($category ?? null) && count($category ?? []) > 0) || (($category ?? null) instanceof Collection && $category->isNotEmpty());
                $categoryOptions         = $categoryIsList ? (is_array($category) ? $category : $category->toArray()) : [];
                $selectedCategory        = request('category');
                $applyBtnId              = 'product-service-apply-btn';
                $resetLinkId             = 'product-service-reset-link';
                ?>
                <?php echo e(Form::open([
                    'url'               => $psIndexUrl,
                    'method'            => 'GET',
                    'id'                => $psFormId,
                    'data-url'          => $psIndexUrl,
                    'data-guard-msg'    => $psIndexGuardMsg,
                    'data-sv-localized' => 'true',
                ])); ?>

                <div class="<?php echo e(VC::R_FLX_ALC_JCE); ?>">
                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                        <div class="btn-box">
                            <?php echo e(Form::label('category', __('Category'), ['class' => VC::FM_LB])); ?>

                            <?php echo e(Form::select('category', $categoryOptions, $selectedCategory, array_merge([
                                'class'     => VC::FM_CT_SL,
                                'id'        => 'choices-multiple',
                                'required'  => 'required',
                                'placeholder' => __('Select Category'),
                            ], $categoryIsList ? [] : ['disabled' => 'disabled']))); ?>

                        </div>
                    </div>
                    <div class="<?php echo e(VC::C_AT_FEND); ?>">
                        <a
                            id="<?php echo e($applyBtnId); ?>"
                            href="<?php echo e($psIndexUrl); ?>"
                            data-url="<?php echo e($psIndexUrl); ?>"
                            data-guard-msg="<?php echo e($psIndexGuardMsg); ?>"
                            data-sv-localized="true"
                            class="<?php echo e(VC::BT_SM_PM); ?>"
                            data-bs-toggle="tooltip"
                            title="<?php echo e(__('apply')); ?>">
                            <span class="btn-inner--icon"><i class="<?php echo e(VC::TI_SRC); ?>"></i></span>
                        </a>
                        <a
                            id="<?php echo e($resetLinkId); ?>"
                            href="<?php echo e($psIndexUrl); ?>"
                            data-url="<?php echo e($psIndexUrl); ?>"
                            data-guard-msg="<?php echo e($psIndexGuardMsg); ?>"
                            data-sv-localized="true"
                            class="<?php echo e(VC::BT_SM_DG); ?>"
                            data-bs-toggle="tooltip"
                            title="<?php echo e(__('Reset')); ?>">
                            <span class="btn-inner--icon"><i class="<?php echo e(VC::TI_TRS_OFF); ?>"></i></span>
                        </a>
                    </div>
                </div>
                <script defer src="<?php echo e(asset('assets/js/routes/products/services/index.js')); ?>"></script>
                <?php echo e(Form::close()); ?>

            </div>
        </div>
    </div>
</div>

<div class="<?php echo e(VC::RW); ?>">
    <div class="<?php echo e(VC::C12); ?>">
        <div class="<?php echo e(VC::CD); ?>">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="<?php echo e(VC::TB); ?> datatable">
                        <thead>
                            <tr>
                                <th><?php echo e(__('Name')); ?></th>
                                <th><?php echo e(__('Sku')); ?></th>
                                <th><?php echo e(__('Sale Price')); ?></th>
                                <th><?php echo e(__('Purchase Price')); ?></th>
                                <th><?php echo e(__('Tax')); ?></th>
                                <th><?php echo e(__('Category')); ?></th>
                                <th><?php echo e(__('Unit')); ?></th>
                                <th><?php echo e(__('Quantity')); ?></th>
                                <th><?php echo e(__('Type')); ?></th>
                                <th><?php echo e(__('Action')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true;
                            $__currentLoopData = $items;
                            $__env->addLoop($__currentLoopData);
                            foreach ($__currentLoopData as $productService): $__env->incrementLoopIndices();
                                $loop = $__env->getLastLoop();
                                $__empty_1 = false; ?>
                                <?php
                                $psId      = data_get($productService, 'id');
                                $name      = data_get($productService, 'name', __('(no name)'));
                                $sku       = data_get($productService, 'sku', __('(no sku)'));
                                $sale      = data_get($productService, 'sale_price');
                                $purchase  = data_get($productService, 'purchase_price');
                                $typeRaw   = strtolower((string) data_get($productService, 'type', ''));
                                $typeTxt   = $typeRaw ? ucwords($typeRaw) : __('Unknown');
                                $categoryN = data_get($productService, 'category.name', __('(no category)'));
                                $unitN     = data_get($productService, 'unit.name', __('(no unit)'));
                                $qty       = $typeRaw === 'product' ? (data_get($productService, 'quantity') ?? 0) : '-';
                                $detailUrl   = Route::has(VW::PRD_SV . '.detail') ? route(VW::PRD_SV . '.detail', $psId) : '#';
                                $detailGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRD_SV, 'warehouse_details_unavailable') : 'Warehouse Details route is unavailable. Please contact technical support or your domain administrator.') ?? __('Warehouse Details route is unavailable. Please contact technical support or your domain administrator.');
                                $taxHtml = '-';
                                $taxId   = data_get($productService, 'tax_id');
                                if (!empty($taxId)) {
                                    $taxes = Utility::tax($taxId);
                                    $titems = [];
                                    if (Utility::isFilled($taxes) ?? [])
                                        $titems = $taxes;
                                    if (!empty($titems)) {
                                        $parts = [];
                                        foreach ($titems as $tx) {
                                            $tn = data_get($tx, 'name');
                                            $tr = data_get($tx, 'rate');
                                            if ($tn !== null && $tr !== null) {
                                                $parts[] = e($tn) . ' (' . e($tr) . '%)';
                                            }
                                        }
                                        $taxHtml = !empty($parts) ? implode('<br>', $parts) : '-';
                                    }
                                }
                                ?>
                                <tr class="font-style">
                                    <td><?php echo e($name); ?></td>
                                    <td><?php echo e($sku); ?></td>
                                    <td><?php echo e($canPriceFormat ? $user?->priceFormat($sale) : __('Failed to format sale price')); ?></td>
                                    <td><?php echo e($canPriceFormat ? $user?->priceFormat($purchase) : __('Failed to format purchase price')); ?></td>
                                    <td><?php echo $taxHtml; ?></td>
                                    <td><?php echo e($categoryN); ?></td>
                                    <td><?php echo e($unitN); ?></td>
                                    <td><?php echo e($qty); ?></td>
                                    <td><?php echo e($typeTxt); ?></td>
                                    <td class="Action">
                                        <div class="<?php echo e(VC::ACT_BTN_WRN); ?>">
                                            <a href="<?php echo e($detailUrl); ?>"
                                                class="<?php echo e(VC::BT_SM_CT); ?>"
                                                data-url="<?php echo e($detailUrl); ?>"
                                                data-ajax-popup="true"
                                                data-bs-toggle="tooltip"
                                                title="<?php echo e(__('Warehouse Details')); ?>"
                                                data-title="<?php echo e(__('Warehouse Details')); ?>"
                                                data-sv-localized="true"
                                                data-guard-msg="<?php echo e($detailGuard); ?>">
                                                <i class="<?php echo e(VC::TI_EYE_WT); ?>"></i>
                                            </a>
                                        </div>

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit product & service')): ?>
                                            <?php
                                            $editUrl   = Route::has(VW::PRD_SV . '.edit') ? route(VW::PRD_SV . '.edit', $psId) : '#';
                                            $editGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRD_SV, 'edit_product_service_unavailable') : 'Edit Product Service route is unavailable. Please contact technical support or your domain administrator.') ?? __('Edit Product Service route is unavailable. Please contact technical support or your domain administrator.');
                                            ?>
                                            <div class="<?php echo e(VC::ACT_BTN_INF); ?>">
                                                <a href="<?php echo e($editUrl); ?>"
                                                    class="<?php echo e(VC::BT_SM_CT); ?>"
                                                    data-url="<?php echo e($editUrl); ?>"
                                                    data-ajax-popup="true"
                                                    data-size="lg"
                                                    data-bs-toggle="tooltip"
                                                    title="<?php echo e(__('Edit')); ?>"
                                                    data-title="<?php echo e(__('Edit Product')); ?>"
                                                    data-sv-localized="true"
                                                    data-guard-msg="<?php echo e($editGuard); ?>">
                                                    <i class="<?php echo e(VC::TI_PC_WT); ?>"></i>
                                                </a>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete product & service')): ?>
                                            <?php
                                            $delUrl    = Route::has(VW::PRD_SV . '.destroy') ? route(VW::PRD_SV . '.destroy', $psId) : '#';
                                            $delGuard  = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::PRD_SV, 'delete_product_service_unavailable') : 'Delete Product Service route is unavailable. Please contact technical support or your domain administrator.') ?? __('Delete Product Service route is unavailable. Please contact technical support or your domain administrator.');
                                            $formId    = 'delete-form-' . $psId;
                                            ?>
                                            <div class="<?php echo e(VC::ACT_BTN_DNG_2); ?>">
                                                <?php echo Form::open(['method' => 'DELETE', 'url' => $delUrl, 'id' => $formId, 'data-url' => $delUrl, 'data-sv-localized' => 'true', 'data-guard-msg' => $delGuard]); ?>

                                                <a href="<?php echo e($delUrl); ?>"
                                                    class="<?php echo e(VC::BT_SM_CT_PR); ?>"
                                                    data-url="<?php echo e($delUrl); ?>"
                                                    data-sv-localized="true"
                                                    data-guard-msg="<?php echo e($delGuard); ?>"
                                                    data-bs-toggle="tooltip"
                                                    title="<?php echo e(__('Delete')); ?>"
                                                    data-confirm="<?php echo e(__(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?')); ?>|<?php echo e(__(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?')); ?>"
                                                    data-confirm-yes="document.getElementById('<?php echo e($formId); ?>').submit();">
                                                    <i class="<?php echo e(VC::TI_TRS_WT); ?>"></i>
                                                </a>
                                                <?php echo Form::close(); ?>

                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach;
                            $__env->popLoop();
                            $loop = $__env->getLastLoop();
                            if ($__empty_1): ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted"><?php echo e(__('No products or services found.')); ?></td>
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
<script defer src="<?php echo e(asset('assets/js/routes/products/services/index.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make(EL::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/product_services/index.blade.php ENDPATH**/ ?>