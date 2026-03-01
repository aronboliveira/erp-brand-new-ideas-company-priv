<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Registers global class aliases used inside Blade @php blocks.
 *
 * Blade compiles @php…@endphp into <?php…?> inside a function scope,
 * where PHP `use` statements are illegal.  Instead of littering every
 * view with fully-qualified class names we register short aliases here
 * so that VW::, VC::, Utility::, Form::, etc. resolve globally.
 *
 * ⚡  This provider is auto-discovered (no manual config needed) and
 *     runs *before* any view is compiled.
 */
class BladeImportsServiceProvider extends ServiceProvider
{
	/**
	 * shortAlias => fullyQualifiedClassName
	 *
	 * Only entries that are NOT already in config/app.php 'aliases' need
	 * to be listed here.  Laravel-core facades (Auth, Route, Log, DB,
	 * Str, Cache, Crypt, Session, Storage, URL, …) are already global.
	 */
	private const ALIASES = [
		// ── App\Config\Constants ────────────────────────────────────
		'AC'                        => \App\Config\Constants\ActivitiesConstants::class,
		'ActivitiesConstants'       => \App\Config\Constants\ActivitiesConstants::class,
		'BillsConstants'            => \App\Config\Constants\BillsConstants::class,
		'DatabaseConstants'         => \App\Config\Constants\DatabaseConstants::class,
		'DBC'                       => \App\Config\Constants\DatabaseConstants::class,
		'DC'                        => \App\Config\Constants\DatabaseConstants::class,
		'EL'                        => \App\Config\Constants\ExtendingLayoutsConstants::class,
		'ELC'                       => \App\Config\Constants\ExtendingLayoutsConstants::class,
		'ExtendingLayoutsConstants' => \App\Config\Constants\ExtendingLayoutsConstants::class,
		'LangsConstants'            => \App\Config\Constants\LangsConstants::class,
		'PC'                        => \App\Config\Constants\PermissionsConstants::class,
		'PERM'                      => \App\Config\Constants\PermissionsConstants::class,
		'PM'                        => \App\Config\Constants\PermissionsConstants::class,
		'PMC'                       => \App\Config\Constants\PermissionsConstants::class,
		'PermissionsConstants'      => \App\Config\Constants\PermissionsConstants::class,
		'PJ'                        => \App\Config\Constants\ProjectsConstants::class,
		'ProjectsConstants'         => \App\Config\Constants\ProjectsConstants::class,
		'PL'                        => \App\Config\Constants\PlansConstants::class,
		'PLC'                       => \App\Config\Constants\PlansConstants::class,
		'PlansConstants'            => \App\Config\Constants\PlansConstants::class,
		'SC'                        => \App\Config\Constants\SettingsConstants::class,
		'STG'                       => \App\Config\Constants\SettingsConstants::class,
		'SettingsConstants'         => \App\Config\Constants\SettingsConstants::class,
		'ST'                        => \App\Config\Constants\StacksConstants::class,
		'StacksConstants'           => \App\Config\Constants\StacksConstants::class,
		'SupportsConstants'         => \App\Config\Constants\SupportsConstants::class,
		'UC'                        => \App\Config\Constants\UsersConstants::class,
		'UCN'                       => \App\Config\Constants\UsersConstants::class,
		'UsersConstants'            => \App\Config\Constants\UsersConstants::class,
		'C'                         => \App\Config\Constants\ViewClassNamesConstants::class,
		'VC'                        => \App\Config\Constants\ViewClassNamesConstants::class,
		'VCN'                       => \App\Config\Constants\ViewClassNamesConstants::class,
		'ViewClassNamesConstants'   => \App\Config\Constants\ViewClassNamesConstants::class,
		'VW'                        => \App\Config\Constants\ViewsConstants::class,
		'ViewsConstants'            => \App\Config\Constants\ViewsConstants::class,
		'ViewsConstans'             => \App\Config\Constants\ViewsConstants::class, // typo alias used in some views
		'YC'                        => \App\Config\Constants\YieldingConstants::class,
		'YD'                        => \App\Config\Constants\YieldingConstants::class,
		'YW'                        => \App\Config\Constants\YieldingConstants::class,
		'YieldingConstants'         => \App\Config\Constants\YieldingConstants::class,

		// ── App\Models ─────────────────────────────────────────────
		'Bill'                      => \App\Models\Bill::class,
		'BillPayment'               => \App\Models\BillPayment::class,
		'Budget'                    => \App\Models\Budget::class,
		'ChartOfAccount'            => \App\Models\ChartOfAccount::class,
		'Contract'                  => \App\Models\Contract::class,
		'CustomQuestion'            => \App\Models\CustomQuestion::class,
		'Employee'                  => \App\Models\Employee::class,
		'Estimation'                => \App\Models\Estimation::class,
		'Goal'                      => \App\Models\Goal::class,
		'Invoice'                   => \App\Models\Invoice::class,
		'Job'                       => \App\Models\Job::class,
		'JobOnBoard'                => \App\Models\JobOnBoard::class,
		'Payslip'                   => \App\Models\Payslip::class,
		'Plan'                      => \App\Models\Plan::class,
		'ProductService'            => \App\Models\ProductService::class,
		'ProductServiceCategory'    => \App\Models\ProductServiceCategory::class,
		'ProductServiceUnit'        => \App\Models\ProductServiceUnit::class,
		'Project'                   => \App\Models\Project::class,
		'ProjectTask'               => \App\Models\ProjectTask::class,
		'Proposal'                  => \App\Models\Proposal::class,
		'Purchase'                  => \App\Models\Purchase::class,
		'Support'                   => \App\Models\Support::class,
		'TaskStage'                 => \App\Models\TaskStage::class,
		'Timesheet'                 => \App\Models\Timesheet::class,
		'User'                      => \App\Models\User::class,
		'UserDeal'                  => \App\Models\UserDeal::class,
		'Utility'                   => \App\Models\Utility::class,
		'UtilModel'                 => \App\Models\Utility::class,
		'Vendor'                    => \App\Models\Vendor::class,
		'WebhookSettings'           => \App\Models\WebhookSettings::class,
		'Branch'                    => \App\Models\Branch::class,
		'Department'                => \App\Models\Department::class,
		'Designation'               => \App\Models\Designation::class,
		'EmailTemplate'             => \App\Models\EmailTemplate::class,
		'Language'                  => \App\Models\Language::class,
		'ChMessage'                 => \App\Models\ChMessage::class,
		'LandingPageSetting'        => \Modules\LandingPage\Entities\LandingPageSetting::class,

		// ── App\Http\Controllers (constants used in views) ───────
		'EAC'                       => \App\Http\Controllers\EmployeeAttendanceController::class,

		// ── Illuminate non-facade helpers ───────────────────────────
		'Arr'                       => \Illuminate\Support\Arr::class,
		'Carbon'                    => \Illuminate\Support\Carbon::class,
		'Collection'                => \Illuminate\Support\Collection::class,
		'ModelNotFoundException'    => \Illuminate\Database\Eloquent\ModelNotFoundException::class,
		'QueryException'            => \Illuminate\Database\QueryException::class,
		'UrlGenerationException'    => \Illuminate\Routing\Exceptions\UrlGenerationException::class,

		// ── Symfony / third-party ───────────────────────────────────
		'ConsoleOutput'             => \Symfony\Component\Console\Output\ConsoleOutput::class,
		'RouteNotFoundException'    => \Symfony\Component\Routing\Exception\RouteNotFoundException::class,
		'Form'                      => \Collective\Html\FormFacade::class,
		'FormFacade'                => \Collective\Html\FormFacade::class,
		'Role'                      => \Spatie\Permission\Models\Role::class,
		// 'Purifier'               => \Mews\Purifier\Facades\Purifier::class, // TODO: mews/purifier not installed — uncomment after: composer require mews/purifier
		'DNS2D'                     => \Milon\Barcode\DNS2D::class,

		// ── LandingPage module ──────────────────────────────────────
		'E'                         => \Modules\LandingPage\Config\Constants\ExtendingLandingPageLayoutConstants::class,
		'R'                         => \Modules\LandingPage\Config\Constants\RoutesResourcesConstants::class,
		'RRC'                       => \Modules\LandingPage\Config\Constants\RoutesResourcesConstants::class,
		'RoutesResourcesConstants'  => \Modules\LandingPage\Config\Constants\RoutesResourcesConstants::class,
		'LPSC'                      => \Modules\LandingPage\Config\Constants\SettingsConstants::class,
		'LandingPageSettingsConstants' => \Modules\LandingPage\Config\Constants\SettingsConstants::class,
		'LPC'                       => \App\Config\Constants\LandingPageConstants::class,
		'LandingPageConstants'      => \App\Config\Constants\LandingPageConstants::class,
	];

