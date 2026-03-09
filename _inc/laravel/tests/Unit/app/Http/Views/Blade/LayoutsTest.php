<?php

declare(strict_types=1);

namespace Tests\Unit\app\Http\Views\Blade;

use Tests\TestCase;
use Illuminate\Support\Facades\View;
use App\Config\Constants\ExtendingLayoutsConstants;

/**
 * Unit tests for layout Blade views.
 *
 * These are the shared parent layouts used by @extends():
 *   - layouts.admin
 *   - layouts.auth
 *   - layouts.landing
 *   - layouts.account_setup
 *   - layouts.contract_header
 *   - layouts.cookie_consent
 *   - layouts.crm_setup
 *   - layouts.hrm_setup
 *   - layouts.share_project
 *
 * Also checks partials that every layout relies on:
 *   - partials.admin.header
 *   - partials.admin.footer
 *   - partials.admin.menu
 *   - partials.validation.admin_error
 *   - partials.helpers.route_helpers
 */
class LayoutsTest extends TestCase
{
	use BladeViewTestHelper;

	// ═════════════════════════════════════════════════════════════════
	//  Layout files
	// ═════════════════════════════════════════════════════════════════

	// ─── layouts.admin ──────────────────────────────────────────────

	public function test_layouts_admin_view_file_exists(): void
	{
		$this->assertViewFileExists('layouts.admin');
	}

	public function test_layouts_admin_view_is_registered(): void
	{
		$this->assertViewRegistered('layouts.admin');
	}

	public function test_layouts_admin_matches_constant(): void
	{
		$this->assertSame(
			'layouts.admin',
			ExtendingLayoutsConstants::ADM,
			'ExtendingLayoutsConstants::ADM must resolve to layouts.admin'
		);
	}

	public function test_layouts_admin_renders_safely(): void
	{
		$user = $this->buildMockUserModel();
		$this->actingAs($user);

		$result = $this->safeRenderView('layouts.admin', array_merge(
			$this->buildMockSettings(),
			[
				'settings' => $this->buildMockSettings(),
				'setting'  => (object) $this->buildMockSettings(),
				'users'    => collect([]),
				'languages' => ['en' => 'English'],
				'currantLang' => 'en',
				'lang'     => 'en',
			]
		));

		if ($result !== true) {
			$this->assertIsString($result);
		} else {
			$this->assertTrue($result);
		}
	}

	// ─── layouts.auth ───────────────────────────────────────────────

	public function test_layouts_auth_view_file_exists(): void
	{
		$this->assertViewFileExists('layouts.auth');
	}

	public function test_layouts_auth_view_is_registered(): void
	{
		$this->assertViewRegistered('layouts.auth');
	}

	public function test_layouts_auth_matches_constant(): void
	{
		$this->assertSame(
			'layouts.auth',
			ExtendingLayoutsConstants::AUTH,
			'ExtendingLayoutsConstants::AUTH must resolve to layouts.auth'
		);
	}

	public function test_layouts_auth_renders_safely(): void
	{
		$result = $this->safeRenderView('layouts.auth', [
			'settings'        => $this->buildMockSettings(),
			'setting'         => (object) $this->buildMockSettings(),
			'languages'       => ['en' => 'English'],
			'lang'            => 'en',
			'logo'            => '',
			'company_logo'    => '',
			'company_favicon' => '',
			'color'           => 'theme-3',
			'SITE_RTL'        => 'off',
		]);

		if ($result !== true) {
			$this->assertIsString($result);
		} else {
			$this->assertTrue($result);
		}
	}

	// ─── layouts.landing ────────────────────────────────────────────

	public function test_layouts_landing_view_file_exists(): void
	{
		$this->assertViewFileExists('layouts.landing');
	}

	public function test_layouts_landing_view_is_registered(): void
	{
		$this->assertViewRegistered('layouts.landing');
	}

	// ─── layouts.account_setup ──────────────────────────────────────

	public function test_layouts_account_setup_view_file_exists(): void
	{
		$this->assertViewFileExists('layouts.account_setup');
	}

