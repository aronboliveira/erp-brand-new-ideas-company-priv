<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\AllowanceOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AllowanceOptionControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		Log::spy();
		DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
			return $callback();
		});
	}

	/**
	 ** @test
	 **
	 ** When a user with the 'create allowance option' permission submits a valid name,
	 ** the controller should create a new allowance option and redirect to the index.
	 **/
	public function test_store_creates_new_allowance_option()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('create allowance option');

		$response = $this->post(route('allowanceoption.store'), [
			'name' => 'Housing',
		]);

		$response->assertRedirect(route('allowanceoption.index'));
		$this->assertDatabaseHas('allowance_options', [
			'name'       => 'Housing',
			'created_by' => $user?->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** If the provided name is invalid (too long), the store action should redirect back
	 ** and no new allowance option should be created.
	 **/
	public function test_store_fails_with_invalid_name()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('create allowance option');

		$longName = str_repeat('x', 100);
		$response = $this->post(route('allowanceoption.store'), [
			'name' => $longName,
		]);

		$response->assertRedirect();
		$this->assertDatabaseMissing('allowance_options', ['name' => $longName]);
	}

	/**
	 ** @test
	 **
	 ** A user with 'edit allowance option' permission should be able to update
	 ** an existing allowance option's name and be redirected to the index.
	 **/
	public function test_update_changes_allowance_option_name()
	{
		$user = User::factory()->create();
		$opt = AllowanceOption::factory()->create([
			'created_by' => $user?->id,
			'name'       => 'Old Name'
		]);

		$this->actingAs($user);
		$user?->givePermissionTo('edit allowance option');

		$response = $this->put(route('allowanceoption.update', $opt), [
			'name' => 'New Name',
		]);

		$response->assertRedirect(route('allowanceoption.index'));
		$this->assertDatabaseHas('allowance_options', [
			'id'   => $opt->id,
			'name' => 'New Name',
		]);
	}

	/**
	 ** @test
	 **
	 ** A user with 'delete allowance option' permission should be able to delete
	 ** an allowance option and be redirected to the index.
	 **/
	public function test_destroy_deletes_allowance_option()
	{
		$user = User::factory()->create();
		$opt = AllowanceOption::factory()->create(['created_by' => $user?->id]);

		$this->actingAs($user);
		$user?->givePermissionTo('delete allowance option');

		$response = $this->delete(route('allowanceoption.destroy', $opt));
		$response->assertRedirect(route('allowanceoption.index'));
		$this->assertDatabaseMissing('allowance_options', ['id' => $opt->id]);
	}

	/**
	 ** @test
	 **
	 ** Attempting to update without the necessary permission should result
	 ** in a redirect (authorization denial) and no change to the option.
	 **/
	public function test_update_fails_without_permission()
	{
		$user = User::factory()->create();
		$opt = AllowanceOption::factory()->create(['created_by' => $user?->id]);

		$this->actingAs($user);

		$response = $this->put(route('allowanceoption.update', $opt), [
			'name' => 'Blocked'
		]);
		$response->assertStatus(302); // fallback redirect on auth denial
	}
}
