<?php

namespace Tests\Unit\Models;

use Mockery;
use Tests\TestCase;
use App\Models\Contract;
use Illuminate\Support\Collection;
use Tests\Concerns\SafeAliasMock;

class ContractTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		\DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}

	use SafeAliasMock;

	/**
	 ** @test
	 *
	 ** status() must expose the canonical
	 ** accept / decline map exactly as declared.
	 **/
	public function status_method_returns_expected_options(): void
	{
		$this->assertSame(
			[
				'draft'       => 'Draft',
				'pending'     => 'Pending',
				'active'      => 'Active',
				'suspended'   => 'Suspended',
				'completed'   => 'Completed',
				'cancelled'   => 'Cancelled',
				'expired'     => 'Expired',
				'archived'    => 'Archived',
				'undefined'   => 'Undefined',
				'accept'      => 'Accept',
				'decline'     => 'Decline',
				'not_started' => 'Not Started',
				'in_progress' => 'In Progress',
			],
			Contract::status()
		);
	}

	/**
	 ** @test
	 *
	 ** getContractSummary() should:
	 **   • call _checkLogin(),  
	 **   • sum the “value” field of each
	 **     contract in the collection,
	 **   • funnel that number through the
	 **     logged-in user’s priceFormat().
	 **/
	public function it_calculates_and_formats_the_contract_summary(): void
	{
		// Fake user implementing priceFormat().
		$fakeUser = new class
		{
			public function priceFormat($amount): string
			{
				return number_format($amount, 2, '.', ',');
			}
		};

		// Mock static _checkLogin().
		$this->aliasMock(Contract::class)
			->shouldReceive('_checkLogin')
			->once()
			->andReturn($fakeUser);

		$contracts = new Collection([
			(object) ['value' => 125.55],
			(object) ['value' =>  74.45],
		]);

		$result = Contract::getContractSummary($contracts);

		$this->assertSame('200.00', $result);
	}

	/**
	 ** @test
	 *
	 ** The "clients" relation must be BelongsTo
	 ** using contracts.client_id → clients.id.
	 **/
	public function clients_relation_is_has_one_with_correct_keys(): void
	{
		$rel = (new Contract)->clients();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('client_id', $rel->getForeignKeyName());
		$this->assertSame('id',        $rel->getOwnerKeyName());
	}

	/**
	 ** @test
	 *
	 ** Guard against accidental changes in
	 ** the $fillable mass-assignment list.
	 **/
	public function fillable_array_matches_declared_constant(): void
	{
		$expected = [
			'type',
			'title',
			'subject',
			'value',
			'currency',
			'description',
			'notes',
			'start_date',
			'end_date',
			'contract_description',
			'status',
			'renewable',
			'auto_renew',
			'frequency',
			'company',
			'client_name',
			'obligee_name',
			'obligor_name',
			'obligee_identifier',
			'obligor_identifier',
			'obligee_address',
			'obligor_address',
			'obligee_contact',
			'obligor_contact',
			'company_signature',
			'client_signature',
			'client_signed_at',
			'company_signed_at',
			'approved_at',
			'approved_by',
			'rejected_at',
			'rejected_by',
			'witness_one_name',
			'witness_two_name',
			'witness_one_identifier',
			'witness_two_identifier',
			'witness_one_signature',
			'witness_two_signature',
			'witness_one_signed_at',
			'witness_two_signed_at',
			'project_id',
			'file_path',
			'attachment_paths',
			'metadata',
		];
		$fillable = (new Contract)->getFillable();

		$this->assertSame($expected, $fillable);
	}

	protected function tearDown(): void
	{
		Mockery::close();
		parent::tearDown();
	}
}
