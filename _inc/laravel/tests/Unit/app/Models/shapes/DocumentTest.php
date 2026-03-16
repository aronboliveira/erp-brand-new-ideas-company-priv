<?php

namespace Tests\Unit\Models;

use App\Models\Document;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
class DocumentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** The $fillable array must remain consistent
	 ** with the private constant to avoid silent edits.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$expected = [
			'file_path',
			'url',
			'name',
			'extension',
			'mime_type',
			'last_accessed',
			'size',
			'description',
			'notes',
			'download_count',
			'file_size',
			'permission_rules',
			'viewers',
			'editors',
			'executors',
			'expiration_date',
			'type',
			'number',
			'is_required',
			'is_private',
		];

		$this->assertSame($expected, (new Document)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** user() must be a HasOne relation mapping
	 ** users.id ← documents.created_by.
	 **/
	public function user_relation_is_has_one(): void
	{
		$rel = (new Document)->createdBy();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('created_by',         $rel->getForeignKeyName());
		$this->assertSame('id', $rel->getOwnerKeyName());
	}
}
