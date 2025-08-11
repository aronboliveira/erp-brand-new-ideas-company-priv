<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ProposalProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ProposalProductControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;

	protected function setUp(): void
	{
		parent::setUp();

		// authenticate a user and bypass permission checks
		$this->user = User::factory()->create();
		$this->actingAs($this->user);
		Gate::before(fn () => true);

		// ensure creatorId() returns the user's own id
		User::macro(
			'creatorId',
			/** 
			 ** @this \App\Models\User
			 ** @return int|string
			 **/
			function (): int|string {
				/** @var \App\Models\User $this */
				return $this->id;
			}
		);
	}

	/**
	 ** @test
	 **
	 ** index should display only proposal products created by the authenticated user
	 **/
	public function test_index_displays_only_user_products(): void
	{
		$own  = ProposalProduct::factory()->create(['created_by' => $this->user->creatorId()]);
		$other = ProposalProduct::factory()->create();

		$response = $this->get(route('proposal-product.index'));

		$response->assertOk()
			->assertViewIs('proposalProduct.index')
			->assertViewHas(
				'products',
				fn ($prods) =>
				$prods->pluck('id')->all() === [$own->id]
			);
	}

	/**
	 ** @test
	 **
	 ** create should render the form for a new proposal product
	 **/
	public function test_create_shows_form(): void
	{
		$response = $this->get(route('proposal-product.create'));

		$response->assertOk()
			->assertViewIs('proposalProduct.create');
	}

	/**
	 ** @test
	 **
	 ** store should validate input and create a new proposal product
	 **/
	public function test_store_creates_product(): void
	{
		$payload = [
			'name'        => 'New Product',
			'description' => 'Details here',
			'price'       => 123.45,
		];

		$response = $this->post(route('proposal-product.store'), $payload);

		$response->assertRedirect(route('proposal-product.index'));
		$this->assertDatabaseHas('proposal_products', [
			'name'       => 'New Product',
			'price'      => 123.45,
			'created_by' => $this->user->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** store should fail validation when required fields are missing
	 **/
	public function test_store_validation_fails(): void
	{
		$response = $this->post(route('proposal-product.store'), []);

		$response->assertStatus(302)
			->assertSessionHasErrors(['name', 'price']);
	}

	/**
	 ** @test
	 **
	 ** edit should render the edit form for a product the user owns
	 **/
	public function test_edit_shows_form_for_owner(): void
	{
		$product = ProposalProduct::factory()->create(['created_by' => $this->user->creatorId()]);

		$response = $this->get(route('proposal-product.edit', $product->id));

		$response->assertOk()
			->assertViewIs('proposalProduct.edit')
			->assertViewHas('product', fn ($p) => $p->id === $product->id);
	}

	/**
	 ** @test
	 **
	 ** edit should redirect when accessing a product not owned by the user
	 **/
	public function test_edit_redirects_for_non_owner(): void
	{
		$product = ProposalProduct::factory()->create(['created_by' => $this->user->creatorId() + 1]);

		$response = $this->get(route('proposal-product.edit', $product->id));

		$response->assertRedirect(route('proposal-product.index'));
	}

	/**
	 ** @test
	 **
	 ** update should validate and modify the proposal product
	 **/
	public function test_update_modifies_product(): void
	{
		$product = ProposalProduct::factory()->create([
			'created_by' => $this->user->creatorId(),
			'name'       => 'Old',
			'price'      => 10,
		]);

		$payload = ['name' => 'Updated', 'description' => 'X', 'price' => 20];

		$response = $this->put(route('proposal-product.update', $product->id), $payload);

		$response->assertRedirect(route('proposal-product.index'));
		$this->assertDatabaseHas('proposal_products', [
			'id'    => $product->id,
			'name'  => 'Updated',
			'price' => 20,
		]);
	}

	/**
	 ** @test
	 **
	 ** update should fail validation when given invalid data
	 **/
	public function test_update_validation_fails(): void
	{
		$product = ProposalProduct::factory()->create(['created_by' => $this->user->creatorId()]);

		$response = $this->put(route('proposal-product.update', $product->id), [
			'name'  => '',
			'price' => 'not-a-number'
		]);

		$response->assertStatus(302)
			->assertSessionHasErrors(['name', 'price']);
	}

	/**
	 ** @test
	 **
	 ** destroy should delete the product when owned by the user
	 **/
	public function test_destroy_deletes_product(): void
	{
		$product = ProposalProduct::factory()->create(['created_by' => $this->user->creatorId()]);

		$response = $this->delete(route('proposal-product.destroy', $product->id));

		$response->assertRedirect(route('proposal-product.index'));
		$this->assertDatabaseMissing('proposal_products', ['id' => $product->id]);
	}

	/**
	 ** @test
	 **
	 ** destroy should redirect and not delete when not owned by the user
	 **/
	public function test_destroy_redirects_for_non_owner(): void
	{
		$product = ProposalProduct::factory()->create(['created_by' => $this->user->creatorId() + 1]);

		$response = $this->delete(route('proposal-product.destroy', $product->id));

		$response->assertRedirect(route('proposal-product.index'));
		$this->assertDatabaseHas('proposal_products', ['id' => $product->id]);
	}

	/**
	 ** @test
	 **
	 ** Displays the proposal product when it belongs to the authenticated user
	 **/
	public function it_shows_the_product_to_its_owner()
	{
		$user = User::factory()->create();
		$prod = ProposalProduct::factory()->create([
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)
			->get(route('proposalProduct.show', $prod->id));

		$response->assertStatus(200)
			->assertViewIs('proposalProduct.show')
			->assertViewHas('product', fn ($v) => $v->id === $prod->id);
	}

	/**
	 ** @test
	 **
	 ** Returns 404 when the proposal product does not exist
	 **/
	public function it_returns_404_if_not_found()
	{
		$user = User::factory()->create();

		$response = $this->actingAs($user)
			->get(route('proposalProduct.show', 9999));

		$response->assertStatus(404);
	}

	/**
	 ** @test
	 **
	 ** Denies access when a user tries to view a product they do not own
	 **/
	public function it_denies_access_to_non_owner()
	{
		$owner = User::factory()->create();
		$other = User::factory()->create();
		$prod = ProposalProduct::factory()->create([
			'created_by' => $owner->creatorId(),
		]);

		$response = $this->actingAs($other)
			->get(route('proposalProduct.show', $prod->id));

		$response->assertStatus(302)
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Returns 403 when the user lacks the 'view proposal product' permission
	 **/
	public function it_returns_403_if_not_authorized()
	{
		Gate::before(fn () => false);

		$user = User::factory()->create();
		$prod = ProposalProduct::factory()->create([
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)
			->get(route('proposalProduct.show', $prod->id));

		$response->assertStatus(403);
	}
}
