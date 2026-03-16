<?php

namespace Tests\Unit\Models;

use App\Models\ContractNote;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
class ContractNoteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** Validate the HasOne “user” relation
	 ** created_by → users.id.
	 **/
	public function user_relation_is_has_one(): void
	{
		$rel = (new ContractNote)->user();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('user_id',         $rel->getForeignKeyName());
		$this->assertSame('id', $rel->getOwnerKeyName());
	}

	/**
	 ** @test
	 *
	 ** Ensure $fillable stays aligned with
	 ** the model constant.
	 **/
	public function fillable_fields_are_correct(): void
	{
		$expected = [
			'code',
			'contract_id',
			'user_id',
			'notes',
		];

		$this->assertSame($expected, (new ContractNote)->getFillable());
	}
}
