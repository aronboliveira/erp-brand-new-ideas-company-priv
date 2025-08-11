<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\{User, PayslipType};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PayslipTypeControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;

	protected function setUp(): void
	{
		parent::setUp();
		$this->user = User::factory()->create(['type' => 'Company']);
		$this->actingAs($this->user);
	}

	/**
	 ** @test
	 **
	 ** Render the payslip types index view, providing all
	 ** payslip types created by the current user.
	 **/
	public function test_index_returns_view(): void
	{
		PayslipType::factory()->count(2)->create([
			'created_by' => $this->user->creatorId()
		]);

		$response = $this->get(route('paysliptype.index'));
		$response->assertStatus(200)
			->assertViewIs('paysliptype.index')
			->assertViewHas('payslipTypes');
	}

	/**
	 ** @test
	 **
	 ** Display the form to create a new payslip type.
	 **/
	public function test_create_returns_view(): void
	{
		$response = $this->get(route('paysliptype.create'));
		$response->assertStatus(200)
			->assertViewIs('paysliptype.create');
	}

	/**
	 ** @test
	 **
	 ** Store a new payslip type and redirect back to the index,
	 ** persisting the record with the correct creator ID.
	 **/
	public function test_store_creates_payslip_type(): void
	{
		$response = $this->post(route('paysliptype.store'), [
			'name' => 'Contractual'
		]);

		$response->assertRedirect(route('paysliptype.index'));
		$this->assertDatabaseHas('payslip_types', [
			'name'       => 'Contractual',
			'created_by' => $this->user->creatorId()
		]);
	}

	/**
	 ** @test
	 **
	 ** Show the edit form for an existing payslip type,
	 ** passing the current model to the view.
	 **/
	public function test_edit_returns_edit_form(): void
	{
		$type = PayslipType::factory()->create([
			'created_by' => $this->user->creatorId()
		]);

		$response = $this->get(route('paysliptype.edit', $type));
		$response->assertStatus(200)
			->assertViewIs('paysliptype.edit')
			->assertViewHas('payslipType', $type);
	}

	/**
	 ** @test
	 **
	 ** Update the name of an existing payslip type and
	 ** redirect to the index, verifying the change.
	 **/
	public function test_update_changes_name(): void
	{
		$type = PayslipType::factory()->create([
			'name'       => 'Old',
			'created_by' => $this->user->creatorId()
		]);

		$response = $this->put(route('paysliptype.update', $type), [
			'name' => 'Updated Type'
		]);

		$response->assertRedirect(route('paysliptype.index'));
		$this->assertDatabaseHas('payslip_types', [
			'id'   => $type->id,
			'name' => 'Updated Type'
		]);
	}

	/**
	 ** @test
	 **
	 ** Delete an existing payslip type and ensure it
	 ** is removed from the database.
	 **/
	public function test_destroy_removes_payslip_type(): void
	{
		$type = PayslipType::factory()->create([
			'created_by' => $this->user->creatorId()
		]);

		$response = $this->delete(route('paysliptype.destroy', $type));
		$response->assertRedirect(route('paysliptype.index'));
		$this->assertDatabaseMissing('payslip_types', ['id' => $type->id]);
	}

	/**
	 ** @test
	 **
	 ** Accessing the show route should simply redirect
	 ** back to the index of payslip types.
	 **/
	public function test_show_redirects_to_index(): void
	{
		$response = $this->get(route('paysliptype.show'));
		$response->assertRedirect(route('paysliptype.index'));
	}
}
