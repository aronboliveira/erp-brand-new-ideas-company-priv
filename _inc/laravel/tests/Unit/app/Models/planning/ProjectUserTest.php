<?php

namespace Tests\Unit\Models;

use App\Models\ProjectUser;
use Tests\TestCase;

class ProjectUserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** The fillable array must expose
	 ** project_id, user_id, invited_by.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'project_id',
			'user_id',
			'is_active',
			'invited_by',
			'invited_at',
			'invite_status',
			'invite_url',
			'invite_code',
			'joined_at',
			'accepted_at',
			'accepted_by',
			'left_at',
			'removed_by',
			'is_temporary',
			'expires_at',
			'last_edited_at',
			'role',
			'can_write_own_files',
			'can_write_others_files',
			'can_read_others_files',
			'is_project_leader',
			'hourly_price',
			'billable_hours',
			'allow_email_notifications',
			'allow_push_notifications',
			'allow_mention_notifications',
			'allow_status_update_notifications',
			'total_hours',
			'notes',
			'metadata',
			'preferences',
			'updated_by',
		];

		$this->assertSame($expected, (new ProjectUser)->getFillable());
	}
}
