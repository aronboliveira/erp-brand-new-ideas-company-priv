<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\ClientDeal;

class ClientDealTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** ClientDeal is mass assignable for client_id and deal_id
	 **/
	public function client_deal_is_fillable()
	{
		$clientId = 'client-' . uniqid();
		$dealId   = 'deal-' . uniqid();

		$data = [
			'client_id' => $clientId,
			'deal_id'   => $dealId,
		];

		$clientDeal = ClientDeal::create($data);

		$this->assertEquals($clientId, $clientDeal->client_id);
		$this->assertEquals($dealId, $clientDeal->deal_id);
	}

	/**
	 ** @test
	 **
	 ** ClientDeal uses UUIDs for primary key: string, non-incrementing, and valid UUID format
	 **/
	public function client_deal_uses_uuid_for_primary_key()
	{
		$clientDeal = ClientDeal::create([
			'client_id' => 'client-' . uniqid(),
			'deal_id'   => 'deal-' . uniqid(),
		]);

		$key = $clientDeal->getKey();

		$this->assertIsString($key);
		$this->assertFalse($clientDeal->getIncrementing());
		$this->assertSame('string', $clientDeal->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}
}
