<?php

/**
 * tests/Unit/Models/ProductServiceCategoryTest.php
 *
 * Unit-tests for App\Models\ProductServiceCategory
 */

namespace Tests\Unit\Models;

use App\Models\ProductServiceCategory;
use Illuminate\Support\{Collection, Facades\Auth};
use Mockery;
use Tests\TestCase;

class ProductServiceCategoryTest extends TestCase
{
	/** A fake “company” user reused in multiple tests */
	private object $fakeUser;

	protected function setUp(): void
	{
		parent::setUp();

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
			'name', 'type', 'chart_account_id',
			'color', 'created_by',
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
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
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
		// ➊  Stub static _checkLogin()
		Mockery::mock('alias:' . ProductServiceCategory::class)
			->shouldReceive('_checkLogin')
			->andReturn($this->fakeUser);

		// ➋  Create a partial mock of the category instance
		$cat = Mockery::mock(ProductServiceCategory::class)->makePartial();
		$cat->id = 5;

		// Stub categories()->where*->sum => 100
		$cat->shouldReceive('categories')
			->andReturnSelf();
		$cat->shouldReceive('where')
			->andReturnSelf();
		$cat->shouldReceive('whereRaw')
			->andReturnSelf();
		$cat->shouldReceive('sum')
			->andReturn(100);

		// Stub Invoice::where()->whereRaw()->get() → collection of two totals 50+30
		$invoiceCollection = collect([
			(object)['getTotal' => fn () => 50],
			(object)['getTotal' => fn () => 30],
		]);
		Mockery::mock('alias:App\Models\Invoice')
			->shouldReceive('where')
			->andReturnSelf()
			->getMock()->shouldReceive('whereRaw')
			->andReturnSelf()
			->getMock()->shouldReceive('get')
			->andReturn($invoiceCollection);

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
		// _checkLogin already mocked above; reuse fake user.

		// Partial mock of category
		$cat = Mockery::mock(ProductServiceCategory::class)->makePartial();
		$cat->id = 7;

		// Stub Payment::where()->whereRaw()->sum => 40
		Mockery::mock('alias:App\Models\Payment')
			->shouldReceive('where')
			->andReturnSelf()
			->getMock()->shouldReceive('whereRaw')
			->andReturnSelf()
			->getMock()->shouldReceive('sum')
			->andReturn(40);

		// Stub Bill::where()->whereRaw()->get() → two totals 20+10
		$billCollection = collect([
			(object)['getTotal' => fn () => 20],
			(object)['getTotal' => fn () => 10],
		]);
		Mockery::mock('alias:App\Models\Bill')
			->shouldReceive('where')
			->andReturnSelf()
			->getMock()->shouldReceive('whereRaw')
			->andReturnSelf()
			->getMock()->shouldReceive('get')
			->andReturn($billCollection);

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

		// Stub DB::table()->select()->leftJoin()->where()->where()->groupBy()->orderBy()->get()
		Mockery::mock('alias:Illuminate\Support\Facades\DB')
			->shouldReceive('table')
			->once()
			->andReturnSelf()
			->getMock()->shouldReceive('select')
			->andReturnSelf()
			->getMock()->shouldReceive('leftJoin')
			->andReturnSelf()
			->getMock()->shouldReceive('where')
			->andReturnSelf()
			->times(2) // two where calls
			->getMock()->shouldReceive('groupBy')
			->andReturnSelf()
			->getMock()->shouldReceive('orderBy')
			->andReturnSelf()
			->getMock()->shouldReceive('get')
			->andReturn($expected);

		// Stub _checkLogin again for static call
		Mockery::mock('alias:' . ProductServiceCategory::class)
			->shouldReceive('_checkLogin')
			->andReturn($this->fakeUser);

		$result = ProductServiceCategory::getAllCategories();
		$this->assertSame($expected, $result);
	}

	protected function tearDown(): void
	{
		Mockery::close();
		parent::tearDown();
	}
}
