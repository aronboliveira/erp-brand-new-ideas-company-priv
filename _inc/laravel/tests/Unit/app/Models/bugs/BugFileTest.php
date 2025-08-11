<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Bug, BugFile};

class BugFileTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** BugFile is mass assignable for file, name, extension, file_size, created_by, bug_id, and user_type
	 **/
	public function bug_file_is_fillable()
	{
		$data = [
			'file'        => '/path/to/file.png',
			'name'        => 'file',
			'extension'   => 'png',
			'file_size'   => 1024,
			'created_by'  => 'user1',
			'bug_id'      => Bug::factory()->create()->id,
			'user_type'   => 'client',
		];

		$bf = BugFile::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $bf->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** primary key uses UUID: string, non-incrementing, valid UUID format
	 **/
	public function primary_key_is_uuid()
	{
		$bf = BugFile::factory()->create();

		$this->assertIsString($bf->getKey());
		$this->assertFalse($bf->getIncrementing());
		$this->assertSame('string', $bf->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$bf->getKey()
		);
	}
}
