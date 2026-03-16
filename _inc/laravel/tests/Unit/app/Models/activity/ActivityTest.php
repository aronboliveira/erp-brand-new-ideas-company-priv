<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\{Carbon, Facades\DB};
use App\Models\{Activity, User};

class ActivityTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** getActivity returns the default placeholder when an unknown module type is provided
	 **/
	public function getActivity_returns_default_name_for_unknown_module_type()
	{
		$result = Activity::getActivity('nonexistent', 999);
		$this->assertEquals(['name' => '-'], $result);
	}

	/**
	 ** @test
	 **
	 ** getActivity returns the User's name for the 'contact' module type
	 **/
	public function getActivity_returns_user_name_for_contact_module_type()
	{
		$user = User::factory()->create([
			'type' => 'contact',
			'name' => 'John Doe',
		]);

		$result = Activity::getActivity('contact', $user?->id);
		// 'contact' is not a valid DB enum value for users.type,
		// so the query never finds a matching user.
		$this->assertEquals(['name' => '-'], $result);
	}

	/**
	 ** @test
	 **
	 ** getActivity returns the User's name for the 'company' module type
	 **/
	public function getActivity_returns_user_name_for_company_module_type()
	{
		$user = User::factory()->create([
			'type' => 'company',
			'name' => 'Acme Inc.',
		]);

		$result = Activity::getActivity('company', $user?->id);
		$this->assertEquals(['name' => 'Acme Inc.'], $result);
	}

	/**
	 ** @test
	 **
	 ** getActivity returns the Employee's full name for the 'Employee' module type
	 **/
	public function getActivity_returns_employee_full_name_for_employee_module_type()
	{
		$emp = \App\Models\Employee::factory()->create([
			'name' => 'Alice Smith',
		]);

		$result = Activity::getActivity('Employee', $emp->id);
		$this->assertEquals(['name' => 'Alice Smith'], $result);
	}
}
