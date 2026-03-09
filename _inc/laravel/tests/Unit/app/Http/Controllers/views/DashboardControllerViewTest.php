<?php

namespace Tests\Unit\app\Http\Controllers\views;

use App\Http\Controllers\Shapes\DashboardController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;
use Tests\Unit\app\Http\Controllers\ControllerTestHelper;

#[\PHPUnit\Framework\Attributes\Group('controller-views')]
class DashboardControllerViewTest extends TestCase
{
	use RefreshDatabase;
	use ControllerTestHelper;
	use ViewAssertionHelper;

	/* ------------------------------------------------------------------ */
	/*  View-file existence tests                                         */
	/* ------------------------------------------------------------------ */

	public function test_account_dashboard_view_file_exists(): void
	{
		$this->assertBladeViewExists('dashboard.account_dashboard');
	}

	public function test_project_dashboard_view_file_exists(): void
	{
		$this->assertBladeViewExists('dashboard.project_dashboard');
	}

	public function test_dashboard_view_file_exists(): void
	{
		$this->assertBladeViewExists('dashboard.dashboard');
	}

	public function test_super_admin_view_file_exists(): void
	{
		$this->assertBladeViewExists('dashboard.super_admin');
	}

	public function test_crm_dashboard_view_file_exists(): void
	{
		$this->assertBladeViewExists('dashboard.crm_dashboard');
	}

	public function test_pos_dashboard_view_file_exists(): void
	{
		$this->assertBladeViewExists('dashboard.pos_dashboard');
	}

	public function test_dashboard_view_generic_view_file_exists(): void
	{
		$this->assertBladeViewExists('dashboard.view');
	}

	public function test_client_view_file_exists(): void
	{
		$this->assertBladeViewExists('dashboard.client_view');
	}

	public function test_admin_dashboard_view_file_exists(): void
	{
		$this->assertBladeViewExists('admin.dashboard');
	}

	/* ------------------------------------------------------------------ */
	/*  Controller class & reflection tests                                */
	/* ------------------------------------------------------------------ */

	public function test_dashboard_controller_class_exists(): void
	{
		$this->assertTrue(
			class_exists(DashboardController::class),
			'DashboardController class should be loadable.'
		);
	}

	public function test_dashboard_controller_has_expected_methods(): void
	{
		$ref = new ReflectionClass(DashboardController::class);

		$expected = [
			'accountDashboardIndex',
			'projectDashboardIndex',
			'hrmDashboardIndex',
			'crmDashboardIndex',
			'posDashboardIndex',
			'filterView',
			'clientView',
		];

		foreach ($expected as $method) {
			$this->assertTrue(
				$ref->hasMethod($method),
				"DashboardController should have public method [{$method}]."
			);
			$this->assertTrue(
				$ref->getMethod($method)->isPublic(),
				"Method [{$method}] should be public."
			);
		}
	}

	public function test_dashboard_controller_constants_defined(): void
	{
		$this->assertSame('dashboard', DashboardController::ENTITY);
		$this->assertSame('accountDashboardIndex', DashboardController::ACC_DSB_IDX);
		$this->assertSame('projectDashboardIndex', DashboardController::PRJ_DSB_IDX);
		$this->assertSame('hrmDashboardIndex', DashboardController::HRM_DSB_IDX);
		$this->assertSame('crmDashboardIndex', DashboardController::CRM_DSB_IDX);
		$this->assertSame('posDashboardIndex', DashboardController::POS_DSB_IDX);
		$this->assertSame('filterView', DashboardController::FT_VW);
		$this->assertSame('clientView', DashboardController::CL_VW);
		$this->assertSame('getOrderChart', DashboardController::GET_OC);
		$this->assertSame('stopTracker', DashboardController::STP_TRK);
	}
}
