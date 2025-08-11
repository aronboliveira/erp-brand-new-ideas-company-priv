<?php

namespace Tests\Feature;

use App\Models\{
	Contract,
	ContractAttachment,
	ContractComment,
	ContractNotes,
	ContractType,
	Project,
	User
};
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{
	Gate,
	Notification,
	Storage,
	URL
};
use Tests\TestCase;

class ContractControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private User $client;
	private ContractType $type;
	private Project $project;

	protected function setUp(): void
	{
		parent::setUp();

		// allow all permissions
		Gate::before(fn () => true);

		// so creatorId() returns user's own ID
		User::macro('creatorId', function () {
			/** @var \App\Models\User $this */
			return $this->id;
		});

		$this->company = User::factory()->create(['type' => 'company']);
		$this->client = User::factory()->create(['type' => 'client', 'created_by' => $this->company->creatorId()]);

		$this->type   = ContractType::factory()->create(['created_by' => $this->company->creatorId()]);
		$this->project = Project::factory()->create(['created_by' => $this->company->creatorId()]);

		$this->actingAs($this->company);
	}

	/**
	 ** @test
	 **
	 ** index_shows_company_overview:
	 **   - seeds several contracts for the company
	 **   - asserts the index view is rendered with 'all' collection and counts summary
	 **/
	public function index_shows_company_overview()
	{
		Contract::factory()->count(3)->create(['created_by' => $this->company->creatorId()]);
		$resp = $this->get(route('contract.index'));
		$resp->assertOk()
			->assertViewIs('contract.index')
			->assertViewHas('all', fn ($all) => $all->count() === 3)
			->assertViewHas('cnt');
	}

	/**
	 ** @test
	 **
	 ** create_shows_form_with_types_clients_and_projects:
	 **   - renders the contract.create view
	 **   - passes contractTypes, clients, and projects to the view
	 **/
	public function create_shows_form_with_types_clients_and_projects()
	{
		$resp = $this->get(route('contract.create'));
		$resp->assertOk()
			->assertViewIs('contract.create')
			->assertViewHasAll(['contractTypes', 'clients', 'projects']);
	}

	/**
	 ** @test
	 **
	 ** store_validates_and_creates_contract:
	 **   - posts valid payload to store
	 **   - redirects to index
	 **   - ensures contract record is created in database
	 **/
	public function store_validates_and_creates_contract()
	{
		$payload = [
			'client_name' => $this->client->id,
			'subject'     => 'Test',
			'type'        => $this->type->id,
			'value'       => 1000,
			'start_date'  => now()->toDateString(),
			'end_date'    => now()->addDay()->toDateString(),
			'project_id'  => $this->project->id,
		];
		$resp = $this->post(route('contract.store'), $payload);
		$resp->assertRedirect(route('contract.index'));
		$this->assertDatabaseHas('contracts', ['subject' => 'Test', 'created_by' => $this->company->creatorId()]);
	}

	/**
	 ** @test
	 **
	 ** show_denies_and_allows_based_on_owner:
	 **   - allows owner to view the show page
	 **   - denies access for non-owner user
	 **/
	public function show_denies_and_allows_based_on_owner()
	{
		$contract = Contract::factory()->create(['created_by' => $this->company->creatorId()]);
		$resp = $this->get(route('contract.show', $contract->id));
		$resp->assertOk()->assertViewIs('contract.show');

		$this->actingAs(User::factory()->create());
		$resp = $this->get(route('contract.show', $contract->id));
		$resp->assertStatus(403);
	}

	/**
	 ** @test
	 **
	 ** edit_shows_form:
	 **   - renders the edit form for an existing contract
	 **   - passes c, types, clients, and projects to the view
	 **/
	public function edit_shows_form()
	{
		$c = Contract::factory()->create(['created_by' => $this->company->creatorId()]);
		$resp = $this->get(route('contract.edit', $c->id));
		$resp->assertOk()->assertViewIs('contract.edit')
			->assertViewHasAll(['c', 'types', 'clients', 'projects']);
	}

	/**
	 ** @test
	 **
	 ** update_validates_and_saves:
	 **   - submits updated values
	 **   - redirects to index
	 **   - ensures database reflects updated subject
	 **/
	public function update_validates_and_saves()
	{
		$c = Contract::factory()->create(['created_by' => $this->company->creatorId()]);
		$resp = $this->put(route('contract.update', $c->id), [
			'client_name' => $this->client->id,
			'subject'     => 'Updated',
			'type'        => $this->type->id,
			'value'       => 2000,
			'start_date'  => now()->toDateString(),
			'end_date'    => now()->addDay()->toDateString(),
		]);
		$resp->assertRedirect(route('contract.index'));
		$this->assertDatabaseHas('contracts', ['id' => $c->id, 'subject' => 'Updated']);
	}

	/**
	 ** @test
	 **
	 ** destroy_deletes_contract:
	 **   - deletes an existing contract
	 **   - redirects to index
	 **   - ensures the record no longer exists
	 **/
	public function destroy_deletes_contract()
	{
		$c = Contract::factory()->create(['created_by' => $this->company->creatorId()]);
		$resp = $this->delete(route('contract.destroy', $c->id));
		$resp->assertRedirect(route('contract.index'));
		$this->assertDatabaseMissing('contracts', ['id' => $c->id]);
	}

	/**
	 ** @test
	 **
	 ** description_and_grid_endpoints_render:
	 **   - description shows the contract.description view
	 **   - grid shows the contract.grid view
	 **/
	public function description_and_grid_endpoints_render()
	{
		$c = Contract::factory()->create(['created_by' => $this->company->creatorId()]);
		$this->get(route('contract.description', $c->id))->assertOk()->assertViewIs('contract.description');
		$this->get(route('contract.grid'))->assertOk()->assertViewIs('contract.grid');
	}

	/**
	 ** @test
	 **
	 ** file_upload_download_and_delete_flow:
	 **   - uploads a file via AJAX and asserts JSON success
	 **   - downloads the uploaded file
	 **   - deletes it and asserts removal from database
	 **/
	public function file_upload_download_and_delete_flow()
	{
		Storage::fake('local');
		$c = Contract::factory()->create(['created_by' => $this->company->creatorId()]);

		// upload
		$file = UploadedFile::fake()->create('doc.pdf', 100);
		$up  = $this->postJson(route('contracts.file.upload', $c->id), ['file' => $file]);
		$up->assertJson(['is_success' => true]);
		$attach = ContractAttachment::first();
		$adapter = Storage::disk('local');
		assert($adapter instanceof FilesystemAdapter);
		$adapter->assertExists("contract_attachment/{$attach->files}");

		// download
		$dl = $this->get(route('contracts.file.download', [$c->id, $attach->id]));
		$dl->assertStatus(200);

		// delete
		$del = $this->deleteJson(route('contracts.file.delete', [$c->id, $attach->id]));
		$del->assertJson(['is_success' => true]);
		$this->assertDatabaseMissing('contract_attachments', ['id' => $attach->id]);
	}

	/**
	 ** @test
	 **
	 ** status_edit_returns_json_success:
	 **   - posts a new status via AJAX
	 **   - asserts JSON success and updates the model
	 **/
	public function status_edit_returns_json_success()
	{
		$c = Contract::factory()->create(['created_by' => $this->company->creatorId()]);
		$res = $this->postJson(route('contracts.status.edit', $c->id), ['status' => 'completed']);
		$res->assertJson(['is_success' => true]);
		$this->assertEquals('completed', $c->fresh()->status);
	}

	/**
	 ** @test
	 **
	 ** comment_and_note_crud_and_description_store:
	 **   - creates, then deletes a comment
	 **   - creates, then deletes a note
	 **   - updates description via AJAX
	 **/
	public function comment_and_note_crud_and_description_store()
	{
		$c = Contract::factory()->create(['created_by' => $this->company->creatorId()]);

		// comment store & destroy
		$this->post(route('contracts.comment.store', $c->id), ['comment' => 'Hello'])
			->assertRedirect();
		$comm = ContractComment::first();
		$this->delete(route('contracts.comment.destroy', $comm->id))->assertRedirect();
		$this->assertDatabaseMissing('contract_comments', ['id' => $comm->id]);

		// note store & destroy
		$this->post(route('contracts.note.store', $c->id), ['notes' => 'Note'])
			->assertRedirect();
		$note = ContractNotes::first();
		$this->delete(route('contracts.note.destroy', $note->id))->assertRedirect();
		$this->assertDatabaseMissing('contract_notes', ['id' => $note->id]);

		// description store
		$res = $this->postJson(route('contracts.description.store', $c->id), ['contract_description' => 'Desc']);
		$res->assertJson(['is_success' => true]);
		$this->assertEquals('Desc', $c->fresh()->contract_description);
	}

	/**
	 ** @test
	 **
	 ** client_wise_project_returns_json:
	 **   - returns a JSON list of projects filtered by client_id
	 **/
	public function client_wise_project_returns_json()
	{
		$other = Project::factory()->create(['client_id' => $this->client->id]);
		$res = $this->getJson(route('contracts.clientWiseProject', $this->client->id));
		$res->assertOk()->assertJsonFragment(['id' => $other->id, 'name' => $other->project_name]);
	}

	/**
	 ** @test
	 **
	 ** print_copy_signature_and_pdf_endpoints:
	 **   - renders print preview, signature form, copy form, and PDF template
	 **   - stores a signature via AJAX
	 **   - processes copying a contract
	 **/
	public function print_copy_signature_and_pdf_endpoints()
	{
		$c = Contract::factory()->create(['created_by' => $this->company->creatorId()]);

		$this->get(route('contract.print', $c->id))->assertOk()->assertViewIs('contract.preview');
		$this->get(route('contract.signature', $c->id))->assertOk()->assertViewIs('contract.signature');

		// signature store
		$res = $this->postJson(route('contract.signature.store'), [
			'contract_id' => $c->id,
			'company_signature' => 'sig'
		]);
		$res->assertJson(['success' => true]);

		// copy form & store
		$this->get(route('contract.copy', $c->id))->assertOk()->assertViewIs('contract.copy');
		$payload = [
			'client'     => $this->client->id,
			'subject'    => 'Copy',
			'project_id' => [$this->project->id],
			'type'       => $this->type->id,
			'value'      => 500,
			'start_date' => now()->toDateString(),
			'end_date'   => now()->addDay()->toDateString(),
		];
		$this->post(route('contract.copy.store'), $payload)
			->assertRedirect(route('contract.index'));

		// pdf rendering
		$enc = encrypt($c->id);
		$this->get(route('contract.pdf', $enc))->assertOk()->assertViewIs('contract.template');
	}

	/**
	 ** @test
	 **
	 ** send_mail_contract_redirects_with_success:
	 **   - triggers sending email notification
	 **   - redirects back to show with success message
	 **/
	public function send_mail_contract_redirects_with_success()
	{
		Notification::fake();
		$c = Contract::factory()->create(['created_by' => $this->company->creatorId()]);
		$this->post(route('contract.sendMail', $c->id))
			->assertRedirect(route('contract.show', $c->id))
			->assertSessionHas('success');
	}
}
