<?php

namespace Tests\Unit\Models;

use App\Models\EmployeeDocument;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
class EmployeeDocumentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** The $fillable array must include all
	 ** declared fields without extra items.
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
			'employee_id',
			'document_id',
			'document_value',
		];

		$this->assertSame($expected, (new EmployeeDocument)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** employee(), document(), and creator()
	 ** must each be BelongsTo relations with correct keys.
	 **/
	public function relations_are_belongs_to(): void
	{
		$ed = new EmployeeDocument;

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$ed->employee()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$ed->document()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$ed->creator()
		);
	}
}
