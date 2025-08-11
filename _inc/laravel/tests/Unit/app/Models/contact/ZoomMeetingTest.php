<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{Foundation\Testing\RefreshDatabase, Support\Carbon};
use App\Models\{User, ZoomMeeting};

class ZoomMeetingTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
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
			'title', 'meeting_id', 'client_id', 'project_id', 'start_date',
			'duration', 'start_url', 'password', 'join_url', 'status', 'created_by'
		], $zoom->getFillable());
		$this->assertEquals(['client_name', 'project_name'], $zoom->getAppends());
	}

	/**
	 ** @test
	 **
	 ** getClientNameAttribute returns the user's name or empty string.
	 **/
	public function it_returns_client_name_attribute()
	{
		$user = User::factory()->create(['name' => 'Alice']);
		$zoom = ZoomMeeting::factory()->create(['client_id' => $user?->id]);
		$this->assertSame('Alice', $zoom->client_name);

		// when no user
		$zoom2 = ZoomMeeting::factory()->create(['client_id' => 999]);
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
	 ** projectName() relation returns HasOne to Project.
	 **/
	public function it_defines_project_name_relationship()
	{
		$zoom = new ZoomMeeting();
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$zoom->projectName()
		);
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
		$this->assertEquals([$u1->id, $u2->id], array_map(fn ($u) => $u->id, $result));
	}
}
