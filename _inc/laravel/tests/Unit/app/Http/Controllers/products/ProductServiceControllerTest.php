<?php

namespace Tests\Feature;

use App\Models\{
	User,
	ProductService,
	ProductServiceCategory,
	ProductServiceUnit,
	Tax,
	ChartOfAccountType,
	ChartOfAccount
};
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductServiceControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;

	protected function setUp(): void
	{
		parent::setUp();

		// authenticate and bypass permission checks
		$this->user = User::factory()->create(['type' => 'company']);
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
	 ** index should display products and filter by category
	 **/
	public function test_index_displays_products_and_filters(): void
	{
		$catA = ProductServiceCategory::factory()->create([
			'created_by' => $this->user->creatorId(),
			'type'       => 'product & service',
		]);
		$catB = ProductServiceCategory::factory()->create([
			'created_by' => $this->user->creatorId(),
			'type'       => 'product & service',
		]);

		ProductService::factory()->create([
			'created_by'  => $this->user->creatorId(),
			'category_id' => $catA->id,
		]);
		ProductService::factory()->create([
			'created_by'  => $this->user->creatorId(),
			'category_id' => $catB->id,
		]);

		// without filter
		$response = $this->get(route('productservice.index'));
		$response->assertOk()
			->assertViewIs('productservice.index')
			->assertViewHas('productServices', fn ($list) => $list->count() === 2);

		// with category filter
		$response = $this->get(route('productservice.index', ['category' => $catA->id]));
		$response->assertOk()
			->assertViewHas(
				'productServices',
				fn ($list) =>
				$list->pluck('category_id')->all() === [$catA->id]
			);
	}

	/**
	 ** @test
	 **
	 ** create should show form collections
	 **/
	public function test_create_shows_form(): void
	{
		ProductServiceCategory::factory()->create([
			'created_by' => $this->user->creatorId(),
			'type'       => 'product & service',
		]);
		ProductServiceUnit::factory()->count(2)->create([
			'created_by' => $this->user->creatorId(),
		]);
		Tax::factory()->count(2)->create([
			'created_by' => $this->user->creatorId(),
		]);
		$acctType = ChartOfAccountType::create(['name' => 'Income', 'created_by' => $this->user->creatorId()]);
		ChartOfAccount::create([
			'code' => 'INC-1', 'name' => 'Income A', 'type' => $acctType->id, 'created_by' => $this->user->creatorId()
		]);

		$response = $this->get(route('productservice.create'));
		$response->assertOk()
			->assertViewIs('productservice.create')
			->assertViewHasAll(['category', 'unit', 'tax', 'income', 'expense', 'customFields']);
	}

	/**
	 ** @test
	 **
	 ** store should validate and create a product service
	 **/
	public function test_store_creates_product_service(): void
	{
		Storage::fake('uploads');
		$cat = ProductServiceCategory::factory()->create([
			'created_by' => $this->user->creatorId(),
			'type'       => 'product & service',
		]);
		$unit = ProductServiceUnit::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);
		Tax::factory()->create(['created_by' => $this->user->creatorId()]);
		$acctType = ChartOfAccountType::create(['name' => 'Expenses', 'created_by' => $this->user->creatorId()]);
		$expAcct = ChartOfAccount::create([
			'code' => 'EXP-1', 'name' => 'Expense A', 'type' => $acctType->id, 'created_by' => $this->user->creatorId()
		]);

		$payload = [
			'name'                    => 'Test Product',
			'description'             => 'Desc',
			'sku'                     => 'SKU123',
			'sale_price'              => 10.5,
			'purchase_price'          => 5.0,
			'unit_id'                 => $unit->id,
			'quantity'                => 100,
			'type'                    => 'product',
			'sale_chartaccount_id'    => '',
			'expense_chartaccount_id' => $expAcct->id,
			'category_id'             => $cat->id,
			'tax_id'                  => [],
			'customField'             => [],
			// simulate image upload
			'pro_image'               => UploadedFile::fake()->image('foo.jpg'),
		];

		$response = $this->post(route('productservice.store'), $payload);
		$response->assertRedirect(route('productservice.index'));
		$this->assertDatabaseHas('product_services', [
			'name'        => 'Test Product',
			'sku'         => 'SKU123',
			'created_by'  => $this->user->creatorId(),
		]);
		$adapter = Storage::disk('local');
		assert($adapter instanceof FilesystemAdapter);
		$adapter::disk('uploads')->assertExists('pro_image/foo.jpg');
	}

	/**
	 ** @test
	 **
	 ** store should fail validation when required fields missing
	 **/
	public function test_store_validation_fails(): void
	{
		$response = $this->post(route('productservice.store'), []);
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** edit should display existing product for update
	 **/
	public function test_edit_displays_existing_product(): void
	{
		$product = ProductService::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);
		ProductServiceCategory::factory()->create([
			'created_by' => $this->user->creatorId(),
			'type'       => 'product & service',
		]);
		ProductServiceUnit::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);
		Tax::factory()->create(['created_by' => $this->user->creatorId()]);
		$acctType = ChartOfAccountType::create(['name' => 'Income', 'created_by' => $this->user->creatorId()]);
		ChartOfAccount::create([
			'code' => 'INC-1', 'name' => 'Income A', 'type' => $acctType->id, 'created_by' => $this->user->creatorId()
		]);

		$response = $this->get(route('productservice.edit', $product->id));
		$response->assertOk()
			->assertViewIs('productservice.edit')
			->assertViewHas('productService', fn ($p) => $p->id === $product->id);
	}

	/**
	 ** @test
	 **
	 ** update should apply changes and redirect
	 **/
	public function test_update_changes_product(): void
	{
		$product = ProductService::factory()->create([
			'created_by' => $this->user->creatorId(),
			'name'       => 'Old',
			'sku'        => 'OLD123',
		]);
		$cat = ProductServiceCategory::factory()->create([
			'created_by' => $this->user->creatorId(),
			'type'       => 'product & service',
		]);
		$unit = ProductServiceUnit::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);
		Tax::factory()->create(['created_by' => $this->user->creatorId()]);
		$acctType = ChartOfAccountType::create(['name' => 'Asset', 'created_by' => $this->user->creatorId()]);
		$incAcct = ChartOfAccount::create([
			'code' => 'AST-1', 'name' => 'Asset A', 'type' => $acctType->id, 'created_by' => $this->user->creatorId()
		]);

		$payload = [
			'name'                    => 'New Name',
			'description'             => 'New Desc',
			'sku'                     => 'NEW123',
			'sale_price'              => 20.0,
			'purchase_price'          => 10.0,
			'unit_id'                 => $unit->id,
			'quantity'                => 50,
			'type'                    => 'service',
			'sale_chartaccount_id'    => $incAcct->id,
			'expense_chartaccount_id' => '',
			'category_id'             => $cat->id,
			'tax_id'                  => [],
			'customField'             => [],
		];

		$response = $this->put(route('productservice.update', $product->id), $payload);
		$response->assertRedirect(route('productservice.index'));
		$this->assertDatabaseHas('product_services', [
			'id'   => $product->id,
			'name' => 'New Name',
			'sku'  => 'NEW123',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy should delete the product and redirect
	 **/
	public function test_destroy_deletes_product(): void
	{
		$product = ProductService::factory()->create([
			'created_by' => $this->user->creatorId(),
			'pro_image'  => 'foo.jpg',
		]);
		// simulate existing image
		Storage::fake('uploads');
		Storage::disk('uploads')->put('pro_image/foo.jpg', 'dummy');

		$response = $this->delete(route('productservice.destroy', $product->id));
		$response->assertRedirect(route('productservice.index'));
		$this->assertDatabaseMissing('product_services', ['id' => $product->id]);
	}
}
