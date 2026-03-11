<?php

declare(strict_types=1);

namespace Tests\Unit\app\Http\Views\Blade;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Mockery;

/**
 * Shared helpers for blade view unit tests.
 *
 * Provides mock data builders and safe rendering utilities so that
 * individual test classes remain concise and focused on assertions.
 */
trait BladeViewTestHelper
{
    // ─── Mock Data Builders ──────────────────────────────────────────

	/**
	 * Build an associative array representing a mock user row.
	 *
	 * @param  string|int $id
	 * @param  string     $type
	 * @param  string|int $createdBy
	 * @return array<string, mixed>
	 */
	protected function buildMockUser(
		string|int $id = '00000000-0000-0000-0000-000000000001',
		string $type = 'company',
		string|int $createdBy = '00000000-0000-0000-0000-000000000001'
	): array {
		return [
			'id'               => $id,
			'name'             => "Test User {$id}",
			'email'            => "testuser{$id}@example.com",
			'type'             => $type,
			'created_by'       => $createdBy,
			'avatar'           => '',
			'lang'             => 'en',
			'messenger_color'  => '#000000',
			'is_banned'        => 0,
			'default_pipeline' => 0,
			'storage_limit'    => 0,
			'email_verified_at' => now()->toDateTimeString(),
		];
	}

	/**
	 * Build a Mockery User model that works with actingAs().
	 *
	 * @param  string|int $id
	 * @param  string     $type
	 * @param  string|int $createdBy
	 * @return \App\Models\User&\Mockery\MockInterface
	 */
	protected function buildMockUserModel(
		string|int $id = '00000000-0000-0000-0000-000000000001',
		string $type = 'company',
		string|int $createdBy = '00000000-0000-0000-0000-000000000001'
	): User {
		/** @var \App\Models\User&\Mockery\MockInterface $user */
		$user = Mockery::mock(User::class)->makePartial()->shouldIgnoreMissing();
		$user->id         = $id;
		$user->name       = "Test User {$id}";
		$user->email      = "testuser{$id}@example.com";
		$user->type       = $type;
		$user->is_banned  = 0;
		$user->created_by = $createdBy;

		$user->shouldReceive('creatorId')->andReturn($createdBy);
		$user->shouldReceive('can')->andReturn(true);
		$user->shouldReceive('hasPermissionTo')->andReturn(true);
		$user->shouldReceive('hasVerifiedEmail')->andReturn(true);
		$user->shouldReceive('getAuthIdentifier')->andReturn($id);
		$user->shouldReceive('getAuthIdentifierName')->andReturn('id');
		$user->shouldReceive('getAuthPassword')->andReturn('hashed');
		$user->shouldReceive('getRememberToken')->andReturn(null);
		$user->shouldReceive('getRememberTokenName')->andReturn('remember_token');

		return $user;
	}

	/**
	 * Build an associative array representing a mock company/settings.
	 *
	 * @return array<string, mixed>
	 */
	protected function buildMockCompany(): array
	{
		return [
			'id'                 => 1,
			'name'               => 'Test Company',
			'address'            => '123 Test St',
			'city'               => 'Testville',
			'state'              => 'TS',
			'zip'                => '00000',
			'country'            => 'BR',
			'telephone'          => '000-000-0000',
			'company_name'       => 'Test Company',
			'company_email'      => 'company@test.com',
			'company_phone'      => '000-000-0000',
			'company_address'    => '123 Test St',
			'company_city'       => 'Testville',
			'company_state'      => 'TS',
			'company_zipcode'    => '00000',
			'company_country'    => 'BR',
			'registration_number' => '0000000',
			'vat_number'         => '',
			'company_start_time' => '09:00',
			'company_end_time'   => '18:00',
		];
	}

	/**
	 * Build an associative array with common view settings data.
	 *
	 * @return array<string, mixed>
	 */
	protected function buildMockSettings(): array
	{
		return [
			'site_currency'               => 'USD',
			'site_currency_symbol'        => '$',
			'site_currency_symbol_position' => 'pre',
			'site_date_format'            => 'M j, Y',
			'site_time_format'            => 'g:i A',
			'company_name'                => 'Test Company',
			'company_email'               => 'company@test.com',
			'company_logo'                => '',
			'company_dark_logo'           => '',
			'company_favicon'             => '',
			'title_text'                  => 'ERP',
			'footer_text'                 => '© Test',
			'default_language'            => 'en',
			'enable_signup'               => 'on',
			'color'                       => 'theme-3',
			'cust_theme_bg'               => 'on',
			'cust_darklayout'             => '',
			'SITE_RTL'                    => 'off',
			'storage_setting'             => 'local',
		];
	}

