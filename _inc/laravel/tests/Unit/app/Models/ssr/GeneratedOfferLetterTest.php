<?php

/**
 * tests/Unit/Models/GeneratedOfferLetterTest.php
 *
 * Unit tests for App\Models\GeneratedOfferLetter.
 */

namespace Tests\Unit\Models;

use App\Models\{GeneratedOfferLetter, Utility};
use Mockery;
use Tests\TestCase;
use Tests\Concerns\SafeAliasMock;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
class GeneratedOfferLetterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
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
	 ** The $fillable array should only contain 'lang', 'content', and 'created_by'.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = ['lang', 'content', 'created_by'];
		$this->assertSame($expected, (new GeneratedOfferLetter)->getFillable());
	}

	/**
	 ** @test
	 **
	 ** createdBy() should be a BelongsTo relation linking 'created_by' → users.id.
	 **/
	public function created_by_relation_is_belongs_to_user(): void
	{
		$relation = (new GeneratedOfferLetter)->createdBy();
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$relation
		);
		$this->assertSame('created_by', $relation->getForeignKeyName());
		$this->assertSame('id',         $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** replaceVariable() should replace placeholders based on:
	 ** - $obj array keys
	 ** - Utility::settings()['app_name'] over env('APP_NAME')
	 ** - Utility::settings()['default_salary_type']
	 ** - Utility::settings()['default_salary_duration']
	 **/
	public function replace_variable_applies_all_settings_and_obj_values(): void
	{
		// Stub Utility::settings() to provide app_name, default_salary_type, default_salary_duration
		$this->aliasMock(Utility::class)
			->shouldReceive('settings')
			->once()
			->andReturn([
				'app_name'               => 'AcmeCorp',
				'default_salary_type'    => 'hourly',
				'default_salary_duration' => 'weekly',
			]);

		// Ensure env fallback would be ignored
		putenv('APP_NAME=IgnoredApp');

		$template = 'Hello {applicant_name}, Welcome to {app_name}. Pay: {salary} per {salary_type} ({salary_duration}).';
		$inputValues = [
			'applicant_name' => 'Jane Doe',
			'salary'         => '$50',
		];

		$output = GeneratedOfferLetter::replaceVariable($template, $inputValues);

		// applicant_name from $obj
		$this->assertStringContainsString('Jane Doe', $output);

		// app_name from Utility::settings
		$this->assertStringContainsString('AcmeCorp', $output);

		// salary from $obj
		$this->assertStringContainsString('$50', $output);

		// salary_type from settings
		$this->assertStringContainsString('hourly', $output);

		// salary_duration from settings
		$this->assertStringContainsString('weekly', $output);
	}

	/**
	 ** @test
	 **
	 ** replaceVariable() should fall back to env('APP_NAME') if settings['app_name'] is empty.
	 **/
	public function replace_variable_uses_env_app_name_when_settings_empty(): void
	{
		$this->aliasMock(Utility::class)
			->shouldReceive('settings')
			->once()
			->andReturn([
				'app_name'               => '',
				'default_salary_type'    => '',
				'default_salary_duration' => '',
			]);

		putenv('APP_NAME=MyEnvApp');

		$template = 'Company: {app_name}, Type: {salary_type}, Duration: {salary_duration}';
		$output = GeneratedOfferLetter::replaceVariable($template, []);

		// app_name should come from env, not empty
		$this->assertStringContainsString('MyEnvApp', $output);

		// salary_type and salary_duration remain '-' because no settings nor $obj
		$this->assertStringContainsString('-', $output);
	}

	/**
	 ** @test
	 **
	 ** defaultOfferLetter() should call create() once per language in its template.
	 **/
	public function default_offer_letter_creates_expected_number_of_records(): void
	{
		$defaultTemplate = (new \ReflectionClass(GeneratedOfferLetter::class))
			->getMethod('defaultOfferLetter')
			->getClosure()
			->bindTo(new GeneratedOfferLetter(), GeneratedOfferLetter::class);

		// Count how many entries exist in the hardcoded defaultOfferLetter array
		// We can invoke the method as a closure and intercept create() calls
		$templateProperty = (new \ReflectionClass(GeneratedOfferLetter::class))
			->getMethod('defaultOfferLetter')
			->getClosure();
		// Instead of introspecting, simply mock create() for however many languages appear:
		// The user code defines exactly 16 keys in defaultTemplate.

		// Spy on GeneratedOfferLetter::create()
		$createMock = $this->aliasMock(GeneratedOfferLetter::class)
			->shouldAllowMockingProtectedMethods()
			->shouldReceive('create')
			->times(16)
			->andReturnUsing(function ($attrs) {
				$this->assertArrayHasKey('lang', $attrs);
				$this->assertArrayHasKey('content', $attrs);
				$this->assertArrayHasKey('created_by', $attrs);
				return new GeneratedOfferLetter($attrs);
			});

		// Call the static method
		GeneratedOfferLetter::defaultOfferLetter('1');
	}

	/**
	 ** @test
	 **
	 ** defaultOfferLetterRegister() should call create() once per language in its template.
	 **/
	public function default_offer_letter_register_creates_expected_number_of_records(): void
	{
		// Spy on GeneratedOfferLetter::create()
		$createMock = $this->aliasMock(GeneratedOfferLetter::class)
			->shouldAllowMockingProtectedMethods()
			->shouldReceive('create')
			->times(16)
			->andReturnUsing(function ($attrs) {
				$this->assertArrayHasKey('lang', $attrs);
				$this->assertArrayHasKey('content', $attrs);
				$this->assertArrayHasKey('created_by', $attrs);
				return new GeneratedOfferLetter($attrs);
			});

		// Pass any user_id; it's passed straight through to create()
		GeneratedOfferLetter::defaultOfferLetterRegister(42);
	}
}
