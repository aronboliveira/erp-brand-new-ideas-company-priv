<?php

/**
 * Unit-tests for App\Models\Note
 */

namespace Tests\Unit\Models;

use App\Models\Note;
use Tests\TestCase;

class NoteTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** A Note must use a string UUID PK with
	 ** `$incrementing = false`.
	 **/
	public function primary_key_is_uuid_string(): void
	{
		$note = new Note;

		$this->assertFalse($note->getIncrementing());
		$this->assertSame('string', $note->getKeyType());
	}

	/**
	 ** @test
	 *
	 ** The fillable array must contain only the
	 ** five whitelisted columns.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'id',
			'note',
			'module_id',
			'module_type',
			'note_created_by',
		];

		$this->assertSame($expected, (new Note)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** The creator() relation must be BelongsTo
	 ** via `note_created_by`.
	 **/
	public function creator_relation_is_belongs_to(): void
	{
		$rel = (new Note)->creator();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('note_created_by', $rel->getForeignKeyName());
	}

	/**
	 ** @test
	 *
	 ** The global “orderById” scope must always
	 ** sort by id **DESC**.
	 **/
	public function global_scope_orders_by_id_desc(): void
	{
		$query    = Note::query()->getQuery();
		$orderings = $query->orders ?? [];

		$this->assertNotEmpty($orderings);
		$this->assertSame('id',        $orderings[0]['column']);
		$this->assertSame('desc', strtolower($orderings[0]['direction']));
	}
}