	/**
	 * These are Log-facade re-aliases used by some views (e.g. AccountLog, AiLog…).
	 * They all point to the same Illuminate\Support\Facades\Log class.
	 */
	private const LOG_ALIASES = [
		'AccountLog',
		'AiLog',
		'BcLog',
		'CrmLog',
		'FieldsLog',
		'FormLog',
		'HrmLog',
		'NestedLog',
		'PosLog',
		'ProjectLog',
		'StaffLog',
		'UpdateLog',
	];

	public function register(): void
	{
		//
	}

	public function boot(): void
	{
		foreach (self::ALIASES as $alias => $fqcn) {
			if (!\class_exists($alias, false) && \class_exists($fqcn)) {
				\class_alias($fqcn, $alias);
			}
		}

		$logClass = \Illuminate\Support\Facades\Log::class;
		foreach (self::LOG_ALIASES as $alias) {
			if (!\class_exists($alias, false)) {
				\class_alias($logClass, $alias);
			}
		}

		// Also alias 'ViewFacade' → Illuminate\Support\Facades\View
		if (!\class_exists('ViewFacade', false)) {
			\class_alias(\Illuminate\Support\Facades\View::class, 'ViewFacade');
		}

		// RF → Request facade alias used in some views
		if (!\class_exists('RF', false)) {
			\class_alias(\Illuminate\Support\Facades\Request::class, 'RF');
		}
	}
}
