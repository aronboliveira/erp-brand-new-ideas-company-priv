<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Branch;

class BranchTest extends TestCase
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
	 ** Branch is mass assignable for name
	 **/
	public function branch_is_fillable()
	{
		$data = [
			'name'       => 'Main Office',
		];

		$branch = Branch::create($data);

		$this->assertFillableMatches($data, $branch);
	}

	/**
	 ** @test
	 **
	 ** Branch uses UUID for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function branch_uses_uuid_for_primary_key()
	{
		$branch = Branch::factory()->create();
		$key   = $branch->getKey();

		$this->assertIsString($key);
		$this->assertFalse($branch->getIncrementing());
		$this->assertSame('string', $branch->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}
}
