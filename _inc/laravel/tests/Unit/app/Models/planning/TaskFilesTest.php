<?php

namespace Tests\Unit\app\Models\planning;

use App\Models\TaskFile;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
class TaskFilesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** Ensure mass-assignment list is as expected.
	 **/
	public function fillable_array_is_correct(): void
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
		];

		$this->assertSame($expected, (new TaskFile)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** user() relation should be HasOne.
	 **/
	public function user_relation_is_has_one(): void
	{
		$rel = (new TaskFile)->createdBy();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('created_by',        $rel->getForeignKeyName());
		$this->assertSame('id', $rel->getOwnerKeyName());
	}
}
