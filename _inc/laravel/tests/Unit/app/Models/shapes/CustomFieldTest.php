<?php

namespace Tests\Unit\Models;

use App\Models\CustomField;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class CustomFieldTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		\DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}

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
		// Trigger booted() to populate static arrays
		new CustomField;

		$expectedFieldTypes = [
			'text'           => 'Text',
			'email'          => 'Email',
			'tel'            => 'Telephone',
			'number'         => 'Number',
			'date'           => 'Date',
			'url'            => 'URL',
			'radiogroup'     => 'Radio Group',
			'checkbox'       => 'Checkbox',
			'select'         => 'Select',
			'time'           => 'Time',
			'datetime-local' => 'Date & Time',
			'month'          => 'Month',
			'week'           => 'Week',
			'textarea'       => 'Textarea',
			'range'          => 'Range',
			'color'          => 'Color Picker',
			'file'           => 'File',
			'password'       => 'Password',
			'search'         => 'Search',
		];
		$this->assertSame($expectedFieldTypes, CustomField::$fieldTypes);

		$expectedModules = [
			'financial'      => 'Financial',
			'sales'          => 'Sales',
			'crm'            => 'CRM',
			'hrm'            => 'HRM',
			'projects'       => 'Projects',
			'management'     => 'Management',
			'inventory'      => 'Inventory',
			'support'        => 'Support',
			'database'       => 'Database',
			'infrastructure' => 'Infrastructure',
			'marketing'      => 'Marketing',
			'custom'         => 'Custom',
			'landing_page'   => 'Landing Page',
			'user'           => 'User',
			'customer'       => 'Customer',
			'vendor'         => 'Vendor',
			'product'        => 'Product',
			'proposal'       => 'Proposal',
			'invoice'        => 'Invoice',
			'bill'           => 'Bill',
			'account'        => 'Account',
			'other'          => 'Other',
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
		$model = Mockery::mock(\Illuminate\Database\Eloquent\Model::class);
		$model->shouldReceive('getKey')->andReturn('abc-123');

		$data = [
			10 => 'Value A',
			15 => 'Value B',
		];

		// Expect DB::insert(...) twice with 5 bindings each
		DB::shouldReceive('insert')
			->twice()
			->withArgs(function ($query, $bindings) {
				return is_string($query)
					&& count($bindings) === 5
					&& $bindings[0] === 'abc-123'
					&& in_array($bindings[1], [10, 15])
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

		$model = Mockery::mock(\Illuminate\Database\Eloquent\Model::class);
		$model->shouldReceive('getKey')->andReturn('xyz');

		$result = CustomField::getData($model, 'user');

		$this->assertInstanceOf(Collection::class, $result);
		// pluck('value', 'id') returns a keyed collection
		$this->assertSame([1 => 'X', 2 => 'Y'], $result->all());
	}
}
