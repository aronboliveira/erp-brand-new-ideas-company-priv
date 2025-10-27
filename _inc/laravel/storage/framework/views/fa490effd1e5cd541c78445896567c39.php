<?php

use App\Config\Constants\{
    DatabaseConstants,
    ExtendingLayoutsConstants,
    PermissionsConstants,
    ProjectsConstants,
    SettingsConstants,
    StacksConstants,
    UsersConstants,
    ViewsConstants as VW,
    ViewClassNamesConstants as VC,
    YieldingConstants,
};
use App\Models\{Bill, Goal, Invoice, Plan, User, Utility};
use Illuminate\Database\{Eloquent\ModelNotFoundException, QueryException};
use Illuminate\Support\Facades\{Auth, Log, Route};

$user = Auth::user();
$lang = Utility::fetchUserLang(user: $user);
$plan ??= Plan::find(DatabaseConstants::DEFAULT_PLAN);
$canAv = method_exists($user, 'can');
?>

<?php $__env->startSection(YieldingConstants::ADM_PG_TTL); ?>
<?php echo e(__('Dashboard')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
<?php if ($canAv && ($user?->can(PermissionsConstants::SHW_ACC_DSB) || $user[UsersConstants::COL_TP] == PermissionsConstants::SA)): ?>
    <?php
    if (!$user?->can(PermissionsConstants::SHW_ACC_DSB) && $user[UsersConstants::COL_TP] == PermissionsConstants::SA)
        Log::notice(
            'User is a super admin bypassing. Be sure this is intended or report.',
            [
                'user_id' => $user->id,
                'user_type' => $user[UsersConstants::COL_TP],
                'permission' => PermissionsConstants::SHW_ACC_DSB
            ]
        );
    ?>
    <script async src="<?php echo e(asset('assets/js/routes/dashboards/account/lang/cash.js')); ?>"></script>
    <script defer>
        (() => {
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";

            const getLocalizedMessage = (el, key) => {
                let msg = errFb;
                if (
                    el.getAttribute("data-sv-localized") === "true" ||
                    el.getAttribute(dataClientLocalized) === "true"
                ) {
                    msg = el.getAttribute(dataGuardMsg) ?? errFb;
                } else {
                    let lang = (
                            window.sessionStorage.getItem("erp-np-lang") ||
                            document.documentElement.lang ||
                            "en"
                        )
                        .toLowerCase()
                        .replace(/_/g, "-");
                    lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                    msg =
                        window.translations?.[lang]?.[key] ??
                        el.getAttribute(dataGuardMsg) ??
                        window.translations?.["en"]?.[key] ??
                        errFb;
                    if (msg !== errFb) {
                        el.setAttribute(dataGuardMsg, msg);
                        el.setAttribute(dataClientLocalized, "true");
                    }
                }
                return msg;
            };

            const showErrorUI = msg => {
                const bsLink = document.querySelector('link[href*="bootstrap"]');
                if (bsLink && window.bootstrap?.Toast) {
                    if (!document.querySelector("#fail-toast")) {
                        const toast = document.createElement("div");
                        toast.id = "fail-toast";
                        toast.className =
                            "toast align-items-center text-white bg-danger border-0 position-fixed bottom-0 end-0 m-3";
                        toast.setAttribute("role", "alert");
                        toast.setAttribute("aria-live", "assertive");
                        toast.setAttribute("aria-atomic", "true");
                        toast.innerHTML = `
                        <div class="d-flex">
                            <div class="toast-body">${msg}</div>
                            <button
                            type="button"
                            class="btn-close btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast"
                            aria-label="Close"
                            ></button>
                        </div>`;
                        document.body.append(toast);
                        new bootstrap.Toast(toast).show();
                    }
                } else {
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error(msg);
                }
            };

            const renderCashFlow = () => {
                try {
                    const el = document.querySelector("#cash-flow");
                    if (!el || !window.ApexCharts) throw new Error();
                    const options = {
                        series: [{
                                name: "<?php echo e(__('Income')); ?>",
                                data: <?php echo json_encode($incExpLineChartData['income']); ?>
                            },
                            {
                                name: "<?php echo e(__('Expense')); ?>",
                                data: <?php echo json_encode($incExpLineChartData['expense']); ?>
                            }
                        ],
                        chart: {
                            height: 250,
                            type: "area",
                            dropShadow: {
                                enabled: true,
                                color: "#000",
                                top: 18,
                                left: 7,
                                blur: 10,
                                opacity: 0.2
                            },
                            toolbar: {
                                show: false
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            width: 2,
                            curve: "smooth"
                        },
                        title: {
                            text: "",
                            align: "left"
                        },
                        xaxis: {
                            categories: <?php echo json_encode($incExpLineChartData['day']); ?>,
                            title: {
                                text: "<?php echo e(__('Date')); ?>"
                            }
                        },
                        colors: ["#6fd944", "#ff3a6e"],
                        grid: {
                            strokeDashArray: 4
                        },
                        legend: {
                            show: false
                        },
                        yaxis: {
                            title: {
                                text: "<?php echo e(__('Amount')); ?>"
                            }
                        }
                    };
                    new ApexCharts(el, options).render();
                } catch {
                    showErrorUI(getLocalizedMessage(document.body, "cash_flow_unavailable"));
                }
            };

            const renderIncExpBar = () => {
                try {
                    const el = document.querySelector("#incExpBarChart");
                    if (!el || !window.ApexCharts) throw new Error();
                    const options = {
                        chart: {
                            height: 180,
                            type: "bar",
                            toolbar: {
                                show: false
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            width: 2,
                            curve: "smooth"
                        },
                        series: [{
                                name: "<?php echo e(__('Income')); ?>",
                                data: <?php echo json_encode($incExpBarChartData['income']); ?>
                            },
                            {
                                name: "<?php echo e(__('Expense')); ?>",
                                data: <?php echo json_encode($incExpBarChartData['expense']); ?>
                            }
                        ],
                        xaxis: {
                            categories: <?php echo json_encode($incExpBarChartData['month']); ?>
                        },
                        colors: ["#3ec9d6", "#FF3A6E"],
                        fill: {
                            type: "solid"
                        },
                        grid: {
                            strokeDashArray: 4
                        },
                        legend: {
                            show: true,
                            position: "top",
                            horizontalAlign: "right"
                        }
                    };
                    new ApexCharts(el, options).render();
                } catch {
                    showErrorUI(getLocalizedMessage(document.body, "incExpBarChart_unavailable"));
                }
            };

            const renderExpenseByCategory = () => {
                try {
                    const el = document.querySelector("#expenseByCategory");
                    if (!el || !window.ApexCharts) throw new Error();
                    const options = {
                        chart: {
                            height: 140,
                            type: "donut"
                        },
                        dataLabels: {
                            enabled: false
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: "70%"
                                }
                            }
                        },
                        series: <?php echo json_encode($expenseCatAmount); ?>,
                        colors: <?php echo json_encode($expenseCategoryColor); ?>,
                        labels: <?php echo json_encode($expenseCategory); ?>,
                        legend: {
                            show: true
                        }
                    };
                    new ApexCharts(el, options).render();
                } catch {
                    showErrorUI(getLocalizedMessage(document.body, "expenseByCategory_unavailable"));
                }
            };

            const renderIncomeByCategory = () => {
                try {
                    const el = document.querySelector("#incomeByCategory");
                    if (!el || !window.ApexCharts) throw new Error();
                    const options = {
                        chart: {
                            height: 140,
                            type: "donut"
                        },
                        dataLabels: {
                            enabled: false
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: "70%"
                                }
                            }
                        },
                        series: <?php echo json_encode($incomeCatAmount); ?>,
                        colors: <?php echo json_encode($incomeCategoryColor); ?>,
                        labels: <?php echo json_encode($incomeCategory); ?>,
                        legend: {
                            show: true
                        }
                    };
                    new ApexCharts(el, options).render();
                } catch {
                    showErrorUI(getLocalizedMessage(document.body, "incomeByCategory_unavailable"));
                }
            };

            const renderLimitChart = () => {
                try {
                    const el = document.querySelector("#limit-chart");
                    if (!el || !window.ApexCharts) throw new Error();
                    const options = {
                        series: [<?php echo e(round($storage_limit, 2)); ?>],
                        chart: {
                            height: 350,
                            type: "radialBar",
                            offsetY: -20,
                            sparkline: {
                                enabled: true
                            }
                        },
                        plotOptions: {
                            radialBar: {
                                startAngle: -90,
                                endAngle: 90,
                                track: {
                                    background: "#e7e7e7",
                                    strokeWidth: "97%",
                                    margin: 5
                                },
                                dataLabels: {
                                    name: {
                                        show: true
                                    },
                                    value: {
                                        offsetY: -50,
                                        fontSize: "20px"
                                    }
                                }
                            }
                        },
                        grid: {
                            padding: {
                                top: -10
                            }
                        },
                        colors: ["#6FD943"],
                        labels: ["Used"]
                    };
                    new ApexCharts(el, options).render();
                } catch {
                    showErrorUI(getLocalizedMessage(document.body, "limitChart_unavailable"));
                }
            };

            renderCashFlow();
            renderIncExpBar();
            renderExpenseByCategory();
            renderIncomeByCategory();
            renderLimitChart();
        })();
    </script>
