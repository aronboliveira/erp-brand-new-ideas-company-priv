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
    <?php echo e(__('Account Statement Summary')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
    <!-- <script src="<?php echo e(asset('js/jspdf.min.js')); ?> "></script>
    <script type="text/javascript" src="<?php echo e(asset('js/html2pdf.bundle.min.js')); ?>"></script>
    <script type="text/javascript" src="<?php echo e(asset('assets/js/jszip.js')); ?>"></script>
    <script type="text/javascript" src="<?php echo e(asset('assets/js/pdfmake.js')); ?>"></script>
    <script type="text/javascript" src="<?php echo e(asset('assets/js/vfs_fonts.js')); ?>"></script>
    <script type="text/javascript" src="<?php echo e(asset('assets/js/dataTables.buttons.js')); ?>"></script>
    <script type="text/javascript" src="<?php echo e(asset('assets/js/buttons.html5.js')); ?>"></script>
    <script type="text/javascript" src="<?php echo e(asset('assets/js/buttons.print.min.js')); ?>"></script> -->
    <script type="text/javascript" src="<?php echo e(asset('js/html2pdf.bundle.min.js')); ?>"></script>
    <script async src="<?php echo e(asset('assets/js/routes/reports/statements/lang/pdf.js')); ?>"></script>
    <script async src="<?php echo e(asset('assets/js/routes/reports/statements/pdf.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection(YieldingConstants::ADM_BDC); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e(Route::has('dashboard') ? route('dashboard') : '#'); ?>"
        <?php echo e(Route::has('dashboard') ? '' : 'aria-disabled="true"'); ?>>
            <?php echo e(__('Dashboard')); ?>

        </a>
    </li>
    <li class="breadcrumb-item"><?php echo e(__('Account Statement Summary')); ?></li>
<?php $__env->stopSection(); ?>




<?php $__env->startSection(YieldingConstants::ADM_ACT_BTN); ?>
    <div class="float-end">
        <?php
            $exportBase = VW::ACC_STT.'.export';
            $exportKebab = Str::kebab($exportBase);
            $exportResolved = Route::has($exportBase) ? $exportBase : (Route::has($exportKebab) ? $exportKebab : null);
            $exportUrl = $exportResolved ? route($exportResolved) : '#';
            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
            $exportGuardMsg = Utility::fetchLinkMessage($langValue, VW::ACC_STT, 'export_account_statements_route_unavailable') ?? 'Export account statements route is unavailable. Please contact technical support or your domain administrator.';
        ?>
        <a id="account-statements-export"
        href="<?php echo e($exportUrl); ?>"
        data-url="<?php echo e($exportUrl); ?>"
        data-guard-msg="<?php echo e($exportGuardMsg); ?>"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="<?php echo e(__('Export')); ?>"
        class="btn btn-sm btn-primary">
            <i class="ti ti-file-export"></i>
        </a>
        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
            <script src="<?php echo e(asset('assets/js/routes/reports/accountStatements/export.js')); ?>" defer></script>
        <?php $__env->stopPush(); ?>
        <?php
            $downloadLabelAs = __('Download');
            $downloadGuardMsgAs = Utility::fetchLinkMessage($lang, VW::RPT, 'download_account_statements_report_unavailable') ?? 'Download function for Account Statements report is unavailable. Please contact technical support or your domain administrator.';
        ?>
        <a href="#"
        class="<?php echo e(VC::BT_SM_PM); ?> download-account-statements"
        data-func-name="saveAsPDF"
        data-guard-msg="<?php echo e($downloadGuardMsgAs); ?>"
        data-sv-localized="true"
        data-bs-toggle="tooltip"
        title="<?php echo e($downloadLabelAs); ?>"
        aria-label="<?php echo e($downloadLabelAs); ?>"
        data-original-title="<?php echo e($downloadLabelAs); ?>">
            <span class="btn-inner--icon"><i class="<?php echo e(VC::TI_DWN); ?>"></i></span>
        </a>
        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
            <script src="<?php echo e(asset('assets/js/routes/reports/accountStatements/download.js')); ?>" defer></script>
        <?php $__env->stopPush(); ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection(YieldingConstants::ADM_CTT); ?>
    <div class="<?php echo e(VC::RW); ?>">
        <div class="<?php echo e(VC::CS12); ?>">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="<?php echo e(VC::CD); ?>">
                    <div class="card-body">
                        <?php
                            $acctStmtBase = VW::RPT.'.account.statement';
                            $acctStmtKebab = Str::kebab($acctStmtBase);
                            $acctStmtResolved = Route::has($acctStmtBase) ? $acctStmtBase : (Route::has($acctStmtKebab) ? $acctStmtKebab : null);
                            $actionRoute = $acctStmtResolved ? [$acctStmtResolved] : ['#'];
                            $actionUrl = $acctStmtResolved ? route($acctStmtResolved) : '#';
                            $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                            $applyGuardMsg = Utility::fetchLinkMessage($langValue, VW::RPT, 'apply_account_statement_route_unavailable') ?? 'Apply account statement route is unavailable. Please contact technical support or your domain administrator.';
                            $resetGuardMsg = Utility::fetchLinkMessage($langValue, VW::RPT, 'reset_account_statement_route_unavailable') ?? 'Reset account statement route is unavailable. Please contact technical support or your domain administrator.';
                        ?>
                        <?php echo e(Form::open(['route'=> $actionRoute,'method'=>'GET','id'=>'report_account','data-url'=>$actionUrl,'data-guard-msg'=>$applyGuardMsg,'data-sv-localized'=>'true'])); ?>

                            <div class="<?php echo e(VC::R_ALC_JCE); ?>">
                                <div class="col-xl-10">
                                    <div class="<?php echo e(VC::RW); ?>">
                                        <div class="<?php echo e(VC::CL_XL3); ?>">
                                            <div class="btn-box">
                                                <?php echo e(Form::label('start_month', __('Start Month'), ['class' => VC::FM_LB])); ?>

                                                <?php echo e(Form::month('start_month', isset($_GET['start_month']) ? $_GET['start_month'] : date('Y-m', strtotime('-5 month')), ['class' => 'month-btn ' . VC::FM_CT])); ?>

                                            </div>
                                        </div>
                                        <div class="<?php echo e(VC::CL_XL3); ?>">
                                            <div class="btn-box">
                                                <?php echo e(Form::label('end_month', __('End Month'), ['class' => VC::FM_LB])); ?>

                                                <?php echo e(Form::month('end_month', isset($_GET['end_month']) ? $_GET['end_month'] : date('Y-m'), ['class' => 'month-btn ' . VC::FM_CT])); ?>

                                            </div>
                                        </div>
                                        <div class="<?php echo e(VC::CL_XL3); ?>">
                                            <div class="btn-box">
                                                <?php echo e(Form::label('account', __('Account'), ['class' => VC::FM_LB])); ?>

                                                <?php echo e(Form::select('account', $account, isset($_GET['account']) ? $_GET['account'] : '', ['class' => VC::FM_CT_SL])); ?>

                                            </div>
                                        </div>
                                        <div class="<?php echo e(VC::CL_XL3); ?>">
                                            <div class="btn-box">
                                                <?php echo e(Form::label('type', __('Category'), ['class' => VC::FM_LB])); ?>

                                                <?php echo e(Form::select('type', $types, isset($_GET['type']) ? $_GET['type'] : '', ['class' => VC::FM_CT_SL, 'placeholder' => __('Select Category')])); ?>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="<?php echo e(VC::C_AT); ?>">
                                    <div class="<?php echo e(VC::RW); ?>">
                                        <div class="<?php echo e(VC::C_AT); ?> <?php echo e(VC::MT4); ?>">
                                            <a id="apply-account-statement"
                                            href="#"
                                            class="<?php echo e(VC::BT_SM_PM); ?>"
                                            data-form-id="report_account"
                                            data-guard-msg="<?php echo e($applyGuardMsg); ?>"
                                            data-sv-localized="true"
                                            data-bs-toggle="tooltip"
                                            title="<?php echo e(__('Apply')); ?>"
                                            data-original-title="<?php echo e(__('apply')); ?>">
                                                <span class="btn-inner--icon"><i class="<?php echo e(VC::TI_SRC); ?>"></i></span>
                                            </a>
                                            <a id="reset-account-statement"
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
                            <script src="<?php echo e(asset('assets/js/routes/reports/accountStatements/apply.js')); ?>" defer></script>
                            <script src="<?php echo e(asset('assets/js/routes/reports/accountStatements/reset.js')); ?>" defer></script>
                        <?php $__env->stopPush(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="printableArea">
        <div class="<?php echo e(VC::RW); ?> <?php echo e(VC::MT3); ?>">
            <div class="col">
                <input type="hidden"
                       id="filename"
                       value="<?php echo e(__('Account Statement') . ' ' . $filter['type'] . ' ' . __('Report of') . ' ' . $filter['startDateRange'] . ' ' . __('to') . ' ' . $filter['endDateRange']); ?>">
                <div class="<?php echo e(VC::CD_POS); ?>">
                    <h7 class="<?php echo e(VC::RPT_TX_GR); ?>"><?php echo e(__('Report')); ?> :</h7>
                    <h6 class="<?php echo e(VC::RPT_TX_DEF); ?>"><?php echo e(__('Account Statement Summary')); ?></h6>
                </div>
            </div>

            <?php if($filter['account'] != __('All')): ?>
                <div class="col">
                    <div class="<?php echo e(VC::CD_POS); ?>">
                        <h7 class="<?php echo e(VC::RPT_TX_GR); ?>"><?php echo e(__('Account')); ?> :</h7>
                        <h6 class="<?php echo e(VC::RPT_TX_DEF); ?>"><?php echo e($filter['account']); ?></h6>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($filter['type'] != __('All')): ?>
                <div class="col">
                    <div class="<?php echo e(VC::CD_POS); ?>">
                        <h7 class="<?php echo e(VC::RPT_TX_GR); ?>"><?php echo e(__('Type')); ?> :</h7>
                        <h6 class="<?php echo e(VC::RPT_TX_DEF); ?>"><?php echo e($filter['type']); ?></h6>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col">
                <div class="<?php echo e(VC::CD_POS); ?>">
                    <h7 class="<?php echo e(VC::RPT_TX_GR); ?>"><?php echo e(__('Duration')); ?> :</h7>
                    <h6 class="<?php echo e(VC::RPT_TX_DEF); ?>"><?php echo e($filter['startDateRange'] . ' ' . __('to') . ' ' . $filter['endDateRange']); ?></h6>
                </div>
            </div>
        </div>

        <?php if(!empty($reportData['revenueAccounts'])): ?>
            <div class="<?php echo e(VC::RW); ?>">
                <?php $__currentLoopData = $reportData['revenueAccounts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="<?php echo e(VC::CL_XL3); ?>">
                        <div class="<?php echo e(VC::CD_POS); ?>">
                            <?php if($acc->holder_name == 'Cash'): ?>
                                <h7 class="<?php echo e(VC::RPT_TX_GR); ?>"><?php echo e($acc->holder_name); ?></h7>
                            <?php elseif(empty($acc->holder_name)): ?>
                                <h7 class="<?php echo e(VC::RPT_TX_GR); ?>"><?php echo e(__('Stripe / PayPal')); ?></h7>
                            <?php else: ?>
                                <h7 class="<?php echo e(VC::RPT_TX_GR); ?>"><?php echo e($acc->holder_name . ' - ' . $acc->bank_name); ?></h7>
                            <?php endif; ?>
                            <h6 class="<?php echo e(VC::RPT_TX_DEF); ?>"><?php echo e($user?->priceFormat($acc->total)); ?></h6>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($reportData['paymentAccounts'])): ?>
            <div class="<?php echo e(VC::RW); ?>">
                <?php $__currentLoopData = $reportData['paymentAccounts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="<?php echo e(VC::CL_XL3); ?>">
                        <div class="<?php echo e(VC::CD_POS); ?>">
                            <?php if($acc->holder_name == 'Cash'): ?>
                                <h7 class="<?php echo e(VC::RPT_TX_GR); ?>"><?php echo e($acc->holder_name); ?></h7>
                            <?php elseif(empty($acc->holder_name)): ?>
                                <h7 class="<?php echo e(VC::RPT_TX_GR); ?>"><?php echo e(__('Stripe / PayPal')); ?></h7>
                            <?php else: ?>
                                <h7 class="<?php echo e(VC::RPT_TX_GR); ?>"><?php echo e($acc->holder_name . ' - ' . $acc->bank_name); ?></h7>
                            <?php endif; ?>
                            <h6 class="<?php echo e(VC::RPT_TX_DEF); ?>"><?php echo e($user?->priceFormat($acc->total)); ?></h6>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php
        $revTotal = 0.0;
        $payTotal = 0.0;
        if (!empty($reportData['revenues'])) {
            foreach ($reportData['revenues'] as $r) { $revTotal += (float) $r->amount; }
        }
        if (!empty($reportData['payments'])) {
            foreach ($reportData['payments'] as $p) { $payTotal += (float) $p->amount; }
        }
        $netTotal = $revTotal - $payTotal;
    ?>

    <div class="<?php echo e(VC::RW); ?>">
        <div class="<?php echo e(VC::CM12); ?>">
            <div class="<?php echo e(VC::CD); ?>">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="<?php echo e(VC::TB); ?> datatable" id="account-statement-table">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Date')); ?></th>
                                    <th class="text-end"><?php echo e(__('Amount')); ?></th>
                                    <th><?php echo e(__('Description')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $hasRows = false; ?>

                                <?php if(!empty($reportData['revenues'])): ?>
                                    <?php $__currentLoopData = $reportData['revenues']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $revenue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php $hasRows = true; ?>
                                        <tr class="font-style">
                                            <td><?php echo e($user?->dateFormat($revenue->date)); ?></td>
                                            <td class="text-end"><?php echo e($user?->priceFormat($revenue->amount)); ?></td>
                                            <td><?php echo e($revenue->description); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>

                                <?php if(!empty($reportData['payments'])): ?>
                                    <?php $__currentLoopData = $reportData['payments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php $hasRows = true; ?>
                                        <tr class="font-style">
                                            <td><?php echo e($user?->dateFormat($payment->date)); ?></td>
                                            <td class="text-end"><?php echo e($user?->priceFormat($payment->amount) ?? __('Failed to fetch user data.')); ?></td>
                                            <td><?php echo e(!empty($payment->description) ? $payment->description : __('No description.')); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>

                                <?php if (! ($hasRows)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted"><?php echo e(__('No transactions found for the selected period.')); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>

                            <?php if($hasRows): ?>
                                <tfoot>
                                    <tr>
                                        <th class="text-end"><?php echo e(__('Total Revenue')); ?></th>
                                        <th class="text-end"><?php echo e($user?->priceFormat($revTotal) ?? __('Failed to fetch user data.')); ?></th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th class="text-end"><?php echo e(__('Total Payments')); ?></th>
                                        <th class="text-end"><?php echo e($user?->priceFormat($payTotal) ?? __('Failed to fetch user data.')); ?></th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th class="text-end"><?php echo e(__('Net Total')); ?></th>
                                        <th class="text-end"><?php echo e($user?->priceFormat($netTotal) ?? __('Failed to fetch user data.')); ?></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/reports/statement_report.blade.php ENDPATH**/ ?>