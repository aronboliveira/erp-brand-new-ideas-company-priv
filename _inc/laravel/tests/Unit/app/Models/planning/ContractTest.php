<?php

namespace Tests\Unit\Models;

use Mockery;
use Tests\TestCase;
use App\Models\Contract;
use Illuminate\Support\Collection;

class ContractTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** status() must expose the canonical
	 ** accept / decline map exactly as declared.
	 **/
	public function status_method_returns_expected_options(): void
	{
		$this->assertSame(
			['accept' => 'Accept', 'decline' => 'Decline'],
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
		Mockery::mock('alias:' . Contract::class)
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
	 ** The “clients” relation must be HasOne
	 ** using User::id → contracts.client_name.
	 **/
	public function clients_relation_is_has_one_with_correct_keys(): void
	{
		$rel = (new Contract)->clients();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$rel
		);
		$this->assertSame('id',          $rel->getForeignKeyName());
		$this->assertSame('client_name', $rel->getLocalKeyName());
	}

	/**
	 ** @test
	 *
	 ** Guard against accidental changes in
	 ** the $fillable mass-assignment list.
	 **/
	public function fillable_array_matches_declared_constant(): void
	{
		$ref      = new \ReflectionClass(Contract::class);
		$expected = $ref->getConstant('FILLABLE');
		$fillable = (new Contract)->getFillable();

		$this->assertSame($expected, $fillable);
	}

	protected function tearDown(): void
	{
		Mockery::close();
		parent::tearDown();
	}
}
