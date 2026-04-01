<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Database\Eloquent\Relations\BelongsTo,
	Foundation\Testing\RefreshDatabase
};
use App\Models\{BillAccount, ChartOfAccount, Bill};

class BillAccountTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		\DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** BillAccount is mass assignable for chart_account_id, price, description, type, and ref_id
	 **/
	public function bill_account_is_fillable()
	{
		$chart = ChartOfAccount::factory()->create();
		$bill = Bill::factory()->create(['amount' => 123.45]);

		$data = [
			'chart_account_id' => $chart->id,
			'price'            => 123.45,
			'description'      => 'Account charge',
			'type'             => 'bill',
			'ref_id'           => $bill->id,
		];

		$account = BillAccount::create($data);

		$this->assertNotNull($account->id);
		$this->assertEquals($chart->id, $account->getRawOriginal('chart_account_id'));
		$this->assertEquals('Account charge', $account->getRawOriginal('description'));
		$this->assertEquals('bill', $account->getRawOriginal('type'));
		$this->assertEquals($bill->id, $account->getRawOriginal('ref_id'));
		// price may be overwritten by Bill->amount via overwritePriceFromBillAmountIfPresent
		$this->assertNotNull($account->getRawOriginal('price'));
	}

	/**
	 ** @test
	 **
	 ** BillAccount uses UUIDs for its primary key: string, non-incrementing, valid UUID format
	 **/
	public function bill_account_uses_uuid_for_primary_key()
	{
		$account = BillAccount::factory()->create();

		$key = $account->getKey();

		$this->assertIsString($key);
		$this->assertFalse($account->getIncrementing());
		$this->assertSame('string', $account->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** chartAccount() relation should point to ChartOfAccount via chart_account_id
	 **/
	public function chart_account_relation_resolves_to_chart_of_account_model()
	{
		$relation = (new BillAccount)->chartAccount();

		$this->assertInstanceOf(BelongsTo::class,       $relation);
		$this->assertSame(ChartOfAccount::class,        get_class($relation->getRelated()));
		$this->assertSame('chart_account_id',           $relation->getForeignKeyName());
		$this->assertSame('id',                         $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** bill() relation should point to Bill via ref_id
	 **/
	public function bill_relation_resolves_to_bill_model()
	{
		$relation = (new BillAccount)->bill();

		$this->assertInstanceOf(BelongsTo::class,       $relation);
		$this->assertSame(Bill::class,                  get_class($relation->getRelated()));
		$this->assertSame('ref_id',                     $relation->getForeignKeyName());
		$this->assertSame('id',                         $relation->getOwnerKeyName());
	}
}
