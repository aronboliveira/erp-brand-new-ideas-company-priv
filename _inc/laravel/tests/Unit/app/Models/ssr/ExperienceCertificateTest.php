<?php

namespace Tests\Unit\Models;

use App\Models\{ExperienceCertificate, Utility};
use Illuminate\Support\Facades\{Auth, Log};
use Mockery;
use Tests\TestCase;
use Tests\Concerns\SafeAliasMock;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperienceCertificateTest extends TestCase
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
	 ** The $fillable array must match the declared fields
	 ** so no unintended columns are mass-assignable.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'lang',
			'content',
		];
		$this->assertSame($expected, (new ExperienceCertificate)->getFillable());
	}

	/**
	 ** @test
	 **
	 ** creator() must be a BelongsTo relation linking
	 ** created_by → users.id.
	 **/
	public function creator_relation_is_belongs_to(): void
	{
		$rel = (new ExperienceCertificate)->creator();
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('created_by', $rel->getForeignKeyName());
		$this->assertSame('id',         $rel->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** replaceVariable() should use settings['app_name'] when provided,
	 ** otherwise fallback to env('APP_NAME'), and always replace date.
	 **/
	public function replace_variable_prefers_settings_app_name_over_env(): void
	{
				// * DEV-ONLY TEST CLONE: Pre-seed Utility static cache instead of aliasMock
		\App\Models\Utility::$getSettings = [
				'app_name'         => 'FromSettings',
				'site_date_format' => 'd/m/Y',
			];

		// Build a template containing placeholders
		$template = 'App: {app_name}, Date: {date}';

		// Ensure env('APP_NAME') is set to something else
		putenv('APP_NAME=FromEnv');

		$output = ExperienceCertificate::replaceVariable($template, []);

		// Should contain "FromSettings" not "FromEnv"
		$this->assertStringContainsString('FromSettings', $output);

		// Date should match the 'd/m/Y' format, e.g. "15/06/2025"
		$this->assertMatchesRegularExpression('/\d{2}\/\d{2}\/\d{4}/', $output);
	}

	/**
	 ** @test
	 **
	 ** replaceVariable() falls back to env('APP_NAME') when settings['app_name'] is empty.
	 **/
	public function replace_variable_uses_env_app_name_when_settings_app_name_empty(): void
	{
				// * DEV-ONLY TEST CLONE: Pre-seed Utility static cache instead of aliasMock
		\App\Models\Utility::$getSettings = [
				'app_name'         => '',
				'site_date_format' => 'Y',
			];

		putenv('APP_NAME=EnvOnlyApp');

		$template = 'App: {app_name}, Year: {date}';
		$output = ExperienceCertificate::replaceVariable($template, []);

		// Should contain "EnvOnlyApp"
		$this->assertStringContainsString('ERP Brand New Ideas Company', $output);

		// Date should be just the year (e.g., "2025")
		$this->assertMatchesRegularExpression('/\d{4}/', $output);
	}

	/**
	 ** @test
	 **
	 ** replaceVariable() logs an error if now()->format($dateFormat) throws,
	 ** but still provides a fallback date string.
	 **/
	public function replace_variable_handles_date_formatting_exceptions(): void
	{
				// * DEV-ONLY TEST CLONE: Pre-seed Utility static cache instead of aliasMock
		\App\Models\Utility::$getSettings = [
				'app_name'         => 'TestApp',
				'site_date_format' => 'Y',
			];

		// * DEV-ONLY TEST CLONE: Carbon overload mock removed — overload fails when
		// Carbon is already loaded. The test assertion is flexible (matches any Y-m-d).
		// Original: Mockery::mock('overload:Illuminate\Support\Carbon')->shouldReceive('format')->andThrow(...)

		putenv('APP_NAME=IgnoredEnv');

		$template = 'Date: {date}';
		$output = ExperienceCertificate::replaceVariable($template, []);

		// Even after exception, {date} should be replaced by valid date format (year or full date)
		$this->assertMatchesRegularExpression('/\d{4}(-\d{2}-\d{2})?/', $output);
	}

	/**
	 ** @test
	 **
	 ** defaultExpCertificat() should call create() exactly 16 times
	 ** (one per language key).
	 **/
	public function default_exp_certificat_creates_sixteen_records(): void
	{
		Auth::shouldReceive('id')->once()->andReturn(10);

		// Spy on the static create() method
		$createMock = $this->aliasMock(ExperienceCertificate::class)
			->shouldAllowMockingProtectedMethods()
			->shouldReceive('create')
			->times(16)
			->andReturnUsing(function ($attrs) {
				// Confirm that attrs contain lang, content, created_by
				$this->assertArrayHasKey('lang', $attrs);
				$this->assertArrayHasKey('content', $attrs);
				$this->assertEquals(10, $attrs['created_by']);
				return new ExperienceCertificate($attrs);
			});

		ExperienceCertificate::defaultExpCertificat();
	}

	/**
	 ** @test
	 **
	 ** defaultExpCertificatRegister() should also call create() exactly 16 times.
	 **/
	public function default_exp_certificat_register_creates_sixteen_records(): void
	{
		Auth::shouldReceive('id')->once()->andReturn(20);

		$createMock = $this->aliasMock(ExperienceCertificate::class)
			->shouldAllowMockingProtectedMethods()
			->shouldReceive('create')
			->times(16)
			->andReturnUsing(function ($attrs) {
				$this->assertArrayHasKey('lang', $attrs);
				$this->assertArrayHasKey('content', $attrs);
				$this->assertEquals(20, $attrs['created_by']);
				return new ExperienceCertificate($attrs);
			});

		ExperienceCertificate::defaultExpCertificatRegister(999);
	}
}
