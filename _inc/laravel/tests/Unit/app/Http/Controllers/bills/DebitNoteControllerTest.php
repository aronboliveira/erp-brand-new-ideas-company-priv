<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\{DebitNote, Bill, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DebitNoteControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $user;
	protected Bill $bill;

	protected function setUp(): void
	{
		parent::setUp();
		$this->user = User::factory()->create();
		$this->actingAs($this->user);

		$this->bill = Bill::factory()->create([
			'created_by' => $this->user->creatorId(),
			'price'      => 1000,
		]);
	}

	/**
	 ** @test
	 **
	 ** Render the debit notes index view for the authenticated user.
	 **/
	public function test_index_renders_view(): void
	{
		$response = $this->get(route('debitnote.index'));
		$response->assertStatus(200)
			->assertViewIs('debitNote.index');
	}

	/**
	 ** @test
	 **
	 ** Display the form to create a new debit note for a specific bill.
	 **/
	public function test_create_renders_form(): void
	{
		$response = $this->get(route('debitnote.create', $this->bill->id));
		$response->assertStatus(200)
			->assertViewIs('debitNote.create');
	}

	/**
	 ** @test
	 **
	 ** Store a new debit note and persist it to the database.
	 **/
	public function test_store_creates_note(): void
	{
		$response = $this->post(route('debitnote.store', $this->bill->id), [
			'amount'      => 100,
			'date'        => now()->toDateString(),
			'description' => 'test note',
		]);

		$response->assertRedirect();
		$this->assertDatabaseHas('debit_notes', [
			'bill'   => $this->bill->id,
			'amount' => 100,
		]);
	}

	/**
	 ** @test
	 **
	 ** Display the edit form for an existing debit note.
	 **/
	public function test_edit_renders_note(): void
	{
		$note = DebitNote::factory()->create(['bill' => $this->bill->id]);
		$response = $this->get(route('debitnote.edit', [$this->bill->id, $note->id]));
		$response->assertStatus(200)
			->assertViewIs('debitNote.edit');
	}

	/**
	 ** @test
	 **
	 ** Update an existing debit note and save changes.
	 **/
	public function test_update_modifies_note(): void
	{
		$note = DebitNote::factory()->create([
			'bill'   => $this->bill->id,
			'amount' => 50,
		]);

		$response = $this->put(route('debitnote.update', [$this->bill->id, $note->id]), [
			'amount'      => 120,
			'date'        => now()->toDateString(),
			'description' => 'updated',
		]);

		$response->assertRedirect();
		$this->assertDatabaseHas('debit_notes', [
			'id'     => $note->id,
			'amount' => 120,
		]);
	}

	/**
	 ** @test
	 **
	 ** Delete an existing debit note and remove it from the database.
	 **/
	public function test_destroy_deletes_note(): void
	{
		$note = DebitNote::factory()->create(['bill' => $this->bill->id]);

		$response = $this->delete(route('debitnote.destroy', [$this->bill->id, $note->id]));
		$response->assertRedirect();
		$this->assertDatabaseMissing('debit_notes', ['id' => $note->id]);
	}

	/**
	 ** @test
	 **
	 ** Display the custom debit note creation form not tied to a specific bill.
	 **/
	public function test_custom_create_renders_form(): void
	{
		$response = $this->get(route('debitnote.custom.create'));
		$response->assertStatus(200)
			->assertViewIs('debitNote.custom_create');
	}

	/**
	 ** @test
	 **
	 ** Store a custom debit note with an explicit bill reference in the request.
	 **/
	public function test_custom_store_creates_note(): void
	{
		$response = $this->post(route('debitnote.custom.store'), [
			'bill'        => $this->bill->id,
			'amount'      => 100,
			'date'        => now()->toDateString(),
			'description' => 'custom test',
		]);

		$response->assertRedirect();
		$this->assertDatabaseHas('debit_notes', [
			'bill'   => $this->bill->id,
			'amount' => 100,
		]);
	}

	/**
	 ** @test
	 **
	 ** Return the outstanding due for a given bill as JSON.
	 **/
	public function test_get_bill_returns_due(): void
	{
		$response = $this->getJson(route('debitnote.get.bill', ['bill_id' => $this->bill->id]));
		$response->assertStatus(200)
			->assertJsonStructure(['due']);
	}
}
