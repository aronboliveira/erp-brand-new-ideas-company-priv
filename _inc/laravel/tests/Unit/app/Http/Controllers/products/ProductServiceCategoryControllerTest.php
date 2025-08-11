<?php

namespace Tests\Feature;

use App\Models\{
	Bill,
	ChartOfAccount,
	ChartOfAccountType,
	Invoice,
	ProductService,
	ProductServiceCategory,
	User
};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ProductServiceCategoryControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;

	protected function setUp(): void
	{
		parent::setUp();

		// authenticate and bypass permission checks
		$this->user = User::factory()->create();
		$this->actingAs($this->user);
		Gate::before(fn () => true);

		// ensure creatorId() returns the user’s own id
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
	 ** index should display only categories created by the authenticated user
	 **/
	public function index_displays_only_user_categories(): void
	{
		$own  = ProductServiceCategory::factory()->create(['created_by' => $this->user->creatorId()]);
		$other = ProductServiceCategory::factory()->create();

		$response = $this->get(route('product-category.index'));

		$response->assertOk()
			->assertViewIs('productServiceCategory.index')
			->assertViewHas(
				'categories',
				fn ($cats) =>
				$cats->pluck('id')->all() === [$own->id]
			);
	}

	/**
	 ** @test
	 **
	 ** create should render form with types and chart accounts
	 **/
	public function create_shows_form_with_types_and_accounts(): void
	{
		// seed a chart account type & account
		$type = ChartOfAccountType::factory()->create();
		ChartOfAccount::factory()->create([
			'created_by' => $this->user->creatorId(),
			'type'       => $type->id,
			'code'       => '100',
			'name'       => 'Cash'
		]);

		$response = $this->get(route('product-category.create'));

		$response->assertOk()
			->assertViewIs('productServiceCategory.create')
			->assertViewHasAll(['types', 'chartAccounts']);
	}

	/**
	 ** @test
	 **
	 ** store should validate input and create a new category
	 **/
	public function store_creates_category(): void
	{
		$payload = [
			'name'           => 'New Cat',
			'type'           => array_key_first(ProductServiceCategory::$catTypes),
			'color'          => '#abcdef',
			'chart_account'  => null,
		];

		$response = $this->post(route('product-category.store'), $payload);

		$response->assertRedirect(route('product-category.index'));
		$this->assertDatabaseHas('product_service_categories', [
			'name'       => 'New Cat',
			'type'       => $payload['type'],
			'color'      => '#abcdef',
			'created_by' => $this->user->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** store should fail when required fields are missing
	 **/
	public function store_validation_fails(): void
	{
		$response = $this->post(route('product-category.store'), []);

		$response->assertRedirect()
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** edit should render form for a category owned by the user
	 **/
	public function edit_shows_form_for_owner(): void
	{
		$category = ProductServiceCategory::factory()->create(['created_by' => $this->user->creatorId()]);

		$response = $this->get(route('product-category.edit', $category->id));

		$response->assertOk()
			->assertViewIs('productServiceCategory.edit')
			->assertViewHas('category', fn ($c) => $c->id === $category->id)
			->assertViewHas('types');
	}

	/**
	 ** @test
	 **
	 ** edit should redirect when accessing a category not owned by the user
	 **/
	public function edit_redirects_for_non_owner(): void
	{
		$category = ProductServiceCategory::factory()->create(['created_by' => $this->user->creatorId() + 1]);

		$response = $this->get(route('product-category.edit', $category->id));

		$response->assertRedirect(route('product-category.index'));
	}

	/**
	 ** @test
	 **
	 ** update should validate and modify the category
	 **/
	public function update_modifies_category(): void
	{
		$category = ProductServiceCategory::factory()->create([
			'created_by' => $this->user->creatorId(),
			'name'       => 'Old',
			'color'      => '#000000',
		]);

		$payload = [
			'name'  => 'Updated',
			'type'  => $category->type,
			'color' => '#123456',
		];

		$response = $this->put(route('product-category.update', $category->id), $payload);

		$response->assertRedirect(route('product-category.index'));
		$this->assertDatabaseHas('product_service_categories', [
			'id'    => $category->id,
			'name'  => 'Updated',
			'color' => '#123456',
		]);
	}

	/**
	 ** @test
	 **
	 ** update should fail validation when given invalid data
	 **/
	public function update_validation_fails(): void
	{
		$category = ProductServiceCategory::factory()->create(['created_by' => $this->user->creatorId()]);

		$response = $this->put(route('product-category.update', $category->id), [
			'name'  => '',
			'type'  => '',
			'color' => '',
		]);

		$response->assertRedirect()
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** destroy should delete a category not in use
	 **/
	public function destroy_deletes_category(): void
	{
		$category = ProductServiceCategory::factory()->create(['created_by' => $this->user->creatorId()]);

		$response = $this->delete(route('product-category.destroy', $category->id));

		$response->assertRedirect(route('product-category.index'));
		$this->assertDatabaseMissing('product_service_categories', ['id' => $category->id]);
	}

	/**
	 ** @test
	 **
	 ** destroy should fail if the category is in use by a product, invoice, or bill
	 **/
	public function destroy_fails_if_in_use(): void
	{
		// type 0 => ProductService
		$category = ProductServiceCategory::factory()->create(['created_by' => $this->user->creatorId(), 'type' => 0]);
		ProductService::factory()->create(['category_id' => $category->id]);

		$resp1 = $this->delete(route('product-category.destroy', $category->id));
		$resp1->assertRedirect()->assertSessionHas('error');

		// type 1 => Invoice
		$category2 = ProductServiceCategory::factory()->create(['created_by' => $this->user->creatorId(), 'type' => 1]);
		Invoice::factory()->create(['category_id' => $category2->id]);

		$resp2 = $this->delete(route('product-category.destroy', $category2->id));
		$resp2->assertRedirect()->assertSessionHas('error');

		// type 99 => Bill
		$category3 = ProductServiceCategory::factory()->create(['created_by' => $this->user->creatorId(), 'type' => 99]);
		Bill::factory()->create(['category_id' => $category3->id]);

		$resp3 = $this->delete(route('product-category.destroy', $category3->id));
		$resp3->assertRedirect()->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** getProductCategories should return HTML containing category names
	 **/
	public function get_product_categories_returns_html(): void
	{
		$cat1 = ProductServiceCategory::factory()->create(['created_by' => $this->user->creatorId(), 'name' => 'Cat One']);
		$cat2 = ProductServiceCategory::factory()->create(['created_by' => $this->user->creatorId(), 'name' => 'Cat Two']);

		$response = $this->get(route('product-category.getProductCategories'));

		$response->assertOk();
		$this->assertStringContainsString('Cat One', $response->getContent());
		$this->assertStringContainsString('Cat Two', $response->getContent());
	}

	/**
	 ** @test
	 **
	 ** getAccount should return JSON mapping account ids to code_name for a given type
	 **/
	public function get_account_returns_json(): void
	{
		// seed account type and account
		$acctType = ChartOfAccount::factory()->create([
			'created_by' => $this->user->creatorId(),
			'code'       => '200',
			'name'       => 'Revenue',
		]);
		// also seed its ChartOfAccountType for join
		$type = ChartOfAccountType::factory()->create(['name' => 'Income']);
		$acctType->type = $type->id;
		$acctType->save();

		$response = $this->getJson(route('product-category.getAccount', ['type' => 'income']));

		$response->assertOk()
			->assertJsonFragment([
				(string)$acctType->id => '200 - Revenue'
			]);
	}
}
