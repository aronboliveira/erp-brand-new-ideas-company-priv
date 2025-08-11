<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\{User, Employee, OtherPayment};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

final class OtherPaymentControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $user;
	protected Employee $employee;

	protected function setUp(): void
	{
		parent::setUp();
		$this->user = User::factory()->create();
		$this->actingAs($this->user);
		$this->employee = Employee::factory()->create(['created_by' => $this->user->creatorId()]);
	}

	/**
	 ** @test
	 **
	 ** Render the form to create a new other payment
	 ** for the specified employee.
	 **/
	public function test_create_form_renders(): void
	{
		$response = $this->get(route('otherpayment.create', $this->employee->id));
		$response->assertStatus(200)
			->assertViewIs('otherpayment.create');
	}

	/**
	 ** @test
	 **
	 ** Store a new other payment record for an employee
	 ** and persist it with the correct creator ID.
	 **/
	public function test_store_creates_otherPayment(): void
	{
		$response = $this->post(route('otherpayment.store'), [
			'employee_id' => $this->employee->id,
			'title'       => 'Bonus',
			'amount'      => 500,
			'type'        => 'performance',
		]);

		$response->assertRedirect();
		$this->assertDatabaseHas('other_payments', [
			'employee_id' => $this->employee->id,
			'title'       => 'Bonus',
			'amount'      => 500,
			'created_by'  => $this->user->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Render the edit form for an existing other payment
	 ** identified by its ID.
	 **/
	public function test_edit_form_renders(): void
	{
		$payment = OtherPayment::factory()->create(['created_by' => $this->user->creatorId()]);
		$response = $this->get(route('otherpayment.edit', $payment->id));

		$response->assertStatus(200)
			->assertViewIs('otherpayment.edit');
	}

	/**
	 ** @test
	 **
	 ** Update an existing other payment's details
	 ** and persist the changes to the database.
	 **/
	public function test_update_modifies_payment(): void
	{
		$payment = OtherPayment::factory()->create([
			'created_by' => $this->user->creatorId(),
			'amount'     => 300,
		]);

		$response = $this->put(route('otherpayment.update', $payment->id), [
			'title'  => 'Correction',
			'amount' => 450,
			'type'   => 'adjustment',
		]);

		$response->assertRedirect();
		$this->assertDatabaseHas('other_payments', [
			'id'     => $payment->id,
			'title'  => 'Correction',
			'amount' => 450,
		]);
	}

	/**
	 ** @test
	 **
	 ** Delete an existing other payment record
	 ** and ensure it is removed from the database.
	 **/
	public function test_destroy_deletes_payment(): void
	{
		$payment = OtherPayment::factory()->create(['created_by' => $this->user->creatorId()]);

		$response = $this->delete(route('otherpayment.destroy', $payment->id));
		$response->assertRedirect();
		$this->assertDatabaseMissing('other_payments', ['id' => $payment->id]);
	}

	/**
	 ** @test
	 **
	 ** Shows the OtherPayment view when the record exists and belongs to the user
	 **/
	public function it_displays_the_show_page_for_owner()
	{
		$user = User::factory()->create();
		$op  = OtherPayment::factory()->create([
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)
			->get(route('otherpayment.show', $op->id));

		$response->assertStatus(200)
			->assertViewIs('otherpayment.show')
			->assertViewHas('otherpayment', fn ($v) => $v->id === $op->id);
	}

	/**
	 ** @test
	 **
	 ** Redirects back with error if the record does not exist
	 **/
	public function it_redirects_back_if_not_found()
	{
		$user = User::factory()->create();

		$response = $this->actingAs($user)
			->get(route('otherpayment.show', 9999));

		$response->assertRedirect()
			->assertSessionHas('error', __('Other payment not found.'));
	}

	/**
	 ** @test
	 **
	 ** Denies access when the user is not the owner
	 **/
	public function it_denies_access_for_non_owner()
	{
		$owner = User::factory()->create();
		$other = User::factory()->create();
		$op   = OtherPayment::factory()->create([
			'created_by' => $owner->creatorId(),
		]);

		$response = $this->actingAs($other)
			->get(route('otherpayment.show', $op->id));

		$response->assertStatus(302)
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Denies access if permission check fails
	 **/
	public function it_denies_access_when_not_authorized()
	{
		Gate::before(fn () => false);

		$user = User::factory()->create();
		$op  = OtherPayment::factory()->create([
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)
			->get(route('otherpayment.show', $op->id));

		$response->assertStatus(403);
	}
}
