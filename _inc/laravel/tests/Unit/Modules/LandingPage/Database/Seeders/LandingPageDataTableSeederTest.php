<?php

namespace Tests\Unit\Seeders;

use Tests\TestCase;
use Modules\LandingPage\{
	Config\Constants\SettingsConstants,
	Database\Seeders\LandingPageDataTableSeeder,
	Entities\LandingPageSetting
};

/**
 * Seeder integration test — does NOT use RefreshDatabase or DatabaseTransactions
 * because the seeder calls Model::unguard() and its JSON-loading do/while loops
 * create deeply nested savepoints that conflict with Laravel's transaction wrapping.
 * We manually clear the table in setUp() and tearDown() instead.
 */
class LandingPageDataTableSeederTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		// Clear the table so we get a deterministic starting state
		// (TestCase skips migrate:fresh to save time, so we truncate manually)
		\Illuminate\Support\Facades\DB::table('landing_page_settings')->delete();
	}

	protected function tearDown(): void
	{
		// Clean up seeder data after each test
		\Illuminate\Support\Facades\DB::table('landing_page_settings')->delete();
		parent::tearDown();
	}

	/**
	 ** @test
	 *
	 ** Seeds default landing-page settings with static values and JSON-fallbacks.
	 **/
	public function it_seeds_default_settings_with_static_and_json_fallbacks()
	{
		// table starts empty
		$this->assertDatabaseCount((new LandingPageSetting)->getTable(), 0);

		// run the seeder
		(new LandingPageDataTableSeeder())->run();

		// static key/value pairs -- values match LandingPageDataTableSeeder::DEFAULT_DATA_ARR
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => SettingsConstants::TB_STT_K,
			'value' => SettingsConstants::TB_STT_DEF,
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => SettingsConstants::TB_NTF_MSG_K,
			'value' => 'Technology assistance and support with over 30 years of tradition. Talk to Brand New Ideas Company and protect your data and devices today.',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => SettingsConstants::SL_K,
			'value' => 'site_logo.png',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => SettingsConstants::SD_K,
			'value' => 'Technology assistance and support focusing on digital security, stability, and humanized service for businesses and end users.',
		]);

		// JSON-sourced entries: seeder loads blob files if present, otherwise stores a fallback message.
		// We verify that at least one row per JSON key was inserted (either from blob or fallback).
		$jsonKeys = [
			SettingsConstants::MB_PG_K,
			SettingsConstants::FT_OF_FTS_K,
			SettingsConstants::OT_FTS_K,
			SettingsConstants::DC_OF_FTS_K,
			SettingsConstants::SC_SHTS_K,
			SettingsConstants::FAQ_FQS_K,
			SettingsConstants::TM_TMS_K,
		];
		foreach ($jsonKeys as $jsonKey) {
			$this->assertTrue(
				\Illuminate\Support\Facades\DB::table('landing_page_settings')
					->where('name', $jsonKey)
					->exists(),
				"Expected at least one row with name={$jsonKey}"
			);
		}

		// Total entry count: 54 (DEFAULT_DATA_ARR keys) when JSON blobs are absent,
		// or more when blob files are present (each JSON array item gets its own row).
		// Assert at least the minimum static-only count.
		$this->assertGreaterThanOrEqual(
			54,
			\Illuminate\Support\Facades\DB::table('landing_page_settings')->count(),
			'Seeder must insert at least 54 rows (all static defaults).'
		);
	}

	/**
	 ** @test
	 *
	 ** Marks incomplete: cannot simulate valid JSON parsing success in unit test.
	 **/
	public function it_marks_json_success_parsing_as_incomplete()
	{
		$this->markTestIncomplete('Cannot easily simulate successful JSON files loading in this environment.');
	}
}
