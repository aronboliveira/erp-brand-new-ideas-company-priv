<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Customer;
use App\Models\Plan;
use Spatie\Permission\Models\Permission;

class CustomerControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** index should display customers for users with 'manage customer' permission
	 **/
	public function index_displays_customers_for_authorized_user()
	{
		Permission::create(['name' => 'manage customer']);
		$user = User::factory()->create();
		$user?->givePermissionTo('manage customer');

		// insert two customer records
		DB::table('customers')->insert([
			[
				'customer_id'       => 1,
				'created_by'        => $user?->creatorId(),
				'lang'              => 'en',
				'name'              => 'Cust A',
				'contact'           => '123',
				'email'             => 'a@example.com',
				'tax_number'        => '',
				'billing_name'      => '',
				'billing_country'   => '',
				'billing_state'     => '',
				'billing_city'      => '',
				'billing_phone'     => '',
				'billing_zip'       => '',
				'billing_address'   => '',
				'shipping_name'     => '',
				'shipping_country'  => '',
				'shipping_state'    => '',
				'shipping_city'     => '',
				'shipping_phone'    => '',
				'shipping_zip'      => '',
				'shipping_address'  => '',
			],
			[
				'customer_id'       => 2,
				'created_by'        => $user?->creatorId(),
				'lang'              => 'en',
				'name'              => 'Cust B',
				'contact'           => '456',
				'email'             => 'b@example.com',
				'tax_number'        => '',
				'billing_name'      => '',
				'billing_country'   => '',
				'billing_state'     => '',
				'billing_city'      => '',
				'billing_phone'     => '',
				'billing_zip'       => '',
				'billing_address'   => '',
				'shipping_name'     => '',
				'shipping_country'  => '',
				'shipping_state'    => '',
				'shipping_city'     => '',
				'shipping_phone'    => '',
				'shipping_zip'      => '',
				'shipping_address'  => '',
			],
		]);

		$response = $this->actingAs($user)->get(route('customer.index'));

		$response->assertStatus(200);
		$response->assertViewIs('customer.index');
		$response->assertViewHas('customers', function ($customers) {
			return $customers->count() === 2;
		});
	}

	/**
	 ** @test
	 **
	 ** index should redirect guests to login
	 **/
	public function index_redirects_guests_to_login()
	{
		$response = $this->get(route('customer.index'));
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** create should display the form for users with 'create customer' permission
	 **/
	public function create_displays_form_for_authorized_user()
	{
		Permission::create(['name' => 'create customer']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create customer');

		$response = $this->actingAs($user)->get(route('customer.create'));

		$response->assertStatus(200);
		$response->assertViewIs('customer.create');
		$response->assertViewHas('customFields');
	}

	/**
	 ** @test
	 **
	 ** store should redirect back on validation failure
	 **/
	public function store_redirects_back_on_validation_failure()
	{
		Permission::create(['name' => 'create customer']);
		$user = User::factory()->create();
		$user?->givePermissionTo('create customer');

		$response = $this->actingAs($user)
			->from(route('customer.create'))
			->post(route('customer.store'), [
				// missing required 'name' and 'contact' and 'email'
			]);

		$response->assertRedirect(route('customer.index'));
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** store should create a new customer and redirect on success
	 **/
	public function store_creates_customer_and_redirects_on_success()
	{
		// seed plan and settings
		$user = User::factory()->create();
		DB::table('plans')->insert([
			'id'             => $user?->creatorId(),
			'max_customers'  => -1,
		]);
		DB::table('settings')->insert([
			'name'       => 'default_language',
			'value'      => 'en',
			'created_by' => $user?->creatorId(),
		]);
		Permission::create(['name' => 'create customer']);
		$user?->givePermissionTo('create customer');

		$payload = [
			'name'    => 'New Cust',
			'contact' => '789',
			'email'   => 'newcust@example.com',
		];

		$response = $this->actingAs($user)
			->post(route('customer.store'), $payload);

		$response->assertRedirect(route('customer.index'));
		$this->assertDatabaseHas('customers', [
			'email'      => 'newcust@example.com',
			'created_by' => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** show should display the customer for authorized user
	 **/
	public function show_displays_customer_for_authorized_user()
	{
		$user = User::factory()->create();
		$user?->givePermissionTo('manage customer');

		// insert a customer
		$id = DB::table('customers')->insertGetId([
			'customer_id'       => 1,
			'created_by'        => $user?->creatorId(),
			'lang'              => 'en',
			'name'              => 'Show Cust',
			'contact'           => '000',
			'email'             => 'show@example.com',
			'tax_number'        => '',
			'billing_name'      => '',
			'billing_country'   => '',
			'billing_state'     => '',
			'billing_city'      => '',
			'billing_phone'     => '',
			'billing_zip'       => '',
			'billing_address'   => '',
			'shipping_name'     => '',
			'shipping_country'  => '',
			'shipping_state'    => '',
			'shipping_city'     => '',
			'shipping_phone'    => '',
			'shipping_zip'      => '',
			'shipping_address'  => '',
		]);

		$encryptedId = Crypt::encrypt($id);

		$response = $this->actingAs($user)->get(route('customer.show', $encryptedId));

		$response->assertStatus(200);
		$response->assertViewIs('customer.show');
		$response->assertViewHas('customer');
	}

	/**
	 ** @test
	 **
	 ** edit should display the edit form for users with 'edit customer' permission
	 **/
	public function edit_displays_form_for_authorized_user()
	{
		Permission::create(['name' => 'edit customer']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit customer');

		// insert a customer
		$id = DB::table('customers')->insertGetId([
			'customer_id'       => 1,
			'created_by'        => $user?->creatorId(),
			'lang'              => 'en',
			'name'              => 'Edit Cust',
			'contact'           => '111',
			'email'             => 'edit@example.com',
			'tax_number'        => '',
			'billing_name'      => '',
			'billing_country'   => '',
			'billing_state'     => '',
			'billing_city'      => '',
			'billing_phone'     => '',
			'billing_zip'       => '',
			'billing_address'   => '',
			'shipping_name'     => '',
			'shipping_country'  => '',
			'shipping_state'    => '',
			'shipping_city'     => '',
			'shipping_phone'    => '',
			'shipping_zip'      => '',
			'shipping_address'  => '',
		]);

		$response = $this->actingAs($user)->get(route('customer.edit', ['customer' => $id]));

		$response->assertStatus(200);
		$response->assertViewIs('customer.edit');
		$response->assertViewHasAll(['customer', 'customFields']);
	}

	/**
	 ** @test
	 **
	 ** update should redirect back on validation failure
	 **/
	public function update_redirects_back_on_validation_failure()
	{
		Permission::create(['name' => 'edit customer']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit customer');

		$id = DB::table('customers')->insertGetId([
			'customer_id'       => 1,
			'created_by'        => $user?->creatorId(),
			'lang'              => 'en',
			'name'              => 'Old Cust',
			'contact'           => '222',
			'email'             => 'old@example.com',
			'tax_number'        => '',
			'billing_name'      => '',
			'billing_country'   => '',
			'billing_state'     => '',
			'billing_city'      => '',
			'billing_phone'     => '',
			'billing_zip'       => '',
			'billing_address'   => '',
			'shipping_name'     => '',
			'shipping_country'  => '',
			'shipping_state'    => '',
			'shipping_city'     => '',
			'shipping_phone'    => '',
			'shipping_zip'      => '',
			'shipping_address'  => '',
		]);

		$response = $this->actingAs($user)
			->from(route('customer.index'))
			->put(route('customer.update', ['customer' => $id]), [
				'name'    => '',
				'contact' => '',
			]);

		$response->assertRedirect(route('customer.index'));
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** update should modify the customer and redirect on success
	 **/
	public function update_modifies_customer_and_redirects_on_success()
	{
		Permission::create(['name' => 'edit customer']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit customer');

		$id = DB::table('customers')->insertGetId([
			'customer_id'       => 1,
			'created_by'        => $user?->creatorId(),
			'lang'              => 'en',
			'name'              => 'Old Cust',
			'contact'           => '333',
			'email'             => 'old2@example.com',
			'tax_number'        => '',
			'billing_name'      => '',
			'billing_country'   => '',
			'billing_state'     => '',
			'billing_city'      => '',
			'billing_phone'     => '',
			'billing_zip'       => '',
			'billing_address'   => '',
			'shipping_name'     => '',
			'shipping_country'  => '',
			'shipping_state'    => '',
			'shipping_city'     => '',
			'shipping_phone'    => '',
			'shipping_zip'      => '',
			'shipping_address'  => '',
		]);

		$payload = [
			'name'    => 'New Cust',
			'contact' => '444',
		];

		$response = $this->actingAs($user)
			->put(route('customer.update', ['customer' => $id]), $payload);

		$response->assertRedirect(route('customer.index'));
		$this->assertDatabaseHas('customers', [
			'id'      => $id,
			'name'    => 'New Cust',
			'contact' => '444',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy should delete the customer and redirect on success
	 **/
	public function destroy_deletes_customer_and_redirects_on_success()
	{
		Permission::create(['name' => 'delete customer']);
		$user = User::factory()->create();
		$user?->givePermissionTo('delete customer');

		$id = DB::table('customers')->insertGetId([
			'customer_id'       => 1,
			'created_by'        => $user?->creatorId(),
			'lang'              => 'en',
			'name'              => 'To Delete',
			'contact'           => '555',
			'email'             => 'del@example.com',
			'tax_number'        => '',
			'billing_name'      => '',
			'billing_country'   => '',
			'billing_state'     => '',
			'billing_city'      => '',
			'billing_phone'     => '',
			'billing_zip'       => '',
			'billing_address'   => '',
			'shipping_name'     => '',
			'shipping_country'  => '',
			'shipping_state'    => '',
			'shipping_city'     => '',
			'shipping_phone'    => '',
			'shipping_zip'      => '',
			'shipping_address'  => '',
		]);

		$response = $this->actingAs($user)
			->delete(route('customer.destroy', ['customer' => $id]));

		$response->assertRedirect(route('customer.index'));
		$this->assertDatabaseMissing('customers', ['id' => $id]);
	}
}
