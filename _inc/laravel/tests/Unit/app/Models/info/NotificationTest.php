<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Auth, Route};
use App\Models\{Notification, User};

class NotificationTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');

		// Create a dummy named route for deals.show used in toHtml()
		Route::get('/deals/{id}', fn ($id) => 'deal')->name('deals.show');

		// Seed a notification template so the booted hook doesn't clear the data field
		\Illuminate\Support\Facades\DB::table('notification_templates')->insertOrIgnore([
			'id'         => (string) \Illuminate\Support\Str::uuid(),
			'name'       => 'assign_deal',
			'slug'       => 'assign_deal',
			'created_by' => \App\Config\Constants\DatabaseConstants::DEFAULT_UUID,
		]);
	}

	/**
	 ** @test
	 **
	 ** The Notification model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'user_id',
			'type',
			'data',
			'attachments',
			'metadata',
			'tags',
			'platforms',
			'sent_at',
			'sent_by',
			'is_read',
			'read_at',
		];
		$this->assertEquals($expected, (new Notification())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** toHtml() returns an empty string when data has no updated_by.
	 **/
	public function to_html_returns_empty_without_updated_by()
	{
		$saUuid = \App\Config\Constants\DatabaseConstants::DEFAULT_UUID;
		$notif = Notification::create([
			'id'         => (string) \Illuminate\Support\Str::uuid(),
			'user_id'    => $saUuid,
			'type'       => 'assign_deal',
			'data'       => json_encode([]),
			'is_read'    => false,
			'created_by' => $saUuid,
			'sent_by'    => $saUuid,
			'sent_at'    => now(),
		]);

		$this->assertSame('', $notif->toHtml());
	}

	/**
	 ** @test
	 **
	 ** toHtml() generates the correct HTML for an 'assign_deal' notification.
	 **/
	public function to_html_generates_assign_deal_markup()
	{
		$user = User::factory()->create(['name' => 'Alice']);
		Auth::login($user);

		$dealId = 42;
		$saUuid = \App\Config\Constants\DatabaseConstants::DEFAULT_UUID;
		$notif = Notification::create([
			'id'         => (string) \Illuminate\Support\Str::uuid(),
			'user_id'    => $user->id,
			'type'       => 'assign_deal',
			'data'       => json_encode([
				'updated_by' => $user->id,
				'deal_id'    => $dealId,
				'name'       => 'Important Deal'
			]),
			'is_read'    => false,
			'created_by' => $saUuid,
			'sent_by'    => $saUuid,
			'sent_at'    => now(),
		]);

		$html = $notif->toHtml();

		$this->assertStringContainsString("/deals/{$dealId}", $html);
		$this->assertStringContainsString('Alice', $html);
		$this->assertStringContainsString('Added you', $html);
		$this->assertStringContainsString('<b class=\'font-weight-bold\'>Important Deal</b>', $html);
		$this->assertStringStartsWith('<a href=', trim($html));
	}
}
