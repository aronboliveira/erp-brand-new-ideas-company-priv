<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Crypt, Gate};
use App\Models\{Support, SupportReply, User};
use Spatie\Permission\Models\Permission;

class SupportControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private User $other;

	protected function setUp(): void
	{
		parent::setUp();

		// allow all permission checks
		Gate::before(fn () => true);

		// make creatorId() return the user's own ID
		User::macro('creatorId', function () {
			/** @var User $this */
			return $this->id;
		});

		$this->company = User::factory()->create(['type' => 'company']);
		$this->other  = User::factory()->create(['type' => 'company']);
	}

	/**
	 ** @test
	 **
	 ** Index should list all tickets for company users.
	 **/
	public function test_index_lists_all_tickets_for_company()
	{
		$company = User::factory()->create(['type' => 'company']);
		$company->givePermissionTo(Permission::create(['name' => 'manage support']));

		// tickets created under the company
		Support::factory()->count(3)->create(['created_by' => $company->creatorId(), 'user' => $company->id]);
		// ticket belonging to another company
		Support::factory()->create(['created_by' => $company->creatorId() + 1, 'user' => $company->id + 1]);

		$response = $this->actingAs($company)->get(route('support.index'));

		$response->assertStatus(200)
			->assertViewIs('support.index')
			->assertViewHas('supports', function ($supports) {
				return $supports->count() === 3;
			})
			->assertViewHasAll(['countAll', 'countOpen', 'countOnHold', 'countClosed']);
	}

	/**
	 ** @test
	 **
	 ** Index should list only own tickets for non-company users.
	 **/
	public function test_index_filters_tickets_for_client()
	{
		$client = User::factory()->create(['type' => 'client']);
		$client->givePermissionTo(Permission::create(['name' => 'manage support']));

		// two tickets for this client
		Support::factory()->count(2)->create([
			'created_by' => $client->creatorId(),
			'user'       => $client->id,
		]);
		// one ticket for other user
		Support::factory()->create([
			'created_by' => $client->creatorId(),
			'user'       => $client->id + 1,
		]);

		$response = $this->actingAs($client)->get(route('support.index'));

		$response->assertStatus(200)
			->assertViewHas('supports', function ($supports) {
				return $supports->count() === 2;
			});
	}

	/**
	 ** @test
	 **
	 ** Create should show form when user has permission.
	 **/
	public function test_create_displays_form_with_permission()
	{
		$user = User::factory()->create();
		$user->givePermissionTo(Permission::create(['name' > 'create support']));

		$response = $this->actingAs($user)->get(route('support.create'));

		$response->assertStatus(200)
			->assertViewIs('support.create')
			->assertViewHasAll(['priority', 'status', 'users']);
	}

	/**
	 ** @test
	 **
	 ** Store should persist a new support ticket and redirect.
	 **/
	public function test_store_persists_new_ticket_and_redirects()
	{
		$user = User::factory()->create();
		$user->givePermissionTo(Permission::create(['name' > 'create support']));

		$data = [
			'subject'  => 'Help Needed',
			'priority' => '1',
			'description' => 'Issue details here.',
		];

		$response = $this->actingAs($user)->post(route('support.store'), $data);

		$response->assertRedirect(route('support.index'))
			->assertSessionHas('success', __('Support successfully added.'));
		$this->assertDatabaseHas('supports', [
			'subject'    => 'Help Needed',
			'priority'   => '1',
			'created_by' => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Edit should display form for owner with permission.
	 **/
	public function test_edit_displays_form_for_owner_with_permission()
	{
		$user = User::factory()->create();
		$user->givePermissionTo(Permission::create(['name' > 'edit support']));

		$support = Support::factory()->create([
			'created_by' => $user?->creatorId(),
			'user'       => $user?->id,
		]);

		$response = $this->actingAs($user)->get(route('support.edit', $support));

		$response->assertStatus(200)
			->assertViewIs('support.edit')
			->assertViewHasAll(['support', 'priority', 'status', 'users']);
	}

	/**
	 ** @test
	 **
	 ** Update should modify the ticket and redirect.
	 **/
	public function test_update_modifies_ticket_and_redirects()
	{
		$user = User::factory()->create();
		$user->givePermissionTo(Permission::create(['name' > 'edit support']));

		$support = Support::factory()->create([
			'created_by' => $user?->creatorId(),
			'user'       => $user?->id,
			'subject'    => 'Old',
			'priority'   => '0',
			'status'     => 'open',
		]);

		$data = [
			'subject'  => 'Updated Subject',
			'priority' => '2',
			'status'   => 'on hold',
		];

		$response = $this->actingAs($user)->put(route('support.update', $support), $data);

		$response->assertRedirect(route('support.index'))
			->assertSessionHas('success', __('Support successfully updated.'));
		$this->assertDatabaseHas('supports', [
			'id'       => $support->id,
			'subject'  => 'Updated Subject',
			'priority' => '2',
			'status'   => 'on hold',
		]);
	}

	/**
	 ** @test
	 **
	 ** Destroy should delete the ticket and redirect.
	 **/
	public function test_destroy_deletes_ticket_and_redirects()
	{
		$user = User::factory()->create();
		$user->givePermissionTo(Permission::create(['name' > 'delete support']));

		$support = Support::factory()->create([
			'created_by' => $user?->creatorId(),
			'user'       => $user?->id,
		]);

		$response = $this->actingAs($user)->delete(route('support.destroy', $support));

		$response->assertRedirect(route('support.index'))
			->assertSessionHas('success', __('Support successfully deleted.'));
		$this->assertModelMissing($support);
	}

	/**
	 ** @test
	 **
	 ** Reply should decrypt ID, mark replies read, and show reply view.
	 **/
	public function test_reply_decrypts_id_and_displays_replies()
	{
		$user = User::factory()->create();
		$support = Support::factory()->create([
			'created_by' => $user?->creatorId(),
			'user'       => $user?->id,
		]);
		SupportReply::factory()->count(2)->create(['support_id' => $support->id, 'created_by' => $user?->creatorId()]);

		$encrypted = Crypt::encrypt($support->id);
		$response = $this->actingAs($user)->get(route('support.reply', ['encryptedId' => $encrypted]));

		$response->assertStatus(200)
			->assertViewIs('support.reply')
			->assertViewHasAll(['support', 'replies']);
	}

	/**
	 ** @test
	 **
	 ** ReplyAnswer should save a reply and redirect back.
	 **/
	public function test_replyAnswer_saves_reply_and_redirects()
	{
		$user = User::factory()->create();
		$user->givePermissionTo(Permission::create(['name' > 'reply support']));

		$support = Support::factory()->create([
			'created_by' => $user?->creatorId(),
			'user'       => $user?->id,
		]);

		$data = ['description' => 'Thank you for reaching out.'];

		$response = $this->actingAs($user)->post(route('support.replyAnswer', $support->id), $data);

		$response->assertRedirect()
			->assertSessionHas('success', __('Support reply successfully sent.'));
		$this->assertDatabaseHas('support_replies', [
			'support_id' => $support->id,
			'description' => 'Thank you for reaching out.',
			'created_by' => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Grid should list tickets scoped by user roles.
	 **/
	public function test_grid_lists_tickets_scoped_by_user()
	{
		$user = User::factory()->create(['type' => 'client']);
		Support::factory()->create([
			'created_by' => $user?->creatorId(),
			'user'       => $user?->id,
		]);
		Support::factory()->create([
			'created_by' => $user?->creatorId(),
			'ticket_created' => $user?->id,
			'user'       => $user?->id + 1,
		]);

		$response = $this->actingAs($user)->get(route('support.grid'));

		$response->assertStatus(200)
			->assertViewIs('support.grid')
			->assertViewHas('supports', function ($supports) {
				return $supports->count() === 2;
			});
	}

	/** @test
	 **
	 ** show displays the support ticket for its owner
	 **/
	public function show_displays_view_for_owner()
	{
		// given a support record belonging to company
		$support = Support::factory()->create([
			'created_by' => $this->company->creatorId(),
		]);

		// when the owner views it
		$resp = $this->actingAs($this->company)
			->get(route('support.show', $support));

		// then they see the support.show view with the record
		$resp->assertOk()
			->assertViewIs('support.show')
			->assertViewHas('support', fn ($s) => $s->id === $support->id);
	}

	/** @test
	 **
	 ** show denies access to non-owner by redirecting with error
	 **/
	public function show_denies_non_owner_and_redirects_with_error()
	{
		// given a support record belonging to company
		$support = Support::factory()->create([
			'created_by' => $this->company->creatorId(),
		]);

		// when another user attempts to view it
		$resp = $this->actingAs($this->other)
			->get(route('support.show', $support));

		// then they are redirected to index with a permission error
		$resp->assertRedirect(route('support.index'))
			->assertSessionHas('error');
	}

	/** @test
	 **
	 ** show redirects unauthenticated users to login
	 **/
	public function show_redirects_guest_to_login()
	{
		// given a support record
		$support = Support::factory()->create();

		// when not logged in
		$resp = $this->get(route('support.show', $support));

		// then they are redirected (to login)
		$resp->assertRedirect();
	}
}
