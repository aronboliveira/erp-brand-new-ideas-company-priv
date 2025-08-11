<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\{Carbon, Facades\DB};
use App\Models\{Activity, User};

class ActivityTest extends TestCase
{
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
		$this->assertEquals(['name' => 'John Doe'], $result);
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
		$employeeId = DB::table('employees')->insertGetId([
			'first_name' => 'Alice',
			'last_name'  => 'Smith',
			'created_at' => Carbon::now(),
			'updated_at' => Carbon::now(),
		]);

		$result = Activity::getActivity('Employee', $employeeId);
		$this->assertEquals(['name' => 'Alice Smith'], $result);
	}
}
