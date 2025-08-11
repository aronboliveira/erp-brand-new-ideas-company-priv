<?php

namespace Tests\Unit\Models;

use App\Models\ContractAttachment;
use Tests\TestCase;

class ContractAttachmentTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Ensure the table name is hard-wired
	 ** to "contract_attachment" as expected.
	 **/
	public function it_uses_the_correct_table_name(): void
	{
		$this->assertSame(
			'contract_attachment',
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
		$ref     = new \ReflectionClass(ContractAttachment::class);
		$expected = $ref->getConstant('FILLABLE_FIELDS');

		$this->assertSame($expected, (new ContractAttachment)->getFillable());
	}
}