<?php endif; ?>
<?php $__env->stopPush(); ?>
<?php $__env->startSection(YieldingConstants::ADM_BDC); ?>
<li class="breadcrumb-item">
    <a href="<?php echo e(Route::has('dashboard') ? route('dashboard') : '#'); ?>"
        <?php echo e(Route::has('dashboard') ? '' : 'aria-disabled="true"'); ?>>
        <?php echo e(__('Dashboard')); ?>

    </a>
</li>
<li class="breadcrumb-item"><?php echo e(__('Account')); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-sm-12">
        <div class="row">
            <?php
            $metrics = [
                ['bg' => 'bg-primary', 'icon' => VC::TI_USRS, 'label' => __('Customers'), 'value' => is_callable($user, 'countCustomers') ? $user->countCustomers() : 0],
                ['bg' => 'bg-info', 'icon' => VC::TI_USRS, 'label' => __('Vendors'), 'value' => is_callable($user, 'countVendors') ? $user->countVendors() : 0],
                ['bg' => 'bg-warning', 'icon' => 'ti ti-report-money', 'label' => __('Invoices'), 'value' => is_callable($user, 'countInvoices') ? $user->countInvoices() : 0],
                ['bg' => 'bg-danger', 'icon' => 'ti ti-report-money', 'label' => __('Bills'), 'value' => is_callable($user, 'countBills') ? $user->countBills() : 0]
            ];
            $currentYear ??= (string) now()->format('Y');
            $bankAccountDetail ??= [];
            $latestIncome ??= [];
            $latestExpense ??= [];
            $recentInvoice ??= [];
            $recentBill ??= [];
            $asString = static function ($value, string $alias) {
                return isset($value) && is_string($value) && trim($value) !== ''
                    ? $value
                    : __('No ' . $alias . ' available');
            };
            $asClass = static function ($value, string $fallback = 'bg-secondary') {
                return isset($value) && is_string($value) && trim($value) !== ''
                    ? $value
                    : $fallback;
            };
            $asNumber = static function ($value, string $alias) {
                return isset($value) && is_numeric($value)
                    ? $value
                    : __('Could not find ' . $alias);
            };
            $fmtDate = static function ($value) use ($user, $asString) {
                try {
                    return isset($user) && method_exists($user, 'dateFormat')
                        ? $user->dateFormat($value ?? null)
                        : $asString($value, 'date');
                } catch (ModelNotFoundException $e) {
                    Log::error('dateFormat model not found', [
                        'file' => __FILE__,
                        'line' => __LINE__,
                        'class' => $e::class,
                        'message' => $e->getMessage(),
                    ]);
                    return __('Failed to get date');
                } catch (QueryException $e) {
                    Log::error('dateFormat query error', [
                        'file' => __FILE__,
                        'line' => __LINE__,
                        'class' => $e::class,
                        'message' => $e->getMessage(),
                    ]);
                    return __('Failed to get date');
                } catch (\TypeError $e) {
                    Log::error('dateFormat type error', [
                        'file' => __FILE__,
                        'line' => __LINE__,
                        'class' => $e::class,
                        'message' => $e->getMessage(),
                    ]);
                    return __('Failed to get date');
                } catch (\Throwable $e) {
                    Log::error('dateFormat error', [
                        'file' => __FILE__,
                        'line' => __LINE__,
                        'class' => $e::class,
                        'message' => $e->getMessage(),
                    ]);
                    return __('Failed to get date');
                }
            };
            $fmtPrice = static function ($value) use ($user, $asNumber) {
                try {
                    return isset($user) && method_exists($user, 'priceFormat')
                        ? $user->priceFormat($value ?? 0)
                        : (is_numeric($value) ? number_format((float) $value, 2, '.', ',')
                            : $asNumber($value, 'amount'));
                } catch (ModelNotFoundException $e) {
                    Log::error('priceFormat model not found', [
                        'file' => __FILE__,
                        'line' => __LINE__,
                        'class' => $e::class,
                        'message' => $e->getMessage(),
                    ]);
                    return __('Failed to get amount');
                } catch (QueryException $e) {
                    Log::error('priceFormat query error', [
                        'file' => __FILE__,
                        'line' => __LINE__,
                        'class' => $e::class,
                        'message' => $e->getMessage(),
                    ]);
                    return __('Failed to get amount');
                } catch (\TypeError $e) {
                    Log::error('priceFormat type error', [
                        'file' => __FILE__,
                        'line' => __LINE__,
                        'class' => $e::class,
                        'message' => $e->getMessage(),
                    ]);
                    return __('Failed to get amount');
                } catch (\Throwable $e) {
                    Log::error('priceFormat error', [
                        'file' => __FILE__,
                        'line' => __LINE__,
                        'class' => $e::class,
                        'message' => $e->getMessage(),
                    ]);
                    return __('Failed to get amount');
                }
            };
            $fmtInv = static function ($value) use ($user, $asString) {
                try {
                    return isset($user) && method_exists($user, 'invoiceNumberFormat')
                        ? $user->invoiceNumberFormat($value ?? null)
                        : $asString($value, 'invoice number');
                } catch (\Throwable $e) {
                    Log::error('invoiceNumberFormat error', [
                        'file' => __FILE__,
                        'line' => __LINE__,
                        'class' => $e::class,
                        'message' => $e->getMessage(),
                    ]);
                    return __('Failed to get invoice number');
                }
            };
            $fmtBill = static function ($value) use ($user, $asString) {
                try {
                    return isset($user) && method_exists($user, 'billNumberFormat')
                        ? $user->billNumberFormat($value ?? null)
                        : $asString($value, 'bill number');
                } catch (\Throwable $e) {
                    Log::error('billNumberFormat error', [
                        'file' => __FILE__,
                        'line' => __LINE__,
                        'class' => $e::class,
                        'message' => $e->getMessage(),
                    ]);
                    return __('Failed to get bill number');
                }
            };
            $invoiceStatusClasses ??= [
                0 => 'bg-secondary',
                1 => 'bg-warning',
                2 => 'bg-danger',
                3 => 'bg-info',
                4 => 'bg-primary',
            ];
            $billStatusClasses ??= $invoiceStatusClasses;
            try {
                $metrics = is_iterable($metrics) ? $metrics : [];
            } catch (\Throwable $e) {
                Log::error('metrics iteration error', [
                    'file' => __FILE__,
                    'line' => __LINE__,
                    'class' => $e::class,
                    'message' => $e->getMessage(),
                ]);
                $metrics = [];
            }
            try {
                $bankAccountDetail = is_iterable($bankAccountDetail) ? $bankAccountDetail : [];
            } catch (\Throwable $e) {
                Log::error('bankAccountDetail iteration error', [
                    'file' => __FILE__,
                    'line' => __LINE__,
                    'class' => $e::class,
                    'message' => $e->getMessage(),
                ]);
                $bankAccountDetail = [];
            }
            try {
                $latestIncome = is_iterable($latestIncome) ? $latestIncome : [];
            } catch (\Throwable $e) {
                Log::error('latestIncome iteration error', [
                    'file' => __FILE__,
                    'line' => __LINE__,
                    'class' => $e::class,
                    'message' => $e->getMessage(),
                ]);
                $latestIncome = [];
            }
            try {
                $latestExpense = is_iterable($latestExpense) ? $latestExpense : [];
            } catch (\Throwable $e) {
                Log::error('latestExpense iteration error', [
                    'file' => __FILE__,
                    'line' => __LINE__,
                    'class' => $e::class,
                    'message' => $e->getMessage(),
                ]);
                $latestExpense = [];
            }
            try {
                $recentInvoice = is_iterable($recentInvoice) ? $recentInvoice : [];
            } catch (\Throwable $e) {
                Log::error('recentInvoice iteration error', [
                    'file' => __FILE__,
                    'line' => __LINE__,
                    'class' => $e::class,
                    'message' => $e->getMessage(),
                ]);
                $recentInvoice = [];
            }
            try {
                $recentBill = is_iterable($recentBill) ? $recentBill : [];
            } catch (\Throwable $e) {
                Log::error('recentBill iteration error', [
                    'file' => __FILE__,
                    'line' => __LINE__,
                    'class' => $e::class,
                    'message' => $e->getMessage(),
                ]);
                $recentBill = [];
            }
            ?>
            <div class="col-xxl-7">
                <div class="<?php echo e(VC::RW); ?>">
                    <div class="col-md-12">
                        <div class="<?php echo e(VC::RW); ?>">
                            <?php $__currentLoopData = $metrics;
                            $__env->addLoop($__currentLoopData);
                            foreach ($__currentLoopData as $m): $__env->incrementLoopIndices();
                                $loop = $__env->getLastLoop(); ?>
                                <?php
                                $bg = $asClass(data_get($m, 'bg'));
                                $icon = $asClass(data_get($m, 'icon'), 'ti ti-help');
                                $label = $asString(data_get($m, 'label'), 'label');
                                $value = $asNumber(data_get($m, 'value'), 'value');
                                ?>
                                <div class="col-lg-3 col-6">
                                    <div class="<?php echo e(VC::CD); ?>">
                                        <div class="card-body">
                                            <div class="theme-avatar <?php echo e($bg); ?>">
                                                <i class="<?php echo e($icon); ?>"></i>
                                            </div>
                                            <p class="<?php echo e(VC::TXT_MT); ?> <?php echo e(VC::TXSM); ?> mt-4 mb-2">
                                                <?php echo e(__('Total')); ?>

                                            </p>
                                            <h6 class="<?php echo e(VC::MB3); ?>"><?php echo e($label); ?></h6>
                                            <h3 class="<?php echo e(VC::MB0); ?>"><?php echo e($value); ?></h3>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach;
                            $__env->popLoop();
                            $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-12">
                    <div class="<?php echo e(VC::CD); ?>">
                        <div class="card-header">
                            <h5>
                                <?php echo e(__('Income & Expense')); ?>

                                <span class="<?php echo e(VC::FEND); ?> <?php echo e(VC::TXT_MT); ?>">
                                    <?php echo e(($currentYear ?: __('Could not find year'))); ?>

                                </span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="incExpBarChart"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="<?php echo e(VC::CD); ?>">
                        <div class="card-header">
                            <h5 class="<?php echo e(VC::MT1); ?> <?php echo e(VC::MB0); ?>"><?php echo e(__('Account Balance')); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="<?php echo e(VC::TB); ?>">
                                    <thead>
                                        <tr>
                                            <th><?php echo e(__('Bank')); ?></th>
                                            <th><?php echo e(__('Holder Name')); ?></th>
                                            <th><?php echo e(__('Balance')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true;
                                        $__currentLoopData = $bankAccountDetail;
                                        $__env->addLoop($__currentLoopData);
                                        foreach ($__currentLoopData as $account): $__env->incrementLoopIndices();
                                            $loop = $__env->getLastLoop();
                                            $__empty_1 = false; ?>
                                            <?php
                                            $bankName = $asString(data_get($account, 'bank_name'), 'bank name');
                                            $holder = $asString(data_get($account, 'holder_name'), 'holder name');
                                            $balance = $fmtPrice(data_get($account, 'opening_balance'));
                                            ?>
                                            <tr class="font-style">
                                                <td><?php echo e($bankName); ?></td>
                                                <td><?php echo e($holder); ?></td>
                                                <td><?php echo e($balance); ?></td>
                                            </tr>
                                        <?php endforeach;
                                        $__env->popLoop();
                                        $loop = $__env->getLastLoop();
                                        if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="4">
                                                    <div class="text-center">
                                                        <h6><?php echo e(__('There is no account balance')); ?></h6>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-12">
                    <div class="<?php echo e(VC::CD); ?>">
                        <div class="card-header">
                            <h5 class="<?php echo e(VC::MT1); ?> <?php echo e(VC::MB0); ?>"><?php echo e(__('Latest Income')); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="<?php echo e(VC::TB); ?>">
                                    <thead>
                                        <tr>
                                            <th><?php echo e(__('Date')); ?></th>
                                            <th><?php echo e(__('Customer')); ?></th>
                                            <th><?php echo e(__('Amount Due')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true;
                                        $__currentLoopData = $latestIncome;
                                        $__env->addLoop($__currentLoopData);
                                        foreach ($__currentLoopData as $income): $__env->incrementLoopIndices();
                                            $loop = $__env->getLastLoop();
                                            $__empty_1 = false; ?>
                                            <?php
                                            $incDate = $fmtDate(data_get($income, 'date'));
                                            $incCust = $asString(
                                                data_get($income, 'customer.name'),
                                                'customer name'
                                            );
                                            $incAmt = $fmtPrice(data_get($income, 'amount'));
                                            ?>
                                            <tr>
                                                <td><?php echo e($incDate); ?></td>
                                                <td><?php echo e($incCust); ?></td>
                                                <td><?php echo e($incAmt); ?></td>
                                            </tr>
                                        <?php endforeach;
                                        $__env->popLoop();
                                        $loop = $__env->getLastLoop();
                                        if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="4">
                                                    <div class="text-center">
                                                        <h6><?php echo e(__('There is no latest income')); ?></h6>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-12">
                    <div class="<?php echo e(VC::CD); ?>">
                        <div class="card-header">
                            <h5 class="<?php echo e(VC::MT1); ?> <?php echo e(VC::MB0); ?>"><?php echo e(__('Latest Expense')); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="<?php echo e(VC::TB); ?>">
                                    <thead>
                                        <tr>
                                            <th><?php echo e(__('Date')); ?></th>
                                            <th><?php echo e(__('Vendor')); ?></th>
                                            <th><?php echo e(__('Amount Due')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true;
                                        $__currentLoopData = $latestExpense;
                                        $__env->addLoop($__currentLoopData);
                                        foreach ($__currentLoopData as $expense): $__env->incrementLoopIndices();
                                            $loop = $__env->getLastLoop();
                                            $__empty_1 = false; ?>
                                            <?php
                                            $expDate = $fmtDate(data_get($expense, 'date'));
                                            $expVend = $asString(
                                                data_get($expense, 'vendor.name'),
                                                'vendor name'
                                            );
                                            $expAmt = $fmtPrice(data_get($expense, 'amount'));
                                            ?>
                                            <tr>
                                                <td><?php echo e($expDate); ?></td>
                                                <td><?php echo e($expVend); ?></td>
                                                <td><?php echo e($expAmt); ?></td>
                                            </tr>
                                        <?php endforeach;
                                        $__env->popLoop();
                                        $loop = $__env->getLastLoop();
                                        if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="4">
                                                    <div class="text-center">
                                                        <h6><?php echo e(__('There is no latest expense')); ?></h6>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-12">
                    <div class="<?php echo e(VC::CD); ?>">
                        <div class="card-header">
                            <h5 class="<?php echo e(VC::MT1); ?> <?php echo e(VC::MB0); ?>"><?php echo e(__('Recent Invoices')); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="<?php echo e(VC::TB); ?>">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php echo e(__('Customer')); ?></th>
                                            <th><?php echo e(__('Issue Date')); ?></th>
                                            <th><?php echo e(__('Due Date')); ?></th>
                                            <th><?php echo e(__('Amount')); ?></th>
                                            <th><?php echo e(__('Status')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true;
                                        $__currentLoopData = $recentInvoice;
                                        $__env->addLoop($__currentLoopData);
                                        foreach ($__currentLoopData as $invoice): $__env->incrementLoopIndices();
                                            $loop = $__env->getLastLoop();
                                            $__empty_1 = false; ?>
                                            <?php
                                            $invNo = $fmtInv(data_get($invoice, 'invoice_id'));
                                            $invCust = $asString(
                                                data_get($invoice, 'customer.name'),
                                                'customer name'
                                            );
                                            $invIssue = $fmtDate(data_get($invoice, 'issue_date'));
                                            $invDue = $fmtDate(data_get($invoice, 'due_date'));
                                            $invTotal = $fmtPrice(
                                                method_exists($invoice, 'getTotal')
                                                    ? $invoice->getTotal()
                                                    : data_get($invoice, 'total')
                                            );
                                            $stIdx = data_get($invoice, 'status');
                                            $bgClass = $invoiceStatusClasses[$stIdx] ?? null;
                                            $stText = is_array(Invoice::$statuses ?? null)
                                                ? data_get(Invoice::$statuses, $stIdx)
                                                : null;
                                            $stText = $asString($stText, 'status');
                                            ?>
                                            <tr>
                                                <td><?php echo e($invNo); ?></td>
                                                <td><?php echo e($invCust); ?></td>
                                                <td><?php echo e($invIssue); ?></td>
                                                <td><?php echo e($invDue); ?></td>
                                                <td><?php echo e($invTotal); ?></td>
                                                <td>
                                                    <?php if ($bgClass): ?>
                                                        <span class="p-2 px-3 rounded <?php echo e(VC::BDG); ?> <?php echo e($bgClass); ?>">
                                                            <?php echo e(__($stText)); ?>

                                                        </span>
                                                    <?php else: ?>
                                                        <span class="p-2 px-3 rounded <?php echo e(VC::BDG); ?> bg-secondary">
                                                            <?php echo e(__('No status available')); ?>

                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach;
                                        $__env->popLoop();
                                        $loop = $__env->getLastLoop();
                                        if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="6">
                                                    <div class="text-center">
                                                        <h6><?php echo e(__('There is no recent invoice')); ?></h6>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-12">
                    <div class="<?php echo e(VC::CD); ?>">
                        <div class="card-header">
                            <h5 class="<?php echo e(VC::MT1); ?> <?php echo e(VC::MB0); ?>"><?php echo e(__('Recent Bills')); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="<?php echo e(VC::TB); ?>">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th><?php echo e(__('Vendor')); ?></th>
                                            <th><?php echo e(__('Bill Date')); ?></th>
                                            <th><?php echo e(__('Due Date')); ?></th>
                                            <th><?php echo e(__('Amount')); ?></th>
                                            <th><?php echo e(__('Status')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true;
                                        $__currentLoopData = $recentBill;
                                        $__env->addLoop($__currentLoopData);
                                        foreach ($__currentLoopData as $bill): $__env->incrementLoopIndices();
                                            $loop = $__env->getLastLoop();
                                            $__empty_1 = false; ?>
                                            <?php
                                            $blNo = $fmtBill(data_get($bill, 'bill_id'));
                                            $blVend = $asString(
                                                data_get($bill, 'vendor.name'),
                                                'vendor name'
                                            );
                                            $blDate = $fmtDate(data_get($bill, 'bill_date'));
                                            $blDue = $fmtDate(data_get($bill, 'due_date'));
                                            $blTotal = $fmtPrice(
                                                method_exists($bill, 'getTotal')
                                                    ? $bill->getTotal()
                                                    : data_get($bill, 'total')
                                            );
                                            $blIdx = data_get($bill, 'status');
                                            $bgClass = $billStatusClasses[$blIdx] ?? null;
                                            $blText = is_array(Bill::$statuses ?? null)
                                                ? data_get(Bill::$statuses, $blIdx)
                                                : null;
                                            $blText = $asString($blText, 'status');
                                            ?>
                                            <tr>
                                                <td><?php echo e($blNo); ?></td>
                                                <td><?php echo e($blVend); ?></td>
                                                <td><?php echo e($blDate); ?></td>
                                                <td><?php echo e($blDue); ?></td>
                                                <td><?php echo e($blTotal); ?></td>
                                                <td>
                                                    <?php if ($bgClass): ?>
                                                        <span class="p-2 px-3 rounded <?php echo e(VC::BDG); ?> <?php echo e($bgClass); ?>">
                                                            <?php echo e(__($blText)); ?>

                                                        </span>
                                                    <?php else: ?>
                                                        <span class="p-2 px-3 rounded <?php echo e(VC::BDG); ?> bg-secondary">
                                                            <?php echo e(__('No status available')); ?>

                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach;
                                        $__env->popLoop();
                                        $loop = $__env->getLastLoop();
                                        if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="6">
                                                    <div class="text-center">
                                                        <h6><?php echo e(__('There is no recent bill')); ?></h6>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            $tiles ??= [];
            $weeklyInvoice ??= [];
            $monthlyInvoice ??= [];
            $goals ??= [];
            $storage ??= __('Could not find storage limits');
            $decimalNumber ??= 2;
            try {
                $decimalNumber = (int) (Utility::getValByName('decimal_number') ?? 2);
            } catch (ModelNotFoundException $e) {
                Log::error('decimal number setting model not found', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
            } catch (QueryException $e) {
                Log::error('decimal number setting query error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
            } catch (\TypeError $e) {
                Log::error('decimal number setting type error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
            } catch (\Throwable $e) {
                Log::error('decimal number setting error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
            }

            try {
                $incomeToday = (isset($user) && method_exists($user, 'todayIncome')) ? $user->todayIncome() : null;
                $expenseToday = (isset($user) && method_exists($user, 'todayExpense')) ? $user->todayExpense() : null;
                $incomeMonth = (isset($user) && method_exists($user, 'incomeCurrentMonth')) ? $user->incomeCurrentMonth() : null;
                $expenseMonth = (isset($user) && method_exists($user, 'expenseCurrentMonth')) ? $user->expenseCurrentMonth() : null;

                $tiles = [
                    ['label' => __('Income Today'), 'value' => $fmtPrice($incomeToday), 'avatarBg' => 'bg-primary', 'icon' => 'ti-report-money', 'textClass' => 'text-success'],
                    ['label' => __('Expense Today'), 'value' => $fmtPrice($expenseToday), 'avatarBg' => 'bg-info', 'icon' => 'ti-file-invoice', 'textClass' => 'text-info'],
                    ['label' => __('Income This Month'), 'value' => $fmtPrice($incomeMonth), 'avatarBg' => 'bg-warning', 'icon' => 'ti-report-money', 'textClass' => 'text-warning'],
                    ['label' => __('Expense This Month'), 'value' => $fmtPrice($expenseMonth), 'avatarBg' => 'bg-danger', 'icon' => 'ti-file-invoice', 'textClass' => 'text-danger'],
                ];
            } catch (\Error $e) {
                Log::error('tiles build fatal error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                $tiles = [];
            } catch (\Throwable $e) {
                Log::error('tiles build error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                $tiles = [];
            }

            try {
                $weeklyInvoice = Utility::isFilled($weeklyInvoice ?? []) ? $weeklyInvoice : [];
                $monthlyInvoice = Utility::isFilled($monthlyInvoice ?? []) ? $monthlyInvoice : [];
            } catch (\Throwable $e) {
                Log::error('invoice stats validation error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                $weeklyInvoice = [];
                $monthlyInvoice = [];
            }

            try {
                if (($user ?? null) instanceof User && ($plan ?? null) instanceof Plan && isset($user->storage_limit, $plan->storage_limit)) {
                    $storage = (string) $user->storage_limit . 'MB / ' . (string) $plan->storage_limit . 'MB';
                } else {
                    $storage = __('Max') . ' ' . (string) SettingsConstants::MAX_SL_LIMIT_MB . 'MB';
                }
            } catch (\Throwable $e) {
                Log::error('storage string build error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                $storage = __('Could not find storage limits');
            }
            ?>
            <div class="col-xxl-5">
                <div class="row">
                    <div class="col-12">
                        <div class="<?php echo e(VC::CD); ?>">
                            <div class="card-header">
                                <h5 class="<?php echo e(VC::MT1); ?> <?php echo e(VC::MB0); ?>"><?php echo e(__('Cashflow')); ?></h5>
                            </div>
                            <div class="card-body">
                                <div id="cash-flow"></div>
                            </div>
                        </div>

                        <div class="<?php echo e(VC::CD); ?>">
                            <div class="card-header">
                                <h5 class="<?php echo e(VC::MT1); ?> <?php echo e(VC::MB0); ?>"><?php echo e(__('Income Vs Expense')); ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="<?php echo e(VC::RW); ?>">
                                    <?php $__currentLoopData = $tiles;
                                    $__env->addLoop($__currentLoopData);
                                    foreach ($__currentLoopData as $tile): $__env->incrementLoopIndices();
                                        $loop = $__env->getLastLoop(); ?>
                                        <?php
                                        $tLbl = $asString(data_get($tile, 'label'), 'label');
                                        $tVal = $asString(data_get($tile, 'value'), 'amount');
                                        $tBg = $asClass(data_get($tile, 'avatarBg'), 'bg-secondary');
                                        $tIc = $asClass(data_get($tile, 'icon'), 'ti-help');
                                        $tTx = $asClass(data_get($tile, 'textClass'), 'text-muted');
                                        ?>
                                        <div class="col-md-6 col-6 my-2">
                                            <div class="<?php echo e(VC::DFL); ?> align-items-start mb-2">
                                                <div class="theme-avatar <?php echo e($tBg); ?>">
                                                    <i class="ti <?php echo e($tIc); ?>"></i>
                                                </div>
                                                <div class="<?php echo e(VC::MS2); ?>">
                                                    <p class="<?php echo e(VC::TXT_MT); ?> <?php echo e(VC::TXSM); ?> <?php echo e(VC::MB0); ?>"><?php echo e($tLbl); ?></p>
                                                    <h4 class="<?php echo e(VC::MB0); ?> <?php echo e($tTx); ?>"><?php echo e($tVal); ?></h4>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach;
                                    $__env->popLoop();
                                    $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-12">
                        <div class="<?php echo e(VC::CD); ?>">
                            <div class="card-header">
                                <h5><?php echo e(__('Storage Limit')); ?><small class="<?php echo e(VC::FEND); ?> <?php echo e(VC::TXT_MT); ?>"><?php echo e($asString($storage, 'storage limits')); ?></small></h5>
                            </div>
                            <div class="card-body">
                                <div id="limit-chart"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-12">
                        <div class="<?php echo e(VC::CD); ?>">
                            <div class="card-header">
                                <h5><?php echo e(__('Income By Category')); ?><span class="<?php echo e(VC::FEND); ?> <?php echo e(VC::TXT_MT); ?>"><?php echo e(($currentYear ?? null) ? __('Year') . ' - ' . $currentYear : __('Could not find year')); ?></span></h5>
                            </div>
                            <div class="card-body">
                                <div id="incomeByCategory"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-12">
                        <div class="<?php echo e(VC::CD); ?>">
                            <div class="card-header">
                                <h5><?php echo e(__('Expense By Category')); ?><span class="<?php echo e(VC::FEND); ?> <?php echo e(VC::TXT_MT); ?>"><?php echo e(($currentYear ?? null) ? __('Year') . ' - ' . $currentYear : __('Could not find year')); ?></span></h5>
                            </div>
                            <div class="card-body">
                                <div id="expenseByCategory"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-12">
                        <div class="<?php echo e(VC::CD); ?>">
                            <div class="card-body">
                                <?php
                                $wTotal = $fmtPrice(data_get($weeklyInvoice, 'invoiceTotal'));
                                $wPaid = $fmtPrice(data_get($weeklyInvoice, 'invoicePaid'));
                                $wDue = $fmtPrice(data_get($weeklyInvoice, 'invoiceDue'));
                                $mTotal = $fmtPrice(data_get($monthlyInvoice, 'invoiceTotal'));
                                $mPaid = $fmtPrice(data_get($monthlyInvoice, 'invoicePaid'));
                                $mDue = $fmtPrice(data_get($monthlyInvoice, 'invoiceDue'));
                                ?>
                                <ul class="nav nav-pills mb-5" id="pills-tab" role="tablist">
                                    <li class="<?php echo e(VC::NV_IT); ?>">
                                        <a class="<?php echo e(VC::NV_LK); ?> active" id="pills-home-tab" data-bs-toggle="pill" href="#invoice_weekly_statistics" role="tab"><?php echo e(__('Invoices Weekly Statistics')); ?></a>
                                    </li>
                                    <li class="<?php echo e(VC::NV_IT); ?>">
                                        <a class="<?php echo e(VC::NV_LK); ?>" id="pills-profile-tab" data-bs-toggle="pill" href="#invoice_monthly_statistics" role="tab"><?php echo e(__('Invoices Monthly Statistics')); ?></a>
                                    </li>
                                </ul>
                                <div class="tab-content" id="pills-tabContent">
                                    <div class="tab-pane fade show active" id="invoice_weekly_statistics" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="<?php echo e(VC::TB_AL); ?> <?php echo e(VC::MB0); ?>">
                                                <tbody class="list">
                                                    <tr>
                                                        <td>
                                                            <h5 class="<?php echo e(VC::MB0); ?>"><?php echo e(__('Total')); ?></h5>
                                                            <p class="<?php echo e(VC::TXT_MT); ?> <?php echo e(VC::TXSM); ?> <?php echo e(VC::MB0); ?>"><?php echo e(__('Invoice Generated')); ?></p>
                                                        </td>
                                                        <td>
                                                            <h4 class="text-muted"><?php echo e($wTotal); ?></h4>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <h5 class="<?php echo e(VC::MB0); ?>"><?php echo e(__('Total')); ?></h5>
                                                            <p class="<?php echo e(VC::TXT_MT); ?> <?php echo e(VC::TXSM); ?> <?php echo e(VC::MB0); ?>"><?php echo e(__('Paid')); ?></p>
                                                        </td>
                                                        <td>
                                                            <h4 class="text-muted"><?php echo e($wPaid); ?></h4>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <h5 class="<?php echo e(VC::MB0); ?>"><?php echo e(__('Total')); ?></h5>
                                                            <p class="<?php echo e(VC::TXT_MT); ?> <?php echo e(VC::TXSM); ?> <?php echo e(VC::MB0); ?>"><?php echo e(__('Due')); ?></p>
                                                        </td>
                                                        <td>
                                                            <h4 class="text-muted"><?php echo e($wDue); ?></h4>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="invoice_monthly_statistics" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="<?php echo e(VC::TB_AL); ?> <?php echo e(VC::MB0); ?>">
                                                <tbody class="list">
                                                    <tr>
                                                        <td>
                                                            <h5 class="<?php echo e(VC::MB0); ?>"><?php echo e(__('Total')); ?></h5>
                                                            <p class="<?php echo e(VC::TXT_MT); ?> <?php echo e(VC::TXSM); ?> <?php echo e(VC::MB0); ?>"><?php echo e(__('Invoice Generated')); ?></p>
                                                        </td>
                                                        <td>
                                                            <h4 class="text-muted"><?php echo e($mTotal); ?></h4>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <h5 class="<?php echo e(VC::MB0); ?>"><?php echo e(__('Total')); ?></h5>
                                                            <p class="<?php echo e(VC::TXT_MT); ?> <?php echo e(VC::TXSM); ?> <?php echo e(VC::MB0); ?>"><?php echo e(__('Paid')); ?></p>
                                                        </td>
                                                        <td>
                                                            <h4 class="text-muted"><?php echo e($mPaid); ?></h4>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <h5 class="<?php echo e(VC::MB0); ?>"><?php echo e(__('Total')); ?></h5>
                                                            <p class="<?php echo e(VC::TXT_MT); ?> <?php echo e(VC::TXSM); ?> <?php echo e(VC::MB0); ?>"><?php echo e(__('Due')); ?></p>
                                                        </td>
                                                        <td>
                                                            <h4 class="text-muted"><?php echo e($mDue); ?></h4>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-12">
                        <div class="<?php echo e(VC::CD); ?>">
                            <div class="card-header">
                                <h5 class="<?php echo e(VC::MT1); ?> <?php echo e(VC::MB0); ?>"><?php echo e(__('Goal')); ?></h5>
                            </div>
                            <div class="card-body">
                                <?php $__empty_1 = true;
                                $__currentLoopData = $goals;
                                $__env->addLoop($__currentLoopData);
                                foreach ($__currentLoopData as $goal): $__env->incrementLoopIndices();
                                    $loop = $__env->getLastLoop();
                                    $__empty_1 = false; ?>
                                    <?php
                                    try {
                                        $results = method_exists($goal, 'target') ? $goal->target(data_get($goal, 'type'), data_get($goal, 'from'), data_get($goal, 'to'), data_get($goal, 'amount')) : ['total' => 0, 'percentage' => 0];
                                        $total = (float) data_get($results, 'total', 0);
                                        $percentage = (float) data_get($results, 'percentage', 0);
                                        $per = number_format($percentage, $decimalNumber, '.', '');
                                    } catch (ModelNotFoundException $e) {
                                        Log::error('goal target model not found', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                                        $total = 0;
                                        $per = number_format(0, $decimalNumber, '.', '');
                                    } catch (QueryException $e) {
                                        Log::error('goal target query error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                                        $total = 0;
                                        $per = number_format(0, $decimalNumber, '.', '');
                                    } catch (\TypeError $e) {
                                        Log::error('goal target type error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                                        $total = 0;
                                        $per = number_format(0, $decimalNumber, '.', '');
                                    } catch (\Throwable $e) {
                                        Log::error('goal target error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                                        $total = 0;
                                        $per = number_format(0, $decimalNumber, '.', '');
                                    }

                                    $gName = $asString(data_get($goal, 'name'), 'goal name');
                                    $gTypeIdx = data_get($goal, 'type');
                                    $gTypeMap = Goal::$goalType ?? null;
                                    $gTypeLabel = is_array($gTypeMap) ? data_get($gTypeMap, $gTypeIdx) : null;
                                    $gType = $asString($gTypeLabel, 'type');
                                    $gFrom = $asString(data_get($goal, 'from'), 'start date');
                                    $gTo = $asString(data_get($goal, 'to'), 'end date');
                                    $gAmount = $fmtPrice(data_get($goal, 'amount'));
                                    $gTotal = $fmtPrice($total);
                                    $perFloat = (float) $per;
                                    ?>
                                    <div class="<?php echo e(VC::CD); ?> border-success border-2 border-bottom-0 border-start-0 border-end-0">
                                        <div class="card-body">
                                            <div class="<?php echo e(VC::FM_CHK); ?>">
                                                <label class="<?php echo e(VC::DBL); ?>" for="goal-<?php echo e((string) data_get($goal, 'id', 'x')); ?>">
                                                    <span>
                                                        <span class="<?php echo e(VC::R_ALC); ?>">
                                                            <span class="col">
                                                                <span class="<?php echo e(VC::TXT_MT); ?> <?php echo e(VC::TXSM); ?>"><?php echo e(__('Name')); ?></span>
                                                                <h6 class="text-nowrap <?php echo e(VC::MB3); ?> mb-sm-0"><?php echo e($gName); ?></h6>
                                                            </span>
                                                            <span class="col">
                                                                <span class="<?php echo e(VC::TXT_MT); ?> <?php echo e(VC::TXSM); ?>"><?php echo e(__('Type')); ?></span>
                                                                <h6 class="<?php echo e(VC::MB3); ?> mb-sm-0"><?php echo e(__($gType)); ?></h6>
                                                            </span>
                                                            <span class="col">
                                                                <span class="<?php echo e(VC::TXT_MT); ?> <?php echo e(VC::TXSM); ?>"><?php echo e(__('Duration')); ?></span>
                                                                <h6 class="<?php echo e(VC::MB3); ?> mb-sm-0"><?php echo e($gFrom . ' ' . __('To') . ' ' . $gTo); ?></h6>
                                                            </span>
                                                            <span class="col">
                                                                <span class="<?php echo e(VC::TXT_MT); ?> <?php echo e(VC::TXSM); ?>"><?php echo e(__('Target')); ?></span>
                                                                <h6 class="<?php echo e(VC::MB3); ?> mb-sm-0"><?php echo e($gTotal . ' ' . __('of') . ' ' . $gAmount); ?></h6>
                                                            </span>
                                                            <span class="col">
                                                                <span class="<?php echo e(VC::TXT_MT); ?> <?php echo e(VC::TXSM); ?>"><?php echo e(__('Progress')); ?></span>
                                                                <h6 class="<?php echo e(VC::MB0); ?>"><?php echo e($per); ?>%</h6>
                                                                <div class="<?php echo e(VC::PG); ?> <?php echo e(VC::MB0); ?>">
                                                                    <?php if ($perFloat <= 33): ?>
                                                                        <div class="progress-bar bg-danger" style="width: <?php echo e($per); ?>%"></div>
                                                                    <?php elseif ($perFloat <= 66): ?>
                                                                        <div class="progress-bar bg-warning" style="width: <?php echo e($per); ?>%"></div>
                                                                    <?php else: ?>
                                                                        <div class="progress-bar bg-primary" style="width: <?php echo e($per); ?>%"></div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach;
                                $__env->popLoop();
                                $loop = $__env->getLastLoop();
                                if ($__empty_1): ?>
                                    <div class="<?php echo e(VC::CD); ?> pb-0">
                                        <div class="card-body text-center">
                                            <h6><?php echo e(__('There is no goal.')); ?></h6>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            $goals ??= [];
            $decimalNumber ??= 2;
            ?>
            <div class="col-xxl-12">
                <div class="<?php echo e(VC::CD); ?>">
                    <div class="card-header">
                        <h5><?php echo e(__('Goal')); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php $__empty_1 = true;
                        $__currentLoopData = $goals;
                        $__env->addLoop($__currentLoopData);
                        foreach ($__currentLoopData as $goal): $__env->incrementLoopIndices();
                            $loop = $__env->getLastLoop();
                            $__empty_1 = false; ?>
                            <?php
                            try {
                                $results = method_exists($goal, 'target') ? $goal->target(data_get($goal, 'type'), data_get($goal, 'from'), data_get($goal, 'to'), data_get($goal, 'amount')) : ['total' => 0, 'percentage' => 0];
                                $total = (float) data_get($results, 'total', 0);
                                $percentage = (float) data_get($results, 'percentage', 0);
                            } catch (ModelNotFoundException $e) {
                                Log::error('goal target model not found', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                                $total = 0;
                                $percentage = 0;
                            } catch (QueryException $e) {
                                Log::error('goal target query error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                                $total = 0;
                                $percentage = 0;
                            } catch (\TypeError $e) {
                                Log::error('goal target type error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                                $total = 0;
                                $percentage = 0;
                            } catch (\Throwable $e) {
                                Log::error('goal target error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                                $total = 0;
                                $percentage = 0;
                            }

                            try {
                                $decimals = (int) ($decimalNumber ?? Utility::getValByName('decimal_number') ?? 2);
                            } catch (ModelNotFoundException $e) {
                                Log::error('decimal number model not found', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                                $decimals = 2;
                            } catch (QueryException $e) {
                                Log::error('decimal number query error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                                $decimals = 2;
                            } catch (\Throwable $e) {
                                Log::error('decimal number error', ['file' => __FILE__, 'line' => __LINE__, 'class' => $e::class, 'message' => $e->getMessage()]);
                                $decimals = 2;
                            }

                            $per = number_format($percentage, $decimals, '.', '');
                            $gName = $asString(data_get($goal, 'name'), 'goal name');
                            $gTypeIdx = data_get($goal, 'type');
                            $gTypeMap = Goal::$goalType ?? [];
                            $gType = $asString(data_get($gTypeMap, $gTypeIdx), 'type');
                            $gFrom = $asString(data_get($goal, 'from'), 'start date');
                            $gTo = $asString(data_get($goal, 'to'), 'end date');
                            $gTotal = $fmtPrice($total);
                            $gAmount = $fmtPrice(data_get($goal, 'amount'));
                            $perFloat = (float) $per;
                            ?>
                            <div class="<?php echo e(VC::CD); ?> border-success border-2 border-bottom-0 border-start-0 border-end-0">
                                <div class="card-body">
                                    <div class="<?php echo e(VC::FM_CHK); ?>">
                                        <label class="form-check-label <?php echo e(VC::DBL); ?>" for="goal-<?php echo e((string) data_get($goal, 'id', 'x')); ?>">
                                            <span>
                                                <span class="<?php echo e(VC::R_ALC); ?>">
                                                    <span class="col">
                                                        <span class="<?php echo e(VC::TXT_MT); ?> <?php echo e(VC::TXSM); ?>"><?php echo e(__('Name')); ?></span>
                                                        <h6 class="text-nowrap <?php echo e(VC::MB3); ?> mb-sm-0"><?php echo e($gName); ?></h6>
                                                    </span>
                                                    <span class="col">
                                                        <span class="<?php echo e(VC::TXT_MT); ?> <?php echo e(VC::TXSM); ?>"><?php echo e(__('Type')); ?></span>
                                                        <h6 class="<?php echo e(VC::MB3); ?> mb-sm-0"><?php echo e(__($gType)); ?></h6>
                                                    </span>
                                                    <span class="col">
                                                        <span class="<?php echo e(VC::TXT_MT); ?> <?php echo e(VC::TXSM); ?>"><?php echo e(__('Duration')); ?></span>
                                                        <h6 class="<?php echo e(VC::MB3); ?> mb-sm-0"><?php echo e($gFrom . ' ' . __('To') . ' ' . $gTo); ?></h6>
                                                    </span>
                                                    <span class="col">
                                                        <span class="<?php echo e(VC::TXT_MT); ?> <?php echo e(VC::TXSM); ?>"><?php echo e(__('Target')); ?></span>
                                                        <h6 class="<?php echo e(VC::MB3); ?> mb-sm-0"><?php echo e($gTotal . ' ' . __('of') . ' ' . $gAmount); ?></h6>
                                                    </span>
                                                    <span class="col">
                                                        <span class="<?php echo e(VC::TXT_MT); ?> <?php echo e(VC::TXSM); ?>"><?php echo e(__('Progress')); ?></span>
                                                        <h6 class="<?php echo e(VC::MB0); ?> <?php echo e(VC::DBL); ?>"><?php echo e($per); ?>%</h6>
                                                        <div class="<?php echo e(VC::PG); ?> <?php echo e(VC::MB0); ?>">
                                                            <?php if ($perFloat <= 33): ?>
                                                                <div class="progress-bar bg-danger" style="width: <?php echo e($per); ?>%"></div>
                                                            <?php elseif ($perFloat <= 66): ?>
                                                                <div class="progress-bar bg-warning" style="width: <?php echo e($per); ?>%"></div>
                                                            <?php else: ?>
                                                                <div class="progress-bar bg-primary" style="width: <?php echo e($per); ?>%"></div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </span>
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach;
                        $__env->popLoop();
                        $loop = $__env->getLastLoop();
                        if ($__empty_1): ?>
                            <div class="<?php echo e(VC::CD); ?> pb-0">
                                <div class="card-body text-center">
                                    <h6><?php echo e(__('There is no goal.')); ?></h6>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(ExtendingLayoutsConstants::ADM, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/resources/views/dashboard/account_dashboard.blade.php ENDPATH**/ ?>