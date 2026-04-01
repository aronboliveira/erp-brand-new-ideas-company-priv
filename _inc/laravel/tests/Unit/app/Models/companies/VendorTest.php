<?php
// tests/Unit/Models/VendorTest.php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\{
	Database\Eloquent\Relations\BelongsTo,
	Foundation\Testing\RefreshDatabase,
	Support\Carbon
};
use App\Models\{Vendor, User};

class VendorTest extends TestCase
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
	 ** Vendor is mass assignable for all fillable fields
	 **/
	public function vendor_is_fillable()
	{
		$user = User::factory()->create();

		$data = [
			'vendor_id'        => 10,
			'name'             => 'Acme Corp',
			'email'            => uniqid('vendor') . '@example.com',
			'tax_number'       => 'TAX123',
			'password'         => 'secret',
			'contact'          => '555-1234',
			'avatar'           => 'avatar.png',
			'created_by'       => $user?->id,
			'is_active'        => true,
			'email_verified_at' => '2025-06-01 10:00:00',
			'billing_name'     => 'Acme Billing',
			'billing_country'  => 'United States',
			'billing_state'    => 'NY',
			'billing_city'     => 'New York',
			'billing_phone'    => '555-5678',
			'billing_zip'      => '10001',
			'billing_address'  => '1 Acme Way',
			'shipping_name'    => 'Acme Shipping',
			'shipping_country' => 'United States',
			'shipping_state'   => 'NY',
			'shipping_city'    => 'New York',
			'shipping_phone'   => '555-8765',
			'shipping_zip'     => '10002',
			'shipping_address' => '2 Acme Lane',
			'lang'             => 'en',
			'balance'          => 1234.56,
		];

		$vendor = Vendor::create($data);

		// assert primitive values and casts
		$this->assertTrue((bool)$vendor->is_active);
		$this->assertInstanceOf(Carbon::class, $vendor->email_verified_at);
		$this->assertSame('1234.56', (string)$vendor->balance);

		foreach (['vendor_id', 'name', 'email', 'tax_number', 'password', 'contact', 'avatar', 'created_by', 'billing_name', 'billing_country', 'billing_state', 'billing_city', 'billing_phone', 'billing_zip', 'billing_address', 'shipping_name', 'shipping_country', 'shipping_state', 'shipping_city', 'shipping_phone', 'shipping_zip', 'shipping_address', 'lang'] as $field) {
			$this->assertEquals((string)$data[$field], (string)$vendor->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** uses UUID for primary key
	 **/
	public function vendor_uses_uuid_for_primary_key()
	{
		$vendor = Vendor::factory()->create();
		$key   = $vendor->getKey();

		$this->assertIsString($key);
		$this->assertFalse($vendor->getIncrementing());
		$this->assertSame('string', $vendor->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** creator() relation resolves to User via created_by
	 **/
	public function creator_relation_resolves_to_user_model()
	{
		$relation = (new Vendor)->creator();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(User::class,            get_class($relation->getRelated()));
		$this->assertSame('created_by',           $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}
}
