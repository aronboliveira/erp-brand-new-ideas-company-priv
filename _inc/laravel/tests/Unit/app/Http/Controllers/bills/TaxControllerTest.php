<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{
	BillProduct,
	InvoiceProduct,
	ProposalProduct,
	Tax,
	User
};
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

class TaxControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private Tax $tax;

	protected function setUp(): void
	{
		parent::setUp();

		// Allow all permissions by default
		Gate::before(fn () => true);

		// Macro so creatorId() returns the user's own ID
		User::macro(
			'creatorId',
			/** 
			 * @this \App\Models\User 
			 * @return int|string
			 **/
			function (): int|string {
				/** @var \App\Models\User $this */
				return $this->id;
			}
		);

		// Create and authenticate a company user
		$this->company = User::factory()->create(['type' => 'company']);
		$this->actingAs($this->company);

		// Create a Tax record owned by this user
		$this->tax = Tax::factory()->create([
			'created_by' => $this->company->creatorId(),
		]);
	}


	/**
	 ** @test
	 **
	 ** Index should list all taxes for an authorized user.
	 **/
	public function test_index_lists_all_taxes_for_authorized_user()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'manage constant tax']);
		$user?->givePermissionTo('manage constant tax');

		Tax::factory()->count(3)->create(['created_by' => $user?->creatorId()]);
		Tax::factory()->create(); // other user's tax

		$response = $this->actingAs($user)->get(route('taxes.index'));

		$response->assertStatus(200)
			->assertViewIs('taxes.index')
			->assertViewHas('taxes', function ($taxes) use ($user) {
				return $taxes->count() === 3
					&& $taxes->every(fn ($t) => $t->created_by === $user?->creatorId());
			});
	}

	/**
	 ** @test
	 **
	 ** Create should show form when user has permission.
	 **/
	public function test_create_displays_form_with_permission()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create constant tax']);
		$user?->givePermissionTo('create constant tax');

		$response = $this->actingAs($user)->get(route('taxes.create'));

		$response->assertStatus(200)
			->assertViewIs('taxes.create');
	}

	/**
	 ** @test
	 **
	 ** Store should persist a new tax and redirect.
	 **/
	public function test_store_creates_tax_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create constant tax']);
		$user?->givePermissionTo('create constant tax');

		$data = ['name' => 'VAT', 'rate' => 15.5];

		$response = $this->actingAs($user)->post(route('taxes.store'), $data);

		$response->assertRedirect(route('taxes.index'))
			->assertSessionHas('success', __('Tax rate successfully created.'));
		$this->assertDatabaseHas('taxes', [
			'name'       => 'VAT',
			'rate'       => 15.5,
			'created_by' => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Store should fail validation with missing fields.
	 **/
	public function test_store_fails_validation_with_missing_fields()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'create constant tax']);
		$user?->givePermissionTo('create constant tax');

		$response = $this->actingAs($user)->post(route('taxes.store'), [
			'name' => '',
			'rate' => '',
		]);

		$response->assertRedirect()
			->assertSessionHas('error');
		$this->assertDatabaseCount('taxes', 0);
	}

	/**
	 ** @test
	 **
	 ** Edit should display form for owner with permission.
	 **/
	public function test_edit_displays_form_for_owner_with_permission()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit constant tax']);
		$user?->givePermissionTo('edit constant tax');

		$tax = Tax::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)->get(route('taxes.edit', $tax));

		$response->assertStatus(200)
			->assertViewIs('taxes.edit')
			->assertViewHas('tax', $tax);
	}

	/**
	 ** @test
	 **
	 ** Update should change tax and redirect on success.
	 **/
	public function test_update_changes_tax_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit constant tax']);
		$user?->givePermissionTo('edit constant tax');

		$tax = Tax::factory()->create([
			'name'       => 'OldTax',
			'rate'       => 10.0,
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)->put(route('taxes.update', $tax), [
			'name' => 'NewTax',
			'rate' => 12.25,
		]);

		$response->assertRedirect(route('taxes.index'))
			->assertSessionHas('success', __('Tax rate successfully updated.'));
		$this->assertDatabaseHas('taxes', [
			'id'   => $tax->id,
			'name' => 'NewTax',
			'rate' => 12.25,
		]);
	}

	/**
	 ** @test
	 **
	 ** Update should fail validation with invalid data.
	 **/
	public function test_update_fails_validation_with_invalid_data()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'edit constant tax']);
		$user?->givePermissionTo('edit constant tax');

		$tax = Tax::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)->put(route('taxes.update', $tax), [
			'name' => '',
			'rate' => 'abc',
		]);

		$response->assertRedirect()
			->assertSessionHas('error');
		$this->assertDatabaseHas('taxes', ['id' => $tax->id, 'name' => $tax->name, 'rate' => $tax->rate]);
	}

	/**
	 ** @test
	 **
	 ** Destroy should delete tax and redirect on success.
	 **/
	public function test_destroy_deletes_tax_and_redirects()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'delete constant tax']);
		$user?->givePermissionTo('delete constant tax');

		$tax = Tax::factory()->create(['created_by' => $user?->creatorId()]);

		$response = $this->actingAs($user)->delete(route('taxes.destroy', $tax));

		$response->assertRedirect(route('taxes.index'))
			->assertSessionHas('success', __('Tax rate successfully deleted.'));
		$this->assertModelMissing($tax);
	}

	/**
	 ** @test
	 **
	 ** Destroy should fail when tax is in use.
	 **/
	public function test_destroy_fails_when_tax_in_use()
	{
		$user = User::factory()->create();
		Permission::create(['name' => 'delete constant tax']);
		$user?->givePermissionTo('delete constant tax');

		$tax = Tax::factory()->create(['created_by' => $user?->creatorId()]);

		InvoiceProduct::factory()->create(['tax' => (string)$tax->id]);

		$response = $this->actingAs($user)->delete(route('taxes.destroy', $tax));

		$response->assertRedirect()
			->assertSessionHas('error', __('This tax is already assigned; remove associated records first.'));
		$this->assertModelExists($tax);
	}


	/**
	 ** @test
	 **
	 ** The owner sees the tax details view.
	 **/
	public function owner_can_view_tax()
	{
		$response = $this->get(route('tax.show', $this->tax));

		$response->assertOk()
			->assertViewIs('taxes.show')
			->assertViewHas('tax', fn ($t) => $t->id === $this->tax->id);
	}

	/**
	 ** @test
	 **
	 ** Non-owner receives 403 Forbidden when viewing.
	 **/
	public function non_owner_gets_forbidden()
	{
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other);

		$response = $this->get(route('tax.show', $this->tax));
		$response->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected to login when attempting to view.
	 **/
	public function guest_is_redirected_to_login()
	{
		auth()->logout();

		$response = $this->get(route('tax.show', $this->tax));
		$response->assertRedirect(); // login page
	}

	/**
	 ** @test
	 **
	 ** If Gate denies the permission, user is sent back to index.
	 **/
	public function permission_denied_redirects_to_index()
	{
		Gate::before(fn () => false);

		$response = $this->actingAs($this->company)
			->get(route('tax.show', $this->tax));

		$response->assertRedirect(route('tax.index'));
	}
}
