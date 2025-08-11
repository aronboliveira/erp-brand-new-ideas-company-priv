<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\DeductionOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class DeductionOptionControllerTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		Log::spy();
	}

	/**
	 ** @test
	 **
	 ** index should display all deduction options
	 ** for a user with the 'manage deduction option' permission.
	 **/
	public function test_index_displays_all_deduction_options()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('manage deduction option');

		DeductionOption::factory()->count(3)->create(['created_by' => $user?->id]);

		$response = $this->get(route('deductionoption.index'));

		$response->assertStatus(200);
		$response->assertViewIs('deductionoption.index');
	}

	/**
	 ** @test
	 **
	 ** create should render the form for creating a new deduction option
	 ** for a user with the 'create deduction option' permission.
	 **/
	public function test_create_displays_form()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('create deduction option');

		$response = $this->get(route('deductionoption.create'));

		$response->assertStatus(200);
		$response->assertViewIs('deductionoption.create');
	}

	/**
	 ** @test
	 **
	 ** store should persist a new deduction option to the database
	 ** and redirect back to the index.
	 **/
	public function test_store_saves_new_option()
	{
		$user = User::factory()->create();
		$this->actingAs($user);
		$user?->givePermissionTo('create deduction option');

		$response = $this->post(route('deductionoption.store'), [
			'name' => 'Social Tax'
		]);

		$response->assertRedirect(route('deductionoption.index'));
		$this->assertDatabaseHas('deduction_options', [
			'name'       => 'Social Tax',
			'created_by' => $user?->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** show should redirect to index (no separate view for a single option).
	 **/
	public function test_show_redirects_to_index()
	{
		$user = User::factory()->create();
		$this->actingAs($user);

		$response = $this->get(route('deductionoption.show', ['any']));

		$response->assertRedirect(route('deductionoption.index'));
	}

	/**
	 ** @test
	 **
	 ** edit should render the edit form for the owner
	 ** when the user has the 'edit deduction option' permission.
	 **/
	public function test_edit_displays_edit_form_for_owner()
	{
		$user  = User::factory()->create();
		$option = DeductionOption::factory()->create(['created_by' => $user?->id]);

		$this->actingAs($user);
		$user?->givePermissionTo('edit deduction option');

		$response = $this->get(route('deductionoption.edit', $option->id));

		$response->assertStatus(200);
		$response->assertViewIs('deductionoption.edit');
		$response->assertViewHas('opt');
	}

	/**
	 ** @test
	 **
	 ** update should apply name changes to the deduction option
	 ** and redirect back to the index.
	 **/
	public function test_update_modifies_option()
	{
		$user  = User::factory()->create();
		$option = DeductionOption::factory()->create([
			'name'       => 'Old Name',
			'created_by' => $user?->id
		]);

		$this->actingAs($user);
		$user?->givePermissionTo('edit deduction option');

		$response = $this->put(route('deductionoption.update', $option->id), [
			'name' => 'Updated Name'
		]);

		$response->assertRedirect(route('deductionoption.index'));
		$this->assertDatabaseHas('deduction_options', [
			'id'   => $option->id,
			'name' => 'Updated Name'
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy should delete the deduction option
	 ** and redirect back to the index.
	 **/
	public function test_destroy_deletes_option()
	{
		$user  = User::factory()->create();
		$option = DeductionOption::factory()->create(['created_by' => $user?->id]);

		$this->actingAs($user);
		$user?->givePermissionTo('delete deduction option');

		$response = $this->delete(route('deductionoption.destroy', $option->id));

		$response->assertRedirect(route('deductionoption.index'));
		$this->assertDatabaseMissing('deduction_options', ['id' => $option->id]);
	}
}
