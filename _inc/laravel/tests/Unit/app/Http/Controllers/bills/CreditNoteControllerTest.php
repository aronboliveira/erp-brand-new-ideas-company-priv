<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\{CreditNote, Invoice, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreditNoteControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $user;
	protected Invoice $invoice;

	protected function setUp(): void
	{
		parent::setUp();

		$this->user = User::factory()->create();
		$this->actingAs($this->user);

		$this->invoice = Invoice::factory()->create([
			'created_by' => $this->user->creatorId(),
			'price'      => 1000,
		]);
	}

	/**
	 ** @test
	 **
	 ** The index route should load the creditNote.index view
	 ** without errors.
	 **/
	public function test_index_loads_view(): void
	{
		$response = $this->get(route('creditnote.index'));

		$response
			->assertStatus(200)
			->assertViewIs('creditNote.index');
	}

	/**
	 ** @test
	 **
	 ** The create route for a given invoice should load
	 ** the view with the invoice’s due amount.
	 **/
	public function test_create_loads_invoice_data(): void
	{
		$response = $this->get(route('creditnote.create', [
			'invoiceId' => $this->invoice->id,
		]));

		$response
			->assertStatus(200)
			->assertViewHas('invoiceDue');
	}

	/**
	 ** @test
	 **
	 ** Posting valid data to store should create a new credit note
	 ** and then redirect (back to a list or detail page).
	 **/
	public function test_store_creates_credit_note(): void
	{
		$response = $this->post(route('creditnote.store', [
			'invoiceId' => $this->invoice->id,
		]), [
			'amount' => 100,
			'date'   => now()->toDateString(),
		]);

		$response->assertRedirect();
		$this->assertDatabaseHas('credit_notes', [
			'invoice' => $this->invoice->id,
			'amount'  => 100,
		]);
	}

	/**
	 ** @test
	 **
	 ** Attempting to store a credit note with invalid data
	 ** should produce validation errors for amount and date.
	 **/
	public function test_store_validation_fails(): void
	{
		$response = $this->post(route('creditnote.store', [
			'invoiceId' => $this->invoice->id,
		]), [
			'amount' => 'INVALID',
			'date'   => '',
		]);

		$response->assertSessionHasErrors(['amount', 'date']);
	}

	/**
	 ** @test
	 **
	 ** The edit route should load the form populated with
	 ** the existing credit note data.
	 **/
	public function test_edit_loads_credit_note(): void
	{
		$creditNote = CreditNote::factory()->create([
			'invoice' => $this->invoice->id,
		]);

		$response = $this->get(route('creditnote.edit', [
			'invoiceId'     => $this->invoice->id,
			'creditNoteId'  => $creditNote->id,
		]));

		$response
			->assertStatus(200)
			->assertViewHas('creditNote');
	}

	/**
	 ** @test
	 **
	 ** Sending valid updated data to update should change
	 ** the credit note in the database and redirect.
	 **/
	public function test_update_credit_note(): void
	{
		$creditNote = CreditNote::factory()->create([
			'invoice' => $this->invoice->id,
			'amount'  => 50,
		]);

		$response = $this->put(route('creditnote.update', [
			'invoiceId'     => $this->invoice->id,
			'creditNoteId'  => $creditNote->id,
		]), [
			'amount' => 80,
			'date'   => now()->toDateString(),
		]);

		$response->assertRedirect();
		$this->assertDatabaseHas('credit_notes', [
			'id'     => $creditNote->id,
			'amount' => 80,
		]);
	}

	/**
	 ** @test
	 **
	 ** Deleting a credit note should remove it from
	 ** the database and redirect back.
	 **/
	public function test_destroy_deletes_credit_note(): void
	{
		$creditNote = CreditNote::factory()->create([
			'invoice' => $this->invoice->id,
		]);

		$response = $this->delete(route('creditnote.destroy', [
			'invoiceId'     => $this->invoice->id,
			'creditNoteId'  => $creditNote->id,
		]));

		$response->assertRedirect();
		$this->assertDatabaseMissing('credit_notes', [
			'id' => $creditNote->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** The custom create route should load the
	 ** creditNote.custom_create view.
	 **/
	public function test_custom_create_view(): void
	{
		$response = $this->get(route('creditnote.custom.create'));

		$response
			->assertStatus(200)
			->assertViewIs('creditNote.custom_create');
	}

	/**
	 ** @test
	 **
	 ** Posting to the custom store route should create
	 ** a credit note and redirect.
	 **/
	public function test_custom_store_works(): void
	{
		$response = $this->post(route('creditnote.custom.store'), [
			'invoice' => $this->invoice->id,
			'amount'  => 100,
			'date'    => now()->toDateString(),
		]);

		$response->assertRedirect();
		$this->assertDatabaseHas('credit_notes', [
			'invoice' => $this->invoice->id,
			'amount'  => 100,
		]);
	}

	/**
	 ** @test
	 **
	 ** The get-invoice JSON endpoint should return
	 ** a structure containing the current due amount.
	 **/
	public function test_get_invoice_returns_due(): void
	{
		$response = $this->getJson(route('creditnote.get.invoice', [
			'id' => $this->invoice->id,
		]));

		$response->assertJsonStructure(['due']);
	}
}
