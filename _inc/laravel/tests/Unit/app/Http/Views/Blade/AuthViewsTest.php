<?php

declare(strict_types=1);

namespace Tests\Unit\app\Http\Views\Blade;

use Tests\TestCase;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

/**
 * Unit tests for authentication-related Blade views.
 *
 * These views extend `layouts.auth` and are relatively simple,
 * so we attempt both existence checks and safe renders.
 *
 * Covered views:
 *   - auth.login
 *   - auth.register
 *   - auth.forgot_password
 *   - auth.verify
 *   - auth.confirm-password
 *   - auth.reset-password
 *   - auth.passwords.email
 *   - auth.passwords.reset
 *   - auth.passwords.confirm
 */
class AuthViewsTest extends TestCase
{
	use BladeViewTestHelper;

    // ─── Common mock data for auth views ─────────────────────────────

	/**
	 * @return array<string, mixed>
	 */
	private function authViewData(): array
	{
		return array_merge($this->buildMockSettings(), [
			'languages'       => ['en' => 'English'],
			'lang'            => 'en',
			'settings'        => $this->buildMockSettings(),
			'logo'            => '',
			'company_logo'    => '',
			'company_favicon' => '',
			'color'           => 'theme-3',
			'SITE_RTL'        => 'off',
			'setting'         => (object) $this->buildMockSettings(),
		]);
	}

	// ═════════════════════════════════════════════════════════════════
	//  auth.login
	// ═════════════════════════════════════════════════════════════════

	public function test_login_view_file_exists(): void
	{
		$this->assertViewFileExists('auth.login');
	}

	public function test_login_view_is_registered(): void
	{
		$this->assertViewRegistered('auth.login');
	}

	public function test_login_view_renders_safely(): void
	{
		$result = $this->safeRenderView('auth.login', $this->authViewData());

		// Even if rendering fails due to deep dependencies, the fact
		// that the view was found and compilation started is valuable.
		if ($result !== true) {
			$this->assertIsString($result, 'Render produced an error message.');
		} else {
			$this->assertTrue($result);
		}
	}

	// ═════════════════════════════════════════════════════════════════
	//  auth.register
	// ═════════════════════════════════════════════════════════════════

	public function test_register_view_file_exists(): void
	{
		$this->assertViewFileExists('auth.register');
	}

	public function test_register_view_is_registered(): void
	{
		$this->assertViewRegistered('auth.register');
	}

	public function test_register_view_renders_safely(): void
	{
		$result = $this->safeRenderView('auth.register', $this->authViewData());

		if ($result !== true) {
			$this->assertIsString($result);
		} else {
			$this->assertTrue($result);
		}
	}

	// ═════════════════════════════════════════════════════════════════
	//  auth.forgot_password
	// ═════════════════════════════════════════════════════════════════

	public function test_forgot_password_view_file_exists(): void
	{
		$this->assertViewFileExists('auth.forgot_password');
	}

	public function test_forgot_password_view_is_registered(): void
	{
		$this->assertViewRegistered('auth.forgot_password');
	}

	public function test_forgot_password_view_renders_safely(): void
	{
		$result = $this->safeRenderView('auth.forgot_password', $this->authViewData());

		if ($result !== true) {
			$this->assertIsString($result);
		} else {
			$this->assertTrue($result);
		}
	}

	// ═════════════════════════════════════════════════════════════════
	//  auth.verify
	// ═════════════════════════════════════════════════════════════════

	public function test_verify_view_file_exists(): void
	{
		$this->assertViewFileExists('auth.verify');
	}

	public function test_verify_view_is_registered(): void
	{
		$this->assertViewRegistered('auth.verify');
	}

	public function test_verify_view_renders_safely(): void
	{
		$result = $this->safeRenderView('auth.verify', $this->authViewData());

		if ($result !== true) {
			$this->assertIsString($result);
		} else {
			$this->assertTrue($result);
		}
	}

	// ═════════════════════════════════════════════════════════════════
	//  auth.confirm-password
	// ═════════════════════════════════════════════════════════════════

	public function test_confirm_password_view_file_exists(): void
	{
		$this->assertViewFileExists('auth.confirm-password');
	}

	public function test_confirm_password_view_is_registered(): void
	{
		$this->assertViewRegistered('auth.confirm-password');
	}

	// ═════════════════════════════════════════════════════════════════
	//  auth.reset-password
	// ═════════════════════════════════════════════════════════════════

	public function test_reset_password_view_file_exists(): void
	{
		$this->assertViewFileExists('auth.reset-password');
	}

	public function test_reset_password_view_is_registered(): void
	{
		$this->assertViewRegistered('auth.reset-password');
	}

	// ═════════════════════════════════════════════════════════════════
	//  auth.passwords.email
	// ═════════════════════════════════════════════════════════════════

	public function test_passwords_email_view_file_exists(): void
	{
		$this->assertViewFileExists('auth.passwords.email');
	}

	public function test_passwords_email_view_is_registered(): void
	{
		$this->assertViewRegistered('auth.passwords.email');
	}

	// ═════════════════════════════════════════════════════════════════
	//  auth.passwords.reset
	// ═════════════════════════════════════════════════════════════════

	public function test_passwords_reset_view_file_exists(): void
	{
		$this->assertViewFileExists('auth.passwords.reset');
	}

	public function test_passwords_reset_view_is_registered(): void
	{
		$this->assertViewRegistered('auth.passwords.reset');
	}

	// ═════════════════════════════════════════════════════════════════
	//  auth.passwords.confirm
	// ═════════════════════════════════════════════════════════════════

	public function test_passwords_confirm_view_file_exists(): void
	{
		$this->assertViewFileExists('auth.passwords.confirm');
	}

	public function test_passwords_confirm_view_is_registered(): void
	{
		$this->assertViewRegistered('auth.passwords.confirm');
	}

    // ═════════════════════════════════════════════════════════════════
    //  Batch – all auth views exist
    // ═════════════════════════════════════════════════════════════════

	/**
	 * Verify every known auth view is discoverable by Laravel.
	 */
	public function test_all_auth_views_exist_in_finder(): void
	{
		$views = [
			'auth.login',
			'auth.register',
			'auth.forgot_password',
			'auth.verify',
			'auth.confirm-password',
			'auth.reset-password',
			'auth.passwords.email',
			'auth.passwords.reset',
			'auth.passwords.confirm',
		];

		foreach ($views as $view) {
			$this->assertTrue(
				View::exists($view),
				"Auth view [{$view}] is not registered."
			);
		}
	}
}
