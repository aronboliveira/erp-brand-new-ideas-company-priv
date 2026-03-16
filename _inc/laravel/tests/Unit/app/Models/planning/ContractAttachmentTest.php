<?php

namespace Tests\Unit\Models;

use App\Models\ContractAttachment;
use Tests\TestCase;

use Illuminate\Support\Facades\DB;
class ContractAttachmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** Ensure the table name is hard-wired
	 ** to "contract_attachment" as expected.
	 **/
	public function it_uses_the_correct_table_name(): void
	{
		$this->assertSame(
			'contract_attachments',
			(new ContractAttachment)->getTable()
		);
	}

	/**
	 ** @test
	 *
	 ** Verify the $fillable array matches
	 ** the private constant list of fields.
	 **/
	public function fillable_array_matches_declared_constant(): void
	{
		$expected = [
			'code',
			'contract_id',
			'user_id',
			'submitted_at',
			'approved_by',
			'approved_at',
			'rejected_by',
			'rejected_at',
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
			'attachment_type',
			'files',
			'metadata',
		];

		$this->assertSame($expected, (new ContractAttachment)->getFillable());
	}
}