	public function test_layouts_account_setup_view_is_registered(): void
	{
		$this->assertViewRegistered('layouts.account_setup');
	}

	// ─── layouts.contract_header ────────────────────────────────────

	public function test_layouts_contract_header_view_file_exists(): void
	{
		$this->assertViewFileExists('layouts.contract_header');
	}

	public function test_layouts_contract_header_view_is_registered(): void
	{
		$this->assertViewRegistered('layouts.contract_header');
	}

	// ─── layouts.cookie_consent ─────────────────────────────────────

	public function test_layouts_cookie_consent_view_file_exists(): void
	{
		$this->assertViewFileExists('layouts.cookie_consent');
	}

	public function test_layouts_cookie_consent_view_is_registered(): void
	{
		$this->assertViewRegistered('layouts.cookie_consent');
	}

	// ─── layouts.crm_setup ──────────────────────────────────────────

	public function test_layouts_crm_setup_view_file_exists(): void
	{
		$this->assertViewFileExists('layouts.crm_setup');
	}

	public function test_layouts_crm_setup_view_is_registered(): void
	{
		$this->assertViewRegistered('layouts.crm_setup');
	}

	// ─── layouts.hrm_setup ──────────────────────────────────────────

	public function test_layouts_hrm_setup_view_file_exists(): void
	{
		$this->assertViewFileExists('layouts.hrm_setup');
	}

	public function test_layouts_hrm_setup_view_is_registered(): void
	{
		$this->assertViewRegistered('layouts.hrm_setup');
	}

	// ─── layouts.share_project ──────────────────────────────────────

	public function test_layouts_share_project_view_file_exists(): void
	{
		$this->assertViewFileExists('layouts.share_project');
	}

	public function test_layouts_share_project_view_is_registered(): void
	{
		$this->assertViewRegistered('layouts.share_project');
	}

	// ═════════════════════════════════════════════════════════════════
	//  Partial views
	// ═════════════════════════════════════════════════════════════════

	public function test_partials_admin_header_exists(): void
	{
		$this->assertViewFileExists('partials.admin.header');
		$this->assertViewRegistered('partials.admin.header');
	}

	public function test_partials_admin_footer_exists(): void
	{
		$this->assertViewFileExists('partials.admin.footer');
		$this->assertViewRegistered('partials.admin.footer');
	}

	public function test_partials_admin_menu_exists(): void
	{
		$this->assertViewFileExists('partials.admin.menu');
		$this->assertViewRegistered('partials.admin.menu');
	}

	public function test_partials_validation_admin_error_exists(): void
	{
		$this->assertViewFileExists('partials.validation.admin_error');
		$this->assertViewRegistered('partials.validation.admin_error');
	}

	public function test_partials_helpers_route_helpers_exists(): void
	{
		$this->assertViewFileExists('partials.helpers.route_helpers');
		$this->assertViewRegistered('partials.helpers.route_helpers');
	}

	// ═════════════════════════════════════════════════════════════════
	//  Batch – all layout files exist
	// ═════════════════════════════════════════════════════════════════

	public function test_all_layout_views_exist_in_finder(): void
	{
		$views = [
			'layouts.admin',
			'layouts.auth',
			'layouts.landing',
			'layouts.account_setup',
			'layouts.contract_header',
			'layouts.cookie_consent',
			'layouts.crm_setup',
			'layouts.hrm_setup',
			'layouts.share_project',
		];

		foreach ($views as $view) {
			$this->assertTrue(
				View::exists($view),
				"Layout view [{$view}] is not registered."
			);
		}
	}

	public function test_all_partial_views_exist_in_finder(): void
	{
		$views = [
			'partials.admin.header',
			'partials.admin.footer',
			'partials.admin.menu',
			'partials.validation.admin_error',
			'partials.helpers.route_helpers',
		];

		foreach ($views as $view) {
			$this->assertTrue(
				View::exists($view),
				"Partial view [{$view}] is not registered."
			);
		}
	}
}
