<?php

/**
 * Unit-tests for App\Models\PlanRequest
 */

namespace Tests\Unit\Models;

use App\Models\PlanRequest;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
class PlanRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** The plan() relation must be HasOne
	 ** using Plan::id ← plan_requests.plan_id.
	 **/
	public function plan_relation_is_has_one(): void
	{
		$rel = (new PlanRequest)->plan();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('plan_id',      $rel->getForeignKeyName());
		$this->assertSame('id', $rel->getOwnerKeyName());
	}

	/**
	 ** @test
	 *
	 ** The user() relation must be HasOne
	 ** using User::id ← plan_requests.user_id.
	 **/
	public function user_relation_is_has_one(): void
	{
		$rel = (new PlanRequest)->user();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('user_id',      $rel->getForeignKeyName());
		$this->assertSame('id', $rel->getOwnerKeyName());
	}

	/**
	 ** @test
	 *
	 ** Check the $fillable whitelist for
	 ** mass-assignment consistency.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'user_id',
			'plan_id',
			'duration',
			'notes',
			'attachments',
		];

		$this->assertSame($expected, (new PlanRequest)->getFillable());
	}
}
