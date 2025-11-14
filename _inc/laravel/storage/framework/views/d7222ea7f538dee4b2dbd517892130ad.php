<?php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
?>

<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
    <?php echo e(__('Sales Report')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e(Route::has('dashboard') ? route('dashboard') : '#'); ?>"
        <?php echo e(Route::has('dashboard') ? '' : 'aria-disabled="true"'); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item"><?php echo e(__('Sales Report')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
    <script type="text/javascript" src="<?php echo e(asset('js/html2pdf.bundle.min.js')); ?>"></script>
    <script async src="<?php echo e(asset('assets/js/routes/reports/sales/index/lang/report.js')); ?>"></script>
    <script defer src="<?php echo e(asset('assets/js/routes/reports/sales/index/report.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection(YieldingConstants::ADM_ACT_BTN); ?>
    <?php
        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
        $printBase        = VW::RPT.'.sales.report.print';
        $printKebab       = Str::kebab($printBase);
        $printResolved    = Route::has($printBase) ? $printBase : (Route::has($printKebab) ? $printKebab : null);
        $printActionRoute = $printResolved ? [$printResolved] : ['#'];
        $printActionUrl   = $printResolved ? route($printResolved) : '#';
        $printGuardMsg    = Utility::fetchLinkMessage($langValue, VW::RPT, 'print_sales_report_route_unavailable') ?? 'Print sales report route is unavailable. Please contact technical support or your domain administrator.';
        $exportBase        = VW::RPT.'.sales.export';
        $exportKebab       = Str::kebab($exportBase);
        $exportResolved    = Route::has($exportBase) ? $exportBase : (Route::has($exportKebab) ? $exportKebab : null);
        $exportActionRoute = $exportResolved ? [$exportResolved] : ['#'];
        $exportActionUrl   = $exportResolved ? route($exportResolved) : '#';
        $exportGuardMsg    = Utility::fetchLinkMessage($langValue, VW::RPT, 'export_sales_report_route_unavailable') ?? 'Export sales report route is unavailable. Please contact technical support or your domain administrator.';
    ?>
    <div class="float-end">
        <?php echo e(Form::open([
            'route'             => $printActionRoute,
            'id'                => 'sales-report-print',
            'data-url'          => $printActionUrl,
            'data-guard-msg'    => $printGuardMsg,
            'data-sv-localized' => 'true',
        ])); ?>

            <input type="hidden" name="start_date" class="start_date">
            <input type="hidden" name="end_date" class="end_date">
            <input type="hidden" name="report" class="report">
            <button type="submit" class="<?php echo e(VC::BT_SM_PM); ?>" data-bs-toggle="tooltip" title="<?php echo e(__('Print')); ?>" data-original-title="<?php echo e(__('Print')); ?>">
                <i class="ti ti-printer"></i>
            </button>
        <?php echo e(Form::close()); ?>

    </div>
    <div class="float-end me-2">
        <?php echo e(Form::open([
            'route'             => $exportActionRoute,
            'id'                => 'sales-report-export',
            'data-url'          => $exportActionUrl,
            'data-guard-msg'    => $exportGuardMsg,
            'data-sv-localized' => 'true',
        ])); ?>

            <input type="hidden" name="start_date" class="start_date">
            <input type="hidden" name="end_date" class="end_date">
            <input type="hidden" name="report" class="report">
            <button type="submit" class="<?php echo e(VC::BT_SM_PM); ?>" data-bs-toggle="tooltip" title="<?php echo e(__('Export')); ?>" data-original-title="<?php echo e(__('Export')); ?>">
                <i class="<?php echo e(VC::TI_EXP); ?>"></i>
            </button>
        <?php echo e(Form::close()); ?>

    </div>
    <div class="float-end me-2" id="filter">
        <button id="filter" class="<?php echo e(VC::BT_SM_PM); ?>"><i class="ti ti-filter"></i></button>
    </div>
    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
        <script src="<?php echo e(asset('assets/js/routes/reports/sales/print.js')); ?>" defer></script>
        <script src="<?php echo e(asset('assets/js/routes/reports/sales/export.js')); ?>" defer></script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
    
<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
    <div class="<?php echo e(VC::MT4); ?>">
        <div class="<?php echo e(VC::RW); ?> justify-content-center">
            <div class="<?php echo e(VC::CM12); ?>">
                <div class="mt-2" id="multiCollapseExample1">
                    <div class="<?php echo e(VC::CD); ?>" id="show_filter" style="display:none;">
                        <div class="card-body">
                            <?php
                                $salesBase        = VW::RPT.'.sales';
                                $salesKebab       = Str::kebab($salesBase);
                                $salesResolved    = Route::has($salesBase) ? $salesBase : (Route::has($salesKebab) ? $salesKebab : null);
                                $actionRoute      = $salesResolved ? [$salesResolved] : ['#'];
                                $actionUrl        = $salesResolved ? route($salesResolved) : '#';
                                $langValue        = isset($lang) ? $lang : Utility::fetchUserLang();
                                $applyGuardMsg    = Utility::fetchLinkMessage($langValue, VW::RPT, 'apply_sales_route_unavailable') ?? 'Apply sales route is unavailable. Please contact technical support or your domain administrator.';
                                $resetGuardMsg    = Utility::fetchLinkMessage($langValue, VW::RPT, 'reset_sales_route_unavailable') ?? 'Reset sales route is unavailable. Please contact technical support or your domain administrator.';
                            ?>
                            <?php echo e(Form::open(['route'=>$actionRoute,'method'=>'GET','id'=>'report_bill_summary','data-url'=>$actionUrl,'data-guard-msg'=>$applyGuardMsg,'data-sv-localized'=>'true'])); ?>

                                <div class="<?php echo e(VC::R_ALC_JCE); ?>">
                                    <div class="col-xl-10">
                                        <div class="<?php echo e(VC::RW); ?>">
                                            <div class="<?php echo e(VC::CL_XL3); ?>"><div class="btn-box"></div></div>
                                            <div class="<?php echo e(VC::CL_XL3); ?>"><div class="btn-box"></div></div>
                                            <div class="<?php echo e(VC::CL_XL3); ?>">
                                                <div class="btn-box">
                                                    <?php echo e(Form::label('start_date', __('Start Date'), ['class' => VC::FM_LB])); ?>

                                                    <?php echo e(Form::date('start_date', $filter['startDateRange'], ['class' => 'startDate ' . VC::FM_CT])); ?>

                                                </div>
                                            </div>
                                            <div class="<?php echo e(VC::CL_XL3); ?>">
                                                <div class="btn-box">
                                                    <?php echo e(Form::label('end_date', __('End Date'), ['class' => VC::FM_LB])); ?>

                                                    <?php echo e(Form::date('end_date', $filter['endDateRange'], ['class' => 'endDate ' . VC::FM_CT])); ?>

                                                </div>
                                            </div>
                                            <input type="hidden" name="view" value="horizontal">
                                        </div>
                                    </div>
                                    <div class="<?php echo e(VC::C_AT); ?> <?php echo e(VC::MT4); ?>">
                                        <div class="<?php echo e(VC::RW); ?>">
                                            <div class="<?php echo e(VC::C_AT); ?>">
                                                <a id="apply-sales-index"
                                                href="#"
                                                class="<?php echo e(VC::BT_SM_PM); ?>"
                                                data-form-id="report_bill_summary"
                                                data-guard-msg="<?php echo e($applyGuardMsg); ?>"
                                                data-sv-localized="true"
                                                data-bs-toggle="tooltip"
                                                title="<?php echo e(__('Apply')); ?>"
                                                data-original-title="<?php echo e(__('apply')); ?>">
                                                    <span class="btn-inner--icon"><i class="<?php echo e(VC::TI_SRC); ?>"></i></span>
                                                </a>
                                                <a id="reset-sales-index"
                                                href="<?php echo e($actionUrl); ?>"
                                                class="<?php echo e(VC::BT_SM_DG); ?>"
                                                data-url="<?php echo e($actionUrl); ?>"
                                                data-guard-msg="<?php echo e($resetGuardMsg); ?>"
                                                data-sv-localized="true"
                                                data-bs-toggle="tooltip"
                                                title="<?php echo e(__('Reset')); ?>"
                                                data-original-title="<?php echo e(__('Reset')); ?>">
                                                    <span class="btn-inner--icon"><i class="<?php echo e(VC::TI_TRS_OFF); ?>"></i></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php echo e(Form::close()); ?>

                            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                <script src="<?php echo e(asset('assets/js/routes/reports/sales/apply.js')); ?>" defer></script>
                                <script src="<?php echo e(asset('assets/js/routes/reports/sales/reset.js')); ?>" defer></script>
                            <?php $__env->stopPush(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="<?php echo e(VC::RW); ?>">
        <div class="<?php echo e(VC::C12); ?>" id="invoice-container">
            <div class="<?php echo e(VC::CD); ?>">
                <div class="card-header">
                    <div class="<?php echo e(VC::DFL_JCB); ?> w-100">
                        <ul class="<?php echo e(VC::NAV_PL); ?> <?php echo e(VC::MB3); ?>" id="pills-tab" role="tablist">
                            <li class="<?php echo e(VC::NV_IT); ?>">
                                <a class="<?php echo e(VC::NV_LK); ?> active"
                                   id="tab-items"
                                   data-bs-toggle="pill"
                                   href="#pane-items"
                                   role="tab"
                                   aria-controls="pane-items"
                                   aria-selected="true"><?php echo e(__('Sales by Item')); ?></a>
                            </li>
                            <li class="<?php echo e(VC::NV_IT); ?>">
                                <a class="<?php echo e(VC::NV_LK); ?>"
                                   id="tab-customers"
                                   data-bs-toggle="pill"
                                   href="#pane-customers"
                                   role="tab"
                                   aria-controls="pane-customers"
                                   aria-selected="false"><?php echo e(__('Sales by Customer')); ?></a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card-body">
                    <div class="<?php echo e(VC::RW); ?>">
                        <div class="<?php echo e(VC::CS12); ?>">
                            <div class="tab-content" id="myTabContent2">
                                <div class="tab-pane fade show active"
                                     id="pane-items"
                                     role="tabpanel"
                                     aria-labelledby="tab-items">
                                    <?php $totQty = 0; $totAmt = 0.0; ?>
                                    <table class="<?php echo e(VC::TB); ?> table-flush" id="report-items-table">
                                        <thead>
                                            <tr>
                                                <th width="33%"><?php echo e(__('Invoice Item')); ?></th>
                                                <th width="33%" class="text-end"><?php echo e(__('Quantity Sold')); ?></th>
                                                <th width="33%" class="text-end"><?php echo e(__('Amount')); ?></th>
                                                <th class="text-end"><?php echo e(__('Average Price')); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__empty_1 = true; $__currentLoopData = $invoiceItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <?php
                                                    $qty = (float)($row['quantity'] ?? 0);
                                                    $amt = (float)($row['price'] ?? 0);
                                                    $avg = isset($row['avg_price']) ? (float)$row['avg_price'] : ($qty > 0 ? $amt / $qty : 0);
                                                    $totQty += $qty; $totAmt += $amt;
                                                ?>
                                                <tr>
                                                    <td><?php echo e($row['name']); ?></td>
                                                    <td class="text-end"><?php echo e($qty); ?></td>
                                                    <td class="text-end"><?php echo e($user?->priceFormat($amt)); ?></td>
                                                    <td class="text-end"><?php echo e($user?->priceFormat($avg)); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted"><?php echo e(__('No data found')); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>

                                        <?php if(!empty($invoiceItems)): ?>
                                            <tfoot>
                                                <tr>
                                                    <th><?php echo e(__('Total')); ?></th>
                                                    <th class="text-end"><?php echo e($totQty); ?></th>
                                                    <th class="text-end"><?php echo e($user?->priceFormat($totAmt)); ?></th>
                                                    <th class="text-end"><?php echo e($user?->priceFormat($totQty > 0 ? $totAmt / $totQty : 0)); ?></th>
                                                </tr>
                                            </tfoot>
                                        <?php endif; ?>
                                    </table>
                                </div>

                                <div class="tab-pane fade"
                                     id="pane-customers"
                                     role="tabpanel"
                                     aria-labelledby="tab-customers">
                                    <?php $totCount = 0; $totSales = 0.0; $totWithTax = 0.0; ?>
                                    <table class="<?php echo e(VC::TB); ?> table-flush" id="report-customers-table">
                                        <thead>
                                            <tr>
                                                <th width="33%"><?php echo e(__('Customer Name')); ?></th>
                                                <th width="33%" class="text-end"><?php echo e(__('Invoice Count')); ?></th>
                                                <th width="33%" class="text-end"><?php echo e(__('Sales')); ?></th>
                                                <th class="text-end"><?php echo e(__('Sales With Tax')); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__empty_1 = true; $__currentLoopData = $invoiceCustomers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <?php
                                                    $count = (int)($row['invoice_count'] ?? 0);
                                                    $amt   = (float)($row['price'] ?? 0);
                                                    $tax   = (float)($row['total_tax'] ?? 0);
                                                    $totCount += $count; $totSales += $amt; $totWithTax += ($amt + $tax);
                                                ?>
                                                <tr>
                                                    <td><?php echo e($row['name']); ?></td>
                                                    <td class="text-end"><?php echo e($count); ?></td>
                                                    <td class="text-end"><?php echo e($user?->priceFormat($amt)); ?></td>
                                                    <td class="text-end"><?php echo e($user?->priceFormat($amt + $tax)); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted"><?php echo e(__('No data found')); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>

                                        <?php if(!empty($invoiceCustomers)): ?>
                                            <tfoot>
                                                <tr>
                                                    <th><?php echo e(__('Total')); ?></th>
                                                    <th class="text-end"><?php echo e($totCount); ?></th>
                                                    <th class="text-end"><?php echo e($user?->priceFormat($totSales)); ?></th>
                                                    <th class="text-end"><?php echo e($user?->priceFormat($totWithTax)); ?></th>
                                                </tr>
                                            </tfoot>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/reports/sales_report.blade.php ENDPATH**/ ?>