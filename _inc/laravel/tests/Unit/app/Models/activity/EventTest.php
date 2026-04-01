<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Event;

class EventTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Event is mass assignable for branch_id, department_id, employee_id, title, start_date, end_date, color, description, and created_by
	 **/
	public function event_is_fillable()
	{
		$data = [
			'branch_id'     => 'branch-123',
			'department_id' => 'dept-456',
			'employee_id'   => 'emp-789',
			'title'         => 'Team Meeting',
			'start_date'    => '2025-05-24',
			'end_date'      => '2025-05-25',
			'color'         => '#ff0000',
			'description'   => 'Quarterly planning session',
			'created_by'    => 'user_001',
		];

		$event = Event::create($data);

		$this->assertFillableMatches($data, $event);
	}

	/**
	 ** @test
	 **
	 ** Event uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function event_uses_uuid_for_primary_key()
	{
		$event = Event::create([
			'branch_id'     => 'branch-abc',
			'department_id' => 'dept-def',
			'employee_id'   => 'emp-ghi',
			'title'         => 'One-off Event',
			'start_date'    => '2025-06-01',
			'end_date'      => '2025-06-02',
			'color'         => '#00ff00',
			'description'   => 'Special training',
			'created_by'    => 'user_002',
		]);

		$key = $event->getKey();

		$this->assertIsString($key);
		$this->assertFalse($event->getIncrementing());
		$this->assertSame('string', $event->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}
}
