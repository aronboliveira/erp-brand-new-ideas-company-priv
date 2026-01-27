@php
	use App\Config\Constants\{
        DatabaseConstants as DBC,
        PermissionsConstants as PMC,
        PlansConstants as PLC,
        SettingsConstants as SC,
        StacksConstants as ST,
        UsersConstants,
        ViewClassNamesConstants as VC,
        ViewsConstants as VW
    };
	use App\Http\Controllers\EmployeeAttendanceController as EAC;
	use App\Models\{Employee, Plan, User, Utility};
    use Illuminate\Http\Request;
    use Illuminate\Support\Str;
	use Illuminate\Support\Facades\{Auth,Crypt,Log,Request as RF,Route};
	use Modules\LandingPage\Config\Constants\{ExtendingLandingPageLayoutConstants as E,RoutesResourcesConstants as R};
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
	$userPlan ??= DBC::DEFAULT_PLAN;
    $routeUnavailableMessage = Utility::fetchLinkMessage($lang, 'generics', 'route_unavailable');
    $disabledRoutes = [];
	try {
		$data=Utility::prepareCommonViewData()?:[];
		$logo=Utility::getFile('uploads/logo/')?:'';
		$colorSettings=$data[SC::CLR_STG]??[];
		$company_logo=$data[SC::CPN_LG_DK]??'';
		$company_logos=$data[SC::CPN_LG_LT]??'';
		$company_small_logo=$data['company_small_logo']??'';
		$emailTemplate=\App\Models\EmailTemplate::emailTemplateData()?:[];
		$user = Auth::user();
		$lang = Utility::fetchUserLang(user:$user);
		$userPlan = $user instanceof User
				   ? Plan::getPlan($user?->showDashboard())
				   : Plan::find(DBC::DEFAULT_PLAN);
	} catch (\Error $e) {
		Log::error(
			'Error fetching attendance data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Exception $e) {
		Log::error(
			'Exception fetching attendance data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
			]
		);
	} catch (\Throwable $e) {
		Log::error(
			'Throwable fetching attendance data',
			[
				'exception_class'=>get_class($e),
				'message'=>$e->getMessage(),
				'file'=>$e->getFile(),
				'line'=>$e->getLine()
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
            'acc' => $userPlan->{PLC::COL_ACC},
            'crm' => $userPlan->{PLC::COL_CRM},
            'hrm' => $userPlan->{PLC::COL_HRM},
            'pos' => $userPlan->{PLC::COL_POS},
            'prj' => $userPlan->{PLC::COL_PJ}
        ]);
    } else Log::notice('No plan found!');
    if ($user instanceof User)
    Log::debug('User permissions names: ', $user->getAllPermissions()->pluck('name')->toArray());
    $isSa = !empty($user->{UsersConstants::COL_TP}) && $user->{UsersConstants::COL_TP} === PMC::SA;
@endphp
@if (!empty($colorSettings[SC::CST_DRK]) && $colorSettings[SC::CST_DRK] === 'on')
    <nav class="dash-sidebar light-sidebar transprent-bg">
@else
    <nav class="dash-sidebar light-sidebar">
