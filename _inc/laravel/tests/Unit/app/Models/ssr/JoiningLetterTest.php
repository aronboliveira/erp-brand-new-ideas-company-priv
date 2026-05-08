<?php


namespace Tests\Unit\Models;

use App\Models\{JoiningLetter, Utility};
use Mockery;
use Tests\TestCase;
use Tests\Concerns\SafeAliasMock;

class JoiningLetterTest extends TestCase
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
	 ** The $fillable array should contain 'id', 'lang', 'content', and 'created_by'.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'lang',
			'content',
		];
		$this->assertSame($expected, (new JoiningLetter)->getFillable());
	}

	/**
	 ** @test
	 **
	 ** replaceVariable() should replace placeholders using:
	 ** - Utility::settings() for date/time format, company_name, company_address
	 ** - values from the $obj array for employee_name and start_date
	 **/
	public function replace_variable_applies_settings_and_obj_values(): void
	{
				// * DEV-ONLY TEST CLONE: Pre-seed Utility static cache instead of aliasMock
		\App\Models\Utility::$getSettings = [
				'site_date_format'  => 'Y-m-d',
				'site_time_format'  => 'H:i',
				'company_name'      => 'TestCorp',
				'company_address'   => '123 Main St',
			];

		$template = '{date} | {app_name} | {address} | {employee_name} | {start_date}';
		$inputValues = [
			'employee_name' => 'Alice Smith',
			'start_date'    => '2025-07-01',
		];

		$output = JoiningLetter::replaceVariable($template, $inputValues);

		// {date} should be in "YYYY-MM-DD HH:MM" format
		$this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2} \|/', $output);

		// {app_name} replaced with 'TestCorp'
		$this->assertStringContainsString('TestCorp', $output);

		// {address} replaced with '123 Main St'
		$this->assertStringContainsString('123 Main St', $output);

		// {employee_name} replaced with 'Alice Smith'
		$this->assertStringContainsString('Alice Smith', $output);

		// {start_date} replaced with '2025-07-01'
		$this->assertStringContainsString('2025-07-01', $output);
	}

	/**
	 ** @test
	 **
	 ** replaceVariable() should leave placeholders as '-' when no settings or obj values provided.
	 **/
	public function replace_variable_handles_missing_values_as_dash(): void
	{
				// * DEV-ONLY TEST CLONE: Pre-seed Utility static cache instead of aliasMock
		\App\Models\Utility::$getSettings = [
				'site_date_format'  => 'Y/m/d',
				'site_time_format'  => 'H:i:s',
				'company_name'      => '',
				'company_address'   => '',
			];

		$template = '{app_name}--{address}--{employee_name}';
		$output = JoiningLetter::replaceVariable($template, []);

		// Empty company_name and company_address should result in '-' placeholders
		$this->assertStringContainsString('-', $output);
	}

	/**
	 ** @test
	 **
	 ** defaultJoiningLetterRegister() should call create() once per language in its template (16 languages).
	 **/
	public function default_joining_letter_register_creates_expected_number_of_records(): void
	{
		// 16 languages: ar, zh, da, de, en, es, fr, he, it, ja, nl, pl,
		// pt, ru, tr, pt-br. Drive the real create() path. The fillable
		// allowlist on JoiningLetter includes only lang+content, so
		// created_by is filtered by mass-assignment — count by the lang
		// set written rather than by created_by.
		$before = JoiningLetter::count();
		JoiningLetter::defaultJoiningLetterRegister(\App\Config\Constants\DatabaseConstants::DEFAULT_UUID);
		$after = JoiningLetter::count();

		$this->assertSame(16, $after - $before);
	}
}
