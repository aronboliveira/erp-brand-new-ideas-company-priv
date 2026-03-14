<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** getRemark returns the raw remark when log_type is unrecognized
	 **/
	public function get_remark_returns_raw_remark_for_unknown_log_type()
	{
		$user = User::factory()->create();
		$raw = 'just some remark text';

		$activity = ActivityLog::create([
			'user_id'  => $user?->id,
			'log_type' => 'Completely Unknown',
			'remark'   => $raw,
		]);

		$this->assertSame($raw, $activity->getRemark());
	}

	/**
	 ** @test
	 **
	 ** getRemark builds an "Invite User" message correctly
	 **/
	public function get_remark_builds_invite_user_message()
	{
		$user = User::factory()->create([
			'name' => 'Jane Doe',
		]);

		$payload = ['title' => 'Project Phoenix'];

		$activity = ActivityLog::create([
			'user_id'  => $user?->id,
			'log_type' => 'Invite User',
			'remark'   => json_encode($payload),
			'document' => null,
		]);

		$result = $activity->getRemark();
		$this->assertStringContainsString('has invited', $result);
		$this->assertStringContainsString('Project Phoenix', $result);
		$this->assertStringContainsString('Jane Doe', $result);
	}

	/**
	 ** @test
	 **
	 ** user() relation returns the correct User instance
	 **/
	public function user_relation_resolves_to_user_model()
	{
		$user = User::factory()->create();

		$activity = new ActivityLog(['user_id' => $user?->id]);
		$this->assertInstanceOf(User::class, $activity->user()->getRelated());
		$this->assertEquals('user_id', $activity->user()->getForeignKeyName());
	}
}
