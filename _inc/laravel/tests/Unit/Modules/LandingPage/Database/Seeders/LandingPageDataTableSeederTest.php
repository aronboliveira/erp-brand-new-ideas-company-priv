<?php

namespace Tests\Unit\Seeders;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\LandingPage\{
	Config\Constants\SettingsConstants,
	Database\Seeders\LandingPageDataTableSeeder,
	Entities\LandingPageSetting
};

class LandingPageDataTableSeederTest extends TestCase
{
	use RefreshDatabase;

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

		// static key/value pairs
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => SettingsConstants::TB_STT_K,
			'value' => SettingsConstants::TB_STT_DEF,
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => SettingsConstants::TB_NTF_MSG_K,
			'value' => '70% Special Offer. Don’t Miss it. The offer ends in 72 hours.',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => SettingsConstants::SL_K,
			'value' => 'site_logo.png',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => SettingsConstants::SD_K,
			'value' => 'We build modern web tools to help you jump-start your daily business work.',
		]);

		// JSON fallbacks (files not present in test environment)
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => SettingsConstants::MB_PG_K,
			'value' => 'Menubar file not found or unreadable',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => SettingsConstants::FT_OF_FTS_K,
			'value' => 'Features file not found or unreadable',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => SettingsConstants::OT_FTS_K,
			'value' => 'Other features file not found or unreadable',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => SettingsConstants::DC_OF_FTS_K,
			'value' => 'Discover file not found or unreadable',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => SettingsConstants::SC_SHTS_K,
			'value' => 'screenshots file not found or unreadable',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => SettingsConstants::FAQ_FQS_K,
			'value' => 'FAQ file not found or unreadable',
		]);
		$this->assertDatabaseHas('landing_page_settings', [
			'name'  => SettingsConstants::TM_TMS_K,
			'value' => 'Testimonials file not found or unreadable',
		]);

		// total entries count should equal number of keys seeded
		$expectedCount = 54;
		$this->assertDatabaseCount('landing_page_settings', $expectedCount);
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
