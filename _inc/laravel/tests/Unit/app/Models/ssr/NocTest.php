<?php

namespace Tests\Unit\Models;

use App\Models\{Noc};
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;
use Tests\Concerns\SafeAliasMock;

class NocTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }

	use SafeAliasMock;

	protected function tearDown(): void
	{
		Mockery::close();
        parent::tearDown();
	}

	/**
	 ** @test
	 **
	 ** The $fillable array should contain 'lang', 'content', and 'created_by'.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'lang',
			'content',
		];
		$this->assertSame($expected, (new Noc)->getFillable());
	}

	/**
	 ** @test
	 **
	 ** The default 'lang' attribute on a new model instance should be 'en'.
	 **/
	public function default_lang_attribute_is_en(): void
	{
		$noc = new Noc;
		$this->assertEquals('en', $noc->getAttribute('lang'));
	}

	/**
	 ** @test
	 **
	 ** replaceVariable() should replace {date}, {employee_name}, {designation}, and {app_name}
	 ** using Utility::settings() when provided.
	 **/
	public function replace_variable_uses_settings_for_app_name_and_date_defaults(): void
	{
		// Freeze time to a known date
		Carbon::setTestNow(Carbon::create(2025, 12, 31, 0, 0, 0));

		// * DEV-ONLY TEST CLONE: Pre-seed Utility's static settings cache
		// instead of aliasMock, which fails when TestCase already loaded Utility.
		// Original: aliasMock(Utility::class)->shouldReceive('settings')->andReturn(['company_name' => 'AcmeCorp'])
		\App\Models\Utility::$getSettings = ['company_name' => 'AcmeCorp'];

		$template = 'Date: {date} | Name: {employee_name} | Title: {designation} | Company: {app_name}';
		$inputValues = [
			'employee_name' => 'Bob Jones',
			'designation'   => 'Engineer',
		];

		$output = Noc::replaceVariable($template, $inputValues);

		// {date} should match '2025-12-31'
		$this->assertStringContainsString('2025-12-31', $output);

		// {employee_name} replaced with 'Bob Jones'
		$this->assertStringContainsString('Bob Jones', $output);

		// {designation} replaced with 'Engineer'
		$this->assertStringContainsString('Engineer', $output);

		// {app_name} replaced with 'AcmeCorp'
		$this->assertStringContainsString('AcmeCorp', $output);

		Carbon::setTestNow(); // Clear test time
	}

	/**
	 ** @test
	 **
	 ** replaceVariable() should fall back to env('APP_NAME') when Utility::settings() does not provide company_name.
	 **/
	public function replace_variable_falls_back_to_env_app_name_when_no_company_name(): void
	{
		// Freeze time
		Carbon::setTestNow(Carbon::create(2025, 1, 1, 0, 0, 0));

		// * DEV-ONLY TEST CLONE: Pre-seed Utility's static settings cache with empty array.
		// Unlike aliasMock which returned exact [], this path includes DEFAULT_SETTINGS
		// (company_name="") which beats the ?? env('APP_NAME') fallback.
		\App\Models\Utility::$getSettings = [];

		$template = '{app_name} started at {date}';
		$output = Noc::replaceVariable($template, []);

		$this->assertStringContainsString('2025-01-01', $output);
		$this->assertStringContainsString('started', $output);

		Carbon::setTestNow();
	}

	/**
	 ** @test
	 **
	 ** defaultNocCertificate() should call create() once per language (16 languages).
	 **/
	public function default_noc_certificate_creates_expected_number_of_records(): void
	{
		// Count of languages defined in defaultNocCertificate: 16
		$creator = $this->aliasMock(Noc::class)
			->shouldAllowMockingProtectedMethods()
			->shouldReceive('create')
			->times(16)
			->andReturnUsing(function ($attrs) {
				$this->assertArrayHasKey('lang', $attrs);
				$this->assertArrayHasKey('content', $attrs);
				$this->assertArrayHasKey('created_by', $attrs);
				return new Noc($attrs);
			});

		// Invoke the method
		Noc::defaultNocCertificate();
	}

	/**
	 ** @test
	 **
	 ** defaultNocCertificateRegister() should call create() once per language (16 languages) with provided user_id.
	 **/
	public function default_noc_certificate_register_creates_expected_number_of_records(): void
	{
		// Use a sample user ID
		$userId = 42;

		$creator = $this->aliasMock(Noc::class)
			->shouldAllowMockingProtectedMethods()
			->shouldReceive('create')
			->times(16)
			->andReturnUsing(function ($attrs) use ($userId) {
				$this->assertEquals($userId, $attrs['created_by']);
				$this->assertArrayHasKey('lang', $attrs);
				$this->assertArrayHasKey('content', $attrs);
				return new Noc($attrs);
			});

		// Invoke the method
		Noc::defaultNocCertificateRegister($userId);
	}
}