	/**
	 * Build mock bill data for bill-related views.
	 *
	 * @return array<string, mixed>
	 */
	protected function buildMockBill(): array
	{
		return [
			'id'              => 1,
			'bill_id'         => '#BILL0001',
			'vendor_id'       => 1,
			'bill_date'       => now()->toDateString(),
			'due_date'        => now()->addDays(30)->toDateString(),
			'order_number'    => 1,
			'status'          => 0,
			'shipping_display' => 'on',
			'send_date'       => null,
			'discount_apply'  => 0,
			'category_id'     => 1,
			'created_by'      => 1,
		];
	}

	/**
	 * Build mock invoice data for invoice-related views.
	 *
	 * @return array<string, mixed>
	 */
	protected function buildMockInvoice(): array
	{
		return [
			'id'              => 1,
			'invoice_id'      => '#INV0001',
			'customer_id'     => 1,
			'issue_date'      => now()->toDateString(),
			'due_date'        => now()->addDays(30)->toDateString(),
			'send_date'       => null,
			'category_id'     => 1,
			'ref_number'      => '',
			'status'          => 0,
			'shipping_display' => 'on',
			'discount_apply'  => 0,
			'created_by'      => 1,
		];
	}

	/**
	 * Build mock employee data for employee-related views.
	 *
	 * @return array<string, mixed>
	 */
	protected function buildMockEmployee(): array
	{
		return [
			'id'              => 1,
			'user_id'         => 1,
			'name'            => 'Test Employee',
			'email'           => 'employee@test.com',
			'dob'             => '1990-01-01',
			'gender'          => 'male',
			'phone'           => '000-000-0000',
			'address'         => '123 Test St',
			'branch_id'       => 1,
			'department_id'   => 1,
			'designation_id'  => 1,
			'company_doj'     => now()->toDateString(),
			'salary_type'     => 'monthly',
			'salary'          => 5000,
			'created_by'      => 1,
		];
	}

    // ─── Safe Render Utility ─────────────────────────────────────────

	/**
	 * Attempt to compile and render a Blade view, catching any Throwable.
	 *
	 * @param  string               $viewName  Dot-notation view name.
	 * @param  array<string, mixed> $data      Variables to pass.
	 * @return true|string           `true` on success, or the Throwable message.
	 */
	protected function safeRenderView(string $viewName, array $data = []): true|string
	{
		try {
			View::make($viewName, $data)->render();
			return true;
		} catch (\Throwable $e) {
			return $e->getMessage();
		}
	}

	/**
	 * Assert that a Blade view file exists on disk.
	 *
	 * @param  string $viewName  Dot-notation view name (e.g. 'auth.login').
	 * @return void
	 */
	protected function assertViewFileExists(string $viewName): void
	{
		$relativePath = str_replace('.', DIRECTORY_SEPARATOR, $viewName) . '.blade.php';
		$fullPath     = resource_path('views' . DIRECTORY_SEPARATOR . $relativePath);

		$this->assertFileExists(
			$fullPath,
			"Blade file for view [{$viewName}] not found at [{$fullPath}]."
		);
	}

	/**
	 * Assert that Laravel's view finder can resolve the given view.
	 *
	 * @param  string $viewName  Dot-notation view name.
	 * @return void
	 */
	protected function assertViewRegistered(string $viewName): void
	{
		$this->assertTrue(
			View::exists($viewName),
			"View [{$viewName}] is not registered with the Laravel view finder."
		);
	}

	/**
	 * Run the full trio of existence checks for a single view:
	 *   1. File exists on disk
	 *   2. Laravel can resolve it
	 *   3. (optional) Safe render attempt
	 *
	 * @param  string               $viewName
	 * @param  array<string, mixed> $data
	 * @param  bool                 $attemptRender
	 * @return void
	 */
	protected function assertViewExistsAndOptionallyRenders(
		string $viewName,
		array $data = [],
		bool $attemptRender = false
	): void {
		$this->assertViewFileExists($viewName);
		$this->assertViewRegistered($viewName);

		if ($attemptRender) {
			$result = $this->safeRenderView($viewName, $data);
			$this->assertTrue(
				$result === true,
				"View [{$viewName}] threw during render: " . (is_string($result) ? $result : '')
			);
		}
	}
}
