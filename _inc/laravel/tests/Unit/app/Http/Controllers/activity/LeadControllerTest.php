<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Mail, Storage};
use App\Http\Controllers\LeadController;
use App\Models\{
	Label,
	Lead,
	LeadCall,
	LeadFile,
	LeadStage,
	Pipeline,
	ProductService,
	Source,
	Stage,
	User,
	UserLead,
};

class LeadControllerTest extends TestCase
{
	use RefreshDatabase;
	private LeadController $controller;

	protected function setUp(): void
	{
		parent::setUp();
		$this->controller = new LeadController();
	}

	/* helper: login & give the user one specific permission */
	protected function acting_as_user_with_permission(string $perm): User
	{
		$user = User::factory()->create();
		$user?->givePermissionTo($perm);
		$this->actingAs($user);

		return $user;
	}

	/**
	 ** @test
	 **
	 ** The index view should be accessible for users
	 ** with the 'manage lead' permission.
	 **/
	public function test_index_view_authorized(): void
	{
		$admin = $this->acting_as_user_with_permission('manage lead');
		Pipeline::factory()->create(['created_by' => $admin->creatorId()]);

		$res = $this->get(route('leads.index'));

		$res->assertOk()
			->assertViewIs('leads.index');
	}

	/**
	 ** @test
	 **
	 ** The index view should return 403 Forbidden
	 ** when the user lacks the 'manage lead' permission.
	 **/
	public function test_index_view_unauthorized(): void
	{
		$this->actingAs(User::factory()->create());

		$this->get(route('leads.index'))
			->assertForbidden();
	}

	/**
	 ** @test
	 **
	 ** The lead list should render for a user
	 ** assigned to that lead.
	 **/
	public function test_lead_list_renders_for_assigned_user(): void
	{
		$admin = $this->acting_as_user_with_permission('manage lead');
		$pipe = Pipeline::factory()->create(['created_by' => $admin->creatorId()]);
		$stage = LeadStage::factory()->create(['pipeline_id' => $pipe->id]);
		$lead = Lead::factory()->create([
			'pipeline_id' => $pipe->id,
			'stage_id'    => $stage->id,
			'created_by'  => $admin->creatorId(),
		]);
		$lead->users()->attach($admin->id);

		$this->get(route('leads.list'))
			->assertOk()
			->assertViewIs('leads.list')
			->assertSee($lead->name);
	}

	/**
	 ** @test
	 **
	 ** The create view should be accessible for users
	 ** with the 'create lead' permission.
	 **/
	public function test_create_view_is_accessible(): void
	{
		$this->acting_as_user_with_permission('create lead');

		$this->get(route('leads.create'))
			->assertOk()
			->assertViewIs('leads.create');
	}

	/**
	 ** @test
	 **
	 ** Storing a valid lead should persist it
	 ** and return a success redirect.
	 **/
	public function test_store_valid_lead_persists(): void
	{
		$admin = $this->acting_as_user_with_permission('create lead');
		Pipeline::factory()->create(['created_by' => $admin->creatorId()]);
		LeadStage::factory()->create();

		$payload = [
			'subject' => 'New opportunity',
			'name'    => 'Alice',
			'email'   => 'alice@example.com',
		];

		$this->post(route('leads.store'), $payload)
			->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseHas('leads', ['email' => 'alice@example.com']);
	}

