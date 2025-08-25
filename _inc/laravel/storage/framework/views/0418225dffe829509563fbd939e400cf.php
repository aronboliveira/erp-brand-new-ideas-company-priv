<?php

use App\Config\Constants\{
    DatabaseConstants,
    PermissionsConstants,
    PlansConstants,
    SettingsConstants,
    StacksConstants,
    UsersConstants,
    ViewClassNamesConstants as VC,
    ViewsConstants
};
use App\Http\Controllers\EmployeeAttendanceController as EAC;
use App\Models\{Plan, User, Utility};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{Auth, Log, Request as RF, Route};
use Modules\LandingPage\Config\Constants\{ExtendingLandingPageLayoutConstants as E, RoutesResourcesConstants as R};

Log::debug('Loading admin menu data...');
$data ??= [];
$company_logo ??= '';
$company_logos ??= '';
$company_small_logo ??= '';
$colorSettings ??= [];
$emailTemplate ??= [];
$lang ??= '';
$logo ??= '';
$user ??= null;
$userPlan ??= DatabaseConstants::DEFAULT_PLAN;
try {
    $data = Utility::prepareCommonViewData() ?: [];
    $logo = Utility::getFile('uploads/logo/') ?: '';
    $colorSettings = $data[SettingsConstants::CLR_STG] ?? [];
    $company_logo = $data[SettingsConstants::CPN_LG_DK] ?? '';
    $company_logos = $data[SettingsConstants::CPN_LG_LT] ?? '';
    $company_small_logo = $data['company_small_logo'] ?? '';
    $emailTemplate = \App\Models\EmailTemplate::emailTemplateData() ?: [];
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
    $userPlan = $user instanceof User
        ? Plan::getPlan($user?->showDashboard())
        : Plan::find(DatabaseConstants::DEFAULT_PLAN);
} catch (\Error $e) {
    Log::error(
        'Error fetching attendance data',
        [
            'exception_class' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    );
} catch (\Exception $e) {
    Log::error(
        'Exception fetching attendance data',
        [
            'exception_class' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    );
} catch (\Throwable $e) {
    Log::error(
        'Throwable fetching attendance data',
        [
            'exception_class' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    );
}
$data = Utility::fallbackSettings($data);
Log::debug('Loading admin menu template...');
Log::debug('User main data for menu: ', ['type' => $user?->type, 'id' => $user->id, 'email' => $user->email]);
if ($userPlan instanceof Plan) {
    Log::debug('Plan main data for menu: ', [
        'id' => $userPlan->id,
        'name' => $userPlan->name,
        'type' => $userPlan->type,
        'status' => $userPlan->status,
        'acc' => $userPlan->{PlansConstants::COL_ACC},
        'crm' => $userPlan->{PlansConstants::COL_CRM},
        'hrm' => $userPlan->{PlansConstants::COL_HRM},
        'pos' => $userPlan->{PlansConstants::COL_POS},
        'prj' => $userPlan->{PlansConstants::COL_PRJ}
    ]);
} else Log::notice('No plan found!');
if ($user instanceof User)
    Log::debug('User permissions names: ', $user->getAllPermissions()->pluck('name')->toArray());
?>
<?php if (!empty($colorSettings[SettingsConstants::CST_DRK]) && $colorSettings[SettingsConstants::CST_DRK] === 'on'): ?>
    <nav class="dash-sidebar light-sidebar transprent-bg">
    <?php else: ?>
        <nav class="dash-sidebar light-sidebar">
        <?php endif; ?>
        <div class="navbar-wrapper">
            <div class="m-header main-logo">
                <a href="#" class="b-brand">

                    <?php if ($colorSettings[SettingsConstants::CST_DRK] && $colorSettings[SettingsConstants::CST_DRK] == 'on'): ?>
                        <img src="<?php echo e((isset($company_logos) && !empty($company_logos) ? $company_logos : SettingsConstants::CPN_LG_DK_DEF)); ?>"
                            alt="<?php echo e(config('app.name', 'ERPNovaPrestech')); ?>" class="<?php echo e(VC::LOGO_LG); ?>">
                    <?php else: ?>
                        <img src="<?php echo e((isset($company_logo) && !empty($company_logo) ? $company_logo : SettingsConstants::CPN_LG_LT_DEF)); ?>"
                            alt="<?php echo e(config('app.name', 'ERPNovaPrestech')); ?>" class="<?php echo e(VC::LOGO_LG); ?>">
                    <?php endif; ?>
                </a>
            </div>
            <div class="navbar-content">
                <?php if ($user instanceof User): ?>
                    <?php if ($user[UsersConstants::COL_TP] !== PermissionsConstants::CL): ?>
                        <ul class="dash-navbar">
                            <?php if (
                                Gate::check(PermissionsConstants::SHW_HRM_DSB) ||
                                Gate::check(PermissionsConstants::SHW_PRJ_DSB) ||
                                Gate::check(PermissionsConstants::SHW_ACC_DSB) ||
                                Gate::check(PermissionsConstants::SHW_CRM_DSB) ||
                                Gate::check(PermissionsConstants::SHW_POS_DSB)
                            ): ?>
                                <?php
                                $segments = [
                                    null,
                                    ViewsConstants::ACC_DSB,
                                    PermissionsConstants::INC_RPT,
                                    'reports',
                                    'reports_monthly_cashflow',
                                    'reports_quarterly_cashflow',
                                    'reports_payroll',
                                    'reports_leave',
                                    'reports_monthly_attendance',
                                    'reports_lead',
                                    'reports_deal',
                                    ViewsConstants::POS_DSB,
                                    'reports_warehouse',
                                    'reports_daily_purchase',
                                    'reports_monthly_purchase',
                                    'reports_daily_pos',
                                    'reports_monthly_pos',
                                    'reports_pos_vs_purchase'
                                ];
                                $kebabSegments = array_map(function ($segment) {
                                    if ($segment === null) return null;
                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                }, $segments);
                                $allSegments = array_merge($segments, $kebabSegments);
                                $isIncomeMatch = in_array(RF::segment(1), $allSegments);
                                ?>
                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isIncomeMatch ? 'active dash-trigger' : ''); ?>">
                                    <a href="#!" class="dash-link">
                                        <span class="dash-micon">
                                            <i class="<?php echo e(VC::TI_HM); ?>"></i>
                                        </span>
                                        <span class="dash-mtext"><?php echo e(__('Dashboard')); ?></span>
                                        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                    </a>
                                    <ul class="dash-submenu">
                                        <?php if ($userPlan?->{PlansConstants::COL_ACC} == 1 && Gate::check(PermissionsConstants::SHW_ACC_DSB)): ?>
                                            <?php
                                            $segments = [
                                                null,
                                                ViewsConstants::ACC_DSB,
                                                'report',
                                                'reports_monthly_cashflow',
                                                'reports_quarterly_cashflow'
                                            ];
                                            $kebabSegments = array_map(function ($segment) {
                                                if ($segment === null) return null;
                                                return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                            }, $segments);
                                            $allSegments = array_merge($segments, $kebabSegments);
                                            $isReportMatch = in_array(RF::segment(1), $allSegments);
                                            ?>
                                            <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isReportMatch ? ' active dash-trigger' : ''); ?>">
                                                <a class="dash-link" href="#"><?php echo e(__('Accounting ')); ?>

                                                    <span class="dash-arrow">
                                                        <i data-feather="chevron-right"></i>
                                                    </span>
                                                </a>
                                                <ul class="dash-submenu">
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::SHW_ACC_DSB)): ?>
                                                        <?php
                                                        $segments = [
                                                            null,
                                                            ViewsConstants::ACC_DSB
                                                        ];
                                                        $kebabSegments = array_map(function ($segment) {
                                                            if ($segment === null) return null;
                                                            return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                        }, $segments);
                                                        $allSegments = array_merge($segments, $kebabSegments);
                                                        $isAccountingDashboard = in_array(RF::segment(1), $allSegments);
                                                        $dashboardRoute = Route::has('dashboard') ? route('dashboard') : '#';
                                                        $message = Utility::fetchLinkMessage(
                                                            $lang,
                                                            'generics',
                                                            'dashboard_unavailable'
                                                        ) ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';
                                                        ?>
                                                        <li class="dash-item <?php echo e($isAccountingDashboard ? ' active' : ''); ?>">
                                                            <a
                                                                id="dashboard-link"
                                                                class="dash-link"
                                                                href="<?php echo e($dashboardRoute); ?>"
                                                                data-url="<?php echo e($dashboardRoute); ?>"
                                                                data-sv-localized="true"
                                                                data-guard-msg="<?php echo e($message); ?>">
                                                                <?php echo e(__('Overview')); ?>

                                                            </a>
                                                        </li>
                                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                        <script defer>
                                                            (() => {
                                                                const listenerAttr = 'data-dashboard-listener-active';
                                                                const el = document.getElementById('dashboard-link');
                                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                el.setAttribute(listenerAttr, 'true');
                                                                el.addEventListener('click', event => {
                                                                    try {
                                                                        const url = el.getAttribute('data-url');
                                                                        const href = el.href;
                                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                                            event.preventDefault();
                                                                            const message = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                            const containerId = 'toast-container';
                                                                            let container = document.getElementById(containerId);
                                                                            if (!container) {
                                                                                container = document.createElement('div');
                                                                                container.id = containerId;
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (bootstrapLink && window.bootstrap) {
                                                                                const toastEl = document.createElement('div');
                                                                                toastEl.className = 'toast';
                                                                                toastEl.setAttribute('role', 'alert');
                                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                                const body = document.createElement('div');
                                                                                body.className = 'toast-body';
                                                                                body.textContent = message;
                                                                                toastEl.appendChild(body);
                                                                                container.appendChild(toastEl);
                                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                            } else {
                                                                                alert(message);
                                                                            }
                                                                            el.setAttribute('data-failed-route', 'true');
                                                                        }
                                                                    } catch (error) {}
                                                                });
                                                                const observer = new MutationObserver(() => {
                                                                    if (!document.body.contains(el)) {
                                                                        observer.disconnect();
                                                                        el.removeEventListener('click', () => {});
                                                                    }
                                                                });
                                                                observer.observe(document.body, {
                                                                    childList: true,
                                                                    subtree: true
                                                                });
                                                            })();
                                                        </script>
                                                        <?php $__env->stopPush(); ?>
                                                    <?php endif; ?>
                                                    <?php if (
                                                        Gate::check(PermissionsConstants::INC_RPT) ||
                                                        Gate::check(PermissionsConstants::EXP_RPT) ||
                                                        Gate::check(PermissionsConstants::IE_RPT) ||
                                                        Gate::check(PermissionsConstants::TAX_RPT) ||
                                                        Gate::check(PermissionsConstants::LP_RPT) ||
                                                        Gate::check(PermissionsConstants::INV_RPT) ||
                                                        Gate::check(PermissionsConstants::BIL_RPT) ||
                                                        Gate::check(PermissionsConstants::STK_RPT) ||
                                                        Gate::check(PermissionsConstants::TAX_RPT) ||
                                                        Gate::check(PermissionsConstants::MNG_TRT)
                                                    ): ?>
                                                        <?php
                                                        $segments = [
                                                            'reports',
                                                            'reports_monthly_cashflow',
                                                            'reports_quarterly_cashflow'
                                                        ];

                                                        $kebabSegments = array_map(function ($segment) {
                                                            if ($segment === null) return null;
                                                            return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                        }, $segments);

                                                        $allSegments = array_merge($segments, $kebabSegments);
                                                        $isCashflowReports = in_array(RF::segment(1), $allSegments);
                                                        ?>
                                                        <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isCashflowReports ? 'active dash-trigger ' : ''); ?>">
                                                            <a class="dash-link" href="#"><?php echo e(__('Reports')); ?>

                                                                <span class="dash-arrow">
                                                                    <i data-feather="chevron-right"></i>
                                                                </span>
                                                            </a>
                                                            <ul class="dash-submenu">
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::STT_RPT)): ?>
                                                                    <?php
                                                                    $accountStatementRoute = Route::has(ViewsConstants::RPT . '.account.statement')
                                                                        ? route(ViewsConstants::RPT . '.account.statement')
                                                                        : '#';
                                                                    $linkId = 'account-statement-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::RPT,
                                                                        'account_statement_route_unavailable'
                                                                    ) ?? 'Account statement route is unavailable. Please contact technical support or your domain administrator.';
                                                                    ?>
                                                                    <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::RPT . '.account.statement' ? ' active' : ''); ?>">
                                                                        <a
                                                                            id="<?php echo e($linkId); ?>"
                                                                            class="dash-link"
                                                                            href="<?php echo e($accountStatementRoute); ?>"
                                                                            data-url="<?php echo e($accountStatementRoute); ?>"
                                                                            data-sv-localized="true"
                                                                            data-guard-msg="<?php echo e($message); ?>">
                                                                            <?php echo e(__('Account Statement')); ?>

                                                                        </a>
                                                                    </li>
                                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                    <script defer>
                                                                        (() => {
                                                                            const listenerAttr = 'data-account-statement-listener-active';
                                                                            const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                            el.setAttribute(listenerAttr, 'true');
                                                                            el.addEventListener('click', event => {
                                                                                try {
                                                                                    const url = el.getAttribute('data-url');
                                                                                    const href = el.href;
                                                                                    if ((!url || url === '#') && (!href || href === '#')) {
                                                                                        event.preventDefault();
                                                                                        const message = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                        const containerId = 'toast-container';
                                                                                        let container = document.getElementById(containerId);
                                                                                        if (!container) {
                                                                                            container = document.createElement('div');
                                                                                            container.id = containerId;
                                                                                            document.body.appendChild(container);
                                                                                        }
                                                                                        if (bootstrapLink && window.bootstrap) {
                                                                                            const toastEl = document.createElement('div');
                                                                                            toastEl.className = 'toast';
                                                                                            toastEl.setAttribute('role', 'alert');
                                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                                            const body = document.createElement('div');
                                                                                            body.className = 'toast-body';
                                                                                            body.textContent = message;
                                                                                            toastEl.appendChild(body);
                                                                                            container.appendChild(toastEl);
                                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                        } else {
                                                                                            alert(message);
                                                                                        }
                                                                                        el.setAttribute('data-failed-route', 'true');
                                                                                    }
                                                                                } catch (error) {}
                                                                            });
                                                                            const observer = new MutationObserver(() => {
                                                                                if (!document.body.contains(el)) {
                                                                                    observer.disconnect();
                                                                                    el.removeEventListener('click', () => {});
                                                                                }
                                                                            });
                                                                            observer.observe(document.body, {
                                                                                childList: true,
                                                                                subtree: true
                                                                            });
                                                                        })();
                                                                    </script>
                                                                    <?php $__env->stopPush(); ?>
                                                                <?php endif; ?>
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::INV_RPT)): ?>
                                                                    <?php
                                                                    $invoiceSummaryRoute = Route::has(ViewsConstants::RPT . '.invoice.summary')
                                                                        ? route(ViewsConstants::RPT . '.invoice.summary')
                                                                        : '#';
                                                                    $linkId = 'invoice-summary-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::RPT,
                                                                        'invoice_summary_route_unavailable'
                                                                    ) ?? 'Invoice summary route is unavailable. Please contact technical support or your domain administrator.';
                                                                    ?>
                                                                    <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::RPT . '.invoice.summary' ? ' active' : ''); ?>">
                                                                        <a
                                                                            id="<?php echo e($linkId); ?>"
                                                                            class="dash-link"
                                                                            href="<?php echo e($invoiceSummaryRoute); ?>"
                                                                            data-url="<?php echo e($invoiceSummaryRoute); ?>"
                                                                            data-sv-localized="true"
                                                                            data-guard-msg="<?php echo e($message); ?>">
                                                                            <?php echo e(__('Invoice Summary')); ?>

                                                                        </a>
                                                                    </li>
                                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                    <script defer>
                                                                        (() => {
                                                                            const listenerAttr = 'data-invoice-summary-listener-active';
                                                                            const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                            el.setAttribute(listenerAttr, 'true');
                                                                            el.addEventListener('click', event => {
                                                                                try {
                                                                                    const url = el.getAttribute('data-url');
                                                                                    const href = el.href;
                                                                                    if ((!url || url === '#') && (!href || href === '#')) {
                                                                                        event.preventDefault();
                                                                                        const message = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                        const containerId = 'toast-container';
                                                                                        let container = document.getElementById(containerId);
                                                                                        if (!container) {
                                                                                            container = document.createElement('div');
                                                                                            container.id = containerId;
                                                                                            document.body.appendChild(container);
                                                                                        }
                                                                                        if (bootstrapLink && window.bootstrap) {
                                                                                            const toastEl = document.createElement('div');
                                                                                            toastEl.className = 'toast';
                                                                                            toastEl.setAttribute('role', 'alert');
                                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                                            const body = document.createElement('div');
                                                                                            body.className = 'toast-body';
                                                                                            body.textContent = message;
                                                                                            toastEl.appendChild(body);
                                                                                            container.appendChild(toastEl);
                                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                        } else {
                                                                                            alert(message);
                                                                                        }
                                                                                        el.setAttribute('data-failed-route', 'true');
                                                                                    }
                                                                                } catch (error) {}
                                                                            });
                                                                            const observer = new MutationObserver(() => {
                                                                                if (!document.body.contains(el)) {
                                                                                    observer.disconnect();
                                                                                    el.removeEventListener('click', () => {});
                                                                                }
                                                                            });
                                                                            observer.observe(document.body, {
                                                                                childList: true,
                                                                                subtree: true
                                                                            });
                                                                        })();
                                                                    </script>
                                                                    <?php $__env->stopPush(); ?>
                                                                <?php endif; ?>
                                                                <?php
                                                                $salesRoute = Route::has(ViewsConstants::RPT . '.sales')
                                                                    ? route(ViewsConstants::RPT . '.sales')
                                                                    : '#';
                                                                $salesLinkId = 'sales-report-link';
                                                                $salesMessage = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::RPT,
                                                                    'sales_report_route_unavailable'
                                                                ) ?? 'Sales report route is unavailable. Please contact technical support or your domain administrator.';
                                                                ?>
                                                                <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::RPT . '.sales' ? ' active' : ''); ?>">
                                                                    <a
                                                                        id="<?php echo e($salesLinkId); ?>"
                                                                        class="dash-link"
                                                                        href="<?php echo e($salesRoute); ?>"
                                                                        data-url="<?php echo e($salesRoute); ?>"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="<?php echo e($salesMessage); ?>">
                                                                        <?php echo e(__('Sales Report')); ?>

                                                                    </a>
                                                                </li>
                                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                <script defer>
                                                                    (() => {
                                                                        const listenerAttr = 'data-sales-listener-active';
                                                                        const el = document.getElementById('<?php echo e($salesLinkId); ?>');
                                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                        el.setAttribute(listenerAttr, 'true');
                                                                        el.addEventListener('click', event => {
                                                                            try {
                                                                                const url = el.getAttribute('data-url');
                                                                                const href = el.href;
                                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                                    event.preventDefault();
                                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                    const containerId = 'toast-container';
                                                                                    let container = document.getElementById(containerId);
                                                                                    if (!container) {
                                                                                        container = document.createElement('div');
                                                                                        container.id = containerId;
                                                                                        document.body.appendChild(container);
                                                                                    }
                                                                                    if (bootstrapLink && window.bootstrap) {
                                                                                        const toastEl = document.createElement('div');
                                                                                        toastEl.className = 'toast';
                                                                                        toastEl.setAttribute('role', 'alert');
                                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
                                                                                        toastEl.appendChild(body);
                                                                                        container.appendChild(toastEl);
                                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                    } else {
                                                                                        alert(msg);
                                                                                    }
                                                                                    el.setAttribute('data-failed-route', 'true');
                                                                                }
                                                                            } catch (error) {}
                                                                        });
                                                                        const observer = new MutationObserver(() => {
                                                                            if (!document.body.contains(el)) {
                                                                                observer.disconnect();
                                                                                el.removeEventListener('click', () => {});
                                                                            }
                                                                        });
                                                                        observer.observe(document.body, {
                                                                            childList: true,
                                                                            subtree: true
                                                                        });
                                                                    })();
                                                                </script>
                                                                <?php $__env->stopPush(); ?>
                                                                <?php
                                                                $receivablesRoute = Route::has(ViewsConstants::RPT . '.receivables')
                                                                    ? route(ViewsConstants::RPT . '.receivables')
                                                                    : '#';
                                                                $receivablesLinkId = 'receivables-link';
                                                                $receivablesMessage = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::RPT,
                                                                    'receivables_report_route_unavailable'
                                                                ) ?? 'Receivables route is unavailable. Please contact technical support or your domain administrator.';
                                                                ?>
                                                                <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::RPT . '.receivables' ? ' active' : ''); ?>">
                                                                    <a
                                                                        id="<?php echo e($receivablesLinkId); ?>"
                                                                        class="dash-link"
                                                                        href="<?php echo e($receivablesRoute); ?>"
                                                                        data-url="<?php echo e($receivablesRoute); ?>"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="<?php echo e($receivablesMessage); ?>">
                                                                        <?php echo e(__('Receivables')); ?>

                                                                    </a>
                                                                </li>
                                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                <script defer>
                                                                    (() => {
                                                                        const listenerAttr = 'data-receivables-listener-active';
                                                                        const el = document.getElementById('<?php echo e($receivablesLinkId); ?>');
                                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                        el.setAttribute(listenerAttr, 'true');
                                                                        el.addEventListener('click', event => {
                                                                            try {
                                                                                const url = el.getAttribute('data-url');
                                                                                const href = el.href;
                                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                                    event.preventDefault();
                                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                    const containerId = 'toast-container';
                                                                                    let container = document.getElementById(containerId);
                                                                                    if (!container) {
                                                                                        container = document.createElement('div');
                                                                                        container.id = containerId;
                                                                                        document.body.appendChild(container);
                                                                                    }
                                                                                    if (bootstrapLink && window.bootstrap) {
                                                                                        const toastEl = document.createElement('div');
                                                                                        toastEl.className = 'toast';
                                                                                        toastEl.setAttribute('role', 'alert');
                                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
                                                                                        toastEl.appendChild(body);
                                                                                        container.appendChild(toastEl);
                                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                    } else {
                                                                                        alert(msg);
                                                                                    }
                                                                                    el.setAttribute('data-failed-route', 'true');
                                                                                }
                                                                            } catch (error) {}
                                                                        });
                                                                        const observer = new MutationObserver(() => {
                                                                            if (!document.body.contains(el)) {
                                                                                observer.disconnect();
                                                                                el.removeEventListener('click', () => {});
                                                                            }
                                                                        });
                                                                        observer.observe(document.body, {
                                                                            childList: true,
                                                                            subtree: true
                                                                        });
                                                                    })();
                                                                </script>
                                                                <?php $__env->stopPush(); ?>
                                                                <?php
                                                                $payablesRoute = Route::has(ViewsConstants::RPT . '.payables')
                                                                    ? route(ViewsConstants::RPT . '.payables')
                                                                    : '#';
                                                                $payablesLinkId = 'payables-link';
                                                                $payablesMessage = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::RPT,
                                                                    'payables_report_route_unavailable'
                                                                ) ?? 'Payables route is unavailable. Please contact technical support or your domain administrator.';
                                                                ?>
                                                                <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::RPT . '.payables' ? ' active' : ''); ?>">
                                                                    <a
                                                                        id="<?php echo e($payablesLinkId); ?>"
                                                                        class="dash-link"
                                                                        href="<?php echo e($payablesRoute); ?>"
                                                                        data-url="<?php echo e($payablesRoute); ?>"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="<?php echo e($payablesMessage); ?>">
                                                                        <?php echo e(__('Payables')); ?>

                                                                    </a>
                                                                </li>
                                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                <script defer>
                                                                    (() => {
                                                                        const listenerAttr = 'data-payables-listener-active';
                                                                        const el = document.getElementById('<?php echo e($payablesLinkId); ?>');
                                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                        el.setAttribute(listenerAttr, 'true');
                                                                        el.addEventListener('click', event => {
                                                                            try {
                                                                                const url = el.getAttribute('data-url');
                                                                                const href = el.href;
                                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                                    event.preventDefault();
                                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                    const containerId = 'toast-container';
                                                                                    let container = document.getElementById(containerId);
                                                                                    if (!container) {
                                                                                        container = document.createElement('div');
                                                                                        container.id = containerId;
                                                                                        document.body.appendChild(container);
                                                                                    }
                                                                                    if (bootstrapLink && window.bootstrap) {
                                                                                        const toastEl = document.createElement('div');
                                                                                        toastEl.className = 'toast';
                                                                                        toastEl.setAttribute('role', 'alert');
                                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
                                                                                        toastEl.appendChild(body);
                                                                                        container.appendChild(toastEl);
                                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                    } else {
                                                                                        alert(msg);
                                                                                    }
                                                                                    el.setAttribute('data-failed-route', 'true');
                                                                                }
                                                                            } catch (error) {}
                                                                        });
                                                                        const observer = new MutationObserver(() => {
                                                                            if (!document.body.contains(el)) {
                                                                                observer.disconnect();
                                                                                el.removeEventListener('click', () => {});
                                                                            }
                                                                        });
                                                                        observer.observe(document.body, {
                                                                            childList: true,
                                                                            subtree: true
                                                                        });
                                                                    })();
                                                                </script>
                                                                <?php $__env->stopPush(); ?>
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::BIL_RPT)): ?>
                                                                    <?php
                                                                    $billSummaryRoute = Route::has(ViewsConstants::RPT . '.bill.summary')
                                                                        ? route(ViewsConstants::RPT . '.bill.summary')
                                                                        : '#';
                                                                    $linkId = 'bill-summary-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::RPT,
                                                                        'bill_summary_route_unavailable'
                                                                    ) ?? 'Bill summary route is unavailable. Please contact technical support or your domain administrator.';
                                                                    ?>
                                                                    <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::RPT . '.bill.summary' ? ' active' : ''); ?>">
                                                                        <a
                                                                            id="<?php echo e($linkId); ?>"
                                                                            class="dash-link"
                                                                            href="<?php echo e($billSummaryRoute); ?>"
                                                                            data-url="<?php echo e($billSummaryRoute); ?>"
                                                                            data-sv-localized="true"
                                                                            data-guard-msg="<?php echo e($message); ?>">
                                                                            <?php echo e(__('Bill Summary')); ?>

                                                                        </a>
                                                                    </li>
                                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                    <script defer>
                                                                        (() => {
                                                                            const listenerAttr = 'data-bill-summary-listener-active';
                                                                            const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                            el.setAttribute(listenerAttr, 'true');
                                                                            el.addEventListener('click', event => {
                                                                                try {
                                                                                    const url = el.getAttribute('data-url');
                                                                                    const href = el.href;
                                                                                    if ((!url || url === '#') && (!href || href === '#')) {
                                                                                        event.preventDefault();
                                                                                        const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                        const containerId = 'toast-container';
                                                                                        let container = document.getElementById(containerId);
                                                                                        if (!container) {
                                                                                            container = document.createElement('div');
                                                                                            container.id = containerId;
                                                                                            document.body.appendChild(container);
                                                                                        }
                                                                                        if (bootstrapLink && window.bootstrap) {
                                                                                            const toastEl = document.createElement('div');
                                                                                            toastEl.className = 'toast';
                                                                                            toastEl.setAttribute('role', 'alert');
                                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                                            const body = document.createElement('div');
                                                                                            body.className = 'toast-body';
                                                                                            body.textContent = msg;
                                                                                            toastEl.appendChild(body);
                                                                                            container.appendChild(toastEl);
                                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                        } else {
                                                                                            alert(msg);
                                                                                        }
                                                                                        el.setAttribute('data-failed-route', 'true');
                                                                                    }
                                                                                } catch (error) {}
                                                                            });
                                                                            const observer = new MutationObserver(() => {
                                                                                if (!document.body.contains(el)) {
                                                                                    observer.disconnect();
                                                                                    el.removeEventListener('click', () => {});
                                                                                }
                                                                            });
                                                                            observer.observe(document.body, {
                                                                                childList: true,
                                                                                subtree: true
                                                                            });
                                                                        })();
                                                                    </script>
                                                                    <?php $__env->stopPush(); ?>
                                                                <?php endif; ?>
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::STK_RPT)): ?>
                                                                    <?php
                                                                    $productStockRoute = Route::has(ViewsConstants::RPT . '.product.stock.report')
                                                                        ? route(ViewsConstants::RPT . '.product.stock.report')
                                                                        : '#';
                                                                    $linkId = 'product-stock-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::RPT,
                                                                        'product_stock_report_route_unavailable'
                                                                    ) ?? 'Product stock report route is unavailable. Please contact technical support or your domain administrator.';
                                                                    ?>
                                                                    <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::RPT . '.product.stock.report' ? ' active' : ''); ?>">
                                                                        <a
                                                                            id="<?php echo e($linkId); ?>"
                                                                            class="dash-link"
                                                                            href="<?php echo e($productStockRoute); ?>"
                                                                            data-url="<?php echo e($productStockRoute); ?>"
                                                                            data-sv-localized="true"
                                                                            data-guard-msg="<?php echo e($message); ?>">
                                                                            <?php echo e(__('Product Stock')); ?>

                                                                        </a>
                                                                    </li>
                                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                    <script defer>
                                                                        (() => {
                                                                            const listenerAttr = 'data-product-stock-listener-active';
                                                                            const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                            el.setAttribute(listenerAttr, 'true');
                                                                            el.addEventListener('click', event => {
                                                                                try {
                                                                                    const url = el.getAttribute('data-url');
                                                                                    const href = el.href;
                                                                                    if ((!url || url === '#') && (!href || href === '#')) {
                                                                                        event.preventDefault();
                                                                                        const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                        const containerId = 'toast-container';
                                                                                        let container = document.getElementById(containerId);
                                                                                        if (!container) {
                                                                                            container = document.createElement('div');
                                                                                            container.id = containerId;
                                                                                            document.body.appendChild(container);
                                                                                        }
                                                                                        if (bootstrapLink && window.bootstrap) {
                                                                                            const toastEl = document.createElement('div');
                                                                                            toastEl.className = 'toast';
                                                                                            toastEl.setAttribute('role', 'alert');
                                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                                            const body = document.createElement('div');
                                                                                            body.className = 'toast-body';
                                                                                            body.textContent = msg;
                                                                                            toastEl.appendChild(body);
                                                                                            container.appendChild(toastEl);
                                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                        } else {
                                                                                            alert(msg);
                                                                                        }
                                                                                        el.setAttribute('data-failed-route', 'true');
                                                                                    }
                                                                                } catch (error) {}
                                                                            });
                                                                            const observer = new MutationObserver(() => {
                                                                                if (!document.body.contains(el)) {
                                                                                    observer.disconnect();
                                                                                    el.removeEventListener('click', () => {});
                                                                                }
                                                                            });
                                                                            observer.observe(document.body, {
                                                                                childList: true,
                                                                                subtree: true
                                                                            });
                                                                        })();
                                                                    </script>
                                                                    <?php $__env->stopPush(); ?>
                                                                <?php endif; ?>
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::LP_RPT)): ?>
                                                                    <?php
                                                                    $cashflowRoute = Route::has(ViewsConstants::RPT . '.monthly.cashflow')
                                                                        ? route(ViewsConstants::RPT . '.monthly.cashflow')
                                                                        : '#';
                                                                    $linkId = 'cashflow-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::RPT,
                                                                        'monthly_cashflow_route_unavailable'
                                                                    ) ?? 'Cash flow route is unavailable. Please contact technical support or your domain administrator.';
                                                                    ?>
                                                                    <li class="dash-item <?php echo e(request()->is('reports-monthly-cashflow') || request()->is('reports-quarterly-cashflow') ? 'active' : ''); ?>">
                                                                        <a
                                                                            id="<?php echo e($linkId); ?>"
                                                                            class="dash-link"
                                                                            href="<?php echo e($cashflowRoute); ?>"
                                                                            data-url="<?php echo e($cashflowRoute); ?>"
                                                                            data-sv-localized="true"
                                                                            data-guard-msg="<?php echo e($message); ?>">
                                                                            <?php echo e(__('Cash Flow')); ?>

                                                                        </a>
                                                                    </li>
                                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                    <script defer>
                                                                        (() => {
                                                                            const listenerAttr = 'data-cashflow-listener-active';
                                                                            const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                            el.setAttribute(listenerAttr, 'true');
                                                                            el.addEventListener('click', event => {
                                                                                try {
                                                                                    const url = el.getAttribute('data-url');
                                                                                    const href = el.href;
                                                                                    if ((!url || url === '#') && (!href || href === '#')) {
                                                                                        event.preventDefault();
                                                                                        const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                        const containerId = 'toast-container';
                                                                                        let container = document.getElementById(containerId);
                                                                                        if (!container) {
                                                                                            container = document.createElement('div');
                                                                                            container.id = containerId;
                                                                                            document.body.appendChild(container);
                                                                                        }
                                                                                        if (bootstrapLink && window.bootstrap) {
                                                                                            const toastEl = document.createElement('div');
                                                                                            toastEl.className = 'toast';
                                                                                            toastEl.setAttribute('role', 'alert');
                                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                                            const body = document.createElement('div');
                                                                                            body.className = 'toast-body';
                                                                                            body.textContent = msg;
                                                                                            toastEl.appendChild(body);
                                                                                            container.appendChild(toastEl);
                                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                        } else {
                                                                                            alert(msg);
                                                                                        }
                                                                                        el.setAttribute('data-failed-route', 'true');
                                                                                    }
                                                                                } catch (error) {}
                                                                            });
                                                                            const observer = new MutationObserver(() => {
                                                                                if (!document.body.contains(el)) {
                                                                                    observer.disconnect();
                                                                                    el.removeEventListener('click', () => {});
                                                                                }
                                                                            });
                                                                            observer.observe(document.body, {
                                                                                childList: true,
                                                                                subtree: true
                                                                            });
                                                                        })();
                                                                    </script>
                                                                    <?php $__env->stopPush(); ?>
                                                                <?php endif; ?>
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_TRT)): ?>
                                                                    <?php
                                                                    $transactionRoute = Route::has(ViewsConstants::TST . '.index')
                                                                        ? route(ViewsConstants::TST . '.index')
                                                                        : '#';
                                                                    $linkId = 'transaction-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::TST,
                                                                        'transaction_index_route_unavailable'
                                                                    ) ?? 'Transaction route is unavailable. Please contact technical support or your domain administrator.';
                                                                    ?>
                                                                    <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::TST . '.index' || RF::route()->getName() == ViewsConstants::TRF . '.create' || RF::route()->getName() == ViewsConstants::TST . '.edit' ? ' active' : ''); ?>">
                                                                        <a
                                                                            id="<?php echo e($linkId); ?>"
                                                                            class="dash-link"
                                                                            href="<?php echo e($transactionRoute); ?>"
                                                                            data-url="<?php echo e($transactionRoute); ?>"
                                                                            data-sv-localized="true"
                                                                            data-guard-msg="<?php echo e($message); ?>">
                                                                            <?php echo e(__('Transaction')); ?>

                                                                        </a>
                                                                    </li>
                                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                    <script defer>
                                                                        (() => {
                                                                            const listenerAttr = 'data-transaction-listener-active';
                                                                            const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                            el.setAttribute(listenerAttr, 'true');
                                                                            el.addEventListener('click', event => {
                                                                                try {
                                                                                    const url = el.getAttribute('data-url');
                                                                                    const href = el.href;
                                                                                    if ((!url || url === '#') && (!href || href === '#')) {
                                                                                        event.preventDefault();
                                                                                        const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                        const containerId = 'toast-container';
                                                                                        let container = document.getElementById(containerId);
                                                                                        if (!container) {
                                                                                            container = document.createElement('div');
                                                                                            container.id = containerId;
                                                                                            document.body.appendChild(container);
                                                                                        }
                                                                                        if (bootstrapLink && window.bootstrap) {
                                                                                            const toastEl = document.createElement('div');
                                                                                            toastEl.className = 'toast';
                                                                                            toastEl.setAttribute('role', 'alert');
                                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                                            const body = document.createElement('div');
                                                                                            body.className = 'toast-body';
                                                                                            body.textContent = msg;
                                                                                            toastEl.appendChild(body);
                                                                                            container.appendChild(toastEl);
                                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                        } else {
                                                                                            alert(msg);
                                                                                        }
                                                                                        el.setAttribute('data-failed-route', 'true');
                                                                                    }
                                                                                } catch (error) {}
                                                                            });
                                                                            const observer = new MutationObserver(() => {
                                                                                if (!document.body.contains(el)) {
                                                                                    observer.disconnect();
                                                                                    el.removeEventListener('click', () => {});
                                                                                }
                                                                            });
                                                                            observer.observe(document.body, {
                                                                                childList: true,
                                                                                subtree: true
                                                                            });
                                                                        })();
                                                                    </script>
                                                                    <?php $__env->stopPush(); ?>
                                                                <?php endif; ?>
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::INC_RPT)): ?>
                                                                    <?php
                                                                    $incomeSummaryRoute = Route::has(ViewsConstants::RPT . '.income.summary')
                                                                        ? route(ViewsConstants::RPT . '.income.summary')
                                                                        : '#';
                                                                    $linkId = 'income-summary-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::RPT,
                                                                        'income_summary_route_unavailable'
                                                                    ) ?? 'Income summary route is unavailable. Please contact technical support or your domain administrator.';
                                                                    ?>
                                                                    <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::RPT . '.income.summary' ? ' active' : ''); ?>">
                                                                        <a
                                                                            id="<?php echo e($linkId); ?>"
                                                                            class="dash-link"
                                                                            href="<?php echo e($incomeSummaryRoute); ?>"
                                                                            data-url="<?php echo e($incomeSummaryRoute); ?>"
                                                                            data-sv-localized="true"
                                                                            data-guard-msg="<?php echo e($message); ?>">
                                                                            <?php echo e(__('Income Summary')); ?>

                                                                        </a>
                                                                    </li>
                                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                    <script defer>
                                                                        (() => {
                                                                            const listenerAttr = 'data-income-summary-listener-active';
                                                                            const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                            el.setAttribute(listenerAttr, 'true');
                                                                            el.addEventListener('click', event => {
                                                                                try {
                                                                                    const url = el.getAttribute('data-url');
                                                                                    const href = el.href;
                                                                                    if ((!url || url === '#') && (!href || href === '#')) {
                                                                                        event.preventDefault();
                                                                                        const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                        const containerId = 'toast-container';
                                                                                        let container = document.getElementById(containerId);
                                                                                        if (!container) {
                                                                                            container = document.createElement('div');
                                                                                            container.id = containerId;
                                                                                            document.body.appendChild(container);
                                                                                        }
                                                                                        if (bootstrapLink && window.bootstrap) {
                                                                                            const toastEl = document.createElement('div');
                                                                                            toastEl.className = 'toast';
                                                                                            toastEl.setAttribute('role', 'alert');
                                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                                            const body = document.createElement('div');
                                                                                            body.className = 'toast-body';
                                                                                            body.textContent = msg;
                                                                                            toastEl.appendChild(body);
                                                                                            container.appendChild(toastEl);
                                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                        } else {
                                                                                            alert(msg);
                                                                                        }
                                                                                        el.setAttribute('data-failed-route', 'true');
                                                                                    }
                                                                                } catch (error) {}
                                                                            });
                                                                            const observer = new MutationObserver(() => {
                                                                                if (!document.body.contains(el)) {
                                                                                    observer.disconnect();
                                                                                    el.removeEventListener('click', () => {});
                                                                                }
                                                                            });
                                                                            observer.observe(document.body, {
                                                                                childList: true,
                                                                                subtree: true
                                                                            });
                                                                        })();
                                                                    </script>
                                                                    <?php $__env->stopPush(); ?>
                                                                <?php endif; ?>
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::EXP_RPT)): ?>
                                                                    <?php
                                                                    $expenseSummaryRoute = Route::has(ViewsConstants::RPT . '.expense.summary')
                                                                        ? route(ViewsConstants::RPT . '.expense.summary')
                                                                        : '#';
                                                                    $linkId = 'expense-summary-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::RPT,
                                                                        'expense_summary_route_unavailable'
                                                                    ) ?? 'Expense summary route is unavailable. Please contact technical support or your domain administrator.';
                                                                    ?>
                                                                    <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::RPT . '.expense.summary' ? ' active' : ''); ?>">
                                                                        <a
                                                                            id="<?php echo e($linkId); ?>"
                                                                            class="dash-link"
                                                                            href="<?php echo e($expenseSummaryRoute); ?>"
                                                                            data-url="<?php echo e($expenseSummaryRoute); ?>"
                                                                            data-sv-localized="true"
                                                                            data-guard-msg="<?php echo e($message); ?>">
                                                                            <?php echo e(__('Expense Summary')); ?>

                                                                        </a>
                                                                    </li>
                                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                    <script defer>
                                                                        (() => {
                                                                            const listenerAttr = 'data-expense-summary-listener-active';
                                                                            const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                            el.setAttribute(listenerAttr, 'true');
                                                                            el.addEventListener('click', event => {
                                                                                try {
                                                                                    const url = el.getAttribute('data-url');
                                                                                    const href = el.href;
                                                                                    if ((!url || url === '#') && (!href || href === '#')) {
                                                                                        event.preventDefault();
                                                                                        const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                        const containerId = 'toast-container';
                                                                                        let container = document.getElementById(containerId);
                                                                                        if (!container) {
                                                                                            container = document.createElement('div');
                                                                                            container.id = containerId;
                                                                                            document.body.appendChild(container);
                                                                                        }
                                                                                        if (bootstrapLink && window.bootstrap) {
                                                                                            const toastEl = document.createElement('div');
                                                                                            toastEl.className = 'toast';
                                                                                            toastEl.setAttribute('role', 'alert');
                                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                                            const body = document.createElement('div');
                                                                                            body.className = 'toast-body';
                                                                                            body.textContent = msg;
                                                                                            toastEl.appendChild(body);
                                                                                            container.appendChild(toastEl);
                                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                        } else {
                                                                                            alert(msg);
                                                                                        }
                                                                                        el.setAttribute('data-failed-route', 'true');
                                                                                    }
                                                                                } catch (error) {}
                                                                            });
                                                                            const observer = new MutationObserver(() => {
                                                                                if (!document.body.contains(el)) {
                                                                                    observer.disconnect();
                                                                                    el.removeEventListener('click', () => {});
                                                                                }
                                                                            });
                                                                            observer.observe(document.body, {
                                                                                childList: true,
                                                                                subtree: true
                                                                            });
                                                                        })();
                                                                    </script>
                                                                    <?php $__env->stopPush(); ?>
                                                                <?php endif; ?>
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::IE_RPT)): ?>
                                                                    <?php
                                                                    $incomeVsExpenseRoute = Route::has(ViewsConstants::RPT . '.income.vs.expense.summary')
                                                                        ? route(ViewsConstants::RPT . '.income.vs.expense.summary')
                                                                        : '#';
                                                                    $linkId = 'income-vs-expense-summary-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::RPT,
                                                                        'income_vs_expense_summary_route_unavailable'
                                                                    ) ?? 'Income VS Expense route is unavailable. Please contact technical support or your domain administrator.';
                                                                    ?>
                                                                    <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::RPT . '.income.vs.expense.summary' ? ' active' : ''); ?>">
                                                                        <a
                                                                            id="<?php echo e($linkId); ?>"
                                                                            class="dash-link"
                                                                            href="<?php echo e($incomeVsExpenseRoute); ?>"
                                                                            data-url="<?php echo e($incomeVsExpenseRoute); ?>"
                                                                            data-sv-localized="true"
                                                                            data-guard-msg="<?php echo e($message); ?>">
                                                                            <?php echo e(__('Income VS Expense')); ?>

                                                                        </a>
                                                                    </li>
                                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                    <script defer>
                                                                        (() => {
                                                                            const listenerAttr = 'data-income-vs-expense-listener-active';
                                                                            const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                            el.setAttribute(listenerAttr, 'true');
                                                                            el.addEventListener('click', event => {
                                                                                try {
                                                                                    const url = el.getAttribute('data-url');
                                                                                    const href = el.href;
                                                                                    if ((!url || url === '#') && (!href || href === '#')) {
                                                                                        event.preventDefault();
                                                                                        const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                        const containerId = 'toast-container';
                                                                                        let container = document.getElementById(containerId);
                                                                                        if (!container) {
                                                                                            container = document.createElement('div');
                                                                                            container.id = containerId;
                                                                                            document.body.appendChild(container);
                                                                                        }
                                                                                        if (bootstrapLink && window.bootstrap) {
                                                                                            const toastEl = document.createElement('div');
                                                                                            toastEl.className = 'toast';
                                                                                            toastEl.setAttribute('role', 'alert');
                                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                                            const body = document.createElement('div');
                                                                                            body.className = 'toast-body';
                                                                                            body.textContent = msg;
                                                                                            toastEl.appendChild(body);
                                                                                            container.appendChild(toastEl);
                                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                        } else {
                                                                                            alert(msg);
                                                                                        }
                                                                                        el.setAttribute('data-failed-route', 'true');
                                                                                    }
                                                                                } catch (error) {}
                                                                            });
                                                                            const observer = new MutationObserver(() => {
                                                                                if (!document.body.contains(el)) {
                                                                                    observer.disconnect();
                                                                                    el.removeEventListener('click', () => {});
                                                                                }
                                                                            });
                                                                            observer.observe(document.body, {
                                                                                childList: true,
                                                                                subtree: true
                                                                            });
                                                                        })();
                                                                    </script>
                                                                    <?php $__env->stopPush(); ?>
                                                                <?php endif; ?>
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::TAX_RPT)): ?>
                                                                    <?php
                                                                    $taxSummaryRoute = Route::has(ViewsConstants::RPT . '.tax.summary')
                                                                        ? route(ViewsConstants::RPT . '.tax.summary')
                                                                        : '#';
                                                                    $linkId = 'tax-summary-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::RPT,
                                                                        'tax_summary_unavailable'
                                                                    ) ?? 'Tax summary route is unavailable. Please contact technical support or your domain administrator.';
                                                                    ?>
                                                                    <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::RPT . '.tax.summary' ? ' active' : ''); ?>">
                                                                        <a
                                                                            id="<?php echo e($linkId); ?>"
                                                                            class="dash-link"
                                                                            href="<?php echo e($taxSummaryRoute); ?>"
                                                                            data-url="<?php echo e($taxSummaryRoute); ?>"
                                                                            data-sv-localized="true"
                                                                            data-guard-msg="<?php echo e($message); ?>">
                                                                            <?php echo e(__('Tax Summary')); ?>

                                                                        </a>
                                                                    </li>
                                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                    <script defer>
                                                                        (() => {
                                                                            const listenerAttr = 'data-tax-summary-listener-active';
                                                                            const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                            el.setAttribute(listenerAttr, 'true');
                                                                            el.addEventListener('click', event => {
                                                                                try {
                                                                                    const url = el.getAttribute('data-url');
                                                                                    const href = el.href;
                                                                                    if ((!url || url === '#') && (!href || href === '#')) {
                                                                                        event.preventDefault();
                                                                                        const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                        const containerId = 'toast-container';
                                                                                        let container = document.getElementById(containerId);
                                                                                        if (!container) {
                                                                                            container = document.createElement('div');
                                                                                            container.id = containerId;
                                                                                            document.body.appendChild(container);
                                                                                        }
                                                                                        if (bootstrapLink && window.bootstrap) {
                                                                                            const toastEl = document.createElement('div');
                                                                                            toastEl.className = 'toast';
                                                                                            toastEl.setAttribute('role', 'alert');
                                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                                            const body = document.createElement('div');
                                                                                            body.className = 'toast-body';
                                                                                            body.textContent = msg;
                                                                                            toastEl.appendChild(body);
                                                                                            container.appendChild(toastEl);
                                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                        } else {
                                                                                            alert(msg);
                                                                                        }
                                                                                        el.setAttribute('data-failed-route', 'true');
                                                                                    }
                                                                                } catch (error) {}
                                                                            });
                                                                            const observer = new MutationObserver(() => {
                                                                                if (!document.body.contains(el)) {
                                                                                    observer.disconnect();
                                                                                    el.removeEventListener('click', () => {});
                                                                                }
                                                                            });
                                                                            observer.observe(document.body, {
                                                                                childList: true,
                                                                                subtree: true
                                                                            });
                                                                        })();
                                                                    </script>
                                                                    <?php $__env->stopPush(); ?>
                                                                <?php endif; ?>
                                                            </ul>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </li>
                                        <?php endif; ?>
                                        <?php if ($userPlan?->{PlansConstants::COL_HRM} == 1): ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::SHW_HRM_DSB)): ?>
                                                <?php
                                                $segments = [
                                                    ViewsConstants::HRM_DSB,
                                                    'reports_payroll'
                                                ];
                                                $kebabSegments = array_map(function ($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);
                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isHrmPayroll = in_array(RF::segment(1), $allSegments);
                                                ?>
                                                <li
                                                    class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isHrmPayroll ? ' active dash-trigger' : ''); ?>">
                                                    <a class="dash-link" href="#"><?php echo e(__('HRM ')); ?>

                                                        <span class="dash-arrow">
                                                            <i data-feather="chevron-right"></i>
                                                        </span>
                                                    </a>
                                                    <ul class="dash-submenu">
                                                        <?php
                                                        $hrmDashboardRoute = Route::has('hrm.dashboard') ? route('hrm.dashboard') : '#';
                                                        $linkId = 'hrm-dashboard-link';
                                                        $message = Utility::fetchLinkMessage(
                                                            $lang,
                                                            'generic',
                                                            'hrm_dashboard_unavailable'
                                                        ) ?? 'Dashboard for the Human Resources Management route is unavailable. Please contact technical support or your domain administrator.';
                                                        ?>
                                                        <li class="dash-item <?php echo e(RF::route()->getName() == 'hrm.dashboard' ? ' active' : ''); ?>">
                                                            <a
                                                                id="<?php echo e($linkId); ?>"
                                                                class="dash-link"
                                                                href="<?php echo e($hrmDashboardRoute); ?>"
                                                                data-url="<?php echo e($hrmDashboardRoute); ?>"
                                                                data-sv-localized="true"
                                                                data-guard-msg="<?php echo e($message); ?>">
                                                                <?php echo e(__('Overview')); ?>

                                                            </a>
                                                        </li>
                                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                        <script defer>
                                                            (() => {
                                                                const listenerAttr = 'data-hrm-dashboard-listener-active';
                                                                const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                el.setAttribute(listenerAttr, 'true');
                                                                el.addEventListener('click', event => {
                                                                    try {
                                                                        const url = el.getAttribute('data-url');
                                                                        const href = el.href;
                                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                                            event.preventDefault();
                                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                            const containerId = 'toast-container';
                                                                            let container = document.getElementById(containerId);
                                                                            if (!container) {
                                                                                container = document.createElement('div');
                                                                                container.id = containerId;
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (bootstrapLink && window.bootstrap) {
                                                                                const toastEl = document.createElement('div');
                                                                                toastEl.className = 'toast';
                                                                                toastEl.setAttribute('role', 'alert');
                                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                                const body = document.createElement('div');
                                                                                body.className = 'toast-body';
                                                                                body.textContent = msg;
                                                                                toastEl.appendChild(body);
                                                                                container.appendChild(toastEl);
                                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            el.setAttribute('data-failed-route', 'true');
                                                                        }
                                                                    } catch (error) {}
                                                                });
                                                                const observer = new MutationObserver(() => {
                                                                    if (!document.body.contains(el)) {
                                                                        observer.disconnect();
                                                                        el.removeEventListener('click', () => {});
                                                                    }
                                                                });
                                                                observer.observe(document.body, {
                                                                    childList: true,
                                                                    subtree: true
                                                                });
                                                            })();
                                                        </script>
                                                        <?php $__env->stopPush(); ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_RPT)): ?>
                                                            <?php
                                                            $segments = [
                                                                'reports_monthly_attendance',
                                                                'reports_leave',
                                                                'reports_payroll'
                                                            ];

                                                            $kebabSegments = array_map(function ($segment) {
                                                                if ($segment === null) return null;
                                                                return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                            }, $segments);

                                                            $allSegments = array_merge($segments, $kebabSegments);
                                                            $isHrmReports = in_array(RF::segment(1), $allSegments);
                                                            ?>
                                                            <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isHrmReports ? 'active dash-trigger' : ''); ?>"
                                                                href="#hr-report" data-toggle="collapse" role="button"
                                                                aria-expanded="<?php echo e($isHrmReports ? 'true' : 'false'); ?>">
                                                                <a class="dash-link" href="#"><?php echo e(__('Reports')); ?>

                                                                    <span class="dash-arrow">
                                                                        <i data-feather="chevron-right"></i>
                                                                    </span>
                                                                </a>
                                                                <?php
                                                                $payrollRoute = Route::has(ViewsConstants::RPT . '.payroll')
                                                                    ? route(ViewsConstants::RPT . '.payroll')
                                                                    : '#';
                                                                $payrollLinkId = 'reports-payroll-link';
                                                                $payrollMessage = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::RPT,
                                                                    'payroll_route_unavailable'
                                                                ) ?? 'Payroll route is unavailable. Please contact technical support or your domain administrator.';
                                                                $leaveRoute = Route::has(ViewsConstants::RPT . '.leave')
                                                                    ? route(ViewsConstants::RPT . '.leave')
                                                                    : '#';
                                                                $leaveLinkId = 'reports-leave-link';
                                                                $leaveMessage = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::RPT,
                                                                    'leave_route_unavailable'
                                                                ) ?? 'Leave route is unavailable. Please contact technical support or your domain administrator.';
                                                                $attendanceRoute = Route::has(ViewsConstants::RPT . '.monthly.attendance')
                                                                    ? route(ViewsConstants::RPT . '.monthly.attendance')
                                                                    : '#';
                                                                $attendanceLinkId = 'reports-monthly-attendance-link';
                                                                $attendanceMessage = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::RPT,
                                                                    'monthly_attendance_route_unavailable'
                                                                ) ?? 'Monthly attendance route is unavailable. Please contact technical support or your domain administrator.';
                                                                ?>
                                                                <ul class="dash-submenu">
                                                                    <li class="dash-item <?php echo e((request()->is('reports-payroll') || request()->is('reports_payroll')) ? 'active' : ''); ?>">
                                                                        <a
                                                                            id="<?php echo e($payrollLinkId); ?>"
                                                                            class="dash-link"
                                                                            href="<?php echo e($payrollRoute); ?>"
                                                                            data-url="<?php echo e($payrollRoute); ?>"
                                                                            data-sv-localized="true"
                                                                            data-guard-msg="<?php echo e($payrollMessage); ?>">
                                                                            <?php echo e(__('Payroll')); ?>

                                                                        </a>
                                                                    </li>
                                                                    <li class="dash-item <?php echo e((request()->is('reports-leave') || request()->is('reports_leave')) ? 'active' : ''); ?>">
                                                                        <a
                                                                            id="<?php echo e($leaveLinkId); ?>"
                                                                            class="dash-link"
                                                                            href="<?php echo e($leaveRoute); ?>"
                                                                            data-url="<?php echo e($leaveRoute); ?>"
                                                                            data-sv-localized="true"
                                                                            data-guard-msg="<?php echo e($leaveMessage); ?>">
                                                                            <?php echo e(__(ViewsConstants::LV)); ?>

                                                                        </a>
                                                                    </li>
                                                                    <li class="dash-item <?php echo e((request()->is('reports-monthly-attendance') || request()->is('reports_monthly_attendance')) ? 'active' : ''); ?>">
                                                                        <a
                                                                            id="<?php echo e($attendanceLinkId); ?>"
                                                                            class="dash-link"
                                                                            href="<?php echo e($attendanceRoute); ?>"
                                                                            data-url="<?php echo e($attendanceRoute); ?>"
                                                                            data-sv-localized="true"
                                                                            data-guard-msg="<?php echo e($attendanceMessage); ?>">
                                                                            <?php echo e(__('Monthly Attendance')); ?>

                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                <script defer>
                                                                    (() => {
                                                                        const listenerAttr = 'data-payroll-listener-active';
                                                                        const el = document.getElementById('<?php echo e($payrollLinkId); ?>');
                                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                        el.setAttribute(listenerAttr, 'true');
                                                                        el.addEventListener('click', event => {
                                                                            try {
                                                                                const url = el.getAttribute('data-url');
                                                                                const href = el.href;
                                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                                    event.preventDefault();
                                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                    const containerId = 'toast-container';
                                                                                    let container = document.getElementById(containerId);
                                                                                    if (!container) {
                                                                                        container = document.createElement('div');
                                                                                        container.id = containerId;
                                                                                        document.body.appendChild(container);
                                                                                    }
                                                                                    if (bootstrapLink && window.bootstrap) {
                                                                                        const toastEl = document.createElement('div');
                                                                                        toastEl.className = 'toast';
                                                                                        toastEl.setAttribute('role', 'alert');
                                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
                                                                                        toastEl.appendChild(body);
                                                                                        container.appendChild(toastEl);
                                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                    } else {
                                                                                        alert(msg);
                                                                                    }
                                                                                    el.setAttribute('data-failed-route', 'true');
                                                                                }
                                                                            } catch (error) {}
                                                                        });
                                                                        const observer = new MutationObserver(() => {
                                                                            if (!document.body.contains(el)) {
                                                                                observer.disconnect();
                                                                                el.removeEventListener('click', () => {});
                                                                            }
                                                                        });
                                                                        observer.observe(document.body, {
                                                                            childList: true,
                                                                            subtree: true
                                                                        });
                                                                    })();
                                                                </script>
                                                                <script defer>
                                                                    (() => {
                                                                        const listenerAttr = 'data-leave-listener-active';
                                                                        const el = document.getElementById('<?php echo e($leaveLinkId); ?>');
                                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                        el.setAttribute(listenerAttr, 'true');
                                                                        el.addEventListener('click', event => {
                                                                            try {
                                                                                const url = el.getAttribute('data-url');
                                                                                const href = el.href;
                                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                                    event.preventDefault();
                                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                    const containerId = 'toast-container';
                                                                                    let container = document.getElementById(containerId);
                                                                                    if (!container) {
                                                                                        container = document.createElement('div');
                                                                                        container.id = containerId;
                                                                                        document.body.appendChild(container);
                                                                                    }
                                                                                    if (bootstrapLink && window.bootstrap) {
                                                                                        const toastEl = document.createElement('div');
                                                                                        toastEl.className = 'toast';
                                                                                        toastEl.setAttribute('role', 'alert');
                                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
                                                                                        toastEl.appendChild(body);
                                                                                        container.appendChild(toastEl);
                                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                    } else {
                                                                                        alert(msg);
                                                                                    }
                                                                                    el.setAttribute('data-failed-route', 'true');
                                                                                }
                                                                            } catch (error) {}
                                                                        });
                                                                        const observer = new MutationObserver(() => {
                                                                            if (!document.body.contains(el)) {
                                                                                observer.disconnect();
                                                                                el.removeEventListener('click', () => {});
                                                                            }
                                                                        });
                                                                        observer.observe(document.body, {
                                                                            childList: true,
                                                                            subtree: true
                                                                        });
                                                                    })();
                                                                </script>
                                                                <script defer>
                                                                    (() => {
                                                                        const listenerAttr = 'data-attendance-listener-active';
                                                                        const el = document.getElementById('<?php echo e($attendanceLinkId); ?>');
                                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                        el.setAttribute(listenerAttr, 'true');
                                                                        el.addEventListener('click', event => {
                                                                            try {
                                                                                const url = el.getAttribute('data-url');
                                                                                const href = el.href;
                                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                                    event.preventDefault();
                                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                    const containerId = 'toast-container';
                                                                                    let container = document.getElementById(containerId);
                                                                                    if (!container) {
                                                                                        container = document.createElement('div');
                                                                                        container.id = containerId;
                                                                                        document.body.appendChild(container);
                                                                                    }
                                                                                    if (bootstrapLink && window.bootstrap) {
                                                                                        const toastEl = document.createElement('div');
                                                                                        toastEl.className = 'toast';
                                                                                        toastEl.setAttribute('role', 'alert');
                                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
                                                                                        toastEl.appendChild(body);
                                                                                        container.appendChild(toastEl);
                                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                    } else {
                                                                                        alert(msg);
                                                                                    }
                                                                                    el.setAttribute('data-failed-route', 'true');
                                                                                }
                                                                            } catch (error) {}
                                                                        });
                                                                        const observer = new MutationObserver(() => {
                                                                            if (!document.body.contains(el)) {
                                                                                observer.disconnect();
                                                                                el.removeEventListener('click', () => {});
                                                                            }
                                                                        });
                                                                        observer.observe(document.body, {
                                                                            childList: true,
                                                                            subtree: true
                                                                        });
                                                                    })();
                                                                </script>
                                                                <?php $__env->stopPush(); ?>
                                                            </li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </li>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if ($userPlan?->{PlansConstants::COL_CRM} == 1): ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::SHW_CRM_DSB)): ?>
                                                <?php
                                                $segments = [ViewsConstants::CRM_DSB, 'reports-lead', 'reports-deal'];
                                                $kebabSegments = array_map(function ($s) {
                                                    return strtolower(preg_replace('/[A-Z]/', '-$0', lcfirst($s)));
                                                }, $segments);
                                                $isLeadMatch = in_array(RF::segment(1), array_merge($segments, $kebabSegments));
                                                ?>
                                                <li
                                                    class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isLeadMatch ? ' active dash-trigger' : ''); ?>">
                                                    <a class="dash-link" href="#"><?php echo e(__('CRM')); ?>

                                                        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                    </a>
                                                    <ul class="dash-submenu">
                                                        <?php
                                                        $crmDashboardRoute = Route::has('crm.dashboard') ? route('crm.dashboard') : '#';
                                                        $linkId = 'crm-dashboard-link';
                                                        $message = Utility::fetchLinkMessage(
                                                            $lang,
                                                            'generics',
                                                            'crm_dashboard_unavailable'
                                                        ) ?? 'The dashboard route for Customer Resources Managament is unavailable. Please contact technical support or your domain administrator.';
                                                        ?>
                                                        <li class="dash-item <?php echo e(RF::route()->getName() == 'crm.dashboard' ? ' active' : ''); ?>">
                                                            <a
                                                                id="<?php echo e($linkId); ?>"
                                                                class="dash-link"
                                                                href="<?php echo e($crmDashboardRoute); ?>"
                                                                data-url="<?php echo e($crmDashboardRoute); ?>"
                                                                data-sv-localized="true"
                                                                data-guard-msg="<?php echo e($message); ?>">
                                                                <?php echo e(__('Overview')); ?>

                                                            </a>
                                                        </li>
                                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                        <script defer>
                                                            (() => {
                                                                const listenerAttr = 'data-crm-dashboard-listener-active';
                                                                const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                el.setAttribute(listenerAttr, 'true');
                                                                el.addEventListener('click', event => {
                                                                    try {
                                                                        const url = el.getAttribute('data-url');
                                                                        const href = el.href;
                                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                                            event.preventDefault();
                                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                            const containerId = 'toast-container';
                                                                            let container = document.getElementById(containerId);
                                                                            if (!container) {
                                                                                container = document.createElement('div');
                                                                                container.id = containerId;
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (bootstrapLink && window.bootstrap) {
                                                                                const toastEl = document.createElement('div');
                                                                                toastEl.className = 'toast';
                                                                                toastEl.setAttribute('role', 'alert');
                                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                                const body = document.createElement('div');
                                                                                body.className = 'toast-body';
                                                                                body.textContent = msg;
                                                                                toastEl.appendChild(body);
                                                                                container.appendChild(toastEl);
                                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            el.setAttribute('data-failed-route', 'true');
                                                                        }
                                                                    } catch (error) {}
                                                                });
                                                                const observer = new MutationObserver(() => {
                                                                    if (!document.body.contains(el)) {
                                                                        observer.disconnect();
                                                                        el.removeEventListener('click', () => {});
                                                                    }
                                                                });
                                                                observer.observe(document.body, {
                                                                    childList: true,
                                                                    subtree: true
                                                                });
                                                            })();
                                                        </script>
                                                        <?php $__env->stopPush(); ?>
                                                        <?php
                                                        $segments = [
                                                            'reports_lead',
                                                            'reports_deal'
                                                        ];

                                                        $kebabSegments = array_map(function ($segment) {
                                                            if ($segment === null) return null;
                                                            return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                        }, $segments);

                                                        $allSegments = array_merge($segments, $kebabSegments);
                                                        $isCrmReports = in_array(RF::segment(1), $allSegments);
                                                        ?>
                                                        <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isCrmReports ? 'active dash-trigger' : ''); ?>" href="#crm-report" data-toggle="collapse" role="button" aria-expanded="<?php echo e($isCrmReports ? 'true' : 'false'); ?>">
                                                            <a class="dash-link" href="#"><?php echo e(__('Reports')); ?>

                                                                <span class="dash-arrow">
                                                                    <i data-feather="chevron-right"></i>
                                                                </span>
                                                            </a>
                                                            <?php
                                                            $leadRoute = Route::has(ViewsConstants::RPT . '.lead')
                                                                ? route(ViewsConstants::RPT . '.lead')
                                                                : '#';
                                                            $leadLinkId = 'reports-lead-link';
                                                            $leadMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::RPT,
                                                                'lead_route_unavailable'
                                                            ) ?? 'Lead report route is unavailable. Please contact technical support or your domain administrator.';

                                                            $dealRoute = Route::has(ViewsConstants::RPT . '.deal')
                                                                ? route(ViewsConstants::RPT . '.deal')
                                                                : '#';
                                                            $dealLinkId = 'reports-deal-link';
                                                            $dealMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::RPT,
                                                                'deal_route_unavailable'
                                                            ) ?? 'Deal report route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <ul class="dash-submenu">
                                                                <li class="dash-item <?php echo e((request()->is('reports-lead') || request()->is('reports_lead')) ? 'active' : ''); ?>">
                                                                    <a
                                                                        id="<?php echo e($leadLinkId); ?>"
                                                                        class="dash-link"
                                                                        href="<?php echo e($leadRoute); ?>"
                                                                        data-url="<?php echo e($leadRoute); ?>"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="<?php echo e($leadMessage); ?>">
                                                                        <?php echo e(__('Lead')); ?>

                                                                    </a>
                                                                </li>
                                                                <li class="dash-item <?php echo e((request()->is('reports-deal') || request()->is('reports_deal')) ? 'active' : ''); ?>">
                                                                    <a
                                                                        id="<?php echo e($dealLinkId); ?>"
                                                                        class="dash-link"
                                                                        href="<?php echo e($dealRoute); ?>"
                                                                        data-url="<?php echo e($dealRoute); ?>"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="<?php echo e($dealMessage); ?>">
                                                                        <?php echo e(__('Deal')); ?>

                                                                    </a>
                                                                </li>
                                                            </ul>
                                                            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                            <script defer>
                                                                (() => {
                                                                    const listenerAttr = 'data-lead-listener-active';
                                                                    const el = document.getElementById('<?php echo e($leadLinkId); ?>');
                                                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                    el.setAttribute(listenerAttr, 'true');
                                                                    el.addEventListener('click', event => {
                                                                        try {
                                                                            const url = el.getAttribute('data-url');
                                                                            const href = el.href;
                                                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                                                event.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                const containerId = 'toast-container';
                                                                                let container = document.getElementById(containerId);
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = containerId;
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bootstrapLink && window.bootstrap) {
                                                                                    const toastEl = document.createElement('div');
                                                                                    toastEl.className = 'toast';
                                                                                    toastEl.setAttribute('role', 'alert');
                                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toastEl.appendChild(body);
                                                                                    container.appendChild(toastEl);
                                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            }
                                                                        } catch (error) {}
                                                                    });
                                                                    const observer = new MutationObserver(() => {
                                                                        if (!document.body.contains(el)) {
                                                                            observer.disconnect();
                                                                            el.removeEventListener('click', () => {});
                                                                        }
                                                                    });
                                                                    observer.observe(document.body, {
                                                                        childList: true,
                                                                        subtree: true
                                                                    });
                                                                })();
                                                            </script>
                                                            <script defer>
                                                                (() => {
                                                                    const listenerAttr = 'data-deal-listener-active';
                                                                    const el = document.getElementById('<?php echo e($dealLinkId); ?>');
                                                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                    el.setAttribute(listenerAttr, 'true');
                                                                    el.addEventListener('click', event => {
                                                                        try {
                                                                            const url = el.getAttribute('data-url');
                                                                            const href = el.href;
                                                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                                                event.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                const containerId = 'toast-container';
                                                                                let container = document.getElementById(containerId);
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = containerId;
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bootstrapLink && window.bootstrap) {
                                                                                    const toastEl = document.createElement('div');
                                                                                    toastEl.className = 'toast';
                                                                                    toastEl.setAttribute('role', 'alert');
                                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toastEl.appendChild(body);
                                                                                    container.appendChild(toastEl);
                                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            }
                                                                        } catch (error) {}
                                                                    });
                                                                    const observer = new MutationObserver(() => {
                                                                        if (!document.body.contains(el)) {
                                                                            observer.disconnect();
                                                                            el.removeEventListener('click', () => {});
                                                                        }
                                                                    });
                                                                    observer.observe(document.body, {
                                                                        childList: true,
                                                                        subtree: true
                                                                    });
                                                                })();
                                                            </script>
                                                            <?php $__env->stopPush(); ?>
                                                        </li>
                                                    </ul>
                                                </li>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if ($userPlan?->{PlansConstants::COL_PJ} == 1): ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::SHW_PRJ_DSB)): ?>
                                                <?php
                                                $projectDashboardRoute = Route::has('project.dashboard')
                                                    ? route('project.dashboard')
                                                    : '#';
                                                $linkId = 'project-dashboard-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::PRJ,
                                                    'project_dashboard_route_unavailable'
                                                ) ?? 'Project dashboard route is unavailable. Please contact technical support or your domain administrator.';
                                                ?>
                                                <li class="dash-item <?php echo e(RF::route()->getName() == 'project.dashboard' ? ' active' : ''); ?>">
                                                    <a
                                                        id="<?php echo e($linkId); ?>"
                                                        class="dash-link"
                                                        href="<?php echo e($projectDashboardRoute); ?>"
                                                        data-url="<?php echo e($projectDashboardRoute); ?>"
                                                        data-sv-localized="true"
                                                        data-guard-msg="<?php echo e($message); ?>">
                                                        <?php echo e(__(ViewsConstants::PRJ)); ?>

                                                    </a>
                                                </li>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                <script defer>
                                                    (() => {
                                                        const listenerAttr = 'data-project-dashboard-listener-active';
                                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                        el.setAttribute(listenerAttr, 'true');
                                                        el.addEventListener('click', event => {
                                                            try {
                                                                const url = el.getAttribute('data-url');
                                                                const href = el.href;
                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                    event.preventDefault();
                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                    const containerId = 'toast-container';
                                                                    let container = document.getElementById(containerId);
                                                                    if (!container) {
                                                                        container = document.createElement('div');
                                                                        container.id = containerId;
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl = document.createElement('div');
                                                                        toastEl.className = 'toast';
                                                                        toastEl.setAttribute('role', 'alert');
                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    el.setAttribute('data-failed-route', 'true');
                                                                }
                                                            } catch (error) {}
                                                        });
                                                        const observer = new MutationObserver(() => {
                                                            if (!document.body.contains(el)) {
                                                                observer.disconnect();
                                                                el.removeEventListener('click', () => {});
                                                            }
                                                        });
                                                        observer.observe(document.body, {
                                                            childList: true,
                                                            subtree: true
                                                        });
                                                    })();
                                                </script>
                                                <?php $__env->stopPush(); ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if ($userPlan?->{PlansConstants::COL_POS} == 1): ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::SHW_POS_DSB)): ?>
                                                <?php
                                                $segments = [
                                                    'pos_dashboard',
                                                    'reports_warehouse',
                                                    'reports_daily_purchase',
                                                    'reports_monthly_purchase',
                                                    'reports_daily_pos',
                                                    'reports_monthly_pos',
                                                    'reports_pos_vs_purchase'
                                                ];

                                                $kebabSegments = array_map(function ($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);

                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isPosReports = in_array(RF::segment(1), $allSegments);
                                                ?>
                                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isPosReports ? ' active dash-trigger' : ''); ?>">
                                                    <a class="dash-link" href="#"><?php echo e(__('POS')); ?>

                                                        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                    </a>
                                                    <ul class="dash-submenu">
                                                        <?php
                                                        $posDashboardRoute = Route::has(ViewsConstants::POS . '.dashboard')
                                                            ? route(ViewsConstants::POS . '.dashboard')
                                                            : '#';
                                                        $linkId = 'pos-dashboard-link';
                                                        $message = Utility::fetchLinkMessage(
                                                            $lang,
                                                            'generics',
                                                            'pos_dashboard_route_unavailable'
                                                        ) ?? 'The route for the dashboard of the Points of Sales is unavailable. Please contact technical support or your domain administrator.';
                                                        ?>
                                                        <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::POS . '.dashboard' ? ' active' : ''); ?>">
                                                            <a
                                                                id="<?php echo e($linkId); ?>"
                                                                class="dash-link"
                                                                href="<?php echo e($posDashboardRoute); ?>"
                                                                data-url="<?php echo e($posDashboardRoute); ?>"
                                                                data-sv-localized="true"
                                                                data-guard-msg="<?php echo e($message); ?>">
                                                                <?php echo e(__('Overview')); ?>

                                                            </a>
                                                        </li>
                                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                        <script defer>
                                                            (() => {
                                                                const listenerAttr = 'data-pos-dashboard-listener-active';
                                                                const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                el.setAttribute(listenerAttr, 'true');
                                                                el.addEventListener('click', event => {
                                                                    try {
                                                                        const url = el.getAttribute('data-url');
                                                                        const href = el.href;
                                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                                            event.preventDefault();
                                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                            const containerId = 'toast-container';
                                                                            let container = document.getElementById(containerId);
                                                                            if (!container) {
                                                                                container = document.createElement('div');
                                                                                container.id = containerId;
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (bootstrapLink && window.bootstrap) {
                                                                                const toastEl = document.createElement('div');
                                                                                toastEl.className = 'toast';
                                                                                toastEl.setAttribute('role', 'alert');
                                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                                const body = document.createElement('div');
                                                                                body.className = 'toast-body';
                                                                                body.textContent = msg;
                                                                                toastEl.appendChild(body);
                                                                                container.appendChild(toastEl);
                                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            el.setAttribute('data-failed-route', 'true');
                                                                        }
                                                                    } catch (error) {}
                                                                });
                                                                const observer = new MutationObserver(() => {
                                                                    if (!document.body.contains(el)) {
                                                                        observer.disconnect();
                                                                        el.removeEventListener('click', () => {});
                                                                    }
                                                                });
                                                                observer.observe(document.body, {
                                                                    childList: true,
                                                                    subtree: true
                                                                });
                                                            })();
                                                        </script>
                                                        <?php $__env->stopPush(); ?>
                                                        <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isPosReports ? 'active dash-trigger' : ''); ?>"
                                                            href="#crm-report" data-toggle="collapse" role="button"
                                                            aria-expanded="<?php echo e($isPosReports ? 'true' : 'false'); ?>">
                                                            <a class="dash-link" href="#"><?php echo e(__('Reports')); ?>

                                                                <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                            </a>
                                                            <?php
                                                            $warehouseRoute = Route::has(ViewsConstants::RPT . '.warehouse')
                                                                ? route(ViewsConstants::RPT . '.warehouse')
                                                                : '#';
                                                            $warehouseLinkId = 'warehouse-report-link';
                                                            $warehouseMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::RPT,
                                                                'warehouse_report_route_unavailable'
                                                            ) ?? 'Warehouse report route is unavailable. Please contact technical support or your domain administrator.';
                                                            $dailyPurchaseRoute = Route::has(ViewsConstants::RPT . '.daily.purchase')
                                                                ? route(ViewsConstants::RPT . '.daily.purchase')
                                                                : '#';
                                                            $dailyPurchaseLinkId = 'daily-purchase-report-link';
                                                            $dailyPurchaseMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::RPT,
                                                                'daily_purchase_report_route_unavailable'
                                                            ) ?? 'Purchase daily/monthly report route is unavailable. Please contact technical support or your domain administrator.';
                                                            $dailyPosRoute = Route::has(ViewsConstants::RPT . '.daily.pos')
                                                                ? route(ViewsConstants::RPT . '.daily.pos')
                                                                : '#';
                                                            $dailyPosLinkId = 'daily-pos-report-link';
                                                            $dailyPosMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::RPT,
                                                                'daily_pos_report_route_unavailable'
                                                            ) ?? 'POS daily/monthly report route is unavailable. Please contact technical support or your domain administrator.';
                                                            $posVsPurchaseRoute = Route::has(ViewsConstants::RPT . '.pos.vs.purchase')
                                                                ? route(ViewsConstants::RPT . '.pos.vs.purchase')
                                                                : '#';
                                                            $posVsPurchaseLinkId = 'pos-vs-purchase-report-link';
                                                            $posVsPurchaseMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::RPT,
                                                                'pos_vs_purchase_report_route_unavailable'
                                                            ) ?? 'POS VS Purchase report route is unavailable. Please contact technical support or your domain administrator.';
                                                            $menuItems = [
                                                                [
                                                                    'routes' => ['reports-warehouse', 'reports_warehouse'],
                                                                    'linkId' => $warehouseLinkId ?? '',
                                                                    'route' => $warehouseRoute ?? '#',
                                                                    'message' => $warehouseMessage ?? '',
                                                                    'label' => __('Warehouse Report')
                                                                ],
                                                                [
                                                                    'routes' => [
                                                                        'reports-daily-purchase',
                                                                        'reports_daily_purchase',
                                                                        'reports-monthly-purchase',
                                                                        'reports_monthly_purchase'
                                                                    ],
                                                                    'linkId' => $dailyPurchaseLinkId ?? '',
                                                                    'route' => $dailyPurchaseRoute ?? '#',
                                                                    'message' => $dailyPurchaseMessage ?? '',
                                                                    'label' => __('Purchase Daily/Monthly Report')
                                                                ],
                                                                [
                                                                    'routes' => [
                                                                        'reports-daily-pos',
                                                                        'reports_daily_pos',
                                                                        'reports-monthly-pos',
                                                                        'reports_monthly_pos'
                                                                    ],
                                                                    'linkId' => $dailyPosLinkId ?? '',
                                                                    'route' => $dailyPosRoute ?? '#',
                                                                    'message' => $dailyPosMessage ?? '',
                                                                    'label' => __('POS Daily/Monthly Report')
                                                                ],
                                                                [
                                                                    'routes' => ['reports-pos-vs-purchase', 'reports_pos_vs_purchase'],
                                                                    'linkId' => $posVsPurchaseLinkId ?? '',
                                                                    'route' => $posVsPurchaseRoute ?? '#',
                                                                    'message' => $posVsPurchaseMessage ?? '',
                                                                    'label' => __('Pos VS Purchase Report')
                                                                ]
                                                            ];
                                                            $isActiveRoute = function ($routes) {
                                                                return collect($routes)->contains(fn($route) => request()->is($route));
                                                            };
                                                            ?>
                                                            <ul class="dash-submenu">
                                                                <?php $__currentLoopData = $menuItems;
                                                                $__env->addLoop($__currentLoopData);
                                                                foreach ($__currentLoopData as $item): $__env->incrementLoopIndices();
                                                                    $loop = $__env->getLastLoop(); ?>
                                                                    <li class="dash-item <?php echo e($isActiveRoute($item['routes']) ? 'active' : ''); ?>">
                                                                        <a
                                                                            id="<?php echo e($item['linkId']); ?>"
                                                                            class="dash-link"
                                                                            href="<?php echo e($item['route']); ?>"
                                                                            data-url="<?php echo e($item['route']); ?>"
                                                                            data-sv-localized="true"
                                                                            data-guard-msg="<?php echo e($item['message']); ?>">
                                                                            <?php echo e($item['label']); ?>

                                                                        </a>
                                                                    </li>
                                                                <?php endforeach;
                                                                $__env->popLoop();
                                                                $loop = $__env->getLastLoop(); ?>
                                                            </ul>
                                                            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                            <script defer>
                                                                (() => {
                                                                    const listenerAttr = 'data-warehouse-listener-active';
                                                                    const el = document.getElementById('<?php echo e($warehouseLinkId); ?>');
                                                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                    el.setAttribute(listenerAttr, 'true');
                                                                    el.addEventListener('click', event => {
                                                                        try {
                                                                            const url = el.getAttribute('data-url');
                                                                            const href = el.href;
                                                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                                                event.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                const containerId = 'toast-container';
                                                                                let container = document.getElementById(containerId);
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = containerId;
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bootstrapLink && window.bootstrap) {
                                                                                    const toastEl = document.createElement('div');
                                                                                    toastEl.className = 'toast';
                                                                                    toastEl.setAttribute('role', 'alert');
                                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toastEl.appendChild(body);
                                                                                    container.appendChild(toastEl);
                                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            }
                                                                        } catch (error) {}
                                                                    });
                                                                    const observer = new MutationObserver(() => {
                                                                        if (!document.body.contains(el)) {
                                                                            observer.disconnect();
                                                                            el.removeEventListener('click', () => {});
                                                                        }
                                                                    });
                                                                    observer.observe(document.body, {
                                                                        childList: true,
                                                                        subtree: true
                                                                    });
                                                                })();
                                                            </script>
                                                            <script defer>
                                                                (() => {
                                                                    const listenerAttr = 'data-daily-purchase-listener-active';
                                                                    const el = document.getElementById('<?php echo e($dailyPurchaseLinkId); ?>');
                                                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                    el.setAttribute(listenerAttr, 'true');
                                                                    el.addEventListener('click', event => {
                                                                        try {
                                                                            const url = el.getAttribute('data-url');
                                                                            const href = el.href;
                                                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                                                event.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                const containerId = 'toast-container';
                                                                                let container = document.getElementById(containerId);
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = containerId;
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bootstrapLink && window.bootstrap) {
                                                                                    const toastEl = document.createElement('div');
                                                                                    toastEl.className = 'toast';
                                                                                    toastEl.setAttribute('role', 'alert');
                                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toastEl.appendChild(body);
                                                                                    container.appendChild(toastEl);
                                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            }
                                                                        } catch (error) {}
                                                                    });
                                                                    const observer = new MutationObserver(() => {
                                                                        if (!document.body.contains(el)) {
                                                                            observer.disconnect();
                                                                            el.removeEventListener('click', () => {});
                                                                        }
                                                                    });
                                                                    observer.observe(document.body, {
                                                                        childList: true,
                                                                        subtree: true
                                                                    });
                                                                })();
                                                            </script>
                                                            <script defer>
                                                                (() => {
                                                                    const listenerAttr = 'data-daily-pos-listener-active';
                                                                    const el = document.getElementById('<?php echo e($dailyPosLinkId); ?>');
                                                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                    el.setAttribute(listenerAttr, 'true');
                                                                    el.addEventListener('click', event => {
                                                                        try {
                                                                            const url = el.getAttribute('data-url');
                                                                            const href = el.href;
                                                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                                                event.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                const containerId = 'toast-container';
                                                                                let container = document.getElementById(containerId);
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = containerId;
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bootstrapLink && window.bootstrap) {
                                                                                    const toastEl = document.createElement('div');
                                                                                    toastEl.className = 'toast';
                                                                                    toastEl.setAttribute('role', 'alert');
                                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toastEl.appendChild(body);
                                                                                    container.appendChild(toastEl);
                                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            }
                                                                        } catch (error) {}
                                                                    });
                                                                    const observer = new MutationObserver(() => {
                                                                        if (!document.body.contains(el)) {
                                                                            observer.disconnect();
                                                                            el.removeEventListener('click', () => {});
                                                                        }
                                                                    });
                                                                    observer.observe(document.body, {
                                                                        childList: true,
                                                                        subtree: true
                                                                    });
                                                                })();
                                                            </script>
                                                            <script defer>
                                                                (() => {
                                                                    const listenerAttr = 'data-pos-vs-purchase-listener-active';
                                                                    const el = document.getElementById('<?php echo e($posVsPurchaseLinkId); ?>');
                                                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                    el.setAttribute(listenerAttr, 'true');
                                                                    el.addEventListener('click', event => {
                                                                        try {
                                                                            const url = el.getAttribute('data-url');
                                                                            const href = el.href;
                                                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                                                event.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                const containerId = 'toast-container';
                                                                                let container = document.getElementById(containerId);
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = container;
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bootstrapLink && window.bootstrap) {
                                                                                    const toastEl = document.createElement('div');
                                                                                    toastEl.className = 'toast';
                                                                                    toastEl.setAttribute('role', 'alert');
                                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toastEl.appendChild(body);
                                                                                    container.appendChild(toastEl);
                                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            }
                                                                        } catch (error) {}
                                                                    });
                                                                    const observer = new MutationObserver(() => {
                                                                        if (!document.body.contains(el)) {
                                                                            observer.disconnect();
                                                                            el.removeEventListener('click', () => {});
                                                                        }
                                                                    });
                                                                    observer.observe(document.body, {
                                                                        childList: true,
                                                                        subtree: true
                                                                    });
                                                                })();
                                                            </script>
                                                            <?php $__env->stopPush(); ?>
                                                        </li>
                                                    </ul>
                                                </li>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </ul>
                                </li>
                            <?php endif; ?>
                            <?php if (!empty($userPlan) && $userPlan?->{PlansConstants::COL_HRM} == 1): ?>
                                <?php if (Gate::check(PermissionsConstants::MNG_EMP) || Gate::check(PermissionsConstants::MNG_SSL)): ?>
                                    <?php
                                    $segments = [
                                        ViewsConstants::ALW_OPT,
                                        ViewsConstants::ANC,
                                        ViewsConstants::AWD,
                                        ViewsConstants::AWD_TP,
                                        ViewsConstants::BRC,
                                        ViewsConstants::C_JB_APL,
                                        ViewsConstants::CPN_PL,
                                        ViewsConstants::CPL,
                                        ViewsConstants::CPT,
                                        ViewsConstants::CRR,
                                        ViewsConstants::CST_QT,
                                        ViewsConstants::DDT_OPT,
                                        ViewsConstants::DOC,
                                        ViewsConstants::DOC_UP,
                                        ViewsConstants::DPT,
                                        ViewsConstants::DSG,
                                        ViewsConstants::EMP,
                                        ViewsConstants::EMP_ATD,
                                        ViewsConstants::GL_TP,
                                        ViewsConstants::HLD,
                                        ViewsConstants::HLD_CLD,
                                        ViewsConstants::ITV_SCD,
                                        ViewsConstants::JB,
                                        ViewsConstants::JB_APL,
                                        ViewsConstants::JB_CAT,
                                        ViewsConstants::JB_OB,
                                        ViewsConstants::JB_STG,
                                        ViewsConstants::LN_OPT,
                                        ViewsConstants::LV,
                                        ViewsConstants::LV_CLD,
                                        ViewsConstants::LV_RQ,
                                        ViewsConstants::LV_TP,
                                        ViewsConstants::PFM_TP,
                                        ViewsConstants::PLC,
                                        ViewsConstants::PRM,
                                        ViewsConstants::PY_SLP,
                                        ViewsConstants::PY_SLP_TP,
                                        ViewsConstants::RSG,
                                        ViewsConstants::S_SLR,
                                        ViewsConstants::TMN,
                                        ViewsConstants::TMN_TP,
                                        ViewsConstants::TNG,
                                        ViewsConstants::TRF,
                                        ViewsConstants::TRV,
                                        ViewsConstants::WRN
                                    ];

                                    $kebabSegments = array_map(function ($segment) {
                                        if ($segment === null) return null;
                                        return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                    }, $segments);

                                    $allSegments = array_merge($segments, $kebabSegments);
                                    $isHrmManagement = in_array(RF::segment(1), $allSegments);
                                    ?>
                                    <li
                                        class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isHrmManagement ? 'active dash-trigger' : ''); ?>">
                                        <a href="#!" class="dash-link">
                                            <span class="dash-micon">
                                                <i class="<?php echo e(VC::TI_USR); ?>"></i>
                                            </span>
                                            <span class="dash-mtext">
                                                <?php echo e(__('HRM System')); ?>

                                            </span>
                                            <span class="dash-arrow">
                                                <i data-feather="chevron-right"></i>
                                            </span>
                                        </a>
                                        <ul class="dash-submenu">
                                            <?php
                                            $isEmployee = strtolower($user[UsersConstants::COL_TP]) === 'employee';
                                            if ($isEmployee) {
                                                $employee = App\Models\Employee::where('user_id', $user?->id)->first();
                                                $empRoute = Route::has(ViewsConstants::EMP . '.show')
                                                    ? route(ViewsConstants::EMP . '.show', Illuminate\Support\Facades\Crypt::encrypt($employee->id))
                                                    : '#';
                                                $msgKey = 'show_employee_route_unavailable';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::EMP,
                                                    $msgKey
                                                ) ?? 'Employee view route is unavailable. Please contact technical support or your domain administrator.';
                                            } else {
                                                $empRoute = Route::has(ViewsConstants::EMP . '.index')
                                                    ? route(ViewsConstants::EMP . '.index')
                                                    : '#';
                                                $msgKey = 'employee_setup_route_unavailable';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::EMP,
                                                    $msgKey
                                                ) ?? 'Employee setup route is unavailable. Please contact technical support or your domain administrator.';
                                            }
                                            $linkId = 'employee-link';
                                            ?>
                                            <li class="dash-item <?php echo e(RF::segment(1) == ViewsConstants::EMP ? 'active dash-trigger' : ''); ?>">
                                                <a
                                                    id="<?php echo e($linkId); ?>"
                                                    class="dash-link"
                                                    href="<?php echo e($empRoute); ?>"
                                                    data-url="<?php echo e($empRoute); ?>"
                                                    data-sv-localized="true"
                                                    data-guard-msg="<?php echo e($message); ?>">
                                                    <?php echo e($isEmployee ? __('Employee') : __('Employee Setup')); ?>

                                                </a>
                                            </li>
                                            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                            <script defer>
                                                (() => {
                                                    const listenerAttr = 'data-employee-listener-active';
                                                    const el = document.getElementById('<?php echo e($linkId); ?>');
                                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                    el.setAttribute(listenerAttr, 'true');
                                                    el.addEventListener('click', event => {
                                                        try {
                                                            const url = el.getAttribute('data-url');
                                                            const href = el.href;
                                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                                event.preventDefault();
                                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                const containerId = 'toast-container';
                                                                let container = document.getElementById(containerId);
                                                                if (!container) {
                                                                    container = document.createElement('div');
                                                                    container.id = containerId;
                                                                    document.body.appendChild(container);
                                                                }
                                                                if (bootstrapLink && window.bootstrap) {
                                                                    const toastEl = document.createElement('div');
                                                                    toastEl.className = 'toast';
                                                                    toastEl.setAttribute('role', 'alert');
                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                    const body = document.createElement('div');
                                                                    body.className = 'toast-body';
                                                                    body.textContent = msg;
                                                                    toastEl.appendChild(body);
                                                                    container.appendChild(toastEl);
                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                } else {
                                                                    alert(msg);
                                                                }
                                                                el.setAttribute('data-failed-route', 'true');
                                                            }
                                                        } catch (error) {}
                                                    });
                                                    const observer = new MutationObserver(() => {
                                                        if (!document.body.contains(el)) {
                                                            observer.disconnect();
                                                            el.removeEventListener('click', () => {});
                                                        }
                                                    });
                                                    observer.observe(document.body, {
                                                        childList: true,
                                                        subtree: true
                                                    });
                                                })();
                                            </script>
                                            <?php $__env->stopPush(); ?>
                                            <?php if (Gate::check(PermissionsConstants::MNG_SSL) || Gate::check(PermissionsConstants::MNG_PSL)): ?>
                                                <?php
                                                $segments = [
                                                    ViewsConstants::PY_SLP,
                                                    ViewsConstants::S_SLR
                                                ];

                                                $kebabSegments = array_map(function ($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);

                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isPayrollSalary = in_array(RF::segment(1), $allSegments);
                                                ?>
                                                <li class="<?php echo e(VC::DSH_IT_MN); ?>  <?php echo e($isPayrollSalary ? 'active dash-trigger' : ''); ?>">
                                                    <a class="dash-link" href="#"><?php echo e(__('Payroll Setup')); ?>

                                                        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                    </a>
                                                    <ul class="dash-submenu">
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_SSL)): ?>
                                                            <?php
                                                            $setSalaryRoute = Route::has(ViewsConstants::S_SLR . '.index')
                                                                ? route(ViewsConstants::S_SLR . '.index')
                                                                : (Route::has(Str::kebab(ViewsConstants::S_SLR . '.index'))
                                                                    ? route(Str::kebab(ViewsConstants::S_SLR . '.index'))
                                                                    : '#');
                                                            $linkId = 'set-salary-link';
                                                            $message = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::S_SLR,
                                                                'set_salary_index_route_unavailable'
                                                            ) ?? 'Set salary route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e((request()->is('set_salaries*') || request()->is('set-salaries*')) ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($linkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($setSalaryRoute); ?>"
                                                                    data-url="<?php echo e($setSalaryRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($message); ?>">
                                                                    <?php echo e(__('Set salary')); ?>

                                                                </a>
                                                            </li>
                                                            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                            <script defer>
                                                                (() => {
                                                                    const listenerAttr = 'data-set-salary-listener-active';
                                                                    const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                    el.setAttribute(listenerAttr, 'true');
                                                                    el.addEventListener('click', event => {
                                                                        try {
                                                                            const url = el.getAttribute('data-url');
                                                                            const href = el.href;
                                                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                                                event.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                const containerId = 'toast-container';
                                                                                let container = document.getElementById(containerId);
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = containerId;
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bootstrapLink && window.bootstrap) {
                                                                                    const toastEl = document.createElement('div');
                                                                                    toastEl.className = 'toast';
                                                                                    toastEl.setAttribute('role', 'alert');
                                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toastEl.appendChild(body);
                                                                                    container.appendChild(toastEl);
                                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            }
                                                                        } catch (error) {}
                                                                    });
                                                                    const observer = new MutationObserver(() => {
                                                                        if (!document.body.contains(el)) {
                                                                            observer.disconnect();
                                                                            el.removeEventListener('click', () => {});
                                                                        }
                                                                    });
                                                                    observer.observe(document.body, {
                                                                        childList: true,
                                                                        subtree: true
                                                                    });
                                                                })();
                                                            </script>
                                                            <?php $__env->stopPush(); ?>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_PSL)): ?>
                                                            <?php
                                                            $payslipRoute = Route::has(ViewsConstants::PY_SLP . '.index')
                                                                ? route(ViewsConstants::PY_SLP . '.index')
                                                                : '#';
                                                            $linkId = 'payslip-link';
                                                            $message = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::PY_SLP,
                                                                'payslip_index_route_unavailable'
                                                            ) ?? 'Payslip route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e(request()->is('payslip*') ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($linkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($setSalaryRoute); ?>"
                                                                    data-url="<?php echo e($payslipRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($message); ?>">
                                                                    <?php echo e(__('Payslip')); ?>

                                                                </a>
                                                            </li>
                                                            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                            <script defer>
                                                                (() => {
                                                                    const listenerAttr = 'data-payslip-listener-active';
                                                                    const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                    el.setAttribute(listenerAttr, 'true');
                                                                    el.addEventListener('click', event => {
                                                                        try {
                                                                            const url = el.getAttribute('data-url');
                                                                            const href = el.href;
                                                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                                                event.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                const containerId = 'toast-container';
                                                                                let container = document.getElementById(containerId);
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = containerId;
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bootstrapLink && window.bootstrap) {
                                                                                    const toastEl = document.createElement('div');
                                                                                    toastEl.className = 'toast';
                                                                                    toastEl.setAttribute('role', 'alert');
                                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toastEl.appendChild(body);
                                                                                    container.appendChild(toastEl);
                                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            }
                                                                        } catch (error) {}
                                                                    });
                                                                    const observer = new MutationObserver(() => {
                                                                        if (!document.body.contains(el)) {
                                                                            observer.disconnect();
                                                                            el.removeEventListener('click', () => {});
                                                                        }
                                                                    });
                                                                    observer.observe(document.body, {
                                                                        childList: true,
                                                                        subtree: true
                                                                    });
                                                                })();
                                                            </script>
                                                            <?php $__env->stopPush(); ?>
                                                        <?php endif; ?>
                                                    </ul>
                                                </li>
                                            <?php endif; ?>
                                            <?php if (Gate::check(PermissionsConstants::MNG_LV) || Gate::check(PermissionsConstants::MNG_ATD)): ?>
                                                <?php
                                                $segments = [
                                                    ViewsConstants::EMP_ATD,
                                                    ViewsConstants::LV
                                                ];

                                                $kebabSegments = array_map(function ($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);

                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isAttendanceLeave = in_array(RF::segment(1), $allSegments);
                                                ?>
                                                <li
                                                    class="<?php echo e(VC::DSH_IT_MN); ?>  <?php echo e($isAttendanceLave ? 'active dash-trigger' : ''); ?>">
                                                    <a class="dash-link" href="#"><?php echo e(__('Leave Management Setup')); ?>

                                                        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                    </a>
                                                    <ul class="dash-submenu">
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_LV)): ?>
                                                            <?php
                                                            $manageLeaveRoute = Route::has(ViewsConstants::LV . '.index')
                                                                ? route(ViewsConstants::LV . '.index')
                                                                : '#';
                                                            $linkId = 'manage-leave-link';
                                                            $message = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::LV,
                                                                'leave_index_route_unavailable'
                                                            ) ?? 'Manage leave route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::LV . '.index' ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($linkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($manageLeaveRoute); ?>"
                                                                    data-url="<?php echo e($manageLeaveRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($message); ?>">
                                                                    <?php echo e(__('Manage Leave')); ?>

                                                                </a>
                                                            </li>
                                                            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                            <script defer>
                                                                (() => {
                                                                    const listenerAttr = 'data-manage-leave-listener-active';
                                                                    const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                    el.setAttribute(listenerAttr, 'true');
                                                                    el.addEventListener('click', event => {
                                                                        try {
                                                                            const url = el.getAttribute('data-url');
                                                                            const href = el.href;
                                                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                                                event.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                const containerId = 'toast-container';
                                                                                let container = document.getElementById(containerId);
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = containerId;
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bootstrapLink && window.bootstrap) {
                                                                                    const toastEl = document.createElement('div');
                                                                                    toastEl.className = 'toast';
                                                                                    toastEl.setAttribute('role', 'alert');
                                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toastEl.appendChild(body);
                                                                                    container.appendChild(toastEl);
                                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            }
                                                                        } catch (error) {}
                                                                    });
                                                                    const observer = new MutationObserver(() => {
                                                                        if (!document.body.contains(el)) {
                                                                            observer.disconnect();
                                                                            el.removeEventListener('click', () => {});
                                                                        }
                                                                    });
                                                                    observer.observe(document.body, {
                                                                        childList: true,
                                                                        subtree: true
                                                                    });
                                                                })();
                                                            </script>
                                                            <?php $__env->stopPush(); ?>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_ATD)): ?>
                                                            <?php
                                                            $segments = [
                                                                ViewsConstants::EMP_ATD
                                                            ];

                                                            $kebabSegments = array_map(function ($segment) {
                                                                if ($segment === null) return null;
                                                                return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                            }, $segments);

                                                            $allSegments = array_merge($segments, $kebabSegments);
                                                            $isEmployeeAttendance = in_array(RF::segment(1), $allSegments);
                                                            ?>
                                                            <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isEmployeeAttendance ? 'active dash-trigger' : ''); ?>"
                                                                href="#navbar-attendance" data-toggle="collapse" role="button"
                                                                aria-expanded="<?php echo e($isEmployeeAttendance ? 'true' : 'false'); ?>">
                                                                <a class="dash-link" href="#"><?php echo e(__('Attendance')); ?>

                                                                    <span class="dash-arrow">
                                                                        <i data-feather="chevron-right"></i>
                                                                    </span>
                                                                </a>
                                                                <ul class="dash-submenu">
                                                                    <?php
                                                                    $markAttendanceRoute = Route::has(ViewsConstants::EMP_ATD . '.index')
                                                                        ? route(ViewsConstants::EMP_ATD . '.index')
                                                                        : (Route::has(Str::kebab(ViewsConstants::EMP_ATD . '.index'))
                                                                            ? route(Str::kebab(ViewsConstants::EMP_ATD . '.index'))
                                                                            : '#');
                                                                    $linkId = 'mark-attendance-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        ViewsConstants::EMP_ATD,
                                                                        'attendance_index_route_unavailable'
                                                                    ) ?? 'Mark attendance route is unavailable. Please contact technical support or your domain administrator.';
                                                                    ?>
                                                                    <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::EMP_ATD . '.index' ? 'active' : ''); ?>">
                                                                        <a
                                                                            id="<?php echo e($linkId); ?>"
                                                                            class="dash-link"
                                                                            href="<?php echo e($markAttendanceRoute); ?>"
                                                                            data-url="<?php echo e($markAttendanceRoute); ?>"
                                                                            data-sv-localized="true"
                                                                            data-guard-msg="<?php echo e($message); ?>">
                                                                            <?php echo e(__('Mark Attendance')); ?>

                                                                        </a>
                                                                    </li>
                                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                    <script defer>
                                                                        (() => {
                                                                            const listenerAttr = 'data-mark-attendance-listener-active';
                                                                            const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                            el.setAttribute(listenerAttr, 'true');
                                                                            el.addEventListener('click', event => {
                                                                                try {
                                                                                    const url = el.getAttribute('data-url');
                                                                                    const href = el.href;
                                                                                    if ((!url || url === '#') && (!href || href === '#')) {
                                                                                        event.preventDefault();
                                                                                        const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                        const containerId = 'toast-container';
                                                                                        let container = document.getElementById(containerId);
                                                                                        if (!container) {
                                                                                            container = document.createElement('div');
                                                                                            container.id = containerId;
                                                                                            document.body.appendChild(container);
                                                                                        }
                                                                                        if (bootstrapLink && window.bootstrap) {
                                                                                            const toastEl = document.createElement('div');
                                                                                            toastEl.className = 'toast';
                                                                                            toastEl.setAttribute('role', 'alert');
                                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                                            const body = document.createElement('div');
                                                                                            body.className = 'toast-body';
                                                                                            body.textContent = msg;
                                                                                            toastEl.appendChild(body);
                                                                                            container.appendChild(toastEl);
                                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                        } else {
                                                                                            alert(msg);
                                                                                        }
                                                                                        el.setAttribute('data-failed-route', 'true');
                                                                                    }
                                                                                } catch (error) {}
                                                                            });
                                                                            const observer = new MutationObserver(() => {
                                                                                if (!document.body.contains(el)) {
                                                                                    observer.disconnect();
                                                                                    el.removeEventListener('click', () => {});
                                                                                }
                                                                            });
                                                                            observer.observe(document.body, {
                                                                                childList: true,
                                                                                subtree: true
                                                                            });
                                                                        })();
                                                                    </script>
                                                                    <?php $__env->stopPush(); ?>
                                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::CR_ATD)): ?>
                                                                        <?php
                                                                        $bulkAttendanceRoute = Route::has(ViewsConstants::EMP_ATD . '.' . EAC::BK_ATD)
                                                                            ? route(ViewsConstants::EMP_ATD . '.' . EAC::BK_ATD)
                                                                            : (Route::has(Str::kebab(Route::has(ViewsConstants::EMP_ATD . '.' . EAC::BK_ATD)))
                                                                                ? route(Str::kebab(ViewsConstants::EMP_ATD . '.' . EAC::BK_ATD))
                                                                                : '#');
                                                                        $linkId = 'bulk-attendance-link';
                                                                        $message = Utility::fetchLinkMessage(
                                                                            $lang,
                                                                            ViewsConstants::EMP_ATD,
                                                                            'bulk_attendance_route_unavailable'
                                                                        ) ?? 'Bulk attendance route is unavailable. Please contact technical support or your domain administrator.';
                                                                        ?>
                                                                        <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::EMP_ATD . '.' . EAC::BK_ATD ? 'active' : ''); ?>">
                                                                            <a
                                                                                id="<?php echo e($linkId); ?>"
                                                                                class="dash-link"
                                                                                href="<?php echo e($bulkAttendanceRoute); ?>"
                                                                                data-url="<?php echo e($bulkAttendanceRoute); ?>"
                                                                                data-sv-localized="true"
                                                                                data-guard-msg="<?php echo e($message); ?>">
                                                                                <?php echo e(__('Bulk Attendance')); ?>

                                                                            </a>
                                                                        </li>
                                                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                        <script defer>
                                                                            (() => {
                                                                                const listenerAttr = 'data-bulk-attendance-listener-active';
                                                                                const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                                el.setAttribute(listenerAttr, 'true');
                                                                                el.addEventListener('click', event => {
                                                                                    try {
                                                                                        const url = el.getAttribute('data-url');
                                                                                        const href = el.href;
                                                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                                                            event.preventDefault();
                                                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                            const containerId = 'toast-container';
                                                                                            let container = document.getElementById(containerId);
                                                                                            if (!container) {
                                                                                                container = document.createElement('div');
                                                                                                container.id = containerId;
                                                                                                document.body.appendChild(container);
                                                                                            }
                                                                                            if (bootstrapLink && window.bootstrap) {
                                                                                                const toastEl = document.createElement('div');
                                                                                                toastEl.className = 'toast';
                                                                                                toastEl.setAttribute('role', 'alert');
                                                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                                                const body = document.createElement('div');
                                                                                                body.className = 'toast-body';
                                                                                                body.textContent = msg;
                                                                                                toastEl.appendChild(body);
                                                                                                container.appendChild(toastEl);
                                                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                            } else {
                                                                                                alert(msg);
                                                                                            }
                                                                                            el.setAttribute('data-failed-route', 'true');
                                                                                        }
                                                                                    } catch (error) {}
                                                                                });
                                                                                const observer = new MutationObserver(() => {
                                                                                    if (!document.body.contains(el)) {
                                                                                        observer.disconnect();
                                                                                        el.removeEventListener('click', () => {});
                                                                                    }
                                                                                });
                                                                                observer.observe(document.body, {
                                                                                    childList: true,
                                                                                    subtree: true
                                                                                });
                                                                            })();
                                                                        </script>
                                                                        <?php $__env->stopPush(); ?>
                                                                    <?php endif; ?>
                                                                </ul>
                                                            </li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </li>
                                            <?php endif; ?>
                                            <?php if (Gate::check(PermissionsConstants::MNG_IND) || Gate::check(PermissionsConstants::MNG_APR) || Gate::check(PermissionsConstants::MNG_GTR)): ?>
                                                <?php
                                                $segments = [
                                                    ViewsConstants::APR,
                                                    ViewsConstants::GL_TRC,
                                                    ViewsConstants::IND
                                                ];

                                                $kebabSegments = array_map(function ($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);

                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isIndicatorApproval = in_array(RF::segment(1), $allSegments);
                                                ?>
                                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isIndicatorApproval ? 'active dash-trigger' : ''); ?>"
                                                    href="#navbar-performance" data-toggle="collapse" role="button"
                                                    aria-expanded="<?php echo e($isIndicatorApproval ? 'true' : 'false'); ?>">
                                                    <a class="dash-link" href="#"><?php echo e(__('Performance Setup')); ?>

                                                        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                    </a>
                                                    <ul class="dash-submenu <?php echo e($isIndicatorApproval ? 'show' : 'collapse'); ?>">
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_IND)): ?>
                                                            <?php
                                                            $indicatorIndexRoute = Route::has(ViewsConstants::IND . '.index')
                                                                ? route(ViewsConstants::IND . '.index')
                                                                : '#';
                                                            $linkId = 'indicator-index-link';
                                                            $message = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::IND,
                                                                'indicator_index_route_unavailable'
                                                            ) ?? 'Indicator index route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e(request()->is('indicator*') ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($linkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($indicatorIndexRoute); ?>"
                                                                    data-url="<?php echo e($indicatorIndexRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($message); ?>">
                                                                    <?php echo e(__('Indicator')); ?>

                                                                </a>
                                                            </li>
                                                            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                            <script defer>
                                                                (() => {
                                                                    const listenerAttr = 'data-indicator-index-listener-active';
                                                                    const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                    el.setAttribute(listenerAttr, 'true');
                                                                    el.addEventListener('click', event => {
                                                                        try {
                                                                            const url = el.getAttribute('data-url');
                                                                            const href = el.href;
                                                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                                                event.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                const containerId = 'toast-container';
                                                                                let container = document.getElementById(containerId);
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = containerId;
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bootstrapLink && window.bootstrap) {
                                                                                    const toastEl = document.createElement('div');
                                                                                    toastEl.className = 'toast';
                                                                                    toastEl.setAttribute('role', 'alert');
                                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toastEl.appendChild(body);
                                                                                    container.appendChild(toastEl);
                                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            }
                                                                        } catch (error) {}
                                                                    });
                                                                    const observer = new MutationObserver(() => {
                                                                        if (!document.body.contains(el)) {
                                                                            observer.disconnect();
                                                                            el.removeEventListener('click', () => {});
                                                                        }
                                                                    });
                                                                    observer.observe(document.body, {
                                                                        childList: true,
                                                                        subtree: true
                                                                    });
                                                                })();
                                                            </script>
                                                            <?php $__env->stopPush(); ?>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_APR)): ?>
                                                            <?php
                                                            $appraisalIndexRoute = Route::has(ViewsConstants::APR . '.index')
                                                                ? route(ViewsConstants::APR . '.index')
                                                                : '#';
                                                            $linkId = 'appraisal-index-link';
                                                            $message = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::APR,
                                                                'appraisal_index_route_unavailable'
                                                            ) ?? 'Appraisal index route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e(request()->is('appraisal*') ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($linkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($indicatorIndexRoute); ?>"
                                                                    data-url="<?php echo e($appraisalIndexRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($message); ?>">
                                                                    <?php echo e(__(ViewsConstants::APR)); ?>

                                                                </a>
                                                            </li>
                                                            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                            <script defer>
                                                                (() => {
                                                                    const listenerAttr = 'data-appraisal-index-listener-active';
                                                                    const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                    el.setAttribute(listenerAttr, 'true');
                                                                    el.addEventListener('click', event => {
                                                                        try {
                                                                            const url = el.getAttribute('data-url');
                                                                            const href = el.href;
                                                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                                                event.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                const containerId = 'toast-container';
                                                                                let container = document.getElementById(containerId);
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = containerId;
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bootstrapLink && window.bootstrap) {
                                                                                    const toastEl = document.createElement('div');
                                                                                    toastEl.className = 'toast';
                                                                                    toastEl.setAttribute('role', 'alert');
                                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toastEl.appendChild(body);
                                                                                    container.appendChild(toastEl);
                                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            }
                                                                        } catch (error) {}
                                                                    });
                                                                    const observer = new MutationObserver(() => {
                                                                        if (!document.body.contains(el)) {
                                                                            observer.disconnect();
                                                                            el.removeEventListener('click', () => {});
                                                                        }
                                                                    });
                                                                    observer.observe(document.body, {
                                                                        childList: true,
                                                                        subtree: true
                                                                    });
                                                                })();
                                                            </script>
                                                            <?php $__env->stopPush(); ?>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_GTR)): ?>
                                                            <?php
                                                            $goalTrackingRoute = Route::has(ViewsConstants::GL_TRC . '.index')
                                                                ? route(ViewsConstants::GL_TRC . '.index')
                                                                : (Route::has(Str::kebab(ViewsConstants::GL_TRC . '.index'))
                                                                    ? route(Str::kebab(ViewsConstants::GL_TRC . '.index'))
                                                                    : '#');
                                                            $linkId = 'goal-tracking-index-link';
                                                            $message = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::GL_TRC,
                                                                'goal_tracking_index_route_unavailable'
                                                            ) ?? 'Goal Tracking route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e((request()->is('goal-tracking*') || request()->is('goal_tracking*')) ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($linkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($indicatorIndexRoute); ?>"
                                                                    data-url="<?php echo e($goalTrackingRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($message); ?>">
                                                                    <?php echo e(__('Goal Tracking')); ?>

                                                                </a>
                                                            </li>
                                                            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                            <script defer>
                                                                (() => {
                                                                    const listenerAttr = 'data-goal-tracking-index-listener-active';
                                                                    const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                    el.setAttribute(listenerAttr, 'true');
                                                                    el.addEventListener('click', event => {
                                                                        try {
                                                                            const url = el.getAttribute('data-url');
                                                                            const href = el.href;
                                                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                                                event.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                const containerId = 'toast-container';
                                                                                let container = document.getElementById(containerId);
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = containerId;
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bootstrapLink && window.bootstrap) {
                                                                                    const toastEl = document.createElement('div');
                                                                                    toastEl.className = 'toast';
                                                                                    toastEl.setAttribute('role', 'alert');
                                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toastEl.appendChild(body);
                                                                                    container.appendChild(toastEl);
                                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            }
                                                                        } catch (error) {}
                                                                    });
                                                                    const observer = new MutationObserver(() => {
                                                                        if (!document.body.contains(el)) {
                                                                            observer.disconnect();
                                                                            el.removeEventListener('click', () => {});
                                                                        }
                                                                    });
                                                                    observer.observe(document.body, {
                                                                        childList: true,
                                                                        subtree: true
                                                                    });
                                                                })();
                                                            </script>
                                                            <?php $__env->stopPush(); ?>
                                                        <?php endif; ?>
                                                    </ul>
                                                </li>
                                            <?php endif; ?>
                                            <?php if (Gate::check(PermissionsConstants::MNG_TNG) || Gate::check(PermissionsConstants::MNG_TNR) || Gate::check(PermissionsConstants::SHW_TNG)): ?>
                                                <?php
                                                $isTraining = RF::segment(1) === ViewsConstants::TNR || RF::segment(1) === ViewsConstants::TNG;
                                                ?>
                                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isTraining ? 'active dash-trigger' : ''); ?>"
                                                    href="#navbar-training" data-toggle="collapse" role="button"
                                                    aria-expanded="<?php echo e($isTraining ? 'true' : 'false'); ?>">
                                                    <a class="dash-link" href="#"><?php echo e(__('Training Setup')); ?>

                                                        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                    </a>
                                                    <ul class="dash-submenu">
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_TNG)): ?>
                                                            <?php
                                                            $trainingIndexRoute = Route::has(ViewsConstants::TNG . '.index')
                                                                ? route(ViewsConstants::TNG . '.index')
                                                                : '#';
                                                            $linkId = 'training-index-link';
                                                            $message = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::TRAINING,
                                                                'training_index_route_unavailable'
                                                            ) ?? 'Training list route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e(request()->is('training*') ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($linkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($trainingIndexRoute); ?>"
                                                                    data-url="<?php echo e($trainingIndexRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($message); ?>">
                                                                    <?php echo e(__('Training List')); ?>

                                                                </a>
                                                            </li>
                                                            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                            <script defer>
                                                                (() => {
                                                                    const listenerAttr = 'data-training-listener-active';
                                                                    const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                    el.setAttribute(listenerAttr, 'true');
                                                                    el.addEventListener('click', event => {
                                                                        try {
                                                                            const url = el.getAttribute('data-url');
                                                                            const href = el.href;
                                                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                                                event.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                const containerId = 'toast-container';
                                                                                let container = document.getElementById(containerId);
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = containerId;
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bootstrapLink && window.bootstrap) {
                                                                                    const toastEl = document.createElement('div');
                                                                                    toastEl.className = 'toast';
                                                                                    toastEl.setAttribute('role', 'alert');
                                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toastEl.appendChild(body);
                                                                                    container.appendChild(toastEl);
                                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            }
                                                                        } catch (error) {}
                                                                    });
                                                                    const observer = new MutationObserver(() => {
                                                                        if (!document.body.contains(el)) {
                                                                            observer.disconnect();
                                                                            el.removeEventListener('click', () => {});
                                                                        }
                                                                    });
                                                                    observer.observe(document.body, {
                                                                        childList: true,
                                                                        subtree: true
                                                                    });
                                                                })();
                                                            </script>
                                                            <?php $__env->stopPush(); ?>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_TNR)): ?>
                                                            <?php
                                                            $trainerIndexRoute = Route::has(ViewsConstants::TNR . '.index')
                                                                ? route(ViewsConstants::TNR . '.index')
                                                                : '#';
                                                            $linkId = 'trainer-index-link';
                                                            $message = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::TNR,
                                                                'trainer_index_route_unavailable'
                                                            ) ?? 'Trainer index route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e(request()->is('trainer*') ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($linkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($trainerIndexRoute); ?>"
                                                                    data-url="<?php echo e($trainerIndexRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($message); ?>">
                                                                    <?php echo e(__('Trainer')); ?>

                                                                </a>
                                                            </li>
                                                            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                            <script defer>
                                                                (() => {
                                                                    const listenerAttr = 'data-trainer-index-listener-active';
                                                                    const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                    el.setAttribute(listenerAttr, 'true');
                                                                    el.addEventListener('click', event => {
                                                                        try {
                                                                            const url = el.getAttribute('data-url');
                                                                            const href = el.href;
                                                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                                                event.preventDefault();
                                                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                const containerId = 'toast-container';
                                                                                let container = document.getElementById(containerId);
                                                                                if (!container) {
                                                                                    container = document.createElement('div');
                                                                                    container.id = containerId;
                                                                                    document.body.appendChild(container);
                                                                                }
                                                                                if (bootstrapLink && window.bootstrap) {
                                                                                    const toastEl = document.createElement('div');
                                                                                    toastEl.className = 'toast';
                                                                                    toastEl.setAttribute('role', 'alert');
                                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                                    const body = document.createElement('div');
                                                                                    body.className = 'toast-body';
                                                                                    body.textContent = msg;
                                                                                    toastEl.appendChild(body);
                                                                                    container.appendChild(toastEl);
                                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                el.setAttribute('data-failed-route', 'true');
                                                                            }
                                                                        } catch (error) {}
                                                                    });
                                                                    const observer = new MutationObserver(() => {
                                                                        if (!document.body.contains(el)) {
                                                                            observer.disconnect();
                                                                            el.removeEventListener('click', () => {});
                                                                        }
                                                                    });
                                                                    observer.observe(document.body, {
                                                                        childList: true,
                                                                        subtree: true
                                                                    });
                                                                })();
                                                            </script>
                                                            <?php $__env->stopPush(); ?>
                                                        <?php endif; ?>
                                                    </ul>
                                                </li>
                                            <?php endif; ?>
                                            <?php if (
                                                Gate::check(PermissionsConstants::MNG_JB) ||
                                                Gate::check(PermissionsConstants::CR_JB) ||
                                                Gate::check(PermissionsConstants::MNG_JB_APL) ||
                                                Gate::check(PermissionsConstants::MNG_CST_QT) ||
                                                Gate::check(PermissionsConstants::SHW_ITV_SCHD) ||
                                                Gate::check(PermissionsConstants::SHW_CRR)
                                            ): ?>
                                                <?php
                                                $segments = [
                                                    ViewsConstants::C_JB_APL,
                                                    ViewsConstants::CRR,
                                                    ViewsConstants::CST_QT,
                                                    ViewsConstants::ITV_SCD,
                                                    ViewsConstants::JB,
                                                    ViewsConstants::JB_APL,
                                                    ViewsConstants::JB_OB
                                                ];

                                                $kebabSegments = array_map(function ($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);

                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isRecruitment = in_array(RF::segment(1), $allSegments);
                                                ?>
                                                <li class="<?php echo e($isRecruitment ? 'active dash-trigger' : ''); ?>">
                                                    <a class="dash-link" href="#"><?php echo e(__('Recruitment Setup')); ?>

                                                        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                    </a>
                                                    <ul class="dash-submenu">
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_JB)): ?>
                                                            <?php
                                                            $routeName = RF::route()->getName();
                                                            ?>
                                                            <li
                                                                class="dash-item <?php echo e($routeName == ViewsConstants::JB . '.index' || $routeName == ViewsConstants::JB . '.create' || $routeName == ViewsConstants::JB . '.edit' || $routeName == ViewsConstants::JB . '.show' ? 'active' : ''); ?>">
                                                                <?php
                                                                $jobsIndexRoute = Route::has(ViewsConstants::JB . '.index')
                                                                    ? route(ViewsConstants::JB . '.index')
                                                                    : '#';
                                                                $linkId = 'job-index-link';
                                                                $message = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    ViewsConstants::JB,
                                                                    'job_index_route_unavailable'
                                                                ) ?? 'Jobs route is unavailable. Please contact technical support or your domain administrator.';
                                                                ?>
                                                                <a
                                                                    id="<?php echo e($linkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($jobsIndexRoute); ?>"
                                                                    data-url="<?php echo e($jobsIndexRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($message); ?>">
                                                                    <?php echo e(__('Jobs')); ?>

                                                                </a>
                                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                                <script defer>
                                                                    (() => {
                                                                        const listenerAttr = 'data-job-index-listener-active';
                                                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                        el.setAttribute(listenerAttr, 'true');
                                                                        el.addEventListener('click', event => {
                                                                            try {
                                                                                const url = el.getAttribute('data-url');
                                                                                const href = el.href;
                                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                                    event.preventDefault();
                                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                    const containerId = 'toast-container';
                                                                                    let container = document.getElementById(containerId);
                                                                                    if (!container) {
                                                                                        container = document.createElement('div');
                                                                                        container.id = containerId;
                                                                                        document.body.appendChild(container);
                                                                                    }
                                                                                    if (bootstrapLink && window.bootstrap) {
                                                                                        const toastEl = document.createElement('div');
                                                                                        toastEl.className = 'toast';
                                                                                        toastEl.setAttribute('role', 'alert');
                                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
                                                                                        toastEl.appendChild(body);
                                                                                        container.appendChild(toastEl);
                                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                    } else {
                                                                                        alert(msg);
                                                                                    }
                                                                                    el.setAttribute('data-failed-route', 'true');
                                                                                }
                                                                            } catch (error) {}
                                                                        });
                                                                        const observer = new MutationObserver(() => {
                                                                            if (!document.body.contains(el)) {
                                                                                observer.disconnect();
                                                                                el.removeEventListener('click', () => {});
                                                                            }
                                                                        });
                                                                        observer.observe(document.body, {
                                                                            childList: true,
                                                                            subtree: true
                                                                        });
                                                                    })();
                                                                </script>
                                                                <?php $__env->stopPush(); ?>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::CR_JB)): ?>
                                                            <?php
                                                            $jobCreateRoute = Route::has(ViewsConstants::JB . '.create')
                                                                ? route(ViewsConstants::JB . '.create')
                                                                : '#';
                                                            $jobCreateLinkId = 'job-create-link';
                                                            $jobCreateMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::JB,
                                                                'job_create_route_unavailable'
                                                            ) ?? 'Job Create route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::JB . '.create' ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($jobCreateLinkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($jobCreateRoute); ?>"
                                                                    data-url="<?php echo e($jobCreateRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($jobCreateMessage); ?>">
                                                                    <?php echo e(__('Job Create')); ?>

                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_JB_APL)): ?>
                                                            <?php
                                                            $jobAppRoute = Route::has(ViewsConstants::JB_APL . '.index')
                                                                ? route(ViewsConstants::JB_APL . '.index')
                                                                : (Router::has(Str::kebab(ViewsConstants::JB_APL . '.index'))
                                                                    ? route(Str::kebab(ViewsConstants::JB_APL . '.index'))
                                                                    : '#');
                                                            $jobAppLinkId = 'job-application-link';
                                                            $jobAppMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::JB,
                                                                'job_application_index_route_unavailable'
                                                            ) ?? 'Job Application route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e((request()->is('job-application*') || request()->is('job_application*')) ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($jobAppLinkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($jobAppRoute); ?>"
                                                                    data-url="<?php echo e($jobAppRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($jobAppMessage); ?>">
                                                                    <?php echo e(__('Job Application')); ?>

                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_JB_APL)): ?>
                                                            <?php
                                                            $jobCandRoute = Route::has(ViewsConstants::JB . '.application.candidate')
                                                                ? route(ViewsConstants::JB . '.application.candidate')
                                                                : '#';
                                                            $jobCandLinkId = 'job-candidate-link';
                                                            $jobCandMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::JB,
                                                                'job_candidate_route_unavailable'
                                                            ) ?? 'Job Candidate route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e((request()->is('job-application/candidate*') || request()->is('job_application/candidate*')) ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($jobCandLinkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($jobCandRoute); ?>"
                                                                    data-url="<?php echo e($jobCandRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($jobCandMessage); ?>">
                                                                    <?php echo e(__('Job Candidate')); ?>

                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_JB_APL)): ?>
                                                            <?php
                                                            $jobOnBoardRoute = Route::has(ViewsConstants::JB . '.on.board')
                                                                ? route(ViewsConstants::JB . '.on.board')
                                                                : '#';
                                                            $jobOnBoardLinkId = 'job-on-board-link';
                                                            $jobOnBoardMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::JB,
                                                                'job_on_board_route_unavailable'
                                                            ) ?? 'Job On-boarding route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e((request()->is('jobs-onboard*') || request()->is('jobs_onboard*')) ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($jobOnBoardLinkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($jobOnBoardRoute); ?>"
                                                                    data-url="<?php echo e($jobOnBoardRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($jobOnBoardMessage); ?>">
                                                                    <?php echo e(__('Job On-boarding')); ?>

                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_CST_QT)): ?>
                                                            <?php
                                                            $customQRoute = Route::has(ViewsConstants::CST_QT . '.index')
                                                                ? route(ViewsConstants::CST_QT . '.index')
                                                                : (Route::has(Str::kebab(ViewsConstants::CST_QT . '.index'))
                                                                    ? route(Str::kebab(ViewsConstants::CST_QT . '.index'))
                                                                    : '#');
                                                            $customQLinkId = 'custom-question-link';
                                                            $customQMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::CST_QT,
                                                                'custom_question_index_route_unavailable'
                                                            ) ?? 'Custom Question route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e((request()->is('custom-question*') || request()->is('custom_question*')) ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($customQLinkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($customQRoute); ?>"
                                                                    data-url="<?php echo e($customQRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($customQMessage); ?>">
                                                                    <?php echo e(__('Custom Question')); ?>

                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::SHW_ITV_SCHD)): ?>
                                                            <?php
                                                            $intvSchedRoute = Route::has(ViewsConstants::ITV_SCD . '.index')
                                                                ? route(ViewsConstants::ITV_SCD . '.index')
                                                                : (Route::has(Str::kebab(ViewsConstants::ITV_SCD . '.index'))
                                                                    ? route(Str::kebab(ViewsConstants::ITV_SCD . '.index'))
                                                                    : '#');
                                                            $intvSchedLinkId = 'interview-schedule-link';
                                                            $intvSchedMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::ITV_SCD,
                                                                'interview_schedule_index_route_unavailable'
                                                            ) ?? 'Interview Schedule route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e(request()->is('interview-schedule*') ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($intvSchedLinkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($intvSchedRoute); ?>"
                                                                    data-url="<?php echo e($intvSchedRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($intvSchedMessage); ?>">
                                                                    <?php echo e(__('Interview Schedule')); ?>

                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::SHW_CRR)): ?>
                                                            <?php
                                                            $careerRoute = Route::has(ViewsConstants::CRR)
                                                                ? route(ViewsConstants::CRR, [$user?->creatorId(), $lang])
                                                                : '#';
                                                            $careerLinkId = 'career-index-link';
                                                            $careerMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::CRR,
                                                                'career_index_route_unavailable'
                                                            ) ?? 'Career route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e(request()->is('career*') ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($careerLinkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($careerRoute); ?>"
                                                                    data-url="<?php echo e($careerRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($careerMessage); ?>">
                                                                    <?php echo e(__('Career')); ?>

                                                                </a>
                                                            </li>
                                                            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                            <script defer>
                                                                (() => {
                                                                    const bindGuard = (id) => {
                                                                        const listenerAttr = `data-${id}-listener-active`;
                                                                        const el = document.getElementById(`${id}`);
                                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                        el.setAttribute(listenerAttr, 'true');
                                                                        el.addEventListener('click', event => {
                                                                            try {
                                                                                const url = el.getAttribute('data-url');
                                                                                const href = el.href;
                                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                                    event.preventDefault();
                                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                                    const containerId = 'toast-container';
                                                                                    let container = document.getElementById(containerId);
                                                                                    if (!container) {
                                                                                        container = document.createElement('div');
                                                                                        container.id = containerId;
                                                                                        document.body.appendChild(container);
                                                                                    }
                                                                                    if (bootstrapLink && window.bootstrap) {
                                                                                        const toastEl = document.createElement('div');
                                                                                        toastEl.className = 'toast';
                                                                                        toastEl.setAttribute('role', 'alert');
                                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                                        const body = document.createElement('div');
                                                                                        body.className = 'toast-body';
                                                                                        body.textContent = msg;
                                                                                        toastEl.appendChild(body);
                                                                                        container.appendChild(toastEl);
                                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                                    } else {
                                                                                        alert(msg);
                                                                                    }
                                                                                    el.setAttribute('data-failed-route', 'true');
                                                                                }
                                                                            } catch (error) {}
                                                                        });
                                                                        const observer = new MutationObserver(() => {
                                                                            const el = document.getElementById(`${id}`);
                                                                            if (!el) observer.disconnect();
                                                                        });
                                                                        observer.observe(document.body, {
                                                                            childList: true,
                                                                            subtree: true
                                                                        });
                                                                    };
                                                                    [
                                                                        'job-index-link',
                                                                        'job-create-link',
                                                                        'job-application-link',
                                                                        'job-candidate-link',
                                                                        'job-on-board-link',
                                                                        'custom-question-link',
                                                                        'interview-schedule-link',
                                                                        'career-index-link'
                                                                    ].forEach(bindGuard);
                                                                })();
                                                            </script>
                                                            <?php $__env->stopPush(); ?>
                                                        <?php endif; ?>
                                                    </ul>
                                                </li>
                                            <?php endif; ?>
                                            <?php
                                            $permissions = [
                                                PermissionsConstants::MNG_AWD,
                                                PermissionsConstants::MNG_TRF,
                                                PermissionsConstants::MNG_RSG,
                                                PermissionsConstants::MNG_TRV,
                                                PermissionsConstants::MNG_PRM,
                                                PermissionsConstants::MNG_CPT,
                                                PermissionsConstants::MNG_WRN,
                                                PermissionsConstants::MNG_TRM,
                                                PermissionsConstants::MNG_ANC,
                                                PermissionsConstants::MNG_HLD
                                            ];
                                            $hasPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                                            $segments = [
                                                ViewsConstants::ANC,
                                                ViewsConstants::AWD,
                                                ViewsConstants::CPN,
                                                ViewsConstants::CPT,
                                                ViewsConstants::HLD,
                                                ViewsConstants::HLD_CLD,
                                                ViewsConstants::PLC,
                                                ViewsConstants::PRM,
                                                ViewsConstants::RSG,
                                                ViewsConstants::TMN,
                                                ViewsConstants::TRF,
                                                ViewsConstants::TRV,
                                                ViewsConstants::WRN
                                            ];
                                            $kebabSegments = array_map(function ($segment) {
                                                if ($segment === null) return null;
                                                return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                            }, $segments);
                                            $allSegments = array_merge($segments, $kebabSegments);
                                            $isEmployeeManagement = in_array(RF::segment(1), $allSegments);
                                            ?>
                                            <?php if ($hasPermission): ?>
                                                <li
                                                    class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isEmployeeManagement ? 'active dash-trigger' : ''); ?>">
                                                    <a class="dash-link" href="#"><?php echo e(__('HR Admin Setup')); ?>

                                                        <span class="dash-arrow">
                                                            < data-feather="chevron-right"></ i>
                                                        </span>
                                                    </a>
                                                    <ul class="dash-submenu">
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_AWD)): ?>
                                                            <?php
                                                            $awardIndexRoute = Route::has(ViewsConstants::AWD . '.index')
                                                                ? route(ViewsConstants::AWD . '.index')
                                                                : '#';
                                                            $awardIndexLinkId = 'award-index-link';
                                                            $awardIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::AWD,
                                                                'award_index_route_unavailable'
                                                            ) ?? 'Award index route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e(request()->is('award*') ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($awardIndexLinkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($awardIndexRoute); ?>"
                                                                    data-url="<?php echo e($awardIndexRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($awardIndexMessage); ?>">
                                                                    <?php echo e(__('Award')); ?>

                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_TRF)): ?>
                                                            <?php
                                                            $transferIndexRoute = Route::has(ViewsConstants::TRF . '.index')
                                                                ? route(ViewsConstants::TRF . '.index')
                                                                : '#';
                                                            $transferIndexLinkId = 'transfer-index-link';
                                                            $transferIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::TRF,
                                                                'transfer_index_route_unavailable'
                                                            ) ?? 'Transfer index route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e(request()->is('transfer*') ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($transferIndexLinkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($transferIndexRoute); ?>"
                                                                    data-url="<?php echo e($transferIndexRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($transferIndexMessage); ?>">
                                                                    <?php echo e(__('Transfer')); ?>

                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_RSG)): ?>
                                                            <?php
                                                            $resignationIndexRoute = Route::has(ViewsConstants::RSG . '.index')
                                                                ? route(ViewsConstants::RSG . '.index')
                                                                : '#';
                                                            $resignationIndexLinkId = 'resignation-index-link';
                                                            $resignationIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::RSG,
                                                                'resignation_index_route_unavailable'
                                                            ) ?? 'Resignation index route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e(request()->is('resignation*') ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($resignationIndexLinkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($resignationIndexRoute); ?>"
                                                                    data-url="<?php echo e($resignationIndexRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($resignationIndexMessage); ?>">
                                                                    <?php echo e(__('Resignation')); ?>

                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_TRV)): ?>
                                                            <?php
                                                            $tripIndexRoute = Route::has(ViewsConstants::TRV . '.index')
                                                                ? route(ViewsConstants::TRV . '.index')
                                                                : '#';
                                                            $tripIndexLinkId = 'trip-index-link';
                                                            $tripIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::TRV,
                                                                'travel_index_route_unavailable'
                                                            ) ?? 'Travel index route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e(request()->is('travel*') ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($tripIndexLinkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($tripIndexRoute); ?>"
                                                                    data-url="<?php echo e($tripIndexRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($tripIndexMessage); ?>">
                                                                    <?php echo e(__('Trip')); ?>

                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_PRM)): ?>
                                                            <?php
                                                            $promotionIndexRoute = Route::has(ViewsConstants::PRM . '.index')
                                                                ? route(ViewsConstants::PRM . '.index')
                                                                : '#';
                                                            $promotionIndexLinkId = 'promotion-index-link';
                                                            $promotionIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::PRM,
                                                                'promotion_index_route_unavailable'
                                                            ) ?? 'Promotion index route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e(request()->is('promotion*') ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($promotionIndexLinkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($promotionIndexRoute); ?>"
                                                                    data-url="<?php echo e($promotionIndexRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($promotionIndexMessage); ?>">
                                                                    <?php echo e(__('Promotion')); ?>

                                                                </a>
                                                            </li>
                                                        <?php endif; ?>

                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_CPT)): ?>
                                                            <?php
                                                            $complaintIndexRoute = Route::has(ViewsConstants::CPL . '.index')
                                                                ? route(ViewsConstants::CPL . '.index')
                                                                : '#';
                                                            $complaintIndexLinkId = 'complaint-index-link';
                                                            $complaintIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::CPL,
                                                                'complaint_index_route_unavailable'
                                                            ) ?? 'Complaints index route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e(request()->is('complaint*') ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($complaintIndexLinkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($complaintIndexRoute); ?>"
                                                                    data-url="<?php echo e($complaintIndexRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($complaintIndexMessage); ?>">
                                                                    <?php echo e(__('Complaints')); ?>

                                                                </a>
                                                            </li>
                                                        <?php endif; ?>

                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_WRN)): ?>
                                                            <?php
                                                            $warningIndexRoute = Route::has(ViewsConstants::WRN . '.index')
                                                                ? route(ViewsConstants::WRN . '.index')
                                                                : '#';
                                                            $warningIndexLinkId = 'warning-index-link';
                                                            $warningIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::WRN,
                                                                'warning_index_route_unavailable'
                                                            ) ?? 'Warning index route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e(request()->is('warning*') ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($warningIndexLinkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($warningIndexRoute); ?>"
                                                                    data-url="<?php echo e($warningIndexRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($warningIndexMessage); ?>">
                                                                    <?php echo e(__('Warning')); ?>

                                                                </a>
                                                            </li>
                                                        <?php endif; ?>

                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_TRM)): ?>
                                                            <?php
                                                            $terminationIndexRoute = Route::has(ViewsConstants::TMN . '.index')
                                                                ? route(ViewsConstants::TMN . '.index')
                                                                : '#';
                                                            $terminationIndexLinkId = 'termination-index-link';
                                                            $terminationIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::TMN,
                                                                'termination_index_route_unavailable'
                                                            ) ?? 'Termination index route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e(request()->is('termination*') ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($terminationIndexLinkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($terminationIndexRoute); ?>"
                                                                    data-url="<?php echo e($terminationIndexRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($terminationIndexMessage); ?>">
                                                                    <?php echo e(__('Termination')); ?>

                                                                </a>
                                                            </li>
                                                        <?php endif; ?>

                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_ANC)): ?>
                                                            <?php
                                                            $announcementIndexRoute = Route::has(ViewsConstants::ANC . '.index')
                                                                ? route(ViewsConstants::ANC . '.index')
                                                                : '#';
                                                            $announcementIndexLinkId = 'announcement-index-link';
                                                            $announcementIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::ANC,
                                                                'announcement_index_route_unavailable'
                                                            ) ?? 'Announcement index route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e(request()->is('announcement*') ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($announcementIndexLinkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($announcementIndexRoute); ?>"
                                                                    data-url="<?php echo e($announcementIndexRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($announcementIndexMessage); ?>">
                                                                    <?php echo e(__('Announcement')); ?>

                                                                </a>
                                                            </li>
                                                        <?php endif; ?>

                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_HLD)): ?>
                                                            <?php
                                                            $holidaysIndexRoute = Route::has(ViewsConstants::HLD . '.index')
                                                                ? route(ViewsConstants::HLD . '.index')
                                                                : '#';
                                                            $holidaysIndexLinkId = 'holidays-index-link';
                                                            $holidaysIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                ViewsConstants::HLD,
                                                                'holidays_index_route_unavailable'
                                                            ) ?? 'Holidays index route is unavailable. Please contact technical support or your domain administrator.';
                                                            ?>
                                                            <li class="dash-item <?php echo e(request()->is('holiday*') || request()->is('holiday-calendar') ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($holidaysIndexLinkId); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($holidaysIndexRoute); ?>"
                                                                    data-url="<?php echo e($holidaysIndexRoute); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($holidaysIndexMessage); ?>">
                                                                    <?php echo e(__('Holidays')); ?>

                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                    </ul>
                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                    <script defer>
                                                        (() => {
                                                            const bindGuard = id => {
                                                                const listenerAttr = `data-${id}-listener-active`;
                                                                const el = document.getElementById(id);
                                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                el.setAttribute(listenerAttr, 'true');
                                                                el.addEventListener('click', event => {
                                                                    try {
                                                                        const url = el.getAttribute('data-url');
                                                                        const href = el.href;
                                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                                            event.preventDefault();
                                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                            const containerId = 'toast-container';
                                                                            let container = document.getElementById(containerId);
                                                                            if (!container) {
                                                                                container = document.createElement('div');
                                                                                container.id = containerId;
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (bootstrapLink && window.bootstrap) {
                                                                                const toastEl = document.createElement('div');
                                                                                toastEl.className = 'toast';
                                                                                toastEl.setAttribute('role', 'alert');
                                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                                const body = document.createElement('div');
                                                                                body.className = 'toast-body';
                                                                                body.textContent = msg;
                                                                                toastEl.appendChild(body);
                                                                                container.appendChild(toastEl);
                                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            el.setAttribute('data-failed-route', 'true');
                                                                        }
                                                                    } catch (error) {}
                                                                });
                                                                const observer = new MutationObserver(() => {
                                                                    if (!document.getElementById(id)) {
                                                                        observer.disconnect();
                                                                    }
                                                                });
                                                                observer.observe(document.body, {
                                                                    childList: true,
                                                                    subtree: true
                                                                });
                                                            };
                                                            [
                                                                'award-index-link',
                                                                'transfer-index-link',
                                                                'resignation-index-link',
                                                                'trip-index-link',
                                                                'promotion-index-link',
                                                                'complaint-index-link',
                                                                'warning-index-link',
                                                                'termination-index-link',
                                                                'announcement-index-link',
                                                                'holidays-index-link'
                                                            ].forEach(bindGuard);
                                                        })();
                                                    </script>
                                                    <?php $__env->stopPush(); ?>
                                                </li>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_EVT)): ?>
                                                <?php
                                                $eventIndexRoute = Route::has(ViewsConstants::EVT . '.index')
                                                    ? route(ViewsConstants::EVT . '.index')
                                                    : '#';
                                                $linkId = 'event-setup-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::EVT,
                                                    'event_index_route_unavailable'
                                                ) ?? 'Event setup route is unavailable. Please contact technical support or your domain administrator.';
                                                ?>
                                                <li class="dash-item <?php echo e(request()->is('event*') ? 'active' : ''); ?>">
                                                    <a
                                                        id="<?php echo e($linkId); ?>"
                                                        class="dash-link"
                                                        href="<?php echo e($eventIndexRoute); ?>"
                                                        data-url="<?php echo e($eventIndexRoute); ?>"
                                                        data-sv-localized="true"
                                                        data-guard-msg="<?php echo e($message); ?>">
                                                        <?php echo e(__('Event Setup')); ?>

                                                    </a>
                                                </li>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                <script defer>
                                                    (() => {
                                                        const listenerAttr = 'data-event-setup-listener-active';
                                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                        el.setAttribute(listenerAttr, 'true');
                                                        el.addEventListener('click', event => {
                                                            try {
                                                                const url = el.getAttribute('data-url');
                                                                const href = el.href;
                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                    event.preventDefault();
                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                    const containerId = 'toast-container';
                                                                    let container = document.getElementById(containerId);
                                                                    if (!container) {
                                                                        container = document.createElement('div');
                                                                        container.id = containerId;
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl = document.createElement('div');
                                                                        toastEl.className = 'toast';
                                                                        toastEl.setAttribute('role', 'alert');
                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    el.setAttribute('data-failed-route', 'true');
                                                                }
                                                            } catch (error) {}
                                                        });
                                                        const observer = new MutationObserver(() => {
                                                            if (!document.body.contains(el)) {
                                                                observer.disconnect();
                                                                el.removeEventListener('click', () => {});
                                                            }
                                                        });
                                                        observer.observe(document.body, {
                                                            childList: true,
                                                            subtree: true
                                                        });
                                                    })();
                                                </script>
                                                <?php $__env->stopPush(); ?>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_MT)): ?>
                                                <?php
                                                $meetingIndexRoute = Route::has(ViewsConstants::MT . '.index')
                                                    ? route(ViewsConstants::MT . '.index')
                                                    : '#';
                                                $linkId = 'meeting-index-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::MEETING,
                                                    'meeting_index_route_unavailable'
                                                ) ?? 'Meeting index route is unavailable. Please contact technical support or your domain administrator.';
                                                ?>
                                                <li class="dash-item <?php echo e(request()->is('meeting*') ? 'active' : ''); ?>">
                                                    <a
                                                        id="<?php echo e($linkId); ?>"
                                                        class="dash-link"
                                                        href="<?php echo e($meetingIndexRoute); ?>"
                                                        data-url="<?php echo e($meetingIndexRoute); ?>"
                                                        data-sv-localized="true"
                                                        data-guard-msg="<?php echo e($message); ?>">
                                                        <?php echo e(__('Meeting')); ?>

                                                    </a>
                                                </li>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                <script defer>
                                                    (() => {
                                                        const listenerAttr = 'data-meeting-index-listener-active';
                                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                        el.setAttribute(listenerAttr, 'true');
                                                        el.addEventListener('click', event => {
                                                            try {
                                                                const url = el.getAttribute('data-url');
                                                                const href = el.href;
                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                    event.preventDefault();
                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                    const containerId = 'toast-container';
                                                                    let container = document.getElementById(containerId);
                                                                    if (!container) {
                                                                        container = document.createElement('div');
                                                                        container.id = containerId;
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl = document.createElement('div');
                                                                        toastEl.className = 'toast';
                                                                        toastEl.setAttribute('role', 'alert');
                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    el.setAttribute('data-failed-route', 'true');
                                                                }
                                                            } catch (error) {}
                                                        });
                                                        const observer = new MutationObserver(() => {
                                                            if (!document.body.contains(el)) {
                                                                observer.disconnect();
                                                                el.removeEventListener('click', () => {});
                                                            }
                                                        });
                                                        observer.observe(document.body, {
                                                            childList: true,
                                                            subtree: true
                                                        });
                                                    })();
                                                </script>
                                                <?php $__env->stopPush(); ?>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_AST)): ?>
                                                <?php
                                                $assetSetupRoute = Route::has(ViewsConstants::ACT_AST . '.index')
                                                    ? route(ViewsConstants::ACT_AST . '.index')
                                                    : (Route::has(Str::kebab(ViewsConstants::ACT_AST . '.index'))
                                                        ? route(Str::kebab(ViewsConstants::ACT_AST . '.index'))
                                                        : '#');
                                                $linkId = 'employees-asset-setup-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::ACT_AST,
                                                    'account_asset_setup_unavailable'
                                                ) ?? 'Account Assets Setup route is unavailable. Please contact technical support or your domain administrator.';
                                                ?>
                                                <li class="dash-item <?php echo e((request()->is('account_assets*') || request()->is('account_assets*')) ? 'active' : ''); ?>">
                                                    <a
                                                        id="<?php echo e($linkId); ?>"
                                                        class="dash-link"
                                                        href="<?php echo e($assetSetupRoute); ?>"
                                                        data-url="<?php echo e($assetSetupRoute); ?>"
                                                        data-sv-localized="true"
                                                        data-guard-msg="<?php echo e($message); ?>">
                                                        <?php echo e(__('Employees Asset Setup')); ?>

                                                    </a>
                                                </li>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                <script defer>
                                                    (() => {
                                                        const listenerAttr = 'data-employees-asset-setup-listener-active';
                                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                        el.setAttribute(listenerAttr, 'true');
                                                        el.addEventListener('click', event => {
                                                            try {
                                                                const url = el.getAttribute('data-url');
                                                                const href = el.href;
                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                    event.preventDefault();
                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                    const containerId = 'toast-container';
                                                                    let container = document.getElementById(containerId);
                                                                    if (!container) {
                                                                        container = document.createElement('div');
                                                                        container.id = containerId;
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl = document.createElement('div');
                                                                        toastEl.className = 'toast';
                                                                        toastEl.setAttribute('role', 'alert');
                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    el.setAttribute('data-failed-route', 'true');
                                                                }
                                                            } catch (error) {}
                                                        });
                                                        const observer = new MutationObserver(() => {
                                                            if (!document.body.contains(el)) {
                                                                observer.disconnect();
                                                                el.removeEventListener('click', () => {});
                                                            }
                                                        });
                                                        observer.observe(document.body, {
                                                            childList: true,
                                                            subtree: true
                                                        });
                                                    })();
                                                </script>
                                                <?php $__env->stopPush(); ?>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_DOC)): ?>
                                                <?php
                                                $docSetupRoute = Route::has(ViewsConstants::DOC_UP . '.index')
                                                    ? route(ViewsConstants::DOC_UP . '.index')
                                                    : (Route::has(Str::kebab(ViewsConstants::DOC_UP . '.index'))
                                                        ? route(Str::kebab(ViewsConstants::DOC_UP . '.index'))
                                                        : '#');
                                                $linkId = 'document-setup-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::DOC,
                                                    'document_index_route_unavailable'
                                                ) ?? 'Document setup route is unavailable. Please contact technical support or your domain administrator.';
                                                ?>
                                                <li class="dash-item <?php echo e((request()->is('document-upload*') || request()->is('document_upload*')) ? 'active' : ''); ?>">
                                                    <a
                                                        id="<?php echo e($linkId); ?>"
                                                        class="dash-link"
                                                        href="<?php echo e($docSetupRoute); ?>"
                                                        data-url="<?php echo e($docSetupRoute); ?>"
                                                        data-sv-localized="true"
                                                        data-guard-msg="<?php echo e($message); ?>">
                                                        <?php echo e(__('Document Setup')); ?>

                                                    </a>
                                                </li>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                <script defer>
                                                    (() => {
                                                        const listenerAttr = 'data-document-setup-listener-active';
                                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                        el.setAttribute(listenerAttr, 'true');
                                                        el.addEventListener('click', event => {
                                                            try {
                                                                const url = el.getAttribute('data-url');
                                                                const href = el.href;
                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                    event.preventDefault();
                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                    const containerId = 'toast-container';
                                                                    let container = document.getElementById(containerId);
                                                                    if (!container) {
                                                                        container = document.createElement('div');
                                                                        container.id = containerId;
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl = document.createElement('div');
                                                                        toastEl.className = 'toast';
                                                                        toastEl.setAttribute('role', 'alert');
                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    el.setAttribute('data-failed-route', 'true');
                                                                }
                                                            } catch (error) {}
                                                        });
                                                        const observer = new MutationObserver(() => {
                                                            if (!document.body.contains(el)) {
                                                                observer.disconnect();
                                                                el.removeEventListener('click', () => {});
                                                            }
                                                        });
                                                        observer.observe(document.body, {
                                                            childList: true,
                                                            subtree: true
                                                        });
                                                    })();
                                                </script>
                                                <?php $__env->stopPush(); ?>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_CPN_PL)): ?>
                                                <?php
                                                $companyPolicyRoute = Route::has(ViewsConstants::CPN_PL . '.index')
                                                    ? route(ViewsConstants::CPN_PL . '.index')
                                                    : (Route::has(Str::kebab(ViewsConstants::CPN_PL . '.index'))
                                                        ? route(Str::kebab(ViewsConstants::CPN_PL . '.index'))
                                                        : '#');
                                                $linkId = 'company-policy-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::CPN_PL,
                                                    'company_policy_index_unavailable'
                                                ) ?? 'Company policy route is unavailable. Please contact technical support or your domain administrator.';
                                                ?>
                                                <li class="dash-item <?php echo e((request()->is('company-policies*') || request()->is('company_policies*')) ? 'active' : ''); ?>">
                                                    <a
                                                        id="<?php echo e($linkId); ?>"
                                                        class="dash-link"
                                                        href="<?php echo e($companyPolicyRoute); ?>"
                                                        data-url="<?php echo e($companyPolicyRoute); ?>"
                                                        data-sv-localized="true"
                                                        data-guard-msg="<?php echo e($message); ?>">
                                                        <?php echo e(__('Company policy')); ?>

                                                    </a>
                                                </li>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                <script defer>
                                                    (() => {
                                                        const listenerAttr = 'data-company-policy-listener-active';
                                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                        el.setAttribute(listenerAttr, 'true');
                                                        el.addEventListener('click', event => {
                                                            try {
                                                                const url = el.getAttribute('data-url');
                                                                const href = el.href;
                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                    event.preventDefault();
                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                    const containerId = 'toast-container';
                                                                    let container = document.getElementById(containerId);
                                                                    if (!container) {
                                                                        container = document.createElement('div');
                                                                        container.id = containerId;
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl = document.createElement('div');
                                                                        toastEl.className = 'toast';
                                                                        toastEl.setAttribute('role', 'alert');
                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    el.setAttribute('data-failed-route', 'true');
                                                                }
                                                            } catch (error) {}
                                                        });
                                                        const observer = new MutationObserver(() => {
                                                            if (!document.body.contains(el)) {
                                                                observer.disconnect();
                                                                el.removeEventListener('click', () => {});
                                                            }
                                                        });
                                                        observer.observe(document.body, {
                                                            childList: true,
                                                            subtree: true
                                                        });
                                                    })();
                                                </script>
                                                <?php $__env->stopPush(); ?>
                                            <?php endif; ?>
                                            <?php if (
                                                $user[UsersConstants::COL_TP] === PermissionsConstants::CPN ||
                                                strtolower($user[UsersConstants::COL_TP]) === 'hr' ||
                                                $user[UsersConstants::COL_TP] === PermissionsConstants::SA
                                            ): ?>
                                                <?php
                                                $segments = [
                                                    ViewsConstants::ALW_OPT,
                                                    ViewsConstants::AWD_TP,
                                                    ViewsConstants::BRC,
                                                    ViewsConstants::DDT_OPT,
                                                    ViewsConstants::DOC,
                                                    ViewsConstants::DPT,
                                                    ViewsConstants::DSG,
                                                    ViewsConstants::GL_TP,
                                                    ViewsConstants::JB_CAT,
                                                    ViewsConstants::JB_STG,
                                                    ViewsConstants::LN_OPT,
                                                    ViewsConstants::LV_TP,
                                                    ViewsConstants::PFM_TP,
                                                    ViewsConstants::PY_SLP_TP,
                                                    ViewsConstants::TMN_TP
                                                ];

                                                $kebabSegments = array_map(function ($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);

                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isHrmSetup = in_array(RF::segment(1), $allSegments);
                                                $hrmSetupRoute = Route::has(ViewsConstants::BRC . '.index')
                                                    ? route(ViewsConstants::BRC . '.index')
                                                    : '#';
                                                $linkId = 'hrm-system-setup-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::BRC,
                                                    'hrm_system_setup_route_unavailable'
                                                ) ?? 'Human Resources Management System Setup route is unavailable. Please contact technical support or your domain administrator.';
                                                ?>
                                                <li class="dash-item <?php echo e($isHrmSetup ? 'active' : ''); ?>">
                                                    <a
                                                        id="<?php echo e($linkId); ?>"
                                                        class="dash-link"
                                                        href="<?php echo e($hrmSetupRoute); ?>"
                                                        data-url="<?php echo e($hrmSetupRoute); ?>"
                                                        data-sv-localized="true"
                                                        data-guard-msg="<?php echo e($message); ?>">
                                                        <?php echo e(__('HRM System Setup')); ?>

                                                    </a>
                                                </li>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                <script defer>
                                                    (() => {
                                                        const listenerAttr = 'data-hrm-system-setup-listener-active';
                                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                        el.setAttribute(listenerAttr, 'true');
                                                        el.addEventListener('click', event => {
                                                            try {
                                                                const url = el.getAttribute('data-url');
                                                                const href = el.href;
                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                    event.preventDefault();
                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                    const containerId = 'toast-container';
                                                                    let container = document.getElementById(containerId);
                                                                    if (!container) {
                                                                        container = document.createElement('div');
                                                                        container.id = containerId;
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl = document.createElement('div');
                                                                        toastEl.className = 'toast';
                                                                        toastEl.setAttribute('role', 'alert');
                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    el.setAttribute('data-failed-route', 'true');
                                                                }
                                                            } catch (error) {}
                                                        });
                                                        const observer = new MutationObserver(() => {
                                                            if (!document.body.contains(el)) {
                                                                observer.disconnect();
                                                                el.removeEventListener('click', () => {});
                                                            }
                                                        });
                                                        observer.observe(document.body, {
                                                            childList: true,
                                                            subtree: true
                                                        });
                                                    })();
                                                </script>
                                                <?php $__env->stopPush(); ?>
                                            <?php endif; ?>
                                        </ul>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (!empty($userPlan) &&  $userPlan?->{PlansConstants::COL_ACC} == 1): ?>
                                <?php
                                $permissions = [
                                    PermissionsConstants::MNG_CST,
                                    PermissionsConstants::MNG_VD,
                                    PermissionsConstants::MNG_PPS,
                                    PermissionsConstants::MNG_BACC,
                                    PermissionsConstants::MNG_BTF,
                                    PermissionsConstants::MNG_INV,
                                    PermissionsConstants::MNG_RVN,
                                    PermissionsConstants::MNG_CRD,
                                    PermissionsConstants::MNG_BIL,
                                    PermissionsConstants::MNG_PMT,
                                    PermissionsConstants::MNG_DBT,
                                    PermissionsConstants::MNG_COA,
                                    PermissionsConstants::MNG_JNL,
                                    PermissionsConstants::BLC_RPT,
                                    PermissionsConstants::LDG_RPT,
                                    PermissionsConstants::TRL_RPT
                                ];
                                $hasFinancialPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                                ?>
                                <?php if ($hasFinancialPermission): ?>
                                    <?php
                                    $routeNames = ['print_setting'];
                                    $segments = [
                                        ViewsConstants::BDG,
                                        ViewsConstants::BIL,
                                        ViewsConstants::BNK_ACC,
                                        ViewsConstants::BNK_TRF,
                                        ViewsConstants::COA,
                                        ViewsConstants::COA_TP,
                                        ViewsConstants::CRD_NT,
                                        ViewsConstants::CST,
                                        ViewsConstants::CST_FD,
                                        ViewsConstants::DBT_NT,
                                        ViewsConstants::EXP,
                                        ViewsConstants::GL,
                                        ViewsConstants::INV,
                                        ViewsConstants::JRN_ET,
                                        ViewsConstants::PAY,
                                        'payment_methods',
                                        ViewsConstants::PPS,
                                        ViewsConstants::PRD_SV_CAT,
                                        ViewsConstants::PRD_SV_UNT,
                                        ViewsConstants::RVN,
                                        ViewsConstants::TX,
                                        ViewsConstants::VND
                                    ];
                                    $segment2Values = ['ledger', 'balance_sheet', 'trial_balance', 'profit_loss'];
                                    $kebabSegments = array_map(function ($segment) {
                                        if ($segment === null) return null;
                                        return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                    }, $segments);
                                    $allSegments = array_merge($segments, $kebabSegments);
                                    $isAccountingModule = in_array(RF::route()->getName(), $routeNames) ||
                                        in_array(RF::segment(1), $allSegments) ||
                                        in_array(RF::segment(2), $segment2Values) ||
                                        (RF::segment(1) == ViewsConstants::TST &&
                                            !in_array(RF::segment(2), ['ledger', 'balance_sheet', 'trial_balance']));
                                    ?>
                                    <li
                                        class="<?php echo e(VC::DSH_IT_MN); ?>

                                    <?php echo e($isAccountingModule ? ' active dash-trigger' : ''); ?>">
                                        <a href="#!" class="dash-link">
                                            <span class="dash-micon">
                                                <i class="ti ti-box"></i>
                                            </span>
                                            <span class="dash-mtext">
                                                <?php echo e(__('Accounting System')); ?>

                                            </span>
                                            <span class="dash-arrow">
                                                <i data-feather="chevron-right"></i>
                                            </span>
                                        </a>
                                        <ul class="dash-submenu">
                                            <?php if (Gate::check(PermissionsConstants::MNG_BACC) || Gate::check(PermissionsConstants::MNG_BTF)): ?>
                                                <?php
                                                $segments = [
                                                    ViewsConstants::BNK_ACC,
                                                    ViewsConstants::BNK_TRF
                                                ];
                                                $kebabSegments = array_map(function ($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);
                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isBankingModule = in_array(RF::segment(1), $allSegments);
                                                ?>
                                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isBankingModule ? 'active dash-trigger' : ''); ?>">
                                                    <a class="dash-link" href="#"><?php echo e(__('Banking')); ?>

                                                        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                    </a>
                                                    <?php
                                                    $bankAccountRoute = Route::has(ViewsConstants::BNK_ACC . '.index')
                                                        ? route(ViewsConstants::BNK_ACC . '.index')
                                                        : (Route::has(Str::kebab(ViewsConstants::BNK_ACC . '.index'))
                                                            ? route(Str::kebab(ViewsConstants::BNK_ACC . '.index'))
                                                            : '#');
                                                    $bankAccountLinkId = 'bank-account-index-link';
                                                    $bankAccountMessage = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::BNK_ACC,
                                                        'bank_account_index_route_unavailable'
                                                    ) ?? 'Bank Account route is unavailable. Please contact technical support or your domain administrator.';

                                                    $bankTransferRoute = Route::has(ViewsConstants::BNK_TRF . '.index')
                                                        ? route(ViewsConstants::BNK_TRF . '.index')
                                                        : (Route::has(Str::kebab(ViewsConstants::BNK_TRF . '.index'))
                                                            ? route(Str::kebab(ViewsConstants::BNK_TRF . '.index'))
                                                            : '#');
                                                    $bankTransferLinkId = 'bank-transfer-index-link';
                                                    $bankTransferMessage = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::BNK_ACC,
                                                        'bank_transfer_index_route_unavailable'
                                                    ) ?? 'Transfer route is unavailable. Please contact technical support or your domain administrator.';
                                                    ?>
                                                    <ul class="dash-submenu">
                                                        <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::BNK_ACC . '.index' || RF::route()->getName() == ViewsConstants::BNK_ACC . '.create' || RF::route()->getName() == ViewsConstants::BNK_ACC . '.edit' ? 'active' : ''); ?>">
                                                            <a
                                                                id="<?php echo e($bankAccountLinkId); ?>"
                                                                class="dash-link"
                                                                href="<?php echo e($bankAccountRoute); ?>"
                                                                data-url="<?php echo e($bankAccountRoute); ?>"
                                                                data-sv-localized="true"
                                                                data-guard-msg="<?php echo e($bankAccountMessage); ?>">
                                                                <?php echo e(__('Account')); ?>

                                                            </a>
                                                        </li>
                                                        <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::BNK_TRF . '.index' || RF::route()->getName() == ViewsConstants::BNK_TRF . '.create' || RF::route()->getName() == ViewsConstants::BNK_TRF . '.edit' ? 'active' : ''); ?>">
                                                            <a
                                                                id="<?php echo e($bankTransferLinkId); ?>"
                                                                class="dash-link"
                                                                href="<?php echo e($bankTransferRoute); ?>"
                                                                data-url="<?php echo e($bankTransferRoute); ?>"
                                                                data-sv-localized="true"
                                                                data-guard-msg="<?php echo e($bankTransferMessage); ?>">
                                                                <?php echo e(__('Transfer')); ?>

                                                            </a>
                                                        </li>
                                                    </ul>
                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                    <script defer>
                                                        (() => {
                                                            const bindGuard = id => {
                                                                const listenerAttr = `data-${id}-listener-active`;
                                                                const el = document.getElementById(id);
                                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                el.setAttribute(listenerAttr, 'true');
                                                                el.addEventListener('click', event => {
                                                                    try {
                                                                        const url = el.getAttribute('data-url');
                                                                        const href = el.href;
                                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                                            event.preventDefault();
                                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                            const containerId = 'toast-container';
                                                                            let container = document.getElementById(containerId);
                                                                            if (!container) {
                                                                                container = document.createElement('div');
                                                                                container.id = containerId;
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (bootstrapLink && window.bootstrap) {
                                                                                const toastEl = document.createElement('div');
                                                                                toastEl.className = 'toast';
                                                                                toastEl.setAttribute('role', 'alert');
                                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                                const body = document.createElement('div');
                                                                                body.className = 'toast-body';
                                                                                body.textContent = msg;
                                                                                toastEl.appendChild(body);
                                                                                container.appendChild(toastEl);
                                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            el.setAttribute('data-failed-route', 'true');
                                                                        }
                                                                    } catch (error) {}
                                                                });
                                                                const observer = new MutationObserver(() => {
                                                                    if (!document.getElementById(id)) observer.disconnect();
                                                                });
                                                                observer.observe(document.body, {
                                                                    childList: true,
                                                                    subtree: true
                                                                });
                                                            };

                                                            [
                                                                '<?php echo e($bankAccountLinkId); ?>',
                                                                '<?php echo e($bankTransferLinkId); ?>'
                                                            ].forEach(bindGuard);
                                                        })();
                                                    </script>
                                                    <?php $__env->stopPush(); ?>
                                                </li>
                                            <?php endif; ?>
                                            <?php
                                            $permissions = [
                                                PermissionsConstants::MNG_CST,
                                                PermissionsConstants::MNG_PPS,
                                                PermissionsConstants::MNG_INV,
                                                PermissionsConstants::MNG_RVN,
                                                PermissionsConstants::MNG_CRD
                                            ];
                                            $hasTransactionsPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                                            ?>
                                            <?php if ($hasTransactionsPermission): ?>
                                                <?php
                                                $segments = [
                                                    ViewsConstants::CRD_NT,
                                                    ViewsConstants::CST,
                                                    ViewsConstants::INV,
                                                    ViewsConstants::PPS,
                                                    ViewsConstants::RVN
                                                ];
                                                $kebabSegments = array_map(function ($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);
                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isCustomerSales = in_array(RF::segment(1), $allSegments);
                                                ?>
                                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isCustomerSales ? 'active dash-trigger' : ''); ?>">
                                                    <a class="dash-link" href="#"><?php echo e(__('Sales')); ?>

                                                        <span class="dash-arrow">
                                                            <i data-feather="chevron-right"></i>
                                                        </span>
                                                    </a>
                                                    <?php
                                                    $submenu = [
                                                        [
                                                            'route'   => 'customer.index',
                                                            'label'   => __('Customer'),
                                                            'can'     => PermissionsConstants::MNG_CST,
                                                            'pattern' => 'customer*',
                                                            'key'     => ViewsConstants::CST
                                                        ],
                                                        [
                                                            'route'   => ViewsConstants::PPS . '.index',
                                                            'label'   => __('Estimate'),
                                                            'can'     => PermissionsConstants::MNG_PPS,
                                                            'pattern' => ViewsConstants::PPS . '*',
                                                            'key'     => ViewsConstants::PPS
                                                        ],
                                                        [
                                                            'route'   => ViewsConstants::INV . '.index',
                                                            'label'   => __('Invoice'),
                                                            'pattern' => ViewsConstants::INV . '*',
                                                            'key'     => ViewsConstants::INV
                                                        ],
                                                        [
                                                            'route'   => ViewsConstants::RVN . '.index',
                                                            'label'   => __('Revenue'),
                                                            'pattern' => ViewsConstants::RVN . '*',
                                                            'key'     => ViewsConstants::RVN
                                                        ],
                                                        [
                                                            'route'   => 'credit.note',
                                                            'label'   => __('Credit Note'),
                                                            'pattern' => 'credit.note',
                                                            'key'     => ViewsConstants::CRD_NT
                                                        ],
                                                    ];
                                                    $guardIds = [];
                                                    ?>
                                                    <ul class="dash-submenu">
                                                        <?php $__currentLoopData = $submenu;
                                                        $__env->addLoop($__currentLoopData);
                                                        foreach ($__currentLoopData as $item): $__env->incrementLoopIndices();
                                                            $loop = $__env->getLastLoop(); ?>
                                                            <?php if (!isset($item['can']) || Gate::check($item['can'])): ?>
                                                                <?php
                                                                $routeName = $item['route'];
                                                                $url       = Route::has($routeName) ? route($routeName) : '#';
                                                                $id        = Str::slug($routeName . '-link', '-');
                                                                $guardIds[] = $id;
                                                                $entity    = Str::before($routeName, '.');
                                                                $msgKey    = Str::snake(str_replace('.', '_', $routeName)) . '_route_unavailable';
                                                                $message   = Utility::fetchLinkMessage($lang, $entity, $msgKey)
                                                                    ?? __(':key route is unavailable. Please contact technical support or your domain administrator.', ['key' => $item['key']]);
                                                                ?>
                                                                <li class="dash-item <?php echo e(request()->routeIs($item['pattern']) ? 'active' : ''); ?>">
                                                                    <a
                                                                        id="<?php echo e($id); ?>"
                                                                        class="dash-link"
                                                                        href="<?php echo e($url); ?>"
                                                                        data-url="<?php echo e($url); ?>"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="<?php echo e($message); ?>">
                                                                        <?php echo e($item['label']); ?>

                                                                    </a>
                                                                </li>
                                                            <?php endif; ?>
                                                        <?php endforeach;
                                                        $__env->popLoop();
                                                        $loop = $__env->getLastLoop(); ?>
                                                    </ul>
                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                    <script defer>
                                                        (() => {
                                                            const bindGuard = id => {
                                                                const listenerAttr = `data-${id}-listener-active`;
                                                                const el = document.getElementById(id);
                                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                el.setAttribute(listenerAttr, 'true');
                                                                el.addEventListener('click', event => {
                                                                    try {
                                                                        const url = el.getAttribute('data-url');
                                                                        const href = el.href;
                                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                                            event.preventDefault();
                                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                            const containerId = 'toast-container';
                                                                            let container = document.getElementById(containerId);
                                                                            if (!container) {
                                                                                container = document.createElement('div');
                                                                                container.id = containerId;
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (bootstrapLink && window.bootstrap) {
                                                                                const toastEl = document.createElement('div');
                                                                                toastEl.className = 'toast';
                                                                                toastEl.setAttribute('role', 'alert');
                                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                                const body = document.createElement('div');
                                                                                body.className = 'toast-body';
                                                                                body.textContent = msg;
                                                                                toastEl.appendChild(body);
                                                                                container.appendChild(toastEl);
                                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            el.setAttribute('data-failed-route', 'true');
                                                                        }
                                                                    } catch (error) {}
                                                                });
                                                                const observer = new MutationObserver(() => {
                                                                    if (!document.getElementById(id)) observer.disconnect();
                                                                });
                                                                observer.observe(document.body, {
                                                                    childList: true,
                                                                    subtree: true
                                                                });
                                                            };
                                                            <?php echo json_encode($guardIds, 15, 512) ?>.forEach(bindGuard);
                                                        })();
                                                    </script>
                                                    <?php $__env->stopPush(); ?>
                                                </li>
                                            <?php endif; ?>
                                            <?php
                                            $permissions = [
                                                PermissionsConstants::MNG_VD,
                                                PermissionsConstants::MNG_BIL,
                                                PermissionsConstants::MNG_PMT,
                                                PermissionsConstants::MNG_DBT
                                            ];
                                            $hasVendorPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                                            ?>
                                            <?php if ($hasVendorPermission): ?>
                                                <?php
                                                $segments = [
                                                    ViewsConstants::BIL,
                                                    ViewsConstants::DBT_NT,
                                                    ViewsConstants::EXP,
                                                    ViewsConstants::PAY,
                                                    ViewsConstants::VND
                                                ];
                                                $kebabSegments = array_map(function ($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);
                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isVendorPurchasing = in_array(RF::segment(1), $allSegments);
                                                ?>
                                                <li
                                                    class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isVendorPurchasing ? 'active dash-trigger' : ''); ?>">
                                                    <a class="dash-link" href="#"><?php echo e(__('Purchases')); ?>

                                                        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                    </a>
                                                    <?php
                                                    $items = [];
                                                    if (Gate::check(PermissionsConstants::MNG_VD)) {
                                                        $route    = ViewsConstants::VND . '.index';
                                                        $url      = Route::has($route) ? route($route) : '#';
                                                        $id       = 'vendor-index-link';
                                                        $key      = 'vendor_index_route_unavailable';
                                                        $message  = Utility::fetchLinkMessage($lang, ViewsConstants::VND, $key)
                                                            ?? __('Suppiler route is unavailable. Please contact technical support or your domain administrator.');
                                                        $items[]  = $id;
                                                    }
                                                    $route    = ViewsConstants::BIL . '.index';
                                                    $urlBil   = Route::has($route) ? route($route) : '#';
                                                    $billId   = 'bill-index-link';
                                                    $billKey  = 'bill_index_route_unavailable';
                                                    $billMsg  = Utility::fetchLinkMessage($lang, ViewsConstants::BIL, $billKey)
                                                        ?? __('Bill route is unavailable. Please contact technical support or your domain administrator.');
                                                    $items[]  = $billId;
                                                    $route    = ViewsConstants::EXP . '.index';
                                                    $urlExp   = Route::has($route) ? route($route) : '#';
                                                    $expId    = 'exp-index-link';
                                                    $expKey   = 'expense_index_route_unavailable';
                                                    $expMsg   = Utility::fetchLinkMessage($lang, ViewsConstants::EXP, $expKey)
                                                        ?? __('Expense route is unavailable. Please contact technical support or your domain administrator.');
                                                    $items[]  = $expId;
                                                    $route    = ViewsConstants::PAY . '.index';
                                                    $urlPay   = Route::has($route) ? route($route) : '#';
                                                    $payId    = 'pay-index-link';
                                                    $payKey   = 'payment_index_route_unavailable';
                                                    $payMsg   = Utility::fetchLinkMessage($lang, ViewsConstants::PAY, $payKey)
                                                        ?? __('Payment route is unavailable. Please contact technical support or your domain administrator.');
                                                    $items[]  = $payId;
                                                    $route    = 'debit.note';
                                                    $urlDN    = Route::has($route) ? route($route) : '#';
                                                    $dnId     = 'debit-note-link';
                                                    $dnKey    = 'debit_note_route_unavailable';
                                                    $dnMsg    = Utility::fetchLinkMessage($lang, null, $dnKey)
                                                        ?? __('Debit Note route is unavailable. Please contact technical support or your domain administrator.');
                                                    $items[]  = $dnId;
                                                    ?>
                                                    <ul class="dash-submenu">
                                                        <?php if (Gate::check(PermissionsConstants::MNG_VD)): ?>
                                                            <li class="dash-item <?php echo e(RF::segment(1) == 'vendor' ? 'active' : ''); ?>">
                                                                <a
                                                                    id="<?php echo e($id); ?>"
                                                                    class="dash-link"
                                                                    href="<?php echo e($url); ?>"
                                                                    data-url="<?php echo e($url); ?>"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="<?php echo e($message); ?>">
                                                                    <?php echo e(__('Suppiler')); ?>

                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <li class="dash-item <?php echo e(RF::route()->getName() === ViewsConstants::BIL . '.index' ? 'active' : ''); ?>">
                                                            <a
                                                                id="<?php echo e($billId); ?>"
                                                                class="dash-link"
                                                                href="<?php echo e($urlBil); ?>"
                                                                data-url="<?php echo e($urlBil); ?>"
                                                                data-sv-localized="true"
                                                                data-guard-msg="<?php echo e($billMsg); ?>">
                                                                <?php echo e(__('Bill')); ?>

                                                            </a>
                                                        </li>
                                                        <li class="dash-item <?php echo e(RF::route()->getName() === ViewsConstants::EXP . '.index' ? 'active' : ''); ?>">
                                                            <a
                                                                id="<?php echo e($expId); ?>"
                                                                class="dash-link"
                                                                href="<?php echo e($urlExp); ?>"
                                                                data-url="<?php echo e($urlExp); ?>"
                                                                data-sv-localized="true"
                                                                data-guard-msg="<?php echo e($expMsg); ?>">
                                                                <?php echo e(__(ViewsConstants::EXP)); ?>

                                                            </a>
                                                        </li>
                                                        <li class="dash-item <?php echo e(RF::route()->getName() === ViewsConstants::PAY . '.index' ? 'active' : ''); ?>">
                                                            <a
                                                                id="<?php echo e($payId); ?>"
                                                                class="dash-link"
                                                                href="<?php echo e($urlPay); ?>"
                                                                data-url="<?php echo e($urlPay); ?>"
                                                                data-sv-localized="true"
                                                                data-guard-msg="<?php echo e($payMsg); ?>">
                                                                <?php echo e(__('Payment')); ?>

                                                            </a>
                                                        </li>
                                                        <li class="dash-item <?php echo e(RF::route()->getName() === 'debit.note' ? 'active' : ''); ?>">
                                                            <a
                                                                id="<?php echo e($dnId); ?>"
                                                                class="dash-link"
                                                                href="<?php echo e($urlDN); ?>"
                                                                data-url="<?php echo e($urlDN); ?>"
                                                                data-sv-localized="true"
                                                                data-guard-msg="<?php echo e($dnMsg); ?>">
                                                                <?php echo e(__('Debit Note')); ?>

                                                            </a>
                                                        </li>
                                                    </ul>
                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                    <script defer>
                                                        (() => {
                                                            const bindGuard = id => {
                                                                const listener = `data-${id}-listener-active`;
                                                                const el = document.getElementById(id);
                                                                if (!el || el.getAttribute(listener) === 'true') return;
                                                                el.setAttribute(listener, 'true');
                                                                el.addEventListener('click', event => {
                                                                    try {
                                                                        const url = el.getAttribute('data-url');
                                                                        const href = el.href;
                                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                                            event.preventDefault();
                                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                            const containerId = 'toast-container';
                                                                            let container = document.getElementById(containerId);
                                                                            if (!container) {
                                                                                container = document.createElement('div');
                                                                                container.id = containerId;
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (bootstrapLink && window.bootstrap) {
                                                                                const toastEl = document.createElement('div');
                                                                                toastEl.className = 'toast';
                                                                                toastEl.setAttribute('role', 'alert');
                                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                                const body = document.createElement('div');
                                                                                body.className = 'toast-body';
                                                                                body.textContent = msg;
                                                                                toastEl.appendChild(body);
                                                                                container.appendChild(toastEl);
                                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            el.setAttribute('data-failed-route', 'true');
                                                                        }
                                                                    } catch {}
                                                                });
                                                                const obs = new MutationObserver(() => {
                                                                    if (!document.getElementById(id)) obs.disconnect();
                                                                });
                                                                obs.observe(document.body, {
                                                                    childList: true,
                                                                    subtree: true
                                                                });
                                                            };
                                                            <?php echo json_encode($items, 15, 512) ?>.forEach(bindGuard);
                                                        })();
                                                    </script>
                                                    <?php $__env->stopPush(); ?>
                                                </li>
                                            <?php endif; ?>
                                            <?php
                                            $permissions = [
                                                PermissionsConstants::MNG_COA,
                                                PermissionsConstants::MNG_JNL,
                                                PermissionsConstants::BLC_RPT,
                                                PermissionsConstants::LDG_RPT,
                                                PermissionsConstants::TRL_RPT
                                            ];
                                            $hasChartsPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                                            ?>
                                            <?php if ($hasChartsPermission): ?>
                                                <?php
                                                $segments = [
                                                    ViewsConstants::COA,
                                                    ViewsConstants::JRN_ET
                                                ];
                                                $segment2Values = [
                                                    'balance_sheet',
                                                    'ledger',
                                                    'profit_loss',
                                                    'trial_balance'
                                                ];
                                                $kebabSegments = array_map(function ($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);
                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isAccountingReports = in_array(RF::segment(1), $allSegments) ||
                                                    in_array(RF::segment(2), $segment2Values);
                                                ?>
                                                <li
                                                    class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isAccountingReports
                                                                                                ? 'active dash-trigger'
                                                                                                : ''); ?>">
                                                    <a class="dash-link" href="#">
                                                        <?php echo e(__('Double Entry')); ?>

                                                        <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                    </a>
                                                    <?php
                                                    $routeName = RF::route()->getName();
                                                    $isCoaRoute = in_array(
                                                        $routeName,
                                                        array_merge(
                                                            [
                                                                ViewsConstants::COA . '.index',
                                                                ViewsConstants::COA . '.show',
                                                            ],
                                                            array_map(
                                                                fn($r) => Str::kebab(ViewsConstants::COA . '.' . $r),
                                                                ['index', 'show']
                                                            )
                                                        )
                                                    );
                                                    $isJrnRoute = in_array(
                                                        $routeName,
                                                        array_merge(
                                                            [
                                                                ViewsConstants::JRN_ET . '.index',
                                                                ViewsConstants::JRN_ET . '.show',
                                                                ViewsConstants::JRN_ET . '.edit',
                                                                ViewsConstants::JRN_ET . '.create',
                                                            ],
                                                            array_map(
                                                                fn($r) => Str::kebab(ViewsConstants::JRN_ET . '.' . $r),
                                                                ['index', 'show', 'edit', 'create']
                                                            )
                                                        )
                                                    );
                                                    $coaRoute = Route::has(ViewsConstants::COA . '.index')
                                                        ? route(ViewsConstants::COA . '.index')
                                                        : (Route::has(Str::kebab(ViewsConstants::COA . '.index'))
                                                            ? route(Str::kebab(ViewsConstants::COA . '.index'))
                                                            : '#');
                                                    $coaId = 'chart-of-accounts-link';
                                                    $coaMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::COA,
                                                        'coa_index_route_unavailable'
                                                    ) ?? 'Chart of Accounts route is unavailable. Please contact technical support or your domain administrator.';

                                                    $jrnRoute = Route::has(ViewsConstants::JRN_ET . '.index')
                                                        ? route(ViewsConstants::JRN_ET . '.index')
                                                        : (Route::has(Str::kebab(ViewsConstants::JRN_ET . '.index'))
                                                            ? route(Str::kebab(ViewsConstants::JRN_ET . '.index'))
                                                            : '#');
                                                    $jrnId = 'journal-account-link';
                                                    $jrnMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::JRN_ET,
                                                        'jrn_et_index_route_unavailable'
                                                    ) ?? 'Journal Account route is unavailable. Please contact technical support or your domain administrator.';

                                                    $ledgerRoute = Route::has(ViewsConstants::RPT . '.ledger')
                                                        ? route(ViewsConstants::RPT . '.ledger', 0)
                                                        : '#';
                                                    $ledgerId = 'ledger-summary-link';
                                                    $ledgerMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::RPT,
                                                        'rpt_ledger_route_unavailable'
                                                    ) ?? 'Ledger Summary route is unavailable. Please contact technical support or your domain administrator.';

                                                    $balanceRoute = Route::has(ViewsConstants::RPT . '.balance.sheet')
                                                        ? route(ViewsConstants::RPT . '.balance.sheet')
                                                        : '#';
                                                    $balanceId = 'balance-sheet-link';
                                                    $balanceMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::RPT,
                                                        'rpt_balance_sheet_route_unavailable'
                                                    ) ?? 'Balance Sheet route is unavailable. Please contact technical support or your domain administrator.';

                                                    $profitRoute = Route::has(ViewsConstants::RPT . '.profit.loss')
                                                        ? route(ViewsConstants::RPT . '.profit.loss')
                                                        : '#';
                                                    $profitId = 'profit-loss-link';
                                                    $profitMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::RPT,
                                                        'rpt_profit_loss_route_unavailable'
                                                    ) ?? 'Profit & Loss route is unavailable. Please contact technical support or your domain administrator.';

                                                    $trialRoute = Route::has(ViewsConstants::RPT . '.trial.balance')
                                                        ? route(ViewsConstants::RPT . '.trial.balance')
                                                        : '#';
                                                    $trialId = 'trial-balance-link';
                                                    $trialMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::RPT,
                                                        'trial_balance_route_unavailable'
                                                    ) ?? 'Trial Balance route is unavailable. Please contact technical support or your domain administrator.';
                                                    ?>
                                                    <ul class="dash-submenu">
                                                        <li class="dash-item <?php echo e($isCoaRoute ? ' active' : ''); ?>">
                                                            <a
                                                                id="<?php echo e($coaId); ?>"
                                                                class="dash-link"
                                                                href="<?php echo e($coaRoute); ?>"
                                                                data-url="<?php echo e($coaRoute); ?>"
                                                                data-sv-localized="true"
                                                                data-guard-msg="<?php echo e($coaMsg); ?>">
                                                                <?php echo e(__('Chart of Accounts')); ?>

                                                            </a>
                                                        </li>
                                                        <li class="dash-item <?php echo e($isJrnRoute ? ' active' : ''); ?>">
                                                            <a
                                                                id="<?php echo e($jrnId); ?>"
                                                                class="dash-link"
                                                                href="<?php echo e($jrnRoute); ?>"
                                                                data-url="<?php echo e($jrnRoute); ?>"
                                                                data-sv-localized="true"
                                                                data-guard-msg="<?php echo e($jrnMsg); ?>">
                                                                <?php echo e(__('Journal Account')); ?>

                                                            </a>
                                                        </li>
                                                        <li class="dash-item <?php echo e($routeName == ViewsConstants::RPT . '.ledger' ? ' active' : ''); ?>">
                                                            <a
                                                                id="<?php echo e($ledgerId); ?>"
                                                                class="dash-link"
                                                                href="<?php echo e($ledgerRoute); ?>"
                                                                data-url="<?php echo e($ledgerRoute); ?>"
                                                                data-sv-localized="true"
                                                                data-guard-msg="<?php echo e($ledgerMsg); ?>">
                                                                <?php echo e(__('Ledger Summary')); ?>

                                                            </a>
                                                        </li>
                                                        <li class="dash-item <?php echo e($routeName == ViewsConstants::RPT . '.balance.sheet' ? ' active' : ''); ?>">
                                                            <a
                                                                id="<?php echo e($balanceId); ?>"
                                                                class="dash-link"
                                                                href="<?php echo e($balanceRoute); ?>"
                                                                data-url="<?php echo e($balanceRoute); ?>"
                                                                data-sv-localized="true"
                                                                data-guard-msg="<?php echo e($balanceMsg); ?>">
                                                                <?php echo e(__('Balance Sheet')); ?>

                                                            </a>
                                                        </li>
                                                        <li class="dash-item <?php echo e($routeName == ViewsConstants::RPT . '.profit.loss' ? ' active' : ''); ?>">
                                                            <a
                                                                id="<?php echo e($profitId); ?>"
                                                                class="dash-link"
                                                                href="<?php echo e($profitRoute); ?>"
                                                                data-url="<?php echo e($profitRoute); ?>"
                                                                data-sv-localized="true"
                                                                data-guard-msg="<?php echo e($profitMsg); ?>">
                                                                <?php echo e(__('Profit & Loss')); ?>

                                                            </a>
                                                        </li>
                                                        <li class="dash-item <?php echo e($routeName == ViewsConstants::RPT . '.trial.balance' ? ' active' : ''); ?>">
                                                            <a
                                                                id="<?php echo e($trialId); ?>"
                                                                class="dash-link"
                                                                href="<?php echo e($trialRoute); ?>"
                                                                data-url="<?php echo e($trialRoute); ?>"
                                                                data-sv-localized="true"
                                                                data-guard-msg="<?php echo e($trialMsg); ?>">
                                                                <?php echo e(__('Trial Balance')); ?>

                                                            </a>
                                                        </li>
                                                    </ul>
                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                    <script defer>
                                                        (() => {
                                                            const bindGuard = id => {
                                                                const listenerAttr = `data-${id}-listener-active`;
                                                                const el = document.getElementById(id);
                                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                el.setAttribute(listenerAttr, 'true');
                                                                el.addEventListener('click', event => {
                                                                    try {
                                                                        const url = el.getAttribute('data-url');
                                                                        const href = el.href;
                                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                                            event.preventDefault();
                                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                            const containerId = 'toast-container';
                                                                            let container = document.getElementById(containerId);
                                                                            if (!container) {
                                                                                container = document.createElement('div');
                                                                                container.id = containerId;
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (bootstrapLink && window.bootstrap) {
                                                                                const toastEl = document.createElement('div');
                                                                                toastEl.className = 'toast';
                                                                                toastEl.setAttribute('role', 'alert');
                                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                                const body = document.createElement('div');
                                                                                body.className = 'toast-body';
                                                                                body.textContent = msg;
                                                                                toastEl.appendChild(body);
                                                                                container.appendChild(toastEl);
                                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            el.setAttribute('data-failed-route', 'true');
                                                                        }
                                                                    } catch (error) {}
                                                                });
                                                                const observer = new MutationObserver(() => {
                                                                    if (!document.getElementById(id)) observer.disconnect();
                                                                });
                                                                observer.observe(document.body, {
                                                                    childList: true,
                                                                    subtree: true
                                                                });
                                                            };
                                                            [
                                                                '<?php echo e($coaId); ?>',
                                                                '<?php echo e($jrnId); ?>',
                                                                '<?php echo e($ledgerId); ?>',
                                                                '<?php echo e($balanceId); ?>',
                                                                '<?php echo e($profitId); ?>',
                                                                '<?php echo e($trialId); ?>'
                                                            ].forEach(bindGuard);
                                                        })();
                                                    </script>
                                                    <?php $__env->stopPush(); ?>
                                                </li>
                                            <?php endif; ?>
                                            <?php if (
                                                $user[UsersConstants::COL_TP] == PermissionsConstants::CPN ||
                                                $user[UsersConstants::COL_TP] == PermissionsConstants::SA
                                            ): ?>
                                                <?php
                                                $budgetRoute = Route::has(ViewsConstants::BDG . '.index')
                                                    ? route(ViewsConstants::BDG . '.index')
                                                    : '#';
                                                $linkId = 'budget-planner-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::BDG,
                                                    'budget_index_route_unavailable'
                                                ) ?? 'Budget Planner route is unavailable. Please contact technical support or your domain administrator.';
                                                ?>
                                                <li class="dash-item <?php echo e(RF::segment(1) == ViewsConstants::BDG ? 'active' : ''); ?>">
                                                    <a
                                                        id="<?php echo e($linkId); ?>"
                                                        class="dash-link"
                                                        href="<?php echo e($budgetRoute); ?>"
                                                        data-url="<?php echo e($budgetRoute); ?>"
                                                        data-sv-localized="true"
                                                        data-guard-msg="<?php echo e($message); ?>">
                                                        <?php echo e(__('Budget Planner')); ?>

                                                    </a>
                                                </li>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                <script defer>
                                                    (() => {
                                                        const listenerAttr = 'data-budget-planner-listener-active';
                                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                        el.setAttribute(listenerAttr, 'true');
                                                        el.addEventListener('click', event => {
                                                            try {
                                                                const url = el.getAttribute('data-url');
                                                                const href = el.href;
                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                    event.preventDefault();
                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                    const containerId = 'toast-container';
                                                                    let container = document.getElementById(containerId);
                                                                    if (!container) {
                                                                        container = document.createElement('div');
                                                                        container.id = containerId;
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl = document.createElement('div');
                                                                        toastEl.className = 'toast';
                                                                        toastEl.setAttribute('role', 'alert');
                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    el.setAttribute('data-failed-route', 'true');
                                                                }
                                                            } catch (error) {}
                                                        });
                                                        const observer = new MutationObserver(() => {
                                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                        });
                                                        observer.observe(document.body, {
                                                            childList: true,
                                                            subtree: true
                                                        });
                                                    })();
                                                </script>
                                                <?php $__env->stopPush(); ?>
                                            <?php endif; ?>
                                            <?php if (Gate::check(PermissionsConstants::MNG_GL)): ?>
                                                <?php
                                                $financialGoalRoute = Route::has(ViewsConstants::GL . '.index')
                                                    ? route(ViewsConstants::GL . '.index')
                                                    : '#';
                                                $linkId = 'financial-goal-index-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::GL,
                                                    'financial_goal_index_route_unavailable'
                                                ) ?? 'Financial Goal route is unavailable. Please contact technical support or your domain administrator.';
                                                ?>
                                                <li class="dash-item <?php echo e(RF::segment(1) == ViewsConstants::GL ? 'active' : ''); ?>">
                                                    <a
                                                        id="<?php echo e($linkId); ?>"
                                                        class="dash-link"
                                                        href="<?php echo e($financialGoalRoute); ?>"
                                                        data-url="<?php echo e($financialGoalRoute); ?>"
                                                        data-sv-localized="true"
                                                        data-guard-msg="<?php echo e($message); ?>">
                                                        <?php echo e(__('Financial Goal')); ?>

                                                    </a>
                                                </li>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                <script defer>
                                                    (() => {
                                                        const listenerAttr = 'data-financial-goal-index-listener-active';
                                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                        el.setAttribute(listenerAttr, 'true');
                                                        el.addEventListener('click', event => {
                                                            try {
                                                                const url = el.getAttribute('data-url');
                                                                const href = el.href;
                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                    event.preventDefault();
                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                    const containerId = 'toast-container';
                                                                    let container = document.getElementById(containerId);
                                                                    if (!container) {
                                                                        container = document.createElement('div');
                                                                        container.id = containerId;
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl = document.createElement('div');
                                                                        toastEl.className = 'toast';
                                                                        toastEl.setAttribute('role', 'alert');
                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    el.setAttribute('data-failed-route', 'true');
                                                                }
                                                            } catch (error) {}
                                                        });
                                                        const observer = new MutationObserver(() => {
                                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                        });
                                                        observer.observe(document.body, {
                                                            childList: true,
                                                            subtree: true
                                                        });
                                                    })();
                                                </script>
                                                <?php $__env->stopPush(); ?>
                                            <?php endif; ?>
                                            <?php
                                            $permissions = [
                                                PermissionsConstants::MNG_CT_TX,
                                                PermissionsConstants::MNG_CT_CAT,
                                                PermissionsConstants::MNG_CT_UNT,
                                                PermissionsConstants::MNG_CT_PAY,
                                                PermissionsConstants::MNG_CT_CST_FD
                                            ];
                                            $hasConstantsPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                                            ?>
                                            <?php if ($hasConstantsPermission): ?>
                                                <?php
                                                $segments = [
                                                    ViewsConstants::COA_TP,
                                                    ViewsConstants::CST_FD,
                                                    ViewsConstants::PAY_MTD,
                                                    ViewsConstants::PRD_SV_CAT,
                                                    ViewsConstants::PRD_SV_UNT,
                                                    ViewsConstants::TX
                                                ];
                                                $kebabSegments = array_map(function ($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);
                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isConstantSettings = in_array(RF::segment(1), $allSegments);
                                                $accountingSetupRoute = Route::has(ViewsConstants::TX . '.index')
                                                    ? route(ViewsConstants::TX . '.index')
                                                    : '#';
                                                $linkId = 'accounting-setup-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::TX,
                                                    'tx_index_route_unavailable'
                                                ) ?? 'Accounting Setup route is unavailable. Please contact technical support or your domain administrator.';
                                                ?>
                                                <li class="dash-item <?php echo e($isConstantSettings ? 'active dash-trigger' : ''); ?>">
                                                    <a
                                                        id="<?php echo e($linkId); ?>"
                                                        class="dash-link"
                                                        href="<?php echo e($accountingSetupRoute); ?>"
                                                        data-url="<?php echo e($accountingSetupRoute); ?>"
                                                        data-sv-localized="true"
                                                        data-guard-msg="<?php echo e($message); ?>">
                                                        <?php echo e(__('Accounting Setup')); ?>

                                                    </a>
                                                </li>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                <script defer>
                                                    (() => {
                                                        const listenerAttr = 'data-accounting-setup-listener-active';
                                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                        el.setAttribute(listenerAttr, 'true');
                                                        el.addEventListener('click', event => {
                                                            try {
                                                                const url = el.getAttribute('data-url');
                                                                const href = el.href;
                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                    event.preventDefault();
                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                    const containerId = 'toast-container';
                                                                    let container = document.getElementById(containerId);
                                                                    if (!container) {
                                                                        container = document.createElement('div');
                                                                        container.id = containerId;
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl = document.createElement('div');
                                                                        toastEl.className = 'toast';
                                                                        toastEl.setAttribute('role', 'alert');
                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    el.setAttribute('data-failed-route', 'true');
                                                                }
                                                            } catch (error) {}
                                                        });
                                                        const observer = new MutationObserver(() => {
                                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                        });
                                                        observer.observe(document.body, {
                                                            childList: true,
                                                            subtree: true
                                                        });
                                                    })();
                                                </script>
                                                <?php $__env->stopPush(); ?>
                                            <?php endif; ?>
                                            <?php if (Gate::check(PermissionsConstants::MNG_PRT)): ?>
                                                <?php
                                                $printSettingRoute = Route::has('print.setting')
                                                    ? route('print.setting')
                                                    : '#';
                                                $linkId = 'print-setting-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::SET,
                                                    'print_settings_route_unavailable'
                                                ) ?? 'Print Settings route is unavailable. Please contact technical support or your domain administrator.';
                                                ?>
                                                <li
                                                    class="dash-item <?php echo e((RF::route()->getName() == 'print-setting' || RF::route()->getName() == 'print_setting') ? 'active' : ''); ?>">
                                                    <a
                                                        id="<?php echo e($linkId); ?>"
                                                        class="dash-link"
                                                        href="<?php echo e($printSettingRoute); ?>"
                                                        data-url="<?php echo e($printSettingRoute); ?>"
                                                        data-sv-localized="true"
                                                        data-guard-msg="<?php echo e($message); ?>">
                                                        <?php echo e(__('Print Settings')); ?>

                                                    </a>
                                                </li>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                <script defer>
                                                    (() => {
                                                        const listenerAttr = 'data-print-setting-listener-active';
                                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                        el.setAttribute(listenerAttr, 'true');
                                                        el.addEventListener('click', event => {
                                                            try {
                                                                const url = el.getAttribute('data-url');
                                                                const href = el.href;
                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                    event.preventDefault();
                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                    const containerId = 'toast-container';
                                                                    let container = document.getElementById(containerId);
                                                                    if (!container) {
                                                                        container = document.createElement('div');
                                                                        container.id = containerId;
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl = document.createElement('div');
                                                                        toastEl.className = 'toast';
                                                                        toastEl.setAttribute('role', 'alert');
                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    el.setAttribute('data-failed-route', 'true');
                                                                }
                                                            } catch (error) {}
                                                        });
                                                        const observer = new MutationObserver(() => {
                                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                        });
                                                        observer.observe(document.body, {
                                                            childList: true,
                                                            subtree: true
                                                        });
                                                    })();
                                                </script>
                                                <?php $__env->stopPush(); ?>
                                            <?php endif; ?>
                                        </ul>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (!empty($userPlan) &&  $userPlan?->{PlansConstants::COL_CRM} == 1): ?>
                                <?php
                                $permissions = [
                                    PermissionsConstants::MNG_LD,
                                    PermissionsConstants::MNG_DL,
                                    PermissionsConstants::MNG_FM_BD,
                                    PermissionsConstants::MNG_CTC
                                ];
                                $hasActivityPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                                ?>
                                <?php if ($hasActivityPermission): ?>
                                    <?php
                                    $segments = [
                                        ViewsConstants::CTC,
                                        ViewsConstants::DL,
                                        ViewsConstants::FM_BD,
                                        ViewsConstants::FM_RP,
                                        ViewsConstants::LBL,
                                        ViewsConstants::LD,
                                        ViewsConstants::LD_STG,
                                        ViewsConstants::PPL,
                                        ViewsConstants::SRC,
                                        ViewsConstants::STG
                                    ];
                                    $kebabSegments = array_map(function ($segment) {
                                        if ($segment === null) return null;
                                        return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                    }, $segments);
                                    $allSegments = array_merge($segments, $kebabSegments);
                                    $isCrmModule = in_array(RF::segment(1), $allSegments);
                                    ?>
                                    <li class="<?php echo e($isCrmModule ? ' active dash-trigger' : ''); ?>">
                                        <a href="#!" class="dash-link">
                                            <span class="dash-micon">
                                                <i class="ti ti-layers-difference"></i>
                                            </span>
                                            <span class="dash-mtext"><?php echo e(__('CRM System')); ?></span>
                                            <span class="dash-arrow">
                                                <i data-feather="chevron-right"></i>
                                            </span>
                                        </a>
                                        <?php
                                        $segments = [
                                            ViewsConstants::DL,
                                            ViewsConstants::FM_BD,
                                            ViewsConstants::FM_RP,
                                            ViewsConstants::LBL,
                                            ViewsConstants::LD,
                                            ViewsConstants::LD_STG,
                                            ViewsConstants::PPL,
                                            ViewsConstants::SRC,
                                            ViewsConstants::STG
                                        ];
                                        $kebabSegments = array_map(function ($segment) {
                                            if ($segment === null) return null;
                                            return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                        }, $segments);
                                        $allSegments = array_merge($segments, $kebabSegments);
                                        $isCrmManagement = in_array(RF::segment(1), $allSegments);
                                        ?>
                                        <ul class="dash-submenu <?php echo e($isCrmManagement ? 'show' : ''); ?>">
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_LD)): ?>
                                                <?php
                                                $ldIndexRoute = Route::has(ViewsConstants::LD . '.index')
                                                    ? route(ViewsConstants::LD . '.index')
                                                    : '#';
                                                $linkId = 'ld-index-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::LD,
                                                    'lead_index_route_unavailable'
                                                ) ?? __('Lead setup route is unavailable. Please contact technical support or your domain administrator.');
                                                ?>
                                                <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::LD . '.list' || RF::route()->getName() == ViewsConstants::LD . '.index' || RF::route()->getName() == ViewsConstants::LD . '.show' ? 'active' : ''); ?>">
                                                    <a
                                                        id="<?php echo e($linkId); ?>"
                                                        class="dash-link"
                                                        href="<?php echo e($ldIndexRoute); ?>"
                                                        data-url="<?php echo e($ldIndexRoute); ?>"
                                                        data-sv-localized="true"
                                                        data-guard-msg="<?php echo e($message); ?>">
                                                        <?php echo e(__(ViewsConstants::LD)); ?>

                                                    </a>
                                                </li>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                <script defer>
                                                    (() => {
                                                        const listenerAttr = 'data-ld-index-listener-active';
                                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                        el.setAttribute(listenerAttr, 'true');
                                                        el.addEventListener('click', event => {
                                                            try {
                                                                const url = el.getAttribute('data-url');
                                                                const href = el.href;
                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                    event.preventDefault();
                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                    let container = document.getElementById('toast-container');
                                                                    if (!container) {
                                                                        container = document.createElement('div');
                                                                        container.id = 'toast-container';
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl = document.createElement('div');
                                                                        toastEl.className = 'toast';
                                                                        toastEl.setAttribute('role', 'alert');
                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    el.setAttribute('data-failed-route', 'true');
                                                                }
                                                            } catch (error) {}
                                                        });
                                                        const observer = new MutationObserver(() => {
                                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                        });
                                                        observer.observe(document.body, {
                                                            childList: true,
                                                            subtree: true
                                                        });
                                                    })();
                                                </script>
                                                <?php $__env->stopPush(); ?>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_DL)): ?>
                                                <?php
                                                $dlIndexRoute = Route::has(ViewsConstants::DL . '.index')
                                                    ? route(ViewsConstants::DL . '.index')
                                                    : '#';
                                                $linkId = 'dl-index-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::DL,
                                                    'deal_index_route_unavailable'
                                                ) ?? __('Deal setup route is unavailable. Please contact technical support or your domain administrator.');
                                                ?>
                                                <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::DL . '.list' || RF::route()->getName() == ViewsConstants::DL . '.index' || RF::route()->getName() == ViewsConstants::DL . '.show' ? 'active' : ''); ?>">
                                                    <a
                                                        id="<?php echo e($linkId); ?>"
                                                        class="dash-link"
                                                        href="<?php echo e($dlIndexRoute); ?>"
                                                        data-url="<?php echo e($dlIndexRoute); ?>"
                                                        data-sv-localized="true"
                                                        data-guard-msg="<?php echo e($message); ?>">
                                                        <?php echo e(__(ViewsConstants::DL)); ?>

                                                    </a>
                                                </li>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                <script defer>
                                                    (() => {
                                                        const listenerAttr = 'data-dl-index-listener-active';
                                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                        el.setAttribute(listenerAttr, 'true');
                                                        el.addEventListener('click', event => {
                                                            try {
                                                                const url = el.getAttribute('data-url');
                                                                const href = el.href;
                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                    event.preventDefault();
                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                    let container = document.getElementById('toast-container');
                                                                    if (!container) {
                                                                        container = document.createElement('div');
                                                                        container.id = 'toast-container';
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl = document.createElement('div');
                                                                        toastEl.className = 'toast';
                                                                        toastEl.setAttribute('role', 'alert');
                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    el.setAttribute('data-failed-route', 'true');
                                                                }
                                                            } catch (error) {}
                                                        });
                                                        const observer = new MutationObserver(() => {
                                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                        });
                                                        observer.observe(document.body, {
                                                            childList: true,
                                                            subtree: true
                                                        });
                                                    })();
                                                </script>
                                                <?php $__env->stopPush(); ?>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_FM_BD)): ?>
                                                <?php
                                                $formBuilderRoute = Route::has(ViewsConstants::FM_BD . '.index')
                                                    ? route(ViewsConstants::FM_BD . '.index')
                                                    : (Route::has(Str::kebab(ViewsConstants::FM_BD . '.index'))
                                                        ? route(Str::kebab(ViewsConstants::FM_BD . '.index'))
                                                        : '#');
                                                $linkId = 'form-builder-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::FM_BD,
                                                    'form_builder_index_route_unavailable'
                                                ) ?? 'Form Builder route is unavailable. Please contact technical support or your domain administrator.';
                                                ?>
                                                <li class="dash-item <?php echo e(RF::segment(1) == ViewsConstants::FM_BD || RF::segment(1) == Str::kebab(ViewsConstants::FM_BD) || RF::segment(1) == ViewsConstants::FM_RP || RF::segment(1) == Str::kebab(ViewsConstants::FM_RP) ? 'active open' : ''); ?>">
                                                    <a
                                                        id="<?php echo e($linkId); ?>"
                                                        class="dash-link"
                                                        href="<?php echo e($formBuilderRoute); ?>"
                                                        data-url="<?php echo e($formBuilderRoute); ?>"
                                                        data-sv-localized="true"
                                                        data-guard-msg="<?php echo e($message); ?>">
                                                        <?php echo e(__('Form Builder')); ?>

                                                    </a>
                                                </li>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                <script defer>
                                                    (() => {
                                                        const listenerAttr = 'data-form-builder-listener-active';
                                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                        el.setAttribute(listenerAttr, 'true');
                                                        el.addEventListener('click', event => {
                                                            try {
                                                                const url = el.getAttribute('data-url');
                                                                const href = el.href;
                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                    event.preventDefault();
                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                    const containerId = 'toast-container';
                                                                    let container = document.getElementById(containerId);
                                                                    if (!container) {
                                                                        container = document.createElement('div');
                                                                        container.id = containerId;
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl = document.createElement('div');
                                                                        toastEl.className = 'toast';
                                                                        toastEl.setAttribute('role', 'alert');
                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    el.setAttribute('data-failed-route', 'true');
                                                                }
                                                            } catch (error) {}
                                                        });
                                                        const observer = new MutationObserver(() => {
                                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                        });
                                                        observer.observe(document.body, {
                                                            childList: true,
                                                            subtree: true
                                                        });
                                                    })();
                                                </script>
                                                <?php $__env->stopPush(); ?>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_CTC)): ?>
                                                <?php
                                                $ctcIndexRoute = Route::has(ViewsConstants::CTC . '.index')
                                                    ? route(ViewsConstants::CTC . '.index')
                                                    : '#';
                                                $linkId = 'ctc-index-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    ViewsConstants::CTC,
                                                    'contract_index_route_unavailable'
                                                ) ?? __('Contract setup route is unavailable. Please contact technical support or your domain administrator.');
                                                ?>
                                                <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::CTC . '.index' || RF::route()->getName() == ViewsConstants::CTC . '.show' ? 'active' : ''); ?>">
                                                    <a
                                                        id="<?php echo e($linkId); ?>"
                                                        class="dash-link"
                                                        href="<?php echo e($ctcIndexRoute); ?>"
                                                        data-url="<?php echo e($ctcIndexRoute); ?>"
                                                        data-sv-localized="true"
                                                        data-guard-msg="<?php echo e($message); ?>">
                                                        <?php echo e(__(ViewsConstants::CTC)); ?>

                                                    </a>
                                                </li>
                                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                <script defer>
                                                    (() => {
                                                        const listenerAttr = 'data-ctc-index-listener-active';
                                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                        el.setAttribute(listenerAttr, 'true');
                                                        el.addEventListener('click', event => {
                                                            try {
                                                                const url = el.getAttribute('data-url');
                                                                const href = el.href;
                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                    event.preventDefault();
                                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                    let container = document.getElementById('toast-container');
                                                                    if (!container) {
                                                                        container = document.createElement('div');
                                                                        container.id = 'toast-container';
                                                                        document.body.appendChild(container);
                                                                    }
                                                                    if (bootstrapLink && window.bootstrap) {
                                                                        const toastEl = document.createElement('div');
                                                                        toastEl.className = 'toast';
                                                                        toastEl.setAttribute('role', 'alert');
                                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                                        const body = document.createElement('div');
                                                                        body.className = 'toast-body';
                                                                        body.textContent = msg;
                                                                        toastEl.appendChild(body);
                                                                        container.appendChild(toastEl);
                                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                    } else {
                                                                        alert(msg);
                                                                    }
                                                                    el.setAttribute('data-failed-route', 'true');
                                                                }
                                                            } catch (error) {}
                                                        });
                                                        const observer = new MutationObserver(() => {
                                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                        });
                                                        observer.observe(document.body, {
                                                            childList: true,
                                                            subtree: true
                                                        });
                                                    })();
                                                </script>
                                                <?php $__env->stopPush(); ?>
                                            <?php endif; ?>
                                        </ul>
                                    </li>
                                <?php endif; ?>
                                <?php
                                $permissions = [
                                    PermissionsConstants::MNG_LD_ST,
                                    PermissionsConstants::MNG_PPL,
                                    PermissionsConstants::MNG_SRC,
                                    PermissionsConstants::MNG_LB,
                                    PermissionsConstants::MNG_ST
                                ];
                                $hasStagesPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                                ?>
                                <?php if ($hasStagesPermission): ?>
                                    <?php
                                    $segments = [
                                        ViewsConstants::COA_TP,
                                        ViewsConstants::CST_FD,
                                        ViewsConstants::LBL,
                                        ViewsConstants::LD_STG,
                                        ViewsConstants::PAY_MTD,
                                        ViewsConstants::PPL,
                                        ViewsConstants::PRD_SV_CAT,
                                        ViewsConstants::PRD_SV_UNT,
                                        ViewsConstants::SRC,
                                        ViewsConstants::STG
                                    ];
                                    $kebabSegments = array_map(function ($segment) {
                                        if ($segment === null) return null;
                                        return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                    }, $segments);
                                    $allSegments = array_merge($segments, $kebabSegments);
                                    $isCrmSetup = in_array(RF::segment(1), $allSegments);
                                    $crmSetupRoute = Route::has(ViewsConstants::PPL . '.index')
                                        ? route(ViewsConstants::PPL . '.index')
                                        : '#';
                                    $linkId = 'crm-system-setup-link';
                                    $message = Utility::fetchLinkMessage(
                                        $lang,
                                        ViewsConstants::PPL,
                                        'pipeline_index_route_unavailable'
                                    ) ?? 'Pipeline setup route is unavailable. Please contact technical support or your domain administrator.';
                                    ?>
                                    <li class="dash-item <?php echo e($isCrmSetup ? 'active dash-trigger' : ''); ?>">
                                        <a
                                            id="<?php echo e($linkId); ?>"
                                            class="dash-link"
                                            href="<?php echo e($crmSetupRoute); ?>"
                                            data-url="<?php echo e($crmSetupRoute); ?>"
                                            data-sv-localized="true"
                                            data-guard-msg="<?php echo e($message); ?>">
                                            <?php echo e(__('CRM System Setup')); ?>

                                        </a>
                                    </li>
                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                    <script defer>
                                        (() => {
                                            const listenerAttr = 'data-crm-system-setup-listener-active';
                                            const el = document.getElementById('<?php echo e($linkId); ?>');
                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                            el.setAttribute(listenerAttr, 'true');
                                            el.addEventListener('click', event => {
                                                try {
                                                    const url = el.getAttribute('data-url');
                                                    const href = el.href;
                                                    if ((!url || url === '#') && (!href || href === '#')) {
                                                        event.preventDefault();
                                                        const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                        const containerId = 'toast-container';
                                                        let container = document.getElementById(containerId);
                                                        if (!container) {
                                                            container = document.createElement('div');
                                                            container.id = containerId;
                                                            document.body.appendChild(container);
                                                        }
                                                        if (bootstrapLink && window.bootstrap) {
                                                            const toastEl = document.createElement('div');
                                                            toastEl.className = 'toast';
                                                            toastEl.setAttribute('role', 'alert');
                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                            const body = document.createElement('div');
                                                            body.className = 'toast-body';
                                                            body.textContent = msg;
                                                            toastEl.appendChild(body);
                                                            container.appendChild(toastEl);
                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                        } else {
                                                            alert(msg);
                                                        }
                                                        el.setAttribute('data-failed-route', 'true');
                                                    }
                                                } catch (error) {}
                                            });
                                            const observer = new MutationObserver(() => {
                                                if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                            });
                                            observer.observe(document.body, {
                                                childList: true,
                                                subtree: true
                                            });
                                        })();
                                    </script>
                                    <?php $__env->stopPush(); ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </ul>
                    <?php endif; ?>


                    <?php if (!empty($userPlan) && $userPlan?->{PlansConstants::COL_PJ} == 1): ?>
                        <?php if (Gate::check(PermissionsConstants::MNG_PRJ)): ?>
                            <?php
                            $segments = [
                                ViewsConstants::BUG_RPT,
                                ViewsConstants::BUG_STT,
                                ViewsConstants::CLD,
                                ViewsConstants::PRJ,
                                ViewsConstants::PRJ_TSK_STG,
                                ViewsConstants::PRJ_RPT,
                                ViewsConstants::TSKB,
                                ViewsConstants::TMS_LT
                            ];
                            $kebabSegments = array_map(function ($segment) {
                                if ($segment === null) return null;
                                return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                            }, $segments);
                            $allSegments = array_merge($segments, $kebabSegments);
                            $isProjectManagement = in_array(RF::segment(1), $allSegments);
                            ?>
                            <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isProjectManagement ? 'active dash-trigger' : ''); ?>">
                                <a href="#!" class="dash-link">
                                    <span class="dash-micon">
                                        <i class="ti ti-share"></i>
                                    </span>
                                    <span class="dash-mtext"><?php echo e(__('Project System')); ?></span>
                                    <span class="dash-arrow">
                                        <i data-feather="chevron-right"></i>
                                    </span>
                                </a>
                                <ul class="dash-submenu">
                                    <?php
                                    $projectIndexRoute = Route::has(ViewsConstants::PRJ . '.index')
                                        ? route(ViewsConstants::PRJ . '.index')
                                        : '#';
                                    $linkId = 'projects-index-link';
                                    $message = Utility::fetchLinkMessage(
                                        $lang,
                                        ViewsConstants::PRJ,
                                        'project_index_route_unavailable'
                                    ) ?? 'Projects route is unavailable. Please contact technical support or your domain administrator.';
                                    ?>
                                    <li
                                        class="dash-item <?php echo e(RF::segment(1) == ViewsConstants::PRJ || RF::route()->getName() == ViewsConstants::PRJ . '.list' || RF::route()->getName() == ViewsConstants::PRJ . '.index' || RF::route()->getName() == ViewsConstants::PRJ . '.show' || request()->is('projects/*') ? 'active' : ''); ?>">
                                        <a
                                            id="<?php echo e($linkId); ?>"
                                            class="dash-link"
                                            href="<?php echo e($projectIndexRoute); ?>"
                                            data-url="<?php echo e($projectIndexRoute); ?>"
                                            data-sv-localized="true"
                                            data-guard-msg="<?php echo e($message); ?>">
                                            <?php echo e(__('Projects')); ?>

                                        </a>
                                    </li>
                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                    <script defer>
                                        (() => {
                                            const listenerAttr = 'data-projects-index-listener-active';
                                            const el = document.getElementById('<?php echo e($linkId); ?>');
                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                            el.setAttribute(listenerAttr, 'true');
                                            el.addEventListener('click', event => {
                                                try {
                                                    const url = el.getAttribute('data-url');
                                                    const href = el.href;
                                                    if ((!url || url === '#') && (!href || href === '#')) {
                                                        event.preventDefault();
                                                        const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                        let container = document.getElementById('toast-container');
                                                        if (!container) {
                                                            container = document.createElement('div');
                                                            container.id = 'toast-container';
                                                            document.body.appendChild(container);
                                                        }
                                                        if (bootstrapLink && window.bootstrap) {
                                                            const toastEl = document.createElement('div');
                                                            toastEl.className = 'toast';
                                                            toastEl.setAttribute('role', 'alert');
                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                            const body = document.createElement('div');
                                                            body.className = 'toast-body';
                                                            body.textContent = msg;
                                                            toastEl.appendChild(body);
                                                            container.appendChild(toastEl);
                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                        } else {
                                                            alert(msg);
                                                        }
                                                        el.setAttribute('data-failed-route', 'true');
                                                    }
                                                } catch (error) {}
                                            });
                                            const observer = new MutationObserver(() => {
                                                if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                            });
                                            observer.observe(document.body, {
                                                childList: true,
                                                subtree: true
                                            });
                                        })();
                                    </script>
                                    <?php $__env->stopPush(); ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_PRJ_TSK)): ?>
                                        <?php
                                        $tasksRoute = Route::has(ViewsConstants::TSKB . '.view')
                                            ? route(ViewsConstants::TSKB . '.view', 'list')
                                            : '#';
                                        $linkId = 'tasks-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::TSKB,
                                            'taskboard_view_route_unavailable'
                                        ) ?? 'Tasks route is unavailable. Please contact technical support or your domain administrator.';
                                        ?>
                                        <li class="dash-item <?php echo e(request()->is('taskboard*') ? 'active' : ''); ?>">
                                            <a
                                                id="<?php echo e($linkId); ?>"
                                                class="dash-link"
                                                href="<?php echo e($tasksRoute); ?>"
                                                data-url="<?php echo e($tasksRoute); ?>"
                                                data-sv-localized="true"
                                                data-guard-msg="<?php echo e($message); ?>">
                                                <?php echo e(__('Tasks')); ?>

                                            </a>
                                        </li>
                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (() => {
                                                const listenerAttr = 'data-tasks-listener-active';
                                                const el = document.getElementById('tasks-link');
                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                el.setAttribute(listenerAttr, 'true');
                                                el.addEventListener('click', event => {
                                                    try {
                                                        const url = el.getAttribute('data-url');
                                                        const href = el.href;
                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                            event.preventDefault();
                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            const containerId = 'toast-container';
                                                            let container = document.getElementById(containerId);
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = containerId;
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bootstrapLink && window.bootstrap) {
                                                                const toastEl = document.createElement('div');
                                                                toastEl.className = 'toast';
                                                                toastEl.setAttribute('role', 'alert');
                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            el.setAttribute('data-failed-route', 'true');
                                                        }
                                                    } catch (error) {}
                                                });
                                                const observer = new MutationObserver(() => {
                                                    if (!document.getElementById('tasks-link')) observer.disconnect();
                                                });
                                                observer.observe(document.body, {
                                                    childList: true,
                                                    subtree: true
                                                });
                                            })();
                                        </script>
                                        <?php $__env->stopPush(); ?>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_TS)): ?>
                                        <?php
                                        $timesheetListRoute = Route::has(ViewsConstants::TMS . '.list')
                                            ? route(ViewsConstants::TMS . '.list')
                                            : '#';
                                        $linkId = 'timesheet-list-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::TMS,
                                            'timesheet_list_route_unavailable'
                                        ) ?? 'Timesheet route is unavailable. Please contact technical support or your domain administrator.';
                                        ?>
                                        <li class="dash-item <?php echo e((request()->is('timesheet-list*') || request()->is('timesheet_list*')) ? 'active' : ''); ?>">
                                            <a
                                                id="<?php echo e($linkId); ?>"
                                                class="dash-link"
                                                href="<?php echo e($timesheetListRoute); ?>"
                                                data-url="<?php echo e($timesheetListRoute); ?>"
                                                data-sv-localized="true"
                                                data-guard-msg="<?php echo e($message); ?>">
                                                <?php echo e(__('Timesheet')); ?>

                                            </a>
                                        </li>
                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (() => {
                                                const listenerAttr = 'data-timesheet-listener-active';
                                                const el = document.getElementById('<?php echo e($linkId); ?>');
                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                el.setAttribute(listenerAttr, 'true');
                                                el.addEventListener('click', event => {
                                                    try {
                                                        const url = el.getAttribute('data-url');
                                                        const href = el.href;
                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                            event.preventDefault();
                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            const containerId = 'toast-container';
                                                            let container = document.getElementById(containerId);
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = containerId;
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bootstrapLink && window.bootstrap) {
                                                                const toastEl = document.createElement('div');
                                                                toastEl.className = 'toast';
                                                                toastEl.setAttribute('role', 'alert');
                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            el.setAttribute('data-failed-route', 'true');
                                                        }
                                                    } catch (error) {}
                                                });
                                                const observer = new MutationObserver(() => {
                                                    if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                });
                                                observer.observe(document.body, {
                                                    childList: true,
                                                    subtree: true
                                                });
                                            })();
                                        </script>
                                        <?php $__env->stopPush(); ?>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_BUG_RPT)): ?>
                                        <?php
                                        $bugViewRoute = Route::has(ViewsConstants::BUG . '.view')
                                            ? route(ViewsConstants::BUG . '.view', 'list')
                                            : '#';
                                        $linkId = 'bug-view-list-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::BUG,
                                            'bug_view_route_unavailable'
                                        ) ?? 'Bug route is unavailable. Please contact technical support or your domain administrator.';
                                        ?>
                                        <li class="dash-item <?php echo e((request()->is('bugs-report*') || request()->is('bugs_report*')) ? 'active' : ''); ?>">
                                            <a
                                                id="<?php echo e($linkId); ?>"
                                                class="dash-link"
                                                href="<?php echo e($bugViewRoute); ?>"
                                                data-url="<?php echo e($bugViewRoute); ?>"
                                                data-sv-localized="true"
                                                data-guard-msg="<?php echo e($message); ?>">
                                                <?php echo e(__('Bug')); ?>

                                            </a>
                                        </li>
                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (() => {
                                                const listenerAttr = 'data-bug-view-list-listener-active';
                                                const el = document.getElementById('<?php echo e($linkId); ?>');
                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                el.setAttribute(listenerAttr, 'true');
                                                el.addEventListener('click', event => {
                                                    try {
                                                        const url = el.getAttribute('data-url');
                                                        const href = el.href;
                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                            event.preventDefault();
                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            const containerId = 'toast-container';
                                                            let container = document.getElementById(containerId);
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = containerId;
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bootstrapLink && window.bootstrap) {
                                                                const toastEl = document.createElement('div');
                                                                toastEl.className = 'toast';
                                                                toastEl.setAttribute('role', 'alert');
                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            el.setAttribute('data-failed-route', 'true');
                                                        }
                                                    } catch (error) {}
                                                });
                                                const observer = new MutationObserver(() => {
                                                    if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                });
                                                observer.observe(document.body, {
                                                    childList: true,
                                                    subtree: true
                                                });
                                            })();
                                        </script>
                                        <?php $__env->stopPush(); ?>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_PRJ_TSK)): ?>
                                        <?php
                                        $taskCalendarRoute = Route::has(ViewsConstants::TSK . '.calendar')
                                            ? route(ViewsConstants::TSK . '.calendar', ['all'])
                                            : '#';
                                        $linkId = 'task-calendar-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::TSK,
                                            'tsk_calendar_route_unavailable'
                                        ) ?? 'Task Calendar route is unavailable. Please contact technical support or your domain administrator.';
                                        ?>
                                        <li class="dash-item <?php echo e(request()->is('calendar*') ? 'active' : ''); ?>">
                                            <a
                                                id="<?php echo e($linkId); ?>"
                                                class="dash-link"
                                                href="<?php echo e($taskCalendarRoute); ?>"
                                                data-url="<?php echo e($taskCalendarRoute); ?>"
                                                data-sv-localized="true"
                                                data-guard-msg="<?php echo e($message); ?>">
                                                <?php echo e(__('Task Calendar')); ?>

                                            </a>
                                        </li>
                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (() => {
                                                const listenerAttr = 'data-task-calendar-listener-active';
                                                const el = document.getElementById('<?php echo e($linkId); ?>');
                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                el.setAttribute(listenerAttr, 'true');
                                                el.addEventListener('click', event => {
                                                    try {
                                                        const url = el.getAttribute('data-url');
                                                        const href = el.href;
                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                            event.preventDefault();
                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = 'toast-container';
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bootstrapLink && window.bootstrap) {
                                                                const toastEl = document.createElement('div');
                                                                toastEl.className = 'toast';
                                                                toastEl.setAttribute('role', 'alert');
                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            el.setAttribute('data-failed-route', 'true');
                                                        }
                                                    } catch (error) {}
                                                });
                                                const observer = new MutationObserver(() => {
                                                    if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                });
                                                observer.observe(document.body, {
                                                    childList: true,
                                                    subtree: true
                                                });
                                            })();
                                        </script>
                                        <?php $__env->stopPush(); ?>
                                    <?php endif; ?>
                                    <?php if ($user[UsersConstants::COL_TP] != PermissionsConstants::SA): ?>
                                        <?php
                                        $trackerRoute = Route::has('time.tracker')
                                            ? route('time.tracker')
                                            : '#';
                                        $linkId = 'tracker-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::TMT,
                                            'time_tracker_route_unavailable'
                                        ) ?? 'Tracker route is unavailable. Please contact technical support or your domain administrator.';
                                        ?>
                                        <li class="dash-item <?php echo e((RF::segment(1) == 'time-trackers' || RF::segment(1) == ViewsConstants::TMT) ? 'active open' : ''); ?>">
                                            <a
                                                id="<?php echo e($linkId); ?>"
                                                class="dash-link"
                                                href="<?php echo e($trackerRoute); ?>"
                                                data-url="<?php echo e($trackerRoute); ?>"
                                                data-sv-localized="true"
                                                data-guard-msg="<?php echo e($message); ?>">
                                                <?php echo e(__('Tracker')); ?>

                                            </a>
                                        </li>
                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (() => {
                                                const listenerAttr = 'data-tracker-listener-active';
                                                const el = document.getElementById('<?php echo e($linkId); ?>');
                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                el.setAttribute(listenerAttr, 'true');
                                                el.addEventListener('click', event => {
                                                    try {
                                                        const url = el.getAttribute('data-url');
                                                        const href = el.href;
                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                            event.preventDefault();
                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = 'toast-container';
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bootstrapLink && window.bootstrap) {
                                                                const toastEl = document.createElement('div');
                                                                toastEl.className = 'toast';
                                                                toastEl.setAttribute('role', 'alert');
                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            el.setAttribute('data-failed-route', 'true');
                                                        }
                                                    } catch (error) {}
                                                });
                                                const observer = new MutationObserver(() => {
                                                    if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                });
                                                observer.observe(document.body, {
                                                    childList: true,
                                                    subtree: true
                                                });
                                            })();
                                        </script>
                                        <?php $__env->stopPush(); ?>
                                    <?php endif; ?>
                                    <?php if (
                                        $user[UsersConstants::COL_TP] == PermissionsConstants::CPN ||
                                        strtolower($user[UsersConstants::COL_TP]) == 'employee' ||
                                        $user[UsersConstants::COL_TP] == PermissionsConstants::SA
                                    ): ?>
                                        <?php
                                        $projectReportRoute = Route::has(ViewsConstants::PRJ_RPT . '.index')
                                            ? route(ViewsConstants::PRJ_RPT . '.index')
                                            : (Route::has(Str::kebab(ViewsConstants::PRJ_RPT . '.index'))
                                                ? route(Str::kebab(ViewsConstants::PRJ_RPT . '.index'))
                                                : '#');
                                        $linkId = 'project-report-index-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::PRJ_RPT,
                                            'project_report_index_route_unavailable'
                                        ) ?? 'Project Report route is unavailable. Please contact technical support or your domain administrator.';
                                        ?>
                                        <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::PRJ_RPT . '.index' || RF::route()->getName() == ViewsConstants::PRJ_RPT . '.show' ? 'active' : ''); ?>">
                                            <a
                                                id="<?php echo e($linkId); ?>"
                                                class="dash-link"
                                                href="<?php echo e($projectReportRoute); ?>"
                                                data-url="<?php echo e($projectReportRoute); ?>"
                                                data-sv-localized="true"
                                                data-guard-msg="<?php echo e($message); ?>">
                                                <?php echo e(__('Project Report')); ?>

                                            </a>
                                        </li>
                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (() => {
                                                const listenerAttr = 'data-project-report-index-listener-active';
                                                const el = document.getElementById('<?php echo e($linkId); ?>');
                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                el.setAttribute(listenerAttr, 'true');
                                                el.addEventListener('click', event => {
                                                    try {
                                                        const url = el.getAttribute('data-url');
                                                        const href = el.href;
                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                            event.preventDefault();
                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = 'toast-container';
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bootstrapLink && window.bootstrap) {
                                                                const toastEl = document.createElement('div');
                                                                toastEl.className = 'toast';
                                                                toastEl.setAttribute('role', 'alert');
                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            el.setAttribute('data-failed-route', 'true');
                                                        }
                                                    } catch (error) {}
                                                });
                                                const observer = new MutationObserver(() => {
                                                    if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                });
                                                observer.observe(document.body, {
                                                    childList: true,
                                                    subtree: true
                                                });
                                            })();
                                        </script>
                                        <?php $__env->stopPush(); ?>
                                    <?php endif; ?>
                                    <?php
                                    $permissions = [
                                        PermissionsConstants::MNG_PRJ_TSK_STG,
                                        PermissionsConstants::MNG_BUG_STT
                                    ];
                                    $hasStatusManagementPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                                    ?>
                                    <?php if ($hasStatusManagementPermission): ?>
                                        <?php
                                        $segments = [
                                            ViewsConstants::BUG_STT,
                                            ViewsConstants::PRJ_TSK_STG
                                        ];
                                        $kebabSegments = array_map(function ($segment) {
                                            if ($segment === null) return null;
                                            return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                        }, $segments);
                                        $allSegments = array_merge($segments, $kebabSegments);
                                        $isProjectSetup = in_array(RF::segment(1), $allSegments);
                                        ?>
                                        <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isProjectSetup ? 'active dash-trigger' : ''); ?>">
                                            <a class="dash-link" href="#">
                                                <?php echo e(__('Project System Setup')); ?>

                                                <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                            </a>
                                            <ul class="dash-submenu">
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_PRJ_TSK_STG)): ?>
                                                    <?php
                                                    $projectTaskStagesRoute = Route::has(ViewsConstants::PRJ_TSK_STG . '.index')
                                                        ? route(ViewsConstants::PRJ_TSK_STG . '.index')
                                                        : (Route::has(Str::kebab(ViewsConstants::PRJ_TSK_STG . '.index'))
                                                            ? route(Str::kebab(ViewsConstants::PRJ_TSK_STG . '.index'))
                                                            : '#');
                                                    $linkId = 'project-task-stages-index-link';
                                                    $message = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::PRJ_TSK_STG,
                                                        'project_task_stages_index_route_unavailable'
                                                    ) ?? 'Project Task Stages route is unavailable. Please contact technical support or your domain administrator.';
                                                    ?>
                                                    <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::PRJ_TSK_STG . '.index' ? 'active' : ''); ?>">
                                                        <a
                                                            id="<?php echo e($linkId); ?>"
                                                            class="dash-link"
                                                            href="<?php echo e($projectTaskStagesRoute); ?>"
                                                            data-url="<?php echo e($projectTaskStagesRoute); ?>"
                                                            data-sv-localized="true"
                                                            data-guard-msg="<?php echo e($message); ?>">
                                                            <?php echo e(__('Project Task Stages')); ?>

                                                        </a>
                                                    </li>
                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                    <script defer>
                                                        (() => {
                                                            const listenerAttr = 'data-project-task-stages-listener-active';
                                                            const el = document.getElementById('<?php echo e($linkId); ?>');
                                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                            el.setAttribute(listenerAttr, 'true');
                                                            el.addEventListener('click', event => {
                                                                try {
                                                                    const url = el.getAttribute('data-url');
                                                                    const href = el.href;
                                                                    if ((!url || url === '#') && (!href || href === '#')) {
                                                                        event.preventDefault();
                                                                        const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        let container = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container = document.createElement('div');
                                                                            container.id = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl = document.createElement('div');
                                                                            toastEl.className = 'toast';
                                                                            toastEl.setAttribute('role', 'alert');
                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                            const body = document.createElement('div');
                                                                            body.className = 'toast-body';
                                                                            body.textContent = msg;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
                                                                        el.setAttribute('data-failed-route', 'true');
                                                                    }
                                                                } catch (error) {}
                                                            });
                                                            const observer = new MutationObserver(() => {
                                                                if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                            });
                                                            observer.observe(document.body, {
                                                                childList: true,
                                                                subtree: true
                                                            });
                                                        })();
                                                    </script>
                                                    <?php $__env->stopPush(); ?>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_BUG_STT)): ?>
                                                    <?php
                                                    $bugStatusRoute = Route::has(ViewsConstants::BUG_STT . '.index')
                                                        ? route(ViewsConstants::BUG_STT . '.index')
                                                        : (Route::has(Str::kebab(ViewsConstants::BUG_STT . '.index'))
                                                            ? route(Str::kebab(ViewsConstants::BUG_STT . '.index'))
                                                            : '#');
                                                    $linkId = 'bug-status-index-link';
                                                    $message = Utility::fetchLinkMessage(
                                                        $lang,
                                                        ViewsConstants::BUG_STT,
                                                        'bug_status_index_route_unavailable'
                                                    ) ?? 'Bug Status route is unavailable. Please contact technical support or your domain administrator.';
                                                    ?>
                                                    <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::BUG_STT . '.index' ? 'active' : ''); ?>">
                                                        <a
                                                            id="<?php echo e($linkId); ?>"
                                                            class="dash-link"
                                                            href="<?php echo e($bugStatusRoute); ?>"
                                                            data-url="<?php echo e($bugStatusRoute); ?>"
                                                            data-sv-localized="true"
                                                            data-guard-msg="<?php echo e($message); ?>">
                                                            <?php echo e(__('Bug Status')); ?>

                                                        </a>
                                                    </li>
                                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                                    <script defer>
                                                        (() => {
                                                            const listenerAttr = 'data-bug-status-listener-active';
                                                            const el = document.getElementById('<?php echo e($linkId); ?>');
                                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                            el.setAttribute(listenerAttr, 'true');
                                                            el.addEventListener('click', event => {
                                                                try {
                                                                    const url = el.getAttribute('data-url');
                                                                    const href = el.href;
                                                                    if ((!url || url === '#') && (!href || href === '#')) {
                                                                        event.preventDefault();
                                                                        const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        let container = document.getElementById('toast-container');
                                                                        if (!container) {
                                                                            container = document.createElement('div');
                                                                            container.id = 'toast-container';
                                                                            document.body.appendChild(container);
                                                                        }
                                                                        if (bootstrapLink && window.bootstrap) {
                                                                            const toastEl = document.createElement('div');
                                                                            toastEl.className = 'toast';
                                                                            toastEl.setAttribute('role', 'alert');
                                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                                            const body = document.createElement('div');
                                                                            body.className = 'toast-body';
                                                                            body.textContent = msg;
                                                                            toastEl.appendChild(body);
                                                                            container.appendChild(toastEl);
                                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                        } else {
                                                                            alert(msg);
                                                                        }
                                                                        el.setAttribute('data-failed-route', 'true');
                                                                    }
                                                                } catch (error) {}
                                                            });
                                                            const observer = new MutationObserver(() => {
                                                                if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                            });
                                                            observer.observe(document.body, {
                                                                childList: true,
                                                                subtree: true
                                                            });
                                                        })();
                                                    </script>
                                                    <?php $__env->stopPush(); ?>
                                                <?php endif; ?>
                                            </ul>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>


                    <?php
                    $userTypes = [PermissionsConstants::SA, PermissionsConstants::ADM];
                    $permissions = [
                        PermissionsConstants::MNG_USER,
                        PermissionsConstants::MNG_ROLE,
                        PermissionsConstants::MNG_CLT
                    ];
                    $hasUserType = in_array($user[UsersConstants::COL_TP], $userTypes);
                    $hasUserAdminPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                    ?>
                    <?php if ($hasUserAdminPermission): ?>
                        <?php
                        $segments = [
                            ViewsConstants::CLT,
                            ViewsConstants::RL,
                            ViewsConstants::USR,
                            ViewsConstants::USR_LG
                        ];
                        $kebabSegments = array_map(function ($segment) {
                            if ($segment === null) return null;
                            return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                        }, $segments);
                        $allSegments = array_merge($segments, $kebabSegments);
                        $isUserManagement = in_array(RF::segment(1), $allSegments);
                        ?>
                        <li class="<?php echo e($isUserManagement ? ' active dash-trigger' : ''); ?>">
                            <a href="#!" class="dash-link">
                                <span class="dash-micon">
                                    <i class="<?php echo e(VC::TI_USRS); ?>"></i>
                                </span>
                                <span class="dash-mtext"><?php echo e(__('User Management')); ?></span>
                                <span class="dash-arrow">
                                    <i data-feather="chevron-right"></i>
                                </span>
                            </a>
                            <ul class="dash-submenu">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_USER)): ?>
                                    <?php
                                    $userIndexRoute = Route::has(ViewsConstants::USR . '.index')
                                        ? route(ViewsConstants::USR . '.index')
                                        : '#';
                                    $linkId = 'user-index-link';
                                    $message = Utility::fetchLinkMessage(
                                        $lang,
                                        ViewsConstants::USR,
                                        'user_index_route_unavailable'
                                    ) ?? __('User route is unavailable. Please contact technical support or your domain administrator.');
                                    ?>
                                    <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::USR . '.index' || RF::route()->getName() == ViewsConstants::USR . '.create' || RF::route()->getName() == ViewsConstants::USR . '.edit' || RF::route()->getName() == ViewsConstants::USR . '.userlog' ? 'active' : ''); ?>">
                                        <a
                                            id="<?php echo e($linkId); ?>"
                                            class="dash-link"
                                            href="<?php echo e($userIndexRoute); ?>"
                                            data-url="<?php echo e($userIndexRoute); ?>"
                                            data-sv-localized="true"
                                            data-guard-msg="<?php echo e($message); ?>">
                                            <?php echo e(__('User')); ?>

                                        </a>
                                    </li>
                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                    <script defer>
                                        (() => {
                                            const listenerAttr = 'data-user-index-listener-active';
                                            const el = document.getElementById('<?php echo e($linkId); ?>');
                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                            el.setAttribute(listenerAttr, 'true');
                                            el.addEventListener('click', event => {
                                                try {
                                                    const url = el.getAttribute('data-url');
                                                    const href = el.href;
                                                    if ((!url || url === '#') && (!href || href === '#')) {
                                                        event.preventDefault();
                                                        const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                        let container = document.getElementById('toast-container');
                                                        if (!container) {
                                                            container = document.createElement('div');
                                                            container.id = 'toast-container';
                                                            document.body.appendChild(container);
                                                        }
                                                        if (bootstrapLink && window.bootstrap) {
                                                            const toastEl = document.createElement('div');
                                                            toastEl.className = 'toast';
                                                            toastEl.setAttribute('role', 'alert');
                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                            const body = document.createElement('div');
                                                            body.className = 'toast-body';
                                                            body.textContent = msg;
                                                            toastEl.appendChild(body);
                                                            container.appendChild(toastEl);
                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                        } else {
                                                            alert(msg);
                                                        }
                                                        el.setAttribute('data-failed-route', 'true');
                                                    }
                                                } catch (error) {}
                                            });
                                            const observer = new MutationObserver(() => {
                                                if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                            });
                                            observer.observe(document.body, {
                                                childList: true,
                                                subtree: true
                                            });
                                        })();
                                    </script>
                                    <?php $__env->stopPush(); ?>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_ROLE)): ?>
                                    <?php
                                    $roleIndexRoute = Route::has(ViewsConstants::RL . '.index')
                                        ? route(ViewsConstants::RL . '.index')
                                        : '#';
                                    $linkId = 'role-index-link';
                                    $message = Utility::fetchLinkMessage(
                                        $lang,
                                        ViewsConstants::RL,
                                        'role_index_route_unavailable'
                                    ) ?? 'Role route is unavailable. Please contact technical support or your domain administrator.';
                                    ?>
                                    <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::RL . '.index' || RF::route()->getName() == ViewsConstants::RL . '.create' || RF::route()->getName() == ViewsConstants::RL . '.edit' ? 'active' : ''); ?>">
                                        <a
                                            id="<?php echo e($linkId); ?>"
                                            class="dash-link"
                                            href="<?php echo e($roleIndexRoute); ?>"
                                            data-url="<?php echo e($roleIndexRoute); ?>"
                                            data-sv-localized="true"
                                            data-guard-msg="<?php echo e($message); ?>">
                                            <?php echo e(__('Role')); ?>

                                        </a>
                                    </li>
                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                    <script defer>
                                        (() => {
                                            const listenerAttr = 'data-role-index-listener-active';
                                            const el = document.getElementById('<?php echo e($linkId); ?>');
                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                            el.setAttribute(listenerAttr, 'true');
                                            el.addEventListener('click', event => {
                                                try {
                                                    const url = el.getAttribute('data-url');
                                                    const href = el.href;
                                                    if ((!url || url === '#') && (!href || href === '#')) {
                                                        event.preventDefault();
                                                        const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                        let container = document.getElementById('toast-container');
                                                        if (!container) {
                                                            container = document.createElement('div');
                                                            container.id = 'toast-container';
                                                            document.body.appendChild(container);
                                                        }
                                                        if (bootstrapLink && window.bootstrap) {
                                                            const toastEl = document.createElement('div');
                                                            toastEl.className = 'toast';
                                                            toastEl.setAttribute('role', 'alert');
                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                            const body = document.createElement('div');
                                                            body.className = 'toast-body';
                                                            body.textContent = msg;
                                                            toastEl.appendChild(body);
                                                            container.appendChild(toastEl);
                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                        } else {
                                                            alert(msg);
                                                        }
                                                        el.setAttribute('data-failed-route', 'true');
                                                    }
                                                } catch (error) {}
                                            });
                                            const observer = new MutationObserver(() => {
                                                if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                            });
                                            observer.observe(document.body, {
                                                childList: true,
                                                subtree: true
                                            });
                                        })();
                                    </script>
                                    <?php $__env->stopPush(); ?>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_CLT)): ?>
                                    <?php
                                    $clientsIndexRoute = Route::has(ViewsConstants::CLT . '.index')
                                        ? route(ViewsConstants::CLT . '.index')
                                        : '#';
                                    $linkId = 'clients-index-link';
                                    $message = Utility::fetchLinkMessage(
                                        $lang,
                                        ViewsConstants::CLT,
                                        'client_index_route_unavailable'
                                    ) ?? 'Clients route is unavailable. Please contact technical support or your domain administrator.';
                                    ?>
                                    <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::CLT . '.index' || RF::segment(1) == ViewsConstants::CLT || RF::route()->getName() == ViewsConstants::CLT . '.edit' ? 'active' : ''); ?>">
                                        <a
                                            id="<?php echo e($linkId); ?>"
                                            class="dash-link"
                                            href="<?php echo e($clientsIndexRoute); ?>"
                                            data-url="<?php echo e($clientsIndexRoute); ?>"
                                            data-sv-localized="true"
                                            data-guard-msg="<?php echo e($message); ?>">
                                            <?php echo e(__('Clients')); ?>

                                        </a>
                                    </li>
                                    <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                    <script defer>
                                        (() => {
                                            const listenerAttr = 'data-clients-index-listener-active';
                                            const el = document.getElementById('<?php echo e($linkId); ?>');
                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                            el.setAttribute(listenerAttr, 'true');
                                            el.addEventListener('click', event => {
                                                try {
                                                    const url = el.getAttribute('data-url');
                                                    const href = el.href;
                                                    if ((!url || url === '#') && (!href || href === '#')) {
                                                        event.preventDefault();
                                                        const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                        let container = document.getElementById('toast-container');
                                                        if (!container) {
                                                            container = document.createElement('div');
                                                            container.id = 'toast-container';
                                                            document.body.appendChild(container);
                                                        }
                                                        if (bootstrapLink && window.bootstrap) {
                                                            const toastEl = document.createElement('div');
                                                            toastEl.className = 'toast';
                                                            toastEl.setAttribute('role', 'alert');
                                                            toastEl.setAttribute('aria-live', 'assertive');
                                                            toastEl.setAttribute('aria-atomic', 'true');
                                                            const body = document.createElement('div');
                                                            body.className = 'toast-body';
                                                            body.textContent = msg;
                                                            toastEl.appendChild(body);
                                                            container.appendChild(toastEl);
                                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                        } else {
                                                            alert(msg);
                                                        }
                                                        el.setAttribute('data-failed-route', 'true');
                                                    }
                                                } catch (error) {}
                                            });
                                            const observer = new MutationObserver(() => {
                                                if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                            });
                                            observer.observe(document.body, {
                                                childList: true,
                                                subtree: true
                                            });
                                        })();
                                    </script>
                                    <?php $__env->stopPush(); ?>
                                <?php endif; ?>





                            </ul>
                        </li>
                    <?php endif; ?>


                    <?php if (Gate::check(PermissionsConstants::MNG_PRD_SV)): ?>
                        <li class="<?php echo e(VC::DSH_IT_MN); ?>">
                            <a href="#!" class="dash-link">
                                <span class="dash-micon">
                                    <i class="ti ti-shopping-cart"></i>
                                </span>
                                <span class="dash-mtext"><?php echo e(__('Products System')); ?></span>
                                <span class="dash-arrow">
                                    <i data-feather="chevron-right"></i>
                                </span>
                            </a>
                            <?php
                            $prodSvRoute = Route::has(ViewsConstants::PRD_SV . '.index')
                                ? route(ViewsConstants::PRD_SV . '.index')
                                : (Route::has(Str::kebab(ViewsConstants::PRD_SV . '.index'))
                                    ? route(Str::kebab(ViewsConstants::PRD_SV . '.index'))
                                    : '#');
                            $prodSvId = 'product-services-index-link';
                            $prodSvMsg = Utility::fetchLinkMessage(
                                $lang,
                                ViewsConstants::PRD_SV,
                                'product_services_index_route_unavailable'
                            ) ?? 'Product & Services route is unavailable. Please contact technical support or your domain administrator.';
                            $prodStkRoute = Route::has(ViewsConstants::PRD_STK . '.index')
                                ? route(ViewsConstants::PRD_STK . '.index')
                                : (Route::has(Str::kebab(ViewsConstants::PRD_STK . '.index'))
                                    ? route(Str::kebab(ViewsConstants::PRD_STK . '.index'))
                                    : '#');
                            $prodStkId = 'product-stock-index-link';
                            $prodStkMsg = Utility::fetchLinkMessage(
                                $lang,
                                ViewsConstants::PRD_STK,
                                'product_stock_index_route_unavailable'
                            ) ?? 'Product Stock route is unavailable. Please contact technical support or your domain administrator.';
                            ?>
                            <ul class="dash-submenu">
                                <li class="dash-item <?php echo e(RF::segment(1) == ViewsConstants::PRD_SV || RF::segment(1) == Str::kebab(ViewsConstants::PRD_SV) ? 'active' : ''); ?>">
                                    <a
                                        id="<?php echo e($prodSvId); ?>"
                                        class="dash-link"
                                        href="<?php echo e($prodSvRoute); ?>"
                                        data-url="<?php echo e($prodSvRoute); ?>"
                                        data-sv-localized="true"
                                        data-guard-msg="<?php echo e($prodSvMsg); ?>">
                                        <?php echo e(__('Product & Services')); ?>

                                    </a>
                                </li>
                                <li class="dash-item <?php echo e(RF::segment(1) == ViewsConstants::PRD_STK || RF::segment(1) == Str::kebab(ViewsConstants::PRD_STK) ? 'active' : ''); ?>">
                                    <a
                                        id="<?php echo e($prodStkId); ?>"
                                        class="dash-link"
                                        href="<?php echo e($prodStkRoute); ?>"
                                        data-url="<?php echo e($prodStkRoute); ?>"
                                        data-sv-localized="true"
                                        data-guard-msg="<?php echo e($prodStkMsg); ?>">
                                        <?php echo e(__('Product Stock')); ?>

                                    </a>
                                </li>
                            </ul>
                            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                            <script defer>
                                (() => {
                                    const bindGuard = id => {
                                        const listenerAttr = `data-${id}-listener-active`;
                                        const el = document.getElementById(id);
                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                        el.setAttribute(listenerAttr, 'true');
                                        el.addEventListener('click', event => {
                                            try {
                                                const url = el.getAttribute('data-url');
                                                const href = el.href;
                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                    event.preventDefault();
                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bootstrapLink && window.bootstrap) {
                                                        const toastEl = document.createElement('div');
                                                        toastEl.className = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    el.setAttribute('data-failed-route', 'true');
                                                }
                                            } catch {}
                                        });
                                        const observer = new MutationObserver(() => {
                                            if (!document.getElementById(id)) observer.disconnect();
                                        });
                                        observer.observe(document.body, {
                                            childList: true,
                                            subtree: true
                                        });
                                    };
                                    ['<?php echo e($prodSvId); ?>', '<?php echo e($prodStkId); ?>'].forEach(bindGuard);
                                })();
                            </script>
                            <?php $__env->stopPush(); ?>
                        </li>
                    <?php endif; ?>


                    <?php if (!empty($userPlan) && $userPlan?->{PlansConstants::COL_POS} == 1): ?>
                        <?php
                        $permissions = [
                            PermissionsConstants::MNG_WRH,
                            PermissionsConstants::MNG_PRC,
                            PermissionsConstants::MNG_POS,
                            PermissionsConstants::MNG_PRT
                        ];
                        $hasPurchasePermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                        ?>
                        <?php if ($hasPurchasePermission): ?>
                            <?php
                            $segments = [
                                ViewsConstants::PRC,
                                ViewsConstants::WRH
                            ];
                            $routeNames = [
                                ViewsConstants::POS . '.barcode',
                                ViewsConstants::POS . '.print',
                                ViewsConstants::POS . '.show'
                            ];
                            $kebabSegments = array_map(function ($segment) {
                                if ($segment === null) return null;
                                return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                            }, $segments);
                            $allSegments = array_merge($segments, $kebabSegments);
                            $isPosModule = in_array(RF::segment(1), $allSegments) ||
                                in_array(RF::route()->getName(), $routeNames);
                            ?>
                            <li
                                class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e($isPosModule ? ' active dash-trigger' : ''); ?>">
                                <a href="#!" class="dash-link">
                                    <span class="dash-micon"><i class="ti ti-layers-difference"></i></span>
                                    <span class="dash-mtext"><?php echo e(__('POS System')); ?></span>
                                    <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                </a>
                                <ul class="dash-submenu <?php echo e(RF::segment(1) == ViewsConstants::WRH ||
                                                            RF::segment(1) == ViewsConstants::PRC ||
                                                            RF::route()->getName() == ViewsConstants::POS . '.barcode' ||
                                                            RF::route()->getName() == ViewsConstants::POS . '.print' ||
                                                            RF::route()->getName() == ViewsConstants::POS . '.show'
                                                            ? 'show'
                                                            : ''); ?>">
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_WRH)): ?>
                                        <?php
                                        $warehouseIndexRoute = Route::has(ViewsConstants::WRH . '.index')
                                            ? route(ViewsConstants::WRH . '.index')
                                            : '#';
                                        $linkId = 'warehouse-index-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::WRH,
                                            'warehouse_index_route_unavailable'
                                        ) ?? 'Warehouse route is unavailable. Please contact technical support or your domain administrator.';
                                        ?>
                                        <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::WRH . '.index' || RF::route()->getName() == ViewsConstants::WRH . '.show' ? 'active' : ''); ?>">
                                            <a
                                                id="<?php echo e($linkId); ?>"
                                                class="dash-link"
                                                href="<?php echo e($warehouseIndexRoute); ?>"
                                                data-url="<?php echo e($warehouseIndexRoute); ?>"
                                                data-sv-localized="true"
                                                data-guard-msg="<?php echo e($message); ?>">
                                                <?php echo e(__('Warehouse')); ?>

                                            </a>
                                        </li>
                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (() => {
                                                const listenerAttr = 'data-warehouse-index-listener-active';
                                                const el = document.getElementById('<?php echo e($linkId); ?>');
                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                el.setAttribute(listenerAttr, 'true');
                                                el.addEventListener('click', event => {
                                                    try {
                                                        const url = el.getAttribute('data-url');
                                                        const href = el.href;
                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                            event.preventDefault();
                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = 'toast-container';
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bootstrapLink && window.bootstrap) {
                                                                const toastEl = document.createElement('div');
                                                                toastEl.className = 'toast';
                                                                toastEl.setAttribute('role', 'alert');
                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            el.setAttribute('data-failed-route', 'true');
                                                        }
                                                    } catch (error) {}
                                                });
                                                const observer = new MutationObserver(() => {
                                                    if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                });
                                                observer.observe(document.body, {
                                                    childList: true,
                                                    subtree: true
                                                });
                                            })();
                                        </script>
                                        <?php $__env->stopPush(); ?>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_PRC)): ?>
                                        <?php
                                        $purchaseIndexRoute = Route::has(ViewsConstants::PRC . '.index')
                                            ? route(ViewsConstants::PRC . '.index')
                                            : '#';
                                        $linkId = 'purchase-index-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::PRC,
                                            'purchase_index_route_unavailable'
                                        ) ?? 'Purchase route is unavailable. Please contact technical support or your domain administrator.';
                                        ?>
                                        <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::PRC . '.index' || RF::route()->getName() == ViewsConstants::PRC . '.create' || RF::route()->getName() == ViewsConstants::PRC . '.edit' || RF::route()->getName() == ViewsConstants::PRC . '.show' ? 'active' : ''); ?>">
                                            <a
                                                id="<?php echo e($linkId); ?>"
                                                class="dash-link"
                                                href="<?php echo e($purchaseIndexRoute); ?>"
                                                data-url="<?php echo e($purchaseIndexRoute); ?>"
                                                data-sv-localized="true"
                                                data-guard-msg="<?php echo e($message); ?>">
                                                <?php echo e(__('Purchase')); ?>

                                            </a>
                                        </li>
                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (() => {
                                                const listenerAttr = 'data-purchase-index-listener-active';
                                                const el = document.getElementById('<?php echo e($linkId); ?>');
                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                el.setAttribute(listenerAttr, 'true');
                                                el.addEventListener('click', event => {
                                                    try {
                                                        const url = el.getAttribute('data-url');
                                                        const href = el.href;
                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                            event.preventDefault();
                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = 'toast-container';
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bootstrapLink && window.bootstrap) {
                                                                const toastEl = document.createElement('div');
                                                                toastEl.className = 'toast';
                                                                toastEl.setAttribute('role', 'alert');
                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            el.setAttribute('data-failed-route', 'true');
                                                        }
                                                    } catch (error) {}
                                                });
                                                const observer = new MutationObserver(() => {
                                                    if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                });
                                                observer.observe(document.body, {
                                                    childList: true,
                                                    subtree: true
                                                });
                                            })();
                                        </script>
                                        <?php $__env->stopPush(); ?>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_POS)): ?>
                                        <?php
                                        $posAddRoute = Route::has(ViewsConstants::POS . '.index')
                                            ? route(ViewsConstants::POS . '.index')
                                            : '#';
                                        $posReportRoute = Route::has(ViewsConstants::POS . '.report')
                                            ? route(ViewsConstants::POS . '.report')
                                            : '#';
                                        $posAddId = 'pos-add-index-link';
                                        $posReportId = 'pos-report-index-link';
                                        $posAddMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::POS,
                                            'pos_index_route_unavailable'
                                        ) ?? __('POS Setup route is unavailable. Please contact technical support or your domain administrator.');
                                        $posReportMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::POS,
                                            'pos_report_route_unavailable'
                                        ) ?? __('POS Report route is unavailable. Please contact technical support or your domain administrator.');
                                        ?>
                                        <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::POS . '.index' ? 'active' : ''); ?>">
                                            <a
                                                id="<?php echo e($posAddId); ?>"
                                                class="dash-link"
                                                href="<?php echo e($posAddRoute); ?>"
                                                data-url="<?php echo e($posAddRoute); ?>"
                                                data-sv-localized="true"
                                                data-guard-msg="<?php echo e($posAddMsg); ?>">
                                                <?php echo e(__(' Add POS')); ?>

                                            </a>
                                        </li>
                                        <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::POS . '.report' || RF::route()->getName() == ViewsConstants::POS . '.show' ? 'active' : ''); ?>">
                                            <a
                                                id="<?php echo e($posReportId); ?>"
                                                class="dash-link"
                                                href="<?php echo e($posReportRoute); ?>"
                                                data-url="<?php echo e($posReportRoute); ?>"
                                                data-sv-localized="true"
                                                data-guard-msg="<?php echo e($posReportMsg); ?>">
                                                <?php echo e(__('POS')); ?>

                                            </a>
                                        </li>
                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (() => {
                                                const bindGuard = id => {
                                                    const listenerAttr = `data-${id}-listener-active`;
                                                    const el = document.getElementById(id);
                                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                    el.setAttribute(listenerAttr, 'true');
                                                    el.addEventListener('click', event => {
                                                        try {
                                                            const url = el.getAttribute('data-url');
                                                            const href = el.href;
                                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                                event.preventDefault();
                                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                let container = document.getElementById('toast-container');
                                                                if (!container) {
                                                                    container = document.createElement('div');
                                                                    container.id = 'toast-container';
                                                                    document.body.appendChild(container);
                                                                }
                                                                if (bootstrapLink && window.bootstrap) {
                                                                    const toastEl = document.createElement('div');
                                                                    toastEl.className = 'toast';
                                                                    toastEl.setAttribute('role', 'alert');
                                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                                    const body = document.createElement('div');
                                                                    body.className = 'toast-body';
                                                                    body.textContent = msg;
                                                                    toastEl.appendChild(body);
                                                                    container.appendChild(toastEl);
                                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                                } else {
                                                                    alert(msg);
                                                                }
                                                                el.setAttribute('data-failed-route', 'true');
                                                            }
                                                        } catch (error) {}
                                                    });
                                                    const observer = new MutationObserver(() => {
                                                        if (!document.getElementById(id)) observer.disconnect();
                                                    });
                                                    observer.observe(document.body, {
                                                        childList: true,
                                                        subtree: true
                                                    });
                                                };
                                                ['<?php echo e($posAddId); ?>', '<?php echo e($posReportId); ?>'].forEach(bindGuard);
                                            })();
                                        </script>
                                        <?php $__env->stopPush(); ?>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_WRH)): ?>
                                        <?php
                                        $warehouseTransferRoute = Route::has(ViewsConstants::WRH_TRF . '.index')
                                            ? route(ViewsConstants::WRH_TRF . '.index')
                                            : (Route::has(Str::kebab(ViewsConstants::WRH_TRF . '.index'))
                                                ? route(Str::kebab(ViewsConstants::WRH_TRF . '.index'))
                                                : '#');
                                        $linkId = 'warehouse-transfer-index-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::WRH_TRF,
                                            'wrh_trf_index_route_unavailable'
                                        ) ?? 'Warehouse Transfer route is unavailable. Please contact technical support or your domain administrator.';
                                        ?>
                                        <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::WRH_TRF . '.index' || RF::route()->getName() == ViewsConstants::WRH_TRF . '.show' ? 'active' : ''); ?>">
                                            <a
                                                id="<?php echo e($linkId); ?>"
                                                class="dash-link"
                                                href="<?php echo e($warehouseTransferRoute); ?>"
                                                data-url="<?php echo e($warehouseTransferRoute); ?>"
                                                data-sv-localized="true"
                                                data-guard-msg="<?php echo e($message); ?>">
                                                <?php echo e(__('Transfer')); ?>

                                            </a>
                                        </li>
                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (() => {
                                                const listenerAttr = 'data-warehouse-transfer-listener-active';
                                                const el = document.getElementById('<?php echo e($linkId); ?>');
                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                el.setAttribute(listenerAttr, 'true');
                                                el.addEventListener('click', event => {
                                                    try {
                                                        const url = el.getAttribute('data-url');
                                                        const href = el.href;
                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                            event.preventDefault();
                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = 'toast-container';
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bootstrapLink && window.bootstrap) {
                                                                const toastEl = document.createElement('div');
                                                                toastEl.className = 'toast';
                                                                toastEl.setAttribute('role', 'alert');
                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            el.setAttribute('data-failed-route', 'true');
                                                        }
                                                    } catch (error) {}
                                                });
                                                const observer = new MutationObserver(() => {
                                                    if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                });
                                                observer.observe(document.body, {
                                                    childList: true,
                                                    subtree: true
                                                });
                                            })();
                                        </script>
                                        <?php $__env->stopPush(); ?>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::CR_BC)): ?>
                                        <?php
                                        $posBarcodeRoute = Route::has(ViewsConstants::POS . '.barcode')
                                            ? route(ViewsConstants::POS . '.barcode')
                                            : '#';
                                        $linkId = 'pos-barcode-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::POS,
                                            'pos_barcode_route_unavailable'
                                        ) ?? __('POS Barcode route is unavailable. Please contact technical support or your domain administrator.');
                                        ?>
                                        <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::POS . '.barcode' || RF::route()->getName() == ViewsConstants::POS . '.print' ? 'active' : ''); ?>">
                                            <a
                                                id="<?php echo e($linkId); ?>"
                                                class="dash-link"
                                                href="<?php echo e($posBarcodeRoute); ?>"
                                                data-url="<?php echo e($posBarcodeRoute); ?>"
                                                data-sv-localized="true"
                                                data-guard-msg="<?php echo e($message); ?>">
                                                <?php echo e(__('Print Barcode')); ?>

                                            </a>
                                        </li>
                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (() => {
                                                const listenerAttr = 'data-pos-barcode-listener-active';
                                                const el = document.getElementById('<?php echo e($linkId); ?>');
                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                el.setAttribute(listenerAttr, 'true');
                                                el.addEventListener('click', event => {
                                                    try {
                                                        const url = el.getAttribute('data-url');
                                                        const href = el.href;
                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                            event.preventDefault();
                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = 'toast-container';
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bootstrapLink && window.bootstrap) {
                                                                const toastEl = document.createElement('div');
                                                                toastEl.className = 'toast';
                                                                toastEl.setAttribute('role', 'alert');
                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            el.setAttribute('data-failed-route', 'true');
                                                        }
                                                    } catch (error) {}
                                                });
                                                const observer = new MutationObserver(() => {
                                                    if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                });
                                                observer.observe(document.body, {
                                                    childList: true,
                                                    subtree: true
                                                });
                                            })();
                                        </script>
                                        <?php $__env->stopPush(); ?>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_POS)): ?>
                                        <?php
                                        $printSettingRoute = Route::has(ViewsConstants::POS . '.print.setting')
                                            ? route(ViewsConstants::POS . '.print.setting')
                                            : (Route::has(Str::kebab(ViewsConstants::POS . '.print.setting'))
                                                ? route(Str::kebab(ViewsConstants::POS . '.print.setting'))
                                                : '#');
                                        $linkId = 'pos-print-setting-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::POS,
                                            'pos_print_setting_route_unavailable'
                                        ) ?? 'POS Print Settings route is unavailable. Please contact technical support or your domain administrator.';
                                        ?>
                                        <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::POS . '.print.setting' ? 'active' : ''); ?>">
                                            <a
                                                id="<?php echo e($linkId); ?>"
                                                class="dash-link"
                                                href="<?php echo e($printSettingRoute); ?>"
                                                data-url="<?php echo e($printSettingRoute); ?>"
                                                data-sv-localized="true"
                                                data-guard-msg="<?php echo e($message); ?>">
                                                <?php echo e(__('Print Settings')); ?>

                                            </a>
                                        </li>
                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (() => {
                                                const listenerAttr = 'data-pos-print-setting-listener-active';
                                                const el = document.getElementById('<?php echo e($linkId); ?>');
                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                el.setAttribute(listenerAttr, 'true');
                                                el.addEventListener('click', event => {
                                                    try {
                                                        const url = el.getAttribute('data-url');
                                                        const href = el.href;
                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                            event.preventDefault();
                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = 'toast-container';
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bootstrapLink && window.bootstrap) {
                                                                const toastEl = document.createElement('div');
                                                                toastEl.className = 'toast';
                                                                toastEl.setAttribute('role', 'alert');
                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            el.setAttribute('data-failed-route', 'true');
                                                        }
                                                    } catch (error) {}
                                                });
                                                const observer = new MutationObserver(() => {
                                                    if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                });
                                                observer.observe(document.body, {
                                                    childList: true,
                                                    subtree: true
                                                });
                                            })();
                                        </script>
                                        <?php $__env->stopPush(); ?>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($user[UsersConstants::COL_TP] != PermissionsConstants::ADM): ?>
                        <?php
                        $supportRoute = Route::has(ViewsConstants::SPT . '.index')
                            ? route(ViewsConstants::SPT . '.index')
                            : '#';
                        $supportId = 'support-system-link';
                        $supportMsg = Utility::fetchLinkMessage(
                            $lang,
                            ViewsConstants::SPT,
                            'support_system_index_route_unavailable'
                        ) ?? 'Support System route is unavailable. Please contact technical support or your domain administrator.';
                        $zoomRoute = Route::has(ViewsConstants::ZMM . '.index')
                            ? route(ViewsConstants::ZMM . '.index')
                            : (Route::has(Str::kebab(ViewsConstants::ZMM . '.index'))
                                ? route(Str::kebab(ViewsConstants::ZMM . '.index'))
                                : '#');
                        $zoomId = 'zoom-meeting-link';
                        $zoomMsg = Utility::fetchLinkMessage(
                            $lang,
                            ViewsConstants::ZMM,
                            'zoom_meeting_index_route_unavailable'
                        ) ?? 'Zoom Meeting route is unavailable. Please contact technical support or your domain administrator.';
                        $messengerRoute = url('chats');
                        $messengerId = 'messenger-link';
                        $messengerMsg = Utility::fetchLinkMessage(
                            $lang,
                            'chats',
                            'messenger_index_route_unavailable'
                        ) ?? 'Messenger route is unavailable. Please contact technical support or your domain administrator.';
                        ?>
                        <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e(RF::segment(1) == ViewsConstants::SPT ? 'active' : ''); ?>">
                            <a
                                id="<?php echo e($supportId); ?>"
                                class="dash-link"
                                href="<?php echo e($supportRoute); ?>"
                                data-url="<?php echo e($supportRoute); ?>"
                                data-sv-localized="true"
                                data-guard-msg="<?php echo e($supportMsg); ?>">
                                <span class="dash-micon"><i class="ti ti-headphones"></i></span>
                                <span class="dash-mtext"><?php echo e(__('Support System')); ?></span>
                            </a>
                        </li>
                        <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e(RF::segment(1) == ViewsConstants::ZMM || RF::segment(1) == 'zoom-meeting' || RF::segment(1) == 'zoom_meeting_calendar' || RF::segment(1) == 'zoom-meeting-calendar' ? 'active' : ''); ?>">
                            <a
                                id="<?php echo e($zoomId); ?>"
                                class="dash-link"
                                href="<?php echo e($zoomRoute); ?>"
                                data-url="<?php echo e($zoomRoute); ?>"
                                data-sv-localized="true"
                                data-guard-msg="<?php echo e($zoomMsg); ?>">
                                <span class="dash-micon"><i class="ti ti-user-check"></i></span>
                                <span class="dash-mtext"><?php echo e(__('Zoom Meeting')); ?></span>
                            </a>
                        </li>
                        <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e(RF::segment(1) == 'chats' ? 'active' : ''); ?>">
                            <a
                                id="<?php echo e($messengerId); ?>"
                                class="dash-link"
                                href="<?php echo e($messengerRoute); ?>"
                                data-url="<?php echo e($messengerRoute); ?>"
                                data-sv-localized="true"
                                data-guard-msg="<?php echo e($messengerMsg); ?>">
                                <span class="dash-micon"><i class="ti ti-message-circle"></i></span>
                                <span class="dash-mtext"><?php echo e(__('Messenger')); ?></span>
                            </a>
                        </li>
                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                        <script defer>
                            (() => {
                                const bindGuard = id => {
                                    const listenerAttr = `data-${id}-listener-active`;
                                    const el = document.getElementById(id);
                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                    el.setAttribute(listenerAttr, 'true');
                                    el.addEventListener('click', event => {
                                        try {
                                            const url = el.getAttribute('data-url');
                                            const href = el.href;
                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                event.preventDefault();
                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                let container = document.getElementById('toast-container');
                                                if (!container) {
                                                    container = document.createElement('div');
                                                    container.id = 'toast-container';
                                                    document.body.appendChild(container);
                                                }
                                                if (bootstrapLink && window.bootstrap) {
                                                    const toastEl = document.createElement('div');
                                                    toastEl.className = 'toast';
                                                    toastEl.setAttribute('role', 'alert');
                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                    const body = document.createElement('div');
                                                    body.className = 'toast-body';
                                                    body.textContent = msg;
                                                    toastEl.appendChild(body);
                                                    container.appendChild(toastEl);
                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                } else {
                                                    alert(msg);
                                                }
                                                el.setAttribute('data-failed-route', 'true');
                                            }
                                        } catch (error) {}
                                    });
                                    const observer = new MutationObserver(() => {
                                        if (!document.getElementById(id)) observer.disconnect();
                                    });
                                    observer.observe(document.body, {
                                        childList: true,
                                        subtree: true
                                    });
                                };
                                ['<?php echo e($supportId); ?>', '<?php echo e($zoomId); ?>', '<?php echo e($messengerId); ?>'].forEach(bindGuard);
                            })();
                        </script>
                        <?php $__env->stopPush(); ?>
                    <?php endif; ?>
                    <?php if (
                        $user[UsersConstants::COL_TP] == PermissionsConstants::CPN ||
                        $user[UsersConstants::COL_TP] == PermissionsConstants::SA
                    ): ?>
                        <?php
                        $notifTmpRoute = Route::has(ViewsConstants::NTF_TMP . '.index')
                            ? route(ViewsConstants::NTF_TMP . '.index')
                            : (Route::has(Str::kebab(ViewsConstants::NTF_TMP . '.index'))
                                ? route(Str::kebab(ViewsConstants::NTF_TMP . '.index'))
                                : '#');
                        $linkId = 'notification-template-index-link';
                        $message = Utility::fetchLinkMessage(
                            $lang,
                            ViewsConstants::NTF_TMP,
                            'notification_template_index_route_unavailable'
                        ) ?? 'Notification Template route is unavailable. Please contact technical support or your domain administrator.';
                        ?>
                        <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e((RF::segment(1) === 'notification-templates' || RF::segment(1) === ViewsConstants::NTF_TMP) ? 'active' : ''); ?>">
                            <a
                                id="<?php echo e($linkId); ?>"
                                class="dash-link"
                                href="<?php echo e($notifTmpRoute); ?>"
                                data-url="<?php echo e($notifTmpRoute); ?>"
                                data-sv-localized="true"
                                data-guard-msg="<?php echo e($message); ?>">
                                <span class="dash-micon"><i class="ti ti-notification"></i></span>
                                <span class="dash-mtext"><?php echo e(__('Notification Template')); ?></span>
                            </a>
                        </li>
                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                        <script defer>
                            (() => {
                                const listenerAttr = 'data-notification-template-listener-active';
                                const el = document.getElementById('<?php echo e($linkId); ?>');
                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                el.setAttribute(listenerAttr, 'true');
                                el.addEventListener('click', event => {
                                    try {
                                        const url = el.getAttribute('data-url');
                                        const href = el.href;
                                        if ((!url || url === '#') && (!href || href === '#')) {
                                            event.preventDefault();
                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                            const containerId = 'toast-container';
                                            let container = document.getElementById(containerId);
                                            if (!container) {
                                                container = document.createElement('div');
                                                container.id = containerId;
                                                document.body.appendChild(container);
                                            }
                                            if (bootstrapLink && window.bootstrap) {
                                                const toastEl = document.createElement('div');
                                                toastEl.className = 'toast';
                                                toastEl.setAttribute('role', 'alert');
                                                toastEl.setAttribute('aria-live', 'assertive');
                                                toastEl.setAttribute('aria-atomic', 'true');
                                                const body = document.createElement('div');
                                                body.className = 'toast-body';
                                                body.textContent = msg;
                                                toastEl.appendChild(body);
                                                container.appendChild(toastEl);
                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                            } else {
                                                alert(msg);
                                            }
                                            el.setAttribute('data-failed-route', 'true');
                                        }
                                    } catch (error) {}
                                });
                                const observer = new MutationObserver(() => {
                                    if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                });
                                observer.observe(document.body, {
                                    childList: true,
                                    subtree: true
                                });
                            })();
                        </script>
                        <?php $__env->stopPush(); ?>
                    <?php endif; ?>

                    <?php if ($user[UsersConstants::COL_TP] != PermissionsConstants::ADM): ?>
                        <?php if (Gate::check(PermissionsConstants::MNG_CP_PL) || Gate::check(PermissionsConstants::MNG_OD) || Gate::check(PermissionsConstants::MNG_CPN_SET)): ?>
                            <li
                                class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e(RF::segment(1) == ViewsConstants::SET ||
                                                                            RF::segment(1) == ViewsConstants::PLN ||
                                                                            RF::segment(1) == 'stripe' ||
                                                                            RF::segment(1) == ViewsConstants::OD
                                                                            ? ' active dash-trigger'
                                                                            : ''); ?>">
                                <a href="#!" class="dash-link">
                                    <span class="dash-micon"><i class="ti ti-settings"></i></span>
                                    <span class="dash-mtext"><?php echo e(__('Settings')); ?></span>
                                    <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                </a>
                                <ul class="dash-submenu">
                                    <?php if (Gate::check(PermissionsConstants::MNG_CPN_SET)): ?>
                                        <?php
                                        $systemSettingsRoute = Route::has(ViewsConstants::SET)
                                            ? route(ViewsConstants::SET)
                                            : '#';
                                        $linkId = 'system-settings-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::SET,
                                            'system_settings_route_unavailable'
                                        ) ?? 'System Settings route is unavailable. Please contact technical support or your domain administrator.';
                                        ?>
                                        <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e(RF::segment(1) == ViewsConstants::SET ? 'active' : ''); ?>">
                                            <a
                                                id="<?php echo e($linkId); ?>"
                                                class="dash-link"
                                                href="<?php echo e($systemSettingsRoute); ?>"
                                                data-url="<?php echo e($systemSettingsRoute); ?>"
                                                data-sv-localized="true"
                                                data-guard-msg="<?php echo e($message); ?>">
                                                <?php echo e(__('System Settings')); ?>

                                            </a>
                                        </li>
                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (() => {
                                                const listenerAttr = 'data-system-settings-listener-active';
                                                const el = document.getElementById('<?php echo e($linkId); ?>');
                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                el.setAttribute(listenerAttr, 'true');
                                                el.addEventListener('click', event => {
                                                    try {
                                                        const url = el.getAttribute('data-url');
                                                        const href = el.href;
                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                            event.preventDefault();
                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = 'toast-container';
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bootstrapLink && window.bootstrap) {
                                                                const toastEl = document.createElement('div');
                                                                toastEl.className = 'toast';
                                                                toastEl.setAttribute('role', 'alert');
                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            el.setAttribute('data-failed-route', 'true');
                                                        }
                                                    } catch (error) {}
                                                });
                                                const observer = new MutationObserver(() => {
                                                    if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                });
                                                observer.observe(document.body, {
                                                    childList: true,
                                                    subtree: true
                                                });
                                            })();
                                        </script>
                                        <?php $__env->stopPush(); ?>
                                    <?php endif; ?>
                                    <?php if (Gate::check(PermissionsConstants::MNG_CP_PL)): ?>
                                        <?php
                                        $setupSubscriptionPlanRoute = Route::has(ViewsConstants::PLN . '.index')
                                            ? route(ViewsConstants::PLN . '.index')
                                            : '#';
                                        $linkId = 'setup-subscription-plan-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::PLN,
                                            'plan_index_route_unavailable'
                                        ) ?? 'Setup Subscription Plan route is unavailable. Please contact technical support or your domain administrator.';
                                        ?>
                                        <li class="dash-item<?php echo e(RF::route()->getName() == ViewsConstants::PLN . '.index' || RF::route()->getName() == 'stripe' ? ' active' : ''); ?>">
                                            <a
                                                id="<?php echo e($linkId); ?>"
                                                class="dash-link"
                                                href="<?php echo e($setupSubscriptionPlanRoute); ?>"
                                                data-url="<?php echo e($setupSubscriptionPlanRoute); ?>"
                                                data-sv-localized="true"
                                                data-guard-msg="<?php echo e($message); ?>">
                                                <?php echo e(__('Setup Subscription Plan')); ?>

                                            </a>
                                        </li>
                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (() => {
                                                const listenerAttr = 'data-setup-subscription-plan-listener-active';
                                                const el = document.getElementById('<?php echo e($linkId); ?>');
                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                el.setAttribute(listenerAttr, 'true');
                                                el.addEventListener('click', event => {
                                                    try {
                                                        const url = el.getAttribute('data-url');
                                                        const href = el.href;
                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                            event.preventDefault();
                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = 'toast-container';
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bootstrapLink && window.bootstrap) {
                                                                const toastEl = document.createElement('div');
                                                                toastEl.className = 'toast';
                                                                toastEl.setAttribute('role', 'alert');
                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            el.setAttribute('data-failed-route', 'true');
                                                        }
                                                    } catch (error) {}
                                                });
                                                const observer = new MutationObserver(() => {
                                                    if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                });
                                                observer.observe(document.body, {
                                                    childList: true,
                                                    subtree: true
                                                });
                                            })();
                                        </script>
                                        <?php $__env->stopPush(); ?>
                                    <?php endif; ?>
                                    <?php if (Gate::check(PermissionsConstants::MNG_OD) && ($user[UsersConstants::COL_TP] == PermissionsConstants::CPN || $user[UsersConstants::COL_TP] == PermissionsConstants::SA)): ?>
                                        <?php
                                        $orderRoute = Route::has(ViewsConstants::OD . '.index')
                                            ? route(ViewsConstants::OD . '.index')
                                            : '#';
                                        $linkId = 'order-index-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            ViewsConstants::OD,
                                            'order_index_route_unavailable'
                                        ) ?? 'Order route is unavailable. Please contact technical support or your domain administrator.';
                                        ?>
                                        <li class="dash-item <?php echo e(RF::segment(1) == ViewsConstants::OD ? 'active' : ''); ?>">
                                            <a
                                                id="<?php echo e($linkId); ?>"
                                                class="dash-link"
                                                href="<?php echo e($orderRoute); ?>"
                                                data-url="<?php echo e($orderRoute); ?>"
                                                data-sv-localized="true"
                                                data-guard-msg="<?php echo e($message); ?>">
                                                <?php echo e(__('Order')); ?>

                                            </a>
                                        </li>
                                        <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                        <script defer>
                                            (() => {
                                                const listenerAttr = 'data-order-listener-active';
                                                const el = document.getElementById('<?php echo e($linkId); ?>');
                                                if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                el.setAttribute(listenerAttr, 'true');
                                                el.addEventListener('click', event => {
                                                    try {
                                                        const url = el.getAttribute('data-url');
                                                        const href = el.href;
                                                        if ((!url || url === '#') && (!href || href === '#')) {
                                                            event.preventDefault();
                                                            const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                            const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = 'toast-container';
                                                                document.body.appendChild(container);
                                                            }
                                                            if (bootstrapLink && window.bootstrap) {
                                                                const toastEl = document.createElement('div');
                                                                toastEl.className = 'toast';
                                                                toastEl.setAttribute('role', 'alert');
                                                                toastEl.setAttribute('aria-live', 'assertive');
                                                                toastEl.setAttribute('aria-atomic', 'true');
                                                                const body = document.createElement('div');
                                                                body.className = 'toast-body';
                                                                body.textContent = msg;
                                                                toastEl.appendChild(body);
                                                                container.appendChild(toastEl);
                                                                bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            el.setAttribute('data-failed-route', 'true');
                                                        }
                                                    } catch {}
                                                });
                                                const observer = new MutationObserver(() => {
                                                    if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                                });
                                                observer.observe(document.body, {
                                                    childList: true,
                                                    subtree: true
                                                });
                                            })();
                                        </script>
                                        <?php $__env->stopPush(); ?>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (
                        $user[UsersConstants::COL_TP] === PermissionsConstants::CL ||
                        $user[UsersConstants::COL_TP] === PermissionsConstants::SA
                    ): ?>
                        <ul class="dash-navbar">
                            <?php if (Gate::check(PermissionsConstants::MNG_CLT_DSB)): ?>
                                <?php
                                $dashboardRoute = Route::has(ViewsConstants::CLT . '.dashboard.view')
                                    ? route(ViewsConstants::CLT . '.dashboard.view')
                                    : '#';
                                $linkId = 'dashboard-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::CLT,
                                    'client_dashboard_view_route_unavailable'
                                ) ?? 'Client Dashboard route is unavailable. Please contact technical support or your domain administrator.';
                                ?>
                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e(RF::segment(1) == 'dashboard' ? ' active' : ''); ?>">
                                    <a
                                        id="<?php echo e($linkId); ?>"
                                        class="dash-link"
                                        href="<?php echo e($dashboardRoute); ?>"
                                        data-url="<?php echo e($dashboardRoute); ?>"
                                        data-sv-localized="true"
                                        data-guard-msg="<?php echo e($message); ?>">
                                        <span class="dash-micon"><i class="ti ti-home"></i></span>
                                        <span class="dash-mtext"><?php echo e(__('Dashboard')); ?></span>
                                    </a>
                                </li>
                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                <script defer>
                                    (() => {
                                        const listenerAttr = 'data-dashboard-listener-active';
                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                        el.setAttribute(listenerAttr, 'true');
                                        el.addEventListener('click', event => {
                                            try {
                                                const url = el.getAttribute('data-url');
                                                const href = el.href;
                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                    event.preventDefault();
                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                    const containerId = 'toast-container';
                                                    let container = document.getElementById(containerId);
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = containerId;
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bootstrapLink && window.bootstrap) {
                                                        const toastEl = document.createElement('div');
                                                        toastEl.className = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    el.setAttribute('data-failed-route', 'true');
                                                }
                                            } catch (error) {}
                                        });
                                        const observer = new MutationObserver(() => {
                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                        });
                                        observer.observe(document.body, {
                                            childList: true,
                                            subtree: true
                                        });
                                    })();
                                </script>
                                <?php $__env->stopPush(); ?>
                            <?php endif; ?>
                            <?php if (Gate::check(PermissionsConstants::MNG_DL)): ?>
                                <?php
                                $dealsRoute = Route::has(ViewsConstants::DL . '.index')
                                    ? route(ViewsConstants::DL . '.index')
                                    : '#';
                                $linkId = 'deals-index-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::DL,
                                    'dl_index_route_unavailable'
                                ) ?? __('Deals route is unavailable. Please contact technical support or your domain administrator.');
                                ?>
                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e(RF::segment(1) == ViewsConstants::DL ? 'active' : ''); ?>">
                                    <a
                                        id="<?php echo e($linkId); ?>"
                                        class="dash-link"
                                        href="<?php echo e($dealsRoute); ?>"
                                        data-url="<?php echo e($dealsRoute); ?>"
                                        data-sv-localized="true"
                                        data-guard-msg="<?php echo e($message); ?>">
                                        <span class="dash-micon"><i class="ti ti-rocket"></i></span>
                                        <span class="dash-mtext"><?php echo e(__('Deals')); ?></span>
                                    </a>
                                </li>
                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                <script defer>
                                    (() => {
                                        const listenerAttr = 'data-deals-index-listener-active';
                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                        el.setAttribute(listenerAttr, 'true');
                                        el.addEventListener('click', event => {
                                            try {
                                                const url = el.getAttribute('data-url');
                                                const href = el.href;
                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                    event.preventDefault();
                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bootstrapLink && window.bootstrap) {
                                                        const toastEl = document.createElement('div');
                                                        toastEl.className = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    el.setAttribute('data-failed-route', 'true');
                                                }
                                            } catch (error) {}
                                        });
                                        const observer = new MutationObserver(() => {
                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                        });
                                        observer.observe(document.body, {
                                            childList: true,
                                            subtree: true
                                        });
                                    })();
                                </script>
                                <?php $__env->stopPush(); ?>
                            <?php endif; ?>
                            <?php if (Gate::check(PermissionsConstants::MNG_CTC)): ?>
                                <?php
                                $contractsRoute = Route::has(ViewsConstants::CTC . '.index')
                                    ? route(ViewsConstants::CTC . '.index')
                                    : '#';
                                $linkId = 'contracts-index-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::CTC,
                                    'contract_index_route_unavailable'
                                ) ?? 'Contracts route is unavailable. Please contact technical support or your domain administrator.';
                                ?>
                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e(RF::route()->getName() == ViewsConstants::CTC . '.index' || RF::route()->getName() == ViewsConstants::CTC . '.show' ? 'active' : ''); ?>">
                                    <a
                                        id="<?php echo e($linkId); ?>"
                                        class="dash-link"
                                        href="<?php echo e($contractsRoute); ?>"
                                        data-url="<?php echo e($contractsRoute); ?>"
                                        data-sv-localized="true"
                                        data-guard-msg="<?php echo e($message); ?>">
                                        <span class="dash-micon"><i class="ti ti-rocket"></i></span>
                                        <span class="dash-mtext"><?php echo e(__('Contracts')); ?></span>
                                    </a>
                                </li>
                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                <script defer>
                                    (() => {
                                        const listenerAttr = 'data-contracts-listener-active';
                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                        el.setAttribute(listenerAttr, 'true');
                                        el.addEventListener('click', event => {
                                            try {
                                                const url = el.getAttribute('data-url');
                                                const href = el.href;
                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                    event.preventDefault();
                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bootstrapLink && window.bootstrap) {
                                                        const toastEl = document.createElement('div');
                                                        toastEl.className = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    el.setAttribute('data-failed-route', 'true');
                                                }
                                            } catch (error) {}
                                        });
                                        const observer = new MutationObserver(() => {
                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                        });
                                        observer.observe(document.body, {
                                            childList: true,
                                            subtree: true
                                        });
                                    })();
                                </script>
                                <?php $__env->stopPush(); ?>
                            <?php endif; ?>
                            <?php if (Gate::check(PermissionsConstants::MNG_PRJ)): ?>
                                <?php
                                $projectsRoute = Route::has(ViewsConstants::PRJ . '.index')
                                    ? route(ViewsConstants::PRJ . '.index')
                                    : '#';
                                $linkId = 'projects-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::PRJ,
                                    'project_index_route_unavailable'
                                ) ?? 'Projects route is unavailable. Please contact technical support or your domain administrator.';
                                ?>
                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e(RF::segment(1) == ViewsConstants::PRJ ? 'active' : ''); ?>">
                                    <a
                                        id="<?php echo e($linkId); ?>"
                                        class="dash-link"
                                        href="<?php echo e($projectsRoute); ?>"
                                        data-url="<?php echo e($projectsRoute); ?>"
                                        data-sv-localized="true"
                                        data-guard-msg="<?php echo e($message); ?>">
                                        <span class="dash-micon"><i class="ti ti-share"></i></span>
                                        <span class="dash-mtext"><?php echo e(__('Projects')); ?></span>
                                    </a>
                                </li>
                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                <script defer>
                                    (() => {
                                        const listenerAttr = 'data-projects-listener-active';
                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                        el.setAttribute(listenerAttr, 'true');
                                        el.addEventListener('click', event => {
                                            try {
                                                const url = el.getAttribute('data-url');
                                                const href = el.href;
                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                    event.preventDefault();
                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bootstrapLink && window.bootstrap) {
                                                        const toastEl = document.createElement('div');
                                                        toastEl.className = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    el.setAttribute('data-failed-route', 'true');
                                                }
                                            } catch (error) {}
                                        });
                                        const observer = new MutationObserver(() => {
                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                        });
                                        observer.observe(document.body, {
                                            childList: true,
                                            subtree: true
                                        });
                                    })();
                                </script>
                                <?php $__env->stopPush(); ?>
                            <?php endif; ?>
                            <?php if (Gate::check(PermissionsConstants::MNG_PRJ)): ?>
                                <?php
                                $projectReportRoute = Route::has(ViewsConstants::PRJ_RPT . '.index')
                                    ? route(ViewsConstants::PRJ_RPT . '.index')
                                    : (Route::has(Str::kebab(ViewsConstants::PRJ_RPT . '.index'))
                                        ? route(Str::kebab(ViewsConstants::PRJ_RPT . '.index'))
                                        : '#');
                                $linkId = 'project-report-index-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::PRJ_RPT,
                                    'project_report_index_route_unavailable'
                                ) ?? 'Project Report route is unavailable. Please contact technical support or your domain administrator.';
                                ?>
                                <li class="dash-item <?php echo e(RF::route()->getName() == ViewsConstants::PRJ_RPT . '.index' || RF::route()->getName() == ViewsConstants::PRJ_RPT . '.show' ? 'active' : ''); ?>">
                                    <a
                                        id="<?php echo e($linkId); ?>"
                                        class="dash-link"
                                        href="<?php echo e($projectReportRoute); ?>"
                                        data-url="<?php echo e($projectReportRoute); ?>"
                                        data-sv-localized="true"
                                        data-guard-msg="<?php echo e($message); ?>">
                                        <span class="dash-micon"><i class="ti ti-chart-line"></i></span>
                                        <span class="dash-mtext"><?php echo e(__('Project Report')); ?></span>
                                    </a>
                                </li>
                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                <script defer>
                                    (() => {
                                        const listenerAttr = 'data-project-report-listener-active';
                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                        el.setAttribute(listenerAttr, 'true');
                                        el.addEventListener('click', event => {
                                            try {
                                                const url = el.getAttribute('data-url');
                                                const href = el.href;
                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                    event.preventDefault();
                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bootstrapLink && window.bootstrap) {
                                                        const toastEl = document.createElement('div');
                                                        toastEl.className = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    el.setAttribute('data-failed-route', 'true');
                                                }
                                            } catch (error) {}
                                        });
                                        const observer = new MutationObserver(() => {
                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                        });
                                        observer.observe(document.body, {
                                            childList: true,
                                            subtree: true
                                        });
                                    })();
                                </script>
                                <?php $__env->stopPush(); ?>
                            <?php endif; ?>
                            <?php if (Gate::check(PermissionsConstants::MNG_PRJ_TSK)): ?>
                                <?php
                                $tasksRoute = Route::has(ViewsConstants::TSKB . '.view')
                                    ? route(ViewsConstants::TSKB . '.view', 'list')
                                    : '#';
                                $linkId = 'tasks-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::TSK,
                                    'taskboard_view_route_unavailable'
                                ) ?? 'Tasks route is unavailable. Please contact technical support or your domain administrator.';
                                ?>
                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e(RF::segment(1) == ViewsConstants::TSKB ? 'active' : ''); ?>">
                                    <a
                                        id="<?php echo e($linkId); ?>"
                                        class="dash-link"
                                        href="<?php echo e($tasksRoute); ?>"
                                        data-url="<?php echo e($tasksRoute); ?>"
                                        data-sv-localized="true"
                                        data-guard-msg="<?php echo e($message); ?>">
                                        <span class="dash-micon"><i class="ti ti-list-check"></i></span>
                                        <span class="dash-mtext"><?php echo e(__('Tasks')); ?></span>
                                    </a>
                                </li>
                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                <script defer>
                                    (() => {
                                        const listenerAttr = 'data-tasks-listener-active';
                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                        el.setAttribute(listenerAttr, 'true');
                                        el.addEventListener('click', event => {
                                            try {
                                                const url = el.getAttribute('data-url');
                                                const href = el.href;
                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                    event.preventDefault();
                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bootstrapLink && window.bootstrap) {
                                                        const toastEl = document.createElement('div');
                                                        toastEl.className = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    el.setAttribute('data-failed-route', 'true');
                                                }
                                            } catch (error) {}
                                        });
                                        const observer = new MutationObserver(() => {
                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                        });
                                        observer.observe(document.body, {
                                            childList: true,
                                            subtree: true
                                        });
                                    })();
                                </script>
                                <?php $__env->stopPush(); ?>
                            <?php endif; ?>
                            <?php if (Gate::check(PermissionsConstants::MNG_BUG_RPT)): ?>
                                <?php
                                $bugsRoute = Route::has(ViewsConstants::BUG . '.view')
                                    ? route(ViewsConstants::BUG . '.view', 'list')
                                    : '#';
                                $linkId = 'bugs-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::BUG,
                                    'bug_view_route_unavailable'
                                ) ?? __('Bugs route is unavailable. Please contact technical support or your domain administrator.');
                                ?>
                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e((RF::segment(1) == ViewsConstants::BUG_RPT || RF::segment(1) == 'bug-reports') ? 'active' : ''); ?>">
                                    <a
                                        id="<?php echo e($linkId); ?>"
                                        class="dash-link"
                                        href="<?php echo e($bugsRoute); ?>"
                                        data-url="<?php echo e($bugsRoute); ?>"
                                        data-sv-localized="true"
                                        data-guard-msg="<?php echo e($message); ?>">
                                        <span class="dash-micon"><i class="ti ti-bug"></i></span>
                                        <span class="dash-mtext"><?php echo e(__('Bugs')); ?></span>
                                    </a>
                                </li>
                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                <script defer>
                                    (() => {
                                        const listenerAttr = 'data-bugs-listener-active';
                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                        el.setAttribute(listenerAttr, 'true');
                                        el.addEventListener('click', event => {
                                            try {
                                                const url = el.getAttribute('data-url');
                                                const href = el.href;
                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                    event.preventDefault();
                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bootstrapLink && window.bootstrap) {
                                                        const toastEl = document.createElement('div');
                                                        toastEl.className = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    el.setAttribute('data-failed-route', 'true');
                                                }
                                            } catch (error) {}
                                        });
                                        const observer = new MutationObserver(() => {
                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                        });
                                        observer.observe(document.body, {
                                            childList: true,
                                            subtree: true
                                        });
                                    })();
                                </script>
                                <?php $__env->stopPush(); ?>
                            <?php endif; ?>
                            <?php if (Gate::check(PermissionsConstants::MNG_TS)): ?>
                                <?php
                                $timesheetListRoute = Route::has(ViewsConstants::TMS . '.list')
                                    ? route(ViewsConstants::TMS . '.list')
                                    : '#';
                                $linkId = 'timesheet-list-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::TMS,
                                    'timesheet_list_route_unavailable'
                                ) ?? 'Timesheet route is unavailable. Please contact technical support or your domain administrator.';
                                ?>
                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e((RF::segment(1) == ViewsConstants::TMS_LT || RF::segment(1) == 'timesheet-lists') ? 'active' : ''); ?>">
                                    <a
                                        id="<?php echo e($linkId); ?>"
                                        class="dash-link"
                                        href="<?php echo e($timesheetListRoute); ?>"
                                        data-url="<?php echo e($timesheetListRoute); ?>"
                                        data-sv-localized="true"
                                        data-guard-msg="<?php echo e($message); ?>">
                                        <span class="dash-micon"><i class="ti ti-clock"></i></span>
                                        <span class="dash-mtext"><?php echo e(__('Timesheet')); ?></span>
                                    </a>
                                </li>
                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                <script defer>
                                    (() => {
                                        const listenerAttr = 'data-timesheet-listener-active';
                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                        el.setAttribute(listenerAttr, 'true');
                                        el.addEventListener('click', event => {
                                            try {
                                                const url = el.getAttribute('data-url');
                                                const href = el.href;
                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                    event.preventDefault();
                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bootstrapLink && window.bootstrap) {
                                                        const toastEl = document.createElement('div');
                                                        toastEl.className = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    el.setAttribute('data-failed-route', 'true');
                                                }
                                            } catch (error) {}
                                        });
                                        const observer = new MutationObserver(() => {
                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                        });
                                        observer.observe(document.body, {
                                            childList: true,
                                            subtree: true
                                        });
                                    })();
                                </script>
                                <?php $__env->stopPush(); ?>
                            <?php endif; ?>
                            <?php if (Gate::check(PermissionsConstants::MNG_PRJ_TSK)): ?>
                                <?php
                                $taskCalendarRoute = Route::has(ViewsConstants::TSK . '.calendar')
                                    ? route(ViewsConstants::TSK . '.calendar', ['all'])
                                    : '#';
                                $linkId = 'task-calendar-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::TSK,
                                    'tsk_calendar_route_unavailable'
                                ) ?? 'Task calendar route is unavailable. Please contact technical support or your domain administrator.';
                                ?>
                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e(RF::segment(1) == ViewsConstants::CLD ? 'active' : ''); ?>">
                                    <a
                                        id="<?php echo e($linkId); ?>"
                                        class="dash-link"
                                        href="<?php echo e($taskCalendarRoute); ?>"
                                        data-url="<?php echo e($taskCalendarRoute); ?>"
                                        data-sv-localized="true"
                                        data-guard-msg="<?php echo e($message); ?>">
                                        <span class="dash-micon"><i class="ti ti-calendar"></i></span>
                                        <span class="dash-mtext"><?php echo e(__('Task calendar')); ?></span>
                                    </a>
                                </li>
                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                <script defer>
                                    (() => {
                                        const listenerAttr = 'data-task-calendar-listener-active';
                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                        el.setAttribute(listenerAttr, 'true');
                                        el.addEventListener('click', event => {
                                            try {
                                                const url = el.getAttribute('data-url');
                                                const href = el.href;
                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                    event.preventDefault();
                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bootstrapLink && window.bootstrap) {
                                                        const toastEl = document.createElement('div');
                                                        toastEl.className = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    el.setAttribute('data-failed-route', 'true');
                                                }
                                            } catch (error) {}
                                        });
                                        const observer = new MutationObserver(() => {
                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                        });
                                        observer.observe(document.body, {
                                            childList: true,
                                            subtree: true
                                        });
                                    })();
                                </script>
                                <?php $__env->stopPush(); ?>
                            <?php endif; ?>
                            <?php
                            $supportRoute = Route::has(ViewsConstants::SPT . '.index')
                                ? route(ViewsConstants::SPT . '.index')
                                : '#';
                            $linkId = 'support-link';
                            $message = Utility::fetchLinkMessage(
                                $lang,
                                ViewsConstants::SPT,
                                'spt_index_route_unavailable'
                            ) ?? 'Support route is unavailable. Please contact technical support or your domain administrator.';
                            ?>
                            <li class="<?php echo e(VC::DSH_IT_MN); ?>">
                                <a
                                    id="<?php echo e($linkId); ?>"
                                    class="dash-link <?php echo e(RF::segment(1) == ViewsConstants::SPT ? 'active' : ''); ?>"
                                    href="<?php echo e($supportRoute); ?>"
                                    data-url="<?php echo e($supportRoute); ?>"
                                    data-sv-localized="true"
                                    data-guard-msg="<?php echo e($message); ?>">
                                    <span class="dash-micon"><i class="ti ti-headphones"></i></span>
                                    <span class="dash-mtext"><?php echo e(__('Support')); ?></span>
                                </a>
                            </li>
                            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                            <script defer>
                                (() => {
                                    const listenerAttr = 'data-support-listener-active';
                                    const el = document.getElementById('<?php echo e($linkId); ?>');
                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                    el.setAttribute(listenerAttr, 'true');
                                    el.addEventListener('click', event => {
                                        try {
                                            const url = el.getAttribute('data-url');
                                            const href = el.href;
                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                event.preventDefault();
                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                const containerId = 'toast-container';
                                                let container = document.getElementById(containerId);
                                                if (!container) {
                                                    container = document.createElement('div');
                                                    container.id = containerId;
                                                    document.body.appendChild(container);
                                                }
                                                if (bootstrapLink && window.bootstrap) {
                                                    const toastEl = document.createElement('div');
                                                    toastEl.className = 'toast';
                                                    toastEl.setAttribute('role', 'alert');
                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                    const body = document.createElement('div');
                                                    body.className = 'toast-body';
                                                    body.textContent = msg;
                                                    toastEl.appendChild(body);
                                                    container.appendChild(toastEl);
                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                } else {
                                                    alert(msg);
                                                }
                                                el.setAttribute('data-failed-route', 'true');
                                            }
                                        } catch (error) {}
                                    });
                                    const observer = new MutationObserver(() => {
                                        if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                    });
                                    observer.observe(document.body, {
                                        childList: true,
                                        subtree: true
                                    });
                                })();
                            </script>
                            <?php $__env->stopPush(); ?>
                        </ul>
                    <?php endif; ?>
                    <?php if ($user[UsersConstants::COL_TP] === PermissionsConstants::SA): ?>
                        <ul class="dash-navbar">
                            <?php if (Gate::check(PermissionsConstants::MNG_SA_DSB)): ?>
                                <?php
                                $dashboardRoute = Route::has(ViewsConstants::CLT . '.dashboard.view')
                                    ? route(ViewsConstants::CLT . '.dashboard.view')
                                    : '#';
                                $linkId = 'dashboard-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::CLT,
                                    'client_dashboard_view_route_unavailable'
                                ) ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';
                                ?>
                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e(RF::segment(1) == 'dashboard' ? ' active' : ''); ?>">
                                    <a
                                        id="<?php echo e($linkId); ?>"
                                        class="dash-link"
                                        href="<?php echo e($dashboardRoute); ?>"
                                        data-url="<?php echo e($dashboardRoute); ?>"
                                        data-sv-localized="true"
                                        data-guard-msg="<?php echo e($message); ?>">
                                        <span class="dash-micon"><i class="ti ti-home"></i></span>
                                        <span class="dash-mtext"><?php echo e(__('Dashboard')); ?></span>
                                    </a>
                                </li>
                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                <script defer>
                                    (() => {
                                        const listenerAttr = 'data-dashboard-listener-active';
                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                        el.setAttribute(listenerAttr, 'true');
                                        el.addEventListener('click', event => {
                                            try {
                                                const url = el.getAttribute('data-url');
                                                const href = el.href;
                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                    event.preventDefault();
                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bootstrapLink && window.bootstrap) {
                                                        const toastEl = document.createElement('div');
                                                        toastEl.className = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    el.setAttribute('data-failed-route', 'true');
                                                }
                                            } catch (error) {}
                                        });
                                        const observer = new MutationObserver(() => {
                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                        });
                                        observer.observe(document.body, {
                                            childList: true,
                                            subtree: true
                                        });
                                    })();
                                </script>
                                <?php $__env->stopPush(); ?>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check(PermissionsConstants::MNG_USER)): ?>
                                <?php
                                $userIndexRoute = Route::has(ViewsConstants::USR . '.index')
                                    ? route(ViewsConstants::USR . '.index')
                                    : '#';
                                $linkId = 'user-index-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::USR,
                                    'user_index_route_unavailable'
                                ) ?? __('User route is unavailable. Please contact technical support or your domain administrator.');
                                ?>
                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e((RF::route()->getName() == ViewsConstants::USR . '.index' || RF::route()->getName() == ViewsConstants::USR . '.create' || RF::route()->getName() == ViewsConstants::USR . '.edit') ? 'active' : ''); ?>">
                                    <a
                                        id="<?php echo e($linkId); ?>"
                                        class="dash-link"
                                        href="<?php echo e($userIndexRoute); ?>"
                                        data-url="<?php echo e($userIndexRoute); ?>"
                                        data-sv-localized="true"
                                        data-guard-msg="<?php echo e($message); ?>">
                                        <span class="dash-micon"><i class="<?php echo e(VC::TI_USRS); ?>"></i></span>
                                        <span class="dash-mtext"><?php echo e(__('User')); ?></span>
                                    </a>
                                </li>
                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                <script defer>
                                    (() => {
                                        const listenerAttr = 'data-user-index-listener-active';
                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                        el.setAttribute(listenerAttr, 'true');
                                        el.addEventListener('click', event => {
                                            try {
                                                const url = el.getAttribute('data-url');
                                                const href = el.href;
                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                    event.preventDefault();
                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                    const containerId = 'toast-container';
                                                    let container = document.getElementById(containerId);
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = containerId;
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bootstrapLink && window.bootstrap) {
                                                        const toastEl = document.createElement('div');
                                                        toastEl.className = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    el.setAttribute('data-failed-route', 'true');
                                                }
                                            } catch (error) {}
                                        });
                                        const observer = new MutationObserver(() => {
                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                        });
                                        observer.observe(document.body, {
                                            childList: true,
                                            subtree: true
                                        });
                                    })();
                                </script>
                                <?php $__env->stopPush(); ?>
                            <?php endif; ?>
                            <?php if (Gate::check(PermissionsConstants::MNG_PL)): ?>
                                <?php
                                $planRoute = Route::has(ViewsConstants::PLN . '.index')
                                    ? route(ViewsConstants::PLN . '.index')
                                    : '#';
                                $linkId = 'plan-index-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::PLN,
                                    'plan_index_route_unavailable'
                                ) ?? 'Plan route is unavailable. Please contact technical support or your domain administrator.';
                                ?>
                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e(RF::segment(1) == ViewsConstants::PLN ? 'active' : ''); ?>">
                                    <a
                                        id="<?php echo e($linkId); ?>"
                                        class="dash-link"
                                        href="<?php echo e($planRoute); ?>"
                                        data-url="<?php echo e($planRoute); ?>"
                                        data-sv-localized="true"
                                        data-guard-msg="<?php echo e($message); ?>">
                                        <span class="dash-micon"><i class="ti ti-trophy"></i></span>
                                        <span class="dash-mtext"><?php echo e(__('Plan')); ?></span>
                                    </a>
                                </li>
                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                <script defer>
                                    (() => {
                                        const listenerAttr = 'data-plan-listener-active';
                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                        el.setAttribute(listenerAttr, 'true');
                                        el.addEventListener('click', event => {
                                            try {
                                                const url = el.getAttribute('data-url');
                                                const href = el.href;
                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                    event.preventDefault();
                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                    const containerId = 'toast-container';
                                                    let container = document.getElementById(containerId);
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = containerId;
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bootstrapLink && window.bootstrap) {
                                                        const toastEl = document.createElement('div');
                                                        toastEl.className = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    el.setAttribute('data-failed-route', 'true');
                                                }
                                            } catch (error) {}
                                        });
                                        const observer = new MutationObserver(() => {
                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                        });
                                        observer.observe(document.body, {
                                            childList: true,
                                            subtree: true
                                        });
                                    })();
                                </script>
                                <?php $__env->stopPush(); ?>
                            <?php endif; ?>
                            <?php if ($user[UsersConstants::COL_TP] === PermissionsConstants::SA): ?>
                                <?php
                                $planRequestRoute = Route::has(ViewsConstants::PLN_RQ . '.index')
                                    ? route(ViewsConstants::PLN_RQ . '.index')
                                    : (Route::has(Str::kebab(ViewsConstants::PLN_RQ . '.index'))
                                        ? route(Str::kebab(ViewsConstants::PLN_RQ . '.index'))
                                        : '#');
                                $linkId = 'plan-request-index-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::PLN_RQ,
                                    'plan_request_index_route_unavailable'
                                ) ?? 'Plan Request route is unavailable. Please contact technical support or your domain administrator.';
                                ?>
                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e((request()->is('plan_request*') || request()->is('plan-request*')) ? 'active' : ''); ?>">
                                    <a
                                        id="<?php echo e($linkId); ?>"
                                        class="dash-link"
                                        href="<?php echo e($planRequestRoute); ?>"
                                        data-url="<?php echo e($planRequestRoute); ?>"
                                        data-sv-localized="true"
                                        data-guard-msg="<?php echo e($message); ?>">
                                        <span class="dash-micon"><i class="ti ti-arrow-up-right-circle"></i></span>
                                        <span class="dash-mtext"><?php echo e(__('Plan Request')); ?></span>
                                    </a>
                                </li>
                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                <script defer>
                                    (() => {
                                        const listenerAttr = 'data-plan-request-listener-active';
                                        const el = document.getElementById('plan-request-index-link');
                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                        el.setAttribute(listenerAttr, 'true');
                                        el.addEventListener('click', event => {
                                            try {
                                                const url = el.getAttribute('data-url');
                                                const href = el.href;
                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                    event.preventDefault();
                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bootstrapLink && window.bootstrap) {
                                                        const toastEl = document.createElement('div');
                                                        toastEl.className = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    el.setAttribute('data-failed-route', 'true');
                                                }
                                            } catch (error) {}
                                        });
                                        const observer = new MutationObserver(() => {
                                            if (!document.getElementById('plan-request-index-link')) observer.disconnect();
                                        });
                                        observer.observe(document.body, {
                                            childList: true,
                                            subtree: true
                                        });
                                    })();
                                </script>
                                <?php $__env->stopPush(); ?>
                            <?php endif; ?>
                            <?php if (Gate::check(PermissionsConstants::MNG_CPN)): ?>
                                <?php
                                $couponRoute = Route::has(ViewsConstants::CPN . '.index')
                                    ? route(ViewsConstants::CPN . '.index')
                                    : '#';
                                $linkId = 'coupon-index-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::CPN,
                                    'coupon_index_route_unavailable'
                                ) ?? 'Coupon route is unavailable. Please contact technical support or your domain administrator.';
                                ?>
                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e(RF::segment(1) == ViewsConstants::CPN ? 'active' : ''); ?>">
                                    <a
                                        id="<?php echo e($linkId); ?>"
                                        class="dash-link"
                                        href="<?php echo e($couponRoute); ?>"
                                        data-url="<?php echo e($couponRoute); ?>"
                                        data-sv-localized="true"
                                        data-guard-msg="<?php echo e($message); ?>">
                                        <span class="dash-micon"><i class="ti ti-gift"></i></span>
                                        <span class="dash-mtext"><?php echo e(__('Coupon')); ?></span>
                                    </a>
                                </li>
                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                <script defer>
                                    (() => {
                                        const listenerAttr = 'data-coupon-listener-active';
                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                        el.setAttribute(listenerAttr, 'true');
                                        el.addEventListener('click', event => {
                                            try {
                                                const url = el.getAttribute('data-url');
                                                const href = el.href;
                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                    event.preventDefault();
                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bootstrapLink && window.bootstrap) {
                                                        const toastEl = document.createElement('div');
                                                        toastEl.className = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    el.setAttribute('data-failed-route', 'true');
                                                }
                                            } catch (error) {}
                                        });
                                        const observer = new MutationObserver(() => {
                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                        });
                                        observer.observe(document.body, {
                                            childList: true,
                                            subtree: true
                                        });
                                    })();
                                </script>
                                <?php $__env->stopPush(); ?>
                            <?php endif; ?>
                            <?php if (Gate::check(PermissionsConstants::MNG_OD)): ?>
                                <?php
                                $orderRoute = Route::has(ViewsConstants::OD . '.index')
                                    ? route(ViewsConstants::OD . '.index')
                                    : '#';
                                $linkId = 'order-index-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::OD,
                                    'order_index_route_unavailable'
                                ) ?? 'Order route is unavailable. Please contact technical support or your domain administrator.';
                                ?>
                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e(RF::segment(1) == ViewsConstants::OD ? 'active' : ''); ?>">
                                    <a
                                        id="<?php echo e($linkId); ?>"
                                        class="dash-link"
                                        href="<?php echo e($orderRoute); ?>"
                                        data-url="<?php echo e($orderRoute); ?>"
                                        data-sv-localized="true"
                                        data-guard-msg="<?php echo e($message); ?>">
                                        <span class="dash-micon"><i class="ti ti-shopping-cart-plus"></i></span>
                                        <span class="dash-mtext"><?php echo e(__('Order')); ?></span>
                                    </a>
                                </li>
                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                <script defer>
                                    (() => {
                                        const listenerAttr = 'data-order-listener-active';
                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                        el.setAttribute(listenerAttr, 'true');
                                        el.addEventListener('click', event => {
                                            try {
                                                const url = el.getAttribute('data-url');
                                                const href = el.href;
                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                    event.preventDefault();
                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bootstrapLink && window.bootstrap) {
                                                        const toastEl = document.createElement('div');
                                                        toastEl.className = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    el.setAttribute('data-failed-route', 'true');
                                                }
                                            } catch (error) {}
                                        });
                                        const observer = new MutationObserver(() => {
                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                        });
                                        observer.observe(document.body, {
                                            childList: true,
                                            subtree: true
                                        });
                                    })();
                                </script>
                                <?php $__env->stopPush(); ?>
                            <?php endif; ?>
                            <?php
                            $emailTemplateRoute = Route::has('manage.email.language')
                                ? route('manage.email.language', [$emailTemplate->id, $user?->lang])
                                : (Route::has(Str::kebab('manage.email.language'))
                                    ? route(Str::kebab('manage.email.language'), [$emailTemplate->id, $user?->lang])
                                    : '#');
                            $linkId = 'email-template-link';
                            $message = Utility::fetchLinkMessage(
                                $lang,
                                ViewsConstants::EMLS,
                                'email_template_route_unavailable'
                            ) ?? 'Email Template route is unavailable. Please contact technical support or your domain administrator.';
                            ?>
                            <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e(RF::segment(1) == ViewsConstants::NTF_TMP ? ' active' : ''); ?>">
                                <a
                                    id="<?php echo e($linkId); ?>"
                                    class="dash-link"
                                    href="<?php echo e($emailTemplateRoute); ?>"
                                    data-url="<?php echo e($emailTemplateRoute); ?>"
                                    data-sv-localized="true"
                                    data-guard-msg="<?php echo e($message); ?>">
                                    <span class="dash-micon"><i class="ti ti-template"></i></span>
                                    <span class="dash-mtext"><?php echo e(__('Email Template')); ?></span>
                                </a>
                            </li>
                            <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                            <script defer>
                                (() => {
                                    const listenerAttr = 'data-email-template-listener-active';
                                    const el = document.getElementById('<?php echo e($linkId); ?>');
                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                    el.setAttribute(listenerAttr, 'true');
                                    el.addEventListener('click', event => {
                                        try {
                                            const url = el.getAttribute('data-url');
                                            const href = el.href;
                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                event.preventDefault();
                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                let container = document.getElementById('toast-container');
                                                if (!container) {
                                                    container = document.createElement('div');
                                                    container.id = 'toast-container';
                                                    document.body.appendChild(container);
                                                }
                                                if (bootstrapLink && window.bootstrap) {
                                                    const toastEl = document.createElement('div');
                                                    toastEl.className = 'toast';
                                                    toastEl.setAttribute('role', 'alert');
                                                    toastEl.setAttribute('aria-live', 'assertive');
                                                    toastEl.setAttribute('aria-atomic', 'true');
                                                    const body = document.createElement('div');
                                                    body.className = 'toast-body';
                                                    body.textContent = msg;
                                                    toastEl.appendChild(body);
                                                    container.appendChild(toastEl);
                                                    bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                } else {
                                                    alert(msg);
                                                }
                                                el.setAttribute('data-failed-route', 'true');
                                            }
                                        } catch (error) {}
                                    });
                                    const observer = new MutationObserver(() => {
                                        if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                    });
                                    observer.observe(document.body, {
                                        childList: true,
                                        subtree: true
                                    });
                                })();
                            </script>
                            <?php $__env->stopPush(); ?>
                            <?php if ($user[UsersConstants::COL_TP] == PermissionsConstants::SA): ?>
                                <?php echo $__env->make(R::LP . '::' . ViewsConstants::MN . '.' . R::LP, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                            <?php endif; ?>
                            <?php if (Gate::check(PermissionsConstants::MNG_SYS_ST)): ?>
                                <?php
                                $settingsRoute = Route::has(ViewsConstants::SYS . '.index')
                                    ? route(ViewsConstants::SYS . '.index')
                                    : '#';
                                $linkId = 'settings-index-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    ViewsConstants::SYS,
                                    'settings_index_route_unavailable'
                                ) ?? 'Settings route is unavailable. Please contact technical support or your domain administrator.';
                                ?>
                                <li class="<?php echo e(VC::DSH_IT_MN); ?> <?php echo e(RF::route()->getName() == ViewsConstants::SYS . '.index' ? 'active' : ''); ?>">
                                    <a
                                        id="<?php echo e($linkId); ?>"
                                        class="dash-link"
                                        href="<?php echo e($settingsRoute); ?>"
                                        data-url="<?php echo e($settingsRoute); ?>"
                                        data-sv-localized="true"
                                        data-guard-msg="<?php echo e($message); ?>">
                                        <span class="dash-micon"><i class="ti ti-settings"></i></span>
                                        <span class="dash-mtext"><?php echo e(__('Settings')); ?></span>
                                    </a>
                                </li>
                                <?php $__env->startPush(StacksConstants::ADM_SCR_PG); ?>
                                <script defer>
                                    (() => {
                                        const listenerAttr = 'data-settings-index-listener-active';
                                        const el = document.getElementById('<?php echo e($linkId); ?>');
                                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                        el.setAttribute(listenerAttr, 'true');
                                        el.addEventListener('click', event => {
                                            try {
                                                const url = el.getAttribute('data-url');
                                                const href = el.href;
                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                    event.preventDefault();
                                                    const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                    const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                    let container = document.getElementById('toast-container');
                                                    if (!container) {
                                                        container = document.createElement('div');
                                                        container.id = 'toast-container';
                                                        document.body.appendChild(container);
                                                    }
                                                    if (bootstrapLink && window.bootstrap) {
                                                        const toastEl = document.createElement('div');
                                                        toastEl.className = 'toast';
                                                        toastEl.setAttribute('role', 'alert');
                                                        toastEl.setAttribute('aria-live', 'assertive');
                                                        toastEl.setAttribute('aria-atomic', 'true');
                                                        const body = document.createElement('div');
                                                        body.className = 'toast-body';
                                                        body.textContent = msg;
                                                        toastEl.appendChild(body);
                                                        container.appendChild(toastEl);
                                                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                                    } else {
                                                        alert(msg);
                                                    }
                                                    el.setAttribute('data-failed-route', 'true');
                                                }
                                            } catch (error) {}
                                        });
                                        const observer = new MutationObserver(() => {
                                            if (!document.getElementById('<?php echo e($linkId); ?>')) observer.disconnect();
                                        });
                                        observer.observe(document.body, {
                                            childList: true,
                                            subtree: true
                                        });
                                    })();
                                </script>
                                <?php $__env->stopPush(); ?>
                            <?php endif; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
                <div class="navbar-footer border-top">
                    <div class="d-flex align-items-center py-3 px-3 border-bottom">
                        <div class="me-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="29" height="30" viewBox="0 0 29 30"
                                fill="none">
                                <circle cx="14.5" cy="15.1846" r="14.5" fill="#6FD943"></circle>
                                <path opacity="0.4"
                                    d="M22.08 8.66459C21.75 8.28459 21.4 7.92459 21.02 7.60459C19.28 6.09459 17 5.18461 14.5 5.18461C12.01 5.18461 9.73999 6.09459 7.98999 7.60459C7.60999 7.92459 7.24999 8.28459 6.92999 8.66459C5.40999 10.4146 4.5 12.6946 4.5 15.1846C4.5 17.6746 5.40999 19.9546 6.92999 21.7046C7.24999 22.0846 7.60999 22.4446 7.98999 22.7646C9.73999 24.2746 12.01 25.1846 14.5 25.1846C17 25.1846 19.28 24.2746 21.02 22.7646C21.4 22.4446 21.75 22.0846 22.08 21.7046C23.59 19.9546 24.5 17.6746 24.5 15.1846C24.5 12.6946 23.59 10.4146 22.08 8.66459ZM14.5 19.6246C13.54 19.6246 12.65 19.3146 11.93 18.7946C11.52 18.5146 11.17 18.1646 10.88 17.7546C10.37 17.0346 10.06 16.1346 10.06 15.1846C10.06 14.2346 10.37 13.3346 10.88 12.6146C11.17 12.2046 11.52 11.8546 11.93 11.5746C12.65 11.0546 13.54 10.7446 14.5 10.7446C15.46 10.7446 16.35 11.0546 17.08 11.5646C17.49 11.8546 17.84 12.2046 18.13 12.6146C18.64 13.3346 18.95 14.2346 18.95 15.1846C18.95 16.1346 18.64 17.0346 18.13 17.7546C17.84 18.1646 17.49 18.5146 17.08 18.8046C16.35 19.3146 15.46 19.6246 14.5 19.6246Z"
                                    fill="#162C4E"></path>
                                <path
                                    d="M22.08 8.66459L18.18 12.5746C18.16 12.5846 18.15 12.6046 18.13 12.6146C17.84 12.2046 17.49 11.8546 17.08 11.5646C17.09 11.5446 17.1 11.5346 17.12 11.5146L21.02 7.60459C21.4 7.92459 21.75 8.28459 22.08 8.66459Z"
                                    fill="#162C4E"></path>
                                <path
                                    d="M11.9297 18.7947C11.9197 18.8147 11.9097 18.8347 11.8897 18.8547L7.98969 22.7647C7.60969 22.4447 7.24969 22.0847 6.92969 21.7047L10.8297 17.7947C10.8397 17.7747 10.8597 17.7647 10.8797 17.7547C11.1697 18.1647 11.5197 18.5147 11.9297 18.7947Z"
                                    fill="#162C4E"></path>
                                <path
                                    d="M11.9297 11.5746C11.5197 11.8546 11.1697 12.2045 10.8797 12.6145C10.8597 12.6045 10.8497 12.5846 10.8297 12.5746L6.92969 8.66453C7.24969 8.28453 7.60969 7.92453 7.98969 7.60453L11.8897 11.5146C11.9097 11.5346 11.9197 11.5546 11.9297 11.5746Z"
                                    fill="#162C4E"></path>
                                <path
                                    d="M22.08 21.7046C21.75 22.0846 21.4 22.4446 21.02 22.7646L17.12 18.8546C17.1 18.8346 17.09 18.8246 17.08 18.8046C17.49 18.5146 17.84 18.1646 18.13 17.7546C18.15 17.7646 18.16 17.7746 18.18 17.7946L22.08 21.7046Z"
                                    fill="#162C4E"></path>
                            </svg>
                        </div>
                        <div>
                            <b class="d-block f-w-700"><?php echo e(__('You need help?')); ?></b>
                            <span><?php echo e(__('Check out our repository')); ?> </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .navbar-wrapper {
                height: 95%;
                overflow: hidden;
            }

            .navbar-wrapper li {
                list-style: none;
            }
        </style>
        </nav>
        <?php /**PATH C:\Users\Aron\Desktop\programming\Prestech\erp\erpgo-fork\erp_prestech\_inc\laravel\resources\views/partials/admin/menu.blade.php ENDPATH**/ ?>