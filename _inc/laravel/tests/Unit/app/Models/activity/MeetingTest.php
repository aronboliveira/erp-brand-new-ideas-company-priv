<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Meeting;

class MeetingTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Meeting is mass assignable for branch_id, department_id, employee_id, title, date, time, note, and created_by
	 **/
	public function meeting_is_fillable()
	{
		$data = [
			'branch_id'     => 'branch-123',
			'department_id' => 'dept-456',
			'employee_id'   => 'emp-789',
			'title'         => 'Monthly Sync',
			'date'          => '2025-05-25',
			'time'          => '14:30:00',
			'note'          => 'Discuss monthly targets',
			'created_by'    => 'user_001',
		];

		$meeting = Meeting::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $meeting->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** Meeting uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function meeting_uses_uuid_for_primary_key()
	{
		$meeting = Meeting::create([
			'branch_id'     => 'branch-abc',
			'department_id' => 'dept-def',
			'employee_id'   => 'emp-ghi',
			'title'         => 'One-off Meeting',
			'date'          => '2025-06-01',
			'time'          => '09:00:00',
			'note'          => 'Initial planning',
			'created_by'    => 'user_002',
		]);

		$key = $meeting->getKey();

		$this->assertIsString($key);
		$this->assertFalse($meeting->getIncrementing());
		$this->assertSame('string', $meeting->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}
}
