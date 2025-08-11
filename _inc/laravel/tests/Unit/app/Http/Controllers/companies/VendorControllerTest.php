<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Plan;
use App\Http\Controllers\VendorController;

class VendorControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Guests are redirected to login when accessing the vendor index.
	 **/
	public function guest_cannot_access_index()
	{
		$response = $this->get(action([VendorController::class, 'index']));
		$response->assertRedirect('/login');
	}

	/**
	 ** @test
	 **
	 ** Authenticated users can view their vendors on the index page.
	 **/
	public function user_can_view_index()
	{
		$user = User::factory()->create();
		Vendor::factory()->create([
			'name'       => 'Acme Co',
			'contact'    => '1234567890',
			'email'      => 'acme@example.com',
			'created_by' => $user?->id,
			'vendor_id'  => 1,
		]);
		$this->actingAs($user);

		$response = $this->get(action([VendorController::class, 'index']));

		$response->assertStatus(200);
		$response->assertViewIs('vendor.index');
		$response->assertViewHas('vendors', function ($vendors) {
			return $vendors->first()->name === 'Acme Co';
		});
	}

	/**
	 ** @test
	 **
	 ** Store action creates a vendor when under plan limit and redirects back to index.
	 **/
	public function store_creates_vendor_and_redirects()
	{
		$user = User::factory()->create();
		$plan = Plan::factory()->create(['max_vendors' => -1]);
		$user->plan = $plan->id;
		$user?->save();

		$this->actingAs($user);

		$response = $this->post(action([VendorController::class, 'store']), [
			'name'       => 'New Vendor',
			'contact'    => '555-1234',
			'email'      => 'new@example.com',
		]);

		$response->assertRedirect(route('vendor.index'));
		$this->assertDatabaseHas('vendors', [
			'name'       => 'New Vendor',
			'email'      => 'new@example.com',
			'created_by' => $user?->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** Users cannot edit vendors they do not own.
	 **/
	public function non_owner_cannot_access_edit()
	{
		$owner = User::factory()->create();
		$other = User::factory()->create();
		$vendor = Vendor::factory()->create([
			'created_by' => $owner->id,
			'vendor_id'  => 1,
		]);

		$this->actingAs($other);
		$response = $this->get(action([VendorController::class, 'edit'], $vendor));

		$response->assertRedirect(route('vendor.index'));
	}

	/**
	 ** @test
	 **
	 ** Owners can update their own vendor and are redirected to index.
	 **/
	public function owner_can_update_vendor()
	{
		$user = User::factory()->create();
		$vendor = Vendor::factory()->create([
			'created_by' => $user?->id,
			'vendor_id'  => 1,
			'name'       => 'Old Name',
			'contact'    => '000',
			'email'      => 'old@example.com',
		]);

		$this->actingAs($user);

		$response = $this->put(action([VendorController::class, 'update'], $vendor), [
			'name'    => 'Updated Name',
			'contact' => '999',
		]);

		$response->assertRedirect(route('vendor.index'));
		$this->assertDatabaseHas('vendors', [
			'id'      => $vendor->id,
			'name'    => 'Updated Name',
			'contact' => '999',
		]);
	}

	/**
	 ** @test
	 **
	 ** Owners can delete their own vendor and are redirected to index.
	 **/
	public function owner_can_delete_vendor()
	{
		$user = User::factory()->create();
		$vendor = Vendor::factory()->create([
			'created_by' => $user?->id,
			'vendor_id'  => 1,
		]);

		$this->actingAs($user);

		$response = $this->delete(action([VendorController::class, 'destroy'], $vendor));

		$response->assertRedirect(route('vendor.index'));
		$this->assertDatabaseMissing('vendors', [
			'id' => $vendor->id,
		]);
	}
}
