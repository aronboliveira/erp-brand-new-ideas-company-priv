<?php

namespace Tests\Unit\Seeders;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\{Carbon, Str};
use Illuminate\Support\Facades\{DB, Notification};
use App\Config\Constants\{
	DatabaseConstants,
	NotificationsConstants,
	SeedersTemplating,
	UsersConstants
};
use Database\Seeders\NotificationSeeder;

class NotificationSeederTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 *
	 ** Seeds notification templates and their language rows,
	 ** using the system default user when no users exist,
	 ** and sets the correct creator ID and timestamps.
	 **/
	public function it_seeds_notification_templates_and_languages_using_system_user()
	{
		// Prevent any real notifications from being dispatched
		Notification::fake();

		// Freeze "now" for predictable timestamps
		$now = Carbon::create(2025, 6, 10, 12, 0, 0);
		Carbon::setTestNow($now);

		// Ensure the users table is empty initially
		$this->assertDatabaseCount(DatabaseConstants::TABLE_USERS, 0);

		// Run the seeder
		(new NotificationSeeder())->run();

		// A default system user should have been inserted
		$this->assertDatabaseCount(DatabaseConstants::TABLE_USERS, 1);
		$creatorId = DB::table(DatabaseConstants::TABLE_USERS)->value('id');
		$this->assertEquals(DatabaseConstants::DEFAULT_UUID, $creatorId);

		// Verify notification templates
		$notifications = SeedersTemplating::NOTIFICATIONS_DICT;
		$expectedTemplatesCount = count($notifications);
		$this->assertDatabaseCount(DatabaseConstants::TABLE_NOTIFICATION_TEMPLATES, $expectedTemplatesCount);

		$templates = DB::table(DatabaseConstants::TABLE_NOTIFICATION_TEMPLATES)->get();
		$ids = $templates->pluck('id')->toArray();
		$this->assertCount($expectedTemplatesCount, array_unique($ids));

		foreach ($templates as $tpl) {
			$this->assertTrue(Str::isUuid($tpl->id), "Invalid UUID: {$tpl->id}");
			$this->assertEquals($creatorId, $tpl->creator);
			$this->assertEquals($now->toDateTimeString(), $tpl->created_at);
			$this->assertEquals($now->toDateTimeString(), $tpl->updated_at);
		}

		// Verify notification template language rows
		$defaultTemplates = SeedersTemplating::NOTIFICATIONS_DEFAULT_TEMPLATES['notification'];
		$expectedLangCount = 0;
		foreach ($defaultTemplates as $slug => $data) {
			$expectedLangCount += count($data[NotificationsConstants::COL_TEMPL_LG]);
		}
		$this->assertDatabaseCount(DatabaseConstants::TABLE_NOTIFICATION_TEMPLATE_LANGS, $expectedLangCount);

		$langs = DB::table(DatabaseConstants::TABLE_NOTIFICATION_TEMPLATE_LANGS)->get();
		$langIds = $langs->pluck('id')->toArray();
		$this->assertCount($expectedLangCount, array_unique($langIds));

		foreach ($langs as $lang) {
			$this->assertTrue(Str::isUuid($lang->id), "Invalid UUID: {$lang->id}");
			$this->assertEquals($creatorId, $lang->creator);
			$this->assertEquals($now->toDateTimeString(), $lang->created_at);
			$this->assertEquals($now->toDateTimeString(), $lang->updated_at);
		}
	}

	/**
	 ** @test
	 *
	 ** Uses an existing user as creator when one is already present,
	 ** and applies that creator ID to all new rows.
	 **/
	public function it_uses_existing_user_as_creator_when_present()
	{
		Notification::fake();
		$now = Carbon::create(2025, 6, 10, 12, 0, 0);
		Carbon::setTestNow($now);

		// Insert a pre-existing user
		$customId = (string) Str::uuid();
		DB::table(DatabaseConstants::TABLE_USERS)->insert([
			'id'                  => $customId,
			UsersConstants::COL_NM => 'Existing User',
			UsersConstants::COL_EM => 'existing@yourapp.test',
			UsersConstants::COL_PW => bcrypt('secret'),
			UsersConstants::COL_EM_V_AT => $now,
			UsersConstants::COL_C_AT    => $now,
			UsersConstants::COL_U_AT    => $now,
		]);

		(new NotificationSeeder())->run();

		// Only the pre-existing user should remain
		$this->assertDatabaseCount(DatabaseConstants::TABLE_USERS, 1);
		$creatorId = DB::table(DatabaseConstants::TABLE_USERS)->value('id');
		$this->assertEquals($customId, $creatorId);

		// All templates must reference the existing user
		DB::table(DatabaseConstants::TABLE_NOTIFICATION_TEMPLATES)
			->get()
			->each(fn ($row) => $this->assertEquals($customId, $row->creator));

		// All language rows must reference the existing user
		DB::table(DatabaseConstants::TABLE_NOTIFICATION_TEMPLATE_LANGS)
			->get()
			->each(fn ($row) => $this->assertEquals($customId, $row->creator));
	}

	/**
	 ** @test
	 *
	 ** Marks incomplete: cannot simulate exceeding the UUID retry limit in unit tests.
	 **/
	public function it_marks_uuid_retry_limit_as_incomplete()
	{
		$this->markTestIncomplete('Cannot simulate over 100,000 duplicate UUID attempts.');
	}
}