	/**
	 ** @test
	 **
	 ** Attempting to store a lead with a duplicate email
	 ** should return an error in session.
	 **/
	public function test_store_duplicate_email_is_rejected(): void
	{
		$admin = $this->acting_as_user_with_permission('create lead');
		$lead = Lead::factory()->create(['email' => 'inuse@example.com']);

		$payload = ['subject' => 'x', 'name' => 'y', 'email' => $lead->email];

		$this->post(route('leads.store'), $payload)
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** The show view should be accessible to the owner
	 ** with 'view lead' permission.
	 **/
	public function test_show_view_authorized_owner(): void
	{
		$admin = $this->acting_as_user_with_permission('view lead');
		$pipe = Pipeline::factory()->create(['created_by' => $admin->creatorId()]);
		$stage = LeadStage::factory()->create(['pipeline_id' => $pipe->id]);
		$lead = Lead::factory()->create([
			'pipeline_id' => $pipe->id,
			'stage_id'    => $stage->id,
			'created_by'  => $admin->creatorId(),
		]);

		$this->get(route('leads.show', $lead))
			->assertOk()
			->assertViewIs('leads.show');
	}

	/**
	 ** @test
	 **
	 ** The edit view should be accessible to the owner
	 ** with 'edit lead' permission.
	 **/
	public function test_edit_view_authorized_owner(): void
	{
		$admin = $this->acting_as_user_with_permission('edit lead');
		$lead = Lead::factory()->forCreator($admin)->create();

		$this->get(route('leads.edit', $lead))
			->assertOk()
			->assertViewIs('leads.edit');
	}

	/**
	 ** @test
	 **
	 ** Updating a lead with valid data should persist changes
	 ** and return a success redirect.
	 **/
	public function test_update_lead_with_valid_data(): void
	{
		$admin = $this->acting_as_user_with_permission('edit lead');
		$lead = Lead::factory()->forCreator($admin)->create();

		$payload = [
			'subject'     => 'Updated',
			'name'        => 'Bob',
			'email'       => 'bob@example.com',
			'pipeline_id' => $lead->pipeline_id,
			'user_id'     => $admin->id,
			'stage_id'    => $lead->stage_id,
			'sources'     => [1],
			'products'    => [1],
		];

		$this->put(route('leads.update', $lead), $payload)
			->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseHas('leads', ['id' => $lead->id, 'name' => 'Bob']);
	}

	/**
	 ** @test
	 **
	 ** Destroying a lead should remove it and return
	 ** a success redirect for users with 'delete lead' permission.
	 **/
	public function test_destroy_deletes_lead(): void
	{
		$admin = $this->acting_as_user_with_permission('delete lead');
		$lead = Lead::factory()->forCreator($admin)->create();

		$this->delete(route('leads.destroy', $lead))
			->assertRedirect()
			->assertSessionHas('success');

		$this->assertModelMissing($lead);
	}

	/**
	 ** @test
	 **
	 ** The JSON endpoint should return the stages
	 ** for the specified pipeline.
	 **/
	public function test_json_returns_stages_for_pipeline(): void
	{
		$admin = $this->acting_as_user_with_permission('manage lead');
		$pipe = Pipeline::factory()->create(['created_by' => $admin->creatorId()]);
		$stage = LeadStage::factory()->create(['pipeline_id' => $pipe->id]);

		$res = $this->getJson(route('leads.json', ['pipeline_id' => $pipe->id]));

		$res->assertOk()->assertJsonFragment([$stage->id => $stage->name]);
	}

	/**
	 ** @test
	 **
	 ** Uploading a file should create a database record
	 ** and store the file on disk.
	 **/
	public function test_file_upload_creates_db_row_and_stores_file(): void
	{
		$admin = $this->acting_as_user_with_permission('edit lead');
		Storage::fake();

		$lead = Lead::factory()->forCreator($admin)->create();
		$file = UploadedFile::fake()->create('demo.pdf', 20);

		$this->post(
			route('leads.file.upload', $lead->id),
			['file' => $file]
		)->assertOk()->assertJson(['is_success' => true]);

		$this->assertDatabaseHas('lead_files', ['lead_id' => $lead->id]);
		Storage::assertExists('lead_files');
	}

	/**
	 ** @test
	 **
	 ** Deleting a file should remove its DB record
	 ** and delete the file from storage.
	 **/
	public function test_file_delete_removes_file_and_row(): void
	{
		$admin = $this->acting_as_user_with_permission('edit lead');
		Storage::fake();

		$lead  = Lead::factory()->forCreator($admin)->create();
		$stored = UploadedFile::fake()->create('s.pdf', 5);
		$path  = "{$lead->id}_hash_s.pdf";
		Storage::put("lead_files/{$path}", $stored->getContent());

		$file = LeadFile::create([
			'lead_id'   => $lead->id,
			'file_name' => 's.pdf',
			'file_path' => $path,
		]);

		$this->delete(
			route('leads.file.delete', [$lead->id, $file->id])
		)->assertOk()->assertJson(['is_success' => true]);

		$this->assertModelMissing($file);
		Storage::assertMissing("lead_files/{$path}");
	}

	/**
	 ** @test
	 **
	 ** Storing a note via AJAX should update
	 ** the lead's notes field and return success.
	 **/
	public function test_note_store_updates_notes(): void
	{
		$admin = $this->acting_as_user_with_permission('edit lead');
		$lead = Lead::factory()->forCreator($admin)->create();

		$this->postJson(
			route('leads.note.store', $lead->id),
			['notes' => 'Important!']
		)->assertOk()->assertJson(['is_success' => true]);

		$this->assertEquals('Important!', $lead->fresh()->notes);
	}

	/**
	 ** @test
	 **
	 ** Posting labels should save the selected labels
	 ** to the lead and return success.
	 **/
	public function test_label_store_saves_selected_labels(): void
	{
		$admin = $this->acting_as_user_with_permission('edit lead');
		$pipe = Pipeline::factory()->create(['created_by' => $admin->creatorId()]);
		$label = Label::factory()->create(['pipeline_id' => $pipe->id, 'created_by' => $admin->creatorId()]);
		$lead = Lead::factory()->forCreator($admin)->create(['pipeline_id' => $pipe->id]);

		$this->post(
			route('leads.label.store', $lead->id),
			['labels' => [$label->id]]
		)->assertRedirect()->assertSessionHas('success');

		$this->assertDatabaseHas('leads', [
			'id'     => $lead->id,
			'labels' => (string)$label->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** Updating users on a lead should attach
	 ** the given users and return success.
	 **/
	public function test_user_update_adds_users_to_lead(): void
	{
		$admin = $this->acting_as_user_with_permission('edit lead');
		$member = User::factory()->create(['created_by' => $admin->creatorId()]);
		$lead  = Lead::factory()->forCreator($admin)->create();

		$this->put(
			route('leads.user.update', $lead->id),
			['users' => [$member->id]]
		)->assertRedirect()->assertSessionHas('success');

		$this->assertDatabaseHas('user_leads', [
			'lead_id' => $lead->id,
			'user_id' => $member->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** Updating products on a lead should attach
	 ** the given products and return success.
	 **/
	public function test_product_update_attaches_products(): void
	{
		$admin  = $this->acting_as_user_with_permission('edit lead');
		$product = ProductService::factory()->create(['created_by' => $admin->creatorId()]);
		$lead   = Lead::factory()->forCreator($admin)->create(['products' => '']);

		$this->put(
			route('leads.product.update', $lead->id),
			['products' => [$product->id]]
		)->assertRedirect()->assertSessionHas('success');

		$this->assertStringContainsString((string)$product->id, $lead->fresh()->products);
	}

	/**
	 ** @test
	 **
	 ** Updating sources on a lead should replace
	 ** the sources and return success.
	 **/
	public function test_source_update_replaces_sources(): void
	{
		$admin = $this->acting_as_user_with_permission('edit lead');
		$source = Source::factory()->create(['created_by' => $admin->creatorId()]);
		$lead  = Lead::factory()->forCreator($admin)->create(['sources' => '']);

		$this->put(
			route('leads.source.update', $lead->id),
			['sources' => [$source->id]]
		)->assertRedirect()->assertSessionHas('success');

		$this->assertEquals((string)$source->id, $lead->fresh()->sources);
	}

	/**
	 ** @test
	 **
	 ** Storing a discussion comment should
	 ** persist it and return success.
	 **/
	public function test_discussion_store_adds_comment(): void
	{
		$admin = $this->acting_as_user_with_permission('edit lead');
		$lead = Lead::factory()->forCreator($admin)->create();

		$this->post(
			route('leads.discussion.store', $lead->id),
			['comment' => 'Hello']
		)->assertRedirect()->assertSessionHas('success');

		$this->assertDatabaseHas('lead_discussions', [
			'lead_id' => $lead->id,
			'comment' => 'Hello',
		]);
	}

	/**
	 ** @test
	 **
	 ** Moving a lead to a new stage should
	 ** update the stage_id and return JSON success.
	 **/
	public function test_order_moves_lead_and_logs_activity(): void
	{
		$admin = $this->acting_as_user_with_permission('move lead');
		$pipe  = Pipeline::factory()->create(['created_by' => $admin->creatorId()]);
		$stage1 = LeadStage::factory()->create(['pipeline_id' => $pipe->id]);
		$stage2 = LeadStage::factory()->create(['pipeline_id' => $pipe->id]);
		$lead  = Lead::factory()->create([
			'pipeline_id' => $pipe->id,
			'stage_id'    => $stage1->id,
			'created_by'  => $admin->creatorId(),
		]);

		$payload = [
			'lead_id'  => $lead->id,
			'stage_id' => $stage2->id,
			'order'    => [$lead->id],
		];

		$this->postJson(route('leads.order'), $payload)
			->assertOk()->assertJson(['success' => true]);

		$this->assertEquals($stage2->id, $lead->fresh()->stage_id);
	}

	/**
	 ** @test
	 **
	 ** The show-convert-to-deal view should display
	 ** the convert form for authorized users.
	 **/
	public function test_show_convert_to_deal_displays_view(): void
	{
		$admin = $this->acting_as_user_with_permission('convert lead');
		$lead = Lead::factory()->forCreator($admin)->create();

		$this->get(route('leads.convert.show', $lead->id))
			->assertOk()
			->assertViewIs('leads.convert')
			->assertViewHas('lead', $lead);
	}

	/**
	 ** @test
	 **
	 ** Converting to deal should create a new client,
	 ** a deal, mark the lead as converted, and queue emails.
	 **/
	public function test_convert_to_deal_creates_client_and_deal(): void
	{
		Mail::fake();
		$admin = $this->acting_as_user_with_permission('convert lead');
		$pipe = Pipeline::factory()->create(['created_by' => $admin->creatorId()]);
		$stage = Stage::factory()->create(['pipeline_id' => $pipe->id]);
		$lead = Lead::factory()->forCreator($admin)
			->create(['pipeline_id' => $pipe->id, 'stage_id' => $stage->id]);

		$payload = [
			'client_name'     => 'Acme Inc.',
			'client_email'    => 'acme@example.com',
			'client_password' => 'secret123',
			'name'            => 'Big Deal',
			'price'           => 1000,
			'is_transfer'     => ['discussion', 'files'],
		];

		$this->post(route('leads.convert.do', $lead->id), $payload)
			->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseHas('users', ['email' => 'acme@example.com', 'type' => 'client']);
		$this->assertDatabaseHas('deals', ['name' => 'Big Deal']);
		$this->assertNotNull($lead->fresh()->is_converted);
		Mail::assertQueued(\App\Mail\SendLeadEmail::class);
	}

	/**
	 ** @test
	 **
	 ** Storing a lead call should persist the call
	 ** and return a success redirect.
	 **/
	public function test_call_store_persists_call(): void
	{
		$admin = $this->acting_as_user_with_permission('create lead call');
		$lead = Lead::factory()->forCreator($admin)->create();
		UserLead::create(['lead_id' => $lead->id, 'user_id' => $admin->id]);

		$payload = [
			'subject'   => 'Demo Call',
			'call_type' => 'Outbound',
			'user_id'   => $admin->id,
		];

		$this->post(route('leads.call.store', $lead->id), $payload)
			->assertRedirect()->assertSessionHas('success');

		$this->assertDatabaseHas('lead_calls', ['lead_id' => $lead->id, 'subject' => 'Demo Call']);
	}

	/**
	 ** @test
	 **
	 ** Updating an existing lead call should
	 ** modify its subject and return success.
	 **/
	public function test_call_update_modifies_existing_call(): void
	{
		$admin = $this->acting_as_user_with_permission('edit lead call');
		$lead = Lead::factory()->forCreator($admin)->create();
		$call = LeadCall::factory()->create([
			'lead_id' => $lead->id,
			'subject' => 'Old Subject',
			'user_id' => $admin->id,
		]);

		$payload = [
			'subject'   => 'Updated Subject',
			'call_type' => 'Inbound',
			'user_id'   => $admin->id,
		];

		$this->put(route('leads.call.update', [$lead->id, $call->id]), $payload)
			->assertRedirect()->assertSessionHas('success');

		$this->assertEquals('Updated Subject', $call->fresh()->subject);
	}

	/**
	 ** @test
	 **
	 ** Deleting a lead call should remove it
	 ** and return a success redirect.
	 **/
	public function test_call_destroy_deletes_call(): void
	{
		$admin = $this->acting_as_user_with_permission('delete lead call');
		$lead = Lead::factory()->forCreator($admin)->create();
		$call = LeadCall::factory()->create(['lead_id' => $lead->id]);

		$this->delete(route('leads.call.destroy', [$lead->id, $call->id]))
			->assertRedirect()->assertSessionHas('success');

		$this->assertModelMissing($call);
	}

	/**
	 ** @test
	 **
	 ** Storing a lead email should save it,
	 ** queue the mail, and return success.
	 **/
	public function test_email_store_saves_email_and_sends(): void
	{
		Mail::fake();
		$admin = $this->acting_as_user_with_permission('create lead email');
		$lead = Lead::factory()->forCreator($admin)->create();

		$payload = [
			'to'          => 'lead@example.com',
			'subject'     => 'Hello',
			'description' => 'Body',
		];

		$this->post(route('leads.email.store', $lead->id), $payload)
			->assertRedirect()->assertSessionHas('success');

		$this->assertDatabaseHas('lead_emails', [
			'lead_id' => $lead->id,
			'subject' => 'Hello',
		]);
		Mail::assertQueued(\App\Mail\SendLeadEmail::class);
	}

	/**
	 ** @test
	 **
	 ** Calling lead() with a valid ID returns the corresponding Lead model.
	 **/
	public function it_returns_the_lead_model_for_valid_id()
	{
		$lead = Lead::factory()->create();

		$result = $this->controller->lead($lead->id);

		$this->assertInstanceOf(Lead::class, $result);
		$this->assertEquals($lead->id, $result->id);
	}

	/**
	 ** @test
	 **
	 ** Calling lead() twice with the same ID returns the same instance (cached).
	 **/
	public function it_caches_the_lead_instance_for_subsequent_calls()
	{
		$lead = Lead::factory()->create();

		$first = $this->controller->lead($lead->id);
		$second = $this->controller->lead($lead->id);

		$this->assertSame($first, $second, 'Expected the same instance to be returned from cache');
	}

	/**
	 ** @test
	 **
	 ** Calling lead() with a different ID returns a different instance.
	 **/
	public function it_loads_a_new_instance_for_different_ids()
	{
		$lead1 = Lead::factory()->create();
		$lead2 = Lead::factory()->create();

		$first = $this->controller->lead($lead1->id);
		$second = $this->controller->lead($lead2->id);

		$this->assertNotSame($first, $second, 'Expected different instances for different IDs');
		$this->assertEquals($lead2->id, $second->id);
	}

	/**
	 ** @test
	 **
	 ** Calling lead() with a non-existent ID throws a ModelNotFoundException.
	 **/
	public function it_throws_when_lead_not_found()
	{
		$this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

		// No leads in DB, ID=999 should fail
		$this->controller->lead(999);
	}
}