@endif
    <div class="navbar-wrapper">
        <div class="m-header main-logo">
            <a href="#" class="b-brand">
                {{--                <img src="{{ asset(Storage::url('uploads/logo/'.$logo)) }}" alt="{{ env('APP_NAME') }}" class="{{ VC::LOGO_LG }}" /> --}}
                @if ($colorSettings[SC::CST_DRK] && $colorSettings[SC::CST_DRK] == 'on')
                    <img src="{{ (isset($company_logos) && !empty($company_logos) ? asset($company_logos) : asset(SC::CPN_LG_DK_DEF)) }}"
                        alt="{{ config('app.name', 'ERPNovaPrestech') }}" class="{{ VC::LOGO_LG }}">
                @else
                    <img src="{{ (isset($company_logo) && !empty($company_logo) ? asset($company_logo) : asset(SC::CPN_LG_LT_DEF)) }}"
                        alt="{{ config('app.name', 'ERPNovaPrestech') }}" class="{{ VC::LOGO_LG }}">
                @endif
            </a>
        </div>
        <div class="navbar-content">
            @if ($user instanceof User)
                @if ($user[UsersConstants::COL_TP] !== PMC::CL)
                    <ul class="dash-navbar">
                        @if (Gate::check(PMC::SHW_HRM_DSB) ||
                                Gate::check(PMC::SHW_PRJ_DSB) ||
                                Gate::check(PMC::SHW_ACC_DSB) ||
                                Gate::check(PMC::SHW_CRM_DSB) ||
                                Gate::check(PMC::SHW_POS_DSB) || $isSa)
                            @php
                                $segments = [
                                    null,
                                    VW::ACC_DSB,
                                    PMC::INC_RPT,
                                    'reports',
                                    'reports_monthly_cashflow',
                                    'reports_quarterly_cashflow',
                                    'reports_payroll',
                                    'reports_leave',
                                    'reports_monthly_attendance',
                                    'reports_lead',
                                    'reports_deal',
                                    VW::POS_DSB,
                                    'reports_warehouse',
                                    'reports_daily_purchase',
                                    'reports_monthly_purchase',
                                    'reports_daily_pos',
                                    'reports_monthly_pos',
                                    'reports_pos_vs_purchase'
                                ];
                                $kebabSegments = array_map(function($segment) {
                                    if ($segment === null) return null;
                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                }, $segments);
                                $allSegments = array_merge($segments, $kebabSegments);
                                $isIncomeMatch = in_array(RF::segment(1), $allSegments);
                            @endphp
                            <li class="{{ VC::DSH_IT_MN }} {{ $isIncomeMatch ? 'active dash-trigger' : '' }}">
                                <a href="#!" class="dash-link">
                                    <span class="dash-micon">
                                        <i class="{{ VC::TI_HM }}"></i>
                                    </span>
                                    <span class="dash-mtext">{{ __('Dashboard') }}</span>
                                    <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                </a>
                                <ul class="dash-submenu">
                                    @if (($userPlan?->{PLC::COL_ACC} == 1 && Gate::check(PMC::SHW_ACC_DSB)) || $isSa)
                                        @php
                                            $segments = [
                                                null,
                                                VW::ACC_DSB,
                                                'report',
                                                'reports_monthly_cashflow',
                                                'reports_quarterly_cashflow'
                                            ];
                                            $kebabSegments = array_map(function($segment) {
                                                if ($segment === null) return null;
                                                return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                            }, $segments);
                                            $allSegments = array_merge($segments, $kebabSegments);
                                            $isReportMatch = in_array(RF::segment(1), $allSegments);
                                        @endphp
                                        <li class="{{ VC::DSH_IT_MN }} {{ $isReportMatch ? ' active dash-trigger' : '' }}">
                                            <a class="dash-link" href="#">{{ __('Accounting ') }}
                                                <span class="dash-arrow">
                                                    <i data-feather="chevron-right"></i>
                                                </span>
                                            </a>
                                            <ul class="dash-submenu">
                                                @can(PMC::SHW_ACC_DSB)
                                                    @php
                                                        $segments = [
                                                            null,
                                                            VW::ACC_DSB
                                                        ];
                                                        $kebabSegments = array_map(function($segment) {
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
                                                    @endphp
                                                    <li class="dash-item {{ $isAccountingDashboard ? ' active' : '' }}">
                                                        <a
                                                            id="dashboard-link"
                                                            class="dash-link"
                                                            href="{{ $dashboardRoute }}"
                                                            data-url="{{ $dashboardRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $message }}"
                                                        >
                                                            {{ __('Overview') }}
                                                        </a>
                                                    </li>
                                                    @push(ST::ADM_SCR_PG)
                                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/dashboard.js') }}"></script>
                                                    @endpush
                                                @endcan
                                                @if (Gate::check(PMC::INC_RPT) ||
                                                        Gate::check(PMC::EXP_RPT) ||
                                                        Gate::check(PMC::IE_RPT) ||
                                                        Gate::check(PMC::TAX_RPT) ||
                                                        Gate::check(PMC::LP_RPT) ||
                                                        Gate::check(PMC::INV_RPT) ||
                                                        Gate::check(PMC::BIL_RPT) ||
                                                        Gate::check(PMC::STK_RPT) ||
                                                        Gate::check(PMC::TAX_RPT) ||
                                                        Gate::check(PMC::MNG_TRT) || $isSa)
                                                    @php
                                                        $segments = [
                                                            'reports',
                                                            'reports_monthly_cashflow',
                                                            'reports_quarterly_cashflow'
                                                        ];
                                                        
                                                        $kebabSegments = array_map(function($segment) {
                                                            if ($segment === null) return null;
                                                            return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                        }, $segments);
                                                        
                                                        $allSegments = array_merge($segments, $kebabSegments);
                                                        $isCashflowReports = in_array(RF::segment(1), $allSegments);
                                                    @endphp
                                                    <li class="{{ VC::DSH_IT_MN }} {{ $isCashflowReports ? 'active dash-trigger ' : '' }}">
                                                        <a class="dash-link" href="#">{{ __('Reports') }}
                                                            <span class="dash-arrow">
                                                                <i data-feather="chevron-right"></i>
                                                            </span>
                                                        </a>
                                                        <ul class="dash-submenu">
                                                            @can(PMC::STT_RPT)
                                                                @php
                                                                    $accountStatementRoute = Route::has(VW::RPT.'.account.statement')
                                                                        ? route(VW::RPT.'.account.statement')
                                                                        : '#';
                                                                    $linkId = 'account-statement-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        VW::RPT,
                                                                        'account_statement_route_unavailable'
                                                                    ) ?? 'Account statement route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <li class="dash-item {{ RF::route()->getName() == VW::RPT.'.account.statement' ? ' active' : '' }}">
                                                                    <a
                                                                        id="{{ $linkId }}"
                                                                        class="dash-link"
                                                                        href="{{ $accountStatementRoute }}"
                                                                        data-url="{{ $accountStatementRoute }}"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="{{ $message }}"
                                                                    >
                                                                        {{ __('Account Statement') }}
                                                                    </a>
                                                                </li>
                                                                @push(ST::ADM_SCR_PG)
                                                                    <script defer src="{{ asset('assets/js/routes/partials/admin/menu/accountStatement.js') }}"></script>
                                                                @endpush
                                                            @endcan
                                                            @can(PMC::INV_RPT)
                                                                @php
                                                                    $invoiceSummaryRoute = Route::has(VW::RPT.'.invoice.summary')
                                                                        ? route(VW::RPT.'.invoice.summary')
                                                                        : '#';
                                                                    $invoiceSummaryId = 'invoice-summary-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        VW::RPT,
                                                                        'invoice_summary_route_unavailable'
                                                                    ) ?? 'Invoice summary route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <li class="dash-item {{ RF::route()->getName() == VW::RPT.'.invoice.summary' ? ' active' : '' }}">
                                                                    <a
                                                                        id="{{ $invoiceSummaryId }}"
                                                                        class="dash-link"
                                                                        href="{{ $invoiceSummaryRoute }}"
                                                                        data-url="{{ $invoiceSummaryRoute }}"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="{{ $message }}"
                                                                    >
                                                                        {{ __('Invoice Summary') }}
                                                                    </a>
                                                                </li>
                                                                @push(ST::ADM_SCR_PG)
                                                                    <script defer src="{{ asset('assets/js/routes/partials/admin/menu/invoiceSummary.js') }}"></script>
                                                                @endpush
                                                            @endcan
                                                            @php
                                                                $salesRoute = Route::has(VW::RPT.'.sales')
                                                                    ? route(VW::RPT.'.sales')
                                                                    : '#';
                                                                $salesLinkId = 'sales-report-link';
                                                                $salesMessage = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    VW::RPT,
                                                                    'sales_report_route_unavailable'
                                                                ) ?? 'Sales report route is unavailable. Please contact technical support or your domain administrator.';
                                                            @endphp
                                                            <li class="dash-item {{ RF::route()->getName() == VW::RPT.'.sales' ? ' active' : '' }}">
                                                                <a
                                                                    id="{{ $salesLinkId }}"
                                                                    class="dash-link"
                                                                    href="{{ $salesRoute }}"
                                                                    data-url="{{ $salesRoute }}"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="{{ $salesMessage }}"
                                                                >
                                                                    {{ __('Sales Report') }}
                                                                </a>
                                                            </li>
                                                            @push(ST::ADM_SCR_PG)
                                                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/salesReport.js') }}"></script>
                                                            @endpush
                                                            @php
                                                                $receivablesRoute = Route::has(VW::RPT.'.receivables')
                                                                    ? route(VW::RPT.'.receivables')
                                                                    : '#';
                                                                $receivablesLinkId = 'receivables-link';
                                                                $receivablesMessage = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    VW::RPT,
                                                                    'receivables_report_route_unavailable'
                                                                ) ?? 'Receivables route is unavailable. Please contact technical support or your domain administrator.';
                                                            @endphp
                                                            <li class="dash-item {{ RF::route()->getName() == VW::RPT.'.receivables' ? ' active' : '' }}">
                                                                <a
                                                                    id="{{ $receivablesLinkId }}"
                                                                    class="dash-link"
                                                                    href="{{ $receivablesRoute }}"
                                                                    data-url="{{ $receivablesRoute }}"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="{{ $receivablesMessage }}"
                                                                >
                                                                    {{ __('Receivables') }}
                                                                </a>
                                                            </li>
                                                            @push(ST::ADM_SCR_PG)
                                                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/receivables.js') }}"></script>
                                                            @endpush
                                                            @php
                                                                $payablesRoute = Route::has(VW::RPT.'.payables')
                                                                    ? route(VW::RPT.'.payables')
                                                                    : '#';
                                                                $payablesLinkId = 'payables-link';
                                                                $payablesMessage = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    VW::RPT,
                                                                    'payables_report_route_unavailable'
                                                                ) ?? 'Payables route is unavailable. Please contact technical support or your domain administrator.';
                                                            @endphp
                                                            <li class="dash-item {{ RF::route()->getName() == VW::RPT.'.payables' ? ' active' : '' }}">
                                                                <a
                                                                    id="{{ $payablesLinkId }}"
                                                                    class="dash-link"
                                                                    href="{{ $payablesRoute }}"
                                                                    data-url="{{ $payablesRoute }}"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="{{ $payablesMessage }}"
                                                                >
                                                                    {{ __('Payables') }}
                                                                </a>
                                                            </li>
                                                            @push(ST::ADM_SCR_PG)
                                                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/payables.js') }}"></script>
                                                            @endpush
                                                            @can(PMC::BIL_RPT)
                                                                @php
                                                                    $billSummaryRoute = Route::has(VW::RPT.'.bill.summary')
                                                                        ? route(VW::RPT.'.bill.summary')
                                                                        : '#';
                                                                    $billSummaryLinkId = 'bill-summary-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        VW::RPT,
                                                                        'bill_summary_route_unavailable'
                                                                    ) ?? 'Bill summary route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <li class="dash-item {{ RF::route()->getName() == VW::RPT.'.bill.summary' ? ' active' : '' }}">
                                                                    <a
                                                                        id="{{ $billSummaryLinkId }}"
                                                                        class="dash-link"
                                                                        href="{{ $billSummaryRoute }}"
                                                                        data-url="{{ $billSummaryRoute }}"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="{{ $message }}"
                                                                    >
                                                                        {{ __('Bill Summary') }}
                                                                    </a>
                                                                </li>
                                                                @push(ST::ADM_SCR_PG)
                                                                    <script defer src="{{ asset('assets/js/routes/partials/admin/menu/billSummary.js') }}"></script>
                                                                @endpush
                                                            @endcan
                                                            @can(PMC::STK_RPT)
                                                                @php
                                                                    $productStockRoute = Route::has(VW::RPT.'.product.stock.report')
                                                                        ? route(VW::RPT.'.product.stock.report')
                                                                        : '#';
                                                                    $productStockLinkId = 'product-stock-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        VW::RPT,
                                                                        'product_stock_report_route_unavailable'
                                                                    ) ?? 'Product stock report route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <li class="dash-item {{ RF::route()->getName() == VW::RPT.'.product.stock.report' ? ' active' : '' }}">
                                                                    <a
                                                                        id="{{ $productStockLinkId }}"
                                                                        class="dash-link"
                                                                        href="{{ $productStockRoute }}"
                                                                        data-url="{{ $productStockRoute }}"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="{{ $message }}"
                                                                    >
                                                                        {{ __('Product Stock') }}
                                                                    </a>
                                                                </li>
                                                                @push(ST::ADM_SCR_PG)
                                                                    <script defer src="{{ asset('assets/js/routes/partials/admin/menu/productStock.js') }}"></script>
                                                                @endpush
                                                            @endcan
                                                            @can(PMC::LP_RPT)
                                                                @php
                                                                    $cashflowRoute = Route::has(VW::RPT.'.monthly.cashflow')
                                                                        ? route(VW::RPT.'.monthly.cashflow')
                                                                        : '#';
                                                                    $cashFlowId = 'cashflow-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        VW::RPT,
                                                                        'monthly_cashflow_route_unavailable'
                                                                    ) ?? 'Cash flow route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <li class="dash-item {{ request()->is('reports-monthly-cashflow') || request()->is('reports-quarterly-cashflow') ? 'active' : '' }}">
                                                                    <a
                                                                        id="{{ $cashFlowId }}"
                                                                        class="dash-link"
                                                                        href="{{ $cashflowRoute }}"
                                                                        data-url="{{ $cashflowRoute }}"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="{{ $message }}"
                                                                    >
                                                                        {{ __('Cash Flow') }}
                                                                    </a>
                                                                </li>
                                                                @push(ST::ADM_SCR_PG)
                                                                    <script defer>
                                                                        (() => {
                                                                            const listenerAttr = 'data-cashflow-listener-active';
                                                                            const el = document.getElementById('{{ $linkId }}');
                                                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                                            el.setAttribute(listenerAttr, 'true');
                                                                            el.addEventListener('click', event => {
                                                                                try {
                                                                                    const url = el.getAttribute('data-url');
                                                                                    const href = el.href.replace(window.location.origin, '').replace(window.location.pathname, '');
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
                                                                            observer.observe(document.body, { childList: true, subtree: true });
                                                                        })();
                                                                    </script>
                                                                @endpush 
                                                            @endcan
                                                            @can(PMC::MNG_TRT)
                                                                @php
                                                                    $transactionRoute = Route::has(VW::TST.'.index')
                                                                        ? route(VW::TST.'.index')
                                                                        : '#';
                                                                    $linkId = 'transaction-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        VW::TST,
                                                                        'transaction_index_route_unavailable'
                                                                    ) ?? 'Transaction route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <li class="dash-item {{ RF::route()->getName() == VW::TST.'.index' || RF::route()->getName() == VW::TRF.'.create' || RF::route()->getName() == VW::TST.'.edit' ? ' active' : '' }}">
                                                                    <a
                                                                        id="{{ $linkId }}"
                                                                        class="dash-link"
                                                                        href="{{ $transactionRoute }}"
                                                                        data-url="{{ $transactionRoute }}"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="{{ $message }}"
                                                                    >
                                                                        {{ __('Transaction') }}
                                                                    </a>
                                                                </li>
                                                                @push(ST::ADM_SCR_PG)
                                                                    <script defer src="{{ asset('assets/js/routes/partials/admin/menu/transactions.js') }}"></script>
                                                                @endpush
                                                            @endcan
                                                            @can(PMC::INC_RPT)
                                                                @php
                                                                    $incomeSummaryRoute = Route::has(VW::RPT.'.income.summary')
                                                                        ? route(VW::RPT.'.income.summary')
                                                                        : '#';
                                                                    $incomeSummaryLinkId = 'income-summary-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        VW::RPT,
                                                                        'income_summary_route_unavailable'
                                                                    ) ?? 'Income summary route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <li class="dash-item {{ RF::route()->getName() == VW::RPT.'.income.summary' ? ' active' : '' }}">
                                                                    <a
                                                                        id="{{ $incomeSummaryLinkId }}"
                                                                        class="dash-link"
                                                                        href="{{ $incomeSummaryRoute }}"
                                                                        data-url="{{ $incomeSummaryRoute }}"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="{{ $message }}"
                                                                    >
                                                                        {{ __('Income Summary') }}
                                                                    </a>
                                                                </li>
                                                                @push(ST::ADM_SCR_PG)
                                                                    <script defer src="{{ asset('assets/js/routes/partials/admin/menu/incomeSummary.js') }}"></script>
                                                                @endpush
                                                            @endcan
                                                            @can(PMC::EXP_RPT)
                                                                @php
                                                                    $expenseSummaryRoute = Route::has(VW::RPT.'.expense.summary')
                                                                        ? route(VW::RPT.'.expense.summary')
                                                                        : '#';
                                                                    $expenseSummaryLinkId = 'expense-summary-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        VW::RPT,
                                                                        'expense_summary_route_unavailable'
                                                                    ) ?? 'Expense summary route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <li class="dash-item {{ RF::route()->getName() == VW::RPT.'.expense.summary' ? ' active' : '' }}">
                                                                    <a
                                                                        id="{{ $expenseSummaryLinkId }}"
                                                                        class="dash-link"
                                                                        href="{{ $expenseSummaryRoute }}"
                                                                        data-url="{{ $expenseSummaryRoute }}"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="{{ $message }}"
                                                                    >
                                                                        {{ __('Expense Summary') }}
                                                                    </a>
                                                                </li>
                                                                @push(ST::ADM_SCR_PG)
                                                                    <script defer src="{{ asset('assets/js/routes/partials/admin/menu/expenseSummary.js') }}"></script>
                                                                @endpush
                                                            @endcan
                                                            @can(PMC::IE_RPT)
                                                                @php
                                                                    $incomeVsExpenseRoute = Route::has(VW::RPT.'.income.vs.expense.summary')
                                                                        ? route(VW::RPT.'.income.vs.expense.summary')
                                                                        : '#';
                                                                    $incomeVsExpenseSummaryId = 'income-vs-expense-summary-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        VW::RPT,
                                                                        'income_vs_expense_summary_route_unavailable'
                                                                    ) ?? 'Income VS Expense route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <li class="dash-item {{ RF::route()->getName() == VW::RPT.'.income.vs.expense.summary' ? ' active' : '' }}">
                                                                    <a
                                                                        id="{{ $incomeVsExpenseSummaryId }}"
                                                                        class="dash-link"
                                                                        href="{{ $incomeVsExpenseRoute }}"
                                                                        data-url="{{ $incomeVsExpenseRoute }}"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="{{ $message }}"
                                                                    >
                                                                        {{ __('Income VS Expense') }}
                                                                    </a>
                                                                </li>
                                                                @push(ST::ADM_SCR_PG)
                                                                    <script defer src="{{asset('assets/js/routes/partials/admin/menu/incomeVsExpenseSummary.js')}}"></script>
                                                                @endpush
                                                            @endcan
                                                            @can(PMC::TAX_RPT)
                                                                @php
                                                                    $taxSummaryRoute = Route::has(VW::RPT.'.tax.summary')
                                                                        ? route(VW::RPT.'.tax.summary')
                                                                        : '#';
                                                                    $taxLinkId = 'tax-summary-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        VW::RPT,
                                                                        'tax_summary_unavailable'
                                                                    ) ?? 'Tax summary route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <li class="dash-item {{ RF::route()->getName() == VW::RPT.'.tax.summary' ? ' active' : '' }}">
                                                                    <a
                                                                        id="{{ $taxLinkId }}"
                                                                        class="dash-link"
                                                                        href="{{ $taxSummaryRoute }}"
                                                                        data-url="{{ $taxSummaryRoute }}"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="{{ $message }}"
                                                                    >
                                                                        {{ __('Tax Summary') }}
                                                                    </a>
                                                                </li>
                                                                @push(ST::ADM_SCR_PG)
                                                                    <script defer src="{{asset('assets/js/routes/partials/admin/menu/taxSummary.js')}}"></script>
                                                                @endpush
                                                            @endcan
                                                        </ul>
                                                    </li>
                                                @endif
                                            </ul>
                                        </li>
                                    @endif
                                    @if ((!empty($userPlan) && $userPlan?->{PLC::COL_HRM} == 1) || $isSa)
                                        @can(PMC::SHW_HRM_DSB)
                                            @php
                                                $segments = [
                                                    VW::HRM_DSB,
                                                    'reports_payroll'
                                                ];
                                                $kebabSegments = array_map(function($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);
                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isHrmPayroll = in_array(RF::segment(1), $allSegments);
                                            @endphp
                                            <li
                                                class="{{ VC::DSH_IT_MN }} {{ $isHrmPayroll ? ' active dash-trigger' : '' }}">
                                                <a class="dash-link" href="#">{{ __('HRM ') }}
                                                    <span class="dash-arrow">
                                                        <i data-feather="chevron-right"></i>
                                                    </span>
                                                </a>
                                                <ul class="dash-submenu">
                                                    @php
                                                        $hrmDashboardRoute = Route::has('hrm.dashboard') ? route('hrm.dashboard') : '#';
                                                        $hrmDsbLinkId = 'hrm-dashboard-link';
                                                        $message = Utility::fetchLinkMessage(
                                                            $lang,
                                                            'generic',
                                                            'hrm_dashboard_unavailable'
                                                        ) ?? 'Dashboard for the Human Resources Management route is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <li class="dash-item {{ RF::route()->getName() == 'hrm.dashboard' ? ' active' : '' }}">
                                                        <a
                                                            id="{{ $hrmDsbLinkId }}"
                                                            class="dash-link"
                                                            href="{{ $hrmDashboardRoute }}"
                                                            data-url="{{ $hrmDashboardRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $message }}"
                                                        >
                                                            {{ __('Overview') }}
                                                        </a>
                                                    </li>
                                                    @push(ST::ADM_SCR_PG)
                                                        <script defer src="{{asset('assets/js/routes/partials/admin/menu/hrmDashboard.js')}}"></script>
                                                    @endpush
                                                    @can(PMC::MNG_RPT)
                                                        @php
                                                            $segments = [
                                                                'reports_monthly_attendance',
                                                                'reports_leave',
                                                                'reports_payroll'
                                                            ];
                                                            $kebabSegments = array_map(function($segment) {
                                                                if ($segment === null) return null;
                                                                return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                            }, $segments);
                                                            $allSegments = array_merge($segments, $kebabSegments);
                                                            $isHrmReports = in_array(RF::segment(1), $allSegments);
                                                        @endphp
                                                        <li class="{{ VC::DSH_IT_MN }} {{ $isHrmReports ? 'active dash-trigger' : '' }}"
                                                            href="#hr-report" data-toggle="collapse" role="button"
                                                            aria-expanded="{{ $isHrmReports ? 'true' : 'false' }}">
                                                            <a class="dash-link" href="#">{{ __('Reports') }}
                                                                <span class="dash-arrow">
                                                                    <i data-feather="chevron-right"></i>
                                                                </span>
                                                            </a>
                                                            @php
                                                                $payrollRoute = Route::has(VW::RPT.'.payroll')
                                                                    ? route(VW::RPT.'.payroll')
                                                                    : '#';
                                                                $payrollLinkId = 'reports-payroll-link';
                                                                $payrollMessage = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    VW::RPT,
                                                                    'payroll_route_unavailable'
                                                                ) ?? 'Payroll route is unavailable. Please contact technical support or your domain administrator.';
                                                                $leaveRoute = Route::has(VW::RPT.'.leave')
                                                                    ? route(VW::RPT.'.leave')
                                                                    : '#';
                                                                $leaveLinkId = 'reports-leave-link';
                                                                $leaveMessage = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    VW::RPT,
                                                                    'leave_route_unavailable'
                                                                ) ?? 'Leave route is unavailable. Please contact technical support or your domain administrator.';
                                                                $attendanceRoute = Route::has(VW::RPT.'.monthly.attendance')
                                                                    ? route(VW::RPT.'.monthly.attendance')
                                                                    : '#';
                                                                $attendanceLinkId = 'reports-monthly-attendance-link';
                                                                $attendanceMessage = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    VW::RPT,
                                                                    'monthly_attendance_route_unavailable'
                                                                ) ?? 'Monthly attendance route is unavailable. Please contact technical support or your domain administrator.';
                                                            @endphp
                                                            <ul class="dash-submenu">
                                                                <li class="dash-item {{ (request()->is('reports-payroll') || request()->is('reports_payroll')) ? 'active' : '' }}">
                                                                    <a
                                                                        id="{{ $payrollLinkId }}"
                                                                        class="dash-link"
                                                                        href="{{ $payrollRoute }}"
                                                                        data-url="{{ $payrollRoute }}"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="{{ $payrollMessage }}"
                                                                    >
                                                                        {{ __('Payroll') }}
                                                                    </a>
                                                                </li>
                                                                <li class="dash-item {{ (request()->is('reports-leave') || request()->is('reports_leave')) ? 'active' : '' }}">
                                                                    <a
                                                                        id="{{ $leaveLinkId }}"
                                                                        class="dash-link"
                                                                        href="{{ $leaveRoute }}"
                                                                        data-url="{{ $leaveRoute }}"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="{{ $leaveMessage }}"
                                                                    >
                                                                        {{ __('Leave') }}
                                                                    </a>
                                                                </li>
                                                                <li class="dash-item {{ (request()->is('reports-monthly-attendance') || request()->is('reports_monthly_attendance')) ? 'active' : '' }}">
                                                                    <a
                                                                        id="{{ $attendanceLinkId }}"
                                                                        class="dash-link"
                                                                        href="{{ $attendanceRoute }}"
                                                                        data-url="{{ $attendanceRoute }}"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="{{ $attendanceMessage }}"
                                                                    >
                                                                        {{ __('Monthly Attendance') }}
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                            @push(ST::ADM_SCR_PG)
                                                                <script defer src="{{asset('assets/js/routes/partials/admin/menu/reportsPayroll.js')}}"></script>
                                                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/reportsLeave.js') }}"></script>
                                                                <script defer src="{{asset('assets/js/routes/partials/admin/menu/reportsMonthlyAttendance.js')}}"></script>
                                                            @endpush
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </li>
                                        @endcan
                                    @endif
                                    @if ((!empty($userPlan) && $userPlan?->{PLC::COL_CRM} == 1) || $isSa)
                                        @can(PMC::SHW_CRM_DSB)
                                            @php
                                                $segments = [VW::CRM_DSB, 'reports-lead', 'reports-deal'];
                                                $kebabSegments = array_map(function($s) {
                                                    return strtolower(preg_replace('/[A-Z]/', '-$0', lcfirst($s)));
                                                }, $segments);
                                                $isLeadMatch = in_array(RF::segment(1), array_merge($segments, $kebabSegments));
                                            @endphp
                                            <li
                                                class="{{ VC::DSH_IT_MN }} {{ $isLeadMatch ? ' active dash-trigger' : '' }}">
                                                <a class="dash-link" href="#">{{ __('CRM') }}
                                                    <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                </a>
                                                <ul class="dash-submenu">
                                                    @php
                                                        $crmDashboardRoute = Route::has('crm.dashboard') ? route('crm.dashboard') : '#';
                                                        $linkId = 'crm-dashboard-link';
                                                        $message = Utility::fetchLinkMessage(
                                                            $lang,
                                                            'generics',
                                                            'crm_dashboard_unavailable'
                                                        ) ?? 'The dashboard route for Customer Resources Managament is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <li class="dash-item {{ RF::route()->getName() == 'crm.dashboard' ? ' active' : '' }}">
                                                        <a
                                                            id="{{ $linkId }}"
                                                            class="dash-link"
                                                            href="{{ $crmDashboardRoute }}"
                                                            data-url="{{ $crmDashboardRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $message }}"
                                                        >
                                                            {{ __('Overview') }}
                                                        </a>
                                                    </li>
                                                    @push(ST::ADM_SCR_PG)
                                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/crmDashboard.js') }}"></script>
                                                    @endpush
                                                    @php
                                                        $segments = [
                                                            'reports_lead',
                                                            'reports_deal'
                                                        ];
                                                        $kebabSegments = array_map(function($segment) {
                                                            if ($segment === null) return null;
                                                            return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                        }, $segments);
                                                        $allSegments = array_merge($segments, $kebabSegments);
                                                        $isCrmReports = in_array(RF::segment(1), $allSegments);
                                                    @endphp
                                                    <li class="{{ VC::DSH_IT_MN }} {{ $isCrmReports ? 'active dash-trigger' : '' }}" href="#crm-report" data-toggle="collapse" role="button" aria-expanded="{{ $isCrmReports ? 'true' : 'false' }}">
                                                        <a class="dash-link" href="#">{{ __('Reports') }}
                                                            <span class="dash-arrow">
                                                                <i data-feather="chevron-right"></i>
                                                            </span>
                                                        </a>
                                                        @php
                                                            $leadRoute = Route::has(VW::RPT.'.lead')
                                                                ? route(VW::RPT.'.lead')
                                                                : '#';
                                                            $leadLinkId = 'reports-lead-link';
                                                            $leadMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::RPT,
                                                                'lead_route_unavailable'
                                                            ) ?? 'Lead report route is unavailable. Please contact technical support or your domain administrator.';
                                                        
                                                            $dealRoute = Route::has(VW::RPT.'.deal')
                                                                ? route(VW::RPT.'.deal')
                                                                : '#';
                                                            $dealLinkId = 'reports-deal-link';
                                                            $dealMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::RPT,
                                                                'deal_route_unavailable'
                                                            ) ?? 'Deal report route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <ul class="dash-submenu">
                                                            <li class="dash-item {{ (request()->is('reports-lead') || request()->is('reports_lead')) ? 'active' : '' }}">
                                                                <a
                                                                    id="{{ $leadLinkId }}"
                                                                    class="dash-link"
                                                                    href="{{ $leadRoute }}"
                                                                    data-url="{{ $leadRoute }}"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="{{ $leadMessage }}"
                                                                >
                                                                    {{ __('Lead') }}
                                                                </a>
                                                            </li>
                                                            <li class="dash-item {{ (request()->is('reports-deal') || request()->is('reports_deal')) ? 'active' : '' }}">
                                                                <a
                                                                    id="{{ $dealLinkId }}"
                                                                    class="dash-link"
                                                                    href="{{ $dealRoute }}"
                                                                    data-url="{{ $dealRoute }}"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="{{ $dealMessage }}"
                                                                >
                                                                    {{ __('Deal') }}
                                                                </a>
                                                            </li>
                                                        </ul>
                                                        @push(ST::ADM_SCR_PG)
                                                            <script defer src="{{ asset('assets/js/routes/partials/admin/menu/reportsLead.js') }}"></script>
                                                            <script defer src="{{ asset('assets/js/routes/partials/admin/menu/reportsDeal.js') }}"></script>
                                                        @endpush
                                                    </li>
                                                </ul>
                                            </li>
                                        @endcan
                                    @endif
                                    @if ($userPlan?->{PLC::COL_PJ} == 1 || $isSa)
                                        @can(PMC::SHW_PRJ_DSB)
                                            @php
                                                $projectDashboardRoute = Route::has('project.dashboard')
                                                    ? route('project.dashboard')
                                                    : '#';
                                                $projectDashboardLinkId = 'project-dashboard-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    VW::PRJ,
                                                    'project_dashboard_route_unavailable'
                                                ) ?? 'Project dashboard route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <li class="dash-item {{ RF::route()->getName() == 'project.dashboard' ? ' active' : '' }}">
                                                <a
                                                    id="{{ $projectDashboardLinkId }}"
                                                    class="dash-link"
                                                    href="{{ $projectDashboardRoute }}"
                                                    data-url="{{ $projectDashboardRoute }}"
                                                    data-sv-localized="true"
                                                    data-guard-msg="{{ $message }}"
                                                >
                                                    {{ __(VW::PRJ) }}
                                                </a>
                                            </li>
                                            @push(ST::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/projectDashboard.js') }}"></script>
                                            @endpush                                
                                        @endcan
                                    @endif
                                    @if ((!empty($userPlan) && $userPlan?->{PLC::COL_POS} == 1) || $isSa)
                                        @can(PMC::SHW_POS_DSB)
                                            @php
                                                $segments = [
                                                    'pos_dashboard',
                                                    'reports_warehouse',
                                                    'reports_daily_purchase',
                                                    'reports_monthly_purchase',
                                                    'reports_daily_pos',
                                                    'reports_monthly_pos',
                                                    'reports_pos_vs_purchase'
                                                ];
                                                
                                                $kebabSegments = array_map(function($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);
                                                
                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isPosReports = in_array(RF::segment(1), $allSegments);
                                            @endphp
                                            <li class="{{ VC::DSH_IT_MN }} {{ $isPosReports ? ' active dash-trigger' : '' }}">
                                                <a class="dash-link" href="#">{{ __('POS') }}
                                                    <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                </a>
                                                <ul class="dash-submenu">
                                                    @php
                                                        $posDashboardRoute = Route::has(VW::POS.'.dashboard')
                                                            ? route(VW::POS.'.dashboard')
                                                            : '#';
                                                        $posDashboardLinkId = 'pos-dashboard-link';
                                                        $message = Utility::fetchLinkMessage(
                                                            $lang,
                                                            'generics',
                                                            'pos_dashboard_route_unavailable'
                                                        ) ?? 'The route for the dashboard of the Points of Sales is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <li class="dash-item {{ RF::route()->getName() == VW::POS.'.dashboard' ? ' active' : '' }}">
                                                        <a
                                                            id="{{ $posDashboardLinkId }}"
                                                            class="dash-link"
                                                            href="{{ $posDashboardRoute }}"
                                                            data-url="{{ $posDashboardRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $message }}"
                                                        >
                                                            {{ __('Overview') }}
                                                        </a>
                                                    </li>
                                                    @push(ST::ADM_SCR_PG)
                                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/posDashboard.js') }}"></script>
                                                    @endpush
                                                    <li class="{{ VC::DSH_IT_MN }} {{ $isPosReports ? 'active dash-trigger' : '' }}"
                                                        href="#crm-report" data-toggle="collapse" role="button"
                                                        aria-expanded="{{ $isPosReports ? 'true' : 'false' }}">
                                                        <a class="dash-link" href="#">{{ __('Reports') }}
                                                            <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                        </a>
                                                    @php
                                                        $warehouseRoute = Route::has(VW::RPT.'.warehouse')
                                                            ? route(VW::RPT.'.warehouse')
                                                            : '#';
                                                        $warehouseLinkId = 'warehouse-report-link';
                                                        $warehouseMessage = Utility::fetchLinkMessage(
                                                            $lang,
                                                            VW::RPT,
                                                            'warehouse_report_route_unavailable'
                                                        ) ?? 'Warehouse report route is unavailable. Please contact technical support or your domain administrator.';
                                                        $dailyPurchaseRoute = Route::has(VW::RPT.'.daily.purchase')
                                                            ? route(VW::RPT.'.daily.purchase')
                                                            : '#';
                                                        $dailyPurchaseLinkId = 'daily-purchase-report-link';
                                                        $dailyPurchaseMessage = Utility::fetchLinkMessage(
                                                            $lang,
                                                            VW::RPT,
                                                            'daily_purchase_report_route_unavailable'
                                                        ) ?? 'Purchase daily/monthly report route is unavailable. Please contact technical support or your domain administrator.';
                                                        $dailyPosRoute = Route::has(VW::RPT.'.daily.pos')
                                                            ? route(VW::RPT.'.daily.pos')
                                                            : '#';
                                                        $dailyPosLinkId = 'daily-pos-report-link';
                                                        $dailyPosMessage = Utility::fetchLinkMessage(
                                                            $lang,
                                                            VW::RPT,
                                                            'daily_pos_report_route_unavailable'
                                                        ) ?? 'POS daily/monthly report route is unavailable. Please contact technical support or your domain administrator.';
                                                        $posVsPurchaseRoute = Route::has(VW::RPT.'.pos.vs.purchase')
                                                            ? route(VW::RPT.'.pos.vs.purchase')
                                                            : '#';
                                                        $posVsPurchaseLinkId = 'pos-vs-purchase-report-link';
                                                        $posVsPurchaseMessage = Utility::fetchLinkMessage(
                                                            $lang,
                                                            VW::RPT,
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
                                                        $isActiveRoute = function($routes) {
                                                            return collect($routes)->contains(fn($route) => request()->is($route));
                                                        };
                                                    @endphp
                                                    <ul class="dash-submenu">
                                                        @foreach ($menuItems as $item)
                                                            <li class="dash-item {{ $isActiveRoute($item['routes']) ? 'active' : '' }}">
                                                                <a
                                                                    id="{{ $item['linkId'] }}"
                                                                    class="dash-link"
                                                                    href="{{ $item['route'] }}"
                                                                    data-url="{{ $item['route'] }}"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="{{ $item['message'] }}"
                                                                >
                                                                    {{ $item['label'] }}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                    @push(ST::ADM_SCR_PG)
                                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/reportsWarehouse.js') }}"></script>
                                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/reportsDailyPurchase.js') }}"></script>
                                                        {{-- <script defer src="{{ asset('assets/js/routes/partials/admin/menu/reportsPosPurchase.js') }}"></script> --}}
                                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/reportsPosVsPurchase.js') }}"></script>
                                                    @endpush                                                
                                                    </li>
                                                </ul>
                                            </li>
                                        @endcan
                                    @endif
                                </ul>
                            </li>
                        @endif
                        @if ((!empty($userPlan) && $userPlan?->{PLC::COL_HRM} == 1) || $isSa)
                            @if (Gate::check(PMC::MNG_EMP) || Gate::check(PMC::MNG_SSL) || $isSa)
                                @php
                                    $segments = [
                                        VW::ALW_OPT,
                                        VW::ANC,
                                        VW::AWD,
                                        VW::AWD_TP,
                                        VW::BRC,
                                        VW::C_JB_APL,
                                        VW::CPN_PL,
                                        VW::CPL,
                                        VW::CPT,
                                        VW::CRR,
                                        VW::CST_QT,
                                        VW::DDT_OPT,
                                        VW::DOC,
                                        VW::DOC_UP,
                                        VW::DPT,
                                        VW::DSG,
                                        VW::EMP,
                                        VW::EMP_ATD,
                                        VW::GL_TP,
                                        VW::HLD,
                                        VW::HLD_CLD,
                                        VW::ITV_SCD,
                                        VW::JB,
                                        VW::JB_APL,
                                        VW::JB_CAT,
                                        VW::JB_OB,
                                        VW::JB_STG,
                                        VW::LN_OPT,
                                        VW::LV,
                                        VW::LV_CLD,
                                        VW::LV_RQ,
                                        VW::LV_TP,
                                        VW::PFM_TP,
                                        VW::PLC,
                                        VW::PRM,
                                        VW::PY_SLP,
                                        VW::PY_SLP_TP,
                                        VW::RSG,
                                        VW::S_SLR,
                                        VW::TMN,
                                        VW::TMN_TP,
                                        VW::TNG,
                                        VW::TRF,
                                        VW::TRV,
                                        VW::WRN
                                    ];
                                    
                                    $kebabSegments = array_map(function($segment) {
                                        if ($segment === null) return null;
                                        return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                    }, $segments);
                                    
                                    $allSegments = array_merge($segments, $kebabSegments);
                                    $isHrmManagement = in_array(RF::segment(1), $allSegments);
                                @endphp
                                <li
                                    class="{{ VC::DSH_IT_MN }} {{ $isHrmManagement ? 'active dash-trigger' : '' }}">
                                    <a href="#!" class="dash-link">
                                        <span class="dash-micon">
                                            <i class="{{ VC::TI_USR }}"></i>
                                        </span>
                                        <span class="dash-mtext">
                                            {{ __('HRM System') }}
                                        </span>
                                        <span class="dash-arrow">
                                            <i data-feather="chevron-right"></i>
                                        </span>
                                    </a>
                                    <ul class="dash-submenu">
                                        @php
                                            $isEmployee = strtolower($user[UsersConstants::COL_TP]) === 'employee';
                                            if ($isEmployee) {
                                                $employee = Employee::where('user_id', $user?->id)->first();
                                                $empRoute = Route::has(VW::EMP.'.show')
                                                    ? route(VW::EMP.'.show', Crypt::encrypt($employee->id))
                                                    : '#';
                                                $msgKey = 'show_employee_route_unavailable';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    VW::EMP,
                                                    $msgKey
                                                ) ?? 'Employee view route is unavailable. Please contact technical support or your domain administrator.';
                                            } else {
                                                $empRoute = Route::has(VW::EMP.'.index')
                                                    ? route(VW::EMP.'.index')
                                                    : '#';
                                                $msgKey = 'employee_setup_route_unavailable';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    VW::EMP,
                                                    $msgKey
                                                ) ?? 'Employee setup route is unavailable. Please contact technical support or your domain administrator.';
                                            }
                                            $linkId = 'employee-link';
                                        @endphp
                                        <li class="dash-item {{ RF::segment(1) == VW::EMP ? 'active dash-trigger' : '' }}">
                                            <a
                                                id="{{ $linkId }}"
                                                class="dash-link"
                                                href="{{ $empRoute }}"
                                                data-url="{{ $empRoute }}"
                                                data-sv-localized="true"
                                                data-guard-msg="{{ $message }}"
                                            >
                                                {{ $isEmployee ? __('Employee') : __('Employee Setup') }}
                                            </a>
                                        </li>
                                        @push(ST::ADM_SCR_PG)
                                            <script defer src="{{ asset('assets/js/routes/partials/admin/menu/employee.js') }}"></script>
                                        @endpush
                                        @if (Gate::check(PMC::MNG_SSL) || Gate::check(PMC::MNG_PSL) || $isSa)
                                            @php
                                                $segments = [
                                                    VW::PY_SLP,
                                                    VW::S_SLR
                                                ];
                                                
                                                $kebabSegments = array_map(function($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);
                                                
                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isPayrollSalary = in_array(RF::segment(1), $allSegments);
                                            @endphp
                                            <li class="{{ VC::DSH_IT_MN }}  {{ $isPayrollSalary ? 'active dash-trigger' : '' }}">
                                                <a class="dash-link" href="#">{{ __('Payroll Setup') }}
                                                    <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                </a>
                                                <ul class="dash-submenu">
                                                    @can(PMC::MNG_SSL)
                                                        @php
                                                            $setSalaryRoute = Route::has(VW::S_SLR.'.index')
                                                                ? route(VW::S_SLR.'.index')
                                                                : (Route::has(Str::kebab(VW::S_SLR.'.index'))
                                                                ? route(Str::kebab(VW::S_SLR.'.index'))
                                                                : '#');
                                                            $setSalaryLinkId = 'set-salary-link';
                                                            $message = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::S_SLR,
                                                                'set_salary_index_route_unavailable'
                                                            ) ?? 'Set salary route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ (request()->is('set_salaries*') || request()->is('set-salaries*')) ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $setSalaryLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $setSalaryRoute }}"
                                                                data-url="{{ $setSalaryRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $message }}"
                                                            >
                                                                {{ __('Set salary') }}
                                                            </a>
                                                        </li>
                                                        @push(ST::ADM_SCR_PG)
                                                            <script defer src="{{ asset('assets/js/routes/partials/admin/menu/setSalary.js') }}"></script>
                                                        @endpush
                                                    @endcan
                                                    @can(PMC::MNG_PSL)
                                                        @php
                                                            $payslipRoute = Route::has(VW::PY_SLP.'.index')
                                                                ? route(VW::PY_SLP.'.index')
                                                                : '#';
                                                            $paySlipLinkId = 'payslip-link';
                                                            $message = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::PY_SLP,
                                                                'payslip_index_route_unavailable'
                                                            ) ?? 'Payslip route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ request()->is('payslip*') ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $paySlipLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $setSalaryRoute }}"
                                                                data-url="{{ $payslipRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $message }}"
                                                            >
                                                                {{ __('Payslip') }}
                                                            </a>
                                                        </li>
                                                        @push(ST::ADM_SCR_PG)
                                                            <script defer src="{{ asset('assets/js/routes/partials/admin/menu/payslip.js') }}"></script>
                                                        @endpush
                                                    @endcan
                                                </ul>
                                            </li>
                                        @endif
                                        @if (Gate::check(PMC::MNG_LV) || Gate::check(PMC::MNG_ATD) || $isSa)
                                            @php
                                                $segments = [
                                                    VW::EMP_ATD,
                                                    VW::LV
                                                ];
                                                
                                                $kebabSegments = array_map(function($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);
                                                
                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isAttendanceLeave = in_array(RF::segment(1), $allSegments);
                                            @endphp
                                            <li
                                                class="{{ VC::DSH_IT_MN }}  {{ $isAttendanceLeave ? 'active dash-trigger' : '' }}">
                                                <a class="dash-link" href="#">{{ __('Leave Management Setup') }}
                                                    <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                </a>
                                                <ul class="dash-submenu">
                                                    @can(PMC::MNG_LV)
                                                        @php
                                                            $manageLeaveRoute = Route::has(VW::LV.'.index')
                                                                ? route(VW::LV.'.index')
                                                                : '#';
                                                            $manageLeaveLinkId = 'manage-leave-link';
                                                            $message = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::LV,
                                                                'leave_index_route_unavailable'
                                                            ) ?? 'Manage leave route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ RF::route()->getName() == VW::LV.'.index' ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $manageLeaveLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $manageLeaveRoute }}"
                                                                data-url="{{ $manageLeaveRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $message }}"
                                                            >
                                                                {{ __('Manage Leave') }}
                                                            </a>
                                                        </li>
                                                        @push(ST::ADM_SCR_PG)
                                                            <script defer src="{{ asset('assets/js/routes/partials/admin/menu/manageLeave.js') }}"></script>
                                                        @endpush
                                                    @endcan
                                                    @can(PMC::MNG_ATD)
                                                        @php
                                                            $segments = [
                                                                VW::EMP_ATD
                                                            ];
                                                            
                                                            $kebabSegments = array_map(function($segment) {
                                                                if ($segment === null) return null;
                                                                return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                            }, $segments);
                                                            
                                                            $allSegments = array_merge($segments, $kebabSegments);
                                                            $isEmployeeAttendance = in_array(RF::segment(1), $allSegments);
                                                        @endphp
                                                        <li class="{{ VC::DSH_IT_MN }} {{ $isEmployeeAttendance ? 'active dash-trigger' : '' }}"
                                                            href="#navbar-attendance" data-toggle="collapse" role="button"
                                                            aria-expanded="{{ $isEmployeeAttendance ? 'true' : 'false' }}">
                                                            <a class="dash-link" href="#">{{ __('Attendance') }}
                                                                <span class="dash-arrow">
                                                                    <i data-feather="chevron-right"></i>
                                                                </span>
                                                            </a>
                                                            <ul class="dash-submenu">
                                                                @php
                                                                    $markAttendanceRoute = Route::has(VW::EMP_ATD.'.index')
                                                                        ? route(VW::EMP_ATD.'.index')
                                                                        : (Route::has(Str::kebab(VW::EMP_ATD.'.index'))
                                                                        ? route(Str::kebab(VW::EMP_ATD.'.index'))
                                                                        : '#');
                                                                    $markAttendanceLinkId = 'mark-attendance-link';
                                                                    $message = Utility::fetchLinkMessage(
                                                                        $lang,
                                                                        VW::EMP_ATD,
                                                                        'attendance_index_route_unavailable'
                                                                    ) ?? 'Mark attendance route is unavailable. Please contact technical support or your domain administrator.';
                                                                @endphp
                                                                <li class="dash-item {{ RF::route()->getName() == VW::EMP_ATD.'.index' ? 'active' : '' }}">
                                                                    <a
                                                                        id="{{ $markAttendanceLinkId }}"
                                                                        class="dash-link"
                                                                        href="{{ $markAttendanceRoute }}"
                                                                        data-url="{{ $markAttendanceRoute }}"
                                                                        data-sv-localized="true"
                                                                        data-guard-msg="{{ $message }}"
                                                                    >
                                                                        {{ __('Mark Attendance') }}
                                                                    </a>
                                                                </li>
                                                                @push(ST::ADM_SCR_PG)
                                                                    <script defer src="{{ asset('assets/js/routes/partials/admin/menu/markAttendance.js') }}"></script>
                                                                @endpush
                                                                @can(PMC::CR_ATD)
                                                                    @php
                                                                        $bulkAttendanceRoute = Route::has(VW::EMP_ATD.'.'.EAC::BK_ATD)
                                                                            ? route(VW::EMP_ATD.'.'.EAC::BK_ATD)
                                                                            : (Route::has(Str::kebab(Route::has(VW::EMP_ATD.'.'.EAC::BK_ATD)))
                                                                            ? route(Str::kebab(VW::EMP_ATD.'.'.EAC::BK_ATD))
                                                                            : '#');
                                                                        $bulkAttendanceLinkId = 'bulk-attendance-link';
                                                                        $message = Utility::fetchLinkMessage(
                                                                            $lang,
                                                                            VW::EMP_ATD,
                                                                            'bulk_attendance_route_unavailable'
                                                                        ) ?? 'Bulk attendance route is unavailable. Please contact technical support or your domain administrator.';
                                                                    @endphp
                                                                    <li class="dash-item {{ RF::route()->getName() == VW::EMP_ATD.'.'.EAC::BK_ATD ? 'active' : '' }}">
                                                                        <a
                                                                            id="{{ $bulkAttendanceLinkId }}"
                                                                            class="dash-link"
                                                                            href="{{ $bulkAttendanceRoute }}"
                                                                            data-url="{{ $bulkAttendanceRoute }}"
                                                                            data-sv-localized="true"
                                                                            data-guard-msg="{{ $message }}"
                                                                        >
                                                                            {{ __('Bulk Attendance') }}
                                                                        </a>
                                                                    </li>
                                                                    @push(ST::ADM_SCR_PG)
                                                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/bulkAttendance.js') }}"></script>
                                                                    @endpush
                                                                @endcan
                                                            </ul>
                                                        </li>
                                                    @endcan
                                                </ul>
                                            </li>
                                        @endif
                                        @if (Gate::check(PMC::MNG_IND) || Gate::check(PMC::MNG_APR) || Gate::check(PMC::MNG_GTR) || $isSa)
                                            @php
                                                $segments = [
                                                    VW::APR,
                                                    VW::GL_TRC,
                                                    VW::IND
                                                ];
                                                $kebabSegments = array_map(function($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);
                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isIndicatorApproval = in_array(RF::segment(1), $allSegments);
                                            @endphp
                                            <li class="{{ VC::DSH_IT_MN }} {{ $isIndicatorApproval ? 'active dash-trigger' : '' }}"
                                                href="#navbar-performance" data-toggle="collapse" role="button"
                                                aria-expanded="{{ $isIndicatorApproval ? 'true' : 'false' }}">
                                                <a class="dash-link" href="#">{{ __('Performance Setup') }}
                                                    <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                </a>
                                                <ul class="dash-submenu {{ $isIndicatorApproval? 'show' : 'collapse' }}">
                                                    @can(PMC::MNG_IND)
                                                        @php
                                                            $indicatorIndexRoute = Route::has(VW::IND.'.index')
                                                                ? route(VW::IND.'.index')
                                                                : '#';
                                                            $indicatorLinkId = 'indicator-index-link';
                                                            $message = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::IND,
                                                                'indicator_index_route_unavailable'
                                                            ) ?? 'Indicator index route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ request()->is('indicator*') ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $indicatorLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $indicatorIndexRoute }}"
                                                                data-url="{{ $indicatorIndexRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $message }}"
                                                            >
                                                                {{ __('Indicator') }}
                                                            </a>
                                                        </li>
                                                        @push(ST::ADM_SCR_PG)
                                                            <script defer src="{{ asset('assets/js/routes/partials/admin/menu/indicator.js') }}"></script>
                                                        @endpush
                                                    @endcan
                                                    @can(PMC::MNG_APR)
                                                        @php
                                                            $appraisalIndexRoute = Route::has(VW::APR.'.index')
                                                                ? route(VW::APR.'.index')
                                                                : '#';
                                                            $appraisalLinkId = 'appraisal-index-link';
                                                            $message = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::APR,
                                                                'appraisal_index_route_unavailable'
                                                            ) ?? 'Appraisal index route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ request()->is('appraisal*') ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $appraisalLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $indicatorIndexRoute }}"
                                                                data-url="{{ $appraisalIndexRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $message }}"
                                                            >
                                                                {{ __(VW::APR) }}
                                                            </a>
                                                        </li>
                                                        @push(ST::ADM_SCR_PG)
                                                            <script defer src="{{ asset('assets/js/routes/partials/admin/menu/appraisal.js') }}"></script>
                                                        @endpush
                                                    @endcan
                                                    @can(PMC::MNG_GTR)
                                                        @php
                                                            $goalTrackingRoute = Route::has(VW::GL_TRC.'.index')
                                                                ? route(VW::GL_TRC.'.index')
                                                                : (Route::has(Str::kebab(VW::GL_TRC.'.index'))
                                                                ? route(Str::kebab(VW::GL_TRC.'.index'))
                                                                : '#');
                                                            $goalTrackingLinkId = 'goal-tracking-index-link';
                                                            $message = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::GL_TRC,
                                                                'goal_tracking_index_route_unavailable'
                                                            ) ?? 'Goal Tracking route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ (request()->is('goal-tracking*') || request()->is('goal_tracking*')) ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $goalTrackingLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $indicatorIndexRoute }}"
                                                                data-url="{{ $goalTrackingRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $message }}"
                                                            >
                                                                {{ __('Goal Tracking') }}
                                                            </a>
                                                        </li>
                                                        @push(ST::ADM_SCR_PG)
                                                            <script defer src="{{ asset('assets/js/routes/partials/admin/menu/goalTracking.js') }}"></script>
                                                        @endpush
                                                    @endcan
                                                </ul>
                                            </li>
                                        @endif
                                        @if (Gate::check(PMC::MNG_TNG) || Gate::check(PMC::MNG_TNR) || Gate::check(PMC::SHW_TNG) || $isSa)
                                            @php
                                                $isTraining = RF::segment(1) === VW::TNR || RF::segment(1) === VW::TNG;
                                            @endphp
                                            <li class="{{ VC::DSH_IT_MN }} {{ $isTraining ? 'active dash-trigger' : '' }}"
                                                href="#navbar-training" data-toggle="collapse" role="button"
                                                aria-expanded="{{ $isTraining ? 'true' : 'false' }}">
                                                <a class="dash-link" href="#">{{ __('Training Setup') }}
                                                    <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                </a>
                                                <ul class="dash-submenu">
                                                    @can(PMC::MNG_TNG)
                                                        @php
                                                            $trainingIndexRoute = Route::has(VW::TNG.'.index')
                                                                ? route(VW::TNG.'.index')
                                                                : '#';
                                                            $trainingLinkId = 'training-index-link';
                                                            $message = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::TNG,
                                                                'training_index_route_unavailable'
                                                            ) ?? 'Training list route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ request()->is('training*') ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $trainingLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $trainingIndexRoute }}"
                                                                data-url="{{ $trainingIndexRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $message }}"
                                                            >
                                                                {{ __('Training List') }}
                                                            </a>
                                                        </li>
                                                        @push(ST::ADM_SCR_PG)
                                                            <script defer src="{{ asset('assets/js/routes/partials/admin/menu/training.js') }}"></script>
                                                        @endpush
                                                    @endcan
                                                    @can(PMC::MNG_TNR)
                                                        @php
                                                            $trainerIndexRoute = Route::has(VW::TNR.'.index')
                                                                ? route(VW::TNR.'.index')
                                                                : '#';
                                                            $trainerLinkId = 'trainer-index-link';
                                                            $message = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::TNR,
                                                                'trainer_index_route_unavailable'
                                                            ) ?? 'Trainer index route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ request()->is('trainer*') ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $trainerLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $trainerIndexRoute }}"
                                                                data-url="{{ $trainerIndexRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $message }}"
                                                            >
                                                                {{ __('Trainer') }}
                                                            </a>
                                                        </li>
                                                        @push(ST::ADM_SCR_PG)
                                                            <script defer src="{{ asset('assets/js/routes/partials/admin/menu/trainer.js') }}"></script>
                                                        @endpush
                                                    @endcan
                                                </ul>
                                            </li>
                                        @endif
                                        @if (Gate::check(PMC::MNG_JB) ||
                                                Gate::check(PMC::CR_JB) ||
                                                Gate::check(PMC::MNG_JB_APL) ||
                                                Gate::check(PMC::MNG_CST_QT) ||
                                                Gate::check(PMC::SHW_ITV_SCHD) ||
                                                Gate::check(PMC::SHW_CRR) || $isSa)
                                            @php
                                                $segments = [
                                                    VW::C_JB_APL,
                                                    VW::CRR,
                                                    VW::CST_QT,
                                                    VW::ITV_SCD,
                                                    VW::JB,
                                                    VW::JB_APL,
                                                    VW::JB_OB
                                                ];
                                                $kebabSegments = array_map(function($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);
                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isRecruitment = in_array(RF::segment(1), $allSegments);
                                            @endphp
                                            <li class="{{ $isRecruitment ? 'active dash-trigger' : '' }}">
                                                <a class="dash-link" href="#">{{ __('Recruitment Setup') }}
                                                    <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                </a>
                                                <ul class="dash-submenu">
                                                    @can(PMC::MNG_JB)
                                                        @php
                                                            $routeName = RF::route()->getName();
                                                        @endphp
                                                        <li
                                                            class="dash-item {{ $routeName == VW::JB.'.index' || $routeName == VW::JB.'.create' || $routeName == VW::JB.'.edit' || $routeName == VW::JB.'.show' ? 'active' : '' }}">
                                                            @php
                                                                $jobsIndexRoute = Route::has(VW::JB.'.index')
                                                                    ? route(VW::JB.'.index')
                                                                    : '#';
                                                                $jobLinkId = 'job-index-link';
                                                                $message = Utility::fetchLinkMessage(
                                                                    $lang,
                                                                    VW::JB,
                                                                    'job_index_route_unavailable'
                                                                ) ?? 'Jobs route is unavailable. Please contact technical support or your domain administrator.';
                                                            @endphp
                                                            <a
                                                                id="{{ $jobLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $jobsIndexRoute }}"
                                                                data-url="{{ $jobsIndexRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $message }}"
                                                            >
                                                                {{ __('Jobs') }}
                                                            </a>
                                                            @push(ST::ADM_SCR_PG)
                                                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/job.js') }}"></script>
                                                            @endpush
                                                        </li>
                                                    @endcan
                                                    @can(PMC::CR_JB)
                                                        @php
                                                            $jobCreateRoute = Route::has(VW::JB.'.create')
                                                                ? route(VW::JB.'.create')
                                                                : '#';
                                                            $jobCreateLinkId = 'job-create-link';
                                                            $jobCreateMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::JB,
                                                                'job_create_route_unavailable'
                                                            ) ?? 'Job Create route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ RF::route()->getName() == VW::JB.'.create' ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $jobCreateLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $jobCreateRoute }}"
                                                                data-url="{{ $jobCreateRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $jobCreateMessage }}"
                                                            >
                                                                {{ __('Job Create') }}
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can(PMC::MNG_JB_APL)
                                                        @php
                                                            $jobAppRoute = Route::has(VW::JB_APL.'.index')
                                                                ? route(VW::JB_APL.'.index')
                                                                : (Route::has(Str::kebab(VW::JB_APL.'.index'))
                                                                ? route(Str::kebab(VW::JB_APL.'.index'))
                                                                : '#');
                                                            $jobAppLinkId = 'job-application-link';
                                                            $jobAppMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::JB,
                                                                'job_application_index_route_unavailable'
                                                            ) ?? 'Job Application route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ (request()->is('job-application*') || request()->is('job_application*')) ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $jobAppLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $jobAppRoute }}"
                                                                data-url="{{ $jobAppRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $jobAppMessage }}"
                                                            >
                                                                {{ __('Job Application') }}
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can(PMC::MNG_JB_APL)
                                                        @php
                                                            $jobCandRoute = Route::has(VW::JB.'.application.candidate')
                                                                ? route(VW::JB.'.application.candidate')
                                                                : '#';
                                                            $jobCandLinkId = 'job-candidate-link';
                                                            $jobCandMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::JB,
                                                                'job_candidate_route_unavailable'
                                                            ) ?? 'Job Candidate route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ (request()->is('job-application/candidate*') || request()->is('job_application/candidate*')) ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $jobCandLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $jobCandRoute }}"
                                                                data-url="{{ $jobCandRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $jobCandMessage }}"
                                                            >
                                                                {{ __('Job Candidate') }}
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can(PMC::MNG_JB_APL)
                                                        @php
                                                            $jobOnBoardRoute = Route::has(VW::JB.'.on.board')
                                                                ? route(VW::JB.'.on.board')
                                                                : '#';
                                                            $jobOnBoardLinkId = 'job-on-board-link';
                                                            $jobOnBoardMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::JB,
                                                                'job_on_board_route_unavailable'
                                                            ) ?? 'Job On-boarding route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ (request()->is('jobs-onboard*') || request()->is('jobs_onboard*')) ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $jobOnBoardLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $jobOnBoardRoute }}"
                                                                data-url="{{ $jobOnBoardRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $jobOnBoardMessage }}"
                                                            >
                                                                {{ __('Job On-boarding') }}
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can(PMC::MNG_CST_QT)
                                                        @php
                                                            $customQRoute = Route::has(VW::CST_QT.'.index')
                                                                ? route(VW::CST_QT.'.index')
                                                                : (Route::has(Str::kebab(VW::CST_QT.'.index'))
                                                                ? route(Str::kebab(VW::CST_QT.'.index'))
                                                                : '#');
                                                            $customQLinkId = 'custom-question-link';
                                                            $customQMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::CST_QT,
                                                                'custom_question_index_route_unavailable'
                                                            ) ?? 'Custom Question route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ (request()->is('custom-question*') || request()->is('custom_question*')) ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $customQLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $customQRoute }}"
                                                                data-url="{{ $customQRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $customQMessage }}"
                                                            >
                                                                {{ __('Custom Question') }}
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can(PMC::SHW_ITV_SCHD)
                                                        @php
                                                            $intvSchedRoute = Route::has(VW::ITV_SCD.'.index')
                                                                ? route(VW::ITV_SCD.'.index')
                                                                : (Route::has(Str::kebab(VW::ITV_SCD.'.index'))
                                                                ? route(Str::kebab(VW::ITV_SCD.'.index'))
                                                                : '#');
                                                            $intvSchedLinkId = 'interview-schedule-link';
                                                            $intvSchedMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::ITV_SCD,
                                                                'interview_schedule_index_route_unavailable'
                                                            ) ?? 'Interview Schedule route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ request()->is('interview-schedule*') ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $intvSchedLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $intvSchedRoute }}"
                                                                data-url="{{ $intvSchedRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $intvSchedMessage }}"
                                                            >
                                                                {{ __('Interview Schedule') }}
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can(PMC::SHW_CRR)
                                                        @php
                                                            $careerRoute = Route::has(VW::CRR)
                                                                ? route(VW::CRR, [$user?->creatorId(), $lang])
                                                                : '#';
                                                            $careerLinkId = 'career-index-link';
                                                            $careerMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::CRR,
                                                                'career_index_route_unavailable'
                                                            ) ?? 'Career route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ request()->is('career*') ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $careerLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $careerRoute }}"
                                                                data-url="{{ $careerRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $careerMessage }}"
                                                            >
                                                                {{ __('Career') }}
                                                            </a>
                                                        </li>
                                                        @push(ST::ADM_SCR_PG)
                                                            <script defer src="{{ asset('assets/js/routes/partials/admin/menu/jobApplication.js') }}"></script>
                                                        @endpush
                                                    @endcan
                                                </ul>
                                            </li>
                                        @endif
                                        @php
                                            $permissions = [
                                                PMC::MNG_AWD,
                                                PMC::MNG_TRF,
                                                PMC::MNG_RSG,
                                                PMC::MNG_TRV,
                                                PMC::MNG_PRM,
                                                PMC::MNG_CPT,
                                                PMC::MNG_WRN,
                                                PMC::MNG_TRM,
                                                PMC::MNG_ANC,
                                                PMC::MNG_HLD
                                            ];
                                            $hasPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                                            $segments = [
                                                VW::ANC,
                                                VW::AWD,
                                                VW::CPN,
                                                VW::CPT,
                                                VW::HLD,
                                                VW::HLD_CLD,
                                                VW::PLC,
                                                VW::PRM,
                                                VW::RSG,
                                                VW::TMN,
                                                VW::TRF,
                                                VW::TRV,
                                                VW::WRN
                                            ];
                                            $kebabSegments = array_map(function($segment) {
                                                if ($segment === null) return null;
                                                return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                            }, $segments);
                                            $allSegments = array_merge($segments, $kebabSegments);
                                            $isEmployeeManagement = in_array(RF::segment(1), $allSegments);
                                        @endphp
                                        @if ($hasPermission || $isSa)
                                            <li
                                                class="{{ VC::DSH_IT_MN }} {{ $isEmployeeManagement ? 'active dash-trigger' : '' }}">
                                                <a class="dash-link" href="#">{{ __('HR Admin Setup') }}
                                                    <span class="dash-arrow">< data-feather="chevron-right"></ i></span>
                                                </a>
                                                <ul class="dash-submenu">
                                                    @can(PMC::MNG_AWD)
                                                        @php
                                                            $awardIndexRoute = Route::has(VW::AWD.'.index')
                                                                ? route(VW::AWD.'.index')
                                                                : '#';
                                                            $awardIndexLinkId = 'award-index-link';
                                                            $awardIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::AWD,
                                                                'award_index_route_unavailable'
                                                            ) ?? 'Award index route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ request()->is('award*') ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $awardIndexLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $awardIndexRoute }}"
                                                                data-url="{{ $awardIndexRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $awardIndexMessage }}"
                                                            >
                                                                {{ __('Award') }}
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can(PMC::MNG_TRF)
                                                        @php
                                                            $transferIndexRoute = Route::has(VW::TRF.'.index')
                                                                ? route(VW::TRF.'.index')
                                                                : '#';
                                                            $transferIndexLinkId = 'transfer-index-link';
                                                            $transferIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::TRF,
                                                                'transfer_index_route_unavailable'
                                                            ) ?? 'Transfer index route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ request()->is('transfer*') ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $transferIndexLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $transferIndexRoute }}"
                                                                data-url="{{ $transferIndexRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $transferIndexMessage }}"
                                                            >
                                                                {{ __('Transfer') }}
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can(PMC::MNG_RSG)
                                                        @php
                                                            $resignationIndexRoute = Route::has(VW::RSG.'.index')
                                                                ? route(VW::RSG.'.index')
                                                                : '#';
                                                            $resignationIndexLinkId = 'resignation-index-link';
                                                            $resignationIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::RSG,
                                                                'resignation_index_route_unavailable'
                                                            ) ?? 'Resignation index route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ request()->is('resignation*') ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $resignationIndexLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $resignationIndexRoute }}"
                                                                data-url="{{ $resignationIndexRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $resignationIndexMessage }}"
                                                            >
                                                                {{ __('Resignation') }}
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can(PMC::MNG_TRV)
                                                        @php
                                                            $tripIndexRoute = Route::has(VW::TRV.'.index')
                                                                ? route(VW::TRV.'.index')
                                                                : '#';
                                                            $tripIndexLinkId = 'trip-index-link';
                                                            $tripIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::TRV,
                                                                'travel_index_route_unavailable'
                                                            ) ?? 'Travel index route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ request()->is('travel*') ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $tripIndexLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $tripIndexRoute }}"
                                                                data-url="{{ $tripIndexRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $tripIndexMessage }}"
                                                            >
                                                                {{ __('Trip') }}
                                                            </a>
                                                        </li>
                                                    @endcan
                                                    @can(PMC::MNG_PRM)
                                                        @php
                                                            $promotionIndexRoute = Route::has(VW::PRM.'.index')
                                                                ? route(VW::PRM.'.index')
                                                                : '#';
                                                            $promotionIndexLinkId = 'promotion-index-link';
                                                            $promotionIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::PRM,
                                                                'promotion_index_route_unavailable'
                                                            ) ?? 'Promotion index route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ request()->is('promotion*') ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $promotionIndexLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $promotionIndexRoute }}"
                                                                data-url="{{ $promotionIndexRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $promotionIndexMessage }}"
                                                            >
                                                                {{ __('Promotion') }}
                                                            </a>
                                                        </li>
                                                    @endcan
                                                
                                                    @can(PMC::MNG_CPT)
                                                        @php
                                                            $complaintIndexRoute = Route::has(VW::CPL.'.index')
                                                                ? route(VW::CPL.'.index')
                                                                : '#';
                                                            $complaintIndexLinkId = 'complaint-index-link';
                                                            $complaintIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::CPL,
                                                                'complaint_index_route_unavailable'
                                                            ) ?? 'Complaints index route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ request()->is('complaint*') ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $complaintIndexLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $complaintIndexRoute }}"
                                                                data-url="{{ $complaintIndexRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $complaintIndexMessage }}"
                                                            >
                                                                {{ __('Complaints') }}
                                                            </a>
                                                        </li>
                                                    @endcan
                                                
                                                    @can(PMC::MNG_WRN)
                                                        @php
                                                            $warningIndexRoute = Route::has(VW::WRN.'.index')
                                                                ? route(VW::WRN.'.index')
                                                                : '#';
                                                            $warningIndexLinkId = 'warning-index-link';
                                                            $warningIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::WRN,
                                                                'warning_index_route_unavailable'
                                                            ) ?? 'Warning index route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ request()->is('warning*') ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $warningIndexLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $warningIndexRoute }}"
                                                                data-url="{{ $warningIndexRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $warningIndexMessage }}"
                                                            >
                                                                {{ __('Warning') }}
                                                            </a>
                                                        </li>
                                                    @endcan
                                                
                                                    @can(PMC::MNG_TRM)
                                                        @php
                                                            $terminationIndexRoute = Route::has(VW::TMN.'.index')
                                                                ? route(VW::TMN.'.index')
                                                                : '#';
                                                            $terminationIndexLinkId = 'termination-index-link';
                                                            $terminationIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::TMN,
                                                                'termination_index_route_unavailable'
                                                            ) ?? 'Termination index route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ request()->is('termination*') ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $terminationIndexLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $terminationIndexRoute }}"
                                                                data-url="{{ $terminationIndexRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $terminationIndexMessage }}"
                                                            >
                                                                {{ __('Termination') }}
                                                            </a>
                                                        </li>
                                                    @endcan
                                                
                                                    @can(PMC::MNG_ANC)
                                                        @php
                                                            $announcementIndexRoute = Route::has(VW::ANC.'.index')
                                                                ? route(VW::ANC.'.index')
                                                                : '#';
                                                            $announcementIndexLinkId = 'announcement-index-link';
                                                            $announcementIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::ANC,
                                                                'announcement_index_route_unavailable'
                                                            ) ?? 'Announcement index route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ request()->is('announcement*') ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $announcementIndexLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $announcementIndexRoute }}"
                                                                data-url="{{ $announcementIndexRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $announcementIndexMessage }}"
                                                            >
                                                                {{ __('Announcement') }}
                                                            </a>
                                                        </li>
                                                    @endcan
                                                
                                                    @can(PMC::MNG_HLD)
                                                        @php
                                                            $holidaysIndexRoute = Route::has(VW::HLD.'.index')
                                                                ? route(VW::HLD.'.index')
                                                                : '#';
                                                            $holidaysIndexLinkId = 'holidays-index-link';
                                                            $holidaysIndexMessage = Utility::fetchLinkMessage(
                                                                $lang,
                                                                VW::HLD,
                                                                'holidays_index_route_unavailable'
                                                            ) ?? 'Holidays index route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <li class="dash-item {{ request()->is('holiday*') || request()->is('holiday-calendar') ? 'active' : '' }}">
                                                            <a
                                                                id="{{ $holidaysIndexLinkId }}"
                                                                class="dash-link"
                                                                href="{{ $holidaysIndexRoute }}"
                                                                data-url="{{ $holidaysIndexRoute }}"
                                                                data-sv-localized="true"
                                                                data-guard-msg="{{ $holidaysIndexMessage }}"
                                                            >
                                                                {{ __('Holidays') }}
                                                            </a>
                                                        </li>
                                                    @endcan
                                                </ul>
                                                @push(ST::ADM_SCR_PG)
                                                    <script defer src="{{ asset('assets/js/routes/partials/admin/menu/info.js') }}"></script>
                                                @endpush
                                            </li>
                                        @endif
                                        @can(PMC::MNG_EVT)
                                            @php
                                                $eventIndexRoute = Route::has(VW::EVT.'.index')
                                                    ? route(VW::EVT.'.index')
                                                    : '#';
                                                $eventLinkId = 'event-setup-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    VW::EVT,
                                                    'event_index_route_unavailable'
                                                ) ?? 'Event setup route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <li class="dash-item {{ request()->is('event*') ? 'active' : '' }}">
                                                <a
                                                    id="{{ $eventLinkId }}"
                                                    class="dash-link"
                                                    href="{{ $eventIndexRoute }}"
                                                    data-url="{{ $eventIndexRoute }}"
                                                    data-sv-localized="true"
                                                    data-guard-msg="{{ $message }}"
                                                >
                                                    {{ __('Event Setup') }}
                                                </a>
                                            </li>
                                            @push(ST::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/event.js') }}"></script>
                                            @endpush
                                        @endcan
                                        @can(PMC::MNG_MT)
                                            @php
                                                $meetingIndexRoute = Route::has(VW::MT.'.index')
                                                    ? route(VW::MT.'.index')
                                                    : '#';
                                                $meetingLinkId = 'meeting-index-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    VW::MT,
                                                    'meeting_index_route_unavailable'
                                                ) ?? 'Meeting index route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <li class="dash-item {{ request()->is('meeting*') ? 'active' : '' }}">
                                                <a
                                                    id="{{ $meetingLinkId }}"
                                                    class="dash-link"
                                                    href="{{ $meetingIndexRoute }}"
                                                    data-url="{{ $meetingIndexRoute }}"
                                                    data-sv-localized="true"
                                                    data-guard-msg="{{ $message }}"
                                                >
                                                    {{ __('Meeting') }}
                                                </a>
                                            </li>
                                            @push(ST::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/meeting.js') }}"></script>
                                            @endpush
                                        @endcan
                                        @can(PMC::MNG_AST)
                                            @php
                                                $assetSetupRoute = Route::has(VW::ACC_AST.'.index')
                                                    ? route(VW::ACC_AST.'.index')
                                                    : (Route::has(Str::kebab(VW::ACC_AST.'.index'))
                                                    ? route(Str::kebab(VW::ACC_AST.'.index'))
                                                    : '#');
                                                $employeeAssetLinkId = 'employees-asset-setup-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    VW::ACC_AST,
                                                    'account_asset_setup_unavailable'
                                                ) ?? 'Account Assets Setup route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <li class="dash-item {{ (request()->is('account_assets*') || request()->is('account_assets*')) ? 'active' : '' }}">
                                                <a
                                                    id="{{ $employeeAssetLinkId }}"
                                                    class="dash-link"
                                                    href="{{ $assetSetupRoute }}"
                                                    data-url="{{ $assetSetupRoute }}"
                                                    data-sv-localized="true"
                                                    data-guard-msg="{{ $message }}"
                                                >
                                                    {{ __('Employees Asset Setup') }}
                                                </a>
                                            </li>
                                            @push(ST::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/employeeAsset.js') }}"></script>
                                            @endpush
                                        @endcan
                                        @can(PMC::MNG_DOC)
                                            @php
                                                $docSetupRoute = Route::has(VW::DOC_UP.'.index')
                                                    ? route(VW::DOC_UP.'.index')
                                                    : (Route::has(Str::kebab(VW::DOC_UP.'.index'))
                                                    ? route(Str::kebab(VW::DOC_UP.'.index'))
                                                    : '#');
                                                $documentLinkId = 'document-setup-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    VW::DOC,
                                                    'document_index_route_unavailable'
                                                ) ?? 'Document setup route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <li class="dash-item {{ (request()->is('document-upload*') || request()->is('document_upload*')) ? 'active' : '' }}">
                                                <a
                                                    id="{{ $documentLinkId }}"
                                                    class="dash-link"
                                                    href="{{ $docSetupRoute }}"
                                                    data-url="{{ $docSetupRoute }}"
                                                    data-sv-localized="true"
                                                    data-guard-msg="{{ $message }}"
                                                >
                                                    {{ __('Document Setup') }}
                                                </a>
                                            </li>
                                            @push(ST::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/document.js') }}"></script>
                                            @endpush
                                        @endcan
                                        @can(PMC::MNG_CPN_PL)
                                            @php
                                                $companyPolicyRoute = Route::has(VW::CPN_PL.'.index')
                                                    ? route(VW::CPN_PL.'.index')
                                                    : (Route::has(Str::kebab(VW::CPN_PL.'.index'))
                                                    ? route(Str::kebab(VW::CPN_PL.'.index'))
                                                    : '#');
                                                $companyPolicyLinkId = 'company-policy-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    VW::CPN_PL,
                                                    'company_policy_index_unavailable'
                                                ) ?? 'Company policy route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <li class="dash-item {{ (request()->is('company-policies*') || request()->is('company_policies*')) ? 'active' : '' }}">
                                            <a
                                                id="{{ $companyPolicyLinkId }}"
                                                class="dash-link"
                                                href="{{ $companyPolicyRoute }}"
                                                data-url="{{ $companyPolicyRoute }}"
                                                data-sv-localized="true"
                                                data-guard-msg="{{ $message }}"
                                            >
                                                {{ __('Company policy') }}
                                            </a>
                                            </li>
                                            @push(ST::ADM_SCR_PG)
                                            <script defer src="{{ asset('assets/js/routes/partials/admin/menu/companyPolicy.js') }}"></script>
                                            @endpush
                                        @endcan
                                        @if ($user[UsersConstants::COL_TP] === PMC::CPN || 
                                            strtolower($user[UsersConstants::COL_TP]) === 'hr' ||
                                            $isSa)
                                            @php
                                                $segments = [
                                                    VW::ALW_OPT,
                                                    VW::AWD_TP,
                                                    VW::BRC,
                                                    VW::DDT_OPT,
                                                    VW::DOC,
                                                    VW::DPT,
                                                    VW::DSG,
                                                    VW::GL_TP,
                                                    VW::JB_CAT,
                                                    VW::JB_STG,
                                                    VW::LN_OPT,
                                                    VW::LV_TP,
                                                    VW::PFM_TP,
                                                    VW::PY_SLP_TP,
                                                    VW::TMN_TP
                                                ];
                                                $kebabSegments = array_map(function($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);
                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isHrmSetup = in_array(RF::segment(1), $allSegments);
                                                $hrmSetupRoute = Route::has(VW::BRC.'.index')
                                                    ? route(VW::BRC.'.index')
                                                    : '#';
                                                $hrmSystemLinkId = 'hrm-system-setup-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    VW::BRC,
                                                    'hrm_system_setup_route_unavailable'
                                                ) ?? 'Human Resources Management System Setup route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <li class="dash-item {{ $isHrmSetup ? 'active' : '' }}">
                                                <a
                                                    id="{{ $hrmSystemLinkId }}"
                                                    class="dash-link"
                                                    href="{{ $hrmSetupRoute }}"
                                                    data-url="{{ $hrmSetupRoute }}"
                                                    data-sv-localized="true"
                                                    data-guard-msg="{{ $message }}"
                                                >
                                                    {{ __('HRM System Setup') }}
                                                </a>
                                            </li>
                                            @push(ST::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/hrmSystem.js') }}"></script>
                                            @endpush
                                        @endif
                                    </ul>
                                </li>
                            @endif
                        @endif
                        @if ((!empty($userPlan) &&  $userPlan?->{PLC::COL_ACC} == 1) || $isSa)
                            @php
                                $permissions = [
                                    PMC::MNG_CST,
                                    PMC::MNG_VD,
                                    PMC::MNG_PPS,
                                    PMC::MNG_BACC,
                                    PMC::MNG_BTF,
                                    PMC::MNG_INV,
                                    PMC::MNG_RVN,
                                    PMC::MNG_CRD,
                                    PMC::MNG_BIL,
                                    PMC::MNG_PMT,
                                    PMC::MNG_DBT,
                                    PMC::MNG_COA,
                                    PMC::MNG_JNL,
                                    PMC::BLC_RPT,
                                    PMC::LDG_RPT,
                                    PMC::TRL_RPT
                                ];
                                $hasFinancialPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                            @endphp
                            @if ($hasFinancialPermission)
                                @php
                                    $routeNames = ['print_setting'];
                                    $segments = [
                                        VW::BDG,
                                        VW::BIL,
                                        VW::BNK_ACC,
                                        VW::BNK_TRF,
                                        VW::COA,
                                        VW::COA_TP,
                                        VW::CRD_NT,
                                        VW::CST,
                                        VW::CST_FD,
                                        VW::DBT_NT,
                                        VW::EXP,
                                        VW::GL,
                                        VW::INV,
                                        VW::JRN_ET,
                                        VW::PAY,
                                        'payment_methods',
                                        VW::PPS,
                                        VW::PRD_SV_CAT,
                                        VW::PRD_SV_UNT,
                                        VW::RVN,
                                        VW::TX,
                                        VW::VND
                                    ];
                                    $segment2Values = ['ledger', 'balance_sheet', 'trial_balance', 'profit_loss'];
                                    $kebabSegments = array_map(function($segment) {
                                        if ($segment === null) return null;
                                        return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                    }, $segments);
                                    $allSegments = array_merge($segments, $kebabSegments);
                                    $isAccountingModule = in_array(RF::route()->getName(), $routeNames) ||
                                                            in_array(RF::segment(1), $allSegments) ||
                                                            in_array(RF::segment(2), $segment2Values) ||
                                                            (RF::segment(1) == VW::TST &&
                                                            !in_array(RF::segment(2), ['ledger', 'balance_sheet', 'trial_balance']));
                                @endphp
                                <li
                                    class="{{ VC::DSH_IT_MN }}
                                    {{ $isAccountingModule ? ' active dash-trigger' : '' }}">
                                    <a href="#!" class="dash-link">
                                        <span class="dash-micon">
                                            <i class="ti ti-box"></i>
                                        </span>
                                        <span class="dash-mtext">
                                            {{ __('Accounting System') }}
                                        </span>
                                        <span class="dash-arrow">
                                            <i data-feather="chevron-right"></i>
                                        </span>
                                    </a>
                                    <ul class="dash-submenu">
                                        @if (Gate::check(PMC::MNG_BACC) || Gate::check(PMC::MNG_BTF))
                                            @php
                                                $segments = [
                                                    VW::BNK_ACC,
                                                    VW::BNK_TRF
                                                ];
                                                $kebabSegments = array_map(function($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);
                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isBankingModule = in_array(RF::segment(1), $allSegments);
                                            @endphp
                                            <li class="{{ VC::DSH_IT_MN }} {{ $isBankingModule ? 'active dash-trigger' : '' }}">
                                                <a class="dash-link" href="#">{{ __('Banking') }}
                                                    <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                </a>
                                                @php
                                                    $bankAccountRoute = Route::has(VW::BNK_ACC.'.index')
                                                        ? route(VW::BNK_ACC.'.index')
                                                        : (Route::has(Str::kebab(VW::BNK_ACC.'.index'))
                                                        ? route(Str::kebab(VW::BNK_ACC.'.index'))
                                                        : '#');
                                                    $bankAccountLinkId = 'bank-account-index-link';
                                                    $bankAccountMessage = Utility::fetchLinkMessage(
                                                        $lang,
                                                        VW::BNK_ACC,
                                                        'bank_account_index_route_unavailable'
                                                    ) ?? 'Bank Account route is unavailable. Please contact technical support or your domain administrator.';
                                                
                                                    $bankTransferRoute = Route::has(VW::BNK_TRF.'.index')
                                                        ? route(VW::BNK_TRF.'.index')
                                                        : (Route::has(Str::kebab(VW::BNK_TRF.'.index'))
                                                        ? route(Str::kebab(VW::BNK_TRF.'.index'))
                                                        : '#');
                                                    $bankTransferLinkId = 'bank-transfer-index-link';
                                                    $bankTransferMessage = Utility::fetchLinkMessage(
                                                        $lang,
                                                        VW::BNK_ACC,
                                                        'bank_transfer_index_route_unavailable'
                                                    ) ?? 'Transfer route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <ul class="dash-submenu">
                                                    <li class="dash-item {{ RF::route()->getName() == VW::BNK_ACC.'.index' || RF::route()->getName() == VW::BNK_ACC.'.create' || RF::route()->getName() == VW::BNK_ACC.'.edit' ? 'active' : '' }}">
                                                        <a
                                                            id="{{ $bankAccountLinkId }}"
                                                            class="dash-link"
                                                            href="{{ $bankAccountRoute }}"
                                                            data-url="{{ $bankAccountRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $bankAccountMessage }}"
                                                        >
                                                            {{ __('Account') }}
                                                        </a>
                                                    </li>
                                                    <li class="dash-item {{ RF::route()->getName() == VW::BNK_TRF.'.index' || RF::route()->getName() == VW::BNK_TRF.'.create' || RF::route()->getName() == VW::BNK_TRF.'.edit' ? 'active' : '' }}">
                                                        <a
                                                            id="{{ $bankTransferLinkId }}"
                                                            class="dash-link"
                                                            href="{{ $bankTransferRoute }}"
                                                            data-url="{{ $bankTransferRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $bankTransferMessage }}"
                                                        >
                                                            {{ __('Transfer') }}
                                                        </a>
                                                    </li>
                                                </ul>
                                                @push(ST::ADM_SCR_PG)
                                                    <script defer src="{{ asset('assets/js/routes/partials/admin/menu/bank.js') }}"></script>
                                                @endpush
                                            </li>
                                        @endif
                                        @php
                                            $permissions = [
                                                PMC::MNG_CST,
                                                PMC::MNG_PPS,
                                                PMC::MNG_INV,
                                                PMC::MNG_RVN,
                                                PMC::MNG_CRD
                                            ];
                                            $hasTransactionsPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                                        @endphp
                                        @if ($hasTransactionsPermission)
                                            @php
                                                $segments = [
                                                    VW::CRD_NT,
                                                    VW::CST,
                                                    VW::INV,
                                                    VW::PPS,
                                                    VW::RVN
                                                ];
                                                $kebabSegments = array_map(function($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);
                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isCustomerSales = in_array(RF::segment(1), $allSegments);
                                            @endphp
                                            <li class="{{ VC::DSH_IT_MN }} {{ $isCustomerSales ? 'active dash-trigger' : '' }}">
                                                <a class="dash-link" href="#">{{ __('Sales') }}
                                                    <span class="dash-arrow">
                                                        <i data-feather="chevron-right"></i>
                                                    </span>
                                                </a>
                                                @php
                                                    $submenu = [
                                                        [
                                                            'route'   => 'customer.index',
                                                            'label'   => __('Customer'),
                                                            'can'     => PMC::MNG_CST,
                                                            'pattern' => 'customer*',
                                                            'key'     => VW::CST
                                                        ],
                                                        [
                                                            'route'   => VW::PPS . '.index',
                                                            'label'   => __('Estimate'),
                                                            'can'     => PMC::MNG_PPS,
                                                            'pattern' => VW::PPS . '*',
                                                            'key'     => VW::PPS
                                                        ],
                                                        [
                                                            'route'   => VW::INV . '.index',
                                                            'label'   => __('Invoice'),
                                                            'pattern' => VW::INV . '*',
                                                            'key'     => VW::INV
                                                        ],
                                                        [
                                                            'route'   => VW::RVN . '.index',
                                                            'label'   => __('Revenue'),
                                                            'pattern' => VW::RVN . '*',
                                                            'key'     => VW::RVN
                                                        ],
                                                        [
                                                            'route'   => 'credit.note',
                                                            'label'   => __('Credit Note'),
                                                            'pattern' => 'credit.note',
                                                            'key'     => VW::CRD_NT
                                                        ],
                                                    ];
                                                    $guardIds = [];
                                                @endphp
                                                <ul class="dash-submenu">
                                                    @foreach($submenu as $item)
                                                        @if(!isset($item['can']) || Gate::check($item['can']))
                                                            @php
                                                                $routeName = $item['route'];
                                                                $url       = Route::has($routeName) ? route($routeName) : '#';
                                                                $id        = Str::slug($routeName . '-link', '-');
                                                                $guardIds[] = $id;
                                                                $entity    = Str::before($routeName, '.');
                                                                $msgKey    = Str::snake(str_replace('.', '_', $routeName)) . '_route_unavailable';
                                                                $message   = Utility::fetchLinkMessage($lang, $entity, $msgKey)
                                                                            ?? __(':key route is unavailable. Please contact technical support or your domain administrator.', ['key' => $item['key']]);
                                                            @endphp
                                                            <li class="dash-item {{ request()->routeIs($item['pattern']) ? 'active' : '' }}">
                                                                <a
                                                                    id="{{ $id }}"
                                                                    class="dash-link"
                                                                    href="{{ $url }}"
                                                                    data-url="{{ $url }}"
                                                                    data-sv-localized="true"
                                                                    data-guard-msg="{{ $message }}"
                                                                >
                                                                    {{ $item['label'] }}
                                                                </a>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                                @push(ST::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        const bindGuard = id => {
                                                            const listenerAttr = `data-${id}-listener-active`;
                                                            const el = document.getElementById(id);
                                                            if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                                            el.setAttribute(listenerAttr, 'true');
                                                            el.addEventListener('click', event => {
                                                            try {
                                                                const url  = el.getAttribute('data-url');
                                                                const href = el.href.replace(window.location.origin, '').replace(window.location.pathname, '');
                                                                if ((!url || url === '#') && (!href || href === '#')) {
                                                                event.preventDefault();
                                                                const msg           = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                const containerId   = 'toast-container';
                                                                let container       = document.getElementById(containerId);
                                                                if (!container) {
                                                                    container     = document.createElement('div');
                                                                    container.id  = containerId;
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
                                                            observer.observe(document.body, { childList: true, subtree: true });
                                                        };
                                                        @json($guardIds).forEach(bindGuard);
                                                    })();
                                                </script>
                                                @endpush
                                            </li>
                                        @endif
                                        @php
                                            $permissions = [
                                                PMC::MNG_VD,
                                                PMC::MNG_BIL,
                                                PMC::MNG_PMT,
                                                PMC::MNG_DBT
                                            ];
                                            $hasVendorPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                                        @endphp
                                        @if ($hasVendorPermission)
                                            @php
                                                $segments = [
                                                    VW::BIL,
                                                    VW::DBT_NT,
                                                    VW::EXP,
                                                    VW::PAY,
                                                    VW::VND
                                                ];
                                                $kebabSegments = array_map(function($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);
                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isVendorPurchasing = in_array(RF::segment(1), $allSegments);
                                            @endphp
                                            <li
                                                class="{{ VC::DSH_IT_MN }} {{ $isVendorPurchasing ? 'active dash-trigger' : '' }}">
                                                <a class="dash-link" href="#">{{ __('Purchases') }}
                                                    <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                </a>
                                                @php
                                                    $items = [];
                                                    if (Gate::check(PMC::MNG_VD)) {
                                                        $route    = VW::VND . '.index';
                                                        $url      = Route::has($route) ? route($route) : '#';
                                                        $id       = 'vendor-index-link';
                                                        $key      = 'vendor_index_route_unavailable';
                                                        $message  = Utility::fetchLinkMessage($lang, VW::VND, $key)
                                                                    ?? __('Suppiler route is unavailable. Please contact technical support or your domain administrator.');
                                                        $items[]  = $id;
                                                    }
                                                    $route    = VW::BIL . '.index';
                                                    $urlBil   = Route::has($route) ? route($route) : '#';
                                                    $billId   = 'bill-index-link';
                                                    $billKey  = 'bill_index_route_unavailable';
                                                    $billMsg  = Utility::fetchLinkMessage($lang, VW::BIL, $billKey)
                                                                ?? __('Bill route is unavailable. Please contact technical support or your domain administrator.');
                                                    $items[]  = $billId;
                                                    $route    = VW::EXP . '.index';
                                                    $urlExp   = Route::has($route) ? route($route) : '#';
                                                    $expId    = 'exp-index-link';
                                                    $expKey   = 'expense_index_route_unavailable';
                                                    $expMsg   = Utility::fetchLinkMessage($lang, VW::EXP, $expKey)
                                                                ?? __('Expense route is unavailable. Please contact technical support or your domain administrator.');
                                                    $items[]  = $expId;
                                                    $route    = VW::PAY . '.index';
                                                    $urlPay   = Route::has($route) ? route($route) : '#';
                                                    $payId    = 'pay-index-link';
                                                    $payKey   = 'payment_index_route_unavailable';
                                                    $payMsg   = Utility::fetchLinkMessage($lang, VW::PAY, $payKey)
                                                                ?? __('Payment route is unavailable. Please contact technical support or your domain administrator.');
                                                    $items[]  = $payId;
                                                    $route    = 'debit.note';
                                                    $urlDN    = Route::has($route) ? route($route) : '#';
                                                    $dnId     = 'debit-note-link';
                                                    $dnKey    = 'debit_note_route_unavailable';
                                                    $dnMsg    = Utility::fetchLinkMessage($lang, VW::DBT_NT, $dnKey)
                                                                ?? __('Debit Note route is unavailable. Please contact technical support or your domain administrator.');
                                                    $items[]  = $dnId;
                                                @endphp
                                            <ul class="dash-submenu">
                                                @if (Gate::check(PMC::MNG_VD))
                                                    <li class="dash-item {{ RF::segment(1) == 'vendor' ? 'active' : '' }}">
                                                        <a
                                                            id="{{ $id }}"
                                                            class="dash-link"
                                                            href="{{ $url }}"
                                                            data-url="{{ $url }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $message }}"
                                                        >
                                                            {{ __('Suppiler') }}
                                                        </a>
                                                    </li>
                                                @endif
                                                <li class="dash-item {{ RF::route()->getName() === VW::BIL.'.index' ? 'active' : '' }}">
                                                    <a
                                                        id="{{ $billId }}"
                                                        class="dash-link"
                                                        href="{{ $urlBil }}"
                                                        data-url="{{ $urlBil }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ $billMsg }}"
                                                    >
                                                        {{ __('Bill') }}
                                                    </a>
                                                </li>
                                                <li class="dash-item {{ RF::route()->getName() === VW::EXP.'.index' ? 'active' : '' }}">
                                                    <a
                                                        id="{{ $expId }}"
                                                        class="dash-link"
                                                        href="{{ $urlExp }}"
                                                        data-url="{{ $urlExp }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ $expMsg }}"
                                                    >
                                                        {{ __(VW::EXP) }}
                                                    </a>
                                                </li>
                                                <li class="dash-item {{ RF::route()->getName() === VW::PAY.'.index' ? 'active' : '' }}">
                                                    <a
                                                        id="{{ $payId }}"
                                                        class="dash-link"
                                                        href="{{ $urlPay }}"
                                                        data-url="{{ $urlPay }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ $payMsg }}"
                                                    >
                                                        {{ __('Payment') }}
                                                    </a>
                                                </li>
                                                <li class="dash-item {{ RF::route()->getName() === 'debit.note' ? 'active' : '' }}">
                                                    <a
                                                        id="{{ $dnId }}"
                                                        class="dash-link"
                                                        href="{{ $urlDN }}"
                                                        data-url="{{ $urlDN }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ $dnMsg }}"
                                                    >
                                                        {{ __('Debit Note') }}
                                                    </a>
                                                </li>
                                            </ul>
                                            @push(ST::ADM_SCR_PG)
                                                <script defer>
                                                    (() => {
                                                        const bindGuard = id => {
                                                            const listener = `data-${id}-listener-active`;
                                                            const el = document.getElementById(id);
                                                            if (!el || el.getAttribute(listener) === 'true') return;
                                                            el.setAttribute(listener, 'true');
                                                            el.addEventListener('click', event => {
                                                                try {
                                                                    const url  = el.getAttribute('data-url');
                                                                    const href = el.href.replace(window.location.origin, '').replace(window.location.pathname, '');
                                                                    if ((!url || url === '#') && (!href || href === '#')) {
                                                                        event.preventDefault();
                                                                        const msg           = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                                                        const containerId   = 'toast-container';
                                                                        let container       = document.getElementById(containerId);
                                                                        if (!container) {
                                                                            container     = document.createElement('div');
                                                                            container.id  = containerId;
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
                                                            obs.observe(document.body, { childList: true, subtree: true });
                                                        };
                                                        @json($items).forEach(bindGuard);
                                                    })();
                                                </script>
                                            @endpush
                                            </li>
                                        @endif
                                        @php
                                            $permissions = [
                                                PMC::MNG_COA,
                                                PMC::MNG_JNL,
                                                PMC::BLC_RPT,
                                                PMC::LDG_RPT,
                                                PMC::TRL_RPT
                                            ];
                                            $hasChartsPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                                        @endphp
                                        @if ($hasChartsPermission)
                                            @php
                                                $segments = [
                                                    VW::COA,
                                                    VW::JRN_ET
                                                ];
                                                $segment2Values = [
                                                    'balance_sheet',
                                                    'ledger',
                                                    'profit_loss',
                                                    'trial_balance'
                                                ];
                                                $kebabSegments = array_map(function($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);
                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isAccountingReports = in_array(RF::segment(1), $allSegments) || 
                                                                        in_array(RF::segment(2), $segment2Values);
                                            @endphp
                                            <li
                                                class="{{ VC::DSH_IT_MN }} {{ $isAccountingReports
                                                    ? 'active dash-trigger'
                                                    : '' }}">
                                                <a class="dash-link" href="#">
                                                    {{ __('Double Entry') }}
                                                    <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                                </a>
                                                @php
                                                    $routeName = RF::route()->getName();
                                                    $isCoaRoute = in_array(
                                                        $routeName,
                                                        array_merge(
                                                            [
                                                                VW::COA . '.index',
                                                                VW::COA . '.show',
                                                            ],
                                                            array_map(
                                                                fn($r) => Str::kebab(VW::COA . '.' . $r),
                                                                ['index', 'show']
                                                            )
                                                        )
                                                    );
                                                    $isJrnRoute = in_array(
                                                        $routeName,
                                                        array_merge(
                                                            [
                                                                VW::JRN_ET . '.index',
                                                                VW::JRN_ET . '.show',
                                                                VW::JRN_ET . '.edit',
                                                                VW::JRN_ET . '.create',
                                                            ],
                                                            array_map(
                                                                fn($r) => Str::kebab(VW::JRN_ET . '.' . $r),
                                                                ['index', 'show', 'edit', 'create']
                                                            )
                                                        )
                                                    );
                                                    $coaRoute = Route::has(VW::COA.'.index')
                                                        ? route(VW::COA.'.index')
                                                        : (Route::has(Str::kebab(VW::COA.'.index'))
                                                        ? route(Str::kebab(VW::COA.'.index'))
                                                        : '#');
                                                    $coaId = 'chart-of-accounts-link';
                                                    $coaMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        VW::COA,
                                                        'coa_index_route_unavailable'
                                                    ) ?? 'Chart of Accounts route is unavailable. Please contact technical support or your domain administrator.';

                                                    $jrnRoute = Route::has(VW::JRN_ET.'.index')
                                                        ? route(VW::JRN_ET.'.index')
                                                        : (Route::has(Str::kebab(VW::JRN_ET.'.index'))
                                                        ? route(Str::kebab(VW::JRN_ET.'.index'))
                                                        : '#');
                                                    $jrnId = 'journal-account-link';
                                                    $jrnMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        VW::JRN_ET,
                                                        'jrn_et_index_route_unavailable'
                                                    ) ?? 'Journal Account route is unavailable. Please contact technical support or your domain administrator.';

                                                    $ledgerRoute = Route::has(VW::RPT.'.ledger')
                                                        ? route(VW::RPT.'.ledger', 0)
                                                        : '#';
                                                    $ledgerId = 'ledger-summary-link';
                                                    $ledgerMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        VW::RPT,
                                                        'rpt_ledger_route_unavailable'
                                                    ) ?? 'Ledger Summary route is unavailable. Please contact technical support or your domain administrator.';

                                                    $balanceRoute = Route::has(VW::RPT.'.balance.sheet')
                                                        ? route(VW::RPT.'.balance.sheet')
                                                        : '#';
                                                    $balanceId = 'balance-sheet-link';
                                                    $balanceMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        VW::RPT,
                                                        'rpt_balance_sheet_route_unavailable'
                                                    ) ?? 'Balance Sheet route is unavailable. Please contact technical support or your domain administrator.';

                                                    $profitRoute = Route::has(VW::RPT.'.profit.loss')
                                                        ? route(VW::RPT.'.profit.loss')
                                                        : '#';
                                                    $profitId = 'profit-loss-link';
                                                    $profitMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        VW::RPT,
                                                        'rpt_profit_loss_route_unavailable'
                                                    ) ?? 'Profit & Loss route is unavailable. Please contact technical support or your domain administrator.';

                                                    $trialRoute = Route::has(VW::RPT . '.trial.balance')
                                                        ? route(VW::RPT . '.trial.balance')
                                                        : '#';
                                                    $trialId = 'trial-balance-link';
                                                    $trialMsg = Utility::fetchLinkMessage(
                                                        $lang,
                                                        VW::RPT,
                                                        'trial_balance_route_unavailable'
                                                    ) ?? 'Trial Balance route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <ul class="dash-submenu">
                                                    <li class="dash-item {{ $isCoaRoute ? ' active' : '' }}">
                                                        <a
                                                            id="{{ $coaId }}"
                                                            class="dash-link"
                                                            href="{{ $coaRoute }}"
                                                            data-url="{{ $coaRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $coaMsg }}"
                                                        >
                                                            {{ __('Chart of Accounts') }}
                                                        </a>
                                                    </li>
                                                    <li class="dash-item {{ $isJrnRoute ? ' active' : '' }}">
                                                        <a
                                                            id="{{ $jrnId }}"
                                                            class="dash-link"
                                                            href="{{ $jrnRoute }}"
                                                            data-url="{{ $jrnRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $jrnMsg }}"
                                                        >
                                                            {{ __('Journal Account') }}
                                                        </a>
                                                    </li>
                                                    <li class="dash-item {{ $routeName == VW::RPT.'.ledger' ? ' active' : '' }}">
                                                        <a
                                                            id="{{ $ledgerId }}"
                                                            class="dash-link"
                                                            href="{{ $ledgerRoute }}"
                                                            data-url="{{ $ledgerRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $ledgerMsg }}"
                                                        >
                                                            {{ __('Ledger Summary') }}
                                                        </a>
                                                    </li>
                                                    <li class="dash-item {{ $routeName == VW::RPT.'.balance.sheet' ? ' active' : '' }}">
                                                        <a
                                                            id="{{ $balanceId }}"
                                                            class="dash-link"
                                                            href="{{ $balanceRoute }}"
                                                            data-url="{{ $balanceRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $balanceMsg }}"
                                                        >
                                                            {{ __('Balance Sheet') }}
                                                        </a>
                                                    </li>
                                                    <li class="dash-item {{ $routeName == VW::RPT.'.profit.loss' ? ' active' : '' }}">
                                                        <a
                                                            id="{{ $profitId }}"
                                                            class="dash-link"
                                                            href="{{ $profitRoute }}"
                                                            data-url="{{ $profitRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $profitMsg }}"
                                                        >
                                                            {{ __('Profit & Loss') }}
                                                        </a>
                                                    </li>
                                                    <li class="dash-item {{ $routeName == VW::RPT . '.trial.balance' ? ' active' : '' }}">
                                                        <a
                                                            id="{{ $trialId }}"
                                                            class="dash-link"
                                                            href="{{ $trialRoute }}"
                                                            data-url="{{ $trialRoute }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ $trialMsg }}"
                                                        >
                                                            {{ __('Trial Balance') }}
                                                        </a>
                                                    </li>
                                                </ul>
                                                @push(ST::ADM_SCR_PG)
                                                    <script defer src="{{ asset('assets/js/routes/partials/admin/menu/chart.js') }}"></script>
                                                @endpush
                                            </li>
                                        @endif
                                        @if ($user[UsersConstants::COL_TP] == PMC::CPN ||
                                            $user[UsersConstants::COL_TP] == PMC::SA)
                                            @php
                                                $budgetRoute = Route::has(VW::BDG.'.index')
                                                    ? route(VW::BDG.'.index')
                                                    : '#';
                                                $budgetPlannerLinkId = 'budget-planner-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    VW::BDG,
                                                    'budget_index_route_unavailable'
                                                ) ?? 'Budget Planner route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <li class="dash-item {{ RF::segment(1) == VW::BDG ? 'active' : '' }}">
                                                <a
                                                    id="{{ $budgetPlannerLinkId }}"
                                                    class="dash-link"
                                                    href="{{ $budgetRoute }}"
                                                    data-url="{{ $budgetRoute }}"
                                                    data-sv-localized="true"
                                                    data-guard-msg="{{ $message }}"
                                                >
                                                    {{ __('Budget Planner') }}
                                                </a>
                                            </li>
                                            @push(ST::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/budgetPlanner.js') }}"></script>
                                            @endpush
                                        @endif
                                        @if (Gate::check(PMC::MNG_GL))
                                            @php
                                                $financialGoalRoute = Route::has(VW::GL.'.index')
                                                    ? route(VW::GL.'.index')
                                                    : '#';
                                                $financialGoalLinkId = 'financial-goal-index-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    VW::GL,
                                                    'financial_goal_index_route_unavailable'
                                                ) ?? 'Financial Goal route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <li class="dash-item {{ RF::segment(1) == VW::GL ? 'active' : '' }}">
                                                <a
                                                    id="{{ $financialGoalLinkId }}"
                                                    class="dash-link"
                                                    href="{{ $financialGoalRoute }}"
                                                    data-url="{{ $financialGoalRoute }}"
                                                    data-sv-localized="true"
                                                    data-guard-msg="{{ $message }}"
                                                >
                                                    {{ __('Financial Goal') }}
                                                </a>
                                            </li>
                                            @push(ST::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/financialGoal.js') }}"></script>
                                            @endpush                                    
                                        @endif
                                        @php
                                            $permissions = [
                                                PMC::MNG_CT_TX,
                                                PMC::MNG_CT_CAT,
                                                PMC::MNG_CT_UNT,
                                                PMC::MNG_CT_PAY,
                                                PMC::MNG_CT_CST_FD
                                            ];
                                            $hasConstantsPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                                        @endphp
                                        @if ($hasConstantsPermission)
                                            @php
                                               $segments = [
                                                    VW::COA_TP,
                                                    VW::CST_FD,
                                                    VW::PAY_MTD,
                                                    VW::PRD_SV_CAT,
                                                    VW::PRD_SV_UNT,
                                                    VW::TX
                                                ];
                                                $kebabSegments = array_map(function($segment) {
                                                    if ($segment === null) return null;
                                                    return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                                }, $segments);
                                                $allSegments = array_merge($segments, $kebabSegments);
                                                $isConstantSettings = in_array(RF::segment(1), $allSegments);
                                                $accountingSetupRoute = Route::has(VW::TX.'.index')
                                                    ? route(VW::TX.'.index')
                                                    : '#';
                                                $accountLinkId = 'accounting-setup-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    VW::TX,
                                                    'tx_index_route_unavailable'
                                                ) ?? 'Accounting Setup route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <li class="dash-item {{ $isConstantSettings ? 'active dash-trigger' : '' }}">
                                                <a
                                                    id="{{ $accountLinkId }}"
                                                    class="dash-link"
                                                    href="{{ $accountingSetupRoute }}"
                                                    data-url="{{ $accountingSetupRoute }}"
                                                    data-sv-localized="true"
                                                    data-guard-msg="{{ $message }}"
                                                >
                                                    {{ __('Accounting Setup') }}
                                                </a>
                                            </li>
                                            @push(ST::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/accountingSetup.js') }}"></script>
                                            @endpush
                                        @endif
                                        @if (Gate::check(PMC::MNG_PRT))
                                            @php
                                                $printSettingRoute = Route::has('print.setting')
                                                    ? route('print.setting')
                                                    : '#';
                                                $printSettingLinkId = 'print-setting-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    VW::SET,
                                                    'print_settings_route_unavailable'
                                                ) ?? 'Print Settings route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <li
                                                class="dash-item {{ (RF::route()->getName() == 'print-setting' || RF::route()->getName() == 'print_setting') ? 'active' : '' }}">
                                                <a
                                                    id="{{ $printSettingLinkId }}"
                                                    class="dash-link"
                                                    href="{{ $printSettingRoute }}"
                                                    data-url="{{ $printSettingRoute }}"
                                                    data-sv-localized="true"
                                                    data-guard-msg="{{ $message }}"
                                                >
                                                    {{ __('Print Settings') }}
                                                </a>
                                            </li>
                                            @push(ST::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/printSetting.js') }}"></script>
                                            @endpush
                                        @endif
                                    </ul>
                                </li>
                            @endif
                        @endif
                        @if ((!empty($userPlan) &&  $userPlan?->{PLC::COL_CRM} == 1) || $isSa)
                            @php
                                $permissions = [
                                    PMC::MNG_LD,
                                    PMC::MNG_DL,
                                    PMC::MNG_FM_BD,
                                    PMC::MNG_CTC
                                ];
                                $hasActivityPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                            @endphp
                            @if ($hasActivityPermission)
                                @php
                                    $segments = [
                                        VW::CTC,
                                        VW::DL,
                                        VW::FM_BD,
                                        VW::FM_RP,
                                        VW::LBL,
                                        VW::LD,
                                        VW::LD_STG,
                                        VW::PPL,
                                        VW::SRC,
                                        VW::STG
                                    ];
                                    $kebabSegments = array_map(function($segment) {
                                        if ($segment === null) return null;
                                        return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                    }, $segments);
                                    $allSegments = array_merge($segments, $kebabSegments);
                                    $isCrmModule = in_array(RF::segment(1), $allSegments);
                                @endphp
                                <li class="{{ $isCrmModule ? ' active dash-trigger' : '' }}">
                                    <a href="#!" class="dash-link">
                                        <span class="dash-micon">
                                            <i class="ti ti-layers-difference"></i>
                                        </span>
                                        <span class="dash-mtext">{{ __('CRM System') }}</span>
                                        <span class="dash-arrow">
                                            <i data-feather="chevron-right"></i>
                                        </span>
                                    </a>
                                    @php
                                        $segments = [
                                            VW::DL,
                                            VW::FM_BD,
                                            VW::FM_RP,
                                            VW::LBL,
                                            VW::LD,
                                            VW::LD_STG,
                                            VW::PPL,
                                            VW::SRC,
                                            VW::STG
                                        ];
                                        $kebabSegments = array_map(function($segment) {
                                            if ($segment === null) return null;
                                            return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                        }, $segments);
                                        $allSegments = array_merge($segments, $kebabSegments);
                                        $isCrmManagement = in_array(RF::segment(1), $allSegments);
                                    @endphp
                                    <ul class="dash-submenu {{ $isCrmManagement ? 'show' : '' }}">
                                        @can(PMC::MNG_LD)
                                            @php
                                                $ldIndexRoute = Route::has(VW::LD.'.index')
                                                    ? route(VW::LD.'.index')
                                                    : '#';
                                                $leadLinkId = 'ld-index-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    VW::LD,
                                                    'lead_index_route_unavailable'
                                                ) ?? __('Lead setup route is unavailable. Please contact technical support or your domain administrator.');
                                            @endphp
                                            <li class="dash-item {{ RF::route()->getName() == VW::LD.'.list' || RF::route()->getName() == VW::LD.'.index' || RF::route()->getName() == VW::LD.'.show' ? 'active' : '' }}">
                                                <a
                                                    id="{{ $leadLinkId }}"
                                                    class="dash-link"
                                                    href="{{ $ldIndexRoute }}"
                                                    data-url="{{ $ldIndexRoute }}"
                                                    data-sv-localized="true"
                                                    data-guard-msg="{{ $message }}"
                                                >
                                                    {{ __('Leads') }}
                                                </a>
                                            </li>
                                            @push(ST::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/lead.js') }}"></script>
                                            @endpush
                                        @endcan
                                        @can(PMC::MNG_DL)
                                            @php
                                                $dlIndexRoute = Route::has(VW::DL.'.index')
                                                    ? route(VW::DL.'.index')
                                                    : '#';
                                                $deadLinkId = 'dl-index-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    VW::DL,
                                                    'deal_index_route_unavailable'
                                                ) ?? __('Deal setup route is unavailable. Please contact technical support or your domain administrator.');
                                            @endphp
                                            <li class="dash-item {{ RF::route()->getName() == VW::DL.'.list' || RF::route()->getName() == VW::DL.'.index' || RF::route()->getName() == VW::DL.'.show' ? 'active' : '' }}">
                                                <a
                                                    id="{{ $deadLinkId }}"
                                                    class="dash-link"
                                                    href="{{ $dlIndexRoute }}"
                                                    data-url="{{ $dlIndexRoute }}"
                                                    data-sv-localized="true"
                                                    data-guard-msg="{{ $message }}"
                                                >
                                                    {{ __(VW::DL) }}
                                                </a>
                                            </li>
                                            @push(ST::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/deal.js') }}"></script>
                                            @endpush
                                        @endcan
                                        @can(PMC::MNG_FM_BD)
                                            @php
                                                $formBuilderRoute = Route::has(VW::FM_BD.'.index')
                                                    ? route(VW::FM_BD.'.index')
                                                    : (Route::has(Str::kebab(VW::FM_BD.'.index'))
                                                        ? route(Str::kebab(VW::FM_BD.'.index'))
                                                        : '#');
                                                $formBuilderLinkId = 'form-builder-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    VW::FM_BD,
                                                    'form_builder_index_route_unavailable'
                                                ) ?? 'Form Builder route is unavailable. Please contact technical support or your domain administrator.';
                                            @endphp
                                            <li class="dash-item {{ RF::segment(1) == VW::FM_BD || RF::segment(1) == Str::kebab(VW::FM_BD) || RF::segment(1) == VW::FM_RP || RF::segment(1) == Str::kebab(VW::FM_RP) ? 'active open' : '' }}">
                                                <a
                                                    id="{{ $formBuilderLinkId }}"
                                                    class="dash-link"
                                                    href="{{ $formBuilderRoute }}"
                                                    data-url="{{ $formBuilderRoute }}"
                                                    data-sv-localized="true"
                                                    data-guard-msg="{{ $message }}"
                                                >
                                                    {{ __('Form Builder') }}
                                                </a>
                                            </li>
                                            @push(ST::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/formBuilder.js') }}"></script>
                                            @endpush
                                        @endcan
                                        @can(PMC::MNG_CTC)
                                            @php
                                                $ctcIndexRoute = Route::has(VW::CTC.'.index')
                                                    ? route(VW::CTC.'.index')
                                                    : '#';
                                                $contractLinkId = 'ctc-index-link';
                                                $message = Utility::fetchLinkMessage(
                                                    $lang,
                                                    VW::CTC,
                                                    'contract_index_route_unavailable'
                                                ) ?? __('Contract setup route is unavailable. Please contact technical support or your domain administrator.');
                                            @endphp
                                            <li class="dash-item {{ RF::route()->getName() == VW::CTC.'.index' || RF::route()->getName() == VW::CTC.'.show' ? 'active' : '' }}">
                                                <a
                                                    id="{{ $contractLinkId }}"
                                                    class="dash-link"
                                                    href="{{ $ctcIndexRoute }}"
                                                    data-url="{{ $ctcIndexRoute }}"
                                                    data-sv-localized="true"
                                                    data-guard-msg="{{ $message }}"
                                                >
                                                    {{ __(VW::CTC) }}
                                                </a>
                                            </li>
                                            @push(ST::ADM_SCR_PG)
                                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/contract.js') }}"></script>
                                            @endpush
                                        @endcan
                                    </ul>
                                </li>
                            @endif
                            @php
                                $permissions = [
                                    PMC::MNG_LD_ST,
                                    PMC::MNG_PPL,
                                    PMC::MNG_SRC,
                                    PMC::MNG_LB,
                                    PMC::MNG_ST
                                ];
                                $hasStagesPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                            @endphp
                            @if ($hasStagesPermission)
                                @php
                                   $segments = [
                                        VW::COA_TP,
                                        VW::CST_FD,
                                        VW::LBL,
                                        VW::LD_STG,
                                        VW::PAY_MTD,
                                        VW::PPL,
                                        VW::PRD_SV_CAT,
                                        VW::PRD_SV_UNT,
                                        VW::SRC,
                                        VW::STG
                                    ];
                                    $kebabSegments = array_map(function($segment) {
                                        if ($segment === null) return null;
                                        return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                    }, $segments);
                                    $allSegments = array_merge($segments, $kebabSegments);
                                    $isCrmSetup = in_array(RF::segment(1), $allSegments);
                                    $crmSetupRoute = Route::has(VW::PPL.'.index')
                                        ? route(VW::PPL.'.index')
                                        : '#';
                                    $crmSystemLinkId = 'crm-system-setup-link';
                                    $message = Utility::fetchLinkMessage(
                                        $lang,
                                        VW::PPL,
                                        'pipeline_index_route_unavailable'
                                    ) ?? 'Pipeline setup route is unavailable. Please contact technical support or your domain administrator.';
                                @endphp
                                <li class="dash-item {{ $isCrmSetup ? 'active dash-trigger' : '' }}">
                                    <a
                                        id="{{ $crmSystemLinkId }}"
                                        class="dash-link"
                                        href="{{ $crmSetupRoute }}"
                                        data-url="{{ $crmSetupRoute }}"
                                        data-sv-localized="true"
                                        data-guard-msg="{{ $message }}"
                                    >
                                        {{ __('CRM System Setup') }}
                                    </a>
                                </li>
                                @push(ST::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/partials/admin/menu/crmSystem.js') }}"></script>
                                @endpush
                            @endif
                        @endif
                    </ul>
                @endif
                {{-- <!--------------------- End CRM -----------------------------------> --}}
                {{-- <!--------------------- Start Project -----------------------------------> --}}
                @if ((!empty($userPlan) && $userPlan?->{PLC::COL_PJ} == 1) || $isSa)
                    @if (Gate::check(PMC::MNG_PRJ))
                        @php
                            $segments = [
                                VW::BUG_RPT,
                                VW::BUG_STT,
                                VW::CLD,
                                VW::PRJ,
                                VW::PRJ_TSK_STG,
                                VW::PRJ_RPT,
                                VW::TSKB,
                                VW::TMS_LT
                            ];
                            $kebabSegments = array_map(function($segment) {
                                if ($segment === null) return null;
                                return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                            }, $segments);
                            $allSegments = array_merge($segments, $kebabSegments);
                            $isProjectManagement = in_array(RF::segment(1), $allSegments);
                        @endphp
                        <li class="{{ VC::DSH_IT_MN }} {{ $isProjectManagement ? 'active dash-trigger' : '' }}">
                            <a href="#!" class="dash-link">
                                <span class="dash-micon">
                                    <i class="ti ti-share"></i>
                                </span>
                                <span class="dash-mtext">{{ __('Project System') }}</span>
                                <span class="dash-arrow">
                                    <i data-feather="chevron-right"></i>
                                </span>
                            </a>
                            <ul class="dash-submenu">
                                @php
                                    $projectIndexRoute = Route::has(VW::PRJ.'.index')
                                        ? route(VW::PRJ.'.index')
                                        : '#';
                                    $projectLinkId = 'projects-index-link';
                                    $message = Utility::fetchLinkMessage(
                                        $lang,
                                        VW::PRJ,
                                        'project_index_route_unavailable'
                                    ) ?? 'Projects route is unavailable. Please contact technical support or your domain administrator.';
                                @endphp
                                <li
                                    class="dash-item {{ RF::segment(1) == VW::PRJ || RF::route()->getName() == VW::PRJ.'.list' || RF::route()->getName() == VW::PRJ.'.index' || RF::route()->getName() == VW::PRJ.'.show' || request()->is('projects/*') ? 'active' : '' }}"
                                >
                                    <a
                                        id="{{ $projectLinkId }}"
                                        class="dash-link"
                                        href="{{ $projectIndexRoute }}"
                                        data-url="{{ $projectIndexRoute }}"
                                        data-sv-localized="true"
                                        data-guard-msg="{{ $message }}"
                                    >
                                        {{ __('Projects') }}
                                    </a>
                                </li>
                                @push(ST::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/partials/admin/menu/project.js') }}"></script>
                                @endpush                            
                                @can(PMC::MNG_PRJ_TSK)
                                    @php
                                        $tasksRoute = Route::has(VW::TSKB.'.view')
                                            ? route(VW::TSKB.'.view', 'list')
                                            : '#';
                                        $taskLinkId = 'tasks-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            VW::TSK,
                                            'taskboard_view_route_unavailable'
                                        ) ?? 'Tasks route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <li class="dash-item {{ request()->is('taskboard*') ? 'active' : '' }}">
                                        <a
                                            id="{{ $taskLinkId }}"
                                            class="dash-link"
                                            href="{{ $tasksRoute }}"
                                            data-url="{{ $tasksRoute }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ $message }}"
                                        >
                                            {{ __('Tasks') }}
                                        </a>
                                    </li>
                                    @push(ST::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/task.js') }}"></script>
                                    @endpush                            
                                @endcan
                                @can(PMC::MNG_TS)
                                    @php
                                        $timesheetListRoute = Route::has(VW::TMS.'.list')
                                            ? route(VW::TMS.'.list')
                                            : '#';
                                        $timeSheetLinkId = 'timesheet-list-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            VW::TMS,
                                            'timesheet_list_route_unavailable'
                                        ) ?? 'Timesheet route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <li class="dash-item {{ (request()->is('timesheet-list*') || request()->is('timesheet_list*')) ? 'active' : '' }}">
                                        <a
                                            id="{{ $timeSheetLinkId }}"
                                            class="dash-link"
                                            href="{{ $timesheetListRoute }}"
                                            data-url="{{ $timesheetListRoute }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ $message }}"
                                        >
                                            {{ __('Timesheet') }}
                                        </a>
                                    </li>
                                    @push(ST::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/timesheet.js') }}"></script>
                                    @endpush
                                @endcan
                                @can(PMC::MNG_BUG_RPT)
                                    @php
                                        $bugViewRoute = Route::has(VW::BUG.'.view')
                                            ? route(VW::BUG.'.view', 'list')
                                            : '#';
                                        $bugViewLinkId = 'bug-view-list-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            VW::BUG,
                                            'bug_view_route_unavailable'
                                        ) ?? 'Bug route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <li class="dash-item {{ (request()->is('bugs-report*') || request()->is('bugs_report*')) ? 'active' : '' }}">
                                        <a
                                            id="{{ $bugViewLinkId }}"
                                            class="dash-link"
                                            href="{{ $bugViewRoute }}"
                                            data-url="{{ $bugViewRoute }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ $message }}"
                                        >
                                            {{ __('Bug') }}
                                        </a>
                                    </li>
                                    @push(ST::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/bug.js') }}"></script>
                                    @endpush
                                @endcan
                                @can(PMC::MNG_PRJ_TSK)
                                    @php
                                        $taskCalendarRoute = Route::has(VW::TSK.'.calendar')
                                            ? route(VW::TSK.'.calendar', ['all'])
                                            : '#';
                                        $taskCalendarLinkId = 'task-calendar-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            VW::TSK,
                                            'tsk_calendar_route_unavailable'
                                        ) ?? 'Task Calendar route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <li class="dash-item {{ request()->is('calendar*') ? 'active' : '' }}">
                                        <a
                                            id="{{ $taskCalendarLinkId }}"
                                            class="dash-link"
                                            href="{{ $taskCalendarRoute }}"
                                            data-url="{{ $taskCalendarRoute }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ $message }}"
                                        >
                                            {{ __('Task Calendar') }}
                                        </a>
                                    </li>
                                    @push(ST::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/taskCalendarLink.js') }}"></script>
                                    @endpush
                                @endcan
                                @if ($user[UsersConstants::COL_TP] != PMC::SA)
                                    @php
                                        $trackerRoute = Route::has('time.tracker')
                                            ? route('time.tracker')
                                            : '#';
                                        $trackerLinkId = 'tracker-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            VW::TMT,
                                            'time_tracker_route_unavailable'
                                        ) ?? 'Tracker route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <li class="dash-item {{ (RF::segment(1) == 'time-trackers' || RF::segment(1) == VW::TMT) ? 'active open' : '' }}">
                                        <a
                                            id="{{ $trackerLinkId }}"
                                            class="dash-link"
                                            href="{{ $trackerRoute }}"
                                            data-url="{{ $trackerRoute }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ $message }}"
                                        >
                                            {{ __('Tracker') }}
                                        </a>
                                    </li>
                                    @push(ST::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/tracker.js') }}"></script>
                                    @endpush
                                @endif
                                @if ($user[UsersConstants::COL_TP] == PMC::CPN ||
                                    strtolower($user[UsersConstants::COL_TP]) == 'employee' || 
                                    $user[UsersConstants::COL_TP] == PMC::SA)
                                    @php
                                        $projectReportRoute = Route::has(VW::PRJ_RPT.'.index')
                                            ? route(VW::PRJ_RPT.'.index')
                                            : (Route::has(Str::kebab(VW::PRJ_RPT.'.index'))
                                                ? route(Str::kebab(VW::PRJ_RPT.'.index'))
                                                : '#');
                                        $projectReportLinkId = 'project-report-index-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            VW::PRJ_RPT,
                                            'project_report_index_route_unavailable'
                                        ) ?? 'Project Report route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <li class="dash-item {{ RF::route()->getName() == VW::PRJ_RPT.'.index' || RF::route()->getName() == VW::PRJ_RPT.'.show' ? 'active' : '' }}">
                                        <a
                                            id="{{ $projectReportLinkId }}"
                                            class="dash-link"
                                            href="{{ $projectReportRoute }}"
                                            data-url="{{ $projectReportRoute }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ $message }}"
                                        >
                                            {{ __('Project Report') }}
                                        </a>
                                    </li>
                                    @push(ST::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/projectReport.js') }}"></script>
                                    @endpush
                                @endif
                                @php
                                    $permissions = [
                                        PMC::MNG_PRJ_TSK_STG,
                                        PMC::MNG_BUG_STT
                                    ];
                                    $hasStatusManagementPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                                @endphp
                                @if ($hasStatusManagementPermission)
                                    @php
                                       $segments = [
                                            VW::BUG_STT,
                                            VW::PRJ_TSK_STG
                                        ];
                                        $kebabSegments = array_map(function($segment) {
                                            if ($segment === null) return null;
                                            return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                                        }, $segments);
                                        $allSegments = array_merge($segments, $kebabSegments);
                                        $isProjectSetup = in_array(RF::segment(1), $allSegments);
                                    @endphp
                                    <li class="{{ VC::DSH_IT_MN }} {{ $isProjectSetup ? 'active dash-trigger' : '' }}">
                                        <a class="dash-link" href="#">
                                            {{ __('Project System Setup') }}
                                            <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                        </a>
                                        <ul class="dash-submenu">
                                            @can(PMC::MNG_PRJ_TSK_STG)
                                                @php
                                                    $projectTaskStagesRoute = Route::has(VW::PRJ_TSK_STG.'.index')
                                                        ? route(VW::PRJ_TSK_STG.'.index')
                                                        : (Route::has(Str::kebab(VW::PRJ_TSK_STG.'.index'))
                                                            ? route(Str::kebab(VW::PRJ_TSK_STG.'.index'))
                                                            : '#');
                                                    $projectTaskStagesLinkId = 'project-task-stages-index-link';
                                                    $message = Utility::fetchLinkMessage(
                                                        $lang,
                                                        VW::PRJ_TSK_STG,
                                                        'project_task_stages_index_route_unavailable'
                                                    ) ?? 'Project Task Stages route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <li class="dash-item {{ RF::route()->getName() == VW::PRJ_TSK_STG.'.index' ? 'active' : '' }}">
                                                    <a
                                                        id="{{ $projectTaskStagesLinkId }}"
                                                        class="dash-link"
                                                        href="{{ $projectTaskStagesRoute }}"
                                                        data-url="{{ $projectTaskStagesRoute }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ $message }}"
                                                    >
                                                        {{ __('Project Task Stages') }}
                                                    </a>
                                                </li>
                                                @push(ST::ADM_SCR_PG)
                                                    <script defer src="{{ asset('assets/js/routes/partials/admin/menu/projectTaskStages.js') }}"></script>
                                                @endpush
                                            @endcan
                                            @can(PMC::MNG_BUG_STT)
                                                @php
                                                    $bugStatusRoute = Route::has(VW::BUG_STT.'.index')
                                                        ? route(VW::BUG_STT.'.index')
                                                        : (Route::has(Str::kebab(VW::BUG_STT.'.index'))
                                                            ? route(Str::kebab(VW::BUG_STT.'.index'))
                                                            : '#');
                                                    $bugStatusLinkId = 'bug-status-index-link';
                                                    $message = Utility::fetchLinkMessage(
                                                        $lang,
                                                        VW::BUG_STT,
                                                        'bug_status_index_route_unavailable'
                                                    ) ?? 'Bug Status route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <li class="dash-item {{ RF::route()->getName() == VW::BUG_STT.'.index' ? 'active' : '' }}">
                                                    <a
                                                        id="{{ $bugStatusLinkId }}"
                                                        class="dash-link"
                                                        href="{{ $bugStatusRoute }}"
                                                        data-url="{{ $bugStatusRoute }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ $message }}"
                                                    >
                                                        {{ __('Bug Status') }}
                                                    </a>
                                                </li>
                                                @push(ST::ADM_SCR_PG)
                                                    <script defer src="{{ asset('assets/js/routes/partials/admin/menu/bugStatus.js') }}"></script>
                                                @endpush
                                            @endcan
                                        </ul>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                @endif
                {{-- <!--------------------- End Project -----------------------------------> --}}
                {{-- <!--------------------- Start User Managaement System -----------------------------------> --}}
                @php
                    $userTypes = [PMC::SA, PMC::ADM];
                    $permissions = [
                        PMC::MNG_USER,
                        PMC::MNG_ROLE,
                        PMC::MNG_CLT
                    ];
                    $hasUserType = in_array($user[UsersConstants::COL_TP], $userTypes);
                    $hasUserAdminPermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                @endphp
                @if ($hasUserAdminPermission || $isSa)
                    @php
                       $segments = [
                            VW::CLT,
                            VW::RL,
                            VW::USR,
                            VW::USR_LG
                        ];
                        $kebabSegments = array_map(function($segment) {
                            if ($segment === null) return null;
                            return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                        }, $segments);
                        $allSegments = array_merge($segments, $kebabSegments);
                        $isUserManagement = in_array(RF::segment(1), $allSegments);
                    @endphp
                    <li class="{{ $isUserManagement ? ' active dash-trigger' : '' }}">
                        <a href="#!" class="dash-link">
                            <span class="dash-micon">
                                <i class="{{ VC::TI_USRS }}"></i>
                            </span>
                            <span class="dash-mtext">{{ __('User Management') }}</span>
                            <span class="dash-arrow">
                                <i data-feather="chevron-right"></i>
                            </span>
                        </a>
                        <ul class="dash-submenu">
                            @can(PMC::MNG_USER)
                                @php
                                    $userIndexRoute = Route::has(VW::USR.'.index')
                                        ? route(VW::USR.'.index')
                                        : '#';
                                    $userLinkId = 'user-index-link';
                                    $message = Utility::fetchLinkMessage(
                                        $lang,
                                        VW::USR,
                                        'user_index_route_unavailable'
                                    ) ?? __('User route is unavailable. Please contact technical support or your domain administrator.');
                                @endphp
                                <li class="dash-item {{ RF::route()->getName() == VW::USR.'.index' || RF::route()->getName() == VW::USR.'.create' || RF::route()->getName() == VW::USR.'.edit' || RF::route()->getName() == VW::USR.'.log' ? 'active' : '' }}">
                                    <a
                                        id="{{ $userLinkId }}"
                                        class="dash-link"
                                        href="{{ $userIndexRoute }}"
                                        data-url="{{ $userIndexRoute }}"
                                        data-sv-localized="true"
                                        data-guard-msg="{{ $message }}"
                                    >
                                        {{ __('User') }}
                                    </a>
                                </li>
                                @push(ST::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/partials/admin/menu/userLink.js') }}"></script>
                                @endpush
                            @endcan
                            @can(PMC::MNG_ROLE)
                                @php
                                    $roleIndexRoute = Route::has(VW::RL.'.index')
                                        ? route(VW::RL.'.index')
                                        : '#';
                                    $roleLinkId = 'role-index-link';
                                    $message = Utility::fetchLinkMessage(
                                        $lang,
                                        VW::RL,
                                        'role_index_route_unavailable'
                                    ) ?? 'Role route is unavailable. Please contact technical support or your domain administrator.';
                                @endphp
                                <li class="dash-item {{ RF::route()->getName() == VW::RL.'.index' || RF::route()->getName() == VW::RL.'.create' || RF::route()->getName() == VW::RL.'.edit' ? 'active' : '' }}">
                                    <a
                                        id="{{ $roleLinkId }}"
                                        class="dash-link"
                                        href="{{ $roleIndexRoute }}"
                                        data-url="{{ $roleIndexRoute }}"
                                        data-sv-localized="true"
                                        data-guard-msg="{{ $message }}"
                                    >
                                        {{ __('Role') }}
                                    </a>
                                </li>
                                @push(ST::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/partials/admin/menu/role.js') }}"></script>
                                @endpush
                            @endcan
                            @can(PMC::MNG_CLT)
                                @php
                                    $clientsIndexRoute = Route::has(VW::CLT.'.index')
                                        ? route(VW::CLT.'.index')
                                        : '#';
                                    $clientLinkId = 'clients-index-link';
                                    $message = Utility::fetchLinkMessage(
                                        $lang,
                                        VW::CLT,
                                        'client_index_route_unavailable'
                                    ) ?? 'Clients route is unavailable. Please contact technical support or your domain administrator.';
                                @endphp
                                <li class="dash-item {{ RF::route()->getName() == VW::CLT.'.index' || RF::segment(1) == VW::CLT || RF::route()->getName() == VW::CLT.'.edit' ? 'active' : '' }}">
                                    <a
                                        id="{{ $clientLinkId }}"
                                        class="dash-link"
                                        href="{{ $clientsIndexRoute }}"
                                        data-url="{{ $clientsIndexRoute }}"
                                        data-sv-localized="true"
                                        data-guard-msg="{{ $message }}"
                                    >
                                        {{ __('Clients') }}
                                    </a>
                                </li>
                                @push(ST::ADM_SCR_PG)
                                    <script defer src="{{ asset('assets/js/routes/partials/admin/menu/client.js') }}"></script>
                                @endpush
                            @endcan
                                {{--                                    @can(PMC::MNG_USER) --}}
                                {{--                                        <li class="dash-item {{ (RF::route()->getName() == VW::USR.'.index' || RF::segment(1) == VW::USR || RF::route()->getName() == VW::USR.'.edit') ? ' active' : '' }}"> --}}
                                {{--                                            <a class="dash-link" href="{{ routVW::USR.eusers.userlog') }}">{{__('User Logs')}}</a> --}}
                                {{--                                        </li> --}}
                                {{--                                    @endcan --}}
                        </ul>
                    </li>
                @endif
                {{-- <!--------------------- End User Managaement System-----------------------------------> --}}
                {{-- <!--------------------- Start Products System -----------------------------------> --}}
                @if (Gate::check(PMC::MNG_PRD_SV) || $isSa)
                    <li class="{{ VC::DSH_IT_MN }}">
                        <a href="#!" class="dash-link">
                            <span class="dash-micon">
                                <i class="ti ti-shopping-cart"></i>
                            </span>
                            <span class="dash-mtext">{{ __('Products System') }}</span>
                            <span class="dash-arrow">
                                <i data-feather="chevron-right"></i>
                            </span>
                        </a>
                        @php
                            $prodSvRoute = Route::has(VW::PRD_SV.'.index')
                                ? route(VW::PRD_SV.'.index')
                                : (Route::has(Str::kebab(VW::PRD_SV.'.index'))
                                    ? route(Str::kebab(VW::PRD_SV.'.index'))
                                    : '#');
                            $prodSvId = 'product-services-index-link';
                            $prodSvMsg = Utility::fetchLinkMessage(
                                $lang,
                                VW::PRD_SV,
                                'product_services_index_route_unavailable'
                            ) ?? 'Product & Services route is unavailable. Please contact technical support or your domain administrator.';
                            $prodStkRoute = Route::has(VW::PRD_STK.'.index')
                                ? route(VW::PRD_STK.'.index')
                                : (Route::has(Str::kebab(VW::PRD_STK.'.index'))
                                    ? route(Str::kebab(VW::PRD_STK.'.index'))
                                    : '#');
                            $prodStkId = 'product-stock-index-link';
                            $prodStkMsg = Utility::fetchLinkMessage(
                                $lang,
                                VW::PRD_STK,
                                'product_stock_index_route_unavailable'
                            ) ?? 'Product Stock route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        <ul class="dash-submenu">
                            <li class="dash-item {{ RF::segment(1) == VW::PRD_SV || RF::segment(1) == Str::kebab(VW::PRD_SV) ? 'active' : '' }}">
                                <a
                                    id="{{ $prodSvId }}"
                                    class="dash-link"
                                    href="{{ $prodSvRoute }}"
                                    data-url="{{ $prodSvRoute }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $prodSvMsg }}"
                                >
                                    {{ __('Product & Services') }}
                                </a>
                            </li>
                            <li class="dash-item {{ RF::segment(1) == VW::PRD_STK || RF::segment(1) == Str::kebab(VW::PRD_STK) ? 'active' : '' }}">
                                <a
                                    id="{{ $prodStkId }}"
                                    class="dash-link"
                                    href="{{ $prodStkRoute }}"
                                    data-url="{{ $prodStkRoute }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $prodStkMsg }}"
                                >
                                    {{ __('Product Stock') }}
                                </a>
                            </li>
                        </ul>
                        @push(ST::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/partials/admin/menu/productService.js') }}"></script>
                        @endpush
                    </li>
                @endif
                {{-- <!--------------------- End Products System -----------------------------------> --}}
                {{-- <!--------------------- Start POs System -----------------------------------> --}}
                @if ((!empty($userPlan) && $userPlan?->{PLC::COL_POS} == 1) || $isSa)
                    @php
                        $permissions = [
                            PMC::MNG_WRH,
                            PMC::MNG_PRC,
                            PMC::MNG_POS,
                            PMC::MNG_PRT
                        ];
                        $hasPurchasePermission = collect($permissions)->some(fn($permission) => Gate::check($permission));
                    @endphp
                    @if ($hasPurchasePermission)
                        @php
                           $segments = [
                                VW::PRC,
                                VW::WRH
                            ];
                            $routeNames = [
                                VW::POS . '.barcode',
                                VW::POS . '.print',
                                VW::POS . '.show'
                            ];
                            $kebabSegments = array_map(function($segment) {
                                if ($segment === null) return null;
                                return str_replace('_', '-', strtolower(preg_replace('/([A-Z])/', '-$1', $segment)));
                            }, $segments);
                            $allSegments = array_merge($segments, $kebabSegments);
                            $isPosModule = in_array(RF::segment(1), $allSegments) || 
                                            in_array(RF::route()->getName(), $routeNames);
                        @endphp
                        <li
                            class="{{ VC::DSH_IT_MN }} {{ $isPosModule ? ' active dash-trigger' : '' }}">
                            <a href="#!" class="dash-link">
                                <span class="dash-micon"><i class="ti ti-layers-difference"></i></span>
                                <span class="dash-mtext">{{ __('POS System') }}</span>
                                <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="dash-submenu {{ RF::segment(1) == VW::WRH ||
                                RF::segment(1) == VW::PRC ||
                                RF::route()->getName() == VW::POS . '.barcode' ||
                                RF::route()->getName() == VW::POS . '.print' ||
                                RF::route()->getName() == VW::POS . '.show'
                                    ? 'show'
                                    : '' }}">
                                @can(PMC::MNG_WRH)
                                    @php
                                        $warehouseIndexRoute = Route::has(VW::WRH.'.index')
                                            ? route(VW::WRH.'.index')
                                            : '#';
                                        $warehouseLinkId = 'warehouse-index-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            VW::WRH,
                                            'warehouse_index_route_unavailable'
                                        ) ?? 'Warehouse route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <li class="dash-item {{ RF::route()->getName() == VW::WRH.'.index' || RF::route()->getName() == VW::WRH.'.show' ? 'active' : '' }}">
                                        <a
                                            id="{{ $warehouseLinkId }}"
                                            class="dash-link"
                                            href="{{ $warehouseIndexRoute }}"
                                            data-url="{{ $warehouseIndexRoute }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ $message }}"
                                        >
                                            {{ __('Warehouse') }}
                                        </a>
                                    </li>
                                    @push(ST::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/warehouse.js') }}"></script>
                                    @endpush
                                @endcan
                                @can(PMC::MNG_PRC)
                                    @php
                                        $purchaseIndexRoute = Route::has(VW::PRC.'.index')
                                            ? route(VW::PRC.'.index')
                                            : '#';
                                        $purchaseLinkId = 'purchase-index-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            VW::PRC,
                                            'purchase_index_route_unavailable'
                                        ) ?? 'Purchase route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <li class="dash-item {{ RF::route()->getName() == VW::PRC.'.index' || RF::route()->getName() == VW::PRC.'.create' || RF::route()->getName() == VW::PRC.'.edit' || RF::route()->getName() == VW::PRC.'.show' ? 'active' : '' }}">
                                        <a
                                            id="{{ $purchaseLinkId }}"
                                            class="dash-link"
                                            href="{{ $purchaseIndexRoute }}"
                                            data-url="{{ $purchaseIndexRoute }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ $message }}"
                                        >
                                            {{ __('Purchase') }}
                                        </a>
                                    </li>
                                    @push(ST::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/purchase.js') }}"></script>
                                    @endpush
                                @endcan
                                @can(PMC::MNG_POS)
                                    @php
                                        $posAddRoute = Route::has(VW::POS.'.index')
                                            ? route(VW::POS.'.index')
                                            : '#';
                                        $posReportRoute = Route::has(VW::POS.'.report')
                                            ? route(VW::POS.'.report')
                                            : '#';
                                        $posAddId = 'pos-add-index-link';
                                        $posReportId = 'pos-report-index-link';
                                        $posAddMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            VW::POS,
                                            'pos_index_route_unavailable'
                                        ) ?? __('POS Setup route is unavailable. Please contact technical support or your domain administrator.');
                                        $posReportMsg = Utility::fetchLinkMessage(
                                            $lang,
                                            VW::POS,
                                            'pos_report_route_unavailable'
                                        ) ?? __('POS Report route is unavailable. Please contact technical support or your domain administrator.');
                                    @endphp
                                    <li class="dash-item {{ RF::route()->getName() == VW::POS.'.index' ? 'active' : '' }}">
                                        <a
                                            id="{{ $posAddId }}"
                                            class="dash-link"
                                            href="{{ $posAddRoute }}"
                                            data-url="{{ $posAddRoute }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ $posAddMsg }}"
                                        >
                                            {{ __(' Add POS') }}
                                        </a>
                                    </li>
                                    <li class="dash-item {{ RF::route()->getName() == VW::POS.'.report' || RF::route()->getName() == VW::POS.'.show' ? 'active' : '' }}">
                                        <a
                                            id="{{ $posReportId }}"
                                            class="dash-link"
                                            href="{{ $posReportRoute }}"
                                            data-url="{{ $posReportRoute }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ $posReportMsg }}"
                                        >
                                            {{ __('POS') }}
                                        </a>
                                    </li>
                                    @push(ST::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/pos.js') }}"></script>
                                    @endpush
                                @endcan
                                @can(PMC::MNG_WRH)
                                    @php
                                        $warehouseTransferRoute = Route::has(VW::WRH_TRF.'.index')
                                            ? route(VW::WRH_TRF.'.index')
                                            : (Route::has(Str::kebab(VW::WRH_TRF.'.index'))
                                                ? route(Str::kebab(VW::WRH_TRF.'.index'))
                                                : '#');
                                        $warehouseTransferLinkId = 'warehouse-transfer-index-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            VW::WRH_TRF,
                                            'wrh_trf_index_route_unavailable'
                                        ) ?? 'Warehouse Transfer route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <li class="dash-item {{ RF::route()->getName() == VW::WRH_TRF.'.index' || RF::route()->getName() == VW::WRH_TRF.'.show' ? 'active' : '' }}">
                                        <a
                                            id="{{ $warehouseTransferLinkId }}"
                                            class="dash-link"
                                            href="{{ $warehouseTransferRoute }}"
                                            data-url="{{ $warehouseTransferRoute }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ $message }}"
                                        >
                                            {{ __('Transfer') }}
                                        </a>
                                    </li>
                                    @push(ST::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/warehouseTransfer.js') }}"></script>
                                    @endpush
                                @endcan
                                @can(PMC::CR_BC)
                                    @php
                                        $posBarcodeRoute = Route::has(VW::POS.'.barcode')
                                            ? route(VW::POS.'.barcode')
                                            : '#';
                                        $posBarcodeLinkId = 'pos-barcode-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            VW::POS,
                                            'pos_barcode_route_unavailable'
                                        ) ?? __('POS Barcode route is unavailable. Please contact technical support or your domain administrator.');
                                    @endphp
                                    <li class="dash-item {{ RF::route()->getName() == VW::POS.'.barcode' || RF::route()->getName() == VW::POS.'.print' ? 'active' : '' }}">
                                        <a
                                            id="{{ $posBarcodeLinkId }}"
                                            class="dash-link"
                                            href="{{ $posBarcodeRoute }}"
                                            data-url="{{ $posBarcodeRoute }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ $message }}"
                                        >
                                            {{ __('Print Barcode') }}
                                        </a>
                                    </li>
                                    @push(ST::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/posBarcode.js') }}"></script>
                                    @endpush
                                @endcan
                                @can(PMC::MNG_POS)
                                    @php
                                        $printSettingRoute = Route::has(VW::POS.'.print.setting')
                                            ? route(VW::POS.'.print.setting')
                                            : (Route::has(Str::kebab(VW::POS.'.print.setting'))
                                                ? route(Str::kebab(VW::POS.'.print.setting'))
                                                : '#');
                                        $posPrintLinkId = 'pos-print-setting-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            VW::POS,
                                            'pos_print_setting_route_unavailable'
                                        ) ?? 'POS Print Settings route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <li class="dash-item {{ RF::route()->getName() == VW::POS.'.print.setting' ? 'active' : '' }}">
                                        <a
                                            id="{{ $posPrintLinkId }}"
                                            class="dash-link"
                                            href="{{ $printSettingRoute }}"
                                            data-url="{{ $printSettingRoute }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ $message }}"
                                        >
                                            {{ __('Print Settings') }}
                                        </a>
                                    </li>
                                    @push(ST::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/posPrintSetting.js') }}"></script>
                                    @endpush
                                @endcan
                            </ul>
                        </li>
                    @endif
                @endif
                {{-- <!--------------------- End POs System -----------------------------------> --}}
                @if (($user[UsersConstants::COL_TP] != PMC::ADM) || $isSa)
                    @php
                        $supportRoute = Route::has(VW::SPT.'.index')
                            ? route(VW::SPT.'.index')
                            : '#';
                        $supportId = 'support-system-link';
                        $supportMsg = Utility::fetchLinkMessage(
                            $lang,
                            VW::SPT,
                            'support_system_index_route_unavailable'
                        ) ?? 'Support System route is unavailable. Please contact technical support or your domain administrator.';
                        $zoomRoute = Route::has(VW::ZMM.'.index')
                            ? route(VW::ZMM.'.index')
                            : (Route::has(Str::kebab(VW::ZMM.'.index'))
                                ? route(Str::kebab(VW::ZMM.'.index'))
                                : '#');
                        $zoomId = 'zoom-meeting-link';
                        $zoomMsg = Utility::fetchLinkMessage(
                            $lang,
                            VW::ZMM,
                            'zoom_meeting_index_route_unavailable'
                        ) ?? 'Zoom Meeting route is unavailable. Please contact technical support or your domain administrator.';
                        $messengerRoute = url('chats');
                        $messengerId = 'messenger-link';
                        $messengerMsg = Utility::fetchLinkMessage(
                            $lang,
                            'chats',
                            'messenger_index_route_unavailable'
                        ) ?? 'Messenger route is unavailable. Please contact technical support or your domain administrator.';
                    @endphp
                    <li class="{{ VC::DSH_IT_MN }} {{ RF::segment(1) == VW::SPT ? 'active' : '' }}">
                        <a
                            id="{{ $supportId }}"
                            class="dash-link"
                            href="{{ $supportRoute }}"
                            data-url="{{ $supportRoute }}"
                            data-sv-localized="true"
                            data-guard-msg="{{ $supportMsg }}"
                        >
                            <span class="dash-micon"><i class="ti ti-headphones"></i></span>
                            <span class="dash-mtext">{{ __('Support System') }}</span>
                        </a>
                    </li>
                    <li class="{{ VC::DSH_IT_MN }} {{ RF::segment(1) == VW::ZMM || RF::segment(1) == 'zoom-meeting' || RF::segment(1) == 'zoom_meeting_calendar' || RF::segment(1) == 'zoom-meeting-calendar' ? 'active' : '' }}">
                        <a
                            id="{{ $zoomId }}"
                            class="dash-link"
                            {{-- href="{{ $zoomRoute }}" --}}
                            {{-- data-url="{{ $zoomRoute }}" --}}
                            data-candidate-url="{{ $zoomRoute }}"
                            data-sv-localized="true"
                            data-guard-msg="{{ $zoomMsg }}"
                            href="#"
                        >
                            <span class="dash-micon"><i class="ti ti-user-check"></i></span>
                            <span class="dash-mtext">{{ __('Zoom Meeting') }}</span>
                        </a>
                        @php $disabledRoutes[] = $zoomId; @endphp
                    </li>
                    <li class="{{ VC::DSH_IT_MN }} {{ RF::segment(1) == 'chats' ? 'active' : '' }}">
                        <a
                            id="{{ $messengerId }}"
                            class="dash-link"
                            {{-- href="{{ $messengerRoute }}" --}}
                            {{--data-url="{{ $messengerRoute }}" --}}
                            data-candidate-url="{{ $messengerRoute }}"
                            data-sv-localized="true"
                            data-guard-msg="{{ $messengerMsg }}"
                            style="cursor: pointer;"
                        >
                            <span class="dash-micon"><i class="ti ti-message-circle"></i></span>
                            <span class="dash-mtext">{{ __('Messenger') }}</span>
                        </a>
                        @php $disabledRoutes[] = $messengerId; @endphp
                    </li>
                    @push(ST::ADM_SCR_PG)
                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/calls.js') }}"></script>
                    @endpush
                @endif
                @if ($user[UsersConstants::COL_TP] == PMC::CPN || $user[UsersConstants::COL_TP] == PMC::SA)
                    @php
                        $notifTmpRoute = Route::has(VW::NTF_TMP.'.index')
                            ? route(VW::NTF_TMP.'.index')
                            : (Route::has(Str::kebab(VW::NTF_TMP.'.index'))
                                ? route(Str::kebab(VW::NTF_TMP.'.index'))
                                : '#');
                        $ntfTmpLinkId = 'notification-template-index-link';
                        $message = Utility::fetchLinkMessage(
                            $lang,
                            VW::NTF_TMP,
                            'notification_template_index_route_unavailable'
                        ) ?? 'Notification Template route is unavailable. Please contact technical support or your domain administrator.';
                    @endphp
                    <li class="{{ VC::DSH_IT_MN }} {{ (RF::segment(1) === 'notification-templates' || RF::segment(1) === VW::NTF_TMP) ? 'active' : '' }}">
                        <a
                            id="{{ $ntfTmpLinkId }}"
                            class="dash-link"
                            href="{{ $notifTmpRoute }}"
                            data-url="{{ $notifTmpRoute }}"
                            data-sv-localized="true"
                            data-guard-msg="{{ $message }}"
                        >
                            <span class="dash-micon"><i class="ti ti-notification"></i></span>
                            <span class="dash-mtext">{{ __('Notification Template') }}</span>
                        </a>
                    </li>
                    @push(ST::ADM_SCR_PG)
                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/notificationTemplate.js') }}"></script>
                    @endpush
                @endif
                {{-- <!--------------------- Start System Setup -----------------------------------> --}}
                @if ($user[UsersConstants::COL_TP] != PMC::ADM || $isSa)
                    @if (Gate::check(PMC::MNG_CP_PL) || Gate::check(PMC::MNG_OD) || Gate::check(PMC::MNG_CPN_SET))
                        <li
                            class="{{ VC::DSH_IT_MN }} {{ RF::segment(1) == VW::SET ||
                            RF::segment(1) == VW::PLN ||
                            RF::segment(1) == 'stripe' ||
                            RF::segment(1) == VW::OD
                                ? ' active dash-trigger'
                                : '' }}">
                            <a href="#!" class="dash-link">
                                <span class="dash-micon"><i class="ti ti-settings"></i></span>
                                <span class="dash-mtext">{{ __('Settings') }}</span>
                                <span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="dash-submenu">
                                @if (Gate::check(PMC::MNG_CPN_SET))
                                    @php
                                        $systemSettingsRoute = Route::has(VW::SET)
                                            ? route(VW::SET)
                                            : '#';
                                        $systemSettingsLinkId = 'system-settings-link';
                                        $systemSettingsMessage = Utility::fetchLinkMessage(
                                            $lang,
                                            VW::SET,
                                            'system_settings_route_unavailable'
                                        ) ?? 'System Settings route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <li class="{{ VC::DSH_IT_MN }} {{ RF::segment(1) == VW::SET ? 'active' : '' }}">
                                        <a
                                            id="{{ $systemSettingsLinkId }}"
                                            class="dash-link"
                                            href="{{ $systemSettingsRoute }}"
                                            data-url="{{ $systemSettingsRoute }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ $systemSettingsMessage }}"
                                        >
                                            {{ __('System Settings') }}
                                        </a>
                                    </li>
                                    @push(ST::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/systemSettings.js') }}"></script>
                                    @endpush
                                @endif
                                @if (Gate::check(PMC::MNG_CP_PL))
                                    @php
                                        $setupSubscriptionPlanRoute = Route::has(VW::PLN.'.index')
                                            ? route(VW::PLN.'.index')
                                            : '#';
                                        $setupSubscriptionLinkId = 'setup-subscription-plan-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            VW::PLN,
                                            'plan_index_route_unavailable'
                                        ) ?? 'Setup Subscription Plan route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <li class="dash-item{{ RF::route()->getName() == VW::PLN.'.index' || RF::route()->getName() == 'stripe' ? ' active' : '' }}">
                                        <a
                                            id="{{ $setupSubscriptionLinkId }}"
                                            class="dash-link"
                                            href="{{ $setupSubscriptionPlanRoute }}"
                                            data-url="{{ $setupSubscriptionPlanRoute }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ $message }}"
                                        >
                                            {{ __('Setup Subscription Plan') }}
                                        </a>
                                    </li>
                                    @push(ST::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/setupSubscription.js') }}"></script>
                                    @endpush
                                @endif
                                @if (Gate::check(PMC::MNG_OD) && ($user[UsersConstants::COL_TP] == PMC::CPN || $user[UsersConstants::COL_TP] == PMC::SA))
                                    @php
                                        $orderRoute = Route::has(VW::OD.'.index')
                                            ? route(VW::OD.'.index')
                                            : '#';
                                        $orderLinkId = 'order-index-link';
                                        $message = Utility::fetchLinkMessage(
                                            $lang,
                                            VW::OD,
                                            'order_index_route_unavailable'
                                        ) ?? 'Order route is unavailable. Please contact technical support or your domain administrator.';
                                    @endphp
                                    <li class="dash-item {{ RF::segment(1) == VW::OD ? 'active' : '' }}">
                                        <a
                                            id="{{ $orderLinkId }}"
                                            class="dash-link"
                                            href="{{ $orderRoute }}"
                                            data-url="{{ $orderRoute }}"
                                            data-sv-localized="true"
                                            data-guard-msg="{{ $message }}"
                                        >
                                            {{ __('Order') }}
                                        </a>
                                    </li>
                                    @push(ST::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/partials/admin/menu/orderLink.js') }}"></script>
                                    @endpush
                                @endif
                            </ul>
                        </li>
                    @endif
                @endif
                {{-- <!--------------------- End System Setup -----------------------------------> --}}
                @if ($user[UsersConstants::COL_TP] === PMC::CL || $isSa)
                    <ul class="dash-navbar">
                        @if (Gate::check(PMC::MNG_CLT_DSB))
                            @php
                                $dashboardRoute = Route::has(VW::CLT.'.dashboard.view')
                                    ? route(VW::CLT.'.dashboard.view')
                                    : '#';
                                $dashboardViewLinkId = 'dashboard-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    VW::CLT,
                                    'client_dashboard_view_route_unavailable'
                                ) ?? 'Client Dashboard route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <li class="{{ VC::DSH_IT_MN }} {{ RF::segment(1) == 'dashboard' ? ' active' : '' }}">
                                <a
                                    id="{{ $dashboardViewLinkId }}"
                                    class="dash-link"
                                    href="{{ $dashboardRoute }}"
                                    data-url="{{ $dashboardRoute }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $message }}"
                                >
                                    <span class="dash-micon"><i class="ti ti-home"></i></span>
                                    <span class="dash-mtext">{{ __('Dashboard') }}</span>
                                </a>
                            </li>
                            @push(ST::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/dashboardViewLink.js') }}"></script>
                            @endpush
                        @endif
                        @if (Gate::check(PMC::MNG_DL))
                            @php
                                $dealsRoute = Route::has(VW::DL.'.index')
                                    ? route(VW::DL.'.index')
                                    : '#';
                                $dealIndexLinkId = 'deals-index-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    VW::DL,
                                    'dl_index_route_unavailable'
                                ) ?? __('Deals route is unavailable. Please contact technical support or your domain administrator.');
                            @endphp
                            <li class="{{ VC::DSH_IT_MN }} {{ RF::segment(1) == VW::DL ? 'active' : '' }}">
                                <a
                                    id="{{ $dealIndexLinkId }}"
                                    class="dash-link"
                                    href="{{ $dealsRoute }}"
                                    data-url="{{ $dealsRoute }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $message }}"
                                >
                                    <span class="dash-micon"><i class="ti ti-rocket"></i></span>
                                    <span class="dash-mtext">{{ __('Deals') }}</span>
                                </a>
                            </li>
                            @push(ST::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/dealIndexLink.js') }}"></script>
                            @endpush
                        @endif
                        @if (Gate::check(PMC::MNG_CTC))
                            @php
                                $contractsRoute = Route::has(VW::CTC.'.index')
                                    ? route(VW::CTC.'.index')
                                    : '#';
                                $contractIndexLinkId = 'contracts-index-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    VW::CTC,
                                    'contract_index_route_unavailable'
                                ) ?? 'Contracts route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <li class="{{ VC::DSH_IT_MN }} {{ RF::route()->getName() == VW::CTC.'.index' || RF::route()->getName() == VW::CTC.'.show' ? 'active' : '' }}">
                                <a
                                    id="{{ $contractIndexLinkId }}"
                                    class="dash-link"
                                    href="{{ $contractsRoute }}"
                                    data-url="{{ $contractsRoute }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $message }}"
                                >
                                    <span class="dash-micon"><i class="ti ti-rocket"></i></span>
                                    <span class="dash-mtext">{{ __('Contracts') }}</span>
                                </a>
                            </li>
                            @push(ST::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/contractIndexLink.js') }}"></script>
                            @endpush
                        @endif
                        @if (Gate::check(PMC::MNG_PRJ))
                            @php
                                $projectsRoute = Route::has(VW::PRJ.'.index')
                                    ? route(VW::PRJ.'.index')
                                    : '#';
                                $projectIndexLinkId = 'projects-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    VW::PRJ,
                                    'project_index_route_unavailable'
                                ) ?? 'Projects route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <li class="{{ VC::DSH_IT_MN }} {{ RF::segment(1) == VW::PRJ ? 'active' : '' }}">
                                <a
                                    id="{{ $projectIndexLinkId }}"
                                    class="dash-link"
                                    href="{{ $projectsRoute }}"
                                    data-url="{{ $projectsRoute }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $message }}"
                                >
                                    <span class="dash-micon"><i class="ti ti-share"></i></span>
                                    <span class="dash-mtext">{{ __('Projects') }}</span>
                                </a>
                            </li>
                            @push(ST::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/projectIndexLink.js') }}"></script>
                            @endpush
                        @endif
                        @if (Gate::check(PMC::MNG_PRJ))
                            @php
                                $projectReportRoute = Route::has(VW::PRJ_RPT.'.index')
                                    ? route(VW::PRJ_RPT.'.index')
                                    : (Route::has(Str::kebab(VW::PRJ_RPT.'.index'))
                                        ? route(Str::kebab(VW::PRJ_RPT.'.index'))
                                        : '#');
                                $projectReportLinkId = 'project-report-index-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    VW::PRJ_RPT,
                                    'project_report_index_route_unavailable'
                                ) ?? 'Project Report route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <li class="dash-item {{ RF::route()->getName() == VW::PRJ_RPT.'.index' || RF::route()->getName() == VW::PRJ_RPT.'.show' ? 'active' : '' }}">
                                <a
                                    id="{{ $projectReportLinkId }}"
                                    class="dash-link"
                                    href="{{ $projectReportRoute }}"
                                    data-url="{{ $projectReportRoute }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $message }}"
                                >
                                    <span class="dash-micon"><i class="ti ti-chart-line"></i></span>
                                    <span class="dash-mtext">{{ __('Project Report') }}</span>
                                </a>
                            </li>
                            @push(ST::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/projectReportLink.js') }}"></script>
                            @endpush
                        @endif
                        @if (Gate::check(PMC::MNG_PRJ_TSK))
                            @php
                                $tasksRoute = Route::has(VW::TSKB.'.view')
                                    ? route(VW::TSKB.'.view', 'list')
                                    : '#';
                                $tasksLinkId = 'tasks-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    VW::TSK,
                                    'taskboard_view_route_unavailable'
                                ) ?? 'Tasks route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <li class="{{ VC::DSH_IT_MN }} {{ RF::segment(1) == VW::TSKB ? 'active' : '' }}">
                                <a
                                    id="{{ $tasksLinkId }}"
                                    class="dash-link"
                                    href="{{ $tasksRoute }}"
                                    data-url="{{ $tasksRoute }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $message }}"
                                >
                                    <span class="dash-micon"><i class="ti ti-list-check"></i></span>
                                    <span class="dash-mtext">{{ __('Tasks') }}</span>
                                </a>
                            </li>
                            @push(ST::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/tasksLink.js') }}"></script>
                            @endpush
                        @endif
                        @if (Gate::check(PMC::MNG_BUG_RPT))
                            @php
                                $bugsRoute = Route::has(VW::BUG.'.view')
                                    ? route(VW::BUG.'.view', 'list')
                                    : '#';
                                $bugViewLinkId = 'bugs-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    VW::BUG,
                                    'bug_view_route_unavailable'
                                ) ?? __('Bugs route is unavailable. Please contact technical support or your domain administrator.');
                            @endphp
                            <li class="{{ VC::DSH_IT_MN }} {{ (RF::segment(1) == VW::BUG_RPT || RF::segment(1) == 'bug-reports') ? 'active' : '' }}">
                                <a
                                    id="{{ $bugViewLinkId }}"
                                    class="dash-link"
                                    href="{{ $bugsRoute }}"
                                    data-url="{{ $bugsRoute }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $message }}"
                                >
                                    <span class="dash-micon"><i class="ti ti-bug"></i></span>
                                    <span class="dash-mtext">{{ __('Bugs') }}</span>
                                </a>
                            </li>
                            @push(ST::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/bugsLink.js') }}"></script>
                            @endpush
                        @endif
                        @if (Gate::check(PMC::MNG_TS))
                            @php
                                $timesheetListRoute = Route::has(VW::TMS.'.list')
                                    ? route(VW::TMS.'.list')
                                    : '#';
                                $timesheetListLinkId = 'timesheet-list-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    VW::TMS,
                                    'timesheet_list_route_unavailable'
                                ) ?? 'Timesheet route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <li class="{{ VC::DSH_IT_MN }} {{ (RF::segment(1) == VW::TMS_LT || RF::segment(1) == 'timesheet-lists') ? 'active' : '' }}">
                                <a
                                    id="{{ $timesheetListLinkId }}"
                                    class="dash-link"
                                    href="{{ $timesheetListRoute }}"
                                    data-url="{{ $timesheetListRoute }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $message }}"
                                >
                                    <span class="dash-micon"><i class="ti ti-clock"></i></span>
                                    <span class="dash-mtext">{{ __('Timesheet') }}</span>
                                </a>
                            </li>
                            @push(ST::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/timesheetListLink.js') }}"></script>
                            @endpush
                        @endif
                        @if (Gate::check(PMC::MNG_PRJ_TSK))
                            @php
                                $taskCalendarRoute = Route::has(VW::TSK.'.calendar')
                                    ? route(VW::TSK.'.calendar', ['all'])
                                    : '#';
                                $taskCalendarLinkId = 'task-calendar-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    VW::TSK,
                                    'tsk_calendar_route_unavailable'
                                ) ?? 'Task calendar route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <li class="{{ VC::DSH_IT_MN }} {{ RF::segment(1) == VW::CLD ? 'active' : '' }}">
                                <a
                                    id="{{ $taskCalendarLinkId }}"
                                    class="dash-link"
                                    href="{{ $taskCalendarRoute }}"
                                    data-url="{{ $taskCalendarRoute }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $message }}"
                                >
                                    <span class="dash-micon"><i class="ti ti-calendar"></i></span>
                                    <span class="dash-mtext">{{ __('Task calendar') }}</span>
                                </a>
                            </li>
                            @push(ST::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/taskCalendar.js') }}"></script>
                            @endpush
                        @endif
                        @php
                            $supportRoute = Route::has(VW::SPT.'.index')
                                ? route(VW::SPT.'.index')
                                : '#';
                            $supportLinkId = 'support-link';
                            $message = Utility::fetchLinkMessage(
                                $lang,
                                VW::SPT,
                                'spt_index_route_unavailable'
                            ) ?? 'Support route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        <li class="{{ VC::DSH_IT_MN }}">
                            <a
                                id="{{ $supportLinkId }}"
                                class="dash-link {{ RF::segment(1) == VW::SPT ? 'active' : '' }}"
                                href="{{ $supportRoute }}"
                                data-url="{{ $supportRoute }}"
                                data-sv-localized="true"
                                data-guard-msg="{{ $message }}"
                            >
                                <span class="dash-micon"><i class="ti ti-headphones"></i></span>
                                <span class="dash-mtext">{{ __('Support') }}</span>
                            </a>
                        </li>
                        @push(ST::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/partials/admin/menu/support.js') }}"></script>
                        @endpush
                    </ul>
                @endif
                @if ($user[UsersConstants::COL_TP] === PMC::SA)
                    <ul class="dash-navbar">
                        @if (Gate::check(PMC::MNG_SA_DSB))
                            @php
                                $dashboardRoute = Route::has(VW::CLT.'.dashboard.view')
                                    ? route(VW::CLT.'.dashboard.view')
                                    : '#';
                                $dashboardViewLinkId2 = 'dashboard-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    VW::CLT,
                                    'client_dashboard_view_route_unavailable'
                                ) ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <li class="{{ VC::DSH_IT_MN }} {{ RF::segment(1) == 'dashboard' ? ' active' : '' }}">
                                <a
                                    id="{{ $dashboardViewLinkId2 }}"
                                    class="dash-link"
                                    href="{{ $dashboardRoute }}"
                                    data-url="{{ $dashboardRoute }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $message }}"
                                >
                                    <span class="dash-micon"><i class="ti ti-home"></i></span>
                                    <span class="dash-mtext">{{ __('Dashboard') }}</span>
                                </a>
                            </li>
                            @push(ST::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/dashboardLink.js') }}"></script>
                            @endpush
                        @endif
                        @can(PMC::MNG_USER)
                            @php
                                $userIndexRoute = Route::has(VW::USR.'.index')
                                    ? route(VW::USR.'.index')
                                    : '#';
                                $userLinkId2 = 'user-index-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    VW::USR,
                                    'user_index_route_unavailable'
                                ) ?? __('User route is unavailable. Please contact technical support or your domain administrator.');
                            @endphp
                            <li class="{{ VC::DSH_IT_MN }} {{ (RF::route()->getName() == VW::USR.'.index' || RF::route()->getName() == VW::USR.'.create' || RF::route()->getName() == VW::USR.'.edit') ? 'active' : '' }}">
                                <a
                                    id="{{ $userLinkId2 }}"
                                    class="dash-link"
                                    href="{{ $userIndexRoute }}"
                                    data-url="{{ $userIndexRoute }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $message }}"
                                >
                                    <span class="dash-micon"><i class="{{ VC::TI_USRS }}"></i></span>
                                    <span class="dash-mtext">{{ __('User') }}</span>
                                </a>
                            </li>
                            @push(ST::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/user.js') }}"></script>
                            @endpush
                        @endcan
                        @if (Gate::check(PMC::MNG_PL))
                            @php
                                $planRoute = Route::has(VW::PLN.'.index')
                                    ? route(VW::PLN.'.index')
                                    : '#';
                                $planLinkId2 = 'plan-index-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    VW::PLN,
                                    'plan_index_route_unavailable'
                                ) ?? 'Plan route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <li class="{{ VC::DSH_IT_MN }} {{ RF::segment(1) == VW::PLN ? 'active' : '' }}">
                                <a
                                    id="{{ $planLinkId2 }}"
                                    class="dash-link"
                                    href="{{ $planRoute }}"
                                    data-url="{{ $planRoute }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $message }}"
                                >
                                    <span class="dash-micon"><i class="ti ti-trophy"></i></span>
                                    <span class="dash-mtext">{{ __('Plan') }}</span>
                                </a>
                            </li>
                            @push(ST::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/plan.js') }}"></script>
                            @endpush
                        @endif
                        @if ($user[UsersConstants::COL_TP] === PMC::SA)
                            @php
                                $planRequestRoute = Route::has(VW::PLN_RQ.'.index')
                                    ? route(VW::PLN_RQ.'.index')
                                    : (Route::has(Str::kebab(VW::PLN_RQ.'.index'))
                                        ? route(Str::kebab(VW::PLN_RQ.'.index'))
                                        : '#');
                                $planRequestLinkId2 = 'plan-request-index-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    VW::PLN_RQ,
                                    'plan_request_index_route_unavailable'
                                ) ?? 'Plan Request route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <li class="{{ VC::DSH_IT_MN }} {{ (request()->is('plan_request*') || request()->is('plan-request*')) ? 'active' : '' }}">
                                <a
                                    id="{{ $planRequestLinkId2 }}"
                                    class="dash-link"
                                    href="{{ $planRequestRoute }}"
                                    data-url="{{ $planRequestRoute }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $message }}"
                                >
                                    <span class="dash-micon"><i class="ti ti-arrow-up-right-circle"></i></span>
                                    <span class="dash-mtext">{{ __('Plan Request') }}</span>
                                </a>
                            </li>
                            @push(ST::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/planRequest.js') }}"></script>
                            @endpush
                        @endif
                        @if (Gate::check(PMC::MNG_CPN))
                            @php
                                $couponRoute = Route::has(VW::CPN.'.index')
                                    ? route(VW::CPN.'.index')
                                    : '#';
                                $couponLinkId2 = 'coupon-index-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    VW::CPN,
                                    'coupon_index_route_unavailable'
                                ) ?? 'Coupon route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <li class="{{ VC::DSH_IT_MN }} {{ RF::segment(1) == VW::CPN ? 'active' : '' }}">
                                <a
                                    id="{{ $couponLinkId2 }}"
                                    class="dash-link"
                                    href="{{ $couponRoute }}"
                                    data-url="{{ $couponRoute }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $message }}"
                                >
                                    <span class="dash-micon"><i class="ti ti-gift"></i></span>
                                    <span class="dash-mtext">{{ __('Coupon') }}</span>
                                </a>
                            </li>
                            @push(ST::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/coupon.js') }}"></script>
                            @endpush
                        @endif
                        @if (Gate::check(PMC::MNG_OD))
                            @php
                                $orderRoute = Route::has(VW::OD.'.index')
                                    ? route(VW::OD.'.index')
                                    : '#';
                                $orderLinkId2 = 'order-index-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    VW::OD,
                                    'order_index_route_unavailable'
                                ) ?? 'Order route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <li class="{{ VC::DSH_IT_MN }} {{ RF::segment(1) == VW::OD ? 'active' : '' }}">
                                <a
                                    id="{{ $orderLinkId2 }}"
                                    class="dash-link"
                                    href="{{ $orderRoute }}"
                                    data-url="{{ $orderRoute }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $message }}"
                                >
                                    <span class="dash-micon"><i class="ti ti-shopping-cart-plus"></i></span>
                                    <span class="dash-mtext">{{ __('Order') }}</span>
                                </a>
                            </li>
                            @push(ST::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/order.js') }}"></script>
                            @endpush
                        @endif
                        @php
                            if (!empty($emailTemplate?->id))
                                $emailTemplateRoute = Route::has(VW::EMLS . '.manage.language')
                                    ? route(VW::EMLS . '.manage.language', [$emailTemplate->id, $user?->lang])
                                    : (Route::has(Str::kebab(VW::EMLS . '.manage.language'))
                                        ? route(Str::kebab(VW::EMLS . '.manage.language'), [$emailTemplate->id, $user?->lang])
                                        : '#');
                            else
                                $emailTemplateRoute = "#";
                            $emailTmpLinkId = 'email-template-link';
                            $message = Utility::fetchLinkMessage(
                                $lang,
                                VW::EMLS,
                                'email_template_route_unavailable'
                            ) ?? 'Email Template route is unavailable. Please contact technical support or your domain administrator.';
                        @endphp
                        <li class="{{ VC::DSH_IT_MN }} {{ RF::segment(1) == VW::NTF_TMP ? ' active' : '' }}">
                            <a
                                id="{{ $emailTmpLinkId }}"
                                class="dash-link"
                                href="{{ $emailTemplateRoute }}"
                                data-url="{{ $emailTemplateRoute }}"
                                data-sv-localized="true"
                                data-guard-msg="{{ $message }}"
                            >
                                <span class="dash-micon"><i class="ti ti-template"></i></span>
                                <span class="dash-mtext">{{ __('Email Template') }}</span>
                            </a>
                        </li>
                        @push(ST::ADM_SCR_PG)
                            <script defer src="{{ asset('assets/js/routes/partials/admin/menu/emailTemplate.js') }}"></script>
                        @endpush
                        @if ($user[UsersConstants::COL_TP] == PMC::SA)
                            @include(R::LP.'::'.VW::MN.'.'.R::LP)
                        @endif
                        @if (Gate::check(PMC::MNG_SYS_ST))
                            @php
                                $settingsRoute = Route::has(VW::SYS.'.index')
                                    ? route(VW::SYS.'.index')
                                    : '#';
                                $settingsLinkId = 'settings-index-link';
                                $message = Utility::fetchLinkMessage(
                                    $lang,
                                    VW::SYS,
                                    'settings_index_route_unavailable'
                                ) ?? 'Settings route is unavailable. Please contact technical support or your domain administrator.';
                            @endphp
                            <li class="{{ VC::DSH_IT_MN }} {{ RF::route()->getName() == VW::SYS.'.index' ? 'active' : '' }}">
                                <a
                                    id="{{ $settingsLinkId }}"
                                    class="dash-link"
                                    href="{{ $settingsRoute }}"
                                    data-url="{{ $settingsRoute }}"
                                    data-sv-localized="true"
                                    data-guard-msg="{{ $message }}"
                                >
                                    <span class="dash-micon"><i class="ti ti-settings"></i></span>
                                    <span class="dash-mtext">{{ __('Settings') }}</span>
                                </a>
                            </li>
                            @push(ST::ADM_SCR_PG)
                                <script defer src="{{ asset('assets/js/routes/partials/admin/menu/settings.js') }}"></script>
                            @endpush
                        @endif
                    </ul>
                @endif
            @endif
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
                        <b class="d-block f-w-700">{{ __('You need help?') }}</b>
                        <span>{{ __('Check out our repository') }} </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push(ST::ADM_SCR_PG)
    <script defer>
        document.addEventListener("DOMContentLoaded", () => {
            (@json($disabledRoutes) || []).forEach(route => {
                    const el = document.getElementById(route);
                    if (!(el instanceof HTMLAnchorElement)) return;
                    if (el.dataset.disablerAttached === "true") return;
                    el.dataset.disablerAttached = "true";
                    el.addEventListener("click", e => {
                    e.preventDefault();
                    displayUnavailableRouteMessage("{{ $lang }}");
                });
            });
        });
    </script>
    @endpush
    <link rel="stylesheet" href="{{ asset('assets/css/routes/partials/admin/menu.css') }}">
</nav>
