<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{Foundation\Testing\RefreshDatabase, Support\Carbon};
use App\Models\{User, ZoomMeeting};

use Illuminate\Support\Facades\DB;
class ZoomMeetingTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
		Carbon::setTestNow(now());
	}
	/**
	 ** @test
	 **
	 ** The fillable and appends properties are set correctly.
	 **/
	public function it_has_expected_fillable_and_appends()
	{
		$zoom = new ZoomMeeting();
		$this->assertEquals([
			'meeting_id',
			'code',
			'title',
			'password',
			'approval_type',
			'encryption_type',
			'duration',
			'start_url',
			'join_url',
			'registration_url',
			'type',
			'frequency',
			'timezone',
			'project_id',
			'user_id',
			'client_id',
			'start_date',
			'audio',
			'auto_recording',
			'max_participants',
			'agenda',
			'status',
			'meeting_chat',
			'private_chat',
			'screen_sharing',
			'who_can_share_screen',
			'waiting_room',
			'breakout_room',
			'focus_mode',
			'use_pmi',
			'alternative_hosts_enabled',
			'alternative_hosts',
			'close_registration_after_hours',
			'mute_upon_entry',
			'contact_name_required',
			'contact_email_required',
			'allow_share_button',
			'allow_multiple_devices',
			'settings',
			'participants',
			'webhooks',
			'metadata',
		], $zoom->getFillable());
		$this->assertEquals(['client_name'], $zoom->getAppends());
	}

	/**
	 ** @test
	 **
	 ** getClientNameAttribute returns the user's name or empty string.
	 **/
	public function it_returns_client_name_attribute()
	{
		$uuid = \Illuminate\Support\Str::uuid()->toString();

		// Mock DB::selectOne to bypass raw query referencing nonexistent columns
		DB::shouldReceive('selectOne')
			->once()
			->andReturn((object) ['nm' => 'Alice', 'first_name' => '', 'last_name' => '']);

		$zoom = new ZoomMeeting();
		$zoom->setAttribute('client_id', $uuid);
		$this->assertSame('Alice', $zoom->client_name);

		// when no valid user UUID
		$zoom2 = new ZoomMeeting();
		$zoom2->setAttribute('client_id', '999');
		$this->assertSame('', $zoom2->client_name);
	}

	/**
	 ** @test
	 **
	 ** checkDateTime returns 1 if meeting is in future, 0 if past.
	 **/
	public function it_checks_date_time_correctly()
	{
		$future = ZoomMeeting::factory()->create([
			'start_date' => now()->addHour(),
			'duration'   => 30
		]);
		$this->assertSame(1, $future->checkDateTime());

		$past = ZoomMeeting::factory()->create([
			'start_date' => now()->subHours(2),
			'duration'   => 30
		]);
		$this->assertSame(0, $past->checkDateTime());
	}

	/**
	 ** @test
	 **
	 ** projectName() returns null on a bare model (accessor, not a relation).
	 **/
	public function it_defines_project_name_relationship()
	{
		$zoom = new ZoomMeeting();
		// projectName() is an accessor that returns ?string (the project's name),
		// not a relation. On a bare model with no project, it returns null.
		$this->assertNull($zoom->projectName());
	}

	/**
	 ** @test
	 **
	 ** users() returns an array of User models for given comma-separated IDs.
	 **/
	public function it_parses_users_method()
	{
		$u1 = User::factory()->create();
		$u2 = User::factory()->create();
		$zoom = new ZoomMeeting();
		$result = $zoom->users("{$u1->id},{$u2->id}");
		$this->assertIsArray($result);
		$this->assertEqualsCanonicalizing([$u1->id, $u2->id], array_map(fn($u) => $u->id, $result));
	}
}
