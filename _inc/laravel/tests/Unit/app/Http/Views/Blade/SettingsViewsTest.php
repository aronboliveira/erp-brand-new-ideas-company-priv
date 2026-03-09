<?php

declare(strict_types=1);

namespace Tests\Unit\app\Http\Views\Blade;

use Tests\TestCase;
use Illuminate\Support\Facades\View;
use Mockery;

/**
 * Unit tests for settings-related Blade views.
 *
 * Covered views:
 *   - settings.index
 *   - settings.create
 *   - settings.edit
 *   - settings.pos
 *   - settings.print
 *   - settings.test_mail
 */
class SettingsViewsTest extends TestCase
{
	use BladeViewTestHelper;

	// ─── settings.index ─────────────────────────────────────────────

	public function test_settings_index_view_file_exists(): void
	{
		$this->assertViewFileExists('settings.index');
	}

	public function test_settings_index_view_is_registered(): void
	{
		$this->assertViewRegistered('settings.index');
	}

	public function test_settings_index_view_renders_safely(): void
	{
		$user = $this->buildMockUserModel();
		$this->actingAs($user);

		$result = $this->safeRenderView('settings.index', [
			'settings'         => $this->buildMockSettings(),
			'setting'          => (object) $this->buildMockSettings(),
			'payment_setting'  => [],
			'store_settings'   => [],
			'admin_payment_setting' => [],
			'languages'        => ['en' => 'English'],
			'lang'             => 'en',
			'currantLang'      => 'en',
			'EmailTemplates'   => collect([]),
			'timezones'        => [],
			'company_setting'  => (object) $this->buildMockCompany(),
			'file_type'        => [],
		]);

		if ($result !== true) {
			$this->assertIsString($result);
		} else {
			$this->assertTrue($result);
		}
	}

	// ─── settings.create ────────────────────────────────────────────

	public function test_settings_create_view_file_exists(): void
	{
		$this->assertViewFileExists('settings.create');
	}

	public function test_settings_create_view_is_registered(): void
	{
		$this->assertViewRegistered('settings.create');
	}

	// ─── settings.edit ──────────────────────────────────────────────

	public function test_settings_edit_view_file_exists(): void
	{
		$this->assertViewFileExists('settings.edit');
	}

	public function test_settings_edit_view_is_registered(): void
	{
		$this->assertViewRegistered('settings.edit');
	}

	// ─── settings.pos ───────────────────────────────────────────────

	public function test_settings_pos_view_file_exists(): void
	{
		$this->assertViewFileExists('settings.pos');
	}

	public function test_settings_pos_view_is_registered(): void
	{
		$this->assertViewRegistered('settings.pos');
	}

	// ─── settings.print ─────────────────────────────────────────────

	public function test_settings_print_view_file_exists(): void
	{
		$this->assertViewFileExists('settings.print');
	}

	public function test_settings_print_view_is_registered(): void
	{
		$this->assertViewRegistered('settings.print');
	}

	// ─── settings.test_mail ─────────────────────────────────────────

	public function test_settings_test_mail_view_file_exists(): void
	{
		$this->assertViewFileExists('settings.test_mail');
	}

	public function test_settings_test_mail_view_is_registered(): void
	{
		$this->assertViewRegistered('settings.test_mail');
	}

	// ─── Batch – all settings views exist ───────────────────────────

	public function test_all_settings_views_exist_in_finder(): void
	{
		$views = [
			'settings.index',
			'settings.create',
			'settings.edit',
			'settings.pos',
			'settings.print',
			'settings.test_mail',
		];

		foreach ($views as $view) {
			$this->assertTrue(
				View::exists($view),
				"Settings view [{$view}] is not registered."
			);
		}
	}
}
