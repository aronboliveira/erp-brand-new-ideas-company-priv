<?php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\{Invoice, Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
?>

<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
    <?php echo e(__('Receivable Reports')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e(Route::has('dashboard') ? route('dashboard') : '#'); ?>"
        <?php echo e(Route::has('dashboard') ? '' : 'aria-disabled="true"'); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item"><?php echo e(__('Receivable Reports')); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
    <script type="text/javascript" src="<?php echo e(asset('js/html2pdf.bundle.min.js')); ?>"></script>
    <script async src="<?php echo e('assets/js/routes/reports/receivables/lang/pdf.js'); ?>"></script>
    <script async src="<?php echo e('assets/js/routes/reports/receivables/pdf.js'); ?>"></script>
<?php $__env->stopPush(); ?>
<?php $__env->startSection(YieldingConstants::ADM_ACT_BTN); ?>
    
    
    <div class="float-end">
        <?php
            $rcvPrintBase = VW::RPT.'.receivables.print';
            $rcvPrintKebab = Str::kebab($rcvPrintBase);
            $rcvPrintResolved = Route::has($rcvPrintBase) ? $rcvPrintBase : (Route::has($rcvPrintKebab) ? $rcvPrintKebab : null);
            $actionRoute = $rcvPrintResolved ? [$rcvPrintResolved] : ['#'];
            $actionUrl = $rcvPrintResolved ? route($rcvPrintResolved) : '#';
            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
            $guardMsg = Utility::fetchLinkMessage($langValue, VW::RPT, 'print_receivables_route_unavailable') ?? 'Print receivables route is unavailable. Please contact technical support or your domain administrator.';
        ?>
        <?php echo e(Form::open(['route' => $actionRoute, 'method' => 'POST', 'id' => 'receivables-print', 'data-url' => $actionUrl, 'data-guard-msg' => $guardMsg, 'data-sv-localized' => 'true'])); ?>

            <input type="hidden" name="start_date" class="start_date">
            <input type="hidden" name="end_date" class="end_date">
            <input type="hidden" name="report" class="report">
            <button type="submit" class="<?php echo e(VC::BT_SM_PM); ?>" data-bs-toggle="tooltip" title="<?php echo e(__('Print')); ?>" data-original-title="<?php echo e(__('Print')); ?>">
                <i class="ti ti-printer"></i>
            </button>
        <?php echo e(Form::close()); ?>

        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
            <script src="<?php echo e(asset('assets/js/routes/reports/receivables/print.js')); ?>" defer></script>
        <?php $__env->stopPush(); ?>
    </div>
    <div class="float-end me-2" id="filter">
        <button id="filter" class="<?php echo e(VC::BT_SM_PM); ?>"><i class="ti ti-filter"></i></button>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
    <div class="<?php echo e(VC::MT4); ?>">
        <div class="<?php echo e(VC::RW); ?> justify-content-center">
            <div class="<?php echo e(VC::CM12); ?>">
                <div class="mt-2" id="multiCollapseExample1">
                    <div class="<?php echo e(VC::CD); ?>" id="show_filter" style="display:none;">
                        <div class="card-body">
                            <?php
                                $receivablesBase    = ViewsConstants::RPT.'.receivables';
                                $receivablesKebab   = Str::kebab($receivablesBase);
                                $receivablesResolved= Route::has($receivablesBase) ? $receivablesBase : (Route::has($receivablesKebab) ? $receivablesKebab : null);
                                $actionRoute        = $receivablesResolved ? [$receivablesResolved] : ['#'];
                                $actionUrl          = $receivablesResolved ? route($receivablesResolved) : '#';
                                $langValue          = isset($lang) ? $lang : Utility::fetchUserLang();
                                $applyGuardMsg      = Utility::fetchLinkMessage($langValue, ViewsConstants::RPT, 'apply_receivables_route_unavailable') ?? 'Apply receivables route is unavailable. Please contact technical support or your domain administrator.';
                                $resetGuardMsg      = Utility::fetchLinkMessage($langValue, ViewsConstants::RPT, 'reset_receivables_route_unavailable') ?? 'Reset receivables route is unavailable. Please contact technical support or your domain administrator.';
                            ?>
                            <?php echo e(Form::open(['route' => $actionRoute, 'method' => 'GET', 'id' => 'report_bill_summary', 'data-url' => $actionUrl, 'data-guard-msg' => $applyGuardMsg, 'data-sv-localized' => 'true'])); ?>

                                <div class="<?php echo e(VC::R_ALC_JCE); ?>">
                                    <div class="col-xl-10">
                                        <div class="<?php echo e(VC::RW); ?>">
                                            <div class="<?php echo e(VC::CL_XL3); ?>">
                                                <div class="btn-box"></div>
                                            </div>
                                            <div class="<?php echo e(VC::CL_XL3); ?>">
                                                <div class="btn-box"></div>
                                            </div>
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
                                            <input type="hidden" name="report" class="report">
                                        </div>
                                    </div>
                                    <div class="<?php echo e(VC::C_AT); ?> <?php echo e(VC::MT4); ?>">
                                        <div class="<?php echo e(VC::RW); ?>">
                                            <div class="<?php echo e(VC::C_AT); ?>">
                                                <a id="apply-receivables-index"
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
                                                <a id="reset-receivables-index"
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
                                <script src="<?php echo e(asset('assets/js/routes/reports/receivables/apply.js')); ?>" defer></script>
                                <script src="<?php echo e(asset('assets/js/routes/reports/receivables/reset.js')); ?>" defer></script>
                            <?php $__env->stopPush(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12" id="invoice-container">
            <div class="card">
                <?php
                    $tabs = [
                        ['id' => 'customer_balance',  'label' => __('Customer Balance')],
                        ['id' => 'receivable_summary','label' => __('Receivable Summary')],
                        ['id' => 'receivable_details','label' => __('Receivable Details')],
                        ['id' => 'aging_summary',     'label' => __('Aging Summary')],
                        ['id' => 'aging_details',     'label' => __('Aging Details')],
                    ];
                    $activeTab = request('tab', 'customer_balance');
                ?>
                <div class="card-header">
                    <div class="<?php echo e(VC::DFL); ?> <?php echo e(VC::JCB); ?> w-100">
                        <ul class="<?php echo e(VC::NAV_PL); ?> <?php echo e(VC::MB3); ?>" id="pills-tab" role="tablist">
                            <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="<?php echo e(VC::NV_IT); ?>">
                                    <a  id="tab-<?php echo e($t['id']); ?>"
                                        class="<?php echo e(VC::NV_LK); ?> <?php echo e($activeTab === $t['id'] ? 'active' : ''); ?>"
                                        data-bs-toggle="pill"
                                        href="#<?php echo e($t['id']); ?>"
                                        role="tab"
                                        aria-controls="<?php echo e($t['id']); ?>"
                                        aria-selected="<?php echo e($activeTab === $t['id'] ? 'true' : 'false'); ?>">
                                        <?php echo e($t['label']); ?>

                                    </a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                    <script>
                        (function () {
                            const list = document.getElementById('pills-tab');
                            if (!list) return;
                            const links = Array.from(list.querySelectorAll('a[data-bs-toggle="pill"]'));
                            const ids   = links.map(a => a.getAttribute('href')?.replace('#','')).filter(Boolean);
                            const url   = new URL(window.location.href);
                            const hash  = (window.location.hash || '').replace('#','');
                            const qTab  = url.searchParams.get('tab');
                            const init  = ids.includes(hash) ? hash : (ids.includes(qTab) ? qTab : ids[0]);
                            if (init) {
                                const el = list.querySelector(`a[href="#${init}"]`);
                                if (el && !el.classList.contains('active')) {
                                    el.click();
                                }
                                url.searchParams.set('tab', init);
                                history.replaceState(null, '', url.toString().split('#')[0] + '#' + init);
                            }
                            links.forEach(a => {
                                a.addEventListener('shown.bs.tab', function (ev) {
                                    const id = this.getAttribute('href').replace('#','');
                                    const u  = new URL(window.location.href);
                                    u.searchParams.set('tab', id);
                                    history.replaceState(null, '', u.toString().split('#')[0] + '#' + id);
                                });
                            });
                        })();
                    </script>
                <?php $__env->stopPush(); ?>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="tab-content" id="myTabContent2">
                                <?php
                                    $statusClasses = [
                                        0 => 'bg-secondary',
                                        1 => 'bg-warning',
                                        2 => 'bg-danger',
                                        3 => 'bg-info',
                                        4 => 'bg-primary',
                                    ];
                                ?>
                                <div class="tab-pane fade show active" id="customer_balance" role="tabpanel" aria-labelledby="receivable-tab1">
                                    <table class="<?php echo e(VC::TB); ?> table-flush" id="report-customer-balance">
                                        <thead>
                                        <tr>
                                            <th width="33%"><?php echo e(__('Customer Name')); ?></th>
                                            <th width="33%"><?php echo e(__('Invoice Balance')); ?></th>
                                            <th width="33%"><?php echo e(__('Available Credits')); ?></th>
                                            <th class="text-end"><?php echo e(__('Balance')); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            $grouped = collect($receivableCustomers ?? [])->groupBy('name')->map(function ($rows, $name) {
                                                $price      = (float) $rows->sum('price');
                                                $totalTax   = (float) $rows->sum('total_tax');
                                                $paid       = (float) $rows->sum(fn($r) => $r['pay_price'] ?? 0);
                                                $credits    = (float) $rows->sum('credit_price');

                                                return [
                                                    'name'            => $name,
                                                    'invoice_balance' => $price + $totalTax - $paid,
                                                    'credits'         => $credits,
                                                ];
                                            });

                                            $grandTotal = 0.0;
                                        ?>

                                        <?php $__empty_1 = true; $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <?php
                                                $balance    = $row['invoice_balance'] - $row['credits'];
                                                $grandTotal += $balance;
                                            ?>
                                            <tr>
                                                <td><?php echo e($row['name']); ?></td>
                                                <td><?php echo e($user?->priceFormat($row['invoice_balance'])); ?></td>
                                                <td><?php echo e($user?->priceFormat($row['credits'] ?: 0)); ?></td>
                                                <td class="text-end"><?php echo e($user?->priceFormat($balance)); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr><td colspan="4" class="text-center text-muted"><?php echo e(__('No data found')); ?></td></tr>
                                        <?php endif; ?>

                                        <?php if(($receivableCustomers ?? []) !== []): ?>
                                            <tr>
                                                <th><?php echo e(__('Total')); ?></th>
                                                <td></td>
                                                <td></td>
                                                <th class="text-end"><?php echo e($user?->priceFormat($grandTotal)); ?></th>
                                            </tr>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="receivable_summary" role="tabpanel" aria-labelledby="receivable-tab2">
                                    <table class="<?php echo e(VC::TB); ?> table-flush" id="report-receivable-summary">
                                        <thead>
                                        <tr>
                                            <th><?php echo e(__('Customer Name')); ?></th>
                                            <th><?php echo e(__('Date')); ?></th>
                                            <th><?php echo e(__('Transaction')); ?></th>
                                            <th><?php echo e(__('Status')); ?></th>
                                            <th><?php echo e(__('Transaction Type')); ?></th>
                                            <th><?php echo e(__('Total')); ?></th>
                                            <th><?php echo e(__('Balance')); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            $rows = $receivableSummaries ?? [];
                                            usort($rows, fn($a,$b) => strtotime($b['issue_date']) <=> strtotime($a['issue_date']));
                                            $totalBalance = 0.0;
                                            $totalAmount  = 0.0;
                                        ?>

                                        <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <?php
                                                $isInvoice          = !empty($r['invoice']);
                                                $receivableAmount   = $isInvoice ? ($r['price'] + $r['total_tax']) : -$r['price'];
                                                $paid               = (float) ($r['pay_price'] ?? 0);
                                                $balance            = $receivableAmount - $paid;

                                                $totalBalance      += $balance;
                                                $totalAmount       += $receivableAmount;

                                                $bgClass = $statusClasses[$r['status']] ?? null;
                                            ?>
                                            <tr>
                                                <td><?php echo e($r['name']); ?></td>
                                                <td><?php echo e($r['issue_date']); ?></td>

                                                <td>
                                                    <?php if($isInvoice): ?>
                                                        <?php echo e($user?->invoiceNumberFormat($r['invoice'])); ?>

                                                    <?php else: ?>
                                                        <?php echo e(__('Credit Note')); ?>

                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <?php if($bgClass): ?>
                                                        <span class="status_badge <?php echo e(VC::BDG); ?> <?php echo e($bgClass); ?> p-2 <?php echo e(VC::PX3); ?> rounded">
                                                            <?php echo e(__(Invoice::$statuses[$r['status']] ?? '-')); ?>

                                                        </span>
                                                    <?php else: ?>
                                                        <span class="p-2 <?php echo e(VC::PX3); ?>">-</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td><?php echo e($isInvoice ? __('Invoice') : __('Credit Note')); ?></td>
                                                <td><?php echo e($user?->priceFormat($receivableAmount)); ?></td>
                                                <td><?php echo e($user?->priceFormat($balance)); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr><td colspan="7" class="text-center text-muted"><?php echo e(__('No data found')); ?></td></tr>
                                        <?php endif; ?>

                                        <?php if(($receivableSummaries ?? []) !== []): ?>
                                            <tr>
                                                <th><?php echo e(__('Total')); ?></th>
                                                <th></th><th></th><th></th><th></th>
                                                <th><?php echo e($user?->priceFormat($totalAmount)); ?></th>
                                                <th><?php echo e($user?->priceFormat($totalBalance)); ?></th>
                                            </tr>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="receivable_details" role="tabpanel" aria-labelledby="receivable-tab3">
                                    <table class="<?php echo e(VC::TB); ?> table-flush" id="report-receivable-details">
                                        <thead>
                                        <tr>
                                            <th><?php echo e(__('Customer Name')); ?></th>
                                            <th><?php echo e(__('Date')); ?></th>
                                            <th><?php echo e(__('Transaction')); ?></th>
                                            <th><?php echo e(__('Status')); ?></th>
                                            <th><?php echo e(__('Transaction Type')); ?></th>
                                            <th><?php echo e(__('Item Name')); ?></th>
                                            <th><?php echo e(__('Quantity Ordered')); ?></th>
                                            <th><?php echo e(__('Item Price')); ?></th>
                                            <th><?php echo e(__('Total')); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            $rows = $receivableDetails ?? [];
                                            usort($rows, fn($a,$b) => strtotime($b['issue_date']) <=> strtotime($a['issue_date']));
                                            $grandTotal = 0.0;
                                            $grandQty   = 0;
                                        ?>

                                        <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <?php
                                                $isInvoice         = !empty($row['invoice']);
                                                $unitPrice         = $isInvoice ? $row['price'] : -$row['price'];
                                                $qty               = $isInvoice ? ($row['quantity'] ?? 0) : 0;
                                                $lineTotal         = $isInvoice ? ($unitPrice * $qty) : -$row['price'];
                                                $grandTotal       += $lineTotal;
                                                $grandQty         += $qty;
                                                $bgClass           = $statusClasses[$row['status']] ?? null;
                                            ?>
                                            <tr>
                                                <td><?php echo e($row['name']); ?></td>
                                                <td><?php echo e($row['issue_date']); ?></td>
                                                <td>
                                                    <?php if($isInvoice): ?>
                                                        <?php echo e($user?->invoiceNumberFormat($row['invoice'])); ?>

                                                    <?php else: ?>
                                                        <?php echo e(__('Credit Note')); ?>

                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($bgClass): ?>
                                                        <span class="status_badge <?php echo e(VC::BDG); ?> <?php echo e($bgClass); ?> p-2 <?php echo e(VC::PX3); ?> rounded">
                                                            <?php echo e(__(Invoice::$statuses[$row['status']] ?? '-')); ?>

                                                        </span>
                                                    <?php else: ?>
                                                        <span class="p-2 <?php echo e(VC::PX3); ?>">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo e($isInvoice ? __('Invoice') : __('Credit Note')); ?></td>
                                                <td><?php echo e($row['product_name']); ?></td>
                                                <td><?php echo e($qty); ?></td>
                                                <td><?php echo e($user?->priceFormat($unitPrice)); ?></td>
                                                <td><?php echo e($user?->priceFormat($lineTotal)); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr><td colspan="9" class="text-center text-muted"><?php echo e(__('No data found')); ?></td></tr>
                                        <?php endif; ?>

                                        <?php if(!empty($rows)): ?>
                                            <tr>
                                                <th><?php echo e(__('Total')); ?></th>
                                                <th colspan="5"></th>
                                                <th><?php echo e($grandQty); ?></th>
                                                <th></th>
                                                <th><?php echo e($user?->priceFormat($grandTotal)); ?></th>
                                            </tr>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="aging_summary" role="tabpanel" aria-labelledby="receivable-tab4">
                                    <table class="<?php echo e(VC::TB); ?> table-flush" id="report-aging-summary">
                                        <thead>
                                        <tr>
                                            <th><?php echo e(__('Customer Name')); ?></th>
                                            <th><?php echo e(__('Current')); ?></th>
                                            <th><?php echo e(__('1-15 DAYS')); ?></th>
                                            <th><?php echo e(__('16-30 DAYS')); ?></th>
                                            <th><?php echo e(__('31-45 DAYS')); ?></th>
                                            <th><?php echo e(__('> 45 DAYS')); ?></th>
                                            <th><?php echo e(__('Total')); ?></th>
                                        </tr>
                                        </thead>
                                        <?php
                                            $summaries   = $agingSummaries ?? [];
                                            $totCurr     = 0.0; $tot15 = 0.0; $tot30 = 0.0; $tot45 = 0.0; $totMore45 = 0.0; $totAll = 0.0;
                                        ?>
                                        <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $summaries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer => $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td><?php echo e($customer); ?></td>
                                                <td><?php echo e($user?->priceFormat($s['current'])); ?></td>
                                                <td><?php echo e($user?->priceFormat($s['1_15_days'])); ?></td>
                                                <td><?php echo e($user?->priceFormat($s['16_30_days'])); ?></td>
                                                <td><?php echo e($user?->priceFormat($s['31_45_days'])); ?></td>
                                                <td><?php echo e($user?->priceFormat($s['greater_than_45_days'])); ?></td>
                                                <td><?php echo e($user?->priceFormat($s['total_due'])); ?></td>
                                            </tr>
                                            <?php
                                                $totCurr   += $s['current'];
                                                $tot15     += $s['1_15_days'];
                                                $tot30     += $s['16_30_days'];
                                                $tot45     += $s['31_45_days'];
                                                $totMore45 += $s['greater_than_45_days'];
                                                $totAll    += $s['total_due'];
                                            ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr><td colspan="7" class="text-center text-muted"><?php echo e(__('No data found')); ?></td></tr>
                                        <?php endif; ?>

                                        <?php if(!empty($summaries)): ?>
                                            <tr>
                                                <th><?php echo e(__('Total')); ?></th>
                                                <th><?php echo e($user?->priceFormat($totCurr)); ?></th>
                                                <th><?php echo e($user?->priceFormat($tot15)); ?></th>
                                                <th><?php echo e($user?->priceFormat($tot30)); ?></th>
                                                <th><?php echo e($user?->priceFormat($tot45)); ?></th>
                                                <th><?php echo e($user?->priceFormat($totMore45)); ?></th>
                                                <th><?php echo e($user?->priceFormat($totAll)); ?></th>
                                            </tr>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="aging_details" role="tabpanel" aria-labelledby="receivable-tab5">
                                    <table class="<?php echo e(VC::TB); ?> table-flush" id="report-aging-details">
                                        <thead>
                                        <tr>
                                            <th><?php echo e(__('Date')); ?></th>
                                            <th><?php echo e(__('Transaction')); ?></th>
                                            <th><?php echo e(__('Type')); ?></th>
                                            <th><?php echo e(__('Status')); ?></th>
                                            <th><?php echo e(__('Customer Name')); ?></th>
                                            <th><?php echo e(__('Age')); ?></th>
                                            <th><?php echo e(__('Amount')); ?></th>
                                            <th><?php echo e(__('Balance Due')); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            $bucketTotals = [
                                                'current'   => ['amt' => 0.0, 'due' => 0.0, 'rows' => $currents ?? []],
                                                '1_15'      => ['amt' => 0.0, 'due' => 0.0, 'rows' => $days1to15 ?? []],
                                                '16_30'     => ['amt' => 0.0, 'due' => 0.0, 'rows' => $days16to30 ?? []],
                                                '31_45'     => ['amt' => 0.0, 'due' => 0.0, 'rows' => $days31to45 ?? []],
                                                '45_plus'   => ['amt' => 0.0, 'due' => 0.0, 'rows' => $moreThan45 ?? []],
                                            ];
                                            $labels = [
                                                '45_plus' => __('> 45 Days'),
                                                '31_45'   => __('31 to 45 Days'),
                                                '16_30'   => __('16 to 30 Days'),
                                                '1_15'    => __('1 to 15 Days'),
                                                'current' => __('Current'),
                                            ];
                                        ?>

                                        <?php $__currentLoopData = ['45_plus','31_45','16_30','1_15','current']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php $bucket = $bucketTotals[$key]; ?>
                                            <?php if(!empty($bucket['rows'])): ?>
                                                <tr class="table-light">
                                                    <th colspan="8"><?php echo e($labels[$key]); ?></th>
                                                </tr>
                                            <?php endif; ?>

                                            <?php $__currentLoopData = $bucket['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $bucketTotals[$key]['amt'] += $r['total_price'];
                                                    $bucketTotals[$key]['due'] += $r['balance_due'];
                                                    $bgClass = $statusClasses[$r['status']] ?? null;
                                                ?>
                                                <tr>
                                                    <td><?php echo e($r['due_date']); ?></td>
                                                    <td><?php echo e($user?->invoiceNumberFormat($r['invoice_id'])); ?></td>
                                                    <td><?php echo e(__('Invoice')); ?></td>
                                                    <td>
                                                        <?php if($bgClass): ?>
                                                            <span class="status_badge <?php echo e(VC::BDG); ?> <?php echo e($bgClass); ?> p-2 <?php echo e(VC::PX3); ?> rounded">
                                                                <?php echo e(__(Invoice::$statuses[$r['status']] ?? '-')); ?>

                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo e($r['name']); ?></td>
                                                    <td><?php echo e($key === 'current' ? '-' : ($r['age'] . ' ' . __('Days'))); ?></td>
                                                    <td><?php echo e($user?->priceFormat($r['total_price'])); ?></td>
                                                    <td><?php echo e($user?->priceFormat($r['balance_due'])); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                            <?php if(!empty($bucket['rows'])): ?>
                                                <tr>
                                                    <th colspan="6"></th>
                                                    <th><?php echo e($user?->priceFormat($bucketTotals[$key]['amt'])); ?></th>
                                                    <th><?php echo e($user?->priceFormat($bucketTotals[$key]['due'])); ?></th>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                        <?php
                                            $grandAmt = array_sum(array_column($bucketTotals,'amt'));
                                            $grandDue = array_sum(array_column($bucketTotals,'due'));
                                        ?>

                                        <?php if($grandAmt || $grandDue): ?>
                                            <tr>
                                                <th><?php echo e(__('Total')); ?></th>
                                                <th colspan="5"></th>
                                                <th><?php echo e($user?->priceFormat($grandAmt)); ?></th>
                                                <th><?php echo e($user?->priceFormat($grandDue)); ?></th>
                                            </tr>
                                        <?php endif; ?>
                                        </tbody>
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

<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/reports/receivable_report.blade.php ENDPATH**/ ?>