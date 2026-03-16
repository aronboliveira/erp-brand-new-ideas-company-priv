<?php

use Tests\TestCase;
use Illuminate\{
	Foundation\Testing\RefreshDatabase,
	Support\Str
};
use App\Models\Pipeline;

use Illuminate\Support\Facades\DB;
class PipelineTest extends TestCase
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
	 ** Pipeline is fillable for name and created_by
	 **/
	public function pipeline_is_fillable()
	{
		$data = [
			'name'       => 'Sales Pipeline',
			'created_by' => Str::uuid()->toString(),
		];

		$pl = Pipeline::create($data);

		$this->assertFillableMatches($data, $pl);
	}

	/**
	 ** @test
	 **
	 ** Uses UUID for primary key: string, non-incrementing, valid UUID
	 **/
	public function pipeline_primary_key_is_uuid()
	{
		$pl = Pipeline::factory()->create();
		$key = $pl->getKey();

		$this->assertIsString($key);
		$this->assertFalse($pl->getIncrementing());
		$this->assertSame('string', $pl->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}
}
