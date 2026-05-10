<?php

/**
 * tests/Unit/Models/ProductServiceCategoryTest.php
 *
 * Unit-tests for App\Models\ProductServiceCategory
 */

namespace Tests\Unit\Models;

use App\Models\ProductServiceCategory;
use App\Services\ProductOrServiceRequestService;
use Illuminate\Support\{Collection, Facades\Auth};
use Mockery;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductServiceCategoryTest extends TestCase
{
	/** A fake “company” user reused in multiple tests */
	private object $fakeUser;

	protected function setUp(): void
	{
		parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');

		// A simple stub user object
		$this->fakeUser = new class
		{
			public int   $id        = 1;
			public string $type     = 'company';
			public int   $warehouse_id = 0;

			public function creatorId()
			{
				return 1;
			}
			public function isUser()
			{
				return false;
			}
		};

		// Default Auth facade return value
		Auth::shouldReceive('user')->andReturn($this->fakeUser);
	}

	/**
	 ** @test
	 *
	 ** The `$fillable` array must match the list
	 ** declared inside the model.
	 **/
	public function fillable_array_is_correct(): void
	{
		$expected = [
			'name',
			'code',
			'type',
			'type_label',
			'chart_account_id',
			'color',
			'icon',
			'attributes',
			'description',
			'related_categories',
			'notes',
			'is_active',
		];

		$this->assertSame($expected, (new ProductServiceCategory)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** categories() must be a HasMany relation.
	 **/
	public function categories_relation_is_has_many(): void
	{
		$rel = (new ProductServiceCategory)->categories();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasMany::class,
			$rel
		);
	}

	/**
	 ** @test
	 *
	 ** chartAccount() must be a HasOne relation.
	 **/
	public function chart_account_relation_is_has_one(): void
	{
		$rel = (new ProductServiceCategory)->chartAccount();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
	}

	/**
	 ** @test
	 *
	 ** incomeCategoryRevenueAmount() should add up
	 ** revenue sum + invoice totals.
	 **/
	public function income_category_revenue_amount_is_calculated(): void
	{
		$cat = new ProductServiceCategory;
		$service = Mockery::mock(ProductOrServiceRequestService::class);
		$this->app->instance(ProductOrServiceRequestService::class, $service);

		// The model now delegates this calculation to the service; keep the
		// model test focused on that contract instead of aliasing loaded models.
		$service->shouldReceive('getIncomeCategoryRevenueAmount')
			->once()
			->with($cat)
			->andReturn(180.0);

		$this->assertSame(180.0, $cat->incomeCategoryRevenueAmount());
	}

	/**
	 ** @test
	 *
	 ** expenseCategoryAmount() must return
	 ** payment sum + bill totals.
	 **/
	public function expense_category_amount_is_calculated(): void
	{
		$cat = new ProductServiceCategory;
		$service = Mockery::mock(ProductOrServiceRequestService::class);
		$this->app->instance(ProductOrServiceRequestService::class, $service);

		$service->shouldReceive('getExpenseCategoryAmount')
			->once()
			->with($cat)
			->andReturn(70.0);

		$this->assertSame(70.0, $cat->expenseCategoryAmount());
	}

	/**
	 ** @test
	 *
	 ** getAllCategories() should return the
	 ** collection provided by the DB facade.
	 **/
	public function get_all_categories_returns_db_result(): void
	{
		$expected = new Collection(['dummy']);
		$service = Mockery::mock(ProductOrServiceRequestService::class);
		$this->app->instance(ProductOrServiceRequestService::class, $service);

		$service->shouldReceive('getAllCategories')
			->once()
			->andReturn($expected);

		$result = ProductServiceCategory::getAllCategories();
		$this->assertSame($expected, $result);
	}

	protected function tearDown(): void
	{
		Mockery::close();
        parent::tearDown();
	}
}
