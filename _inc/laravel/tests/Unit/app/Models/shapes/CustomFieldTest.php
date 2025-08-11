<?php

namespace Tests\Unit\Models;

use App\Models\CustomField;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class CustomFieldTest extends TestCase
{
	protected function tearDown(): void
	{
		Mockery::close();
		parent::tearDown();
	}

	/**
	 ** @test
	 *
	 ** The static $fieldTypes and $modules arrays must
	 ** remain unchanged to ensure UI consistency.
	 **/
	public function static_arrays_remain_intact(): void
	{
		$expectedFieldTypes = [
			'text'     => 'Text',
			'email'    => 'Email',
			'number'   => 'Number',
			'date'     => 'Date',
			'textarea' => 'Textarea',
		];
		$this->assertSame($expectedFieldTypes, CustomField::$fieldTypes);

		$expectedModules = [
			'user'     => 'User',
			'customer' => 'Customer',
			'vendor'   => 'Vendor',
			'product'  => 'Product',
			'proposal' => 'Proposal',
			'Invoice'  => 'Invoice',
			'Bill'     => 'Bill',
			'account'  => 'Account',
		];
		$this->assertSame($expectedModules, CustomField::$modules);
	}

	/**
	 ** @test
	 *
	 ** saveData() should call DB::insert with proper
	 ** bindings for each entry in $data.
	 **/
	public function save_data_inserts_records_or_updates(): void
	{
		$model = new class
		{
			public $id = 'abc-123';
		};

		$data = [
			10 => 'Value A',
			15 => 'Value B',
		];

		// Expect DB::insert(...) twice
		DB::shouldReceive('insert')
			->once()
			->withArgs(function ($query, $bindings) use ($model, $data) {
				return is_string($query)
					&& $bindings[0] === $model->id
					&& ($bindings[1] === 10 || $bindings[1] === 15)
					&& in_array($bindings[2], ['Value A', 'Value B']);
			})
			->andReturnTrue();

		// Second call
		DB::shouldReceive('insert')
			->once()
			->withArgs(function ($query, $bindings) use ($model, $data) {
				return is_string($query)
					&& $bindings[0] === $model->id
					&& ($bindings[1] === 10 || $bindings[1] === 15)
					&& in_array($bindings[2], ['Value A', 'Value B']);
			})
			->andReturnTrue();

		CustomField::saveData($model, $data);
	}

	/**
	 ** @test
	 *
	 ** getData() should return the Collection returned
	 ** by the DB facade’s query builder and pluck values.
	 **/
	public function get_data_returns_collection_of_values(): void
	{
		$fakeCollection = new Collection([
			(object)['value' => 'X', 'id' => 1],
			(object)['value' => 'Y', 'id' => 2],
		]);

		// Stub DB::table(...)->select(...)->join(...)->where(...)->where(...)->get()->pluck()
		$mock = Mockery::mock()
			->shouldReceive('select')->andReturnSelf()
			->getMock()
			->shouldReceive('join')->andReturnSelf()
			->getMock()
			->shouldReceive('where')->andReturnSelf()
			->getMock()
			->shouldReceive('where')->andReturnSelf()
			->getMock()
			->shouldReceive('get')->andReturn($fakeCollection)
			->getMock();

		DB::shouldReceive('table')
			->once()
			->andReturn($mock);

		$model = new class
		{
			public $id = 'xyz';
		};
		$result = CustomField::getData($model, 'user');

		$this->assertInstanceOf(Collection::class, $result);
		$this->assertSame(['X', 'Y'], $result->all());
	}
}
